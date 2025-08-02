<?php
session_start();
require("conectar.php");
require("functions.php"); 
require("preference.php"); 

// Verifica se o usuário está logado
if (isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID']; // Obtém o ID do usuário da sessão
    $userPlan = getUserPlan($userID, $conn); // Obtém o plano do usuário
} else {
    $userPlan = 'Não logado'; // Valor padrão se o usuário não estiver logado
    $userID = null; // Inicializa a variável userID
}
?>
