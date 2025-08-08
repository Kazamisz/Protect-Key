<?php
require_once __DIR__ . '/../vendor/autoload.php';
require("conectar.php");

// Importa as classes do PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Definir a chave de criptografia (deve ser mantida em segredo)
$key = 'my_secret_key'; // A chave deve ter 32 bytes para AES-256
$cipher = "AES-256-CBC"; // Cipher method
$iv_length = openssl_cipher_iv_length($cipher); // Comprimento do vetor de inicialização (IV)

// Função para criptografar uma senha usando AES
function encryptPassword($password, $key, $cipher, $iv_length)
{
    $iv = openssl_random_pseudo_bytes($iv_length);
    $encrypted = openssl_encrypt($password, $cipher, $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Função para descriptografar uma senha usando AES
function decryptPassword($encrypted, $key, $cipher, $iv_length)
{
    list($encrypted_data, $iv) = explode('::', base64_decode($encrypted), 2);
    return openssl_decrypt($encrypted_data, $cipher, $key, 0, $iv);
}


// Função para obter o plano do usuário
function getUserPlan($userID, $conn)
{
    // Preparar a consulta
    $stmt = $conn->prepare("SELECT plano FROM users WHERE userID = ? LIMIT 1");

    // Verificar se a preparação foi bem-sucedida
    if ($stmt === false) {
        die('Erro na preparação da consulta: ' . $conn->error);
    }

    // Vincular o parâmetro
    $stmt->bind_param('i', $userID);

    // Executar a consulta
    $stmt->execute();

    // Buscar o resultado
    $result = $stmt->get_result()->fetch_assoc();

    // Verificar se foi encontrado um plano
    if ($result) {
        return $result['plano'];
    } else {
        return 'Nenhum plano encontrado'; // Valor padrão se nenhum plano for encontrado
    }
}

// Função para obter as informações do usuário
function getUserInfo($conn, $userID)
{
    $sql = "SELECT userNome, userEmail, userCpf, userTel, userEstato, securityWord, enableTwoFactor, dicaSenha, secret FROM users WHERE userID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row;
        }
        $stmt->close();
    }
    return null;
}

// Função para obter a quantidade de senhas salvas pelo usuário
function getPasswordCount($userID, $conn)
{
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM gerenciadorsenhas.passwords WHERE user_id = ?");

    if ($stmt === false) {
        die('Erro na preparação da consulta: ' . $conn->error);
    }

    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result['total'] ?? 0;
}

// Função para gerar um token de 6 dígitos
function generateToken($conn)
{
    do {
        $token = rand(100000, 999999);
        $sql = "SELECT userID FROM gerenciadorsenhas.users WHERE userToken = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $stmt->store_result();
        }
    } while ($stmt->num_rows > 0);
    $stmt->close();
    return $token;
}

// Função principal para enviar o e-mail
function sendEmail($toEmail, $subject, $bodyContent, $altBodyContent = '')
{
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor SMTP
        $mail->CharSet = "UTF-8";
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST']; // Endereço do servidor SMTP
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME']; // Seu nome de usuário do SMTP
        $mail->Password = $_ENV['MAIL_PASSWORD']; // Sua senha do SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['MAIL_PORT'];

        // Configurações do e-mail
        $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], 'Segurança');
        $mail->addAddress($toEmail);

        // Conteúdo do e-mail
        $mail->isHTML(true); // Definir o formato do e-mail como HTML
        $mail->Subject = $subject;
        $mail->Body = $bodyContent;
        $mail->AltBody = $altBodyContent ?: strip_tags($bodyContent); // Alternativo para clientes sem suporte a HTML

        // Envia o e-mail
        $mail->send();
        return ''; // Retorna vazio em caso de sucesso
    } catch (Exception $e) {
        return "Erro ao enviar o e-mail: {$mail->ErrorInfo}"; // Retorna mensagem de erro
    }
}

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
        // Pode haver vários IPs aqui
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ipList[0]); // Retorna o primeiro IP da lista
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
        // Verifica se é o localhost e altera se necessário
        return ($ip === '::1' || $ip === '127.0.0.1') ? 'Localhost' : $ip;
    }
}


function logAction($conn, $userID, $actionType, $description)
{
    $userIp = getUserIP(); // Captura o IP do usuário
    $sql = "INSERT INTO gerenciadorsenhas.logs (user_id, action_type, description, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("isss", $userID, $actionType, $description, $userIp);
        $stmt->execute();
        if ($stmt->error) {
            error_log("Erro ao inserir log: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Erro ao preparar a declaração SQL: " . $conn->error);
    }
}


// Função para verificar se o usuário tem a role de admin
function checkAdminRole($conn, $userID)
{
    $role = ''; // Inicializar a variável para evitar referência indefinida

    // Preparar a consulta SQL
    $sql = "SELECT role FROM users WHERE userID = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $stmt->bind_result($role);
        $stmt->fetch();
        $stmt->close();

        // Verificar se a role é "admin"
        if ($role === 'admin') {
            return true;
        } else {
            return false;
        }
    }
    else {
        return false;
    }
}

// Função para validar o CPF, mesmo com formatação
function validarCPF($userCpf)
{
    // Remove caracteres especiais (pontos, traços, etc.)
    $userCpf = preg_replace('/[^0-9]/', '', $userCpf); // Remove tudo que não for número

    // Verifica se o CPF tem 11 dígitos
    if (strlen($userCpf) != 11) {
        return false;
    }

    // Verifica se todos os dígitos são iguais (exemplo: 111.111.111-11)
    if (preg_match('/(\d)\1{10}/', $userCpf)) {
        return false;
    }

    // Calcula os dígitos verificadores
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

//função para gerar codigo unico
function generateUniqueCode($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Função para verificar se o e-mail ou CPF ou o Tel já estão registrados
function isAlreadyRegistered($conn, $field, $value)
{
    $sql = "SELECT userID FROM gerenciadorsenhas.users WHERE $field = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $stmt->store_result();
        $result = $stmt->num_rows > 0; // Retorna true se já existe no banco
        $stmt->close();
        return $result;
    }
    return false;
}

// Função para gerar um token CSRF
function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

?>