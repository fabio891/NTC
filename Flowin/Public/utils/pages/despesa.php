<!DOCTYPE html>
<html lang="pt" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Despesas – FlowIn</title>
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
                <a href="Dashboard.html" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
                <a href="vendas.html" class="flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-700 hover:text-white transition-all">
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
                <a href="despesa.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-orange-500/10 text-orange-500 border border-orange-500/20">
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

            <!-- Expense Content -->
            <div class="p-4 sm:p-6 md:p-8">
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
                            <p class="text-xl lg:text-2xl font-bold text-white">Kz 0,00</p>
                        </div>
                    </div>
                     <div class="bg-slate-800 rounded-lg p-5 flex items-center">
                        <div class="bg-yellow-500/20 text-yellow-400 rounded-full p-3 flex-shrink-0">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-slate-400">Despesas Pendentes</p>
                            <p class="text-xl lg:text-2xl font-bold text-white">0</p>
                        </div>
                    </div>
                     <div class="bg-slate-800 rounded-lg p-5 flex items-center">
                        <div class="bg-blue-500/20 text-blue-400 rounded-full p-3 flex-shrink-0">
                           <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-12v.75m0 3v.75m0 3v.75m0 3V18m-3-12v.75m0 3v.75m0 3v.75m0 3V18m9-12l-3 3m0 0l-3-3m3 3v12" /></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-slate-400">Total de Registos</p>
                            <p class="text-xl lg:text-2xl font-bold text-white">0</p>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative">
                        <input type="text" placeholder="Pesquisar..." class="w-full bg-slate-800 border border-slate-700 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    </div>
                    <div class="relative">
                        <select class="w-full appearance-none bg-slate-800 border border-slate-700 rounded-lg py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option>Todas as Categorias</option>
                            <option>Renda</option>
                            <option>Serviços Públicos</option>
                            <option>Marketing</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </div>
                     <div class="relative">
                        <select class="w-full appearance-none bg-slate-800 border border-slate-700 rounded-lg py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option>Todos os Estados</option>
                            <option>Pago</option>
                            <option>Pendente</option>
                            <option>Vencido</option>
                        </select>
                        <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </div>
                </div>

                <!-- Expense List -->
                <div class="mt-6 bg-slate-800 rounded-lg">
                    <div class="px-6 py-4 border-b border-slate-700 flex items-center space-x-2">
                        <svg class="h-6 w-6 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3m0 0l3-3m-3 3v-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <h3 class="text-lg font-semibold text-white">Despesas Registadas (0)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-slate-400">
                            <thead class="text-xs text-slate-400 uppercase bg-slate-700/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Descrição</th>
                                    <th scope="col" class="px-6 py-3">Categoria</th>
                                    <th scope="col" class="px-6 py-3">Valor (Kz)</th>
                                    <th scope="col" class="px-6 py-3">Data</th>
                                    <th scope="col" class="px-6 py-3">Estado</th>
                                    <th scope="col" class="px-6 py-3 whitespace-nowrap">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Empty state -->
                                <tr>
                                    <td colspan="6" class="text-center py-12 px-6">
                                        <svg class="mx-auto h-12 w-12 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <p class="mt-4 text-slate-400">Nenhuma despesa encontrada</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- New Expense Modal -->
    <div id="new-expense-modal" class="fixed inset-0 bg-black/60 z-40 hidden items-center justify-center">
        <div class="bg-slate-800 rounded-lg shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between p-5 border-b border-slate-700">
                <h2 class="text-xl font-bold text-white">Nova Despesa</h2>
                <button id="close-expense-modal" class="text-slate-400 hover:text-white">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="flex-1 p-6 overflow-y-auto">
                 <form class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Descrição <span class="text-red-500">*</span></label>
                        <input type="text" class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                     <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Valor (Kz) <span class="text-red-500">*</span></label>
                            <input type="number" class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Categoria <span class="text-red-500">*</span></label>
                            <select class="mt-1 w-full appearance-none bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option>Outros</option>
                                <option>Renda</option>
                                <option>Marketing</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Data da Despesa <span class="text-red-500">*</span></label>
                            <input type="date" value="2025-09-16" class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Forma de Pagamento</label>
                            <select class="mt-1 w-full appearance-none bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option>MCX Express</option>
                                <option>Dinheiro</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Fornecedor/Prestador</label>
                            <input type="text" class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300">Nº Documento/Fatura</label>
                            <input type="text" class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300">Estado do Pagamento</label>
                        <div class="flex items-center justify-between mt-2">
                             <select class="w-full appearance-none bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option>Pago</option>
                                <option>Pendente</option>
                            </select>
                            <label class="ml-6 flex items-center whitespace-nowrap">
                                <input type="checkbox" class="h-4 w-4 rounded border-slate-500 bg-slate-700 text-orange-600 focus:ring-orange-500">
                                <span class="ml-2 text-sm text-slate-300">Despesa Recorrente</span>
                            </label>
                        </div>
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-slate-300">Observações</label>
                        <textarea rows="3" placeholder="Observações sobre a despesa..." class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
                    </div>
                 </form>
            </div>
             <div class="p-5 bg-slate-900/50 border-t border-slate-700 flex justify-end space-x-3">
                <button id="cancel-expense-button" type="button" class="py-2 px-4 rounded-lg bg-slate-700 hover:bg-slate-600 text-sm font-semibold text-white">Cancelar</button>
                <button type="submit" class="py-2 px-4 rounded-lg bg-red-500 hover:bg-red-600 text-sm font-semibold text-white flex items-center">
                    <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3m0 0l3-3m-3 3v-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Registar Despesa
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

            // New Expense Modal Controls
            const expenseModal = document.getElementById('new-expense-modal');
            const newExpenseButton = document.getElementById('new-expense-button');
            const closeExpenseModalButton = document.getElementById('close-expense-modal');
            const cancelExpenseButton = document.getElementById('cancel-expense-button');

            const openExpenseModal = () => {
                expenseModal.classList.remove('hidden');
                expenseModal.classList.add('flex');
            };

             const closeExpenseModal = () => {
                expenseModal.classList.add('hidden');
                expenseModal.classList.remove('flex');
            };

            newExpenseButton.addEventListener('click', openExpenseModal);
            closeExpenseModalButton.addEventListener('click', closeExpenseModal);
            cancelExpenseButton.addEventListener('click', closeExpenseModal);
            
            expenseModal.addEventListener('click', (event) => {
                if (event.target === expenseModal) {
                    closeExpenseModal();
                }
            });

        });
    </script>
</body>
</html>