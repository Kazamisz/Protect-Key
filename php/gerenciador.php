<?php
require('conectar.php');
require("functions.php"); 

$errorMessage = '';
$successMessage = '';
$showCodeForm = false;
$showEditForm = false;
$userToEdit = null;
$canEdit = false;
$userData = [];
$searchTerm = '';
$actionType = '';

session_start();

// Verificar se o usuário está autenticado
if (!isset($_SESSION['userID'])) {
    header('Location: /login.php');
    exit();
}

// Obter a role do usuário
$userID = $_SESSION['userID'];

// Verificar se o usuário é admin
if (!checkAdminRole($conn, $userID)) {
    header('Location: index.php');
    exit();
} 

// Verifica o id do adm e o nome do adm
$admID = $_SESSION['userID'];
$adminNome = $_SESSION['userNome'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        $errorMessage = 'Requisição inválida.';
    } else {
    $actionType = $_POST['actionType'];

    if ($actionType === 'update') {
        $userID = $_POST['userID'];

        // Buscar o e-mail do usuário
        $emailQuery = $conn->prepare("SELECT userEmail FROM users WHERE userID = ?");
        $emailQuery->execute([$userID]);
        $userEmail = $emailQuery->fetchColumn();

        if ($userEmail) {
            // Gerar código (CSPRNG)
            $codigo = random_int(100000, 999999);

            // Atualizar o código na tabela verification_codes
            $expiryDate = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $updateCodigoQuery = $conn->prepare("INSERT INTO verification_codes (user_id, codigo, created_at, expiry_date) VALUES (?, ?, NOW(), ?)");
            $updateCodigoQuery->execute([$userID, $codigo, $expiryDate]);

            // Enviar o código para o e-mail
            $mailResult = sendCodigoEmail($userEmail, $codigo);
            if ($mailResult === '') {
                $successMessage = 'Código enviado com sucesso para o e-mail do usuário.';
                $showCodeForm = true;
                $userToEdit = $userID;
            } else {
                $errorMessage = $mailResult;
            }
        } else {
            $errorMessage = 'Usuário não encontrado.';
        }
    } elseif ($actionType === 'verifyCode') {
        $userID = $_POST['userID'];
        $inputCodigo = $_POST['codigo'];

        // Buscar o código do usuário
        $codigoQuery = $conn->prepare("SELECT codigo FROM verification_codes WHERE user_id = ? AND codigo = ? AND expiry_date > NOW()");
        $codigoQuery->execute([$userID, $inputCodigo]);
        $storedCodigo = $codigoQuery->fetchColumn();

        if ($inputCodigo == $storedCodigo) {
            // Deletar o código após uso
            $deleteCodigoQuery = $conn->prepare("DELETE FROM verification_codes WHERE user_id = ? AND codigo = ?");
            $deleteCodigoQuery->execute([$userID, $inputCodigo]);

            $showEditForm = true;
            $canEdit = true;

            // Buscar dados do usuário para preencher o formulário
            $userQuery = $conn->prepare("SELECT userNome, userEmail, userCpf, userTel, userEstato, role, plano FROM users WHERE userID = ?");
            $userQuery->execute([$userID]);
            $userData = $userQuery->fetch(PDO::FETCH_ASSOC);
        } else {
            $errorMessage = 'Código incorreto ou expirado.';
        }
    } elseif ($actionType === 'saveChanges') {
        $userID = $_POST['userID'];
        $userNome = $_POST['userNome'];
        $userEmail = $_POST['userEmail'];
        $userCpf = $_POST['userCpf'] ?? null;
        $userTel = $_POST['userTel'] ?? null;
        $userEstato = $_POST['userEstato'];
        $role = $_POST['role'];
        $plano = $_POST['plano'];

        // Verificar e atualizar apenas os campos que mudaram
        $userQuery = $conn->prepare("SELECT userNome, userEmail, userCpf, userTel, userEstato, role, plano FROM users WHERE userID = ?");
        $userQuery->execute([$userID]);
        $oldData = $userQuery->fetch(PDO::FETCH_ASSOC);

        if ($userNome === $oldData['userNome'] && $userEmail === $oldData['userEmail'] && $userCpf === $oldData['userCpf'] && $userTel === $oldData['userTel'] && $userEstato === $oldData['userEstato'] && $role === $oldData['role'] && $plano === $oldData['plano']) {
            $errorMessage = 'Nenhuma mudança foi feita.';
        } else {
            // Verificar se o e-mail já está em uso por outro usuário
            $emailCheckQuery = $conn->prepare("SELECT userID FROM users WHERE userEmail = ? AND userID != ?");
            $emailCheckQuery->execute([$userEmail, $userID]);
            $existingUserID = $emailCheckQuery->fetchColumn();

            // Verificar se o CPF já está em uso por outro usuário
            if (!empty($userCpf)) {
                $cpfCheckQuery = $conn->prepare("SELECT userID FROM users WHERE userCpf = ? AND userID != ?");
                $cpfCheckQuery->execute([$userCpf, $userID]);
                $existingCpfUserID = $cpfCheckQuery->fetchColumn();
                if ($existingCpfUserID) {
                    $errorMessage = 'Este CPF já está em uso por outro usuário.';
                }
            }

            // Verificar se o telefone já está em uso por outro usuário
            if (!empty($userTel)) {
                $telCheckQuery = $conn->prepare("SELECT userID FROM users WHERE userTel = ? AND userID != ?");
                $telCheckQuery->execute([$userTel, $userID]);
                $existingTelUserID = $telCheckQuery->fetchColumn();
                if ($existingTelUserID) {
                    $errorMessage = 'Este telefone já está em uso por outro usuário.';
                }
            }

            // Verificar se o CPF é válido (se foi informado)
            if (!empty($userCpf) && !validarCPF($userCpf)) {
                $errorMessage = 'CPF inválido. Tente novamente.';
            } else {
                if ($existingUserID) {
                    // O e-mail já está em uso por outro usuário
                    $errorMessage = 'Este e-mail já está em uso por outro usuário.';
                    log_action($conn, $userID, 'Falha Atualização de Usuario', 'Tentou cadastrar com um email ja existente: ' . $userEmail . ' pelo admin: ' . $adminNome . ' (ID: ' . $admID . ')');
                } else {
                    if (empty($errorMessage)) {
                        // Atualiza os dados da tabela 'users'
                        $updateUserQuery = $conn->prepare("UPDATE users SET userNome = ?, userEmail = ?, userCpf = ?, userTel = ?, userEstato = ?, role = ?, plano = ? WHERE userID = ?");
                        $updateUserQuery->execute([$userNome, $userEmail, $userCpf, $userTel, $userEstato, $role, $plano, $userID]);

                        if ($updateUserQuery->rowCount() > 0) {
                            log_action($conn, $userID, 'Atualização de Usuario', 'Informações Atualizadas: ' . $userNome . ' pelo admin: ' . $adminNome . ' (ID: ' . $admID . ')');
                            $successMessage = 'Informações atualizadas com sucesso.';
                        } else {
                            $errorMessage = 'Falha ao alterar as informações do usuario.';
                        }
                    }
                }
            }
        }
    } elseif ($actionType === 'search') {
        $searchTerm = $_POST['searchTerm'];
    } elseif ($actionType === 'sendUniqueCode') {
        $userID = $_POST['userID'];

        // Buscar o e-mail do usuário
        $emailQuery = $conn->prepare("SELECT userEmail FROM users WHERE userID = ?");
        $emailQuery->execute([$userID]);
        $userEmail = $emailQuery->fetchColumn();

        if ($userEmail) {
            // Deletar qualquer código antigo antes de inserir um novo
            $deleteOldCodes = $conn->prepare("DELETE FROM verification_codes WHERE user_id = ?");
            $deleteOldCodes->execute([$userID]);

            // Gerar código de 10 dígitos
            $codigo = generateUniqueCode();

            // Consulta para inserir o código
            $sql = "INSERT INTO verification_codes (user_id, codigo, expiry_date) VALUES (?, ?, ?)`";
            $expiryDate = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $conn->prepare($sql);
            $stmt->execute([$userID, $codigo, $expiryDate]);

            // Enviar o código para o e-mail usando a função sendCodigoEmail
            $mailResult = sendEntradaEmail($userEmail, $codigo);
            if ($mailResult === '') {
                $successMessage = 'Código enviado com sucesso para o e-mail do usuário.';
            } else {
                $errorMessage = 'Falha ao enviar o código: ' . $mailResult;
            }
        } else {
            $errorMessage = 'E-mail do usuário não encontrado.';
        }
    }
    }
}

// Buscar todos os usuários com base no termo de pesquisa
if ($searchTerm) {
    $searchQuery = $conn->prepare("
        SELECT users.* 
        FROM users 
        WHERE users.userID LIKE ? OR users.userNome LIKE ? OR users.userEmail LIKE ? OR users.userCpf LIKE ? OR users.userTel LIKE ? OR users.userEstato LIKE ? OR users.role LIKE ? OR users.plano LIKE ?");
    $likeTerm = "%$searchTerm%";
    $params = array_fill(0, 8, $likeTerm);
    $searchQuery->execute($params);
    $users = $searchQuery->fetchAll(PDO::FETCH_ASSOC);

    // Limpar o termo de pesquisa após o processamento
    $searchTerm = '';
} else {
    $result = $conn->query("
        SELECT users.* 
        FROM users");
    $users = $result->fetchAll(PDO::FETCH_ASSOC);
}

// (branch sendUniqueCode agora tratado no bloco POST acima)

?>
