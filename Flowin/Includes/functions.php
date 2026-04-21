<?php
/**
 * FlowIn - Funções Auxiliares
 */

/**
 * Formatar valor monetário em Kz (Kwanza)
 */
function formatCurrency($value) {
    return number_format((float)$value, 2, ',', '.') . ' Kz';
}

/**
 * Formatar data para padrão angolano (dd/mm/yyyy)
 */
function formatDate($date) {
    if (empty($date)) return '';
    return date('d/m/Y', strtotime($date));
}

/**
 * Formatar data e hora
 */
function formatDateTime($date) {
    if (empty($date)) return '';
    return date('d/m/Y H:i', strtotime($date));
}

/**
 * Gerar badge de status com cores apropriadas
 */
function getStatusBadge($status) {
    $colors = [
        'active' => 'bg-green-500',
        'inactive' => 'bg-slate-500',
        'blocked' => 'bg-red-500',
        'paid' => 'bg-green-500',
        'pending' => 'bg-yellow-500',
        'overdue' => 'bg-red-500',
        'issued' => 'bg-blue-500',
        'cancelled' => 'bg-slate-600',
        'draft' => 'bg-slate-500',
        'partial' => 'bg-orange-500',
        'completed' => 'bg-green-500',
        'refunded' => 'bg-red-500',
        'discontinued' => 'bg-slate-600'
    ];
    
    $labels = [
        'active' => 'Ativo',
        'inactive' => 'Inativo',
        'blocked' => 'Bloqueado',
        'paid' => 'Pago',
        'pending' => 'Pendente',
        'overdue' => 'Atrasado',
        'issued' => 'Emitida',
        'cancelled' => 'Cancelada',
        'draft' => 'Rascunho',
        'partial' => 'Parcial',
        'completed' => 'Concluído',
        'refunded' => 'Reembolsado',
        'discontinued' => 'Descontinuado'
    ];
    
    $label = $labels[$status] ?? ucfirst($status);
    $color = $colors[$status] ?? 'bg-slate-500';
    
    return "<span class='px-2 py-1 rounded text-xs text-white {$color}'>{$label}</span>";
}

/**
 * Gerar badge de stock
 */
function getStockBadge($stock, $min_stock = 10) {
    if ($stock == 0) {
        return "<span class='px-2 py-1 rounded text-xs text-white bg-red-500'>Esgotado</span>";
    } elseif ($stock <= $min_stock) {
        return "<span class='px-2 py-1 rounded text-xs text-white bg-yellow-500'>Baixo Stock ({$stock})</span>";
    } else {
        return "<span class='px-2 py-1 rounded text-xs text-white bg-green-500'>Em Stock ({$stock})</span>";
    }
}

/**
 * Sanitizar input do usuário
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Verificar se usuário tem permissão
 */
function hasPermission($permission) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    $permissions = [
        'admin' => ['all'],
        'manager' => ['view', 'create', 'edit', 'delete'],
        'cashier' => ['view', 'create'],
        'accountant' => ['view', 'create', 'edit']
    ];
    
    $role = $_SESSION['user_role'];
    
    if (in_array('all', $permissions[$role] ?? [])) {
        return true;
    }
    
    return in_array($permission, $permissions[$role] ?? []);
}

/**
 * Redirecionar para página de login se não autenticado
 */
function requireAuth() {
    if (!isset($_SESSION['company_id'])) {
        header('Location: ../Regist.html');
        exit;
    }
}

/**
 * Obter dados da empresa atual
 */
function getCurrentCompany($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->execute([$_SESSION['company_id']]);
    return $stmt->fetch();
}

/**
 * Obter dados do usuário atual
 */
function getCurrentUser($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND company_id = ?");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['company_id']]);
    return $stmt->fetch();
}
?>
