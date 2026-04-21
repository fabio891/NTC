<?php
/**
 * FlowIn - Página Inicial (Landing Page)
 * Página institucional para visitantes
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlowIn – Gestor Comercial Inteligente</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-text { background: linear-gradient(to right, #3b82f6, #8b5cf6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        @keyframes countUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .counter-animate { animation: countUp 1.5s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        .sparkline-path { stroke-dasharray: 100; stroke-dashoffset: 100; animation: drawLine 2s cubic-bezier(0.4, 0, 0.2, 1) forwards; animation-delay: 0.3s; }
        @keyframes drawLine { to { stroke-dashoffset: 0; } }
        .progress-bar { transform-origin: left; transform: scaleX(0); animation: growBar 1.5s cubic-bezier(0.4, 0, 0.2, 1) forwards; animation-delay: 0.8s; }
        @keyframes growBar { to { transform: scaleX(1); } }
        .bar-chart-rect { transform-origin: bottom; transform: scaleY(0); animation: growBarChart 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        .bar-chart-rect:nth-child(1) { animation-delay: 0.1s; }
        .bar-chart-rect:nth-child(2) { animation-delay: 0.2s; }
        .bar-chart-rect:nth-child(3) { animation-delay: 0.3s; }
        .bar-chart-rect:nth-child(4) { animation-delay: 0.4s; }
        @keyframes growBarChart { to { transform: scaleY(1); } }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        .testimonial-card { animation: float 5s ease-in-out infinite; }
    </style>
</head>
<body class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 antialiased">
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-sm border-b border-slate-200 dark:border-slate-800">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex-shrink-0">
                    <a href="Homevist.php" class="flex items-center space-x-2">
                        <svg class="h-8 w-auto text-orange-500" viewBox="0 0 32 32" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M16 32C24.8366 32 32 24.8366 32 16C32 7.16344 24.8366 0 16 0C7.16344 0 0 7.16344 0 16C0 24.8366 7.16344 32 16 32ZM13.0125 10.3958C12.5942 9.97746 12.5942 9.30948 13.0125 8.89115C13.4308 8.47281 14.0988 8.47281 14.5171 8.89115L19.804 14.178C20.221 14.5949 20.2223 15.263 19.8073 15.6817L14.5203 21.1067C14.0999 21.5292 13.4326 21.5292 13.0122 21.1067C12.5918 20.6842 12.5918 20.0169 13.0122 19.5944L17.3989 15.076L13.0125 10.3958Z"/></svg>
                        <span class="text-2xl font-bold text-slate-800 dark:text-white">FlowIn</span>
                    </a>
                </div>
                <nav class="hidden md:flex md:items-center md:space-x-8">
                    <a href="Homevist.php" class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-orange-500">Home</a>
                    <a href="#sobre" class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-orange-500">Sobre</a>
                    <a href="#funcionalidades" class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-orange-500">Funcionalidades</a>
                    <a href="#contacto" class="text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-orange-500">Contacto</a>
                </nav>
                <div class="flex items-center space-x-4">
                    <a href="Regist.php" class="hidden sm:block px-5 py-2.5 text-sm font-semibold text-white bg-orange-500 rounded-lg hover:bg-orange-600">Entrar no Sistema</a>
                    <button id="mobile-menu-button" class="md:hidden p-2 rounded-md text-slate-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
            <div id="mobile-menu" class="hidden md:hidden pb-4">
                <nav class="flex flex-col space-y-2">
                    <a href="Homevist.php" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-slate-200 dark:hover:bg-slate-800">Home</a>
                    <a href="#sobre" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-slate-200 dark:hover:bg-slate-800">Sobre</a>
                    <a href="#funcionalidades" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-slate-200 dark:hover:bg-slate-800">Funcionalidades</a>
                    <a href="#contacto" class="block px-3 py-2 rounded-md text-base font-medium hover:bg-slate-200 dark:hover:bg-slate-800">Contacto</a>
                    <a href="Regist.php" class="block px-3 py-2 rounded-md text-base font-medium text-white bg-orange-500 text-center">Entrar no Sistema</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="pt-20">
        <section class="py-20 sm:py-28">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div class="text-center lg:text-left">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 dark:text-white leading-tight">Gestão Comercial Inteligente para Todos os Negócios</h1>
                        <p class="mt-6 max-w-xl mx-auto lg:mx-0 text-lg text-slate-600 dark:text-slate-400">Do micro ao grande comércio, o <span class="font-bold text-orange-500">FlowIn</span> simplifica a sua gestão com Inteligência Artificial.</p>
                        <div class="mt-10 flex flex-col sm:flex-row justify-center lg:justify-start items-center gap-4">
                            <a href="Regist.php" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 text-base font-semibold text-white bg-orange-500 rounded-lg shadow-lg hover:bg-orange-600">Experimente o FlowIn</a>
                            <a href="Regist.php" class="w-full sm:w-auto px-6 py-3 text-base font-semibold text-slate-800 bg-white rounded-lg shadow-lg hover:bg-slate-100">Entre em contacto</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="sobre" class="py-16 sm:py-24 bg-slate-50 dark:bg-slate-800/50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto">
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Porque Escolher o <span class="text-orange-500">FlowIn?</span></h2>
                </div>
                <div class="mt-16 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md text-center">
                        <div class="mx-auto bg-orange-100 text-orange-600 w-12 h-12 rounded-lg flex items-center justify-center">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect class="bar-chart-rect" x="3" y="10" width="4" height="10"/><rect class="bar-chart-rect" x="10" y="6" width="4" height="14"/><rect class="bar-chart-rect" x="17" y="12" width="4" height="8"/></svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold">Automação Inteligente</h3>
                        <p class="mt-2 text-sm text-slate-600">Relatórios automáticos, sem fechos manuais.</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md text-center">
                        <div class="mx-auto bg-blue-100 text-blue-600 w-12 h-12 rounded-lg flex items-center justify-center">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M2 12h20"/></svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold">Gestão Completa</h3>
                        <p class="mt-2 text-sm text-slate-600">Vendas, estoque e financeiro num só lugar.</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md text-center">
                        <div class="mx-auto bg-purple-100 text-purple-600 w-12 h-12 rounded-lg flex items-center justify-center">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold">Tempo Real</h3>
                        <p class="mt-2 text-sm text-slate-600">Acompanhe seu negócio a qualquer momento.</p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md text-center">
                        <div class="mx-auto bg-green-100 text-green-600 w-12 h-12 rounded-lg flex items-center justify-center">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold">Segurança Total</h3>
                        <p class="mt-2 text-sm text-slate-600">Dados protegidos com criptografia.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="funcionalidades" class="py-16 sm:py-24">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Funcionalidades Poderosas</h2>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl">
                        <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center text-white mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Gestão de Vendas</h3>
                        <p class="text-slate-600">Controle completo do ciclo de vendas.</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl">
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center text-white mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4"/></svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Controle de Estoque</h3>
                        <p class="text-slate-600">Monitoramento em tempo real.</p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl">
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center text-white mb-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2"/></svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-2">Gestão Financeira</h3>
                        <p class="text-slate-600">Fluxo de caixa e lucros.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="contacto" class="py-16 sm:py-24 bg-slate-50 dark:bg-slate-800/50">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl mx-auto text-center">
                    <h2 class="text-3xl font-bold text-slate-900 dark:text-white">Pronto para Começar?</h2>
                    <p class="mt-4 text-lg text-slate-600">Junte-se a milhares de empresas que já usam o FlowIn.</p>
                    <div class="mt-10">
                        <a href="Regist.php" class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-white bg-orange-500 rounded-lg shadow-lg hover:bg-orange-600">Criar Conta Gratuita</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="bg-slate-900 text-slate-400 py-12">
        <div class="container mx-auto px-4">
            <div class="text-center text-sm">
                <p>&copy; <?php echo date('Y'); ?> FlowIn. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('mobile-menu-button').addEventListener('click', () => {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) { target.scrollIntoView({ behavior: 'smooth' }); }
            });
        });
    </script>
</body>
</html>
