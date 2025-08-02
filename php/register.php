<?php
require('conectar.php');
require('functions.php');

$errorMessage = '';
$successMessage = '';

// Verificar se o formulário foi enviado 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recuperar os dados do formulário
    $userNome = $_POST['userNome'];
    $userEmail = $_POST['userEmail'];
    $userCpf = $_POST['userCpf'] ?? null;
    $userTel = $_POST['userTel'] ?? null;
    $userPassword = $_POST['userPassword'];
    $userPasswordRepeat = $_POST['userPasswordRepeat'];
    $dicaSenha = $_POST['dicaSenha'] ?? null; // Recuperar a dica da senha

    // Validar os dados
    if (empty($userNome) || empty($userEmail) || empty($userPassword) || empty($userPasswordRepeat)) {
        $errorMessage = 'Nome, e-mail e senhas são obrigatórios.';
    } else {
        // Verificar se as senhas coincidem
        if ($userPassword !== $userPasswordRepeat) {
            $errorMessage = 'As senhas não coincidem.';
        } else {
            // Validar e-mail
            if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = 'O e-mail fornecido é inválido.';
            } else {
                    // Verificar se o CPF é válido (se foi informado)
                    if (!empty($userCpf) && !validarCPF($userCpf)) {
                        $errorMessage = 'CPF inválido. Tente novamente.';
                    } else {
                        // Verificar se o e-mail já está registrado
                        if (isAlreadyRegistered($conn, 'userEmail', $userEmail)) {
                            $errorMessage = 'O e-mail informado já está cadastrado.';
                        } elseif (!empty($userCpf) && isAlreadyRegistered($conn, 'userCpf', $userCpf)) {
                            $errorMessage = 'O CPF informado já está cadastrado.';
                        } elseif(!empty($userTel) && isAlreadyRegistered($conn, 'userTel', $userTel)) {
                            $errorMessage = 'O Telefone informado já está cadastrado.';
                        }else {
                            // Continuar com o processo de registro
                            cadastrarUsuario($conn, $userNome, $userEmail, $userCpf, $userTel, $userPassword, $dicaSenha);
                        }
                    }
            }
        }
    }
}

// Função para registrar o usuário
function cadastrarUsuario($conn, $userNome, $userEmail, $userCpf, $userTel, $userPassword, $dicaSenha) {
    global $errorMessage, $successMessage;

    // Criptografar a senha com SHA256 (considere usar password_hash para maior segurança)
    $hashPassword = hash('sha256',$userPassword);

    // Gerar um token de 6 dígitos
    $userToken = generateToken($conn);

    // Inserir o usuário no banco de dados
    $conn->begin_transaction();
    try {
        $sql = "INSERT INTO gerenciadorsenhas.users (userNome, userEmail, userCpf, userTel, userPassword, userToken, dicaSenha, data_fim) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $data_fim = date("Y-m-d", strtotime("+1 month")); // Data de um mês depois
            $stmt->bind_param("ssssssss", $userNome, $userEmail, $userCpf, $userTel, $hashPassword, $userToken, $dicaSenha, $data_fim);

            if ($stmt->execute()) {
                $userID = $stmt->insert_id;

                // Enviar o token por e-mail
                $emailError = sendTokenEmail($userEmail, $userToken);
                if ($emailError) {
                    $conn->rollback();
                    $errorMessage = $emailError;
                } else {
                    $conn->commit();
                    logAction($conn, $userID, 'Registro', 'Novo usuário registrado: ' . $userNome);
                    $successMessage = 'Usuário registrado com sucesso! O token foi enviado para o seu e-mail.';
                }
            } else {
                $conn->rollback();
                $errorMessage = 'Ocorreu um erro ao registrar o usuário. Por favor, tente novamente.';
            }
        } else {
            $conn->rollback();
            $errorMessage = 'Não foi possível preparar a declaração SQL.';
        }
    } catch (Exception $e) {
        $conn->rollback();
        $errorMessage = 'Ocorreu um erro ao registrar o usuário. Por favor, tente novamente.';
    }
}

// Fechar a conexão
$conn->close();

?>
