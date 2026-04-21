<?php
/**
 * FlowIn - Header Comum
 * Inclui sidebar, topbar e estrutura base
 */

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/functions.php';

// Verificar autenticação (descomentar em produção)
// requireAuth();

$pageTitle = $pageTitle ?? 'FlowIn';
$currentPage = basename($_SERVER['PHP_SELF'], ".php");
?>
<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - FlowIn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Animações para o sidebar */
        .sidebar-transition { transition: transform 0.3s ease-in-out; }
    </style>
</head>
<body class="bg-slate-900 text-slate-300 antialiased">

<div class="flex h-screen overflow-hidden">
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 bg-slate-800 transform -translate-x-full md:translate-x-0 sidebar-transition border-r border-slate-700">
        <div class="flex items-center justify-center h-16 bg-slate-900 border-b border-slate-700">
            <a href="dashboard.php" class="text-2xl font-bold text-orange-500 hover:text-orange-400 transition-colors">
                Flow<span class="text-white">In</span>
            </a>
        </div>
        
        <nav class="mt-5 px-4 space-y-2 overflow-y-auto max-h-[calc(100vh-4rem)]">
            <?php 
            $menuItems = [
                ['id' => 'dashboard', 'icon' => 'fa-chart-line', 'label' => 'Dashboard'],
                ['id' => 'flow', 'icon' => 'fa-qrcode', 'label' => 'Flow Scan'],
                ['id' => 'vendas', 'icon' => 'fa-shopping-cart', 'label' => 'Vendas'],
                ['id' => 'clients', 'icon' => 'fa-users', 'label' => 'Clientes'],
                ['id' => 'produtos', 'icon' => 'fa-box', 'label' => 'Produtos'],
                ['id' => 'faturas', 'icon' => 'fa-file-invoice', 'label' => 'Faturas'],
                ['id' => 'despesa', 'icon' => 'fa-wallet', 'label' => 'Despesas'],
                ['id' => 'relatorios', 'icon' => 'fa-chart-pie', 'label' => 'Relatórios'],
                ['id' => 'utilizadores', 'icon' => 'fa-user-shield', 'label' => 'Utilizadores'],
            ];
            
            foreach ($menuItems as $item): 
                $isActive = ($currentPage == $item['id']) ? 'bg-orange-500 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white';
            ?>
                <a href="<?php echo $item['id']; ?>.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?php echo $isActive; ?>">
                    <i class="fas <?php echo $item['icon']; ?> w-6"></i>
                    <span class="ml-3 font-medium"><?php echo htmlspecialchars($item['label']); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- Overlay para mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-[55] hidden md:hidden"></div>

    <!-- Conteúdo Principal -->
    <div class="flex-1 flex flex-col md:ml-64 transition-all duration-300">
        <!-- Top Bar -->
        <header class="h-16 bg-slate-800 border-b border-slate-700 flex items-center justify-between px-6 sticky top-0 z-[60]">
            <button id="mobile-menu-btn" class="md:hidden text-slate-400 hover:text-white focus:outline-none z-[60]">
                <i class="fas fa-bars text-xl"></i>
            </button>
            
            <h2 class="text-xl font-semibold text-white hidden md:block"><?php echo htmlspecialchars($pageTitle); ?></h2>
            
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-white"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Utilizador'); ?></p>
                    <p class="text-xs text-slate-400 capitalize"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'role'); ?></p>
                </div>
                <a href="logout.php" class="text-slate-400 hover:text-red-500 transition-colors" title="Terminar Sessão">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-900 p-6">
