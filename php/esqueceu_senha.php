<?php
require('conectar.php'); // Conexão com o banco de dados
require("functions.php"); // Funções auxiliares

// Inicializar variáveis para mensagens de erro e sucesso
$message = '';
$userID = null; // Inicializa a variável userID

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recuperar o email enviado pelo usuário
    $userEmail = $_POST['userEmail'];

    // Validar o email
    if (!empty($userEmail)) {
        // Verificar se o email existe no banco de dados
        $sql = "SELECT userID, dicaSenha FROM gerenciadorsenhas.users WHERE userEmail = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $userEmail);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($userID, $dicaSenha);
                $stmt->fetch();

                // Enviar a dica de senha para o email
                if (sendDicaSenhaEmail($userEmail, $dicaSenha) === '') {

           // Registrar ação de envio da dica de senha com sucesso
           logAction($conn, $userID, 'Dica de Senha', 'Dica de senha enviada com sucesso para: ' . $userEmail);

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
    $stmt->close();
} else {
    $message = 'Erro ao preparar a consulta.';
}
    } else {
        $message = 'Por favor, insira um email.';
    }
}
?>
