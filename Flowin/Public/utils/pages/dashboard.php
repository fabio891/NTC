<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../../../Includes/header.php';

// Verificar se o utilizador está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: Regist.php');
    exit;
}

$company_id = $_SESSION['company_id'] ?? null;

// Buscar dados reais do banco de dados
try {
    // Vendas hoje
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as total FROM sales WHERE company_id = ? AND DATE(sale_date) = CURDATE()");
    $stmt->execute([$company_id]);
    $vendasHoje = $stmt->fetch()['total'] ?? 0;

    // Faturas pendentes
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM invoices WHERE company_id = ? AND status IN ('issued', 'pending')");
    $stmt->execute([$company_id]);
    $faturasPendentes = $stmt->fetch()['count'] ?? 0;

    // Lucro do mês (simplificado)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(gross_profit), 0) - COALESCE((
            SELECT SUM(amount) FROM expenses 
            WHERE company_id = ? AND MONTH(paid_date) = MONTH(CURDATE()) AND YEAR(paid_date) = YEAR(CURDATE())
        ), 0) as lucro 
        FROM sales 
        WHERE company_id = ? AND MONTH(sale_date) = MONTH(CURDATE()) AND YEAR(sale_date) = YEAR(CURDATE())
    ");
    $stmt->execute([$company_id, $company_id]);
    $lucroMes = $stmt->fetch()['lucro'] ?? 0;

    // Clientes ativos
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM clients WHERE company_id = ? AND status = 'active'");
    $stmt->execute([$company_id]);
    $totalClientes = $stmt->fetch()['count'] ?? 0;

    // Vendas recentes
    $stmt = $pdo->prepare("
        SELECT s.id, c.name, s.sale_date, s.total_amount, s.status 
        FROM sales s 
        JOIN clients c ON s.client_id = c.id 
        WHERE s.company_id = ? 
        ORDER BY s.sale_date DESC 
        LIMIT 5
    ");
    $stmt->execute([$company_id]);
    $vendasRecentes = $stmt->fetchAll();

} catch (PDOException $e) {
    // Em caso de erro, usar valores padrão
    $vendasHoje = 0;
    $faturasPendentes = 0;
    $lucroMes = 0;
    $totalClientes = 0;
    $vendasRecentes = [];
}
?>

<!-- Cards de Estatísticas -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Vendas Hoje -->
    <div class="bg-slate-800 rounded-lg p-6 border border-slate-700 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-400 mb-1">Vendas Hoje</p>
                <h3 class="text-2xl font-bold text-white"><?php echo formatCurrency($vendasHoje); ?></h3>
            </div>
            <div class="p-3 bg-orange-500/20 rounded-full text-orange-500">
                <i class="fas fa-shopping-bag text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Faturas Pendentes -->
    <div class="bg-slate-800 rounded-lg p-6 border border-slate-700 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-400 mb-1">Faturas Pendentes</p>
                <h3 class="text-2xl font-bold text-white"><?php echo $faturasPendentes; ?></h3>
            </div>
            <div class="p-3 bg-yellow-500/20 rounded-full text-yellow-500">
                <i class="fas fa-file-invoice text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Lucro Líquido -->
    <div class="bg-slate-800 rounded-lg p-6 border border-slate-700 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-400 mb-1">Lucro Líquido (Mês)</p>
                <h3 class="text-2xl font-bold text-emerald-400"><?php echo formatCurrency($lucroMes); ?></h3>
            </div>
            <div class="p-3 bg-emerald-500/20 rounded-full text-emerald-500">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Clientes -->
    <div class="bg-slate-800 rounded-lg p-6 border border-slate-700 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-400 mb-1">Clientes Ativos</p>
                <h3 class="text-2xl font-bold text-white"><?php echo $totalClientes; ?></h3>
            </div>
            <div class="p-3 bg-blue-500/20 rounded-full text-blue-500">
                <i class="fas fa-users text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Tabela de Vendas Recentes -->
<div class="bg-slate-800 rounded-lg border border-slate-700 shadow-lg overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-white">Vendas Recentes</h3>
        <a href="vendas.php" class="text-sm text-orange-500 hover:underline">Ver todas</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-900/50 text-slate-400 text-sm uppercase">
                    <th class="px-6 py-3">ID</th>
                    <th class="px-6 py-3">Cliente</th>
                    <th class="px-6 py-3">Data</th>
                    <th class="px-6 py-3">Total</th>
                    <th class="px-6 py-3">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                <?php if (count($vendasRecentes) > 0): ?>
                    <?php foreach ($vendasRecentes as $venda): ?>
                    <tr class="hover:bg-slate-700/50 transition-colors">
                        <td class="px-6 py-4 text-white font-mono text-sm">#<?php echo $venda['id']; ?></td>
                        <td class="px-6 py-4 text-slate-300"><?php echo htmlspecialchars($venda['name']); ?></td>
                        <td class="px-6 py-4 text-slate-400"><?php echo formatDate($venda['sale_date']); ?></td>
                        <td class="px-6 py-4 text-white font-medium"><?php echo formatCurrency($venda['total_amount']); ?></td>
                        <td class="px-6 py-4"><?php echo getStatusBadge($venda['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                        Nenhuma venda registrada recentemente.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../../Includes/footer.php'; ?>
