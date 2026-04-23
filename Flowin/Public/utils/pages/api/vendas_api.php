<?php
/**
 * FlowIn - API de Vendas
 * Processa requisições AJAX para criação de vendas
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../../../Config/database.php';
require_once __DIR__ . '/../../../../Includes/functions.php';

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

// Verificar autenticação
if (!isset($_SESSION['company_id'])) {
    echo json_encode(['success' => false, 'error' => 'Usuário não autenticado']);
    exit;
}

try {
    // Obter dados do formulário
    $action = $_POST['action'] ?? '';
    $company_id = $_SESSION['company_id'];
    $user_id = $_SESSION['user_id'];
    
    if ($action === 'criar_venda') {
        // Validar dados obrigatórios
        $client_id = filter_input(INPUT_POST, 'client_id', FILTER_VALIDATE_INT);
        $payment_method = $_POST['payment_method'] ?? '';
        $subtotal = floatval($_POST['subtotal'] ?? 0);
        $iva_amount = floatval($_POST['iva_amount'] ?? 0);
        $total_amount = floatval($_POST['total_amount'] ?? 0);
        $total_cost_of_goods = floatval($_POST['total_cost_of_goods'] ?? 0);
        $itens_json = $_POST['itens'] ?? '[]';
        
        if (!$client_id || !$payment_method || $total_amount <= 0) {
            throw new Exception('Dados inválidos para criar venda');
        }
        
        // Decodificar itens
        $itens = json_decode($itens_json, true);
        if (!is_array($itens) || empty($itens)) {
            throw new Exception('Pelo menos um item é necessário');
        }
        
        // Iniciar transação
        $pdo->beginTransaction();
        
        try {
            // Obter próximo número da fatura
            $stmt = $pdo->prepare("SELECT next_invoice_number, invoice_series_prefix FROM companies WHERE id = ?");
            $stmt->execute([$company_id]);
            $empresa = $stmt->fetch();
            
            $serie_year = date('Y');
            $invoice_number = sprintf('%s %s/%04d', $empresa['invoice_series_prefix'], $serie_year, $empresa['next_invoice_number']);
            
            // Inserir fatura (invoices)
            $stmt = $pdo->prepare("
                INSERT INTO invoices 
                (company_id, client_id, type, invoice_number, series_year, issue_date, subtotal, iva_amount, total_amount, status, created_by)
                VALUES (?, ?, 'FT', ?, ?, CURDATE(), ?, ?, ?, 'issued', ?)
            ");
            $stmt->execute([
                $company_id,
                $client_id,
                $invoice_number,
                $serie_year,
                $subtotal,
                $iva_amount,
                $total_amount,
                $user_id
            ]);
            $invoice_id = $pdo->lastInsertId();
            
            // Inserir itens da fatura (invoice_items)
            $stmtItem = $pdo->prepare("
                INSERT INTO invoice_items 
                (invoice_id, product_id, description, quantity, unit_price, unit_cost, iva_rate, total_amount)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($itens as $item) {
                // Obter dados do produto para descrição
                $stmtProd = $pdo->prepare("SELECT name FROM products WHERE id = ? AND company_id = ?");
                $stmtProd->execute([$item['product_id'], $company_id]);
                $produto = $stmtProd->fetch();
                
                if (!$produto) {
                    throw new Exception('Produto não encontrado: ' . $item['product_id']);
                }
                
                $stmtItem->execute([
                    $invoice_id,
                    $item['product_id'],
                    $produto['name'],
                    $item['quantity'],
                    $item['unit_price'],
                    $item['unit_cost'],
                    $item['iva_rate'] ?? 14,
                    $item['total_amount']
                ]);
                
                // Atualizar stock do produto
                $stmtStock = $pdo->prepare("
                    UPDATE products 
                    SET stock = stock - ? 
                    WHERE id = ? AND company_id = ? AND stock >= ?
                ");
                $stmtStock->execute([
                    $item['quantity'],
                    $item['product_id'],
                    $company_id,
                    $item['quantity']
                ]);
                
                if ($stmtStock->rowCount() === 0) {
                    throw new Exception('Stock insuficiente para o produto ID: ' . $item['product_id']);
                }
            }
            
            // Inserir venda (sales)
            $stmt = $pdo->prepare("
                INSERT INTO sales 
                (company_id, invoice_id, client_id, subtotal, iva_amount, total_amount, total_cost_of_goods, payment_method, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?)
            ");
            $stmt->execute([
                $company_id,
                $invoice_id,
                $client_id,
                $subtotal,
                $iva_amount,
                $total_amount,
                $total_cost_of_goods,
                $payment_method,
                $user_id
            ]);
            
            // Atualizar próximo número da fatura
            $stmt = $pdo->prepare("UPDATE companies SET next_invoice_number = next_invoice_number + 1 WHERE id = ?");
            $stmt->execute([$company_id]);
            
            // Commit da transação
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'invoice_number' => $invoice_number,
                'invoice_id' => $invoice_id,
                'message' => 'Venda registada com sucesso'
            ]);
            
        } catch (Exception $e) {
            // Rollback em caso de erro
            $pdo->rollBack();
            throw $e;
        }
        
    } else {
        throw new Exception('Ação não reconhecida');
    }
    
} catch (Exception $e) {
    error_log("Erro na API de vendas: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
