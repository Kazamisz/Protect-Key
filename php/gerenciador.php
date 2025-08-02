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
    header('Location: login.php');
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
    $actionType = $_POST['actionType'];

    if ($actionType === 'update') {
        $userID = $_POST['userID'];

        // Buscar o e-mail do usuário
        $emailQuery = $conn->prepare("SELECT userEmail FROM users WHERE userID = ?");
        $emailQuery->bind_param("i", $userID);
        $emailQuery->execute();
        $emailQuery->bind_result($userEmail);
        $emailQuery->fetch();
        $emailQuery->close();

        if ($userEmail) {
            // Gerar código
            $codigo = rand(100000, 999999);

            // Atualizar o código na tabela verification_codes
            $expiryDate = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $updateCodigoQuery = $conn->prepare("INSERT INTO verification_codes (user_id, codigo, created_at, expiry_date) VALUES (?, ?, NOW(), ?)");
            $updateCodigoQuery->bind_param("iis", $userID, $codigo, $expiryDate);
            $updateCodigoQuery->execute();
            $updateCodigoQuery->close();

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
        $codigoQuery->bind_param("is", $userID, $inputCodigo);
        $codigoQuery->execute();
        $codigoQuery->bind_result($storedCodigo);
        $codigoQuery->fetch();
        $codigoQuery->close();

        if ($inputCodigo == $storedCodigo) {
            // Deletar o código após uso
            $deleteCodigoQuery = $conn->prepare("DELETE FROM verification_codes WHERE user_id = ? AND codigo = ?");
            $deleteCodigoQuery->bind_param("is", $userID, $inputCodigo);
            $deleteCodigoQuery->execute();
            $deleteCodigoQuery->close();

            $showEditForm = true;
            $canEdit = true;

            // Buscar dados do usuário para preencher o formulário
            $userQuery = $conn->prepare("SELECT userNome, userEmail, userCpf, userTel, userEstato, role, plano FROM users WHERE userID = ?");
            $userQuery->bind_param("i", $userID);
            $userQuery->execute();
            $userQuery->bind_result($userNome, $userEmail, $userCpf, $userTel, $userEstato, $role, $plano);
            $userQuery->fetch();
            $userQuery->close();

            $userData = [
                'userNome' => $userNome,
                'userEmail' => $userEmail,
                'userCpf' => $userCpf,
                'userTel' => $userTel,
                'userEstato' => $userEstato,
                'role' => $role,
                'plano' => $plano
            ];
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
        $userQuery->bind_param("i", $userID);
        $userQuery->execute();
        $userQuery->bind_result($oldNome, $oldEmail, $oldCpf, $oldTel, $oldEstato, $oldRole, $oldPlano);
        $userQuery->fetch();
        $userQuery->close();

        if ($userNome === $oldNome && $userEmail === $oldEmail && $userCpf === $oldCpf && $userTel === $oldTel && $userEstato === $oldEstato && $role === $oldRole && $plano === $oldPlano) {
            $errorMessage = 'Nenhuma mudança foi feita.';
        } else {
            // Verificar se o e-mail já está em uso por outro usuário
            $emailCheckQuery = $conn->prepare("SELECT userID FROM users WHERE userEmail = ? AND userID != ?");
            $emailCheckQuery->bind_param("si", $userEmail, $userID);
            $emailCheckQuery->execute();
            $emailCheckQuery->bind_result($existingUserID);
            $emailCheckQuery->fetch();
            $emailCheckQuery->close();

                        // Verificar se o CPF já está em uso por outro usuário
                        if (!empty($userCpf)) {
                            $cpfCheckQuery = $conn->prepare("SELECT userID FROM users WHERE userCpf = ? AND userID != ?");
                            $cpfCheckQuery->bind_param("si", $userCpf, $userID);
                            $cpfCheckQuery->execute();
                            $cpfCheckQuery->bind_result($existingCpfUserID);
                            $cpfCheckQuery->fetch();
                            $cpfCheckQuery->close();
                            
                            if ($existingCpfUserID) {
                                $errorMessage = 'Este CPF já está em uso por outro usuário.';
                            }
                        }
            
                        // Verificar se o telefone já está em uso por outro usuário
                        if (!empty($userTel)) {
                            $telCheckQuery = $conn->prepare("SELECT userID FROM users WHERE userTel = ? AND userID != ?");
                            $telCheckQuery->bind_param("si", $userTel, $userID);
                            $telCheckQuery->execute();
                            $telCheckQuery->bind_result($existingTelUserID);
                            $telCheckQuery->fetch();
                            $telCheckQuery->close();
            
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
                logAction($conn, $userID, 'Falha Atualização de Usuario', 'Tentou cadastrar com um email ja existente: ' . $userEmail . ' pelo admin: ' . $adminNome . ' (ID: ' . $admID . ')');
            } else {

                if (empty($errorMessage)) {
                // Atualiza os dados da tabela 'users'
                $updateUserQuery = $conn->prepare("UPDATE users SET userNome = ?, userEmail = ?, userCpf = ?, userTel = ?, userEstato = ?, role = ?, plano = ? WHERE userID = ?");
                $updateUserQuery->bind_param("sssssssi", $userNome, $userEmail, $userCpf, $userTel, $userEstato, $role, $plano, $userID);
                $updateUserQuery->execute();

                // Verifica se as atualizações foram bem-sucedidas
                if ($updateUserQuery->affected_rows > 0) {
                    logAction($conn, $userID, 'Atualização de Usuario', 'Informações Atualizadas: ' . $userNome . ' pelo admin: ' . $adminNome . ' (ID: ' . $admID . ')');
                    $successMessage = 'Informações atualizadas com sucesso.';
                } else {
                    $errorMessage = 'Falha ao alterar as informações do usuario.';
                }

                $updateUserQuery->close();
            }
            }
        }
    }
    } elseif ($actionType === 'search') {
        $searchTerm = $_POST['searchTerm'];
    }
}

// Buscar todos os usuários com base no termo de pesquisa
if ($searchTerm) {
    $searchQuery = $conn->prepare("
        SELECT users.* 
        FROM users 
        WHERE users.userID LIKE ? OR users.userNome LIKE ? OR users.userEmail LIKE ? OR users.userCpf LIKE ? OR users.userTel LIKE ? OR users.userEstato LIKE ? OR users.role LIKE ? OR users.plano LIKE ?");
    $likeTerm = "%$searchTerm%";
    $searchQuery->bind_param("ssssssss", $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm);
    $searchQuery->execute();
    $result = $searchQuery->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $searchQuery->close();

    // Limpar o termo de pesquisa após o processamento
    $searchTerm = '';
    
} else {
    $result = $conn->query("
        SELECT users.* 
        FROM users");
    $users = $result->fetch_all(MYSQLI_ASSOC);
}

if ($actionType === 'sendUniqueCode') {
    $userID = $_POST['userID'];

    // Buscar o e-mail do usuário
    $emailQuery = $conn->prepare("SELECT userEmail FROM users WHERE userID = ?");
    $emailQuery->bind_param("i", $userID);
    $emailQuery->execute();
    $emailQuery->bind_result($userEmail);
    $emailQuery->fetch();
    $emailQuery->close();

    if ($userEmail) {
        // Deletar qualquer código antigo antes de inserir um novo
        $deleteOldCodes = $conn->prepare("DELETE FROM verification_codes WHERE user_id = ?");
        $deleteOldCodes->bind_param("i", $userID);
        $deleteOldCodes->execute();
        $deleteOldCodes->close();

        // Gerar código de 10 dígitos
        $codigo = generateUniqueCode();

        // Consulta para inserir o código
        $sql = "INSERT INTO verification_codes (user_id, codigo, expiry_date) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $expiryDate = date('Y-m-d H:i:s', strtotime('+1 hour')); // Define a data de expiração

            $stmt->bind_param("iss", $userID, $codigo, $expiryDate);
            $stmt->execute();
            $stmt->close();
        } else {
            $errorMessage = 'Erro ao preparar a consulta de inserção de código.';
        }

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

?>
