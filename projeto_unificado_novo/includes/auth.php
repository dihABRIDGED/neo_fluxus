<?php
/**
 * Authentication handler - Integração Fluxus + TCC 2.0
 * Funcionalidades do Fluxus com validações do TCC 2.0
 */

require_once '../includes/connection.php';

// Start session for better security
session_start();

// Validate POST data
if (!isset($_POST['login']) || !isset($_POST['senha']) || !isset($_POST['tipoUsuario'])) {
    header('Location: ../public/index.php?error=missing_data');
    exit();
}

$login = $_POST['login'];
$senha = $_POST['senha'];
$tipoUsuario = $_POST['tipoUsuario'];

// Use prepared statements for better security
$stmt = $con->prepare("SELECT * FROM Usuario WHERE login = :login AND tipo = :tipo");
$stmt->bindParam(':login', $login);
// $stmt->bindParam(':senha', $senha); // Senha será verificada após o fetch para lidar com hash/texto puro
$stmt->bindParam(':tipo', $tipoUsuario);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $password_match = false;
    
    // Tenta verificar se a senha é um hash (melhor prática)
    if (password_verify($senha, $user['senha'])) {
        $password_match = true;
    } 
    // A comparação em texto puro foi removida para forçar o uso de password_verify.
    // Se a senha não for um hash válido, o login falhará, o que é o comportamento esperado para senhas não seguras.
    
    if (!$password_match) {
        $user = false;
    }
}

if ($user) {
    // A lógica de primeiro login foi movida para a criação do usuário.
    // O middleware check_password_change.php cuidará do redirecionamento.
    
    // 2. VERIFICAÇÃO DE TROCA DE SENHA E BLOQUEIO
    // Se a senha não foi alterada E já se passaram 3 dias desde o primeiro login
    $bloqueio_ativo = false;
    if ($user['senha_alterada'] == 0 && $user['primeiro_login'] !== null) {
        $data_primeiro_login = new DateTime($user['primeiro_login']);
        $data_limite = $data_primeiro_login->modify('+3 days');
        $hoje = new DateTime();
        
        if ($hoje > $data_limite) {
            $bloqueio_ativo = true;
        }
    }
    
    // Se o bloqueio estiver ativo, redireciona para a página de troca de senha
    if ($bloqueio_ativo) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_login"] = $user["login"];
        $_SESSION["user_type"] = $user["tipo"];
        $_SESSION['logged_in'] = true;
        $_SESSION['force_password_change'] = true;
        
        echo "<script language='javascript' type='text/javascript'>
            alert('Sua senha é temporária e expirou. Você deve alterá-la agora.');
            window.location.href='../public/configuracoes.php'; // Redirecionar para a nova página de configurações
        </script>";
        exit();
    }
    
    // Store user data in session
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["user_login"] = $user["login"];
    $_SESSION["user_type"] = $user["tipo"];
    $_SESSION['logged_in'] = true;
    
    echo "<script language='javascript' type='text/javascript'>
        alert('Login realizado com sucesso! Bem-vindo(a), " . $user["login"] . "');
        window.location.href='../public/home.php'; // Assumindo que home.php é a página principal, ajustei de home_integrated.php para home.php, se for o caso, por favor, me avise.
    </script>";
} else {
    echo "<script language='javascript' type='text/javascript'>
        alert('Usuário inexistente, senha incorreta ou tipo de usuário inválido');
        window.location.href='../public/index.php';
    </script>";
}

$con = null;
?>

