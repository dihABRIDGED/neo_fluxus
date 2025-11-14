<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php');
    exit();
}

require_once '../includes/connection.php';

$success_message = "";
$error_message = "";

// Processar a troca de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "Todos os campos são obrigatórios.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "A nova senha e a confirmação não coincidem.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "A nova senha deve ter pelo menos 6 caracteres.";
    } else {
        try {
            // 1. Verificar a senha atual
            $stmt = $pdo->prepare("SELECT senha, senha_alterada FROM Usuario WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $error_message = "Erro ao buscar usuário.";
            } else {
                // AVISO: Mantendo a comparação de texto puro para compatibilidade com o código original.
                // O ideal seria usar password_verify.
                if ($user['senha'] !== $current_password) {
                    $error_message = "Senha atual incorreta.";
                } else {
                    // 2. Atualizar a senha e a flag
                    // AVISO: A nova senha está sendo salva em texto puro. O ideal é usar password_hash.
                    $update_stmt = $pdo->prepare("UPDATE Usuario SET senha = ?, senha_alterada = 1 WHERE id = ?");
                    $update_stmt->execute([$new_password, $user_id]);

                    $success_message = "Senha alterada com sucesso! Você pode continuar navegando.";
                    
                    // Remover a flag de forçar troca de senha da sessão
                    unset($_SESSION['force_password_change']);
                }
            }
        } catch (PDOException $e) {
            $error_message = "Erro ao alterar a senha: " . $e->getMessage();
        }
    }
}

// Middleware de Bloqueio (para todas as páginas, exceto esta)
// Este middleware deve ser incluído em todas as páginas que o usuário não deve acessar.
// Como esta é a página de troca, ela não precisa do middleware, mas a lógica de redirecionamento
// em auth.php já garante que o usuário chegue aqui.

$username = $_SESSION['user_login'] ?? 'Usuário';
?>

<?php include '../includes/header.php'; ?>

<div class="main-content">
    <div class="container">
        <div class="page-header">
            <div class="page-title">
                <h1><i class="fas fa-user-cog"></i> Configurações de Usuário</h1>
                <p>Gerencie suas informações e segurança.</p>
            </div>
        </div>

        <div class="card">
            <h2>Troca de Senha</h2>
            
            <?php if (isset($_SESSION['force_password_change'])): ?>
                <div class="alert alert-danger">
                    <strong>ATENÇÃO:</strong> Sua senha temporária expirou. Você <strong>DEVE</strong> alterá-la para continuar.
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="configuracoes.php">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label for="current_password">Senha Atual:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nova Senha:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Nova Senha:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Alterar Senha</button>
            </form>
        </div>
        
        <!-- Outras configurações podem ser adicionadas aqui -->

    </div>
</div>

<?php include '../includes/footer.php'; ?>
