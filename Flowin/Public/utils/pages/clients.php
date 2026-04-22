<?php
session_start();
require_once '../../../Includes/db.php';

// Verificar autenticação
if (!isset($_SESSION['user_id'])) {
    header('Location: Regist.php');
    exit;
}

$empresa_id = $_SESSION['empresa_id'];
$mensagem = '';
$tipo_mensagem = '';

// Processar formulário de criação/edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    try {
        $pdo->beginTransaction();
        
        if ($acao === 'criar') {
            $nome = trim($_POST['nome'] ?? '');
            $tipo = $_POST['tipo'] ?? 'person';
            $documento = trim($_POST['documento'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');
            $morada_rua = trim($_POST['morada_rua'] ?? '');
            $morada_numero = trim($_POST['morada_numero'] ?? '');
            $morada_bairro = trim($_POST['morada_bairro'] ?? '');
            $morada_cidade = trim($_POST['morada_cidade'] ?? '');
            $morada_provincia = trim($_POST['morada_provincia'] ?? '');
            $limite_credito = floatval($_POST['limite_credito'] ?? 0.00);
            $estado = $_POST['estado'] ?? 'active';
            
            // Validações básicas
            if (empty($nome)) {
                throw new Exception('Nome é obrigatório');
            }
            
            if (empty($telefone) && empty($email)) {
                throw new Exception('Telefone ou Email são obrigatórios');
            }
            
            // Inserir cliente
            $sql = "INSERT INTO clients (company_id, name, type, document, email, phone, 
                    address_street, address_city, address_province, credit_limit, status) 
                    VALUES (:company_id, :name, :type, :document, :email, :phone, 
                    :address_street, :address_city, :address_province, :credit_limit, :status)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':company_id' => $empresa_id,
                ':name' => $nome,
                ':type' => $tipo,
                ':document' => $documento ?: null,
                ':email' => $email ?: null,
                ':phone' => $telefone ?: null,
                ':address_street' => $morada_rua ?: null,
                ':address_city' => $morada_cidade ?: null,
                ':address_province' => $morada_provincia ?: null,
                ':credit_limit' => $limite_credito,
                ':status' => $estado
            ]);
            
            $pdo->commit();
            $tipo_mensagem = 'sucesso';
            $mensagem = 'Cliente criado com sucesso!';
            
        } elseif ($acao === 'editar') {
            $id = intval($_POST['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $tipo = $_POST['tipo'] ?? 'person';
            $documento = trim($_POST['documento'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');
            $morada_rua = trim($_POST['morada_rua'] ?? '');
            $morada_numero = trim($_POST['morada_numero'] ?? '');
            $morada_bairro = trim($_POST['morada_bairro'] ?? '');
            $morada_cidade = trim($_POST['morada_cidade'] ?? '');
            $morada_provincia = trim($_POST['morada_provincia'] ?? '');
            $limite_credito = floatval($_POST['limite_credito'] ?? 0.00);
            $estado = $_POST['estado'] ?? 'active';
            
            if (empty($nome)) {
                throw new Exception('Nome é obrigatório');
            }
            
            $sql = "UPDATE clients SET 
                    name = :name, type = :type, document = :document, 
                    email = :email, phone = :phone, 
                    address_street = :address_street, address_city = :address_city, 
                    address_province = :address_province, credit_limit = :credit_limit, 
                    status = :status 
                    WHERE id = :id AND company_id = :company_id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':company_id' => $empresa_id,
                ':name' => $nome,
                ':type' => $tipo,
                ':document' => $documento ?: null,
                ':email' => $email ?: null,
                ':phone' => $telefone ?: null,
                ':address_street' => $morada_rua ?: null,
                ':address_city' => $morada_cidade ?: null,
                ':address_province' => $morada_provincia ?: null,
                ':credit_limit' => $limite_credito,
                ':status' => $estado
            ]);
            
            $pdo->commit();
            $tipo_mensagem = 'sucesso';
            $mensagem = 'Cliente atualizado com sucesso!';
            
        } elseif ($acao === 'eliminar') {
            $id = intval($_POST['id'] ?? 0);
            
            $sql = "DELETE FROM clients WHERE id = :id AND company_id = :company_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':company_id' => $empresa_id
            ]);
            
            $tipo_mensagem = 'sucesso';
            $mensagem = 'Cliente eliminado com sucesso!';
        }
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $tipo_mensagem = 'erro';
        $mensagem = $e->getMessage();
    }
}

// Obter lista de clientes
try {
    $pesquisa = $_GET['pesquisa'] ?? '';
    $filtro_tipo = $_GET['tipo'] ?? '';
    $filtro_estado = $_GET['estado'] ?? '';
    
    $sql = "SELECT * FROM clients WHERE company_id = :company_id";
    $params = [':company_id' => $empresa_id];
    
    if (!empty($pesquisa)) {
        $sql .= " AND (name LIKE :pesquisa OR email LIKE :pesquisa2 OR phone LIKE :pesquisa3 OR document LIKE :pesquisa4)";
        $params[':pesquisa'] = "%{$pesquisa}%";
        $params[':pesquisa2'] = "%{$pesquisa}%";
        $params[':pesquisa3'] = "%{$pesquisa}%";
        $params[':pesquisa4'] = "%{$pesquisa}%";
    }
    
    if (!empty($filtro_tipo)) {
        $sql .= " AND type = :tipo";
        $params[':tipo'] = $filtro_tipo;
    }
    
    if (!empty($filtro_estado)) {
        $sql .= " AND status = :estado";
        $params[':estado'] = $filtro_estado;
    }
    
    $sql .= " ORDER BY name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $clientes = [];
    $tipo_mensagem = 'erro';
    $mensagem = 'Erro ao carregar clientes: ' . $e->getMessage();
}

// Obter dados para edição
$cliente_edicao = null;
if (isset($_GET['editar']) && !empty($_GET['editar'])) {
    $id_edicao = intval($_GET['editar']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id AND company_id = :company_id");
        $stmt->execute([':id' => $id_edicao, ':company_id' => $empresa_id]);
        $cliente_edicao = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Ignora erro
    }
}
?>
<!DOCTYPE html>
<html lang="pt" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Clientes – FlowIn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0F172A; /* bg-slate-900 */
        }
        /* Custom scrollbar for webkit browsers */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #1E293B; /* bg-slate-800 */
        }
        ::-webkit-scrollbar-thumb {
            background: #475569; /* bg-slate-600 */
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #64748B; /* bg-slate-500 */
        }
    </style>
</head>
<body class="text-slate-300 antialiased">

    <div class="relative min-h-screen md:flex">
        <!-- Mobile menu overlay -->
        <div id="mobile-menu-overlay" class="fixed inset-0 bg-black/50 z-20 hidden md:hidden"></div>

        <!-- Sidebar -->
                <aside id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-slate-800 border-r border-slate-700 z-50 transition-transform duration-300 transform -translate-x-full md:translate-x-0">
            <div class="p-6 border-b border-slate-700">
                <h1 class="text-2xl font-bold text-orange-500">FlowIn</h1>
                <p class="text-xs text-slate-400 mt-1">Gestão Inteligente</p>
            </div>
            
            <nav class="mt-6 px-4 space-y-2">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
                <a href="vendas.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Vendas
                </a>
                <a href="clients.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-orange-500/10 text-orange-500 border border-orange-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Clientes
                </a>
                <a href="produtos.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Produtos
                </a>
                <a href="flow.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    Flow
                </a>
                <a href="faturas.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Documentos Fiscais
                </a>
                <a href="despesa.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Despesas
                </a>
                <a href="relatorios.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Relatórios
                </a>
                <a href="utilizadores.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Equipa
                </a>
            </nav>
            
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold">
                        <?php 
                        $user_nome = $_SESSION['user_nome'] ?? 'U';
                        echo strtoupper(substr($user_nome, 0, 2));
                        ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white"><?php echo htmlspecialchars($_SESSION['user_nome'] ?? 'Utilizador'); ?></p>
                        <p class="text-xs text-slate-400"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Staff'); ?></p>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Mobile Menu Button -->
        <button id="mobile-menu-button" class="md:hidden fixed top-4 left-4 z-50 p-2 bg-slate-800 rounded-lg text-slate-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>

        <!-- Main Content -->
        <main class="md:ml-64 flex-1 h-screen overflow-y-auto">
            <!-- Top Bar -->
            <header class="sticky top-0 bg-slate-900/70 backdrop-blur-sm z-10 flex items-center justify-between p-4 border-b border-slate-800">
                 <div class="flex items-center space-x-4">
                     <button id="close-menu-button" class="md:hidden text-slate-400 hover:text-white">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    </button>
                    <a href="logout.php" class="hidden sm:flex items-center text-sm text-slate-300 hover:text-white">
                        <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>
                        Terminar Sessão
                    </a>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold">
                        <?php 
                        $user_nome = $_SESSION['user_nome'] ?? 'U';
                        echo strtoupper(substr($user_nome, 0, 2));
                        ?>
                    </div>
                    <div class="hidden md:block">
                        <p class="text-sm font-semibold text-white"><?php echo htmlspecialchars($_SESSION['user_nome'] ?? 'Utilizador'); ?></p>
                        <p class="text-xs text-slate-400"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Staff'); ?></p>
                    </div>
                </div>
            </header>

            <!-- Customer Content -->
            <div class="p-4 sm:p-6 md:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Gestão de Clientes</h1>
                        <p class="text-slate-400 mt-1">Controlo completo da sua base de clientes</p>
                    </div>
                    <button id="new-customer-button" class="w-full sm:w-auto mt-4 sm:mt-0 flex items-center justify-center bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                        <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Novo Cliente
                    </button>
                </div>

                <!-- Filters -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2 relative">
                        <input type="text" placeholder="Pesquisar por nome, email ou telefone..." class="w-full bg-slate-800 border border-slate-700 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                    <div class="relative">
                        <select class="w-full appearance-none bg-slate-800 border border-slate-700 rounded-lg py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option>Todos os Tipos</option>
                            <option>Individual</option>
                            <option>Empresa</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </div>
                     <div class="relative">
                        <select class="w-full appearance-none bg-slate-800 border border-slate-700 rounded-lg py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option>Todos os Estados</option>
                            <option>Ativo</option>
                            <option>Inativo</option>
                            <option>Bloqueado</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </div>
                </div>

                <!-- Customer List -->
                <div class="mt-6 bg-slate-800 rounded-lg">
                    <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <svg class="h-6 w-6 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.683c.65-.935 1-2.06 1-3.255a4.5 4.5 0 00-9 0c0 1.195.35 2.32 1 3.255a6.375 6.375 0 0111.964 4.684-12.318 12.318 0 01-8.624 2.92z" /></svg>
                            <h3 class="text-lg font-semibold text-white">Clientes Registados (<span id="clientes-count"><?php echo count($clientes); ?></span>)</h3>
                        </div>
                        <?php if (!empty($mensagem)): ?>
                            <div class="text-sm px-3 py-1 rounded <?php echo $tipo_mensagem === 'sucesso' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'; ?>">
                                <?php echo htmlspecialchars($mensagem); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Filtros com form -->
                    <form method="GET" class="px-6 py-4 border-b border-slate-700">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2 relative">
                                <input type="text" name="pesquisa" value="<?php echo htmlspecialchars($pesquisa ?? ''); ?>" placeholder="Pesquisar por nome, email ou telefone..." class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                            </div>
                            <div class="relative">
                                <select name="tipo" class="w-full appearance-none bg-slate-700 border border-slate-600 rounded-lg py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    <option value="">Todos os Tipos</option>
                                    <option value="person" <?php echo ($filtro_tipo ?? '') === 'person' ? 'selected' : ''; ?>>Individual</option>
                                    <option value="company" <?php echo ($filtro_tipo ?? '') === 'company' ? 'selected' : ''; ?>>Empresa</option>
                                </select>
                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </div>
                            <div class="relative">
                                <select name="estado" class="w-full appearance-none bg-slate-700 border border-slate-600 rounded-lg py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    <option value="">Todos os Estados</option>
                                    <option value="active" <?php echo ($filtro_estado ?? '') === 'active' ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="inactive" <?php echo ($filtro_estado ?? '') === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
                                    <option value="blocked" <?php echo ($filtro_estado ?? '') === 'blocked' ? 'selected' : ''; ?>>Bloqueado</option>
                                </select>
                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end gap-2">
                            <a href="clients.php" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-sm">Limpar</a>
                            <button type="submit" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm">Filtrar</button>
                        </div>
                    </form>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-400">
                            <thead class="text-xs text-slate-400 uppercase bg-slate-700/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nome</th>
                                    <th scope="col" class="px-6 py-3">Tipo</th>
                                    <th scope="col" class="px-6 py-3">Contacto</th>
                                    <th scope="col" class="px-6 py-3">Documento</th>
                                    <th scope="col" class="px-6 py-3">Estado</th>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="clientes-list-body">
                                <?php if (empty($clientes)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-12 px-6">
                                        <svg class="mx-auto h-12 w-12 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>
                                        <p class="mt-4 text-slate-400">Nenhum cliente encontrado</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($clientes as $cliente): ?>
                                    <tr class="border-b border-slate-700 hover:bg-slate-700/50">
                                        <td class="px-6 py-4 font-medium text-white"><?php echo htmlspecialchars($cliente['name']); ?></td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 rounded text-xs <?php echo $cliente['type'] === 'company' ? 'bg-blue-500/20 text-blue-400' : 'bg-purple-500/20 text-purple-400'; ?>">
                                                <?php echo $cliente['type'] === 'company' ? 'Empresa' : 'Individual'; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm">
                                                <?php if (!empty($cliente['email'])): ?>
                                                    <div class="text-slate-300"><?php echo htmlspecialchars($cliente['email']); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($cliente['phone'])): ?>
                                                    <div class="text-slate-400 text-xs"><?php echo htmlspecialchars($cliente['phone']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($cliente['document'] ?? '-'); ?></td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 rounded text-xs <?php 
                                                echo $cliente['status'] === 'active' ? 'bg-green-500/20 text-green-400' : 
                                                    ($cliente['status'] === 'blocked' ? 'bg-red-500/20 text-red-400' : 'bg-slate-500/20 text-slate-400'); 
                                            ?>">
                                                <?php 
                                                    echo $cliente['status'] === 'active' ? 'Ativo' : 
                                                        ($cliente['status'] === 'blocked' ? 'Bloqueado' : 'Inativo'); 
                                                ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <a href="?editar=<?php echo $cliente['id']; ?>" class="text-blue-400 hover:text-blue-300" title="Editar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </a>
                                                <form method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja eliminar este cliente?');">
                                                    <input type="hidden" name="acao" value="eliminar">
                                                    <input type="hidden" name="id" value="<?php echo $cliente['id']; ?>">
                                                    <button type="submit" class="text-red-400 hover:text-red-300" title="Eliminar">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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
        </main>
    </div>
    
    <!-- New Customer Modal -->
    <div id="new-customer-modal" class="fixed inset-0 bg-black/60 z-40 hidden items-center justify-center">
        <div class="bg-slate-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between p-5 border-b border-slate-700">
                <h2 class="text-xl font-bold text-white"><?php echo $cliente_edicao ? 'Editar Cliente' : 'Novo Cliente'; ?></h2>
                <button id="close-customer-modal" class="text-slate-400 hover:text-white">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="flex-1 p-6 overflow-y-auto">
                 <form method="POST" class="space-y-6" id="customer-form">
                    <input type="hidden" name="acao" value="<?php echo $cliente_edicao ? 'editar' : 'criar'; ?>">
                    <?php if ($cliente_edicao): ?>
                        <input type="hidden" name="id" value="<?php echo $cliente_edicao['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Nome <span class="text-red-500">*</span></label>
                            <input type="text" name="nome" value="<?php echo htmlspecialchars($cliente_edicao['name'] ?? ''); ?>" required class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Tipo <span class="text-red-500">*</span></label>
                            <select name="tipo" required class="mt-1 w-full appearance-none bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option value="person" <?php echo ($cliente_edicao['type'] ?? '') === 'person' ? 'selected' : ''; ?>>Individual</option>
                                <option value="company" <?php echo ($cliente_edicao['type'] ?? '') === 'company' ? 'selected' : ''; ?>>Empresa</option>
                            </select>
                        </div>
                    </div>
                     <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($cliente_edicao['email'] ?? ''); ?>" class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Telefone <span class="text-red-500">*</span></label>
                            <input type="tel" name="telefone" value="<?php echo htmlspecialchars($cliente_edicao['phone'] ?? ''); ?>" required class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                    </div>
                     <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-300">NIF/BI</label>
                            <input type="text" name="documento" value="<?php echo htmlspecialchars($cliente_edicao['document'] ?? ''); ?>" class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Estado</label>
                            <select name="estado" class="mt-1 w-full appearance-none bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option value="active" <?php echo ($cliente_edicao['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Ativo</option>
                                <option value="inactive" <?php echo ($cliente_edicao['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inativo</option>
                                <option value="blocked" <?php echo ($cliente_edicao['status'] ?? '') === 'blocked' ? 'selected' : ''; ?>>Bloqueado</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <p class="text-base font-medium text-white">Endereço</p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-4">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-slate-300">Rua</label>
                                <input type="text" name="morada_rua" value="<?php echo htmlspecialchars($cliente_edicao['address_street'] ?? ''); ?>" class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Número</label>
                                <input type="text" name="morada_numero" value="<?php echo htmlspecialchars($cliente_edicao['address_number'] ?? ''); ?>" class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-4">
                             <div>
                                <label class="block text-sm font-medium text-slate-300">Bairro</label>
                                <input type="text" name="morada_bairro" value="<?php echo htmlspecialchars($cliente_edicao['address_district'] ?? ''); ?>" class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300">Cidade</label>
                                <input type="text" name="morada_cidade" value="<?php echo htmlspecialchars($cliente_edicao['address_city'] ?? ''); ?>" class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-slate-300">Província</label>
                                <input type="text" name="morada_provincia" value="<?php echo htmlspecialchars($cliente_edicao['address_province'] ?? ''); ?>" class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Limite de Crédito (AOA)</label>
                        <input type="number" step="0.01" name="limite_credito" value="<?php echo htmlspecialchars($cliente_edicao['credit_limit'] ?? '0.00'); ?>" class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-slate-300">Observações</label>
                        <textarea name="observacoes" rows="3" placeholder="Observações sobre o cliente..." class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500"><?php echo htmlspecialchars($cliente_edicao['notes'] ?? ''); ?></textarea>
                    </div>
                 </form>
            </div>
             <div class="p-5 bg-slate-900/50 border-t border-slate-700 flex justify-end space-x-3">
                <button id="cancel-customer-button" type="button" class="py-2 px-4 rounded-lg bg-slate-700 hover:bg-slate-600 text-sm font-semibold text-white">Cancelar</button>
                <button type="submit" form="customer-form" class="py-2 px-4 rounded-lg bg-blue-500 hover:bg-blue-600 text-sm font-semibold text-white flex items-center">
                    <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>
                    <?php echo $cliente_edicao ? 'Atualizar Cliente' : 'Criar Cliente'; ?>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile Overlay -->
    <div id="mobile-menu-overlay" class="lg:hidden fixed inset-0 bg-black/50 z-40 hidden"></div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Mobile sidebar controls
            const sidebar = document.getElementById('sidebar');
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const closeMenuButton = document.getElementById('close-menu-button');
            const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');

            const openMenu = () => {
                sidebar.classList.remove('-translate-x-full');
                mobileMenuOverlay.classList.remove('hidden');
            };

            const closeMenu = () => {
                sidebar.classList.add('-translate-x-full');
                mobileMenuOverlay.classList.add('hidden');
            };

            mobileMenuButton.addEventListener('click', openMenu);
            closeMenuButton.addEventListener('click', closeMenu);
            mobileMenuOverlay.addEventListener('click', closeMenu);

            // New Customer Modal Controls
            const customerModal = document.getElementById('new-customer-modal');
            const newCustomerButton = document.getElementById('new-customer-button');
            const closeCustomerModalButton = document.getElementById('close-customer-modal');
            const cancelCustomerButton = document.getElementById('cancel-customer-button');

            const openCustomerModal = () => {
                customerModal.classList.remove('hidden');
                customerModal.classList.add('flex');
            };

             const closeCustomerModal = () => {
                customerModal.classList.add('hidden');
                customerModal.classList.remove('flex');
            };

            newCustomerButton.addEventListener('click', openCustomerModal);
            closeCustomerModalButton.addEventListener('click', closeCustomerModal);
            cancelCustomerButton.addEventListener('click', closeCustomerModal);
            
            // Close modal on outside click
            customerModal.addEventListener('click', (event) => {
                if (event.target === customerModal) {
                    closeCustomerModal();
                }
            });
            
            // Abrir modal automaticamente se estiver em modo de edição
            <?php if ($cliente_edicao): ?>
            openCustomerModal();
            <?php endif; ?>

        });
    </script>
</body>
</html>