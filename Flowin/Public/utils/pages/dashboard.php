<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../../../Includes/header.php';

// Obter dados do dashboard com filtros multi-tenancy
$empresa_id = $_SESSION['empresa_id'] ?? null;

// KPIs - Faturação (últimos 30 dias)
$stmt = $pdo->prepare("SELECT COALESCE(SUM(total), 0) as total FROM invoices WHERE company_id = ? AND status IN ('emitida', 'paga', 'parcial') AND invoice_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$stmt->execute([$empresa_id]);
$faturacao = $stmt->fetch()['total'];

// KPIs - Despesas (últimos 30 dias)
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE company_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stmt->execute([$empresa_id]);
$despesas = $stmt->fetch()['total'];

// KPIs - Lucro Líquido
$lucro = $faturacao - $despesas;

// KPIs - Clientes Novos (últimos 30 dias)
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM clients WHERE company_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stmt->execute([$empresa_id]);
$clientes_novos = $stmt->fetch()['total'];

// Vendas Recentes
$stmt = $pdo->prepare("SELECT i.id, i.invoice_number, i.invoice_date, i.total, i.status, c.name AS client_name 
                       FROM invoices i 
                       INNER JOIN clients c ON i.client_id = c.id 
                       WHERE i.company_id = ? 
                       ORDER BY i.invoice_date DESC 
                       LIMIT 5");
$stmt->execute([$empresa_id]);
$vendas_recentes = $stmt->fetchAll();

// Alertas de Stock (usando view_low_stock conforme orientações)
$stmt = $pdo->prepare("SELECT * FROM view_low_stock WHERE company_id = ? LIMIT 5");
$stmt->execute([$empresa_id]);
$stock_baixo = $stmt->fetchAll();
?>

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
                <p class="text-xl lg:text-2xl font-bold text-white">Kz <?php echo number_format($faturacao, 2, ',', '.'); ?></p>
            </div>
        </div>
        <div class="bg-slate-800 rounded-lg p-5 flex items-center">
            <div class="bg-red-500/20 text-red-400 rounded-full p-3 flex-shrink-0">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75v6.75m0 0l-3-3m3 3l3-3m-8.25 6a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" /></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-slate-400">Despesas</p>
                <p class="text-xl lg:text-2xl font-bold text-white">Kz <?php echo number_format($despesas, 2, ',', '.'); ?></p>
            </div>
        </div>
        <div class="bg-slate-800 rounded-lg p-5 flex items-center">
            <div class="bg-green-500/20 text-green-400 rounded-full p-3 flex-shrink-0">
               <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m3-3-3 3-3-3m6-9a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-slate-400">Lucro Líquido</p>
                <p class="text-xl lg:text-2xl font-bold text-white">Kz <?php echo number_format($lucro, 2, ',', '.'); ?></p>
            </div>
        </div>
        <div class="bg-slate-800 rounded-lg p-5 flex items-center">
            <div class="bg-sky-500/20 text-sky-400 rounded-full p-3 flex-shrink-0">
               <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-slate-400">Clientes Novos</p>
                <p class="text-xl lg:text-2xl font-bold text-white"><?php echo $clientes_novos; ?></p>
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
                <a href="vendas.php" class="bg-green-500 text-white rounded-lg p-4 flex flex-col items-center justify-center hover:bg-green-600 transition-colors">
                    <svg class="h-6 sm:h-8 w-6 sm:w-8 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    <span class="text-xs sm:text-sm font-semibold">Nova Venda</span>
                </a>
                 <a href="clients.php" class="bg-blue-500 text-white rounded-lg p-4 flex flex-col items-center justify-center hover:bg-blue-600 transition-colors">
                    <svg class="h-6 sm:h-8 w-6 sm:w-8 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>
                    <span class="text-xs sm:text-sm font-semibold">Novo Cliente</span>
                </a>
                 <a href="produtos.php" class="bg-purple-500 text-white rounded-lg p-4 flex flex-col items-center justify-center hover:bg-purple-600 transition-colors">
                    <svg class="h-6 sm:h-8 w-6 sm:w-8 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                    <span class="text-xs sm:text-sm font-semibold">Novo Produto</span>
                </a>
                 <a href="despesa.php" class="bg-red-500 text-white rounded-lg p-4 flex flex-col items-center justify-center hover:bg-red-600 transition-colors">
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
            <?php if (count($vendas_recentes) > 0): ?>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-400 border-b border-slate-700">
                            <th class="pb-2">Nº Fatura</th>
                            <th class="pb-2">Cliente</th>
                            <th class="pb-2">Data</th>
                            <th class="pb-2 text-right">Total</th>
                            <th class="pb-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendas_recentes as $venda): ?>
                        <tr class="border-b border-slate-700/50">
                            <td class="py-2 text-white"><?php echo htmlspecialchars($venda['invoice_number']); ?></td>
                            <td class="py-2"><?php echo htmlspecialchars($venda['client_name']); ?></td>
                            <td class="py-2 text-slate-400"><?php echo date('d/m/Y', strtotime($venda['invoice_date'])); ?></td>
                            <td class="py-2 text-right text-white">Kz <?php echo number_format($venda['total'], 2, ',', '.'); ?></td>
                            <td class="py-2 text-center">
                                <span class="px-2 py-1 rounded text-xs <?php echo $venda['status'] == 'paga' ? 'bg-green-500/20 text-green-400' : ($venda['status'] == 'pendente' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-slate-500/20 text-slate-400'); ?>">
                                    <?php echo htmlspecialchars($venda['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="mt-4 text-center text-slate-500 py-8">
                Nenhuma venda recente encontrada
            </div>
            <?php endif; ?>
        </div>
        <div class="bg-slate-800 rounded-lg p-6 flex flex-col">
            <h3 class="text-lg font-semibold text-white">Alertas de Stock</h3>
            <?php if (count($stock_baixo) > 0): ?>
            <div class="mt-4 overflow-y-auto max-h-48">
                <?php foreach ($stock_baixo as $produto): ?>
                <div class="flex items-center justify-between py-2 border-b border-slate-700/50">
                    <div>
                        <p class="text-sm text-white"><?php echo htmlspecialchars($produto['name']); ?></p>
                        <p class="text-xs text-slate-400">Stock: <?php echo $produto['current_stock']; ?> | Mín: <?php echo $produto['min_stock']; ?></p>
                    </div>
                    <span class="text-xs bg-red-500/20 text-red-400 px-2 py-1 rounded">Faltam <?php echo $produto['faltante']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="flex-grow flex flex-col items-center justify-center text-center text-slate-500 py-8">
                <svg class="h-10 w-10 text-green-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                <p>Todos os produtos com stock adequado!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../../Includes/footer.php'; ?>
