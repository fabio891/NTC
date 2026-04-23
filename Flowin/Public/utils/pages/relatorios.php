<?php
$pageTitle = 'Relatórios e Análises';
require_once __DIR__ . '/../../../Includes/header.php';

// Filtros de data
$data_inicial = $_GET['data_inicial'] ?? date('Y-m-01');
$data_final = $_GET['data_final'] ?? date('Y-m-t');

// Prepared statements para todos os KPIs
$kpi_params = [$_SESSION['empresa_id'], $data_inicial, $data_final];

// 1. Faturamento Total (SUM das vendas)
$stmt_faturamento = $pdo->prepare("
    SELECT COALESCE(SUM(total_amount), 0) AS total
    FROM sales
    WHERE company_id = ?
    AND sale_date BETWEEN ? AND ?
    AND status = 'completed'
");
$stmt_faturamento->execute($kpi_params);
$faturamento = $stmt_faturamento->fetch()['total'] ?? 0;

// 2. Despesas Totais
$stmt_despesas = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0) AS total
    FROM expenses
    WHERE company_id = ?
    AND paid_date BETWEEN ? AND ?
    AND status = 'paid'
");
$stmt_despesas->execute($kpi_params);
$despesas = $stmt_despesas->fetch()['total'] ?? 0;

// 3. Lucro Líquido (Faturamento - Despesas)
$lucro_liquido = $faturamento - $despesas;

// 4. Impostos (IVA das vendas)
$stmt_impostos = $pdo->prepare("
    SELECT COALESCE(SUM(iva_amount), 0) AS total
    FROM sales
    WHERE company_id = ?
    AND sale_date BETWEEN ? AND ?
    AND status = 'completed'
");
$stmt_impostos->execute($kpi_params);
$impostos = $stmt_impostos->fetch()['total'] ?? 0;

// 5. Vendas Realizadas (COUNT)
$stmt_vendas_count = $pdo->prepare("
    SELECT COUNT(*) AS total
    FROM sales
    WHERE company_id = ?
    AND sale_date BETWEEN ? AND ?
    AND status = 'completed'
");
$stmt_vendas_count->execute($kpi_params);
$vendas_count = $stmt_vendas_count->fetch()['total'] ?? 0;

// 6. Top Produtos Mais Vendidos (GROUP BY com JOIN)
$stmt_top_produtos = $pdo->prepare("
    SELECT 
        p.name,
        p.code,
        SUM(ii.quantity) AS total_qty,
        SUM(ii.total_amount) AS total_revenue
    FROM invoice_items ii
    INNER JOIN products p ON ii.product_id = p.id
    INNER JOIN invoices i ON ii.invoice_id = i.id
    WHERE p.company_id = ?
    AND i.issue_date BETWEEN ? AND ?
    GROUP BY p.id, p.name, p.code
    ORDER BY total_qty DESC
    LIMIT 10
");
$stmt_top_produtos->execute($kpi_params);
$top_produtos = $stmt_top_produtos->fetchAll();

// 7. Evolução Mensal (GROUP BY mês)
$stmt_evolucao = $pdo->prepare("
    SELECT 
        DATE_FORMAT(sale_date, '%Y-%m') AS mes,
        SUM(total_amount) AS faturamento,
        SUM(total_cost_of_goods) AS custos,
        SUM(gross_profit) AS lucro
    FROM sales
    WHERE company_id = ?
    AND sale_date BETWEEN ? AND ?
    AND status = 'completed'
    GROUP BY DATE_FORMAT(sale_date, '%Y-%m')
    ORDER BY mes ASC
");
$stmt_evolucao->execute($kpi_params);
$evolucao_mensal = $stmt_evolucao->fetchAll();

// 8. Despesas por Categoria (GROUP BY category)
$stmt_despesas_cat = $pdo->prepare("
    SELECT 
        category,
        SUM(amount) AS total,
        COUNT(*) AS count
    FROM expenses
    WHERE company_id = ?
    AND paid_date BETWEEN ? AND ?
    AND status = 'paid'
    GROUP BY category
    ORDER BY total DESC
");
$stmt_despesas_cat->execute($kpi_params);
$despesas_categoria = $stmt_despesas_cat->fetchAll();

// Mapeamento de categorias para exibição
$categorias_map = [
    'rent' => 'Renda',
    'utilities' => 'Utilidades',
    'salaries' => 'Salários',
    'marketing' => 'Marketing',
    'supplies' => 'Suprimentos',
    'maintenance' => 'Manutenção',
    'other' => 'Outros'
];
?>

<div class="p-4 sm:p-6 md:p-8">
    <!-- Cabeçalho com Filtros -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Relatórios e Análises</h1>
            <p class="text-slate-400 mt-1">Insights detalhados sobre a performance do seu negócio</p>
        </div>
        <form method="GET" action="" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-sm text-slate-400">De:</label>
                <input type="date" name="data_inicial" value="<?= htmlspecialchars($data_inicial) ?>" 
                       class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-slate-400">Até:</label>
                <input type="date" name="data_final" value="<?= htmlspecialchars($data_final) ?>" 
                       class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <button type="submit" class="flex items-center bg-orange-500 hover:bg-orange-600 text-white rounded-lg px-4 py-2 text-sm font-semibold transition-colors">
                <svg class="h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                Filtrar
            </button>
            <button type="button" onclick="window.location.href='relatorios.php'" class="flex items-center bg-slate-700 hover:bg-slate-600 text-white rounded-lg px-4 py-2 text-sm transition-colors">
                Limpar
            </button>
        </form>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mt-8">
        <div class="bg-slate-800 rounded-lg p-5 flex items-center border border-slate-700">
            <div class="bg-green-500/20 text-green-400 rounded-full p-3 flex-shrink-0">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-slate-400">Faturamento</p>
                <p class="text-xl lg:text-2xl font-bold text-white"><?= number_format($faturamento, 2, ',', '.') ?> Kz</p>
            </div>
        </div>
        
        <div class="bg-slate-800 rounded-lg p-5 flex items-center border border-slate-700">
            <div class="bg-red-500/20 text-red-400 rounded-full p-3 flex-shrink-0">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3m0 0l3-3m-3 3v-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-slate-400">Despesas</p>
                <p class="text-xl lg:text-2xl font-bold text-white"><?= number_format($despesas, 2, ',', '.') ?> Kz</p>
            </div>
        </div>
        
        <div class="bg-slate-800 rounded-lg p-5 flex items-center border border-slate-700">
            <div class="bg-emerald-500/20 text-emerald-400 rounded-full p-3 flex-shrink-0">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-slate-400">Lucro Líquido</p>
                <p class="text-xl lg:text-2xl font-bold text-white <?= $lucro_liquido >= 0 ? 'text-emerald-400' : 'text-red-400' ?>">
                    <?= number_format($lucro_liquido, 2, ',', '.') ?> Kz
                </p>
            </div>
        </div>
        
        <div class="bg-slate-800 rounded-lg p-5 flex items-center border border-slate-700">
            <div class="bg-yellow-500/20 text-yellow-400 rounded-full p-3 flex-shrink-0">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-slate-400">Impostos (IVA)</p>
                <p class="text-xl lg:text-2xl font-bold text-white"><?= number_format($impostos, 2, ',', '.') ?> Kz</p>
            </div>
        </div>
        
        <div class="bg-slate-800 rounded-lg p-5 flex items-center border border-slate-700">
            <div class="bg-purple-500/20 text-purple-400 rounded-full p-3 flex-shrink-0">
                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm text-slate-400">Vendas</p>
                <p class="text-xl lg:text-2xl font-bold text-white"><?= $vendas_count ?></p>
            </div>
        </div>
    </div>

    <!-- Gráficos e Tabelas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- Evolução Mensal -->
        <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <svg class="h-5 w-5 mr-2 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                </svg>
                Evolução Mensal
            </h3>
            <div class="mt-4 overflow-x-auto">
                <?php if (count($evolucao_mensal) > 0): ?>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-700 text-slate-400">
                            <th class="text-left py-2">Mês</th>
                            <th class="text-right">Faturamento</th>
                            <th class="text-right">Custos</th>
                            <th class="text-right">Lucro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($evolucao_mensal as $mes): ?>
                        <tr class="border-b border-slate-700/50">
                            <td class="py-3 text-white font-medium"><?= htmlspecialchars($mes['mes']) ?></td>
                            <td class="text-right text-green-400"><?= number_format($mes['faturamento'], 2, ',', '.') ?> Kz</td>
                            <td class="text-right text-red-400"><?= number_format($mes['custos'], 2, ',', '.') ?> Kz</td>
                            <td class="text-right <?= $mes['lucro'] >= 0 ? 'text-emerald-400' : 'text-red-400' ?>">
                                <?= number_format($mes['lucro'], 2, ',', '.') ?> Kz
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="text-center py-12 text-slate-500">
                    <p>Sem dados no período selecionado</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Despesas por Categoria -->
        <div class="bg-slate-800 rounded-lg p-6 border border-slate-700">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <svg class="h-5 w-5 mr-2 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 100 15 7.5 7.5 0 000-15zM21 21l-5.197-5.197" />
                </svg>
                Despesas por Categoria
            </h3>
            <div class="mt-4 space-y-3">
                <?php if (count($despesas_categoria) > 0): ?>
                    <?php 
                    $total_despesas_cat = array_sum(array_column($despesas_categoria, 'total'));
                    ?>
                    <?php foreach ($despesas_categoria as $cat): ?>
                        <?php 
                        $percentual = $total_despesas_cat > 0 ? ($cat['total'] / $total_despesas_cat) * 100 : 0;
                        ?>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-slate-300"><?= htmlspecialchars($categorias_map[$cat['category']] ?? $cat['category']) ?></span>
                                <span class="text-white font-medium"><?= number_format($cat['total'], 2, ',', '.') ?> Kz</span>
                            </div>
                            <div class="w-full bg-slate-700 rounded-full h-2">
                                <div class="bg-gradient-to-r from-red-500 to-orange-500 h-2 rounded-full" style="width: <?= $percentual ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="text-center py-12 text-slate-500">
                    <p>Sem despesas registradas no período</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Top Produtos Mais Vendidos -->
    <div class="mt-6 bg-slate-800 rounded-lg border border-slate-700">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center">
            <svg class="h-6 w-6 text-orange-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.388 16.234l-3.415-1.63M8.612 16.234l3.415-1.63M12 21.75c-2.496 0-4.82-1.02-6.521-2.721a10.455 10.455 0 010-14.758A10.455 10.455 0 0112 2.25c2.496 0 4.82 1.02 6.521 2.721a10.455 10.455 0 010 14.758A10.455 10.455 0 0112 21.75z" />
            </svg>
            <h3 class="text-lg font-semibold text-white">Top Produtos Mais Vendidos</h3>
        </div>
        <div class="overflow-x-auto">
            <?php if (count($top_produtos) > 0): ?>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400">
                        <th class="text-left py-3 px-4">#</th>
                        <th class="text-left py-3 px-4">Produto</th>
                        <th class="text-left py-3 px-4">Código</th>
                        <th class="text-right py-3 px-4">Qtd. Vendida</th>
                        <th class="text-right py-3 px-4">Receita Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $contador = 1; ?>
                    <?php foreach ($top_produtos as $produto): ?>
                    <tr class="border-b border-slate-700/50 hover:bg-slate-700/30">
                        <td class="py-3 px-4 text-slate-400"><?= $contador++ ?></td>
                        <td class="py-3 px-4 text-white font-medium"><?= htmlspecialchars($produto['name']) ?></td>
                        <td class="py-3 px-4 text-slate-400"><?= htmlspecialchars($produto['code'] ?? '-') ?></td>
                        <td class="py-3 px-4 text-right text-white"><?= number_format($produto['total_qty'], 0, ',', '.') ?></td>
                        <td class="py-3 px-4 text-right text-green-400 font-semibold"><?= number_format($produto['total_revenue'], 2, ',', '.') ?> Kz</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="text-center py-12 text-slate-500">
                <p>Nenhum produto vendido no período selecionado</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../../Includes/footer.php'; ?>