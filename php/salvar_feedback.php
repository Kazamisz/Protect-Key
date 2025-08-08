<?php
session_start();
require('conectar.php');
require('logAction.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userID = $_SESSION['user_id'] ?? null;
    $userID = filter_var($userID, FILTER_VALIDATE_INT);

    if (!$userID) {
        error_log("Tentativa de exclusão de conta sem um userID válido na sessão.");
        header("Location: ../index.php?error=invalid_session");
        exit();
    }

    // Iniciar transação
    $conn->begin_transaction();

    try {
        // Log do feedback antes da exclusão
        $reason = $_POST['reason'] ?? 'N/A';
        $comments = $_POST['comments'] ?? '';
        $actionType = 'Exclusão de Conta';
        $description = "Usuário ID $userID solicitou exclusão. Motivo: $reason. Comentários: $comments";
        logAction($conn, $userID, $actionType, $description);

        // Ordem de exclusão para respeitar as chaves estrangeiras
        $tablesToDeleteFrom = ['passwords', 'documents', 'verification_codes', 'logs'];

        foreach ($tablesToDeleteFrom as $table) {
            $sql = "DELETE FROM `$table` WHERE `user_id` = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Erro ao preparar a exclusão para a tabela $table: " . $conn->error);
            $stmt->bind_param("i", $userID);
            if (!$stmt->execute()) throw new Exception("Erro ao executar a exclusão para a tabela $table: " . $stmt->error);
            $stmt->close();
        }

        // Finalmente, excluir o usuário da tabela principal
        $sql = "DELETE FROM `users` WHERE `userID` = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Erro ao preparar a exclusão do usuário: " . $conn->error);
        $stmt->bind_param("i", $userID);
        if (!$stmt->execute()) throw new Exception("Erro ao executar a exclusão do usuário: " . $stmt->error);
        $stmt->close();

        // Se tudo correu bem, comitar a transação
        $conn->commit();

    } catch (Exception $e) {
        // Se algo deu errado, reverter a transação
        $conn->rollback();
        // Registrar o erro detalhado
        error_log("Falha na exclusão da conta: " . $e->getMessage());
        // Redirecionar com uma mensagem de erro genérica
        header("Location: ../conta.php?error=delete_failed");
        exit();
    }

    // Destruir a sessão e redirecionar para a página inicial
    session_destroy();
    header("Location: ../index.php?account_deleted=true");
    exit();
}
?>
