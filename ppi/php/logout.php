<?php
// Primeira linha do arquivo, SEM espaços antes
session_start();

// Destrói tudo da sessão ZUADO
$_SESSION = array();

// Se quiser matar o cookie também (recomendado)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão
session_destroy();

// Redireciona imediatamente
header("Location: ../index.php");
exit();
?>