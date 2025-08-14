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

// Verificar se o usuário está autenticado
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

$userID = $_SESSION['userID'];

// Recuperar o nome do usuário
$userNome = '';
$stmt = $conn->prepare("SELECT userNome FROM users WHERE userID = ?");
if ($stmt->execute([$userID])) {
    $userNome = $stmt->fetchColumn();
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        $errorMessage = 'Requisição inválida.';
    } else {
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
                $sql = "INSERT INTO passwords (user_id, site_name, email, name, password, url) VALUES (?, ?, ?, ?, ?, ?)";
                $encryptedPassword = encryptPassword($password, $key, $cipher, $iv_length);
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$userID, $siteName, $email, $loginName, $encryptedPassword, $url])) {
                    $successMessage = 'Senha armazenada com sucesso!';
                    log_action($conn, $userID, 'Adicionar Senha', 'Usuário ' . $userNome . ' adicionou uma senha.');
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $errorMessage = 'Ocorreu um erro ao processar a senha. Por favor, tente novamente.';
                }
            } elseif ($actionType == 'update') {
                // Preparar a consulta SQL para atualizar senha
                $sql = "UPDATE passwords SET site_name = ?, url = ?, email = ?, name = ?, password = ? WHERE senhaId = ? AND user_id = ?";
                $encryptedPassword = encryptPassword($password, $key, $cipher, $iv_length);
                $stmt = $conn->prepare($sql);
                if ($stmt->execute([$siteName, $url, $email, $loginName, $encryptedPassword, $passwordId, $userID])) {
                    $successMessage = 'Senha atualizada com sucesso!';
                    log_action($conn, $userID, 'Atualizar Senha', 'Usuário ' . $userNome . ' atualizou uma senha.');
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $errorMessage = 'Ocorreu um erro ao processar a senha. Por favor, tente novamente.';
                }
            }
        }
    } elseif ($actionType == 'delete') {
        // Preparar a consulta SQL para deletar senha
        $sql = "DELETE FROM passwords WHERE senhaId = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$passwordId, $userID])) {
            $successMessage = 'Senha deletada com sucesso!';
            log_action($conn, $userID, 'Deletar Senha', 'Usuário ' . $userNome . ' deletou uma senha.');
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $errorMessage = 'Ocorreu um erro ao deletar a senha. Por favor, tente novamente.';
        }
    }
    }
}

// Recuperar informações salvas para exibir
$savedPasswords = [];
$sql = "SELECT senhaId, site_name, url, email, name, password FROM passwords WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$userID]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    // Descriptografar a senha para exibição
    $row['password'] = decryptPassword($row['password'], $key, $cipher, $iv_length);
    $savedPasswords[] = $row;
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
