<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flow – Scan Rápido - FlowIn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background-color: #0f172a; }
        
        /* Scrollbar customizada */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #1e293b; }
        ::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #64748b; }
        
        /* Animações do Modal */
        .modal-overlay {
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .modal-content {
            transform: scale(0.95);
            transition: transform 0.3s ease;
        }
        .modal-overlay.active .modal-content {
            transform: scale(1);
        }
        
        /* Scanner styles */
        #reader {
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
        }
        #reader video {
            object-fit: cover;
            border-radius: 12px;
        }
        
        /* Toast Notifications */
        .toast {
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
        }
        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        /* Stock badge colors */
        .stock-high { background-color: #166534; color: #dcfce7; }
        .stock-low { background-color: #854d0e; color: #fef9c3; }
        .stock-out { background-color: #991b1b; color: #fee2e2; }
        
        /* Animation for result panel */
        .result-panel {
            animation: slideUp 0.4s ease-out;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Input focus glow */
        .input-glow:focus {
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.3);
        }
    </style>
</head>
<body class="text-slate-200 min-h-screen">
    
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
            <a href="flow.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-orange-500/10 text-orange-500 border border-orange-500/20">
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
    
    <!-- Mobile Overlay -->
    <div id="mobile-menu-overlay" class="md:hidden fixed inset-0 bg-black/50 z-40 hidden"></div>

    <!-- Main Content -->
    <main class="md:ml-64 p-4 md:p-8 pt-20 md:pt-8">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2 bg-orange-500/20 rounded-lg">
                    <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-white">Flow – Scan Rápido</h1>
                    <p class="text-slate-400 mt-1">Digitalize ou digite o código de barras para ações instantâneas.</p>
                </div>
            </div>
        </div>
        
        <!-- Input Zone - Dual Option Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            
            <!-- Card 1: Digitar Código -->
            <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-blue-500/20 rounded-lg">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <h2 class="text-xl font-semibold text-white">Digitar Código</h2>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Código de Barras</label>
                        <input type="text" id="barcodeInput" 
                            placeholder="Digite ou cole o código de barras" 
                            class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all input-glow text-lg"
                            autofocus>
                    </div>
                    <button onclick="searchProduct()" 
                        class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-orange-500/25 hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        Verificar
                    </button>
                </div>
            </div>
            
            <!-- Card 2: Escanear com Câmera -->
            <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-green-500/20 rounded-lg">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <h2 class="text-xl font-semibold text-white">Escanear com Câmera</h2>
                </div>
                
                <div id="scannerContainer" class="space-y-4">
                    <button onclick="startScanner()" 
                        class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-green-500/25 hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        Abrir Scanner
                    </button>
                    <div id="reader" class="hidden"></div>
                    <button id="stopScannerBtn" onclick="stopScanner()" 
                        class="hidden w-full flex items-center justify-center gap-2 px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Fechar Scanner
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Result Panel (initially hidden) -->
        <div id="resultPanel" class="hidden result-panel bg-slate-800 rounded-xl p-6 border border-slate-700 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Product Image -->
                <div class="md:col-span-1">
                    <div class="aspect-square bg-slate-700 rounded-xl flex items-center justify-center overflow-hidden">
                        <img id="productImage" src="" alt="Produto" class="w-full h-full object-cover hidden">
                        <svg id="imagePlaceholder" class="w-20 h-20 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="md:col-span-2 space-y-4">
                    <div>
                        <h3 id="productName" class="text-2xl font-bold text-white mb-2">Nome do Produto</h3>
                        <p class="text-slate-400">Código: <span id="productCode" class="text-slate-300 font-mono"></span></p>
                    </div>
                    
                    <div class="flex items-center gap-6">
                        <div>
                            <p class="text-sm text-slate-400 mb-1">Preço de Venda</p>
                            <p id="productPrice" class="text-3xl font-bold text-orange-500">0,00 Kz</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-slate-400 mb-1">Stock Atual</p>
                            <div class="flex items-center gap-2">
                                <span id="productStock" class="text-2xl font-bold text-white">0</span>
                                <span id="stockBadge" class="px-3 py-1 rounded-full text-xs font-semibold">unidades</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="grid grid-cols-3 gap-3 pt-4">
                        <button onclick="addStock()" class="flex flex-col items-center justify-center gap-2 px-4 py-4 bg-green-500/20 hover:bg-green-500/30 border border-green-500/30 rounded-xl text-green-400 transition-all hover:-translate-y-0.5">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            <span class="text-sm font-semibold">Adicionar Stock</span>
                        </button>
                        
                        <button onclick="createSale()" class="flex flex-col items-center justify-center gap-2 px-4 py-4 bg-orange-500/20 hover:bg-orange-500/30 border border-orange-500/30 rounded-xl text-orange-400 transition-all hover:-translate-y-0.5">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span class="text-sm font-semibold">Criar Venda</span>
                        </button>
                        
                        <button onclick="viewDetails()" class="flex flex-col items-center justify-center gap-2 px-4 py-4 bg-blue-500/20 hover:bg-blue-500/30 border border-blue-500/30 rounded-xl text-blue-400 transition-all hover:-translate-y-0.5">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <span class="text-sm font-semibold">Ver Detalhes</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Not Found State -->
        <div id="notFoundPanel" class="hidden bg-slate-800 rounded-xl p-8 border border-slate-700 mb-8 text-center">
            <svg class="w-20 h-20 mx-auto text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <h3 class="text-xl font-semibold text-white mb-2">Código não localizado na base de dados.</h3>
            <p class="text-slate-400 mb-6">Este código de barras não está registado no sistema.</p>
            <button onclick="openNewProductModal()" class="inline-flex items-center gap-2 px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-orange-500/25">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Cadastrar Novo Produto
            </button>
        </div>
        
        <!-- History Section -->
        <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
            <div class="flex items-center gap-3 mb-4">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <h3 class="text-lg font-semibold text-white">Histórico Recente</h3>
            </div>
            <div id="historyList" class="space-y-2">
                <p class="text-slate-400 text-sm text-center py-4">Nenhum produto consultado recentemente.</p>
            </div>
        </div>
        
    </main>

    <!-- New Product Modal -->
    <div id="newProductModal" class="modal-overlay fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-slate-800 rounded-2xl w-full max-w-md border border-slate-700 shadow-2xl">
            
            <!-- Modal Header -->
            <div class="border-b border-slate-700 p-6 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-white">Novo Produto</h2>
                    <p class="text-slate-400 text-sm mt-1">Registre um novo produto rapidamente</p>
                </div>
                <button onclick="closeNewProductModal()" class="p-2 hover:bg-slate-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Nome do Produto *</label>
                    <input type="text" id="newProductName" placeholder="Ex: Refrigerante Cuca 330ml" 
                        class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Código de Barras</label>
                    <input type="text" id="newProductCode" readonly 
                        class="w-full px-4 py-2.5 bg-slate-600 border border-slate-600 rounded-xl text-slate-300 cursor-not-allowed font-mono">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Preço de Venda (Kz)</label>
                        <input type="number" id="newProductPrice" placeholder="0,00" step="0.01" 
                            class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Stock Inicial</label>
                        <input type="number" id="newProductStock" placeholder="0" min="0" 
                            class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500">
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="border-t border-slate-700 p-6 flex items-center justify-end gap-4">
                <button onclick="closeNewProductModal()" class="px-6 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-xl font-medium transition-all">
                    Cancelar
                </button>
                <button onclick="saveNewProduct()" class="flex items-center gap-2 px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-orange-500/25">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Salvar e Continuar
                </button>
            </div>
        </div>
    </div>

    <!-- Add Stock Modal -->
    <div id="addStockModal" class="modal-overlay fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-slate-800 rounded-2xl w-full max-w-sm border border-slate-700 shadow-2xl">
            <div class="border-b border-slate-700 p-6 flex items-center justify-between">
                <h2 class="text-xl font-bold text-white">Adicionar Stock</h2>
                <button onclick="closeAddStockModal()" class="p-2 hover:bg-slate-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-slate-300">Produto: <span id="addStockProductName" class="font-semibold text-white"></span></p>
                <p class="text-slate-300">Stock atual: <span id="addStockCurrent" class="font-semibold text-orange-500"></span></p>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Quantidade a Adicionar</label>
                    <input type="number" id="addStockQuantity" placeholder="0" min="1" 
                        class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500">
                </div>
            </div>
            <div class="border-t border-slate-700 p-6 flex items-center justify-end gap-4">
                <button onclick="closeAddStockModal()" class="px-6 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-xl font-medium transition-all">
                    Cancelar
                </button>
                <button onclick="confirmAddStock()" class="flex items-center gap-2 px-6 py-2.5 bg-green-500 hover:bg-green-600 text-white rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-green-500/25">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-4 right-4 z-[60] space-y-3"></div>

    <script>
        // ==================== DADOS MOCKADOS ====================
        let produtos = JSON.parse(localStorage.getItem('flowin_produtos')) || [
            { id: 1, codigo: "7891234567890", nome: "Refrigerante Cuca 330ml", preco: 150.00, stock: 24, imagem: "" },
            { id: 2, codigo: "7892345678901", nome: "Arroz Tio Lucas 5kg", preco: 2500.00, stock: 8, imagem: "" },
            { id: 3, codigo: "7893456789012", nome: "Óleo Alimentar 1L", preco: 850.00, stock: 0, imagem: "" },
            { id: 4, codigo: "7894567890123", nome: "Açúcar Refinado 1kg", preco: 450.00, stock: 15, imagem: "" },
            { id: 5, codigo: "7895678901234", nome: "Sal Marinho 500g", preco: 200.00, stock: 50, imagem: "" }
        ];

        let historico = JSON.parse(localStorage.getItem('flowin_flow_historico')) || [];
        let produtoAtual = null;
        let html5QrcodeScanner = null;

        // ==================== INICIALIZAÇÃO ====================
        document.addEventListener('DOMContentLoaded', () => {
            setupMobileMenu();
            renderHistory();
            
            // Enter key to search
            document.getElementById('barcodeInput').addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    searchProduct();
                }
            });
        });

        function setupMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const mobileBtn = document.getElementById('mobile-menu-button');
            const overlay = document.getElementById('mobile-menu-overlay');

            mobileBtn.addEventListener('click', () => {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            });

            overlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        }

        // ==================== SEARCH FUNCTION ====================
        function searchProduct() {
            const codigo = document.getElementById('barcodeInput').value.trim();
            
            if (!codigo) {
                showToast('warning', 'Digite um código de barras');
                return;
            }

            const produto = produtos.find(p => p.codigo === codigo);
            
            document.getElementById('resultPanel').classList.add('hidden');
            document.getElementById('notFoundPanel').classList.add('hidden');

            if (produto) {
                produtoAtual = produto;
                displayProduct(produto);
                addToHistory(produto);
            } else {
                document.getElementById('notFoundPanel').classList.remove('hidden');
                document.getElementById('newProductCode').value = codigo;
            }
        }

        function displayProduct(produto) {
            document.getElementById('productName').textContent = produto.nome;
            document.getElementById('productCode').textContent = produto.codigo;
            document.getElementById('productPrice').textContent = formatCurrency(produto.preco);
            document.getElementById('productStock').textContent = produto.stock;
            
            // Stock badge
            const stockBadge = document.getElementById('stockBadge');
            stockBadge.className = 'px-3 py-1 rounded-full text-xs font-semibold';
            
            if (produto.stock > 10) {
                stockBadge.classList.add('stock-high');
                stockBadge.textContent = 'Em Stock';
            } else if (produto.stock > 0) {
                stockBadge.classList.add('stock-low');
                stockBadge.textContent = 'Stock Baixo';
            } else {
                stockBadge.classList.add('stock-out');
                stockBadge.textContent = 'Esgotado';
            }
            
            // Image
            const img = document.getElementById('productImage');
            const placeholder = document.getElementById('imagePlaceholder');
            
            if (produto.imagem) {
                img.src = produto.imagem;
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
            } else {
                img.classList.add('hidden');
                placeholder.classList.remove('hidden');
            }
            
            document.getElementById('resultPanel').classList.remove('hidden');
        }

        // ==================== SCANNER FUNCTIONS ====================
        function startScanner() {
            const readerDiv = document.getElementById('reader');
            const stopBtn = document.getElementById('stopScannerBtn');
            
            readerDiv.classList.remove('hidden');
            stopBtn.classList.remove('hidden');
            
            html5QrcodeScanner = new Html5Qrcode("reader");
            
            const config = { fps: 10, qrbox: { width: 250, height: 250 } };
            
            html5QrcodeScanner.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                onScanFailure
            ).catch(err => {
                console.error("Erro ao iniciar scanner:", err);
                showToast('error', 'Não foi possível aceder à câmera');
                readerDiv.classList.add('hidden');
                stopBtn.classList.add('hidden');
            });
        }

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(() => {
                    document.getElementById('reader').classList.add('hidden');
                    document.getElementById('stopScannerBtn').classList.add('hidden');
                    html5QrcodeScanner.clear();
                }).catch(err => {
                    console.error("Erro ao parar scanner:", err);
                });
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            document.getElementById('barcodeInput').value = decodedText;
            stopScanner();
            searchProduct();
            showToast('success', 'Código detetado!');
        }

        function onScanFailure(error) {
            // Silencioso - erros comuns durante scanning
        }

        // ==================== HISTORY FUNCTIONS ====================
        function addToHistory(produto) {
            // Remove if already exists
            historico = historico.filter(p => p.codigo !== produto.codigo);
            
            // Add to beginning
            historico.unshift({
                codigo: produto.codigo,
                nome: produto.nome,
                data: new Date().toISOString()
            });
            
            // Keep only last 5
            historico = historico.slice(0, 5);
            
            localStorage.setItem('flowin_flow_historico', JSON.stringify(historico));
            renderHistory();
        }

        function renderHistory() {
            const container = document.getElementById('historyList');
            
            if (historico.length === 0) {
                container.innerHTML = '<p class="text-slate-400 text-sm text-center py-4">Nenhum produto consultado recentemente.</p>';
                return;
            }
            
            container.innerHTML = historico.map(item => `
                <button onclick="quickSearch('${item.codigo}')" 
                    class="w-full flex items-center justify-between p-3 bg-slate-700/50 hover:bg-slate-700 rounded-lg transition-all group">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-orange-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div class="text-left">
                            <p class="text-sm font-medium text-white">${item.nome}</p>
                            <p class="text-xs text-slate-400 font-mono">${item.codigo}</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            `).join('');
        }

        function quickSearch(codigo) {
            document.getElementById('barcodeInput').value = codigo;
            searchProduct();
        }

        // ==================== NEW PRODUCT MODAL ====================
        function openNewProductModal() {
            document.getElementById('newProductName').value = '';
            document.getElementById('newProductPrice').value = '';
            document.getElementById('newProductStock').value = '';
            document.getElementById('newProductModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeNewProductModal() {
            document.getElementById('newProductModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function saveNewProduct() {
            const nome = document.getElementById('newProductName').value.trim();
            const codigo = document.getElementById('newProductCode').value;
            const preco = parseFloat(document.getElementById('newProductPrice').value) || 0;
            const stock = parseInt(document.getElementById('newProductStock').value) || 0;
            
            if (!nome) {
                showToast('error', 'Nome do produto é obrigatório');
                return;
            }
            
            const novoProduto = {
                id: Date.now(),
                codigo,
                nome,
                preco,
                stock,
                imagem: ''
            };
            
            produtos.push(novoProduto);
            localStorage.setItem('flowin_produtos', JSON.stringify(produtos));
            
            closeNewProductModal();
            
            produtoAtual = novoProduto;
            displayProduct(novoProduto);
            addToHistory(novoProduto);
            
            showToast('success', 'Produto cadastrado com sucesso!');
        }

        // ==================== ACTION BUTTONS ====================
        function addStock() {
            if (!produtoAtual) return;
            
            document.getElementById('addStockProductName').textContent = produtoAtual.nome;
            document.getElementById('addStockCurrent').textContent = produtoAtual.stock;
            document.getElementById('addStockQuantity').value = '';
            document.getElementById('addStockModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeAddStockModal() {
            document.getElementById('addStockModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function confirmAddStock() {
            const quantidade = parseInt(document.getElementById('addStockQuantity').value) || 0;
            
            if (quantidade <= 0) {
                showToast('error', 'Quantidade inválida');
                return;
            }
            
            const index = produtos.findIndex(p => p.id === produtoAtual.id);
            if (index !== -1) {
                produtos[index].stock += quantidade;
                localStorage.setItem('flowin_produtos', JSON.stringify(produtos));
                produtoAtual.stock = produtos[index].stock;
                
                closeAddStockModal();
                displayProduct(produtoAtual);
                showToast('success', `Stock adicionado! Novo total: ${produtoAtual.stock} unidades`);
            }
        }

        function createSale() {
            if (!produtoAtual) return;
            
            if (produtoAtual.stock <= 0) {
                showToast('warning', 'Produto sem stock disponível');
                return;
            }
            
            // Redirect to sales page with product info
            const saleData = {
                produtoId: produtoAtual.id,
                nome: produtoAtual.nome,
                preco: produtoAtual.preco
            };
            
            sessionStorage.setItem('flow_quick_sale', JSON.stringify(saleData));
            window.location.href = 'vendas.html';
        }

        function viewDetails() {
            if (!produtoAtual) return;
            
            // Redirect to products page with filter
            window.location.href = `produtos.php?codigo=${produtoAtual.codigo}`;
        }

        // ==================== UTILITIES ====================
        function formatCurrency(value) {
            return new Intl.NumberFormat('pt-AO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + ' Kz';
        }

        function showToast(type, message) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');

            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                info: 'bg-blue-500',
                warning: 'bg-yellow-500'
            };

            const icons = {
                success: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
                error: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
                info: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                warning: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>'
            };

            toast.className = `toast flex items-center gap-3 px-4 py-3 ${colors[type]} text-white rounded-lg shadow-lg min-w-[300px]`;
            toast.innerHTML = `${icons[type]}<span class="flex-1">${message}</span>`;

            container.appendChild(toast);

            setTimeout(() => toast.classList.add('show'), 10);

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

    </script>
</body>
</html>
