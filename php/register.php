
<?php
require_once __DIR__ . '/functions.php'; // Garante que validarCPF e outras funções estejam disponíveis
// Este arquivo assume que $conn é um objeto de conexão PDO de conectar.php
// e as funções de functions.php estão disponíveis.

$errorMessage = '';
$successMessage = '';

// Função para registrar o usuário
function cadastrarUsuario($conn, $userNome, $userEmail, $userCpf, $userTel, $userPassword, $dicaSenha)
{
    global $errorMessage, $successMessage;

    // Use password_hash para armazenamento seguro de senhas
    $hashPassword = password_hash($userPassword, PASSWORD_DEFAULT);

    // Gerar um token
    $userToken = generateToken($conn); // Supondo que esta função seja compatível com PDO

    try {
        $conn->beginTransaction();

        // Inserir o usuário no banco de dados
        $sql = "INSERT INTO users (userNome, userEmail, userCpf, userTel, userPassword, userToken, dicaSenha, data_fim, plano) VALUES (:nome, :email, :cpf, :tel, :password, :token, :dica, :data_fim, 'básico')";
        $stmt = $conn->prepare($sql);

        $data_fim = date("Y-m-d", strtotime("+1 month")); // Teste de 1 mês para o plano 'básico'

        $stmt->bindParam(':nome', $userNome);
        $stmt->bindParam(':email', $userEmail);
        $stmt->bindParam(':cpf', $userCpf);
        $stmt->bindParam(':tel', $userTel);
        $stmt->bindParam(':password', $hashPassword);
        $stmt->bindParam(':token', $userToken);
        $stmt->bindParam(':dica', $dicaSenha);
        $stmt->bindParam(':data_fim', $data_fim);

        if ($stmt->execute()) {
            $userID = $conn->lastInsertId();

            // Enviar o token por e-mail
            $emailError = sendTokenEmail($userEmail, $userToken); // Supondo que seja compatível com PDO

            if ($emailError) {
                $conn->rollBack();
                $errorMessage = $emailError;
            } else {
                $conn->commit();
                log_action($conn, $userID, 'Registro', 'Novo usuário registrado: ' . $userNome);
                $successMessage = 'Usuário registrado com sucesso! O token foi enviado para o seu e-mail.';
                // Redirecionar para a página de login após um atraso
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 3000);
                </script>";
            }
        } else {
            $conn->rollBack();
            $errorMessage = 'Ocorreu um erro ao registrar o usuário. Por favor, tente novamente.';
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        // Em um ambiente de produção, você pode querer registrar o erro em vez de exibi-lo.
        $errorMessage = 'Erro no banco de dados. Por favor, tente novamente mais tarde.';
        // error_log('Erro de registro: ' . $e->getMessage());
    }
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        $errorMessage = 'Requisição inválida.';
    } else {
        // Recuperar dados do formulário
        $userNome = $_POST['userNome'] ?? '';
        $userEmail = $_POST['userEmail'] ?? '';
        $userCpf = $_POST['userCpf'] ?? null;
        $userTel = $_POST['userTel'] ?? null;
        $userPassword = $_POST['userPassword'] ?? '';
        $userPasswordRepeat = $_POST['userPasswordRepeat'] ?? '';
        $dicaSenha = $_POST['dicaSenha'] ?? null;

    // Validar dados
    if (empty($userNome) || empty($userEmail) || empty($userPassword) || empty($userPasswordRepeat)) {
        $errorMessage = 'Nome, e-mail e senhas são obrigatórios.';
    } elseif ($userPassword !== $userPasswordRepeat) {
        $errorMessage = 'As senhas não coincidem.';
    } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'O e-mail fornecido é inválido.';
    } elseif (!empty($userCpf) && !validarCPF($userCpf)) { // Supondo que validarCPF esteja em functions.php
        $errorMessage = 'CPF inválido. Tente novamente.';
    } else {
        // Verificar se o usuário já existe
        $emailExists = isAlreadyRegistered($conn, 'userEmail', $userEmail);
        $cpfExists = !empty($userCpf) ? isAlreadyRegistered($conn, 'userCpf', $userCpf) : false;
        $telExists = !empty($userTel) ? isAlreadyRegistered($conn, 'userTel', $userTel) : false;

        if ($emailExists) {
            $errorMessage = 'O e-mail informado já está cadastrado.';
        } elseif ($cpfExists) {
            $errorMessage = 'O CPF informado já está cadastrado.';
        } elseif ($telExists) {
            $errorMessage = 'O Telefone informado já está cadastrado.';
        } else {
            // Continuar com o registro
            cadastrarUsuario($conn, $userNome, $userEmail, $userCpf, $userTel, $userPassword, $dicaSenha);
        }
    }
    }
}