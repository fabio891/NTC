<?php
// faturas.php - Gestão de Documentos Fiscais (FlowIn)
// ✅ Padrão: header.php + footer.php
// ✅ Estilização: Tailwind CSS conforme faturas.html
// ✅ Lógica: PHP/JS funcional com propriedades uniformizadas

session_start();

if (!isset($_SESSION['empresa_id']) && !isset($_SESSION['company_id'])) {
    header('Location: Regist.php');
    exit;
}

$empresa_id = $_SESSION['empresa_id'] ?? $_SESSION['company_id'];
$pageTitle = 'Faturas';

require_once __DIR__ . '/../../../Config/database.php';
require_once __DIR__ . '/../../../Includes/functions.php';

$success_message = null;
$error_message = null;

// Processar ações do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        $pdo->beginTransaction();

        $tipo = $_POST['tipo'];
        $client_id = (int)$_POST['client_id'];
        $issue_date = $_POST['issue_date'];
        $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        $notes = $_POST['notes'] ?? '';
        $status = $_POST['status'] ?? 'draft';
        $created_by = $_SESSION['user_id'];

        $items = $_POST['items'] ?? [];
        $subtotal = 0;
        $discount_amount = 0;
        $iva_amount = 0;

        foreach ($items as $item) {
            $subtotal += (float)($item['subtotal'] ?? 0);
            $iva_amount += (float)($item['iva'] ?? 0);
        }

        $discount_amount = (float)($_POST['discount_amount'] ?? 0);
        $total_amount = $subtotal - $discount_amount + $iva_amount;

        if ($action === 'create') {
            $series_year = date('Y');
            $stmt = $pdo->prepare("SELECT next_invoice_number FROM companies WHERE id = ?");
            $stmt->execute([$empresa_id]);
            $company = $stmt->fetch();
            $next_number = $company['next_invoice_number'] ?? 1;
            $invoice_number = "{$tipo} {$series_year}/" . str_pad($next_number, 4, '0', STR_PAD_LEFT);

            $sql = "INSERT INTO invoices (company_id, client_id, type, invoice_number, series_year, issue_date, due_date, subtotal, discount_amount, iva_amount, total_amount, status, notes, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$empresa_id, $client_id, $tipo, $invoice_number, $series_year, $issue_date, $due_date, $subtotal, $discount_amount, $iva_amount, $total_amount, $status, $notes, $created_by]);

            $invoice_id = $pdo->lastInsertId();
            $pdo->prepare("UPDATE companies SET next_invoice_number = next_invoice_number + 1 WHERE id = ?")->execute([$empresa_id]);

        } elseif ($action === 'update') {
            $invoice_id = (int)$_POST['invoice_id'];
            $sql = "UPDATE invoices SET client_id = ?, type = ?, issue_date = ?, due_date = ?, 
                    subtotal = ?, discount_amount = ?, iva_amount = ?, total_amount = ?, status = ?, notes = ?
                    WHERE id = ? AND company_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$client_id, $tipo, $issue_date, $due_date, $subtotal, $discount_amount, $iva_amount, $total_amount, $status, $notes, $invoice_id, $empresa_id]);
            $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$invoice_id]);
            $invoice_id = $invoice_id;

        } elseif ($action === 'delete') {
            $invoice_id = (int)$_POST['invoice_id'];
            $pdo->prepare("DELETE FROM invoices WHERE id = ? AND company_id = ?")->execute([$invoice_id, $empresa_id]);
            $pdo->commit();
            header("Location: faturas.php?success=deleted");
            exit;
        }

        if ($action !== 'delete') {
            foreach ($items as $item) {
                $sql_item = "INSERT INTO invoice_items (invoice_id, product_id, description, quantity, unit_price, unit_cost, iva_rate, subtotal, iva, total_amount)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_item = $pdo->prepare($sql_item);
                $stmt_item->execute([
                    $invoice_id,
                    $item['product_id'] ?? null,
                    $item['description'] ?? '',
                    (float)($item['quantity'] ?? 1),
                    (float)($item['unit_price'] ?? 0),
                    (float)($item['unit_cost'] ?? 0),
                    (float)($item['iva_rate'] ?? 14),
                    (float)($item['subtotal'] ?? 0),
                    (float)($item['iva'] ?? 0),
                    (float)($item['total_amount'] ?? 0)
                ]);
            }
        }

        $pdo->commit();
        $success_message = $action === 'create' ? 'Documento criado com sucesso!' : 'Documento atualizado com sucesso!';
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log("Erro em faturas.php: " . $e->getMessage());
        $error_message = 'Erro ao processar documento.';
    }
}

// Consulta de faturas
$stmt = $pdo->prepare("
    SELECT i.id, i.type, i.invoice_number AS number, i.issue_date AS date,
           i.due_date AS dueDate, i.client_id, c.name AS clientName,
           i.status, i.notes, i.subtotal, i.discount_amount,
           i.iva_amount AS iva, i.total_amount AS total
    FROM invoices i
    LEFT JOIN clients c ON i.client_id = c.id
    WHERE i.company_id = ?
    ORDER BY i.issue_date DESC, i.invoice_number DESC
");
$stmt->execute([$empresa_id]);
$faturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt_clients = $pdo->prepare("SELECT id, name, document FROM clients WHERE company_id = ? AND status = 'active' ORDER BY name");
$stmt_clients->execute([$empresa_id]);
$clientes = $stmt_clients->fetchAll(PDO::FETCH_ASSOC);

$stmt_products = $pdo->prepare("SELECT id, name, price, cost, iva_rate FROM products WHERE company_id = ? AND status = 'active' ORDER BY name");
$stmt_products->execute([$empresa_id]);
$produtos = $stmt_products->fetchAll(PDO::FETCH_ASSOC);

$toast_messages = [];
if ($success_message) $toast_messages[] = ['type' => 'success', 'message' => $success_message];
if ($error_message) $toast_messages[] = ['type' => 'error', 'message' => $error_message];
?>

<?php require_once __DIR__ . '/../../../Includes/header.php'; ?>

<!-- Conteúdo Principal -->
<div class="p-4 md:p-8">
    
    <!-- Header da Página -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white"><i class="fas fa-file-invoice me-2"></i>Gestão de Documentos Fiscais</h1>
            <p class="text-slate-400 mt-1">Faturas, Pró-formas, Recibos e Notas</p>
        </div>
        <button class="flex items-center justify-center gap-2 px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-orange-500/25" 
                data-bs-toggle="modal" data-bs-target="#documentModal" onclick="openModal('create')">
            <i class="fas fa-plus"></i> Novo Documento
        </button>
    </div>

    <!-- Filtros e Pesquisa -->
    <div class="bg-slate-800 rounded-xl p-6 mb-8 border border-slate-700">
        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Abas de Tipo -->
            <div class="flex gap-2 overflow-x-auto pb-2 lg:pb-0">
                <button class="filter-btn active px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-orange-500 text-white" data-filter="all">Todos</button>
                <button class="filter-btn px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-slate-700 text-slate-300 hover:bg-slate-600" data-filter="FT">Faturas</button>
                <button class="filter-btn px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-slate-700 text-slate-300 hover:bg-slate-600" data-filter="PF">Pró-formas</button>
                <button class="filter-btn px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-slate-700 text-slate-300 hover:bg-slate-600" data-filter="RE">Recibos</button>
                <button class="filter-btn px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-slate-700 text-slate-300 hover:bg-slate-600" data-filter="NC">Notas Crédito</button>
                <button class="filter-btn px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-slate-700 text-slate-300 hover:bg-slate-600" data-filter="ND">Notas Débito</button>
            </div>
            
            <!-- Barra de Pesquisa -->
            <div class="flex-1">
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="searchInput" placeholder="Pesquisar por número, cliente..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500"
                           onkeyup="searchDocuments()">
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Documentos (Cards) -->
    <div id="documentsList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($faturas)): ?>
        <div class="col-span-full">
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-8 text-center">
                <i class="fas fa-file-invoice text-4xl text-slate-500 mb-4"></i>
                <h3 class="text-lg font-semibold text-white mb-2">Nenhum documento encontrado</h3>
                <p class="text-slate-400 mb-4">Crie o seu primeiro documento fiscal para começar.</p>
                <button class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-medium transition-all" 
                        data-bs-toggle="modal" data-bs-target="#documentModal" onclick="openModal('create')">
                    <i class="fas fa-plus me-2"></i>Criar Documento
                </button>
            </div>
        </div>
        <?php else: ?>
            <?php foreach ($faturas as $f): ?>
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 hover:border-orange-500/50 transition-all group document-card" 
                 data-type="<?= htmlspecialchars($f['type']) ?>"
                 data-number="<?= htmlspecialchars($f['number']) ?>"
                 data-client="<?= htmlspecialchars($f['clientName']) ?>">
                
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-white"><?= htmlspecialchars($f['number']) ?></h3>
                        <p class="text-slate-400 text-sm"><?= htmlspecialchars($f['clientName'] ?? 'Cliente Desconhecido') ?></p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= getBadgeClass($f['type']) ?>">
                        <?= htmlspecialchars($f['type']) ?>
                    </span>
                </div>
                
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Data:</span>
                        <span class="text-slate-200"><?= date('d/m/Y', strtotime($f['date'])) ?></span>
                    </div>
                    <?php if ($f['dueDate']): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Vencimento:</span>
                        <span class="text-slate-200"><?= date('d/m/Y', strtotime($f['dueDate'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Valor:</span>
                        <span class="text-orange-500 font-semibold"><?= number_format($f['total'], 2, ',', '.') ?> Kz</span>
                    </div>
                    <?php if ($f['status']): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Estado:</span>
                        <span class="<?= getStatusColor($f['status']) ?>"><?= translateStatus($f['status']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="flex gap-2 pt-4 border-t border-slate-700">
                    <button class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg text-sm transition-all" 
                            onclick="openModal('edit', <?= json_encode($f, JSON_HEX_APOS) ?>)" title="Editar">
                        <i class="fas fa-edit w-4 h-4"></i>
                    </button>
                    <button class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-500 rounded-lg text-sm transition-all" 
                            onclick="confirmDelete(<?= $f['id'] ?>)" title="Eliminar">
                        <i class="fas fa-trash w-4 h-4"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Documento - Estilizado conforme faturas.html -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content bg-slate-800 border border-slate-700 rounded-2xl">
            <form id="documentForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="invoice_id" id="invoiceId">
                
                <!-- Modal Header -->
                <div class="modal-header border-slate-700 pb-4">
                    <div>
                        <h5 class="modal-title text-xl font-bold text-white" id="modalTitle">Novo Documento</h5>
                        <p class="text-slate-400 text-sm mt-1">Preencha as informações do documento fiscal</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <!-- Modal Body -->
                <div class="modal-body space-y-6">
                    
                    <!-- Dados Principais -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Tipo de Documento *</label>
                            <select class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500" 
                                    name="tipo" id="docType" required onchange="onDocTypeChange()">
                                <option value="">Selecione...</option>
                                <option value="FT">Fatura (FT)</option>
                                <option value="PF">Pró-forma (PF)</option>
                                <option value="RE">Recibo (RE)</option>
                                <option value="NC">Nota de Crédito (NC)</option>
                                <option value="ND">Nota de Débito (ND)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Número do Documento</label>
                            <input type="text" class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500" 
                                   name="invoice_number" id="docNumber" readonly>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Data do Documento *</label>
                            <input type="date" class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500" 
                                   name="issue_date" id="issueDate" required>
                        </div>
                        <div id="dueDateField">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Data de Vencimento *</label>
                            <input type="date" class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500" 
                                   name="due_date" id="dueDate">
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Cliente *</label>
                            <select class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500" 
                                    name="client_id" id="clientId" required>
                                <option value="">Selecione um cliente...</option>
                                <?php foreach ($clientes as $c): ?>
                                <option value="<?= $c['id'] ?>" class="text-white"><?= htmlspecialchars($c['name']) ?> <?= $c['document'] ? '('.$c['document'].')' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="sourceDocField" class="hidden">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Documento de Origem *</label>
                            <select class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500" 
                                    id="sourceDoc">
                                <option value="">Selecione...</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tabela de Itens -->
                    <div class="border border-slate-700 rounded-xl overflow-hidden">
                        <div class="bg-slate-700/50 px-4 py-3 border-b border-slate-700">
                            <h3 class="font-semibold text-white">Itens do Documento</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-slate-700/30">
                                    <tr class="text-xs text-slate-400 uppercase">
                                        <th class="px-4 py-3 text-left">Produto/Serviço</th>
                                        <th class="px-4 py-3 text-left w-48">Descrição</th>
                                        <th class="px-4 py-3 text-center w-20">Qtd</th>
                                        <th class="px-4 py-3 text-right w-28">Preço Unit.</th>
                                        <th class="px-4 py-3 text-right w-28">Custo Unit.</th>
                                        <th class="px-4 py-3 text-center w-20">IVA %</th>
                                        <th class="px-4 py-3 text-right w-28">Total</th>
                                        <th class="px-4 py-3 text-center w-12"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody" class="divide-y divide-slate-700">
                                    <!-- Itens inseridos via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-slate-700">
                            <button type="button" onclick="addItem()" class="flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg text-sm font-medium transition-all">
                                <i class="fas fa-plus w-4 h-4"></i> Adicionar Item
                            </button>
                        </div>
                    </div>

                    <!-- Totais e Informações Adicionais -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Método de Pagamento</label>
                                <select class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500" 
                                        name="payment_method" id="paymentMethod">
                                    <option value="">Selecione...</option>
                                    <option value="TPA">TPA</option>
                                    <option value="Transferência">Transferência Bancária</option>
                                    <option value="MCX Express">MCX Express</option>
                                    <option value="Cash">Numerário</option>
                                    <option value="Misto">Misto</option>
                                </select>
                            </div>
                            <div id="statusField">
                                <label class="block text-sm font-medium text-slate-300 mb-2">Estado</label>
                                <select class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500" 
                                        name="status" id="status">
                                    <option value="draft">Rascunho</option>
                                    <option value="issued">Emitido</option>
                                    <option value="paid">Pago</option>
                                    <option value="partial">Parcial</option>
                                    <option value="overdue">Em Atraso</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-2">Observações</label>
                                <textarea class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 resize-none" 
                                          name="notes" id="notes" rows="3" placeholder="Informações adicionais..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Card de Totais -->
                        <div class="bg-slate-700/30 rounded-xl p-6 space-y-3">
                            <div class="flex justify-between text-slate-300">
                                <span>Subtotal:</span>
                                <span id="subtotalValue">0,00 Kz</span>
                            </div>
                            <div class="flex justify-between text-slate-300">
                                <span>Desconto:</span>
                                <div class="flex items-center gap-2">
                                    <input type="number" id="discountValue" name="discount_amount" value="0" min="0" step="0.01" oninput="calculateTotals()"
                                           class="w-24 px-2 py-1 bg-slate-600 border border-slate-500 rounded text-right text-sm text-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                                    <span class="text-xs text-slate-400">Kz</span>
                                </div>
                            </div>
                            <div class="flex justify-between text-slate-300">
                                <span>Base IVA:</span>
                                <span id="ivaBaseValue">0,00 Kz</span>
                            </div>
                            <div class="flex justify-between text-slate-300">
                                <span>IVA (14%):</span>
                                <span id="ivaValue">0,00 Kz</span>
                            </div>
                            <div class="border-t border-slate-600 pt-3 flex justify-between text-lg font-bold text-orange-500">
                                <span>Total Líquido:</span>
                                <span id="totalValue">0,00 Kz</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="modal-footer border-slate-700 pt-4 pb-6 px-6 flex items-center justify-end gap-4">
                    <button type="button" class="px-6 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-xl font-medium transition-all" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="px-6 py-2.5 bg-slate-600 hover:bg-slate-500 text-white rounded-xl font-medium transition-all" onclick="saveDraft()">
                        Guardar Rascunho
                    </button>
                    <button type="submit" class="flex items-center gap-2 px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-orange-500/25">
                        <i class="fas fa-paper-plane"></i> <span id="submitBtnText">Emitir Documento</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="fixed top-4 right-4 z-[1100] space-y-3"></div>

<?php require_once __DIR__ . '/../../../Includes/footer.php'; ?>

<!-- Scripts JavaScript -->
<script>
// ============================================
// DADOS INICIAIS
// ============================================
const productsData = <?= json_encode($produtos, JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const initialDocuments = <?= json_encode($faturas, JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const toastMessages = <?= json_encode($toast_messages) ?>;

let items = [];
let currentFilter = 'all';
let documents = [];
let editingIndex = null;

// ============================================
// FUNÇÕES DE UTILIDADE
// ============================================
function formatCurrency(value) {
    return parseFloat(value || 0).toLocaleString('pt-AO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' Kz';
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('pt-PT');
}

function getBadgeClass(type) {
    const classes = { 'FT': 'badge-ft', 'PF': 'badge-pf', 'RE': 'badge-re', 'NC': 'badge-nc', 'ND': 'badge-nd' };
    return classes[type] || 'badge-ft';
}

function getStatusColor(status) {
    const colors = { 'draft': 'text-yellow-500', 'issued': 'text-blue-500', 'paid': 'text-green-500', 'partial': 'text-orange-500', 'overdue': 'text-red-500', 'cancelled': 'text-slate-400' };
    return colors[status] || 'text-slate-400';
}

function translateStatus(status) {
    const map = { 'draft': 'Rascunho', 'issued': 'Emitido', 'paid': 'Pago', 'partial': 'Parcial', 'overdue': 'Em Atraso', 'cancelled': 'Cancelado' };
    return map[status] || status;
}

// ============================================
// GERENCIAMENTO DE ITENS
// ============================================
function addItem(product = null) {
    items.push({
        product_id: product?.id || '',
        description: product?.name || '',
        quantity: 1,
        unit_price: parseFloat(product?.price) || 0,
        unit_cost: parseFloat(product?.cost) || 0,
        iva_rate: parseFloat(product?.iva_rate) || 14,
        subtotal: 0, iva: 0, total_amount: 0
    });
    renderItems();
    calculateTotals();
}

function removeItem(index) {
    items.splice(index, 1);
    renderItems();
    calculateTotals();
}

function updateItem(index, field, value) {
    const item = items[index];
    
    if (field === 'product_id') {
        const product = productsData.find(p => p.id == value);
        if (product) {
            item.description = product.name;
            item.unit_price = parseFloat(product.price) || 0;
            item.unit_cost = parseFloat(product.cost) || 0;
            item.iva_rate = parseFloat(product.iva_rate) || 14;
        }
    }
    
    if (['quantity', 'unit_price', 'unit_cost', 'iva_rate'].includes(field)) {
        item[field] = parseFloat(value) || 0;
    } else {
        item[field] = value;
    }
    
    item.subtotal = item.quantity * item.unit_price;
    item.iva = item.subtotal * (item.iva_rate / 100);
    item.total_amount = item.subtotal + item.iva;
    
    renderItems();
    calculateTotals();
}

function renderItems() {
    const tbody = document.getElementById('itemsTableBody');
    
    if (items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Nenhum item adicionado. Clique em "Adicionar Item" para começar.</td></tr>`;
        return;
    }
    
    tbody.innerHTML = items.map((item, index) => `
        <tr class="hover:bg-slate-700/30 transition-colors">
            <td class="px-4 py-3">
                <select onchange="updateItem(${index}, 'product_id', this.value)" 
                        class="w-full px-2 py-1.5 bg-slate-600 border border-slate-500 rounded text-sm text-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                    <option value="">Selecione...</option>
                    ${productsData.map(p => `<option value="${p.id}" ${item.product_id == p.id ? 'selected' : ''} class="text-white">${p.name}</option>`).join('')}
                </select>
            </td>
            <td class="px-4 py-3">
                <input type="text" value="${item.description}" oninput="updateItem(${index}, 'description', this.value)"
                       class="w-full px-2 py-1.5 bg-slate-600 border border-slate-500 rounded text-sm text-white focus:outline-none focus:ring-1 focus:ring-orange-500" placeholder="Descrição...">
            </td>
            <td class="px-4 py-3">
                <input type="number" value="${item.quantity}" min="1" oninput="updateItem(${index}, 'quantity', this.value)"
                       class="w-full px-2 py-1.5 bg-slate-600 border border-slate-500 rounded text-sm text-white text-center focus:outline-none focus:ring-1 focus:ring-orange-500">
            </td>
            <td class="px-4 py-3">
                <input type="number" value="${item.unit_price}" min="0" step="0.01" oninput="updateItem(${index}, 'unit_price', this.value)"
                       class="w-full px-2 py-1.5 bg-slate-600 border border-slate-500 rounded text-sm text-white text-right focus:outline-none focus:ring-1 focus:ring-orange-500">
            </td>
            <td class="px-4 py-3">
                <input type="number" value="${item.unit_cost}" min="0" step="0.01" oninput="updateItem(${index}, 'unit_cost', this.value)"
                       class="w-full px-2 py-1.5 bg-slate-600 border border-slate-500 rounded text-sm text-white text-right focus:outline-none focus:ring-1 focus:ring-orange-500">
            </td>
            <td class="px-4 py-3">
                <input type="number" value="${item.iva_rate}" min="0" max="100" oninput="updateItem(${index}, 'iva_rate', this.value)"
                       class="w-full px-2 py-1.5 bg-slate-600 border border-slate-500 rounded text-sm text-white text-center focus:outline-none focus:ring-1 focus:ring-orange-500">
            </td>
            <td class="px-4 py-3 text-right text-sm font-medium text-white">${formatCurrency(item.total_amount)}</td>
            <td class="px-4 py-3 text-center">
                <button type="button" onclick="removeItem(${index})" class="p-1 hover:bg-red-500/20 rounded transition-colors">
                    <i class="fas fa-times w-4 h-4 text-red-500"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function calculateTotals() {
    const subtotal = items.reduce((sum, item) => sum + item.subtotal, 0);
    const discount = parseFloat(document.getElementById('discountValue')?.value) || 0;
    const ivaBase = Math.max(0, subtotal - discount);
    const ivaValue = ivaBase * 0.14;
    const total = ivaBase + ivaValue;
    
    document.getElementById('subtotalValue').textContent = formatCurrency(subtotal);
    document.getElementById('ivaBaseValue').textContent = formatCurrency(ivaBase);
    document.getElementById('ivaValue').textContent = formatCurrency(ivaValue);
    document.getElementById('totalValue').textContent = formatCurrency(total);
    
    return { subtotal, discount, ivaBase, iva: ivaValue, total };
}

// ============================================
// MAPEAMENTO DE DOCUMENTOS
// ============================================
function mapDocuments(rawData) {
    return rawData.map(f => ({
        id: f.id, type: f.type || 'FT',
        number: f.number || f.invoice_number,
        date: f.date || f.issue_date,
        dueDate: f.dueDate || f.due_date || null,
        clientId: f.client_id,
        clientName: f.clientName || f.client_name || 'Cliente Desconhecido',
        status: translateStatus(f.status),
        notes: f.notes || '',
        subtotal: parseFloat(f.subtotal) || 0,
        discount: parseFloat(f.discount_amount) || 0,
        iva: parseFloat(f.iva) || parseFloat(f.iva_amount) || 0,
        total: parseFloat(f.total) || parseFloat(f.total_amount) || 0,
        items: []
    }));
}

// ============================================
// RENDERIZAÇÃO E FILTROS
// ============================================
function renderDocuments(list) {
    const container = document.getElementById('documentsList');
    
    if (list.length === 0) {
        container.innerHTML = `<div class="col-span-full"><div class="bg-slate-800 rounded-xl border border-slate-700 p-8 text-center"><i class="fas fa-file-invoice text-4xl text-slate-500 mb-4"></i><h3 class="text-lg font-semibold text-white mb-2">Nenhum documento encontrado</h3><p class="text-slate-400">Tente ajustar os filtros ou a pesquisa.</p></div></div>`;
        return;
    }
    
    container.innerHTML = list.map(doc => `
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 hover:border-orange-500/50 transition-all group document-card" 
             data-type="${doc.type}" data-number="${doc.number}" data-client="${doc.clientName}">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-white">${doc.number}</h3>
                    <p class="text-slate-400 text-sm">${doc.clientName}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getBadgeClass(doc.type)}">${doc.type}</span>
            </div>
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm"><span class="text-slate-400">Data:</span><span class="text-slate-200">${formatDate(doc.date)}</span></div>
                ${doc.dueDate ? `<div class="flex justify-between text-sm"><span class="text-slate-400">Vencimento:</span><span class="text-slate-200">${formatDate(doc.dueDate)}</span></div>` : ''}
                <div class="flex justify-between text-sm"><span class="text-slate-400">Valor:</span><span class="text-orange-500 font-semibold">${formatCurrency(doc.total)}</span></div>
                ${doc.status ? `<div class="flex justify-between text-sm"><span class="text-slate-400">Estado:</span><span class="${getStatusColor(doc.status.toLowerCase())}">${doc.status}</span></div>` : ''}
            </div>
            <div class="flex gap-2 pt-4 border-t border-slate-700">
                <button class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg text-sm transition-all" onclick='openModal("edit", ${JSON.stringify(doc).replace(/'/g, "\\'")})'><i class="fas fa-edit w-4 h-4"></i></button>
                <button class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-500 rounded-lg text-sm transition-all" onclick="confirmDelete(${doc.id})"><i class="fas fa-trash w-4 h-4"></i></button>
            </div>
        </div>
    `).join('');
}

function searchDocuments() {
    const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
    let filtered = documents;
    
    if (currentFilter !== 'all') {
        filtered = filtered.filter(doc => doc.type === currentFilter);
    }
    
    if (searchTerm) {
        filtered = filtered.filter(doc => {
            return (doc.number?.toLowerCase().includes(searchTerm)) ||
                   (doc.clientName?.toLowerCase().includes(searchTerm)) ||
                   (doc.notes?.toLowerCase().includes(searchTerm));
        });
    }
    
    renderDocuments(filtered);
}

// ============================================
// MODAL E FORMULÁRIO
// ============================================
function onDocTypeChange() {
    const type = document.getElementById('docType')?.value;
    const dueDateField = document.getElementById('dueDateField');
    const statusField = document.getElementById('statusField');
    const sourceDocField = document.getElementById('sourceDocField');
    
    if (dueDateField) dueDateField.classList.toggle('hidden', !['FT', 'PF'].includes(type));
    if (statusField) statusField.classList.toggle('hidden', type !== 'FT');
    if (sourceDocField) sourceDocField.classList.toggle('hidden', !['NC', 'ND', 'RE'].includes(type));
    
    if (type) {
        const nextNum = getNextDocumentNumber(type);
        if (document.getElementById('docNumber')) document.getElementById('docNumber').value = nextNum;
    }
}

function getNextDocumentNumber(type) {
    const year = new Date().getFullYear();
    const docsOfType = documents.filter(d => d.type === type && d.number?.includes(year.toString()));
    const lastNum = docsOfType.length > 0 ? Math.max(...docsOfType.map(d => parseInt((d.number.split('/')[1] || '0').padStart(3, '0')))) : 0;
    return `${type}${year}/${String(lastNum + 1).padStart(3, '0')}`;
}

function openModal(mode, data = null) {
    editingIndex = mode === 'edit' ? data?.id : null;
    
    document.getElementById('formAction').value = mode;
    document.getElementById('modalTitle').textContent = mode === 'create' ? 'Novo Documento' : 'Editar Documento';
    
    document.getElementById('documentForm')?.reset();
    items = [];
    
    if (mode === 'edit' && data) {
        document.getElementById('invoiceId').value = data.id;
        document.getElementById('docType').value = data.type;
        document.getElementById('issueDate').value = data.date;
        document.getElementById('dueDate').value = data.dueDate || '';
        document.getElementById('clientId').value = data.clientId;
        document.getElementById('notes').value = data.notes || '';
        document.getElementById('status').value = data.status?.toLowerCase() || 'draft';
        document.getElementById('discountValue').value = data.discount || 0;
        
        if (data.items?.length > 0) {
            items = data.items.map(item => ({
                product_id: item.product_id, description: item.description,
                quantity: item.quantity, unit_price: item.unit_price,
                unit_cost: item.unit_cost, iva_rate: item.iva_rate,
                subtotal: item.subtotal, iva: item.iva, total_amount: item.total_amount
            }));
        }
    }
    
    onDocTypeChange();
    renderItems();
    calculateTotals();
    
    new bootstrap.Modal(document.getElementById('documentModal')).show();
}

function closeModal() {
    const modalEl = document.getElementById('documentModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
    editingIndex = null;
}

function confirmDelete(id) {
    if (confirm('Tem certeza que deseja eliminar este documento? Esta ação não pode ser desfeita.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="invoice_id" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
}

function saveDraft() {
    const formData = new FormData(document.getElementById('documentForm'));
    formData.set('action', 'create');
    
    items.forEach((item, index) => {
        formData.append(`items[${index}][product_id]`, item.product_id || '');
        formData.append(`items[${index}][description]`, item.description || '');
        formData.append(`items[${index}][quantity]`, item.quantity);
        formData.append(`items[${index}][unit_price]`, item.unit_price);
        formData.append(`items[${index}][unit_cost]`, item.unit_cost || 0);
        formData.append(`items[${index}][iva_rate]`, item.iva_rate);
        formData.append(`items[${index}][subtotal]`, item.subtotal);
        formData.append(`items[${index}][iva]`, item.iva);
        formData.append(`items[${index}][total_amount]`, item.total_amount);
    });
    
    fetch('faturas.php', { method: 'POST', body: formData })
        .then(() => { window.location.reload(); })
        .catch(error => { console.error('Erro:', error); showToast('error', 'Erro ao salvar rascunho.'); });
}

// ============================================
// ENVIO DO FORMULÁRIO
// ============================================
document.getElementById('documentForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    items.forEach((item, index) => {
        formData.append(`items[${index}][product_id]`, item.product_id || '');
        formData.append(`items[${index}][description]`, item.description || '');
        formData.append(`items[${index}][quantity]`, item.quantity);
        formData.append(`items[${index}][unit_price]`, item.unit_price);
        formData.append(`items[${index}][unit_cost]`, item.unit_cost || 0);
        formData.append(`items[${index}][iva_rate]`, item.iva_rate);
        formData.append(`items[${index}][subtotal]`, item.subtotal);
        formData.append(`items[${index}][iva]`, item.iva);
        formData.append(`items[${index}][total_amount]`, item.total_amount);
    });
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
    
    fetch('faturas.php', { method: 'POST', body: formData })
    .then(() => { window.location.reload(); })
    .catch(error => {
        console.error('Erro:', error);
        showToast('error', 'Erro ao salvar documento.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// ============================================
// TOAST NOTIFICATIONS
// ============================================
function showToast(type, message) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    
    const colors = { success: 'bg-green-500', error: 'bg-red-500', info: 'bg-blue-500', warning: 'bg-yellow-500' };
    const icons = { 
        success: '<i class="fas fa-check-circle w-5 h-5"></i>',
        error: '<i class="fas fa-times-circle w-5 h-5"></i>',
        info: '<i class="fas fa-info-circle w-5 h-5"></i>',
        warning: '<i class="fas fa-exclamation-triangle w-5 h-5"></i>'
    };
    
    toast.className = `toast flex items-center gap-3 px-4 py-3 ${colors[type] || 'bg-blue-500'} text-white rounded-xl shadow-lg min-w-[300px] transform translate-x-full opacity-0 transition-all duration-300`;
    toast.innerHTML = `${icons[type] || icons.info}<span class="flex-1">${message}</span>`;
    
    container.appendChild(toast);
    
    setTimeout(() => toast.classList.remove('translate-x-full', 'opacity-0'), 10);
    setTimeout(() => {
        toast.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ============================================
// INICIALIZAÇÃO
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Definir data atual
    if (document.getElementById('issueDate')) {
        document.getElementById('issueDate').value = new Date().toISOString().split('T')[0];
    }
    
    // Mapear documentos
    documents = mapDocuments(initialDocuments);
    renderDocuments(documents);
    
    // Filtros por tipo
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('bg-orange-500', 'text-white');
                b.classList.add('bg-slate-700', 'text-slate-300');
            });
            this.classList.remove('bg-slate-700', 'text-slate-300');
            this.classList.add('bg-orange-500', 'text-white');
            currentFilter = this.dataset.filter;
            searchDocuments();
        });
    });
    
    // Mostrar mensagens toast
    if (toastMessages?.length > 0) {
        toastMessages.forEach(msg => showToast(msg.type, msg.message));
    }
    
    // Calcular totais iniciais
    calculateTotals();
});
</script>

<!-- Estilos CSS para badges (conforme faturas.html) -->
<style>
.badge-ft { background-color: #dbeafe; color: #1e40af; }
.badge-pf { background-color: #f3e8ff; color: #6b21a8; }
.badge-re { background-color: #dcfce7; color: #166534; }
.badge-nc { background-color: #fef9c3; color: #854d0e; }
.badge-nd { background-color: #fee2e2; color: #991b1b; }

/* Scrollbar customizada */
::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: #1e293b; }
::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: #64748b; }

/* Animação do Toast */
.toast { animation: slideIn 0.3s ease forwards; }
@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>
