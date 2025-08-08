<?php
require('conectar.php'); // Conexão com o banco de dados
require_once("functions.php");
require __DIR__ . '/../vendor/autoload.php';

use PragmaRX\Google2FA\Google2FA;

// Verificar se o usuário está autenticado
if (!isset($_SESSION['userID'])) {
    header('Location: ../login.php');
    exit();
}

// Obter o ID do usuário a partir da sessão
$userID = $_SESSION['userID'];

// Inicializar variáveis
$securityWord = '';
$hasSecurityWord = false;
$errorMessage = '';
$successMessage = '';
$enableTwoFactor = false;
$secret = '';

// Função para gerar um segredo
function generateSecret()
{
    $google2fa = new Google2FA();
    return $google2fa->generateSecretKey();
}

// Obter as informações do usuário
$userInfo = getUserInfo($conn, $userID);

$userNome = $userInfo['userNome'];
$userEmail = $userInfo['userEmail'];
$userCpf = $userInfo['userCpf'];
$userTel = $userInfo['userTel'];
$userEstato = $userInfo['userEstato'];
$securityWord = $userInfo['securityWord'];
$hasSecurityWord = !empty($securityWord);
$enableTwoFactor = $userInfo['enableTwoFactor'] ?? false;
$dicaSenha = $userInfo['dicaSenha'] ?? '';
$secret = $userInfo['secret'] ?? ''; // Obter o segredo do banco de dados

// Verificar se o segredo já existe, caso contrário, gerá-lo e salvá-lo
if (empty($secret)) {
    $secret = generateSecret();
    // Atualizar o segredo no banco de dados
    $updateSecretSql = "UPDATE users SET secret = ? WHERE userID = ?";
    if ($stmt = $conn->prepare($updateSecretSql)) {
        $stmt->bind_param("si", $secret, $userID);
        $stmt->execute();
        $stmt->close();
    }
}

// Gere a URL do QR code para o Google Authenticator
$google2fa = new \PragmaRX\Google2FA\Google2FA();
$siteName = 'Protect Key'; // Nome que aparecerá no Google Authenticator
$qrCodeData = $google2fa->getQRCodeUrl(
    $siteName,
    $userEmail,
    $secret
);

$renderer = new \BaconQrCode\Renderer\ImageRenderer(
    new \BaconQrCode\Renderer\RendererStyle\RendererStyle(256),
    new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
);
$writer = new \BaconQrCode\Writer($renderer);
$qrCodeUrl = 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($qrCodeData));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deactivateAccount'])) {
    // Apenas redireciona para a página de confirmação de desativação.
    // A lógica de exclusão foi movida para salvar_feedback.php
    header("Location: ../desativacao.php");
    exit();
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Se houver palavra de segurança, verificar se a antiga é correta
    if ($hasSecurityWord) {
        $submittedSecurityWord = trim($_POST['oldSecurityWord'] ?? '');
        if (!password_verify($submittedSecurityWord, $securityWord)) {
            $errorMessage = 'Palavra de segurança incorreta.';
        }
    }

    if (empty($errorMessage)) {
        // Novos dados do usuário
        $newUserNome = $_POST['userNome'];
        $newUserEmail = $_POST['userEmail'];
        $newUserCpf = $_POST['userCpf'] ?? null; // CPF pode ser null
        $newUserTel = $_POST['userTel'] ?? null; // Tel pode ser null
        $newUserPassword = $_POST['userPassword'];
        $newSecurityWord = trim($_POST['newSecurityWord'] ?? ''); // Nova palavra de segurança, se fornecida
        $newDicaSenha = trim($_POST['dicaSenha'] ?? ''); // Nova dica de senha
        $enableTwoFactor = isset($_POST['enableTwoFactor']) ? 1 : 0;

        // Verificar se o email já está em uso por outro usuário
        $emailCheckSql = "SELECT userID FROM users WHERE userEmail = ? AND userID != ?";
        if ($stmt = $conn->prepare($emailCheckSql)) {
            $stmt->bind_param("si", $newUserEmail, $userID);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errorMessage = 'Este email já está em uso por outro usuário.';
            } else {
                // Se o CPF for fornecido, verificar duplicidade
                if (!empty($newUserCpf)) {
                    $cpfCheckSql = "SELECT userID FROM users WHERE userCpf = ? AND userID != ?";
                    if ($stmt = $conn->prepare($cpfCheckSql)) {
                        $stmt->bind_param("si", $newUserCpf, $userID);
                        $stmt->execute();
                        $stmt->store_result();

                        if ($stmt->num_rows > 0) {
                            $errorMessage = 'Este CPF já está em uso por outro usuário.';
                            logAction($conn, $userID, 'Erro Atualização de Conta', 'O usuário ' . $newUserNome . ' tentou atualizar o CPF para um já existente');
                        }
                    }
                }

                // Verificar se o CPF é válido (se foi informado)
                if (!empty($newUserCpf) && !validarCPF($newUserCpf)) {
                    $errorMessage = 'CPF inválido. Tente novamente.';
                } else {
                    // Verificar se o e-mail já está registrado
                    if (isAlreadyRegistered($conn, 'userEmail', $userEmail)) {
                    } elseif (!empty($userCpf) && isAlreadyRegistered($conn, 'userCpf', $userCpf)) {
                        $errorMessage = 'O CPF informado já está cadastrado.';
                    } else {
                    }
                }

                // Continuar a atualização se não houver erros
                if (empty($errorMessage)) {
                    // Hash da senha, se fornecida SHA256
                    if (!empty($newUserPassword)) {
                        $hashedPassword = hash('sha256', $newUserPassword);
                    } else {
                        // Senha permanece a mesma
                        $passwordCheckSql = "SELECT userPassword FROM users WHERE userID = ?";
                        if ($stmt = $conn->prepare($passwordCheckSql)) {
                            $stmt->bind_param("i", $userID);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($row = $result->fetch_assoc()) {
                                $hashedPassword = $row['userPassword'];
                            }
                            $stmt->close();
                        }
                    }

                    // Hash da palavra de segurança, se fornecida
                    // Se a nova palavra de segurança for fornecida, cria-se o hash, caso contrário, mantém-se a palavra existente
                    if (!empty($newSecurityWord)) {
                        $hashedSecurityWord = password_hash($newSecurityWord, PASSWORD_DEFAULT);
                    } else {
                        $hashedSecurityWord = $securityWord; // Mantém a palavra de segurança atual
                    }

                    // Atualização do banco de dados
                    $updateSql = "UPDATE users SET userNome = ?, userEmail = ?, userCpf = ?, userTel = ?, userPassword = ?, securityWord = ?, dicaSenha = ?, enableTwoFactor = ? WHERE userID = ?";
                    if ($stmt = $conn->prepare($updateSql)) {
                        $stmt->bind_param("ssssssssi", $newUserNome, $newUserEmail, $newUserCpf, $newUserTel, $hashedPassword, $hashedSecurityWord, $newDicaSenha, $enableTwoFactor, $userID);
                        $stmt->execute();

                        if ($stmt->affected_rows > 0) {
                            $successMessage = 'Informações atualizadas com sucesso.';
                            logAction($conn, $userID, 'Atualização de Conta', 'Informações atualizadas pelo usuário: ' . $newUserNome);
                            $_SESSION['userNome'] = $newUserNome;

                            // Atualizar variáveis de sessão
                            $userInfo = getUserInfo($conn, $userID);
                            $userNome = $userInfo['userNome'];
                            $userEmail = $userInfo['userEmail'];
                            $userCpf = $userInfo['userCpf'];
                            $userTel = $userInfo['userTel'];
                            $securityWord = $userInfo['securityWord'];
                            $hasSecurityWord = !empty($securityWord);
                            $enableTwoFactor = $userInfo['enableTwoFactor'] ?? false;
                            $dicaSenha = $userInfo['dicaSenha'] ?? '';
                            $secret = $userInfo['secret'] ?? ''; // Atualiza o segredo
                        } else {
                            $errorMessage = 'Nenhuma mudança foi feita.';
                        }
                        $stmt->close();
                    } else {
                        $errorMessage = 'Erro ao atualizar informações.';
                    }
                }
            }
        }
    }
}


?>