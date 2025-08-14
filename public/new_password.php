<?php
session_start();
$conn = require_once __DIR__ . '/../php/conectar.php';
require_once __DIR__ . '/../php/bootstrap.php';
require_once __DIR__ . "/../php/functions.php";

// Inicializar variáveis para mensagens de erro e sucesso
$errorMessage = '';
$successMessage = '';
$userID = null;
$dicaSenhaAtual = '';

// Verificar se o usuário está autenticado para redefinição de senha
if (!isset($_SESSION['resetUserID'])) {
    header('Location: login.php');
    exit();
}

// Obter a dica de senha atual
try {
    $userID = $_SESSION['resetUserID'];
    $sql = "SELECT dicaSenha FROM users WHERE userID = :userID";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $dicaSenhaAtual = $result['dicaSenha'];
    }
} catch (PDOException $e) {
    $errorMessage = "Erro ao buscar dados do usuário.";
    // Optional: log the detailed error message
    // error_log($e->getMessage());
}


// Processar a redefinição de senha
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        $errorMessage = 'Requisição inválida.';
    } else {
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    $dicaSenha = !empty($_POST['dicaSenha']) ? $_POST['dicaSenha'] : $dicaSenhaAtual; // Se não preencher, usar a dica atual

    // Validar as senhas
    if (empty($newPassword) || empty($confirmPassword)) {
        $errorMessage = 'As senhas são obrigatórias.';
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = 'As senhas não correspondem.';
    } else {
        // Hash da nova senha
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        try {
            // Atualizar a senha e a dica no banco de dados
            $sql = "UPDATE users SET userPassword = :password, dicaSenha = :dica WHERE userID = :userID";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':dica', $dicaSenha, PDO::PARAM_STR);
            $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                // Deletar o código utilizado
                $deleteCodeSql = "DELETE FROM verification_codes WHERE user_id = :userID";
                $deleteStmt = $conn->prepare($deleteCodeSql);
                $deleteStmt->bindParam(':userID', $userID, PDO::PARAM_INT);
                $deleteStmt->execute();

                // Obter o nome do usuário para o log
                if (!isset($_SESSION['userNome'])) {
                    $sqlNome = "SELECT userNome FROM users WHERE userID = :userID";
                    $stmtNome = $conn->prepare($sqlNome);
                    $stmtNome->bindParam(':userID', $userID, PDO::PARAM_INT);
                    $stmtNome->execute();
                    $userNomeResult = $stmtNome->fetch(PDO::FETCH_ASSOC);
                    if ($userNomeResult) {
                        $_SESSION['userNome'] = $userNomeResult['userNome'];
                    }
                }

                // Log da ação de redefinição de senha
                if(isset($_SESSION['userNome'])) {
                    log_action($conn, $userID, 'Redefinição de Senha', 'Redefiniu a senha para o usuário: ' . $_SESSION['userNome']);
                } else {
                    log_action($conn, $userID, 'Redefinição de Senha', 'Redefiniu a senha para o userID: ' . $userID);
                }

                // Limpar a sessão após redefinição da senha
                unset($_SESSION['resetUserID']);
                $successMessage = 'Senha redefinida com sucesso. Redirecionando para o login em 4 segundos.';

                // Esperar 4 segundos antes de redirecionar
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 4000);
                </script>";
            } else {
                $errorMessage = 'Erro ao atualizar a senha.';
            }
        } catch (PDOException $e) {
            $errorMessage = 'Erro no banco de dados ao atualizar a senha.';
            // error_log($e->getMessage());
        }
    }
}
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="./img/ICON-prokey.ico">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./style/styles.css">
    <link rel="stylesheet" href="./style/styles-loginReg.css">
    <title>Redefinir Senha</title>
</head>

<body>
    <header class="header">
        <nav class="navbar">
            <div class="navbar-container">
                <div class="navbar-left">
                    <!-- Logo -->
                    <div class="logo-container">
                        <a href="index.php"><img src="./img/ProtectKey-LOGOW.png" alt="Protect Key Logo"
                                class="logo"></a>
                        <a href="index.php"><img src="./img/ProtectKey-LOGOB.png" alt="Protect Key Logo Hover"
                                class="logo-hover"></a>
                    </div>

                    <!-- Botão de menu hambúrguer -->
                    <button class="hamburger" id="hamburger">&#9776;</button>

                    <!-- Menu de navegação -->
                    <div class="navbar-menu" id="navbarMenu">
                        <a href="store_password.php" class="navbar-item">Controle de Senhas</a>
                        <a href="gerador_senha.php" class="navbar-item">Gerador de Senhas</a>
                        <a href="planos.php" class="navbar-item">Planos</a>
                        <!--    <a href="#" class="navbar-item">Sobre</a>   -->
                        <a href="envia_contato.php" class="navbar-item">Contate-nos</a>
                        <?php if (isset($conn) && function_exists('checkAdminRole') && isset($userID) && checkAdminRole($conn, $userID)) { ?>
                            <a href="gerenciador.php" class="navbar-item">Gerenciador</a>
                            <a href="logs.php" class="navbar-item">Logs</a>
                        <?php } ?>
                    </div>
                </div>

                <!-- PROFILE ICON -->
                <div class="navbar-right" style="z-index:2;">
                    <details class="dropdown">
                        <summary class="profile-icon">
                            <img src="./img/user.png" alt="Profile" class="user">
                            <img src="./img/user02.png" alt="Profile Hover" class="user-hover">
                        </summary>
                        <div class="dropdown-content">
                            <?php if (isset($_SESSION['userNome'])): ?>
                                <?php
                                // Utiliza strtok para obter a primeira parte antes do espaço
                                $primeiroNome = strtok($_SESSION['userNome'], ' ');
                                ?>
                                <p>Bem-vindo, <?php echo htmlspecialchars($primeiroNome); ?></p>
                                <a href="conta.php"> Detalhes da Conta</a>
                                <a href="/php/logout.php" style="border-radius: 15px;">Sair da Conta</a>

                            <?php else: ?>
                                <p>Bem-vindo!</p>
                                <a href="register.php">Registrar</a>
                                <a href="login.php"
                                    style="border-bottom-left-radius: 15px; border-bottom-right-radius: 15px;"
                                    class="dropdown-content-a2">Login</a>
                            <?php endif; ?>
                        </div>
                    </details>
                </div>
            </div>
        </nav>
    </header>

    <!-- Dentro do corpo do HTML -->
    <main class="main-content">
        <section class="hero" style="height: 100vh;">
            <div class="wrapper" style="height: 70%;">

                <form action="" method="post" onsubmit="return validateForm()">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                    <h1>Redefinir Senha</h1>

                    <div class="input-box">
                        <input type="password" id="newPassword" name="newPassword" placeholder="Nova Senha" required
                            oninput="validatePasswords()">
                    </div>

                    <div class="input-box">
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirmar Senha"
                            required oninput="validatePasswords()">
                    </div>

                    <!-- Adiciona a seção para exibir mensagens dinâmicas -->
                    <div id="messageBox" style="display: none; margin-bottom: 20px;"></div>

                    <div id="lengthMessage" class="error-message" style="display: none; margin-left: 30px;"></div>
                    <div id="uppercaseMessage" class="error-message" style="display: none; margin-left: 30px;"></div>
                    <div id="specialCharMessage" class="error-message" style="display: none; margin-left: 30px;"></div>
                    <div id="passwordMatchMessage" class="error-message" style="display: none; margin-left: 30px;">
                    </div>

                    <div class="input-box">
                        <input type="text" id="dicaSenha" name="dicaSenha" placeholder="Dica de Senha (opcional)" value="<?= htmlspecialchars($dicaSenhaAtual) ?>">
                    </div>

                    <?php if ($errorMessage): ?>
                        <p class='message error'><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                    <?php if ($successMessage): ?>
                        <p class="message success"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                    <p style="font-size: 16px; margin:10px 0 20px 0;">
                        Sua dica de senha atual é: "<?= htmlspecialchars($dicaSenhaAtual) ?>". Se desejar, pode alterá-la.
                    </p>

                    <button type="submit" class="btn">Redefinir Senha</button>
                </form>
            </div>
        </section>
    </main>


    <!--FOOTER-->
    <footer>
        <div class="content">
            <div class="top">
                <div class="logo-details">
                    <a href="#"><img class="logo-footer" src="./img/ProtectKey-LOGOW.png" alt="logo icon"></a>
                </div>
            </div>
            <div class="link-boxes">
                <ul class="box">
                    <li class="link_name">Companhia</li>
                    <li><a href="./index.php">Página Inicial</a></li>
                    <li><a href="./register.php">Começar Agora</a></li>
                    <li><a href="./planos.php">Planos</a></li>
                    <li><a href="./envia_contato.php">Entrar em Contato</a></li>
                </ul>
                <ul class="box">
                    <li class="link_name">Serviços</li>
                    <li><a href="./store_password.php">Gerenciar Senhas</a></li>
                    <li><a href="./gerador_senha.php">Gerar uma Senha</a></li>
                    <li><a href="./store_password.php">Criar uma Senha</a></li>
                </ul>
                <ul class="box">
                    <li class="link_name">Conta</li>
                    <li><a href="./conta.php">Configurações Gerais</a></li>
                    <li><a href="./esqueceu_senha.php">Esqueci Minha Senha</a></li>
                    <li><a href="./conta.php">Alterar Senha</a></li>
                </ul>
                <ul class="box input-box-fot">
                    <li class="link_name">Registre-se</li>
                    <li><input type="text" placeholder="Insira seu E-mail"></li>
                    <li><input type="button" value="Registrar"></li>
                </ul>
            </div>
        </div>
        <div class="bottom-details">
            <div class="bottom_text">
                <span class="copyright_text">Copyright © 2024 <a href="../LICENSE">Protect Key</a>Todos os direitos
                    reservados.</span>
            </div>
        </div>
    </footer>

    <script>
        function validateForm() {
            const errorMessages = document.querySelectorAll('.error-message');
            const messageBox = document.getElementById('messageBox');

            // Verifica se alguma mensagem de erro está visível
            for (const message of errorMessages) {
                if (message.style.display === 'block') {
                    displayMessage('Corrija os erros antes de enviar o formulário.', 'error');
                    return false; // Impede o envio do formulário
                }
            }

            return true; // Permite o envio se não houver erros
        }

        function validatePasswords() {
            checkPasswordMatch();
            checkPasswordCriteria();
        }

        function displayMessage(message, type) {
            const messageBox = document.getElementById('messageBox');
            messageBox.style.display = 'block';
            messageBox.textContent = message;

            if (type === 'error') {
                messageBox.className = 'message error';
            } else if (type === 'success') {
                messageBox.className = 'message success';
            }
        }

        function checkPasswordMatch() {
            const password = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const matchMessage = document.getElementById('passwordMatchMessage');

            if (confirmPassword === '') {
                 matchMessage.style.display = 'none';
                 return;
            }

            if (password === confirmPassword && password !== "") {
                matchMessage.textContent = 'As senhas coincidem.';
                matchMessage.className = 'success-message';
                matchMessage.style.display = 'block';
            } else {
                matchMessage.textContent = 'As senhas não coincidem.';
                matchMessage.className = 'error-message';
                matchMessage.style.display = 'block';
            }
        }

        function checkPasswordCriteria() {
            const password = document.getElementById('newPassword').value;

            const lengthMessage = document.getElementById('lengthMessage');
            const uppercaseMessage = document.getElementById('uppercaseMessage');
            const specialCharMessage = document.getElementById('specialCharMessage');

            if(password === '') {
                lengthMessage.style.display = 'none';
                uppercaseMessage.style.display = 'none';
                specialCharMessage.style.display = 'none';
                return;
            }

            if (password.length >= 12) {
                lengthMessage.textContent = 'A senha tem pelo menos 12 caracteres.';
                lengthMessage.className = 'success-message';
                lengthMessage.style.display = 'block';
            } else {
                lengthMessage.textContent = 'A senha deve ter pelo menos 12 caracteres.';
                lengthMessage.className = 'error-message';
                lengthMessage.style.display = 'block';
            }

            if (/[A-Z]/.test(password)) {
                uppercaseMessage.textContent = 'A senha contém pelo menos uma letra maiúscula.';
                uppercaseMessage.className = 'success-message';
                uppercaseMessage.style.display = 'block';
            } else {
                uppercaseMessage.textContent = 'A senha deve conter pelo menos uma letra maiúscula.';
                uppercaseMessage.className = 'error-message';
                uppercaseMessage.style.display = 'block';
            }

            // Verificar se a senha contém pelo menos um caractere especial
            if (/[\W_]/.test(password)) {
                specialCharMessage.textContent = 'A senha contém pelo menos um caractere especial.';
                specialCharMessage.className = 'success-message';
                specialCharMessage.style.display = 'block';  // Torna visível
            } else {
                specialCharMessage.textContent = 'A senha deve conter pelo menos um caractere especial.';
                specialCharMessage.className = 'error-message';
                specialCharMessage.style.display = 'block';  // Torna visível
            }
        }
    </script>
</body>

</html>