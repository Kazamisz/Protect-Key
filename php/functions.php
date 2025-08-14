<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Importa as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;  
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Criptografia do cofre: chave deve vir do ambiente e ter 32 bytes (AES-256)
$cipher = 'AES-256-CBC';
$iv_length = openssl_cipher_iv_length($cipher);

// Carrega chave de criptografia com fallback seguro (deriva 32 bytes via SHA-256 binário)
$rawKey = getenv('ENCRYPTION_KEY') ?: '';
$key = strlen((string)$rawKey) === 32 ? $rawKey : hash('sha256', (string)$rawKey, true);

// Função para criptografar uma senha usando AES
function encryptPassword($password, $key, $cipher, $iv_length)
{
    // Gera IV criptograficamente seguro
    $iv = random_bytes($iv_length);
    $encrypted = openssl_encrypt($password, $cipher, $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Função para descriptografar uma senha usando AES
function decryptPassword($encrypted, $key, $cipher, $iv_length)
{
    $decoded = base64_decode($encrypted, true);
    if ($decoded === false) {
        return '';
    }
    $parts = explode('::', $decoded, 2);
    if (count($parts) !== 2) {
        return '';
    }
    [$encrypted_data, $iv] = $parts;
    return openssl_decrypt($encrypted_data, $cipher, $key, 0, $iv) ?: '';
}


// Função para obter o plano do usuário (Sintaxe PDO)
function getUserPlan($userID, $conn)
{
    if (!$conn || !$userID) {
        return 'Não logado';
    }
    try {
        $stmt = $conn->prepare("SELECT plano FROM users WHERE userID = ? LIMIT 1");
        $stmt->execute([$userID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['plano'] : 'Nenhum plano encontrado';
    } catch (PDOException $e) {
        error_log("Erro em getUserPlan: " . $e->getMessage());
        return 'Erro ao buscar plano';
    }
}

// Função para obter as informações do usuário (Sintaxe PDO)
function getUserInfo($conn, $userID)
{
    if (!$conn || !$userID) {
        return null;
    }
    try {
        $sql = "SELECT userNome, userEmail, userCpf, userTel, userEstato, securityWord, enableTwoFactor, dicaSenha, secret FROM users WHERE userID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro em getUserInfo: " . $e->getMessage());
        return null;
    }
}

// Função para obter a quantidade de senhas salvas pelo usuário (Sintaxe PDO)
function getPasswordCount($userID, $conn)
{
    if (!$conn || !$userID) {
        return 0;
    }
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM passwords WHERE user_id = ?");
        $stmt->execute([$userID]);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Erro em getPasswordCount: " . $e->getMessage());
        return 0;
    }
}

// Função para gerar um token de 6 dígitos único (usa CSPRNG)
function generateToken($conn)
{
    if (!$conn) return random_int(100000, 999999);
    try {
        do {
            $token = (string)random_int(100000, 999999);
            $sql = "SELECT COUNT(*) FROM users WHERE userToken = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$token]);
            $count = (int)$stmt->fetchColumn();
        } while ($count > 0);
        return $token;
    } catch (PDOException $e) {
        error_log("Erro em generateToken: " . $e->getMessage());
        return random_int(100000, 999999); // Fallback
    }
}

// Função principal para enviar o e-mail
function sendEmail($toEmail, $subject, $bodyContent, $altBodyContent = '')
{
    $mail = new PHPMailer(true);

    try {
        // Carrega variáveis de ambiente com fallback para $_ENV
        $host = getenv('MAIL_HOST') ?: ($_ENV['MAIL_HOST'] ?? '');
        $username = getenv('MAIL_USERNAME') ?: ($_ENV['MAIL_USERNAME'] ?? '');
        $password = getenv('MAIL_PASSWORD') ?: ($_ENV['MAIL_PASSWORD'] ?? '');
    $port = (int) (getenv('MAIL_PORT') ?: ($_ENV['MAIL_PORT'] ?? 587));
    $encryption = strtolower((string)(getenv('MAIL_ENCRYPTION') ?: ($_ENV['MAIL_ENCRYPTION'] ?? 'tls')));
        $fromAddress = getenv('MAIL_FROM_ADDRESS') ?: ($_ENV['MAIL_FROM_ADDRESS'] ?? '');
        $fromName = getenv('MAIL_FROM_NAME') ?: ($_ENV['MAIL_FROM_NAME'] ?? 'Protect Key');

        // Valida remetente e aplica fallback seguro em dev
        if (!filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            // Fallback local para evitar erro "Invalid address" durante desenvolvimento
            $fromAddress = 'no-reply@protectkey.local';
            error_log('MAIL_FROM_ADDRESS inválido ou ausente. Usando fallback no-reply@protectkey.local');
        }

        // Valida configuração mínima do SMTP
        if (empty($host) || empty($username) || empty($password) || empty($port)) {
            error_log('Configuração SMTP ausente: verifique MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD, MAIL_PORT.');
            return 'Falha ao enviar o e-mail. Configuração SMTP ausente.';
        }

        // Configurações do servidor SMTP
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        if ($encryption === 'ssl' || $port === 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mail->Port = $port;

        // Configurações do e-mail
        $mail->setFrom($fromAddress, $fromName);
        $mail->addAddress($toEmail);

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $bodyContent;
        $mail->AltBody = $altBodyContent ?: strip_tags($bodyContent);

        $mail->send();
        return '';
    } catch (Exception $e) {
        // Não expor detalhes sensíveis ao usuário final
        error_log('Erro ao enviar e-mail: ' . $mail->ErrorInfo);
        return 'Falha ao enviar o e-mail. Tente novamente mais tarde.';
    }
}

// ... (outras funções de envio de email permanecem as mesmas) ...

// Função para enviar o Token por e-mail
function sendTokenEmail($toEmail, $token)
{
    $subject = 'Bem-vindo ao Protect Key - Seu Token de Registro';
    $bodyContent = "<h1>Bem-vindo ao Protect Key!</h1>
                    <p>Agradecemos por se registrar em nosso site. Para completar o seu cadastro, por favor, utilize o seguinte token:</p>
                    <p><strong>Seu Token de Registro:</strong> {$token}</p>
                    <p>Este token é necessário para ativar sua conta. Caso tenha alguma dúvida, não hesite em entrar em contato com nossa equipe de suporte.</p>
                    <p>Atenciosamente,<br>A Equipe Protect Key</p>";

    $altBodyContent = "Bem-vindo ao Protect Key!\n"
        . "Agradecemos por se registrar em nosso site. Para completar o seu cadastro, por favor, utilize o seguinte token:\n"
        . "Seu Token de Registro: $token\n"
        . "Este token é necessário para ativar sua conta. Caso tenha alguma dúvida, não hesite em entrar em contato com nossa equipe de suporte.\n"
        . "Atenciosamente,\nA Equipe Protect Key";
    return sendEmail($toEmail, $subject, $bodyContent, $altBodyContent);
}

// Função para enviar o Código por e-mail
function sendCodigoEmail($toEmail, $codigo)
{
    $subject = 'Código de Atualização de Conta - Protect Key';
    $bodyContent = "<h1>Atualização de Conta - Protect Key</h1>
                    <p>Prezado(a),</p>
                    <p>Você está recebendo este e-mail porque uma solicitação de atualização foi feita em sua conta.</p>
                    <p>Para prosseguir com as alterações, por favor, forneça o seguinte código de atualização ao administrador:</p>
                    <p><strong>Código de Atualização:</strong> {$codigo}</p>
                    <p>Se você não solicitou essa atualização, por favor, entre em contato com nossa equipe de suporte imediatamente.</p>
                    <p>Atenciosamente,<br>A Equipe Protect Key</p>";

    $altBodyContent = "Atualização de Conta - Protect Key\n"
        . "Prezado(a),\n"
        . "Você está recebendo este e-mail porque uma solicitação de atualização foi feita em sua conta.\n"
        . "Para prosseguir com as alterações, por favor, forneça o seguinte código de atualização ao administrador:\n"
        . "Código de Atualização: $codigo\n"
        . "Se você não solicitou essa atualização, por favor, entre em contato com nossa equipe de suporte imediatamente.\n"
        . "Atenciosamente,\nA Equipe Protect Key";

    return sendEmail($toEmail, $subject, $bodyContent, $altBodyContent);
}

// Função para enviar o Código de entrada por e-mail
function sendEntradaEmail($toEmail, $codigo)
{
    $subject = 'Código de Redefinição de Senha - Protect Key';
    $bodyContent = "<h1>Redefinição de Senha - Protect Key</h1>
                <p>Prezado(a) usuário,</p>
                <p>Você está recebendo este e-mail porque solicitou a redefinição de sua senha.</p>
                <p>Para prosseguir, por favor, insira o seguinte código de entrada no campo designado para redefinir sua senha:</p>
                <p><strong>Código de Redefinição:</strong> {$codigo}</p>
                <p>Se você não solicitou essa redefinição, entre em contato com o suporte imediatamente para garantir a segurança da sua conta.</p>
                <p>Atenciosamente,<br>A Equipe Protect Key</p>";

    $altBodyContent = "Redefinição de Senha - Protect Key\n"
        . "Prezado(a) usuário,\n"
        . "Você está recebendo este e-mail porque solicitou a redefinição de sua senha.\n"
        . "Para prosseguir, por favor, insira o seguinte código de entrada no campo designado para redefinir sua senha:\n"
        . "Código de Redefinição: $codigo\n"
        . "Se você não solicitou essa redefinição, entre em contato com o suporte imediatamente para garantir a segurança da sua conta.\n"
        . "Atenciosamente,\nA Equipe Protect Key";

    return sendEmail($toEmail, $subject, $bodyContent, $altBodyContent);
}

// Função para enviar a Dica de Senha por e-mail
function sendDicaSenhaEmail($toEmail, $dicaSenha)
{
    $subject = 'Dica de Senha - Protect Key';
    $bodyContent = "<h1>Recuperação de Dica de Senha - Protect Key</h1>
                <p>Prezado(a) usuário,</p>
                <p>Você solicitou uma dica para ajudar a lembrar sua senha. A dica de sua senha é:</p>
                <p><strong>Dica de Senha:</strong> {$dicaSenha}</p>
                <p>Se você não solicitou essa dica ou suspeita de atividade não autorizada em sua conta, entre em contato com o suporte imediatamente.</p>
                <p>Atenciosamente,<br>A Equipe Protect Key</p>";

    $altBodyContent = "Recuperação de Dica de Senha - Protect Key\n"
        . "Prezado(a) usuário,\n"
        . "Você solicitou uma dica para ajudar a lembrar sua senha. A dica de sua senha é:\n"
        . "Dica de Senha: $dicaSenha\n"
        . "Se você não solicitou essa dica ou suspeita de atividade não autorizada em sua conta, entre em contato com o suporte imediatamente.\n"
        . "Atenciosamente,\nA Equipe Protect Key";

    return sendEmail($toEmail, $subject, $bodyContent, $altBodyContent);
}

function getUserIP()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]);
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
        return ($ip === '::1' || $ip === '127.0.0.1') ? 'Localhost' : $ip;
    }
}

// (Sintaxe PDO)
function log_action($conn, $userID, $actionType, $description)
{
    if (!$conn) return;
    try {
        $userIp = getUserIP();
        $sql = "INSERT INTO logs (user_id, action_type, description, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userID, $actionType, $description, $userIp]);
    } catch (PDOException $e) {
        error_log("Erro ao inserir log: " . $e->getMessage());
    }
}

// Função para verificar se o usuário tem a role de admin (Sintaxe PDO)
function checkAdminRole($conn, $userID)
{
    if (!$conn || !$userID) {
        return false;
    }
    try {
        $sql = "SELECT role FROM users WHERE userID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$userID]);
        $role = $stmt->fetchColumn();
        return ($role === 'admin');
    } catch (PDOException $e) {
        error_log("Erro em checkAdminRole: " . $e->getMessage());
        return false;
    }
}

// Função para validar o CPF, mesmo com formatação
function validarCPF($userCpf)
{
    $userCpf = preg_replace('/[^0-9]/', '', $userCpf);
    if (strlen($userCpf) != 11) {
        return false;
    }
    if (preg_match('/(\d)\1{10}/', $userCpf)) {
        return false;
    }
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $userCpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($userCpf[$c] != $d) {
            return false;
        }
    }
    return true;
}

// Função para gerar código único (CSPRNG)
function generateUniqueCode($length = 10)
{
    $alphabet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $max = strlen($alphabet) - 1;
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $alphabet[random_int(0, $max)];
    }
    return $code;
}

// Função para verificar se o e-mail ou CPF ou o Tel já estão registrados (Sintaxe PDO)
function isAlreadyRegistered($conn, $field, $value)
{
    $allowed_fields = ['userEmail', 'userCpf', 'userTel'];
    if (!$conn || !in_array($field, $allowed_fields)) {
        return false; // Campo não permitido para busca
    }
    try {
        $sql = "SELECT COUNT(*) FROM users WHERE `$field` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$value]);
        return $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Erro em isAlreadyRegistered: " . $e->getMessage());
        return false;
    }
}

// CSRF helpers
function generateCsrfToken()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token)
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

?>