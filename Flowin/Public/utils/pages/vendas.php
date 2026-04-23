<?php
/**
 * FlowIn - Gestão de Vendas
 * Página principal de vendas com listagem e registro de novas vendas
 */

$pageTitle = 'Vendas';
require_once __DIR__ . '/../../../Includes/header.php';

// Buscar todas as vendas da empresa atual
try {
    $stmt = $pdo->prepare("
        SELECT 
            s.id,
            s.total_amount,
            s.payment_method,
            s.status,
            s.sale_date,
            c.name AS client_name
        FROM sales s
        LEFT JOIN clients c ON s.client_id = c.id
        WHERE s.company_id = ?
        ORDER BY s.sale_date DESC
    ");
    $stmt->execute([$_SESSION['company_id']]);
    $vendas = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar vendas: " . $e->getMessage());
    $vendas = [];
}

// Buscar clientes para o select
try {
    $stmt = $pdo->prepare("
        SELECT id, name, type, document 
        FROM clients 
        WHERE company_id = ? AND status = 'active'
        ORDER BY name ASC
    ");
    $stmt->execute([$_SESSION['company_id']]);
    $clientes = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar clientes: " . $e->getMessage());
    $clientes = [];
}

// Buscar produtos para o select
try {
    $stmt = $pdo->prepare("
        SELECT id, name, code, barcode, price, stock, unit, iva_rate 
        FROM products 
        WHERE company_id = ? AND status = 'active' AND stock > 0
        ORDER BY name ASC
    ");
    $stmt->execute([$_SESSION['company_id']]);
    $produtos = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar produtos: " . $e->getMessage());
    $produtos = [];
}
?>

<!-- Conteúdo Principal -->
<div class="p-4 sm:p-6 md:p-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestão de Vendas</h1>
            <p class="text-slate-400 mt-1">Controlo completo das suas vendas e faturação</p>
        </div>
        <button onclick="abrirPainelVenda()" class="w-full sm:w-auto mt-4 sm:mt-0 flex items-center justify-center bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
            <svg class="h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Nova Venda
        </button>
    </div>

    <!-- Filtros -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2 relative">
            <input type="text" id="filtro-pesquisa" placeholder="Pesquisar vendas..." onkeyup="filtrarVendas()" class="w-full bg-slate-800 border border-slate-700 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
        </div>
        <div class="relative">
            <select id="filtro-estado" onchange="filtrarVendas()" class="w-full appearance-none bg-slate-800 border border-slate-700 rounded-lg py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
                <option value="">Todos os Estados</option>
                <option value="completed">Concluída</option>
                <option value="pending">Pendente</option>
                <option value="refunded">Reembolsada</option>
                <option value="cancelled">Cancelada</option>
            </select>
            <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-500 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
        </div>
    </div>

    <!-- Lista de Vendas -->
    <div class="mt-6 bg-slate-800 rounded-lg">
        <div class="px-6 py-4 border-b border-slate-700">
            <h3 class="text-lg font-semibold text-white">Vendas Registadas (<span id="total-vendas"><?php echo count($vendas); ?></span>)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-400" id="tabela-vendas">
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
                    <?php if (empty($vendas)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-12 px-6">
                            <svg class="mx-auto h-12 w-12 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .962-.344 1.087-.835l1.828-6.491A1.125 1.125 0 0018.02 6H5.25L5.045 5.23c-.244-.923-.952-1.58-1.846-1.58H2.25zM16.5 21a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM8.25 21a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" /></svg>
                            <p class="mt-4 text-slate-400">Nenhuma venda encontrada</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($vendas as $venda): ?>
                        <tr class="bg-slate-800 border-b border-slate-700 hover:bg-slate-700/50 venda-row" data-status="<?php echo htmlspecialchars($venda['status']); ?>" data-cliente="<?php echo htmlspecialchars(strtolower($venda['client_name'] ?? '')); ?>">
                            <td class="px-6 py-4 font-medium text-white whitespace-nowrap">#<?php echo htmlspecialchars($venda['id']); ?></td>
                            <td class="px-6 py-4"><?php echo htmlspecialchars($venda['client_name'] ?? 'Cliente não identificado'); ?></td>
                            <td class="px-6 py-4"><?php echo formatDate($venda['sale_date']); ?></td>
                            <td class="px-6 py-4 font-medium text-white"><?php echo formatCurrency($venda['total_amount']); ?></td>
                            <td class="px-6 py-4">
                                <?php
                                $paymentLabels = [
                                    'cash' => 'Dinheiro',
                                    'transfer' => 'Transferência',
                                    'card' => 'Cartão',
                                    'mcx' => 'MCX',
                                    'credit' => 'Crédito'
                                ];
                                echo htmlspecialchars($paymentLabels[$venda['payment_method']] ?? $venda['payment_method']);
                                ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo getStatusBadge($venda['status']); ?>
                            </td>
                            <td class="px-6 py-4 flex items-center space-x-3">
                                <button onclick="verVenda(<?php echo $venda['id']; ?>)" class="text-blue-500 hover:text-blue-400">Ver</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Sale Slide-out Panel -->
<div id="new-sale-panel-overlay" class="fixed inset-0 bg-black/60 z-40 hidden" onclick="fecharPainelVenda()"></div>
<div id="new-sale-panel" class="fixed inset-y-0 right-0 w-full max-w-lg bg-slate-800 shadow-xl transform translate-x-full slide-in z-50 flex flex-col">
    <div class="flex items-center justify-between p-6 border-b border-slate-700">
        <h2 class="text-xl font-bold text-white">Registar Nova Venda</h2>
        <button onclick="fecharPainelVenda()" class="text-slate-400 hover:text-white">
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>
    <div class="flex-1 p-6 overflow-y-auto">
        <form id="form-venda" class="space-y-6">
            <!-- Cliente -->
            <div>
                <label class="block text-sm font-medium text-slate-300">Cliente</label>
                <select id="cliente_id" name="cliente_id" required class="mt-1 w-full appearance-none bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
                    <option value="">Selecione um cliente</option>
                    <?php foreach ($clientes as $cliente): ?>
                    <option value="<?php echo $cliente['id']; ?>">
                        <?php echo htmlspecialchars($cliente['name']); ?>
                        <?php if ($cliente['type'] === 'company'): ?>
                            (<?php echo htmlspecialchars($cliente['document'] ?? 'NIF'); ?>)
                        <?php endif; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Data -->
            <div>
                <label class="block text-sm font-medium text-slate-300">Data da Venda</label>
                <input type="date" id="data_venda" name="data_venda" value="<?php echo date('Y-m-d'); ?>" required class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
            </div>
            <!-- Itens da Venda -->
            <div>
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-medium text-slate-300">Itens da Venda</label>
                    <button type="button" onclick="adicionarItem()" class="text-sm font-medium text-orange-500 hover:text-orange-400 flex items-center">
                        <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Adicionar Item
                    </button>
                </div>
                <div id="lista-itens" class="mt-2 space-y-4">
                    <!-- Itens serão adicionados aqui via JavaScript -->
                </div>
            </div>
            <!-- Forma de Pagamento -->
            <div>
                <label class="block text-sm font-medium text-slate-300">Forma de Pagamento</label>
                <select id="payment_method" name="payment_method" required class="mt-1 w-full appearance-none bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
                    <option value="cash">Dinheiro</option>
                    <option value="transfer">Transferência Bancária</option>
                    <option value="card">Cartão de Crédito/Débito</option>
                    <option value="mcx">MCX Express</option>
                    <option value="credit">Crédito</option>
                </select>
            </div>
            <!-- Total -->
            <div class="text-right text-lg bg-slate-700/50 p-4 rounded-lg">
                <p class="text-slate-400 text-sm">Subtotal: <span id="subtotal-display" class="font-semibold text-white">Kz 0,00</span></p>
                <p class="text-slate-400 text-sm">IVA (14%): <span id="iva-display" class="font-semibold text-white">Kz 0,00</span></p>
                <p class="text-white text-xl mt-2">Total da Venda: <span id="total-display" class="font-bold">Kz 0,00</span></p>
            </div>
            <!-- Observações -->
            <div>
                <label class="block text-sm font-medium text-slate-300">Observações</label>
                <textarea id="observacoes" name="observacoes" rows="3" placeholder="Observações sobre a venda..." class="mt-1 w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white"></textarea>
            </div>
        </form>
    </div>
    <div class="p-6 bg-slate-900/50 border-t border-slate-700 flex justify-end space-x-3">
        <button type="button" onclick="fecharPainelVenda()" class="py-2 px-4 rounded-lg bg-slate-700 hover:bg-slate-600 text-sm font-semibold text-white">Cancelar</button>
        <button type="submit" form="form-venda" class="py-2 px-4 rounded-lg bg-green-500 hover:bg-green-600 text-sm font-semibold text-white">Registar Venda</button>
    </div>
</div>

<script>
// Dados dos produtos em JSON para acesso no JavaScript
const produtosDisponiveis = <?php echo json_encode($produtos); ?>;

// Funções globais para evitar erros de clique
function abrirPainelVenda() {
    const panel = document.getElementById('new-sale-panel');
    const overlay = document.getElementById('new-sale-panel-overlay');
    panel.classList.remove('translate-x-full');
    overlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Adicionar primeiro item se lista estiver vazia
    if (document.getElementById('lista-itens').children.length === 0) {
        adicionarItem();
    }
}

function fecharPainelVenda() {
    const panel = document.getElementById('new-sale-panel');
    const overlay = document.getElementById('new-sale-panel-overlay');
    panel.classList.add('translate-x-full');
    overlay.classList.add('hidden');
    document.body.style.overflow = '';
    
    // Resetar formulário
    document.getElementById('form-venda').reset();
    document.getElementById('lista-itens').innerHTML = '';
    atualizarTotais();
}

function adicionarItem() {
    const listaItens = document.getElementById('lista-itens');
    const itemIndex = listaItens.children.length;
    
    const itemDiv = document.createElement('div');
    itemDiv.className = 'p-4 space-y-4 border border-slate-700 rounded-lg bg-slate-700/30 item-venda';
    itemDiv.dataset.index = itemIndex;
    
    let produtoOptions = '<option value="">Selecionar produto</option>';
    produtosDisponiveis.forEach(prod => {
        produtoOptions += `<option value="${prod.id}" data-price="${prod.price}" data-stock="${prod.stock}" data-iva="${prod.iva_rate}">${prod.name} (Stock: ${prod.stock})</option>`;
    });
    
    itemDiv.innerHTML = `
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <label class="block text-xs font-medium text-slate-400">Produto</label>
                <select class="produto-select mt-1 w-full appearance-none bg-slate-600 border border-slate-500 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm text-white" onchange="atualizarPrecoUnitario(this)">
                    ${produtoOptions}
                </select>
            </div>
            ${itemIndex > 0 ? '<button type="button" onclick="removerItem(this)" class="ml-2 text-red-500 hover:text-red-400"><svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>' : ''}
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-400">Quantidade</label>
                <input type="number" min="1" value="1" class="quantidade-input mt-1 w-full bg-slate-600 border border-slate-500 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm text-white" oninput="calcularTotalItem(this)">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400">Preço Unitário</label>
                <input type="number" step="0.01" min="0" value="0" class="preco-unitario-input mt-1 w-full bg-slate-600 border border-slate-500 rounded-md py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm text-white" oninput="calcularTotalItem(this)">
            </div>
        </div>
        <p class="text-right text-sm text-slate-300">Total do Item: <span class="font-semibold total-item">Kz 0,00</span></p>
        <input type="hidden" class="custo-unitario" value="0">
    `;
    
    listaItens.appendChild(itemDiv);
}

function removerItem(button) {
    const itemDiv = button.closest('.item-venda');
    itemDiv.remove();
    atualizarTotais();
}

function atualizarPrecoUnitario(select) {
    const option = select.options[select.selectedIndex];
    const price = parseFloat(option.dataset.price) || 0;
    const custo = parseFloat(option.dataset.custo) || 0;
    
    const itemDiv = select.closest('.item-venda');
    itemDiv.querySelector('.preco-unitario-input').value = price;
    itemDiv.querySelector('.custo-unitario').value = custo;
    
    calcularTotalItem(select);
}

function calcularTotalItem(input) {
    const itemDiv = input.closest('.item-venda');
    const quantidade = parseFloat(itemDiv.querySelector('.quantidade-input').value) || 0;
    const precoUnitario = parseFloat(itemDiv.querySelector('.preco-unitario-input').value) || 0;
    const total = quantidade * precoUnitario;
    
    itemDiv.querySelector('.total-item').textContent = formatCurrency(total);
    itemDiv.dataset.total = total;
    
    atualizarTotais();
}

function atualizarTotais() {
    let subtotal = 0;
    let custoTotal = 0;
    
    document.querySelectorAll('.item-venda').forEach(item => {
        const totalItem = parseFloat(item.dataset.total) || 0;
        const quantidade = parseFloat(item.querySelector('.quantidade-input').value) || 0;
        const custoUnitario = parseFloat(item.querySelector('.custo-unitario').value) || 0;
        
        subtotal += totalItem;
        custoTotal += (quantidade * custoUnitario);
    });
    
    const iva = subtotal * 0.14;
    const total = subtotal + iva;
    
    document.getElementById('subtotal-display').textContent = formatCurrency(subtotal);
    document.getElementById('iva-display').textContent = formatCurrency(iva);
    document.getElementById('total-display').textContent = formatCurrency(total);
    
    return { subtotal, iva, total, custoTotal };
}

function formatCurrency(value) {
    return new Intl.NumberFormat('pt-AO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value) + ' Kz';
}

function filtrarVendas() {
    const pesquisa = document.getElementById('filtro-pesquisa').value.toLowerCase();
    const estado = document.getElementById('filtro-estado').value;
    
    document.querySelectorAll('.venda-row').forEach(row => {
        const cliente = row.dataset.cliente;
        const status = row.dataset.status;
        
        const matchPesquisa = cliente.includes(pesquisa);
        const matchEstado = estado === '' || status === estado;
        
        if (matchPesquisa && matchEstado) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function verVenda(id) {
    alert('Visualizar venda #' + id + ' - Funcionalidade em desenvolvimento');
}

// Submeter formulário
document.getElementById('form-venda').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validar itens
    const itens = [];
    let erroValidacao = false;
    
    document.querySelectorAll('.item-venda').forEach(item => {
        const produtoId = item.querySelector('.produto-select').value;
        const quantidade = parseFloat(item.querySelector('.quantidade-input').value) || 0;
        const precoUnitario = parseFloat(item.querySelector('.preco-unitario-input').value) || 0;
        const custoUnitario = parseFloat(item.querySelector('.custo-unitario').value) || 0;
        
        if (!produtoId || quantidade <= 0 || precoUnitario <= 0) {
            erroValidacao = true;
            return;
        }
        
        const ivaRate = parseFloat(item.querySelector('.produto-select').options[item.querySelector('.produto-select').selectedIndex].dataset.iva) || 14;
        const totalAmount = quantidade * precoUnitario;
        
        itens.push({
            product_id: produtoId,
            quantity: quantidade,
            unit_price: precoUnitario,
            unit_cost: custoUnitario,
            iva_rate: ivaRate,
            total_amount: totalAmount
        });
    });
    
    if (erroValidacao || itens.length === 0) {
        alert('Por favor, preencha todos os itens da venda corretamente.');
        return;
    }
    
    const totais = atualizarTotais();
    
    // Preparar dados para envio
    const formData = new FormData();
    formData.append('action', 'criar_venda');
    formData.append('client_id', document.getElementById('cliente_id').value);
    formData.append('payment_method', document.getElementById('payment_method').value);
    formData.append('observacoes', document.getElementById('observacoes').value);
    formData.append('subtotal', totais.subtotal);
    formData.append('iva_amount', totais.iva);
    formData.append('total_amount', totais.total);
    formData.append('total_cost_of_goods', totais.custoTotal);
    formData.append('itens', JSON.stringify(itens));
    
    // Enviar para o servidor
    fetch('api/vendas_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Venda registada com sucesso! Número: ' + data.invoice_number);
            fecharPainelVenda();
            location.reload();
        } else {
            alert('Erro ao registar venda: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao processar venda. Por favor, tente novamente.');
    });
});
</script>

<?php require_once __DIR__ . '/../../../Includes/footer.php'; ?>
