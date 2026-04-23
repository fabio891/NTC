<?php
session_start();

// Verificar autenticação e obter empresa_id
if (!isset($_SESSION['empresa_id']) && !isset($_SESSION['company_id'])) {
    header('Location: ../../../index.php');
    exit;
}
$companyId = $_SESSION['empresa_id'] ?? $_SESSION['company_id'];

$pageTitle = 'Produtos';

// Processar formulário de criação/edição ANTES do header
$mensagem = '';
$tipoMensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    try {
        $pdo->beginTransaction();
        
        if ($acao === 'criar') {
            // Gerar código automático se não fornecido (formato: XX-000XX, 7 caracteres)
            $codigo = $_POST['codigo'] ?? null;
            if (empty($codigo)) {
                // Gerar código único: 2 letras + hífen + 3 números + 1 letra
                $letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $numeros = '0123456789';
                $codigo = $letras[random_int(0, 25)] . $letras[random_int(0, 25)] . '-';
                $codigo .= str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);
                $codigo .= $letras[random_int(0, 25)];
                
                // Verificar se já existe e gerar novo se necessário
                $checkSql = "SELECT id FROM products WHERE code = ? AND company_id = ?";
                $checkStmt = $pdo->prepare($checkSql);
                while (true) {
                    $checkStmt->execute([$codigo, $companyId]);
                    if (!$checkStmt->fetch()) {
                        break;
                    }
                    // Regenerar se já existir
                    $codigo = $letras[random_int(0, 25)] . $letras[random_int(0, 25)] . '-';
                    $codigo .= str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);
                    $codigo .= $letras[random_int(0, 25)];
                }
            }
            
            // Inserir novo produto
            $sql = "INSERT INTO products (company_id, name, code, barcode, description, price, cost, stock, min_stock, category, unit, iva_rate, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $companyId,
                $_POST['nome'],
                $codigo,
                $_POST['barcode'] ?? null,
                $_POST['descricao'] ?? null,
                $_POST['preco'],
                $_POST['custo'] ?? 0,
                $_POST['stock'] ?? 0,
                $_POST['stock_minimo'] ?? 10,
                $_POST['categoria'] ?? null,
                $_POST['unidade'] ?? 'un',
                $_POST['iva_rate'] ?? 14.00,
                $_POST['estado'] ?? 'active'
            ]);
            
            $mensagem = 'Produto criado com sucesso!';
            $tipoMensagem = 'success';
            $pdo->commit();
        } elseif ($acao === 'editar') {
            // Atualizar produto existente
            $sql = "UPDATE products SET 
                    name = ?, code = ?, barcode = ?, description = ?, price = ?, cost = ?, 
                    stock = ?, min_stock = ?, category = ?, unit = ?, iva_rate = ?, status = ?
                    WHERE id = ? AND company_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['nome'],
                $_POST['codigo'] ?? null,
                $_POST['barcode'] ?? null,
                $_POST['descricao'] ?? null,
                $_POST['preco'],
                $_POST['custo'] ?? 0,
                $_POST['stock'] ?? 0,
                $_POST['stock_minimo'] ?? 10,
                $_POST['categoria'] ?? null,
                $_POST['unidade'] ?? 'un',
                $_POST['iva_rate'] ?? 14.00,
                $_POST['estado'] ?? 'active',
                $_POST['produto_id'],
                $companyId
            ]);
            
            $pdo->commit();
            
            // Redirecionar para limpar parâmetros GET e fechar modal
            header('Location: produtos.php?msg=success&tipo=edit');
            exit;
        } elseif ($acao === 'eliminar') {
            // Eliminar produto (soft delete - mudar status para discontinued)
            $sql = "UPDATE products SET status = 'discontinued' WHERE id = ? AND company_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['produto_id'], $companyId]);
            
            $mensagem = 'Produto eliminado com sucesso!';
            $tipoMensagem = 'success';
            $pdo->commit();
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $mensagem = 'Erro: ' . $e->getMessage();
        $tipoMensagem = 'error';
    }
}

// Buscar produtos da empresa
try {
    $sql = "SELECT id, name, code, barcode, description, price, cost, stock, min_stock, category, unit, iva_rate, status, created_at, updated_at 
            FROM products 
            WHERE company_id = ? 
            ORDER BY name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$companyId]);
    $produtos = $stmt->fetchAll();
    
    // Contar total de produtos
    $totalProdutos = count($produtos);
    
    // Buscar produtos com stock crítico (consulta direta para garantir company_id)
    $sqlLowStock = "SELECT name, code, stock, min_stock, (min_stock - stock) AS needed_quantity 
                    FROM products 
                    WHERE stock <= min_stock AND status = 'active' AND company_id = ? 
                    LIMIT 5";
    $stmtLowStock = $pdo->prepare($sqlLowStock);
    $stmtLowStock->execute([$companyId]);
    $stockCritico = $stmtLowStock->fetchAll();
} catch (Exception $e) {
    $produtos = [];
    $totalProdutos = 0;
    $stockCritico = [];
    $mensagem = 'Erro ao carregar produtos: ' . $e->getMessage();
    $tipoMensagem = 'error';
}

// Mensagem de sucesso via GET
$mostrarMensagemSucesso = false;
if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
    $mensagem = 'Produto atualizado com sucesso!';
    $tipoMensagem = 'success';
    $mostrarMensagemSucesso = true;
}

// Produto em edição (se houver) - só abre modal se NÃO vier de um submit bem-sucedido
$produtoEdicao = null;
if (isset($_GET['editar']) && is_numeric($_GET['editar']) && !$mostrarMensagemSucesso) {
    try {
        $sql = "SELECT * FROM products WHERE id = ? AND company_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_GET['editar'], $companyId]);
        $produtoEdicao = $stmt->fetch();
    } catch (Exception $e) {
        // Ignorar erro
    }
}
?>

<?php require_once __DIR__ . '/../../../Includes/header.php'; ?>

<!-- Conteúdo Principal -->
<div class="p-4 sm:p-6 md:p-8">
    <!-- Cabeçalho -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestão de Produtos</h1>
            <p class="text-slate-400 mt-1">Controlo completo do seu inventário</p>
        </div>
        <button onclick="abrirModalNovoProduto()" class="w-full sm:w-auto mt-4 sm:mt-0 flex items-center justify-center bg-purple-500 hover:bg-purple-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Novo Produto
        </button>
    </div>

    <!-- Mensagens de feedback -->
    <?php if ($mensagem): ?>
    <div class="mb-4 p-4 rounded-lg <?php echo $tipoMensagem === 'success' ? 'bg-green-900/50 border border-green-700 text-green-300' : 'bg-red-900/50 border border-red-700 text-red-300'; ?>">
        <?php echo htmlspecialchars($mensagem); ?>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="relative">
            <input type="text" id="filtroPesquisa" placeholder="Pesquisar por nome, código..." 
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
        </div>
        <div class="relative">
            <select id="filtroCategoria" class="w-full appearance-none bg-slate-800 border border-slate-700 rounded-lg py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
                <option value="">Todas as Categorias</option>
                <?php
                $categorias = array_unique(array_column($produtos, 'category'));
                foreach ($categorias as $cat):
                    if ($cat):
                ?>
                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                <?php 
                    endif;
                endforeach; 
                ?>
            </select>
            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none"></i>
        </div>
        <div class="relative">
            <select id="filtroEstado" class="w-full appearance-none bg-slate-800 border border-slate-700 rounded-lg py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
                <option value="">Todos os Estados</option>
                <option value="active">Ativo</option>
                <option value="inactive">Inativo</option>
                <option value="discontinued">Descontinuado</option>
            </select>
            <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none"></i>
        </div>
    </div>

    <!-- Lista de Produtos -->
    <div class="bg-slate-800 rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i class="fas fa-box text-slate-400"></i>
                <h3 class="text-lg font-semibold text-white">Produtos Registados (<?php echo $totalProdutos; ?>)</h3>
            </div>
            <?php if (count($stockCritico) > 0): ?>
            <div class="flex items-center text-orange-400">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span class="text-sm"><?php echo count($stockCritico); ?> produto(s) com stock crítico</span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-400">
                <thead class="text-xs text-slate-400 uppercase bg-slate-700/50">
                    <tr>
                        <th class="px-6 py-3">Produto</th>
                        <th class="px-6 py-3">Código</th>
                        <th class="px-6 py-3">Categoria</th>
                        <th class="px-6 py-3">Preço (Kz)</th>
                        <th class="px-6 py-3">Stock</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3 whitespace-nowrap">Ações</th>
                    </tr>
                </thead>
                <tbody id="tabelaProdutos">
                    <?php if (empty($produtos)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-12 px-6">
                            <i class="fas fa-box-open mx-auto h-12 w-12 text-slate-500"></i>
                            <p class="mt-4 text-slate-400">Nenhum produto encontrado</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($produtos as $produto): 
                            $statusClass = match($produto['status']) {
                                'active' => 'bg-green-900/50 text-green-300',
                                'inactive' => 'bg-slate-700 text-slate-300',
                                'discontinued' => 'bg-red-900/50 text-red-300',
                                default => 'bg-slate-700 text-slate-300'
                            };
                            $statusLabel = match($produto['status']) {
                                'active' => 'Ativo',
                                'inactive' => 'Inativo',
                                'discontinued' => 'Descontinuado',
                                default => $produto['status']
                            };
                            $stockAlert = $produto['stock'] <= $produto['min_stock'] ? 'text-orange-400' : '';
                        ?>
                    <tr class="border-b border-slate-700 hover:bg-slate-700/50">
                        <td class="px-6 py-4 font-medium text-white"><?php echo htmlspecialchars($produto['name']); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($produto['code'] ?? '-'); ?></td>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($produto['category'] ?? '-'); ?></td>
                        <td class="px-6 py-4"><?php echo number_format($produto['price'], 2, ',', '.'); ?> Kz</td>
                        <td class="px-6 py-4 <?php echo $stockAlert; ?>">
                            <?php echo $produto['stock']; ?>
                            <?php if ($produto['stock'] <= $produto['min_stock']): ?>
                                <span class="ml-2 text-xs">(mín: <?php echo $produto['min_stock']; ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs font-medium <?php echo $statusClass; ?>">
                                <?php echo $statusLabel; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <a href="?editar=<?php echo $produto['id']; ?>" class="text-blue-400 hover:text-blue-300" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="confirmarEliminacao(<?php echo $produto['id']; ?>, '<?php echo htmlspecialchars($produto['name']); ?>')" 
                                        class="text-red-400 hover:text-red-300" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Novo/Editar Produto -->
<div id="modalProduto" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center">
    <div class="bg-slate-800 rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between p-5 border-b border-slate-700">
            <h2 id="tituloModal" class="text-xl font-bold text-white">Novo Produto</h2>
            <button onclick="fecharModal()" class="text-slate-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form method="POST" class="flex-1 p-6 overflow-y-auto" id="formProduto">
            <input type="hidden" name="acao" id="formAcao" value="criar">
            <input type="hidden" name="produto_id" id="formProdutoId" value="">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Nome do Produto <span class="text-red-500">*</span></label>
                    <input type="text" name="nome" id="formNome" required 
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Código do Produto</label>
                    <input type="text" name="codigo" id="formCodigo" readonly
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white cursor-not-allowed opacity-75">
                    <small class="text-slate-400 mt-1 block">Gerado automaticamente</small>
                </div>
            </div>
            
            <div class="mt-4">
                <label class="block text-sm font-medium text-slate-300 mb-1">Descrição</label>
                <textarea name="descricao" id="formDescricao" rows="3" 
                          class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white"></textarea>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Categoria</label>
                    <input type="text" name="categoria" id="formCategoria" list="listaCategorias"
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
                    <datalist id="listaCategorias">
                        <option value="Bebidas">
                        <option value="Mercearia">
                        <option value="Limpeza">
                        <option value="Eletrónicos">
                        <option value="Vestuário">
                    </datalist>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Unidade</label>
                    <select name="unidade" id="formUnidade" 
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
                        <option value="un">Unidade</option>
                        <option value="kg">Kg</option>
                        <option value="lt">Litro</option>
                        <option value="cx">Caixa</option>
                        <option value="svc">Serviço</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Preço de Venda (Kz) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="preco" id="formPreco" required min="0"
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Custo (Kz)</label>
                    <input type="number" step="0.01" name="custo" id="formCusto" min="0"
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Stock Atual <span class="text-red-500">*</span></label>
                    <input type="number" name="stock" id="formStock" required min="0" value="0"
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Stock Mínimo</label>
                    <input type="number" name="stock_minimo" id="formStockMinimo" min="0" value="10"
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
                </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Taxa IVA (%)</label>
                    <input type="number" step="0.01" name="iva_rate" id="formIvaRate" min="0" value="14.00"
                           class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Estado</label>
                    <select name="estado" id="formEstado" 
                            class="w-full bg-slate-700 border border-slate-600 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-orange-500 text-white">
                        <option value="active">Ativo</option>
                        <option value="inactive">Inativo</option>
                        <option value="discontinued">Descontinuado</option>
                    </select>
                </div>
            </div>
        </form>
        
        <div class="p-5 bg-slate-900/50 border-t border-slate-700 flex justify-end space-x-3">
            <button type="button" onclick="fecharModal()" class="py-2 px-4 rounded-lg bg-slate-700 hover:bg-slate-600 text-sm font-semibold text-white">
                Cancelar
            </button>
            <button type="submit" form="formProduto" class="py-2 px-4 rounded-lg bg-purple-500 hover:bg-purple-600 text-sm font-semibold text-white flex items-center">
                <i class="fas fa-save mr-2"></i>
                <span id="btnSalvarTexto">Criar Produto</span>
            </button>
        </div>
    </div>
</div>

<!-- Formulário de Eliminação -->
<form method="POST" id="formEliminar" style="display: none;">
    <input type="hidden" name="acao" value="eliminar">
    <input type="hidden" name="produto_id" id="eliminarProdutoId">
</form>

<script>
// Funções do Modal
function abrirModalNovoProduto() {
    document.getElementById('tituloModal').textContent = 'Novo Produto';
    document.getElementById('formAcao').value = 'criar';
    document.getElementById('formProdutoId').value = '';
    document.getElementById('formNome').value = '';
    document.getElementById('formCodigo').value = '';
    document.getElementById('formCodigo').readOnly = true;
    document.getElementById('formDescricao').value = '';
    document.getElementById('formCategoria').value = '';
    document.getElementById('formUnidade').value = 'un';
    document.getElementById('formPreco').value = '';
    document.getElementById('formCusto').value = '';
    document.getElementById('formStock').value = '0';
    document.getElementById('formStockMinimo').value = '10';
    document.getElementById('formIvaRate').value = '14.00';
    document.getElementById('formEstado').value = 'active';
    document.getElementById('btnSalvarTexto').textContent = 'Criar Produto';
    
    const modal = document.getElementById('modalProduto');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function fecharModal() {
    const modal = document.getElementById('modalProduto');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function confirmarEliminacao(id, nome) {
    if (confirm('Tem certeza que deseja eliminar o produto "' + nome + '"? Esta ação não pode ser desfeita.')) {
        document.getElementById('eliminarProdutoId').value = id;
        document.getElementById('formEliminar').submit();
    }
}

// Filtros
document.getElementById('filtroPesquisa')?.addEventListener('input', filtrarProdutos);
document.getElementById('filtroCategoria')?.addEventListener('change', filtrarProdutos);
document.getElementById('filtroEstado')?.addEventListener('change', filtrarProdutos);

function filtrarProdutos() {
    const pesquisa = document.getElementById('filtroPesquisa').value.toLowerCase();
    const categoria = document.getElementById('filtroCategoria').value;
    const estado = document.getElementById('filtroEstado').value;
    
    const linhas = document.querySelectorAll('#tabelaProdutos tr');
    
    linhas.forEach(linha => {
        const celulas = linha.querySelectorAll('td');
        if (celulas.length === 0) return; // Pular linha de "nenhum produto"
        
        const nome = celulas[0]?.textContent.toLowerCase() || '';
        const codigo = celulas[1]?.textContent.toLowerCase() || '';
        const cat = celulas[2]?.textContent || '';
        const estadoCell = celulas[5]?.querySelector('span')?.textContent || '';
        
        const matchPesquisa = !pesquisa || nome.includes(pesquisa) || codigo.includes(pesquisa);
        const matchCategoria = !categoria || cat === categoria;
        const matchEstado = !estado || estadoCell === estado;
        
        if (matchPesquisa && matchCategoria && matchEstado) {
            linha.style.display = '';
        } else {
            linha.style.display = 'none';
        }
    });
}

// Fechar modal ao clicar fora
document.getElementById('modalProduto')?.addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModal();
    }
});

// Se estiver em modo de edição, abrir modal com dados preenchidos
<?php if ($produtoEdicao): ?>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('tituloModal').textContent = 'Editar Produto';
    document.getElementById('formAcao').value = 'editar';
    document.getElementById('formProdutoId').value = '<?php echo $produtoEdicao['id']; ?>';
    document.getElementById('formNome').value = '<?php echo htmlspecialchars($produtoEdicao['name']); ?>';
    document.getElementById('formCodigo').value = '<?php echo htmlspecialchars($produtoEdicao['code'] ?? ''); ?>';
    document.getElementById('formCodigo').readOnly = true; // Código não editável
    document.getElementById('formDescricao').value = '<?php echo htmlspecialchars($produtoEdicao['description'] ?? ''); ?>';
    document.getElementById('formCategoria').value = '<?php echo htmlspecialchars($produtoEdicao['category'] ?? ''); ?>';
    document.getElementById('formUnidade').value = '<?php echo htmlspecialchars($produtoEdicao['unit'] ?? 'un'); ?>';
    document.getElementById('formPreco').value = '<?php echo $produtoEdicao['price']; ?>';
    document.getElementById('formCusto').value = '<?php echo $produtoEdicao['cost'] ?? 0; ?>';
    document.getElementById('formStock').value = '<?php echo $produtoEdicao['stock'] ?? 0; ?>';
    document.getElementById('formStockMinimo').value = '<?php echo $produtoEdicao['min_stock'] ?? 10; ?>';
    document.getElementById('formIvaRate').value = '<?php echo $produtoEdicao['iva_rate'] ?? 14.00; ?>';
    document.getElementById('formEstado').value = '<?php echo htmlspecialchars($produtoEdicao['status'] ?? 'active'); ?>';
    document.getElementById('btnSalvarTexto').textContent = 'Atualizar Produto';
    
    const modal = document.getElementById('modalProduto');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../../Includes/footer.php'; ?>
