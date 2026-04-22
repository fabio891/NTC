<?php
/**
 * FlowIn - Gestão de Despesas
 * Página principal para listagem, inserção, edição e exclusão de despesas
 */

$pageTitle = 'Gestão de Despesas';
require_once __DIR__ . '/../../../Includes/header.php';

// Obter conexão PDO (já disponível via header.php -> database.php)
$empresa_id = $_SESSION['empresa_id'] ?? 1; // Em produção, usar sempre da sessão

// Processar formulário de nova despesa (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];
    
    if ($acao === 'criar' || $acao === 'editar') {
        $descricao = trim($_POST['descricao'] ?? '');
        $amount = floatval(str_replace(',', '.', $_POST['amount'] ?? 0));
        $category = $_POST['category'] ?? 'other';
        $status = $_POST['status'] ?? 'pending';
        $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
        $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        $paid_date = !empty($_POST['paid_date']) ? $_POST['paid_date'] : null;
        $receipt_image = $_POST['receipt_image'] ?? null;
        $created_by = $_SESSION['user_id'] ?? null;
        
        // Validação básica
        if (empty($descricao) || $amount <= 0) {
            $erro = "Descrição e valor são obrigatórios.";
        } else {
            try {
                if ($acao === 'criar') {
                    $sql = "INSERT INTO expenses (company_id, description, amount, category, status, is_recurring, due_date, paid_date, receipt_image, created_by) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $empresa_id, $descricao, $amount, $category, $status, 
                        $is_recurring, $due_date, $paid_date, $receipt_image, $created_by
                    ]);
                    $sucesso = "Despesa registada com sucesso!";
                } else {
                    $expense_id = intval($_POST['id'] ?? 0);
                    $sql = "UPDATE expenses SET description=?, amount=?, category=?, status=?, is_recurring=?, due_date=?, paid_date=?, receipt_image=? 
                            WHERE id=? AND company_id=?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        $descricao, $amount, $category, $status, $is_recurring, 
                        $due_date, $paid_date, $receipt_image, $expense_id, $empresa_id
                    ]);
                    $sucesso = "Despesa atualizada com sucesso!";
                }
            } catch (PDOException $e) {
                $erro = "Erro ao processar despesa: " . $e->getMessage();
            }
        }
    } elseif ($acao === 'eliminar') {
        $expense_id = intval($_POST['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM expenses WHERE id=? AND company_id=?");
            $stmt->execute([$expense_id, $empresa_id]);
            $sucesso = "Despesa eliminada com sucesso!";
        } catch (PDOException $e) {
            $erro = "Erro ao eliminar despesa: " . $e->getMessage();
        }
    }
}

// Obter todas as despesas da empresa
try {
    $stmt = $pdo->prepare("
        SELECT id, description, amount, category, status, is_recurring, due_date, paid_date, created_at 
        FROM expenses 
        WHERE company_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$empresa_id]);
    $despesas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular KPIs
    $total_despesas = 0;
    $pendentes_count = 0;
    foreach ($despesas as $despesa) {
        $total_despesas += floatval($despesa['amount']);
        if ($despesa['status'] === 'pending') {
            $pendentes_count++;
        }
    }
    $total_registos = count($despesas);
} catch (PDOException $e) {
    $erro = "Erro ao carregar despesas: " . $e->getMessage();
    $despesas = [];
    $total_despesas = 0;
    $pendentes_count = 0;
    $total_registos = 0;
}

// Mapeamento de categorias e estados
$categorias = [
    'rent' => 'Renda',
    'utilities' => 'Serviços Públicos',
    'salaries' => 'Salários',
    'marketing' => 'Marketing',
    'supplies' => 'Fornecimentos',
    'maintenance' => 'Manutenção',
    'other' => 'Outros'
];

$status_map = [
    'pending' => ['label' => 'Pendente', 'color' => 'yellow'],
    'paid' => ['label' => 'Pago', 'color' => 'green'],
    'overdue' => ['label' => 'Vencido', 'color' => 'red']
];
?>

<div class="p-4 sm:p-6 md:p-8">
    <!-- Mensagens de sucesso/erro -->
    <?php if (isset($sucesso)): ?>
        <div class="mb-4 bg-green-500/10 border border-green-500 text-green-500 px-4 py-3 rounded-lg">
            <?= htmlspecialchars($sucesso) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($erro)): ?>
        <div class="mb-4 bg-red-500/10 border border-red-500 text-red-500 px-4 py-3 rounded-lg">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestão de Despesas</h1>
            <p class="text-slate-400 mt-1">Controlo completo dos seus gastos empresariais</p>
        </div>
        <button id="new-expense-button" class="w-full sm:w-auto mt-4 sm:mt-0 flex items-center justify-center bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
            <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nova Despesa
        </button>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
        <div class="bg-slate-800 rounded-lg p-5 flex items-center">
            <div class="bg-red-500/20 text-red-400 rounded-full p-3 flex-shrink-0">
               <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-slate-400">Total de Despesas</p>
                <p class="text-xl lg:text-2xl font-bold text-white">Kz <?= number_format($total_despesas, 2, ',', '.') ?></p>
            </div>
        </div>
         <div class="bg-slate-800 rounded-lg p-5 flex items-center">
            <div class="bg-yellow-500/20 text-yellow-400 rounded-full p-3 flex-shrink-0">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-slate-400">Despesas Pendentes</p>
                <p class="text-xl lg:text-2xl font-bold text-white"><?= $pendentes_count ?></p>
            </div>
        </div>
         <div class="bg-slate-800 rounded-lg p-5 flex items-center">
            <div class="bg-blue-500/20 text-blue-400 rounded-full p-3 flex-shrink-0">
               <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-12v.75m0 3v.75m0 3v.75m0 3V18m-3-12v.75m0 3v.75m0 3v.75m0 3V18m9-12l-3 3m0 0l-3-3m3 3v12" /></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-slate-400">Total de Registos</p>
                <p class="text-xl lg:text-2xl font-bold text-white"><?= $total_registos ?></p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="relative">
            <input type="text" id="filtro-pesquisa" placeholder="Pesquisar..." class="w-full bg-slate-800 border border-slate-700 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-orange-500">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
        </div>
        <div class="relative">
            <select id="filtro-categoria" class="w-full appearance-none bg-slate-800 border border-slate-700 rounded-lg py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500">
                <option value="">Todas as Categorias</option>
                <?php foreach ($categorias as $key => $label): ?>
                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
            <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
        </div>
         <div class="relative">
            <select id="filtro-status" class="w-full appearance-none bg-slate-800 border border-slate-700 rounded-lg py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500">
                <option value="">Todos os Estados</option>
                <?php foreach ($status_map as $key => $info): ?>
                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($info['label']) ?></option>
                <?php endforeach; ?>
            </select>
            <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
        </div>
    </div>

    <!-- Expense List -->
    <div class="mt-6 bg-slate-800 rounded-lg">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center space-x-2">
            <svg class="h-6 w-6 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3m0 0l3-3m-3 3v-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <h3 class="text-lg font-semibold text-white">Despesas Registadas (<?= $total_registos ?>)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-400">
                <thead class="text-xs text-slate-400 uppercase bg-slate-700/50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Descrição</th>
                        <th scope="col" class="px-6 py-3">Categoria</th>
                        <th scope="col" class="px-6 py-3">Valor (Kz)</th>
                        <th scope="col" class="px-6 py-3">Data Vencimento</th>
                        <th scope="col" class="px-6 py-3">Estado</th>
                        <th scope="col" class="px-6 py-3 whitespace-nowrap">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabela-despesas">
                    <?php if (empty($despesas)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-12 px-6">
                            <svg class="mx-auto h-12 w-12 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <p class="mt-4 text-slate-400">Nenhuma despesa encontrada</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($despesas as $despesa): 
                            $status_info = $status_map[$despesa['status']] ?? ['label' => $despesa['status'], 'color' => 'gray'];
                            $categoria_label = $categorias[$despesa['category']] ?? $despesa['category'];
                        ?>
                        <tr class="border-b border-slate-700 hover:bg-slate-700/50 expense-row" 
                            data-categoria="<?= htmlspecialchars($despesa['category']) ?>" 
                            data-status="<?= htmlspecialchars($despesa['status']) ?>"
                            data-descricao="<?= htmlspecialchars(strtolower($despesa['description'])) ?>">
                            <td class="px-6 py-4 font-medium text-white"><?= htmlspecialchars($despesa['description']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($categoria_label) ?></td>
                            <td class="px-6 py-4 font-semibold text-white">Kz <?= number_format($despesa['amount'], 2, ',', '.') ?></td>
                            <td class="px-6 py-4"><?= !empty($despesa['due_date']) ? date('d/m/Y', strtotime($despesa['due_date'])) : '-' ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-<?= $status_info['color'] ?>-500/20 text-<?= $status_info['color'] ?>-400">
                                    <?= htmlspecialchars($status_info['label']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button class="btn-editar text-blue-400 hover:text-blue-300" 
                                        data-id="<?= $despesa['id'] ?>"
                                        data-descricao="<?= htmlspecialchars($despesa['description']) ?>"
                                        data-amount="<?= $despesa['amount'] ?>"
                                        data-category="<?= htmlspecialchars($despesa['category']) ?>"
                                        data-status="<?= htmlspecialchars($despesa['status']) ?>"
                                        data-due-date="<?= $despesa['due_date'] ?>"
                                        data-is-recurring="<?= $despesa['is_recurring'] ? '1' : '0' ?>">
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Tem a certeza que deseja eliminar esta despesa?');">
                                        <input type="hidden" name="acao" value="eliminar">
                                        <input type="hidden" name="id" value="<?= $despesa['id'] ?>">
                                        <button type="submit" class="text-red-400 hover:text-red-300">
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Expense Modal -->
<div id="new-expense-modal" class="fixed inset-0 bg-black/60 z-40 hidden items-center justify-center">
    <div class="bg-slate-800 rounded-lg shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-5 border-b border-slate-700">
            <h2 class="text-xl font-bold text-white" id="modal-title">Nova Despesa</h2>
            <button id="close-expense-modal" class="text-slate-400 hover:text-white">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="flex-1 p-6 overflow-y-auto">
            <form id="expense-form" method="POST" class="space-y-6">
                <input type="hidden" name="acao" id="form-acao" value="criar">
                <input type="hidden" name="id" id="form-id" value="">
                
                <div>
                    <label class="block text-sm font-medium text-slate-300">Descrição <span class="text-red-500">*</span></label>
                    <input type="text" name="descricao" id="form-descricao" required class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Valor (Kz) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" id="form-amount" step="0.01" required class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Categoria <span class="text-red-500">*</span></label>
                        <select name="category" id="form-category" required class="mt-1 w-full appearance-none bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <?php foreach ($categorias as $key => $label): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Data de Vencimento</label>
                        <input type="date" name="due_date" id="form-due-date" class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Estado</label>
                        <select name="status" id="form-status" class="mt-1 w-full appearance-none bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <?php foreach ($status_map as $key => $info): ?>
                                <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($info['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="flex items-center mt-2">
                        <input type="checkbox" name="is_recurring" id="form-is-recurring" class="h-4 w-4 rounded border-slate-500 bg-slate-700 text-orange-600 focus:ring-orange-500">
                        <span class="ml-2 text-sm text-slate-300">Despesa Recorrente</span>
                    </label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300">Observações</label>
                    <textarea name="notes" id="form-notes" rows="3" placeholder="Observações sobre a despesa..." class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
                </div>
            </form>
        </div>
        <div class="p-5 bg-slate-900/50 border-t border-slate-700 flex justify-end space-x-3">
            <button id="cancel-expense-button" type="button" class="py-2 px-4 rounded-lg bg-slate-700 hover:bg-slate-600 text-sm font-semibold text-white">Cancelar</button>
            <button type="submit" form="expense-form" class="py-2 px-4 rounded-lg bg-red-500 hover:bg-red-600 text-sm font-semibold text-white flex items-center">
                <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3m0 0l3-3m-3 3v-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span id="btn-submit-text">Registar Despesa</span>
            </button>
        </div>
    </div>
</div>

<!-- Mobile Overlay -->
<div id="mobile-menu-overlay" class="lg:hidden fixed inset-0 bg-black/50 z-40 hidden"></div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Filtros da tabela
            const filtroPesquisa = document.getElementById('filtro-pesquisa');
            const filtroCategoria = document.getElementById('filtro-categoria');
            const filtroStatus = document.getElementById('filtro-status');
            const tabelaDespesas = document.getElementById('tabela-despesas');
            
            function filtrarTabela() {
                const termo = filtroPesquisa.value.toLowerCase();
                const categoria = filtroCategoria.value;
                const status = filtroStatus.value;
                
                const linhas = tabelaDespesas.querySelectorAll('.expense-row');
                
                linhas.forEach(linha => {
                    const descricao = linha.dataset.descricao || '';
                    const linhaCategoria = linha.dataset.categoria || '';
                    const linhaStatus = linha.dataset.status || '';
                    
                    const matchTermo = !termo || descricao.includes(termo);
                    const matchCategoria = !categoria || linhaCategoria === categoria;
                    const matchStatus = !status || linhaStatus === status;
                    
                    if (matchTermo && matchCategoria && matchStatus) {
                        linha.style.display = '';
                    } else {
                        linha.style.display = 'none';
                    }
                });
            }
            
            if (filtroPesquisa) filtroPesquisa.addEventListener('input', filtrarTabela);
            if (filtroCategoria) filtroCategoria.addEventListener('change', filtrarTabela);
            if (filtroStatus) filtroStatus.addEventListener('change', filtrarTabela);
            
            // Modal de Nova Despesa / Editar Despesa
            const expenseModal = document.getElementById('new-expense-modal');
            const newExpenseButton = document.getElementById('new-expense-button');
            const closeExpenseModalButton = document.getElementById('close-expense-modal');
            const cancelExpenseButton = document.getElementById('cancel-expense-button');
            const modalTitle = document.getElementById('modal-title');
            const btnSubmitText = document.getElementById('btn-submit-text');
            const formAcao = document.getElementById('form-acao');
            const formId = document.getElementById('form-id');
            const formDescricao = document.getElementById('form-descricao');
            const formAmount = document.getElementById('form-amount');
            const formCategory = document.getElementById('form-category');
            const formDueDate = document.getElementById('form-due-date');
            const formStatus = document.getElementById('form-status');
            const formIsRecurring = document.getElementById('form-is-recurring');
            
            const openExpenseModal = () => {
                expenseModal.classList.remove('hidden');
                expenseModal.classList.add('flex');
            };

            const closeExpenseModal = () => {
                expenseModal.classList.add('hidden');
                expenseModal.classList.remove('flex');
                // Limpar formulário
                formAcao.value = 'criar';
                formId.value = '';
                formDescricao.value = '';
                formAmount.value = '';
                formCategory.value = 'other';
                formDueDate.value = '';
                formStatus.value = 'pending';
                formIsRecurring.checked = false;
                modalTitle.textContent = 'Nova Despesa';
                btnSubmitText.textContent = 'Registar Despesa';
            };

            newExpenseButton.addEventListener('click', openExpenseModal);
            closeExpenseModalButton.addEventListener('click', closeExpenseModal);
            cancelExpenseButton.addEventListener('click', closeExpenseModal);
            
            expenseModal.addEventListener('click', (event) => {
                if (event.target === expenseModal) {
                    closeExpenseModal();
                }
            });
            
            // Botões de editar
            document.querySelectorAll('.btn-editar').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const descricao = this.dataset.descricao;
                    const amount = this.dataset.amount;
                    const category = this.dataset.category;
                    const status = this.dataset.status;
                    const dueDate = this.dataset.dueDate;
                    const isRecurring = this.dataset.isRecurring === '1';
                    
                    formAcao.value = 'editar';
                    formId.value = id;
                    formDescricao.value = descricao;
                    formAmount.value = amount;
                    formCategory.value = category;
                    formStatus.value = status;
                    formDueDate.value = dueDate || '';
                    formIsRecurring.checked = isRecurring;
                    
                    modalTitle.textContent = 'Editar Despesa';
                    btnSubmitText.textContent = 'Atualizar Despesa';
                    
                    openExpenseModal();
                });
            });
        });
    </script>
    
    <?php require_once __DIR__ . '/../../../Includes/footer.php'; ?>