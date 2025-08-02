<?php
require('conectar.php');
require("functions.php"); 

$errorMessage = '';
$logs = [];

session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['userID'])) {
    header('Location: ../login.php');
    exit();
}

// Obter a role do usuário
$userID = $_SESSION['userID'];

// Verificar se o usuário é admin
if (!checkAdminRole($conn, $userID)) {
    header('Location: ../index.php');
    exit();
}


// Consultar logs do banco de dados
$sql = "SELECT id, user_id, action_type, description, ip_address, created_at FROM logs ORDER BY created_at DESC";
if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Buscar logs
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    $stmt->close();
} else {
    $errorMessage = 'Não foi possível preparar a consulta.';
}
?>
