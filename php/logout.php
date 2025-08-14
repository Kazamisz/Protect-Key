<?php
session_start();

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Se existir um cookie de sessão, destruí-lo
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, 
        $params["path"], $params["domain"], 
        $params["secure"], $params["httponly"]
    );
}

//  destruir a sessão
session_destroy();

// Redirecionar para a página de login
header("Location: ../Protect-Key/public/login.php");
exit();
?>
