<?php
$pageTitle = 'Documentos Fiscais';
require_once __DIR__ . '/../../../Includes/header.php';

// Obter empresa_id da sessão
$empresa_id = $_SESSION['empresa_id'];

// Processar formulário de submissão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    try {
        if ($action === 'create' || $action === 'update') {
            $pdo->beginTransaction();
            
            $tipo = $_POST['tipo'];
            $client_id = (int)$_POST['client_id'];
            $issue_date = $_POST['issue_date'];
            $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
            $notes = $_POST['notes'] ?? '';
            $status = $_POST['status'] ?? 'draft';
            $created_by = $_SESSION['user_id'];
            
            // Calcular totais dos itens
            $items = $_POST['items'] ?? [];
            $subtotal = 0;
            $discount_amount = 0;
            $iva_amount = 0;
            $total_amount = 0;
            
            foreach ($items as $item) {
                $subtotal += (float)$item['subtotal'];
                $iva_amount += (float)$item['iva'];
            }
            
            $discount_amount = (float)($_POST['discount_amount'] ?? 0);
            $total_amount = $subtotal - $discount_amount + $iva_amount;
            
            // Gerar número da fatura
            if ($action === 'create') {
                $series_year = date('Y');
                $stmt = $pdo->prepare("SELECT next_invoice_number FROM companies WHERE id = ?");
                $stmt->execute([$empresa_id]);
                $company = $stmt->fetch();
                $next_number = $company['next_invoice_number'];
                $invoice_number = "{$tipo} {$series_year}/" . str_pad($next_number, 4, '0', STR_PAD_LEFT);
                
                // Inserir fatura
                $sql = "INSERT INTO invoices (company_id, client_id, type, invoice_number, series_year, issue_date, due_date, subtotal, discount_amount, iva_amount, total_amount, status, notes, created_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$empresa_id, $client_id, $tipo, $invoice_number, $series_year, $issue_date, $due_date, $subtotal, $discount_amount, $iva_amount, $total_amount, $status, $notes, $created_by]);
                
                $invoice_id = $pdo->lastInsertId();
                
                // Atualizar próximo número da fatura
                $pdo->prepare("UPDATE companies SET next_invoice_number = next_invoice_number + 1 WHERE id = ?")->execute([$empresa_id]);
            } else {
                $invoice_id = (int)$_POST['invoice_id'];
                $sql = "UPDATE invoices SET client_id = ?, type = ?, issue_date = ?, due_date = ?, subtotal = ?, discount_amount = ?, iva_amount = ?, total_amount = ?, status = ?, notes = ? 
                        WHERE id = ? AND company_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$client_id, $tipo, $issue_date, $due_date, $subtotal, $discount_amount, $iva_amount, $total_amount, $status, $notes, $invoice_id, $empresa_id]);
                
                // Eliminar itens existentes
                $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$invoice_id]);
            }
            
            // Inserir itens
            foreach ($items as $item) {
                $sql_item = "INSERT INTO invoice_items (invoice_id, product_id, description, quantity, unit_price, unit_cost, iva_rate, total_amount) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_item = $pdo->prepare($sql_item);
                $stmt_item->execute([
                    $invoice_id,
                    $item['product_id'] ?? null,
                    $item['description'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['unit_cost'],
                    $item['iva_rate'],
                    $item['total_amount']
                ]);
            }
            
            $pdo->commit();
            $success_message = $action === 'create' ? 'Documento criado com sucesso!' : 'Documento atualizado com sucesso!';
        } elseif ($action === 'delete') {
            $invoice_id = (int)$_POST['invoice_id'];
            $pdo->prepare("DELETE FROM invoices WHERE id = ? AND company_id = ?")->execute([$invoice_id, $empresa_id]);
            $success_message = 'Documento eliminado com sucesso!';
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = 'Erro: ' . $e->getMessage();
    }
}

// Obter todas as faturas da empresa
$stmt = $pdo->prepare("
    SELECT i.*, c.name AS client_name, u.name AS created_by_name
    FROM invoices i
    LEFT JOIN clients c ON i.client_id = c.id
    LEFT JOIN users u ON i.created_by = u.id
    WHERE i.company_id = ?
    ORDER BY i.issue_date DESC, i.invoice_number DESC
");
$stmt->execute([$empresa_id]);
$faturas = $stmt->fetchAll();

// Obter clientes para o select
$stmt_clients = $pdo->prepare("SELECT id, name, document FROM clients WHERE company_id = ? AND status = 'active' ORDER BY name");
$stmt_clients->execute([$empresa_id]);
$clientes = $stmt_clients->fetchAll();

// Obter produtos para o select
$stmt_products = $pdo->prepare("SELECT id, name, price, cost, iva_rate FROM products WHERE company_id = ? AND status = 'active' ORDER BY name");
$stmt_products->execute([$empresa_id]);
$produtos = $stmt_products->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - FlowIn</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        
        /* Loading Spinner */
        .spinner {
            border: 3px solid rgba(249, 115, 22, 0.3);
            border-top-color: #f97316;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        
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
        
        /* Badge colors */
        .badge-ft { background-color: #dbeafe; color: #1e40af; }
        .badge-pf { background-color: #f3e8ff; color: #6b21a8; }
        .badge-re { background-color: #dcfce7; color: #166534; }
        .badge-nc { background-color: #fef9c3; color: #854d0e; }
        .badge-nd { background-color: #fee2e2; color: #991b1b; }
        
        /* Print styles */
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: white; color: black; }
            .card { box-shadow: none; border: 1px solid #ddd; }
        }
    </style>
</head>
<body class="text-slate-200 min-h-screen">

    <!-- Main Content -->
    <main class="md:ml-64 p-4 md:p-8 pt-20 md:pt-8">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Gestão de Documentos Fiscais</h1>
                <p class="text-slate-400 mt-1">Faturas, Pró-formas, Recibos e Notas</p>
            </div>
            <button onclick="openModal()" class="flex items-center justify-center gap-2 px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-orange-500/25 hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Novo Documento
            </button>
        </div>
        
        <!-- Filters -->
        <div class="bg-slate-800 rounded-xl p-6 mb-8 border border-slate-700">
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Type Filter Tabs -->
                <div class="flex gap-2 overflow-x-auto pb-2 lg:pb-0">
                    <button onclick="filterByType('all')" class="filter-btn active px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-orange-500 text-white" data-type="all">Todos</button>
                    <button onclick="filterByType('FT')" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-slate-700 text-slate-300 hover:bg-slate-600" data-type="FT">Faturas</button>
                    <button onclick="filterByType('PF')" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-slate-700 text-slate-300 hover:bg-slate-600" data-type="PF">Pró-formas</button>
                    <button onclick="filterByType('RE')" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-slate-700 text-slate-300 hover:bg-slate-600" data-type="RE">Recibos</button>
                    <button onclick="filterByType('NC')" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-slate-700 text-slate-300 hover:bg-slate-600" data-type="NC">Notas Crédito</button>
                    <button onclick="filterByType('ND')" class="filter-btn px-4 py-2 rounded-lg text-sm font-medium whitespace-nowrap transition-all bg-slate-700 text-slate-300 hover:bg-slate-600" data-type="ND">Notas Débito</button>
                </div>
                
                <!-- Search -->
                <div class="relative flex-1 max-w-md">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" id="searchInput" oninput="searchDocuments()" placeholder="Pesquisar por nº documento ou cliente..." 
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 transition-all">
                </div>
            </div>
        </div>
        
        <!-- Documents List -->
        <div id="documentsGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <!-- Cards will be dynamically inserted here -->
        </div>
        
        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-16">
            <svg class="w-20 h-20 mx-auto text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <h3 class="text-xl font-semibold text-slate-300 mb-2">Nenhum documento encontrado</h3>
            <p class="text-slate-400 mb-6">Crie o seu primeiro documento fiscal para começar</p>
            <button onclick="openModal()" class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold transition-all">
                Criar Documento
            </button>
        </div>
        
    </main>

    <!-- Modal Novo/Editar Documento -->
    <div id="documentModal" class="modal-overlay fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4 overflow-y-auto">
        <div class="modal-content bg-slate-800 rounded-2xl w-full max-w-5xl max-h-[90vh] overflow-y-auto border border-slate-700 shadow-2xl">
            
            <!-- Modal Header -->
            <div class="sticky top-0 bg-slate-800 border-b border-slate-700 p-6 flex items-center justify-between z-10">
                <div>
                    <h2 id="modalTitle" class="text-2xl font-bold text-white">Novo Documento</h2>
                    <p class="text-slate-400 text-sm mt-1">Preencha as informações do documento fiscal</p>
                </div>
                <button onclick="closeModal()" class="p-2 hover:bg-slate-700 rounded-lg transition-colors">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 space-y-6">
                
                <!-- Document Type & Number -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Tipo de Documento *</label>
                        <select id="docType" onchange="onDocTypeChange()" class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500">
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
                        <input type="text" id="docNumber" placeholder="Automático" readonly 
                            class="w-full px-4 py-2.5 bg-slate-600 border border-slate-600 rounded-xl text-slate-300 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Data do Documento *</label>
                        <input type="date" id="docDate" class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500">
                    </div>
                </div>
                
                <!-- Client & Due Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Cliente *</label>
                        <select id="docClient" class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500">
                            <option value="">Selecione um cliente...</option>
                        </select>
                    </div>
                    <div id="dueDateField">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Data de Vencimento *</label>
                        <input type="date" id="docDueDate" class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500">
                    </div>
                </div>
                
                <!-- Source Document (for NC, ND, RE) -->
                <div id="sourceDocField" class="hidden">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Documento de Origem *</label>
                    <select id="sourceDoc" class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500">
                        <option value="">Selecione...</option>
                    </select>
                    <p class="text-xs text-slate-400 mt-1">Documento ao qual esta nota/recibo se refere</p>
                </div>
                
                <!-- Items Table -->
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
                                    <th class="px-4 py-3 text-center w-24">Qtd</th>
                                    <th class="px-4 py-3 text-right w-32">Preço Unit.</th>
                                    <th class="px-4 py-3 text-center w-20">IVA %</th>
                                    <th class="px-4 py-3 text-right w-32">Total</th>
                                    <th class="px-4 py-3 text-center w-12"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody" class="divide-y divide-slate-700">
                                <!-- Items will be added here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-slate-700">
                        <button onclick="addItem()" class="flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg text-sm font-medium transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Adicionar Item
                        </button>
                    </div>
                </div>
                
                <!-- Totals -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Método de Pagamento</label>
                            <select id="paymentMethod" class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500">
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
                            <select id="docStatus" class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500">
                                <option value="Pendente">Pendente</option>
                                <option value="Emitida">Emitida</option>
                                <option value="Paga">Paga</option>
                                <option value="Cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Observações</label>
                            <textarea id="docNotes" rows="3" placeholder="Informações adicionais..." 
                                class="w-full px-4 py-2.5 bg-slate-700 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 resize-none"></textarea>
                        </div>
                    </div>
                    
                    <div class="bg-slate-700/30 rounded-xl p-6 space-y-3">
                        <div class="flex justify-between text-slate-300">
                            <span>Subtotal:</span>
                            <span id="subtotalValue">0,00 Kz</span>
                        </div>
                        <div class="flex justify-between text-slate-300">
                            <span>Desconto:</span>
                            <div class="flex items-center gap-2">
                                <input type="number" id="discountValue" value="0" min="0" oninput="calculateTotals()" 
                                    class="w-24 px-2 py-1 bg-slate-600 border border-slate-500 rounded text-right text-sm text-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                                <span id="discountType" class="text-xs text-slate-400">Kz</span>
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
            <div class="sticky bottom-0 bg-slate-800 border-t border-slate-700 p-6 flex items-center justify-end gap-4">
                <button onclick="closeModal()" class="px-6 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-xl font-medium transition-all">
                    Cancelar
                </button>
                <button onclick="saveDraft()" class="px-6 py-2.5 bg-slate-600 hover:bg-slate-500 text-white rounded-xl font-medium transition-all">
                    Guardar Rascunho
                </button>
                <button onclick="emitDocument()" id="emitBtn" class="flex items-center gap-2 px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold transition-all hover:shadow-lg hover:shadow-orange-500/25">
                    <span id="emitBtnText">Emitir Documento</span>
                    <div id="emitSpinner" class="spinner hidden"></div>
                </button>
            </div>
            
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-4 right-4 z-[60] space-y-3"></div>

    <script>
        // ==================== DADOS MOCKADOS ====================
        // Dados de clientes vindos do servidor PHP (já não é necessário array mock)

        // Dados de produtos vindos do servidor PHP
        const mockProducts = <?= json_encode($produtos) ?>;

        // Dados dos documentos vindos do servidor PHP
        let documents = <?= json_encode($faturas->map(function($f) {
            return [
                'id' => $f['id'],
                'type' => $f['type'] ?? 'FT',
                'number' => $f['invoice_number'],
                'date' => $f['issue_date'],
                'dueDate' => $f['due_date'],
                'clientId' => $f['client_id'],
                'clientName' => $f['client_name'] ?? 'Cliente Desconhecido',
                'status' => $f['status'],
                'notes' => $f['notes'],
                'subtotal' => (float)$f['subtotal'],
                'discount' => (float)$f['discount_amount'],
                'iva' => (float)$f['iva_amount'],
                'total' => (float)$f['total_amount'],
                'items' => []
            ];
        })) ?>;

        // ==================== INICIALIZAÇÃO ====================
        document.addEventListener('DOMContentLoaded', () => {
            loadClients();
            renderDocuments();
            setupMobileMenu();
            setTodayDate();
        });

        function setTodayDate() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('docDate').value = today;
        }

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

        // ==================== GESTÃO DE CLIENTES ====================
        function loadClients() {
            const select = document.getElementById('docClient');
            const clientes = <?= json_encode($clientes) ?>;
            clientes.forEach(client => {
                const option = document.createElement('option');
                option.value = client.id;
                option.textContent = `${client.name} - Doc: ${client.document || 'N/A'}`;
                select.appendChild(option);
            });
        }

        // ==================== RENDERIZAÇÃO DE DOCUMENTOS ====================
        function renderDocuments(filteredDocs = null) {
            const grid = document.getElementById('documentsGrid');
            const emptyState = document.getElementById('emptyState');
            const docsToRender = filteredDocs || documents;

            if (docsToRender.length === 0) {
                grid.innerHTML = '';
                emptyState.classList.remove('hidden');
                return;
            }

            emptyState.classList.add('hidden');
            grid.innerHTML = docsToRender.map((doc, index) => {
                const clientName = doc.clientName || 'Cliente Desconhecido';
                const badgeClass = getBadgeClass(doc.type);
                const typeName = getTypeName(doc.type);
                
                return `
                    <div class="bg-slate-800 rounded-xl border border-slate-700 p-6 hover:border-orange-500/50 transition-all group">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-white">${doc.number}</h3>
                                <p class="text-slate-400 text-sm">${clientName}</p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold ${badgeClass}">${typeName}</span>
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Data:</span>
                                <span class="text-slate-200">${formatDate(doc.date)}</span>
                            </div>
                            ${doc.dueDate ? `
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Vencimento:</span>
                                <span class="text-slate-200">${formatDate(doc.dueDate)}</span>
                            </div>
                            ` : ''}
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Valor:</span>
                                <span class="text-orange-500 font-semibold">${formatCurrency(doc.total)}</span>
                            </div>
                            ${doc.status ? `
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-400">Estado:</span>
                                <span class="${getStatusColor(doc.status)}">${doc.status}</span>
                            </div>
                            ` : ''}
                        </div>
                        
                        <div class="flex gap-2 pt-4 border-t border-slate-700">
                            <button onclick="viewDocument(${index})" class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg text-sm transition-all" title="Visualizar/Imprimir">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                            <button onclick="editDocument(${index})" class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg text-sm transition-all" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            ${doc.type === 'PF' ? `
                            <button onclick="convertToInvoice(${index})" class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-orange-500/20 hover:bg-orange-500/30 text-orange-500 rounded-lg text-sm transition-all" title="Converter em Fatura">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                            </button>
                            ` : ''}
                            ${doc.type === 'FT' && doc.status !== 'Cancelada' ? `
                            <button onclick="generateReceipt(${index})" class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-green-500/20 hover:bg-green-500/30 text-green-500 rounded-lg text-sm transition-all" title="Gerar Recibo">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </button>
                            ` : ''}
                            <button onclick="deleteDocument(${index})" class="flex-1 flex items-center justify-center gap-1 px-3 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-500 rounded-lg text-sm transition-all" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function getBadgeClass(type) {
            const classes = {
                'FT': 'badge-ft',
                'PF': 'badge-pf',
                'RE': 'badge-re',
                'NC': 'badge-nc',
                'ND': 'badge-nd'
            };
            return classes[type] || 'badge-ft';
        }

        function getTypeName(type) {
            const names = {
                'FT': 'Fatura',
                'PF': 'Pró-forma',
                'RE': 'Recibo',
                'NC': 'Nota Crédito',
                'ND': 'Nota Débito'
            };
            return names[type] || type;
        }

        function getStatusColor(status) {
            const colors = {
                'Pendente': 'text-yellow-500',
                'Emitida': 'text-blue-500',
                'Paga': 'text-green-500',
                'Cancelada': 'text-red-500'
            };
            return colors[status] || 'text-slate-400';
        }

        function formatCurrency(value) {
            return new Intl.NumberFormat('pt-AO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + ' Kz';
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('pt-PT');
        }

        // ==================== FILTROS E PESQUISA ====================
        let currentFilter = 'all';

        function filterByType(type) {
            currentFilter = type;
            
            // Update buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
                if (btn.dataset.type === type) {
                    btn.classList.remove('bg-slate-700', 'text-slate-300');
                    btn.classList.add('bg-orange-500', 'text-white');
                } else {
                    btn.classList.add('bg-slate-700', 'text-slate-300');
                    btn.classList.remove('bg-orange-500', 'text-white');
                }
            });

            searchDocuments();
        }

        function searchDocuments() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            let filtered = documents;
            
            // Filter by type
            if (currentFilter !== 'all') {
                filtered = filtered.filter(doc => doc.type === currentFilter);
            }
            
            // Filter by search
            if (searchTerm) {
                filtered = filtered.filter(doc => {
                    return doc.number.toLowerCase().includes(searchTerm) || 
                           (doc.clientName && doc.clientName.toLowerCase().includes(searchTerm));
                });
            }
            
            renderDocuments(filtered);
        }

        // ==================== MODAL FUNCTIONS ====================
        let editingIndex = null;
        let items = [];

        function openModal(index = null) {
            editingIndex = index;
            items = [];
            
            if (index !== null) {
                // Edit mode
                const doc = documents[index];
                document.getElementById('modalTitle').textContent = 'Editar Documento';
                document.getElementById('docType').value = doc.type;
                document.getElementById('docNumber').value = doc.number;
                document.getElementById('docDate').value = doc.date;
                document.getElementById('docDueDate').value = doc.dueDate || '';
                document.getElementById('docClient').value = doc.clientId;
                document.getElementById('paymentMethod').value = doc.paymentMethod || '';
                document.getElementById('docStatus').value = doc.status || 'Pendente';
                document.getElementById('docNotes').value = doc.notes || '';
                items = doc.items || [];
                onDocTypeChange();
                renderItems();
                calculateTotals();
            } else {
                // New mode
                document.getElementById('modalTitle').textContent = 'Novo Documento';
                document.getElementById('docType').value = '';
                document.getElementById('docNumber').value = '';
                document.getElementById('docDate').value = new Date().toISOString().split('T')[0];
                document.getElementById('docDueDate').value = '';
                document.getElementById('docClient').value = '';
                document.getElementById('paymentMethod').value = '';
                document.getElementById('docStatus').value = 'Pendente';
                document.getElementById('docNotes').value = '';
                items = [];
                onDocTypeChange();
                renderItems();
                calculateTotals();
            }
            
            document.getElementById('documentModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('documentModal').classList.remove('active');
            document.body.style.overflow = '';
            editingIndex = null;
        }

        function onDocTypeChange() {
            const type = document.getElementById('docType').value;
            const dueDateField = document.getElementById('dueDateField');
            const statusField = document.getElementById('statusField');
            const sourceDocField = document.getElementById('sourceDocField');
            const paymentMethodField = document.getElementById('paymentMethod').parentElement;
            
            // Show/hide fields based on type
            dueDateField.classList.toggle('hidden', !['FT', 'PF'].includes(type));
            statusField.classList.toggle('hidden', type !== 'FT');
            paymentMethodField.classList.toggle('hidden', !['FT', 'RE'].includes(type));
            sourceDocField.classList.toggle('hidden', !['NC', 'ND', 'RE'].includes(type));
            
            // Generate number
            if (type) {
                const nextNum = getNextDocumentNumber(type);
                document.getElementById('docNumber').value = nextNum;
            }
            
            // Populate source documents for NC, ND, RE
            if (['NC', 'ND', 'RE'].includes(type)) {
                populateSourceDocuments(type);
            }
        }

        function getNextDocumentNumber(type) {
            const year = new Date().getFullYear();
            const docsOfType = documents.filter(d => d.type === type && d.number.includes(year.toString()));
            const lastNum = docsOfType.length > 0 ? Math.max(...docsOfType.map(d => parseInt(d.number.split('/')[1]))) : 0;
            return `${type} ${year}/${String(lastNum + 1).padStart(3, '0')}`;
        }

        function populateSourceDocuments(targetType) {
            const select = document.getElementById('sourceDoc');
            select.innerHTML = '<option value="">Selecione...</option>';
            
            let sourceTypes = [];
            if (targetType === 'RE') sourceTypes = ['FT', 'PF'];
            if (targetType === 'NC' || targetType === 'ND') sourceTypes = ['FT'];
            
            documents.filter(d => sourceTypes.includes(d.type)).forEach(doc => {
                const option = document.createElement('option');
                option.value = doc.number;
                option.textContent = `${doc.number} - ${doc.clientName || 'Cliente'}`;
                select.appendChild(option);
            });
        }

        // ==================== ITEMS MANAGEMENT ====================
        function addItem() {
            const product = mockProducts[0]; // Default first product
            items.push({
                productId: product.id,
                name: product.name,
                description: '',
                quantity: 1,
                price: product.price,
                tax: product.tax,
                total: product.price
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
            
            if (field === 'productId') {
                const product = mockProducts.find(p => p.id == value);
                if (product) {
                    item.name = product.name;
                    item.price = product.price;
                    item.tax = product.tax;
                }
            }
            
            if (['quantity', 'price', 'tax'].includes(field)) {
                item[field] = parseFloat(value) || 0;
            } else {
                item[field] = value;
            }
            
            item.total = item.quantity * item.price;
            renderItems();
            calculateTotals();
        }

        function renderItems() {
            const tbody = document.getElementById('itemsTableBody');
            
            if (items.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                            Nenhum item adicionado. Clique em "Adicionar Item" para começar.
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = items.map((item, index) => `
                <tr class="hover:bg-slate-700/30 transition-colors">
                    <td class="px-4 py-3">
                        <select onchange="updateItem(${index}, 'productId', this.value)" class="w-full px-2 py-1.5 bg-slate-600 border border-slate-500 rounded text-sm text-white focus:outline-none focus:ring-1 focus:ring-orange-500">
                            ${mockProducts.map(p => `
                                <option value="${p.id}" ${p.id == item.productId ? 'selected' : ''}>${p.name}</option>
                            `).join('')}
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
                        <input type="number" value="${item.price}" min="0" step="0.01" oninput="updateItem(${index}, 'price', this.value)" 
                            class="w-full px-2 py-1.5 bg-slate-600 border border-slate-500 rounded text-sm text-white text-right focus:outline-none focus:ring-1 focus:ring-orange-500">
                    </td>
                    <td class="px-4 py-3">
                        <input type="number" value="${item.tax}" min="0" max="100" oninput="updateItem(${index}, 'tax', this.value)" 
                            class="w-full px-2 py-1.5 bg-slate-600 border border-slate-500 rounded text-sm text-white text-center focus:outline-none focus:ring-1 focus:ring-orange-500">
                    </td>
                    <td class="px-4 py-3 text-right text-sm font-medium text-white">
                        ${formatCurrency(item.total)}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button onclick="removeItem(${index})" class="p-1 hover:bg-red-500/20 rounded transition-colors">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function calculateTotals() {
            const subtotal = items.reduce((sum, item) => sum + item.total, 0);
            const discount = parseFloat(document.getElementById('discountValue').value) || 0;
            const isPercentage = false; // Could add toggle for percentage/fixed
            
            const discountAmount = isPercentage ? (subtotal * discount / 100) : discount;
            const ivaBase = Math.max(0, subtotal - discountAmount);
            const ivaValue = ivaBase * 0.14; // 14% IVA standard in Angola
            const total = ivaBase + ivaValue;
            
            document.getElementById('subtotalValue').textContent = formatCurrency(subtotal);
            document.getElementById('ivaBaseValue').textContent = formatCurrency(ivaBase);
            document.getElementById('ivaValue').textContent = formatCurrency(ivaValue);
            document.getElementById('totalValue').textContent = formatCurrency(total);
            
            return { subtotal, discount: discountAmount, ivaBase, iva: ivaValue, total };
        }

        // ==================== SAVE & EMIT ====================
        function validateForm() {
            const type = document.getElementById('docType').value;
            const clientId = document.getElementById('docClient').value;
            const docDate = document.getElementById('docDate').value;
            const dueDate = document.getElementById('docDueDate').value;
            const sourceDoc = document.getElementById('sourceDoc').value;
            
            if (!type) {
                showToast('error', 'Selecione o tipo de documento');
                return false;
            }
            
            if (!clientId) {
                showToast('error', 'Selecione um cliente');
                return false;
            }
            
            if (!docDate) {
                showToast('error', 'Preencha a data do documento');
                return false;
            }
            
            // Check future date (except for PF)
            if (type !== 'PF' && new Date(docDate) > new Date()) {
                showToast('error', 'A data não pode ser futura');
                return false;
            }
            
            if (['FT', 'PF'].includes(type) && !dueDate) {
                showToast('error', 'Preencha a data de vencimento');
                return false;
            }
            
            if (['NC', 'ND', 'RE'].includes(type) && !sourceDoc) {
                showToast('error', 'Selecione o documento de origem');
                return false;
            }
            
            if (items.length === 0) {
                showToast('error', 'Adicione pelo menos um item');
                return false;
            }
            
            return true;
        }

        function saveDocument(isDraft = false) {
            if (!isDraft && !validateForm()) return false;
            
            const totals = calculateTotals();
            const type = document.getElementById('docType').value;
            const action = editingIndex !== null ? 'update' : 'create';
            const invoiceId = editingIndex !== null ? documents[editingIndex].id : null;
            
            // Preparar dados para envio ao servidor
            const formData = new FormData();
            formData.append('action', action);
            formData.append('tipo', type);
            formData.append('client_id', document.getElementById('docClient').value);
            formData.append('issue_date', document.getElementById('docDate').value);
            formData.append('due_date', document.getElementById('docDueDate').value || '');
            formData.append('notes', document.getElementById('docNotes').value || '');
            formData.append('status', document.getElementById('docStatus').value || 'draft');
            formData.append('discount_amount', totals.discount);
            
            if (invoiceId) {
                formData.append('invoice_id', invoiceId);
            }
            
            // Adicionar itens
            items.forEach((item, index) => {
                formData.append(`items[${index}][product_id]`, item.product_id || '');
                formData.append(`items[${index}][description]`, item.description);
                formData.append(`items[${index}][quantity]`, item.quantity);
                formData.append(`items[${index}][unit_price]`, item.unit_price);
                formData.append(`items[${index}][unit_cost]`, item.unit_cost || 0);
                formData.append(`items[${index}][iva_rate]`, item.iva_rate);
                formData.append(`items[${index}][subtotal]`, item.subtotal);
                formData.append(`items[${index}][iva]`, item.iva);
                formData.append(`items[${index}][total_amount]`, item.total_amount);
            });
            
            // Enviar para o servidor
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes('sucesso')) {
                    closeModal();
                    // Recarregar a página para atualizar os dados do servidor
                    window.location.reload();
                } else {
                    showToast('error', 'Erro ao salvar documento');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showToast('error', 'Erro de comunicação com o servidor');
            });
            
            return true;
        }

        function saveDraft() {
            if (saveDocument(true)) {
                closeModal();
                renderDocuments();
                showToast('success', 'Rascunho guardado com sucesso');
            }
        }

        function emitDocument() {
            if (!validateForm()) return;
            
            const btn = document.getElementById('emitBtn');
            const spinner = document.getElementById('emitSpinner');
            const btnText = document.getElementById('emitBtnText');
            
            btn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.textContent = 'A emitir...';
            
            // Simulate API call
            setTimeout(() => {
                if (saveDocument(false)) {
                    closeModal();
                    renderDocuments();
                    showToast('success', 'Documento emitido com sucesso!');
                }
                
                btn.disabled = false;
                spinner.classList.add('hidden');
                btnText.textContent = 'Emitir Documento';
            }, 1000);
        }

        // ==================== DOCUMENT ACTIONS ====================
        function viewDocument(index) {
            showToast('info', 'Funcionalidade de visualização em desenvolvimento');
            // Implement print/view logic here
        }

        function editDocument(index) {
            openModal(index);
        }

        function deleteDocument(index) {
            if (confirm('Tem certeza que deseja eliminar este documento?')) {
                const doc = documents[index];
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('invoice_id', doc.id);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data.includes('sucesso')) {
                        window.location.reload();
                    } else {
                        showToast('error', 'Erro ao eliminar documento');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showToast('error', 'Erro de comunicação com o servidor');
                });
            }
        }

        function convertToInvoice(index) {
            const pf = documents[index];
            
            if (confirm('Converter esta Pró-forma em Fatura? Será criado um novo documento.')) {
                const invoice = {
                    ...pf,
                    type: 'FT',
                    number: getNextDocumentNumber('FT'),
                    date: new Date().toISOString().split('T')[0],
                    status: 'Pendente',
                    sourceDocument: pf.number
                };
                
                documents.push(invoice);
                localStorage.setItem('flowin_documents', JSON.stringify(documents));
                renderDocuments();
                showToast('success', 'Pró-forma convertida em Fatura com sucesso!');
            }
        }

        function generateReceipt(index) {
            const invoice = documents[index];
            
            if (confirm('Gerar Recibo associado a esta Fatura?')) {
                const receipt = {
                    ...invoice,
                    type: 'RE',
                    number: getNextDocumentNumber('RE'),
                    date: new Date().toISOString().split('T')[0],
                    dueDate: null,
                    status: null,
                    sourceDocument: invoice.number,
                    paymentMethod: invoice.paymentMethod
                };
                
                documents.push(receipt);
                localStorage.setItem('flowin_documents', JSON.stringify(documents));
                renderDocuments();
                showToast('success', 'Recibo gerado com sucesso!');
            }
        }

        // ==================== TOAST NOTIFICATIONS ====================
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
            
            // Animate in
            setTimeout(() => toast.classList.add('show'), 10);
            
            // Remove after 4 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

    </script>
</body>
</html>
