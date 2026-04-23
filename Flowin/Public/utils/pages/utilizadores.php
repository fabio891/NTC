<?php
/**
 * FlowIn - Gestão de Utilizadores
 * CRUD completo de utilizadores com segurança e multi-tenancy
 */

$pageTitle = 'Gestão de Equipa';
require_once __DIR__ . '/../../../Includes/header.php';

// Processar ações do formulário (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    try {
        $pdo->beginTransaction();
        
        switch ($_POST['action']) {
            case 'criar':
                // Validações básicas
                $nome = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $role = $_POST['role'] ?? 'cashier';
                $status = $_POST['status'] ?? 'active';
                $phone = trim($_POST['phone'] ?? '');
                
                if (empty($nome) || empty($email) || empty($password)) {
                    throw new Exception('Preencha todos os campos obrigatórios.');
                }
                
                if (!in_array($role, ['admin', 'manager', 'cashier', 'accountant'])) {
                    throw new Exception('Função inválida.');
                }
                
                if (!in_array($status, ['active', 'inactive', 'blocked'])) {
                    throw new Exception('Estado inválido.');
                }
                
                // Verificar email único na empresa
                $stmt_check = $pdo->prepare("SELECT id FROM users WHERE company_id = ? AND email = ?");
                $stmt_check->execute([$_SESSION['company_id'], $email]);
                if ($stmt_check->fetch()) {
                    throw new Exception('Este email já está registado na sua empresa.');
                }
                
                // Hash da password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Inserir utilizador
                $stmt_insert = $pdo->prepare("
                    INSERT INTO users (company_id, name, email, password_hash, role, status, phone, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt_insert->execute([$_SESSION['company_id'], $nome, $email, $password_hash, $role, $status, $phone]);
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Utilizador criado com sucesso!']);
                exit;
                
            case 'editar':
                $id = (int)($_POST['id'] ?? 0);
                $nome = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $role = $_POST['role'] ?? 'cashier';
                $status = $_POST['status'] ?? 'active';
                $phone = trim($_POST['phone'] ?? '');
                $password = $_POST['password'] ?? '';
                
                if ($id <= 0 || empty($nome) || empty($email)) {
                    throw new Exception('Dados inválidos.');
                }
                
                if (!in_array($role, ['admin', 'manager', 'cashier', 'accountant'])) {
                    throw new Exception('Função inválida.');
                }
                
                if (!in_array($status, ['active', 'inactive', 'blocked'])) {
                    throw new Exception('Estado inválido.');
                }
                
                // Verificar se o utilizador existe e pertence à empresa
                $stmt_check = $pdo->prepare("SELECT id FROM users WHERE id = ? AND company_id = ?");
                $stmt_check->execute([$id, $_SESSION['company_id']]);
                if (!$stmt_check->fetch()) {
                    throw new Exception('Utilizador não encontrado.');
                }
                
                // Verificar email único (exceto o próprio)
                $stmt_email = $pdo->prepare("SELECT id FROM users WHERE company_id = ? AND email = ? AND id != ?");
                $stmt_email->execute([$_SESSION['company_id'], $email, $id]);
                if ($stmt_email->fetch()) {
                    throw new Exception('Este email já está em uso por outro utilizador.');
                }
                
                // Atualizar dados
                if (!empty($password)) {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt_update = $pdo->prepare("
                        UPDATE users 
                        SET name = ?, email = ?, password_hash = ?, role = ?, status = ?, phone = ?, updated_at = NOW()
                        WHERE id = ? AND company_id = ?
                    ");
                    $stmt_update->execute([$nome, $email, $password_hash, $role, $status, $phone, $id, $_SESSION['company_id']]);
                } else {
                    $stmt_update = $pdo->prepare("
                        UPDATE users 
                        SET name = ?, email = ?, role = ?, status = ?, phone = ?, updated_at = NOW()
                        WHERE id = ? AND company_id = ?
                    ");
                    $stmt_update->execute([$nome, $email, $role, $status, $phone, $id, $_SESSION['company_id']]);
                }
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Utilizador atualizado com sucesso!']);
                exit;
                
            case 'eliminar':
                $id = (int)($_POST['id'] ?? 0);
                
                if ($id <= 0) {
                    throw new Exception('ID inválido.');
                }
                
                // Proteção: não permitir eliminar o próprio utilizador logado
                if ($id == $_SESSION['user_id']) {
                    throw new Exception('Não pode eliminar a sua própria conta.');
                }
                
                // Verificar se o utilizador existe e pertence à empresa
                $stmt_check = $pdo->prepare("SELECT id FROM users WHERE id = ? AND company_id = ?");
                $stmt_check->execute([$id, $_SESSION['company_id']]);
                if (!$stmt_check->fetch()) {
                    throw new Exception('Utilizador não encontrado.');
                }
                
                // Eliminar utilizador
                $stmt_delete = $pdo->prepare("DELETE FROM users WHERE id = ? AND company_id = ?");
                $stmt_delete->execute([$id, $_SESSION['company_id']]);
                
                $pdo->commit();
                echo json_encode(['success' => true, 'message' => 'Utilizador eliminado com sucesso!']);
                exit;
                
            default:
                throw new Exception('Ação inválida.');
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// Buscar todos os utilizadores da empresa atual
try {
    $stmt = $pdo->prepare("
        SELECT id, name, email, role, status, phone, last_login, created_at
        FROM users
        WHERE company_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['company_id']]);
    $utilizadores = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar utilizadores: " . $e->getMessage());
    $utilizadores = [];
}

// Funções auxiliares para badges
function getRoleBadge($role) {
    $roles = [
        'admin' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-500/20 text-purple-400 border border-purple-500/30">Administrador</span>',
        'manager' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400 border border-blue-500/30">Gestor</span>',
        'cashier' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">Caixa</span>',
        'accountant' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">Contabilista</span>'
    ];
    return $roles[$role] ?? htmlspecialchars($role);
}

function getStatusBadge($status) {
    $statuses = [
        'active' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Ativo</span>',
        'inactive' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-500/20 text-slate-400 border border-slate-500/30">Inativo</span>',
        'blocked' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/20 text-red-400 border border-red-500/30">Bloqueado</span>'
    ];
    return $statuses[$status] ?? htmlspecialchars($status);
}
?>

<!-- Conteúdo Principal -->
<div class="p-4 sm:p-6 md:p-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestão de Equipa</h1>
            <p class="text-slate-400 mt-1">Adicione e gerencie os membros da sua equipa</p>
        </div>
        <button onclick="abrirModalAdicionar()" class="w-full sm:w-auto mt-4 sm:mt-0 flex items-center justify-center bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
            <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Adicionar Membro
        </button>
    </div>
    
    <!-- Filtros -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2 relative">
            <input type="text" id="filtro-pesquisa" placeholder="Pesquisar por nome ou email..." onkeyup="filtrarUtilizadores()" class="w-full bg-slate-800 border border-slate-700 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
        </div>
        <div class="relative">
            <select id="filtro-funcao" onchange="filtrarUtilizadores()" class="w-full appearance-none bg-slate-800 border border-slate-700 rounded-lg py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
                <option value="">Todas as Funções</option>
                <option value="admin">Administrador</option>
                <option value="manager">Gestor</option>
                <option value="cashier">Caixa</option>
                <option value="accountant">Contabilista</option>
            </select>
            <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
        </div>
    </div>
    
    <!-- Lista de Utilizadores -->
    <div class="mt-6">
        <div class="px-6 py-4 bg-slate-800 rounded-t-lg border-b border-slate-700 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="h-6 w-6 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.683c.65-.935 1-2.06 1-3.255a4.5 4.5 0 00-9 0c0 1.195.35 2.32 1 3.255a6.375 6.375 0 0111.964 4.684-12.318 12.318 0 01-8.624 2.92z" /></svg>
                <h3 class="text-lg font-semibold text-white">Membros da Equipa (<span id="total-utilizadores"><?php echo count($utilizadores); ?></span>)</h3>
            </div>
            <div class="relative">
                <select id="filtro-estado" onchange="filtrarUtilizadores()" class="appearance-none bg-slate-700 border border-slate-600 rounded-lg py-1.5 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm text-white">
                    <option value="">Todos os Estados</option>
                    <option value="active">Ativo</option>
                    <option value="inactive">Inativo</option>
                    <option value="blocked">Bloqueado</option>
                </select>
                <svg class="absolute right-2 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
            </div>
        </div>
        
        <?php if (empty($utilizadores)): ?>
        <div class="text-center py-20 px-6 bg-slate-800 rounded-b-lg">
            <svg class="h-16 w-16 mx-auto text-slate-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
            </svg>
            <p class="text-slate-400 font-medium">Nenhum membro na equipa</p>
            <p class="text-slate-500 text-sm mt-1">Clique em "Adicionar Membro" para começar</p>
        </div>
        <?php else: ?>
        <div class="bg-slate-800 rounded-b-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-400" id="tabela-utilizadores">
                    <thead class="text-xs text-slate-400 uppercase bg-slate-700/50">
                        <tr>
                            <th scope="col" class="px-6 py-3">Nome</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Função</th>
                            <th scope="col" class="px-6 py-3">Estado</th>
                            <th scope="col" class="px-6 py-3">Telefone</th>
                            <th scope="col" class="px-6 py-3 whitespace-nowrap">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utilizadores as $utilizador): ?>
                        <tr class="border-b border-slate-700 hover:bg-slate-700/50 utilizador-row" 
                            data-funcao="<?php echo htmlspecialchars($utilizador['role']); ?>" 
                            data-estado="<?php echo htmlspecialchars($utilizador['status']); ?>"
                            data-nome="<?php echo htmlspecialchars(strtolower($utilizador['name'])); ?>"
                            data-email="<?php echo htmlspecialchars(strtolower($utilizador['email'])); ?>">
                            <td class="px-6 py-4 font-medium text-white">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center text-white text-xs font-bold mr-3">
                                        <?php echo strtoupper(substr($utilizador['name'], 0, 2)); ?>
                                    </div>
                                    <?php echo htmlspecialchars($utilizador['name']); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($utilizador['email']); ?></td>
                            <td class="px-6 py-4"><?php echo getRoleBadge($utilizador['role']); ?></td>
                            <td class="px-6 py-4"><?php echo getStatusBadge($utilizador['status']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($utilizador['phone'] ?? '-'); ?></td>
                            <td class="px-6 py-4 flex items-center space-x-3">
                                <button onclick="editarUtilizador(<?php echo htmlspecialchars(json_encode($utilizador)); ?>)" class="text-blue-500 hover:text-blue-400">Editar</button>
                                <?php if (($utilizador['id'] ?? 0) != ($_SESSION['user_id'] ?? 0)): ?>
                                <button onclick="confirmarEliminacao(<?php echo $utilizador['id']; ?>, '<?php echo htmlspecialchars($utilizador['name']); ?>')" class="text-red-500 hover:text-red-400">Eliminar</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Adicionar/Editar Utilizador -->
<div id="modal-utilizador" class="fixed inset-0 bg-black/60 z-40 hidden items-center justify-center">
    <div class="bg-slate-800 rounded-xl shadow-xl w-full max-w-lg mx-4">
        <div class="flex items-center justify-between p-5 border-b border-slate-700">
            <h2 id="modal-titulo" class="text-xl font-bold text-white">Adicionar Membro da Equipa</h2>
            <button onclick="fecharModal()" class="text-slate-400 hover:text-white transition-colors">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="p-6">
            <form id="form-utilizador">
                <input type="hidden" id="utilizador-id" name="id">
                
                <!-- Nome Completo -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Nome Completo <span class="text-orange-500">*</span></label>
                    <input type="text" id="utilizador-nome" name="name" required minlength="3" placeholder="Ex: João Manuel" 
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2.5 px-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 transition-all">
                </div>
                
                <!-- Email -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-300 mb-1">Email <span class="text-orange-500">*</span></label>
                    <input type="email" id="utilizador-email" name="email" required placeholder="joao@email.com" 
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2.5 px-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 transition-all">
                </div>
                
                <!-- Telefone e Função -->
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Telemóvel</label>
                        <input type="tel" id="utilizador-telefone" name="phone" placeholder="923 456 789" maxlength="12"
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2.5 px-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Função <span class="text-orange-500">*</span></label>
                        <select id="utilizador-funcao" name="role" required 
                            class="w-full appearance-none bg-slate-700 border border-slate-600 rounded-lg py-2.5 px-3 text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 transition-all cursor-pointer">
                            <option value="" disabled selected>Selecione</option>
                            <option value="admin">Administrador</option>
                            <option value="manager">Gestor</option>
                            <option value="cashier">Caixa</option>
                            <option value="accountant">Contabilista</option>
                        </select>
                    </div>
                </div>
                
                <!-- Estado -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-300 mb-1">Estado <span class="text-orange-500">*</span></label>
                    <select id="utilizador-estado" name="status" required 
                        class="w-full appearance-none bg-slate-700 border border-slate-600 rounded-lg py-2.5 px-3 text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 transition-all cursor-pointer">
                        <option value="active">Ativo</option>
                        <option value="inactive">Inativo</option>
                        <option value="blocked">Bloqueado</option>
                    </select>
                </div>
                
                <!-- Password -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-300 mb-1">Password <span id="password-label" class="text-orange-500">*</span></label>
                    <input type="password" id="utilizador-password" name="password" placeholder="••••••••" 
                        class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2.5 px-3 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 transition-all">
                    <p class="text-xs text-slate-500 mt-1" id="password-hint">Mínimo 6 caracteres (deixe em branco para manter a atual)</p>
                </div>
            </form>
        </div>
        <div class="p-5 bg-slate-900/50 border-t border-slate-700 flex justify-end space-x-3 rounded-b-xl">
            <button type="button" onclick="fecharModal()" class="py-2.5 px-5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold transition-colors">
                Cancelar
            </button>
            <button type="submit" form="form-utilizador" class="py-2.5 px-5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-semibold transition-colors">
                Guardar
            </button>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Eliminação -->
<div id="modal-eliminacao" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center">
    <div class="bg-slate-800 rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center space-x-3 mb-4">
            <div class="h-10 w-10 rounded-full bg-red-500/20 flex items-center justify-center">
                <svg class="h-6 w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-white">Remover Membro</h3>
        </div>
        <p class="text-slate-300 mb-6">Tem certeza que deseja remover <span id="eliminar-nome" class="font-semibold text-white"></span> da equipa? Esta ação não pode ser desfeita.</p>
        <div class="flex justify-end space-x-3">
            <button type="button" onclick="fecharModalEliminacao()" class="py-2.5 px-5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold transition-colors">
                Cancelar
            </button>
            <button type="button" id="confirmar-eliminar-btn" class="py-2.5 px-5 rounded-lg bg-red-500 hover:bg-red-600 text-white font-semibold transition-colors">
                Remover
            </button>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg transform translate-y-20 opacity-0 transition-all duration-300 z-50 flex items-center space-x-2">
    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
    </svg>
    <span id="toast-mensagem">Operação realizada com sucesso!</span>
</div>

<script>
// Variáveis globais
let eliminacaoId = null;

// Funções globais para evitar erros de clique
function abrirModalAdicionar() {
    document.getElementById('modal-titulo').textContent = 'Adicionar Membro da Equipa';
    document.getElementById('form-utilizador').reset();
    document.getElementById('utilizador-id').value = '';
    document.getElementById('password-label').innerHTML = '*';
    document.getElementById('utilizador-password').required = true;
    document.getElementById('modal-utilizador').classList.remove('hidden');
    document.getElementById('modal-utilizador').classList.add('flex');
}

function fecharModal() {
    document.getElementById('modal-utilizador').classList.add('hidden');
    document.getElementById('modal-utilizador').classList.remove('flex');
    document.getElementById('form-utilizador').reset();
}

function editarUtilizador(utilizador) {
    document.getElementById('modal-titulo').textContent = 'Editar Membro da Equipa';
    document.getElementById('utilizador-id').value = utilizador.id || '';
    document.getElementById('utilizador-nome').value = utilizador.name || '';
    document.getElementById('utilizador-email').value = utilizador.email || '';
    document.getElementById('utilizador-telefone').value = utilizador.phone || '';
    document.getElementById('utilizador-funcao').value = utilizador.role || 'cashier';
    document.getElementById('utilizador-estado').value = utilizador.status || 'active';
    document.getElementById('utilizador-password').value = '';
    document.getElementById('utilizador-password').required = false;
    document.getElementById('password-label').innerHTML = '(opcional)';
    
    document.getElementById('modal-utilizador').classList.remove('hidden');
    document.getElementById('modal-utilizador').classList.add('flex');
}

function confirmarEliminacao(id, nome) {
    eliminacaoId = id;
    document.getElementById('eliminar-nome').textContent = nome;
    document.getElementById('modal-eliminacao').classList.remove('hidden');
    document.getElementById('modal-eliminacao').classList.add('flex');
}

function fecharModalEliminacao() {
    eliminacaoId = null;
    document.getElementById('modal-eliminacao').classList.add('hidden');
    document.getElementById('modal-eliminacao').classList.remove('flex');
}

function filtrarUtilizadores() {
    const pesquisa = document.getElementById('filtro-pesquisa').value.toLowerCase();
    const funcao = document.getElementById('filtro-funcao').value;
    const estado = document.getElementById('filtro-estado').value;
    
    document.querySelectorAll('.utilizador-row').forEach(row => {
        const rowFuncao = row.dataset.funcao;
        const rowEstado = row.dataset.estado;
        const rowNome = row.dataset.nome;
        const rowEmail = row.dataset.email;
        
        const matchPesquisa = rowNome.includes(pesquisa) || rowEmail.includes(pesquisa);
        const matchFuncao = funcao === '' || rowFuncao === funcao;
        const matchEstado = estado === '' || rowEstado === estado;
        
        if (matchPesquisa && matchFuncao && matchEstado) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function mostrarToast(mensagem, erro = false) {
    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toast-mensagem');
    
    toast.className = 'fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 z-50 flex items-center space-x-2';
    toast.classList.add(erro ? 'bg-red-600' : 'bg-green-600');
    toastMsg.textContent = mensagem;
    
    toast.classList.remove('translate-y-20', 'opacity-0');
    
    setTimeout(() => {
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 3000);
}

// Submeter formulário
document.getElementById('form-utilizador').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const id = document.getElementById('utilizador-id').value;
    const action = id ? 'editar' : 'criar';
    formData.append('action', action);
    
    fetch('utilizadores.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarToast(data.message);
            fecharModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarToast(data.error, true);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarToast('Erro ao processar operação. Por favor, tente novamente.', true);
    });
});

// Confirmar eliminação
document.getElementById('confirmar-eliminar-btn').addEventListener('click', function() {
    if (!eliminacaoId) return;
    
    const formData = new FormData();
    formData.append('action', 'eliminar');
    formData.append('id', eliminacaoId);
    
    fetch('utilizadores.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarToast(data.message);
            fecharModalEliminacao();
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarToast(data.error, true);
            fecharModalEliminacao();
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarToast('Erro ao eliminar utilizador. Por favor, tente novamente.', true);
        fecharModalEliminacao();
    });
});

// Fechar modais ao clicar fora
document.getElementById('modal-utilizador').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});

document.getElementById('modal-eliminacao').addEventListener('click', function(e) {
    if (e.target === this) fecharModalEliminacao();
});
</script>

<?php require_once __DIR__ . '/../../../Includes/footer.php'; ?>
