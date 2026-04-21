<?php
/**
 * FlowIn - Página de Autenticação e Registro
 * Login e Registro de utilizadores com validação contra base de dados
 */

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se já estiver logado, redirecionar
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../../../Config/database.php';

$error = '';
$success = '';

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } else {
        try {
            // Verificar se é email ou telemóvel
            $field = filter_var($email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
            
            $stmt = $pdo->prepare("SELECT u.id, u.name, u.email, u.password, u.role, c.id as company_id, c.name as company_name, c.status as company_status 
                                   FROM users u 
                                   JOIN companies c ON u.company_id = c.id 
                                   WHERE u." . $field . " = ? AND u.status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                if ($user['company_status'] !== 'active') {
                    $error = 'A sua empresa está inativa. Contacte o suporte.';
                } else {
                    // Login bem sucedido
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['company_id'] = $user['company_id'];
                    $_SESSION['company_name'] = $user['company_name'];
                    
                    header('Location: dashboard.php');
                    exit;
                }
            } else {
                $error = 'Email/telemóvel ou palavra-passe incorretos.';
            }
        } catch (PDOException $e) {
            error_log("Erro no login: " . $e->getMessage());
            $error = 'Ocorreu um erro. Tente novamente.';
        }
    }
}

// Processar registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $ownerName = trim($_POST['owner_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $idNumber = trim($_POST['id_number'] ?? '');
    $companyName = trim($_POST['company_name'] ?? '');
    $companyNif = trim($_POST['company_nif'] ?? '');
    $companySector = trim($_POST['company_sector'] ?? '');
    $terms = isset($_POST['terms']);
    
    // Validações
    if (empty($ownerName) || empty($email) || empty($password) || empty($companyName) || empty($companyNif)) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido.';
    } elseif (strlen($password) < 8 || !preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        $error = 'A palavra-passe deve ter pelo menos 8 caracteres, incluindo letras e números.';
    } elseif ($password !== $confirmPassword) {
        $error = 'As palavras-passe não coincidem.';
    } elseif (!$terms) {
        $error = 'Deve aceitar os termos e condições.';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Verificar se email já existe
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('Este email já está registado.');
            }
            
            // Verificar se NIF já existe
            $stmt = $pdo->prepare("SELECT id FROM companies WHERE nif = ?");
            $stmt->execute([$companyNif]);
            if ($stmt->fetch()) {
                throw new Exception('Este NIF já está registado.');
            }
            
            // Criar empresa
            $stmt = $pdo->prepare("INSERT INTO companies (name, nif, sector, status, created_at) VALUES (?, ?, ?, 'active', NOW())");
            $stmt->execute([$companyName, $companyNif, $companySector]);
            $companyId = $pdo->lastInsertId();
            
            // Hash da password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Criar utilizador
            $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role, id_number, status, created_at) VALUES (?, ?, ?, ?, 'admin', ?, NOW(), 'active')");
            $stmt->execute([$companyId, $ownerName, $email, $hashedPassword, $idNumber]);
            
            $pdo->commit();
            
            $success = 'Conta criada com sucesso! Redirecionando para login...';
            header('refresh:2;url=Regist.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Erro no registro: " . $e->getMessage());
            $error = $e->getMessage();
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Erro no registro: " . $e->getMessage());
            $error = 'Ocorreu um erro ao criar a conta. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autenticação – FlowIn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .fade-in-up { opacity: 0; transform: translateY(20px); animation: fadeInUp 0.6s ease-out forwards; }
        @keyframes fadeInUp { to { opacity: 1; transform: translateY(0); } }
        .auth-forms-clip { overflow: hidden; width: 100%; }
        .auth-forms-container { display: flex; width: 100%; transition: transform 0.4s cubic-bezier(0.2, 0, 0, 1); }
        .auth-form-wrapper { width: 100%; flex-shrink: 0; }
        .multi-step-clip { overflow: hidden; width: 100%; }
        .multi-step-container { display: flex; width: 100%; transition: transform 0.4s cubic-bezier(0.2, 0, 0, 1); }
        .step-wrapper { min-width: 100%; }
        .progress-dot { transition: all 0.3s ease; }
        .progress-dot.active { animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.2); } }
        .shake { animation: shake 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97) both; }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); } 20%, 40%, 60%, 80% { transform: translateX(4px); } }
        .input-focus-ring:focus { box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.5); }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 antialiased">
    <div class="flex flex-col md:flex-row min-h-screen">
        <!-- Left Branding Column -->
        <div class="hidden md:flex md:w-2/5 lg:w-1/3 bg-slate-900 p-8 flex-col justify-between">
            <div>
                <a href="Homevist.php" class="flex items-center space-x-2">
                    <svg class="h-8 w-auto text-orange-500" viewBox="0 0 32 32" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M16 32C24.8366 32 32 24.8366 32 16C32 7.16344 24.8366 0 16 0C7.16344 0 0 7.16344 0 16C0 24.8366 7.16344 32 16 32Z"/></svg>
                    <span class="text-2xl font-bold text-white">FlowIn</span>
                </a>
                <div class="mt-12 text-white">
                    <h1 class="text-4xl font-bold leading-tight">Gestor Comercial Inteligente para o seu Negócio.</h1>
                    <p class="mt-4 text-slate-300">Simplifique a sua gestão, automatize os seus relatórios e impulsione as suas vendas.</p>
                </div>
            </div>
            <div class="text-sm text-slate-400">&copy; <?php echo date('Y'); ?> FlowIn. Todos os direitos reservados.</div>
        </div>

        <!-- Right Form Column -->
        <div class="w-full md:w-3/5 lg:w-2/3 flex flex-col items-center justify-center p-4 sm:p-6 lg:p-8">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="mb-6 md:hidden">
                    <a href="Homevist.php" class="flex items-center space-x-2">
                        <svg class="h-8 w-auto text-orange-500" viewBox="0 0 32 32" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M16 32C24.8366 32 32 24.8366 32 16C32 7.16344 24.8366 0 16 0C7.16344 0 0 7.16344 0 16C0 24.8366 7.16344 32 16 32Z"/></svg>
                        <span class="text-2xl font-bold text-slate-800 dark:text-white">FlowIn</span>
                    </a>
                </div>

                <!-- Auth Card -->
                <div class="w-full bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700">
                    <!-- Alert Messages -->
                    <?php if ($error): ?>
                    <div class="mx-6 mt-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <p class="text-sm text-red-600 dark:text-red-400"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                    <div class="mx-6 mt-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <p class="text-sm text-green-600 dark:text-green-400"><?php echo htmlspecialchars($success); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Tabs -->
                    <div class="flex border-b border-slate-200 dark:border-slate-700 px-6 sm:px-8 pt-6 sm:pt-8">
                        <button id="login-tab" class="flex-1 pb-3 text-sm font-semibold text-orange-500 border-b-2 border-orange-500 focus:outline-none transition-colors">Entrar</button>
                        <button id="register-tab" class="flex-1 pb-3 text-sm font-semibold text-slate-500 dark:text-slate-400 focus:outline-none transition-colors">Registar</button>
                    </div>

                    <div class="auth-forms-clip">
                        <div class="auth-forms-container" id="auth-forms-container">
                            <!-- Login Form -->
                            <div id="login-form" class="auth-form-wrapper px-6 sm:px-8 pb-6 sm:pb-8">
                                <form class="mt-6 space-y-5" method="POST" action="Regist.php" id="login-form-el">
                                    <input type="hidden" name="action" value="login">
                                    <div>
                                        <label for="login-email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email ou Telemóvel</label>
                                        <div class="mt-1">
                                            <input id="login-email" name="email" type="text" required placeholder="nome@empresa.com" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 sm:text-sm bg-white dark:bg-slate-800 input-focus-ring transition-all duration-150">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="login-password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Palavra-passe</label>
                                        <div class="mt-1">
                                            <input id="login-password" name="password" type="password" required placeholder="••••••••" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 sm:text-sm bg-white dark:bg-slate-800 input-focus-ring transition-all duration-150">
                                        </div>
                                    </div>
                                    <div class="text-right text-sm">
                                        <a href="#" class="font-medium text-orange-600 hover:text-orange-500 transition-colors">Esqueceu a palavra-passe?</a>
                                    </div>
                                    <div>
                                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">Entrar no Sistema</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Register Form -->
                            <div id="register-form" class="auth-form-wrapper px-6 sm:px-8 pb-6 sm:pb-8">
                                <div class="flex justify-center space-x-2 mt-6 mb-2">
                                    <div class="progress-dot w-3 h-3 rounded-full bg-orange-500 active" data-step="1"></div>
                                    <div class="progress-dot w-3 h-3 rounded-full bg-slate-300 dark:bg-slate-600" data-step="2"></div>
                                    <div class="progress-dot w-3 h-3 rounded-full bg-slate-300 dark:bg-slate-600" data-step="3"></div>
                                </div>

                                <div class="multi-step-clip">
                                    <div class="multi-step-container" id="multi-step-container">
                                        <!-- Step 1: Dados Pessoais -->
                                        <div id="step-1" class="step-wrapper">
                                            <form class="mt-4 space-y-4" novalidate>
                                                <div class="text-center mb-4">
                                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white fade-in-up">Dados Pessoais</h3>
                                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Vamos começar com as suas informações</p>
                                                </div>
                                                <div>
                                                    <label for="register-owner-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nome Completo</label>
                                                    <input id="register-owner-name" type="text" required class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 sm:text-sm bg-white dark:bg-slate-800 input-focus-ring">
                                                </div>
                                                <div>
                                                    <label for="register-email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email</label>
                                                    <input id="register-email" type="email" required class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 sm:text-sm bg-white dark:bg-slate-800 input-focus-ring">
                                                </div>
                                                <div>
                                                    <label for="register-password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Palavra-passe</label>
                                                    <input id="register-password" type="password" required minlength="8" class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 sm:text-sm bg-white dark:bg-slate-800 input-focus-ring">
                                                </div>
                                                <div>
                                                    <label for="register-confirm-password" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Confirmar Palavra-passe</label>
                                                    <input id="register-confirm-password" type="password" required class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 sm:text-sm bg-white dark:bg-slate-800 input-focus-ring">
                                                </div>
                                                <div>
                                                    <label for="register-id-number" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Número de Identificação</label>
                                                    <input id="register-id-number" type="text" required class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 sm:text-sm bg-white dark:bg-slate-800 input-focus-ring">
                                                </div>
                                                <div class="flex gap-3 pt-4">
                                                    <button type="button" id="next-to-step-2" class="flex-1 py-3 px-4 bg-orange-500 text-white rounded-lg font-semibold hover:bg-orange-600 transition-colors">Continuar</button>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- Step 2: Dados da Empresa -->
                                        <div id="step-2" class="step-wrapper">
                                            <form class="mt-4 space-y-4" novalidate>
                                                <div class="text-center mb-4">
                                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Dados da Empresa</h3>
                                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Informações sobre o seu negócio</p>
                                                </div>
                                                <div>
                                                    <label for="register-company-name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nome da Empresa</label>
                                                    <input id="register-company-name" type="text" required class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 sm:text-sm bg-white dark:bg-slate-800 input-focus-ring">
                                                </div>
                                                <div>
                                                    <label for="register-company-nif" class="block text-sm font-medium text-slate-700 dark:text-slate-300">NIF</label>
                                                    <input id="register-company-nif" type="text" required maxlength="14" class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 sm:text-sm bg-white dark:bg-slate-800 input-focus-ring">
                                                </div>
                                                <div>
                                                    <label for="register-company-sector" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Setor de Atividade</label>
                                                    <select id="register-company-sector" required class="mt-1 w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500 sm:text-sm bg-white dark:bg-slate-800 input-focus-ring">
                                                        <option value="">Selecione...</option>
                                                        <option value="retail">Comércio Retalho</option>
                                                        <option value="wholesale">Comércio Grossista</option>
                                                        <option value="services">Serviços</option>
                                                        <option value="restaurant">Restauração</option>
                                                        <option value="health">Saúde</option>
                                                        <option value="education">Educação</option>
                                                        <option value="other">Outro</option>
                                                    </select>
                                                </div>
                                                <div class="flex gap-3 pt-4">
                                                    <button type="button" id="back-to-step-1" class="flex-1 py-3 px-4 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Voltar</button>
                                                    <button type="button" id="next-to-step-3" class="flex-1 py-3 px-4 bg-orange-500 text-white rounded-lg font-semibold hover:bg-orange-600 transition-colors">Continuar</button>
                                                </div>
                                            </form>
                                        </div>

                                        <!-- Step 3: Termos -->
                                        <div id="step-3" class="step-wrapper">
                                            <form class="mt-4 space-y-4" id="final-form" method="POST" action="Regist.php">
                                                <input type="hidden" name="action" value="register">
                                                <input type="hidden" name="owner_name" id="final-owner-name">
                                                <input type="hidden" name="email" id="final-email">
                                                <input type="hidden" name="password" id="final-password">
                                                <input type="hidden" name="confirm_password" id="final-confirm-password">
                                                <input type="hidden" name="id_number" id="final-id-number">
                                                <input type="hidden" name="company_name" id="final-company-name">
                                                <input type="hidden" name="company_nif" id="final-company-nif">
                                                <input type="hidden" name="company_sector" id="final-company-sector">
                                                
                                                <div class="text-center mb-4">
                                                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Quase Lá!</h3>
                                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Revise e aceite os termos</p>
                                                </div>
                                                
                                                <div class="bg-slate-50 dark:bg-slate-700/50 p-4 rounded-lg">
                                                    <h4 class="font-semibold text-slate-900 dark:text-white mb-2">Resumo dos Dados</h4>
                                                    <div class="text-sm text-slate-600 dark:text-slate-400 space-y-1">
                                                        <p><strong>Nome:</strong> <span id="summary-name"></span></p>
                                                        <p><strong>Email:</strong> <span id="summary-email"></span></p>
                                                        <p><strong>Empresa:</strong> <span id="summary-company"></span></p>
                                                        <p><strong>NIF:</strong> <span id="summary-nif"></span></p>
                                                    </div>
                                                </div>
                                                
                                                <div class="flex items-start gap-3">
                                                    <input type="checkbox" id="terms" name="terms" class="mt-1 w-4 h-4 text-orange-500 rounded focus:ring-orange-500">
                                                    <label for="terms" class="text-sm text-slate-600 dark:text-slate-400">
                                                        Aceito os <a href="#" class="text-orange-500 hover:underline">Termos e Condições</a> e a <a href="#" class="text-orange-500 hover:underline">Política de Privacidade</a>.
                                                    </label>
                                                </div>
                                                
                                                <div class="flex gap-3 pt-4">
                                                    <button type="button" id="back-to-step-2" class="flex-1 py-3 px-4 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Voltar</button>
                                                    <button type="submit" id="create-account-btn" disabled class="flex-1 py-3 px-4 bg-orange-500 text-white rounded-lg font-semibold hover:bg-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Criar Conta</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
                    Já tem uma conta? <a href="#" id="go-to-login" class="font-medium text-orange-500 hover:text-orange-400">Faça login</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loginTab = document.getElementById('login-tab');
            const registerTab = document.getElementById('register-tab');
            const formsContainer = document.getElementById('auth-forms-container');
            const stepsContainer = document.getElementById('multi-step-container');
            const progressDots = document.querySelectorAll('.progress-dot');
            
            loginTab.addEventListener('click', () => {
                formsContainer.style.transform = 'translateX(0%)';
                loginTab.classList.add('text-orange-500', 'border-b-2', 'border-orange-500');
                loginTab.classList.remove('text-slate-500', 'dark:text-slate-400');
                registerTab.classList.remove('text-orange-500', 'border-b-2', 'border-orange-500');
                registerTab.classList.add('text-slate-500', 'dark:text-slate-400');
            });

            registerTab.addEventListener('click', () => {
                formsContainer.style.transform = 'translateX(-100%)';
                registerTab.classList.add('text-orange-500', 'border-b-2', 'border-orange-500');
                registerTab.classList.remove('text-slate-500', 'dark:text-slate-400');
                loginTab.classList.remove('text-orange-500', 'border-b-2', 'border-orange-500');
                loginTab.classList.add('text-slate-500', 'dark:text-slate-400');
                showStep(1);
            });

            function showStep(stepNum) {
                const n = parseInt(stepNum);
                stepsContainer.style.transform = `translateX(${-(n - 1) * 100}%)`;
                progressDots.forEach(dot => {
                    const d = parseInt(dot.dataset.step);
                    dot.classList.toggle('bg-orange-500', d <= n);
                    dot.classList.toggle('bg-slate-300', d > n);
                    dot.classList.toggle('dark:bg-slate-600', d > n);
                    dot.classList.toggle('active', d === n);
                });
                updateSummary();
            }

            function validateStep(stepId) {
                let valid = true;
                if (stepId === '1') {
                    const fields = ['register-owner-name', 'register-email', 'register-password', 'register-confirm-password', 'register-id-number'];
                    fields.forEach(id => {
                        const el = document.getElementById(id);
                        if (!el.value.trim()) {
                            el.classList.add('border-red-500', 'shake');
                            setTimeout(() => el.classList.remove('shake'), 400);
                            valid = false;
                        } else {
                            el.classList.remove('border-red-500');
                        }
                    });
                    const pwd = document.getElementById('register-password').value;
                    const confirm = document.getElementById('register-confirm-password').value;
                    if (pwd !== confirm) {
                        document.getElementById('register-confirm-password').classList.add('border-red-500', 'shake');
                        setTimeout(() => document.getElementById('register-confirm-password').classList.remove('shake'), 400);
                        valid = false;
                    }
                } else if (stepId === '2') {
                    const fields = ['register-company-name', 'register-company-nif', 'register-company-sector'];
                    fields.forEach(id => {
                        const el = document.getElementById(id);
                        if (!el.value.trim()) {
                            el.classList.add('border-red-500', 'shake');
                            setTimeout(() => el.classList.remove('shake'), 400);
                            valid = false;
                        } else {
                            el.classList.remove('border-red-500');
                        }
                    });
                }
                return valid;
            }

            function updateSummary() {
                document.getElementById('summary-name').textContent = document.getElementById('register-owner-name').value;
                document.getElementById('summary-email').textContent = document.getElementById('register-email').value;
                document.getElementById('summary-company').textContent = document.getElementById('register-company-name').value;
                document.getElementById('summary-nif').textContent = document.getElementById('register-company-nif').value;
            }

            document.getElementById('final-form').addEventListener('submit', (e) => {
                const terms = document.getElementById('terms');
                if (!terms.checked) {
                    e.preventDefault();
                    alert('Deve aceitar os termos e condições para continuar.');
                    return;
                }
                document.getElementById('final-owner-name').value = document.getElementById('register-owner-name').value;
                document.getElementById('final-email').value = document.getElementById('register-email').value;
                document.getElementById('final-password').value = document.getElementById('register-password').value;
                document.getElementById('final-confirm-password').value = document.getElementById('register-confirm-password').value;
                document.getElementById('final-id-number').value = document.getElementById('register-id-number').value;
                document.getElementById('final-company-name').value = document.getElementById('register-company-name').value;
                document.getElementById('final-company-nif').value = document.getElementById('register-company-nif').value;
                document.getElementById('final-company-sector').value = document.getElementById('register-company-sector').value;
            });

            document.getElementById('terms').addEventListener('change', (e) => {
                document.getElementById('create-account-btn').disabled = !e.target.checked;
            });

            document.getElementById('next-to-step-2').addEventListener('click', () => { if (validateStep('1')) showStep(2); });
            document.getElementById('back-to-step-1').addEventListener('click', () => showStep(1));
            document.getElementById('next-to-step-3').addEventListener('click', () => { if (validateStep('2')) showStep(3); });
            document.getElementById('back-to-step-2').addEventListener('click', () => showStep(2));
            document.getElementById('go-to-login').addEventListener('click', (e) => { e.preventDefault(); loginTab.click(); });

            showStep(1);
        });
    </script>
</body>
</html>
