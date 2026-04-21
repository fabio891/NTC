<?php
/**
 * FlowIn - Configuração da Base de Dados
 * Conexão MySQL usando PDO
 */

$host = 'localhost';
$dbname = 'flowin_db';
$username = 'root';
$password = '';

try {
    // Criar conexão PDO
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    
    // Configurar modo de erro para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configurar fetch mode padrão para associativo
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Iniciar sessão se ainda não estiver iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Simular usuário logado para desenvolvimento (remover em produção)
    if (!isset($_SESSION['company_id'])) {
        $_SESSION['company_id'] = 1;
        $_SESSION['user_id'] = 1;
        $_SESSION['user_name'] = "João Manuel";
        $_SESSION['user_role'] = "admin";
        $_SESSION['user_email'] = "joao@kwanza.ao";
    }
    
} catch (PDOException $e) {
    // Em produção, logar o erro e mostrar mensagem genérica
    error_log("Erro na conexão com a base de dados: " . $e->getMessage());
    die("Erro na conexão com a base de dados. Por favor, contacte o suporte.");
}
?>
