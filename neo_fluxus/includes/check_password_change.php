<?php

/**
 * Middleware para verificar se o usuário precisa trocar a senha.
 * Deve ser incluído no início de todas as páginas restritas, exceto a de troca de senha.
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    
    require_once dirname(__FILE__) . '/connection.php';
    
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("SELECT primeiro_login, senha_alterada FROM Usuario WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $bloqueio_ativo = false;
            
            // 1. Verificar se a senha não foi alterada E já se passaram 3 dias desde o primeiro login
            if ($user['senha_alterada'] == 0 && $user['primeiro_login'] !== null) {
                $data_primeiro_login = new DateTime($user['primeiro_login']);
                $data_limite = $data_primeiro_login->modify('+3 days');
                $hoje = new DateTime();
                
                if ($hoje > $data_limite) {
                    $bloqueio_ativo = true;
                }
            }
            

            // 2. Se o bloqueio estiver ativo, redireciona para a página de troca de senha
            if ($bloqueio_ativo) {
                $_SESSION['force_password_change'] = true;
                header('Location: configuracoes.php');
                exit();
            }
        }
    } catch (PDOException $e) {
        // Em caso de erro no banco, logar e permitir o acesso para não bloquear o usuário
        // Em um ambiente de produção, o erro deveria ser logado.
        // echo "Erro no middleware de senha: " . $e->getMessage();
    }
}

?>
