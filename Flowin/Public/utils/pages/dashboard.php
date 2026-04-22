<!DOCTYPE html>
<html lang="pt" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – FlowIn</title>
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
                <a href="Dashboard.html" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-orange-500/10 text-orange-500 border border-orange-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
                <a href="vendas.html" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Vendas
                </a>
                <a href="clients.html" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Clientes
                </a>
                <a href="produtos.html" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Produtos
                </a>
                <a href="flow.html" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    Flow
                </a>
                <a href="faturas.html" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Documentos Fiscais
                </a>
                <a href="despesa.html" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Despesas
                </a>
                <a href="Relatorios.html" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Relatórios
                </a>
                <a href="utilizadores.html" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
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
                    <a href="Regist.html" class="hidden sm:flex items-center text-sm text-slate-300 hover:text-white">
                        <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>
                        Terminar Sessão
                    </a>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-white">Terça-feira, 16 de Setembro</p>
                    <p class="text-xs text-slate-400">Dados dos últimos 30 dias</p>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="p-4 sm:p-6 md:p-8">
                <h1 class="text-2xl font-bold text-white">Dashboard Executivo</h1>
                <p class="text-slate-400">Acompanhe a performance do seu negócio em tempo real</p>
                
                <!-- KPI Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
                    <div class="bg-slate-800 rounded-lg p-5 flex items-center">
                        <div class="bg-green-500/20 text-green-400 rounded-full p-3 flex-shrink-0">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-slate-400">Faturação</p>
                            <p class="text-xl lg:text-2xl font-bold text-white">Kz 0,00</p>
                        </div>
                    </div>
                     <div class="bg-slate-800 rounded-lg p-5 flex items-center">
                        <div class="bg-red-500/20 text-red-400 rounded-full p-3 flex-shrink-0">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75v6.75m0 0l-3-3m3 3l3-3m-8.25 6a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" /></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-slate-400">Despesas</p>
                            <p class="text-xl lg:text-2xl font-bold text-white">Kz 0,00</p>
                        </div>
                    </div>
                     <div class="bg-slate-800 rounded-lg p-5 flex items-center">
                        <div class="bg-green-500/20 text-green-400 rounded-full p-3 flex-shrink-0">
                           <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m3-3-3 3-3-3m6-9a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-slate-400">Lucro Líquido</p>
                            <p class="text-xl lg:text-2xl font-bold text-white">Kz 0,00</p>
                        </div>
                    </div>
                    <div class="bg-slate-800 rounded-lg p-5 flex items-center">
                        <div class="bg-sky-500/20 text-sky-400 rounded-full p-3 flex-shrink-0">
                           <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-slate-400">Clientes Novos</p>
                            <p class="text-xl lg:text-2xl font-bold text-white">0</p>
                        </div>
                    </div>
                </div>

                <!-- Main Chart and Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
                    <div class="lg:col-span-2 bg-slate-800 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-white">Evolução de Vendas</h3>
                        <div class="h-72 mt-4 flex items-center justify-center text-slate-500">
                           <!-- Placeholder for chart -->
                           <div class="w-full h-full border border-dashed border-slate-700 rounded-md flex items-center justify-center">
                               <p class="text-sm">Gráfico de Vendas Indisponível</p>
                           </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-4">Ações Rápidas</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <a href="vendas.html" class="bg-green-500 text-white rounded-lg p-4 flex flex-col items-center justify-center hover:bg-green-600 transition-colors">
                                <svg class="h-6 sm:h-8 w-6 sm:w-8 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                <span class="text-xs sm:text-sm font-semibold">Nova Venda</span>
                            </a>
                             <a href="clients.html" class="bg-blue-500 text-white rounded-lg p-4 flex flex-col items-center justify-center hover:bg-blue-600 transition-colors">
                                <svg class="h-6 sm:h-8 w-6 sm:w-8 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>
                                <span class="text-xs sm:text-sm font-semibold">Novo Cliente</span>
                            </a>
                             <a href="produtos.html" class="bg-purple-500 text-white rounded-lg p-4 flex flex-col items-center justify-center hover:bg-purple-600 transition-colors">
                                <svg class="h-6 sm:h-8 w-6 sm:w-8 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                                <span class="text-xs sm:text-sm font-semibold">Novo Produto</span>
                            </a>
                             <a href="despesa.html" class="bg-red-500 text-white rounded-lg p-4 flex flex-col items-center justify-center hover:bg-red-600 transition-colors">
                                <svg class="h-6 sm:h-8 w-6 sm:w-8 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3m0 0l3-3m-3 3v-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span class="text-xs sm:text-sm font-semibold">Nova Despesa</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Bottom Panels -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    <div class="bg-slate-800 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-white">Vendas Recentes</h3>
                        <div class="mt-4 text-center text-slate-500 py-8">
                            Nenhuma venda recente encontrada
                        </div>
                    </div>
                    <div class="bg-slate-800 rounded-lg p-6 flex flex-col">
                        <h3 class="text-lg font-semibold text-white">Alertas de Stock</h3>
                         <div class="flex-grow flex flex-col items-center justify-center text-center text-slate-500 py-8">
                            <svg class="h-10 w-10 text-green-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                            <p>Todos os produtos com stock adequado!</p>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
    
    <!-- Mobile Overlay -->
    <div id="mobile-menu-overlay" class="lg:hidden fixed inset-0 bg-black/50 z-40 hidden"></div>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
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
        });
    </script>
</body>
</html>
