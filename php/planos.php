<?php
// Incluir dependências com caminhos absolutos e únicos
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$conn = require_once __DIR__ . '/conectar.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/preference.php';

// Verifica se o usuário está logado e resolve plano
if (isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID'];
    $userPlan = getUserPlan($userID, $conn);
} else {
    $userPlan = 'Não logado';
    $userID = null;
}
?>
