<?php
// File: php/login.php
require_once __DIR__ . '/functions.php';

// Assumes $conn (PDO) and functions from functions.php are available.
// Also assumes Composer's autoloader is included for Google2FA.

use PragmaRX\Google2FA\Google2FA;

$errorMessage = '';
$successMessage = '';
$enableTwoFactor = false; // Initialize to false

function login_user_session($conn, $user)
{
    global $successMessage;

    $_SESSION['userID'] = $user['userID'];
    $_SESSION['userNome'] = $user['userNome'];
    $_SESSION['userEmail'] = $user['userEmail'];

    if ($user['userEstato'] === 'Inativo') {
        $updateStmt = $conn->prepare("UPDATE users SET userEstato = 'Ativo' WHERE userID = :userID");
        $updateStmt->bindParam(':userID', $user['userID'], PDO::PARAM_INT);
        $updateStmt->execute();
    }

    log_action($conn, $user['userID'], 'Login', 'Login bem-sucedido: ' . $user['userNome']);

    $successMessage = 'Logado com sucesso! Redirecionando...';

    echo "<script>
            setTimeout(function(){
                window.location.href = 'store_password.php';
            }, 2000);
        </script>";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userIdent = $_POST['userIdent']; // This can be CPF or Token
    $userPassword = $_POST['userPassword'];
    $userTwoFactorCode = $_POST['userTwoFactorCode'] ?? '';

    if (empty($userIdent) || empty($userPassword)) {
        $errorMessage = 'Identificador e senha são obrigatórios.';
    } else {
        try {

            // Corrigido: usar parâmetros diferentes para userToken e userCpf
            $sql = "SELECT userID, userNome, userPassword, userEmail, userEstato, enableTwoFactor, secret FROM users WHERE userToken = :identToken OR userCpf = :identCpf";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':identToken', $userIdent);
            $stmt->bindParam(':identCpf', $userIdent);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $userID = $user['userID'];
                $storedHash = $user['userPassword'];
                $enableTwoFactor = $user['enableTwoFactor'];
                $secret = $user['secret'];

                // First, check for a temporary verification code for password reset
                $codeStmt = $conn->prepare("SELECT codigo FROM verification_codes WHERE user_id = :userID AND expiry_date > NOW()");
                $codeStmt->bindParam(':userID', $userID, PDO::PARAM_INT);
                $codeStmt->execute();
                $activeCodigo = $codeStmt->fetchColumn();

                if ($activeCodigo && $userPassword === $activeCodigo) {
                    // This is a temporary code login for password reset
                    $_SESSION['resetUserID'] = $userID;
                    header('Location: new_password.php');
                    exit();
                }

                // If not a temp code, verify the actual password
                if (password_verify($userPassword, $storedHash)) {
                    if ($enableTwoFactor) {
                        if (!empty($userTwoFactorCode)) {
                            $google2fa = new Google2FA();
                            if ($google2fa->verifyKey($secret, $userTwoFactorCode)) {
                                // 2FA successful, proceed to login
                                login_user_session($conn, $user);
                            } else {
                                $errorMessage = 'Código 2FA inválido.';
                            }
                        } else {
                            // The frontend should show the 2FA field, but we set a flag to be sure
                            $errorMessage = 'Por favor, digite o código de autenticação.';
                        }
                    } else {
                        // No 2FA, proceed to login
                        login_user_session($conn, $user);
                    }
                } else {
                    $errorMessage = 'Dados inválidos.';
                }
            } else {
                $errorMessage = 'Dados inválidos.';
            }
        } catch (PDOException $e) {
            $errorMessage = 'Erro no banco de dados. Por favor, tente novamente.';
            error_log('Login Error: ' . $e->getMessage());
        }
    }
}
