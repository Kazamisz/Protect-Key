<?php


require('conectar.php');

function logAction($conn, $userID, $actionType, $description) {
    $sql = "INSERT INTO logs (user_id, action_type, description, created_at) VALUES (?, ?, ?, NOW())";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("iss", $userID, $actionType, $description);
        $stmt->execute();
        if ($stmt->error) {
            error_log("Erro ao inserir log: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Erro ao preparar a declaração SQL: " . $conn->error);
    }
}
?>
