<?php
// Incluir o arquivo de conexão com o banco de dados
require('conectar.php');

// Incluir o arquivo de funções
require_once('functions.php');

// Inicializar variáveis para mensagens de erro e sucesso
$errorMessage = '';
$successMessage = '';

// Definir o limite de tamanho para a senha (em caracteres)
$passwordMaxLength = 255;

session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$userID = $_SESSION['userID'];

// Recuperar o nome do usuário
$userNome = '';
if ($stmt = $conn->prepare("SELECT userNome FROM users WHERE userID = ?")) {
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $stmt->bind_result($userNome);
    $stmt->fetch();
    $stmt->close();
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $actionType = $_POST['actionType'];

    // Recuperar os dados do formulário
    $siteName = isset($_POST['siteName']) ? $_POST['siteName'] : '';
    $url = isset($_POST['url']) ? $_POST['url'] : '';
    $loginName = isset($_POST['loginName']) ? $_POST['loginName'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $passwordId = isset($_POST['passwordId']) ? intval($_POST['passwordId']) : 0;

    // Validar os dados apenas para ações diferentes de deletar
    if ($actionType != 'delete') {
        if (empty($siteName) || empty($password)) {
            $errorMessage = 'O nome do site e a senha são obrigatórios.';
        } elseif (strlen($password) > $passwordMaxLength) {
            // Verificar se a senha excede o limite
            $errorMessage = 'A senha é muito longa. O limite é de ' . $passwordMaxLength . ' caracteres.';
        } else {
            // Preparar SQL e executar com base na ação
            if ($actionType == 'add') {
                // Preparar a consulta SQL para adicionar senha
                $sql = "INSERT INTO gerenciadorsenhas.passwords (user_id, site_name, email, name, password, url) VALUES (?, ?, ?, ?, ?, ?)";
                $encryptedPassword = encryptPassword($password, $key, $cipher, $iv_length);

                // Preparar a declaração
                if ($stmt = $conn->prepare($sql)) {
                    // Vincular parâmetros para adicionar
                    $stmt->bind_param("isssss", $userID, $siteName, $email, $loginName, $encryptedPassword, $url);

                    // Executar a declaração
                    if ($stmt->execute()) {
                        $successMessage = 'Senha armazenada com sucesso!';
                        logAction($conn, $userID, 'Adicionar Senha', 'Usuário ' . $userNome . ' adicionou uma senha.');
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        $errorMessage = 'Ocorreu um erro ao processar a senha. Por favor, tente novamente.';
                    }

                    $stmt->close();
                } else {
                    $errorMessage = 'Não foi possível preparar a declaração SQL.';
                }
            } elseif ($actionType == 'update') {
                // Preparar a consulta SQL para atualizar senha
                $sql = "UPDATE gerenciadorsenhas.passwords SET site_name = ?, url = ?, email = ?, name = ?, password = ? WHERE senhaId = ? AND user_id = ?";
                $encryptedPassword = encryptPassword($password, $key, $cipher, $iv_length);

                // Preparar a declaração
                if ($stmt = $conn->prepare($sql)) {
                    // Vincular parâmetros para atualizar
                    $stmt->bind_param("ssssssi", $siteName, $url, $email, $loginName, $encryptedPassword, $passwordId, $userID);

                    // Executar a declaração
                    if ($stmt->execute()) {
                        $successMessage = 'Senha atualizada com sucesso!';
                        logAction($conn, $userID, 'Atualizar Senha', 'Usuário ' . $userNome . ' atualizou uma senha.');
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        $errorMessage = 'Ocorreu um erro ao processar a senha. Por favor, tente novamente.';
                    }

                    $stmt->close();
                } else {
                    $errorMessage = 'Não foi possível preparar a declaração SQL.';
                }
            }
        }
    } elseif ($actionType == 'delete') {
        // Preparar a consulta SQL para deletar senha
        $sql = "DELETE FROM gerenciadorsenhas.passwords WHERE senhaId = ? AND user_id = ?";

        // Preparar a declaração
        if ($stmt = $conn->prepare($sql)) {
            // Vincular parâmetros para deletar
            $stmt->bind_param("ii", $passwordId, $userID);

            // Executar a declaração
            if ($stmt->execute()) {
                $successMessage = 'Senha deletada com sucesso!';
                logAction($conn, $userID, 'Deletar Senha', 'Usuário ' . $userNome . ' deletou uma senha.');
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $errorMessage = 'Ocorreu um erro ao deletar a senha. Por favor, tente novamente.';
            }

            $stmt->close();
        } else {
            $errorMessage = 'Não foi possível preparar a declaração SQL para deletar.';
        }
    }
}

// Recuperar informações salvas para exibir
$savedPasswords = [];
$sql = "SELECT senhaId, site_name, url, email, name, password FROM gerenciadorsenhas.passwords WHERE user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Descriptografar a senha para exibição
        $row['password'] = decryptPassword($row['password'], $key, $cipher, $iv_length);
        $savedPasswords[] = $row;
    }

    $stmt->close();
}

// Verifica se a imagem existe no sistema de arquivos
$image_path = './front/img/sem-itens.png';
$button_style = 'style="margin: 0 auto 25vh auto;"'; // Estilo padrão

if (file_exists($image_path)) {
    // Se a imagem existir, altera o estilo do botão
    $button_style; // Exemplo de alteração
}

// Recuperar a quantidade de senhas salvas
$totalPasswords = getPasswordCount($userID, $conn);

// Recuperar o plano do usuário
$userPlan = getUserPlan($userID, $conn);


// Verifica se o usuário atingiu o limite de senhas, caso o plano seja 'básico'
$showAddButton = true;
if ($userPlan == 'básico' && $totalPasswords >= 10) {
    $showAddButton = false;
}

?>
