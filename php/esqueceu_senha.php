<?php
require('conectar.php'); // Conexão com o banco de dados
require("functions.php"); // Funções auxiliares

// Inicializar variáveis para mensagens de erro e sucesso
$message = '';
$userID = null; // Inicializa a variável userID

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        $message = 'Requisição inválida.';
    } else {
    // Recuperar o email enviado pelo usuário
    $userEmail = $_POST['userEmail'];

    // Validar o email
    if (!empty($userEmail)) {
        // Verificar se o email existe no banco de dados
    $sql = "SELECT userID, dicaSenha FROM users WHERE userEmail = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userEmail]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $userID = $result['userID'];
            $dicaSenha = $result['dicaSenha'];

            // Enviar a dica de senha para o email
            if (sendDicaSenhaEmail($userEmail, $dicaSenha) === '') {
                // Registrar ação de envio da dica de senha com sucesso
                log_action($conn, $userID, 'Dica de Senha', 'Dica de senha enviada com sucesso para: ' . $userEmail);

                // Dica enviada com sucesso, redirecionar para o login
                $_SESSION['success_message'] = "Dica de senha enviada para o seu email!"; // Armazena a mensagem na sessão
                header("Location: login.php");
                exit();
            } else {
                $message = 'Erro ao enviar a dica de senha. Tente novamente.';
            }
        } else {
            $message = 'Dica de senha enviada para o seu email!';
        }
    } else {
        $message = 'Erro ao preparar a consulta.';
    }
}
}
