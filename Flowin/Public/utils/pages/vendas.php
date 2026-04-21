<!DOCTYPE html>
<html lang="pt" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Vendas – FlowIn</title>
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
        .slide-in {
            transition: transform 0.3s ease-in-out;
        }
    </style>
</head>
<body class="text-slate-300 antialiased overflow-hidden">

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
                <a href="Dashboard.html" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
                <a href="vendas.html" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-orange-500/10 text-orange-500 border border-orange-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Vendas
                </a>
                <a href="clients.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
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
                    <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white font-bold">BM</div>
                    <div>
                        <p class="text-sm font-semibold text-white">Blu Marketing</p>
                        <p class="text-xs text-slate-400">Admin</p>
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
                    <a href="Regist.php" class="hidden sm:flex items-center text-sm text-slate-300 hover:text-white">
                        <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>
                        Terminar Sessão
                    </a>
                </div>
            </header>

            <!-- Sales Content -->
            <div class="p-4 sm:p-6 md:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">Gestão de Vendas</h1>
                        <p class="text-slate-400 mt-1">Controlo completo das suas vendas e faturação</p>
                    </div>
                    <button id="new-sale-button" class="w-full sm:w-auto mt-4 sm:mt-0 flex items-center justify-center bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                        <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Nova Venda
                    </button>
                </div>

                <!-- Filters -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2 relative">
                        <input type="text" placeholder="Pesquisar vendas..." class="w-full bg-slate-800 border border-slate-700 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                    <div class="relative">
                        <select class="w-full appearance-none bg-slate-800 border border-slate-700 rounded-lg py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option>Todos os Estados</option>
                            <option>Pendente</option>
                            <option>Concluída</option>
                            <option>Cancelada</option>
                            <option>Reembolsada</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </div>
                </div>

                <!-- Sales List -->
                <div class="mt-6 bg-slate-800 rounded-lg">
                    <div class="px-6 py-4 border-b border-slate-700">
                        <h3 class="text-lg font-semibold text-white">Vendas Registadas (0)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-400">
                            <thead class="text-xs text-slate-400 uppercase bg-slate-700/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">ID</th>
                                    <th scope="col" class="px-6 py-3">Cliente</th>
                                    <th scope="col" class="px-6 py-3">Data</th>
                                    <th scope="col" class="px-6 py-3">Total (Kz)</th>
                                    <th scope="col" class="px-6 py-3">Pagamento</th>
                                    <th scope="col" class="px-6 py-3">Estado</th>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Empty state -->
                                <tr>
                                    <td colspan="7" class="text-center py-12 px-6">
                                        <svg class="mx-auto h-12 w-12 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .962-.344 1.087-.835l1.828-6.491A1.125 1.125 0 0018.02 6H5.25L5.045 5.23c-.244-.923-.952-1.58-1.846-1.58H2.25zM16.5 21a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM8.25 21a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" /></svg>
                                        <p class="mt-4 text-slate-400">Nenhuma venda encontrada</p>
                                    </td>
                                </tr>
                                <!-- Example Row (hidden by default) -->
                                <!-- 
                                <tr class="bg-slate-800 border-b border-slate-700 hover:bg-slate-700/50">
                                    <td class="px-6 py-4 font-medium text-white whitespace-nowrap">#12345</td>
                                    <td class="px-6 py-4">Nome do Cliente</td>
                                    <td class="px-6 py-4">16/09/2025</td>
                                    <td class="px-6 py-4 font-medium text-white">Kz 15.000,00</td>
                                    <td class="px-6 py-4">MCX Express</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-500/20 text-green-400">Concluída</span>
                                    </td>
                                    <td class="px-6 py-4 flex items-center space-x-3">
                                        <a href="#" class="text-blue-500 hover:text-blue-400">Ver</a>
                                        <a href="#" class="text-yellow-500 hover:text-yellow-400">Editar</a>
                                    </td>
                                </tr> 
                                -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- New Sale Slide-out Panel -->
    <div id="new-sale-panel-overlay" class="fixed inset-0 bg-black/60 z-40 hidden"></div>
    <div id="new-sale-panel" class="fixed inset-y-0 right-0 w-full max-w-lg bg-slate-800 shadow-xl transform translate-x-full slide-in z-50 flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-slate-700">
            <h2 class="text-xl font-bold text-white">Registar Nova Venda</h2>
            <button id="close-sale-panel" class="text-slate-400 hover:text-white">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        <div class="flex-1 p-6 overflow-y-auto">
            <form class="space-y-6">
                <!-- Customer -->
                <div>
                    <label class="block text-sm font-medium text-slate-300">Cliente</label>
                    <select class="mt-1 w-full appearance-none bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option>Selecione um cliente</option>
                        <!-- Add customers here -->
                    </select>
                </div>
                <!-- Date -->
                <div>
                    <label class="block text-sm font-medium text-slate-300">Data da Venda</label>
                    <input type="date" value="2025-09-16" class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                <!-- Sale Items -->
                <div>
                    <div class="flex items-center justify-between">
                         <label class="block text-sm font-medium text-slate-300">Itens da Venda</label>
                         <button type="button" class="text-sm font-medium text-orange-500 hover:text-orange-400 flex items-center">
                            <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Adicionar Item
                         </button>
                    </div>
                    <div class="mt-2 p-4 space-y-4 border border-slate-700 rounded-lg">
                        <div>
                             <label class="block text-xs font-medium text-slate-400">Produto</label>
                             <select class="mt-1 w-full appearance-none bg-slate-600 border border-slate-500 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm">
                                <option>Selecionar produto</option>
                             </select>
                        </div>
                         <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-400">Quantidade</label>
                                <input type="number" value="1" class="mt-1 w-full bg-slate-600 border border-slate-500 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm">
                            </div>
                             <div>
                                <label class="block text-xs font-medium text-slate-400">Preço Unitário</label>
                                <input type="number" value="0" class="mt-1 w-full bg-slate-600 border border-slate-500 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm">
                            </div>
                         </div>
                         <p class="text-right text-sm text-slate-300">Total do Item: <span class="font-semibold">Kz 0,00</span></p>
                    </div>
                </div>
                <!-- Payment Method -->
                <div>
                    <label class="block text-sm font-medium text-slate-300">Forma de Pagamento</label>
                    <select class="mt-1 w-full appearance-none bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option>MCX Express</option>
                        <option>Cartão de Crédito/Débito</option>
                        <option>Transferência Bancária</option>
                        <option>Dinheiro</option>
                    </select>
                </div>
                 <!-- Total -->
                <div class="text-right text-lg">
                    Total da Venda: <span class="font-bold text-white">Kz 0,00</span>
                </div>
                 <!-- Observations -->
                <div>
                    <label class="block text-sm font-medium text-slate-300">Observações</label>
                    <textarea rows="3" placeholder="Observações sobre a venda..." class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
                </div>
            </form>
        </div>
        <div class="p-6 bg-slate-900/50 border-t border-slate-700 flex justify-end space-x-3">
            <button id="cancel-sale-button" type="button" class="py-2 px-4 rounded-lg bg-slate-700 hover:bg-slate-600 text-sm font-semibold text-white">Cancelar</button>
            <button type="submit" class="py-2 px-4 rounded-lg bg-green-500 hover:bg-green-600 text-sm font-semibold text-white">Registar Venda</button>
        </div>
    </div>

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

            // New Sale Panel Controls
            const newSalePanel = document.getElementById('new-sale-panel');
            const newSalePanelOverlay = document.getElementById('new-sale-panel-overlay');
            const newSaleButton = document.getElementById('new-sale-button');
            const closeSalePanelButton = document.getElementById('close-sale-panel');
            const cancelSaleButton = document.getElementById('cancel-sale-button');

            const openSalePanel = () => {
                newSalePanel.classList.remove('translate-x-full');
                newSalePanelOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            };
            
            const closeSalePanel = () => {
                newSalePanel.classList.add('translate-x-full');
                newSalePanelOverlay.classList.add('hidden');
                document.body.style.overflow = '';
            };

            newSaleButton.addEventListener('click', openSalePanel);
            closeSalePanelButton.addEventListener('click', closeSalePanel);
            cancelSaleButton.addEventListener('click', closeSalePanel);
            newSalePanelOverlay.addEventListener('click', closeSalePanel);

        });
    </script>
</body>
</html>
