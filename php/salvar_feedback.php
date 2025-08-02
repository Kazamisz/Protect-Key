<?php
require('conectar.php'); // Conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = $_POST['reason'];
    $comments = $_POST['comments'] ?? '';
    $timestamp = date('Y-m-d H:i:s');

    $sql = "INSERT INTO feedback (reason, comments, created_at) VALUES (?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sss", $reason, $comments, $timestamp);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: ../index.php"); // Redirecionar para uma página de agradecimento
    exit();
}
?>
