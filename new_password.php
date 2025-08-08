<?php
require_once __DIR__ . '/php/bootstrap.php';
require('./php/conectar.php');
require("./php/functions.php");

// Inicializar variáveis para mensagens de erro e sucesso
$errorMessage = '';
$successMessage = '';
$userID = null;

// Verificar se o usuário está autenticado para redefinição de senha
session_start();
if (!isset($_SESSION['resetUserID'])) {
    header('Location: ../login.php');
    exit();
}

// Obter a dica de senha atual
$userID = $_SESSION['resetUserID'];
$sql = "SELECT dicaSenha FROM gerenciadorsenhas.users WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$stmt->bind_result($dicaSenhaAtual);
$stmt->fetch();
$stmt->close();

// Processar a redefinição de senha
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];
    $dicaSenha = $_POST['dicaSenha'] ?: $dicaSenhaAtual; // Se não preencher, usar a dica atual

    // Validar as senhas
    if (empty($newPassword) || empty($confirmPassword)) {
        $errorMessage = 'As senhas são obrigatórias.';
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = 'As senhas não correspondem.';
    } else {
        // Hash da nova senha
        $hashedPassword = hash('sha256', $newPassword);

        // Atualizar a senha e a dica no banco de dados
        $sql = "UPDATE gerenciadorsenhas.users SET userPassword = ?, dicaSenha = ? WHERE userID = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $hashedPassword, $dicaSenha, $userID);
            if ($stmt->execute()) {
                // Deletar o código utilizado
                $deleteCodeSql = "DELETE FROM verification_codes WHERE user_id = ?";
                $deleteStmt = $conn->prepare($deleteCodeSql);
                $deleteStmt->bind_param("i", $userID);
                $deleteStmt->execute();
                $deleteStmt->close();

                // Obter o nome do usuário com base no userID se não estiver na sessão
                if (!isset($_SESSION['userNome'])) {
                    $userID = $_SESSION['resetUserID'];

                    $sql = "SELECT userNome FROM gerenciadorsenhas.users WHERE userID = ?";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("i", $userID);
                        $stmt->execute();
                        $stmt->bind_result($userNome);

                        if ($stmt->fetch()) {
                            // Definir o nome do usuário na sessão
                            $_SESSION['userNome'] = $userNome;
                        }

                        // Agora podemos fechar o stmt após usar todos os resultados
                        $stmt->close();
                    }
                }

                // Agora você pode fazer o log da ação de redefinição de senha
                logAction($conn, $userID, 'Redefinição de Senha', 'Redefiniu a senha:' . $_SESSION['userNome'] . ') ');


                // Limpar a sessão após redefinição da senha
                unset($_SESSION['resetUserID']);
                $successMessage = 'Senha redefinida com sucesso. Direcionando para o login em 3 segundos.';

                // Esperar 3 segundos antes de redirecionar
                echo "<script>
                    setTimeout(function() {
                        window.location.href = '../login.php';
                    }, 4000);
                </script>";
            } else {
                $errorMessage = 'Erro ao atualizar a senha.';
            }
        } else {
            $errorMessage = 'Erro ao preparar a consulta SQL.';
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
                        <?php if (checkAdminRole($conn, $userID)) { ?>
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
                                <p>Bem-vindo, <?php echo $primeiroNome; ?></p>
                                <a href="conta.php"> Detalhes da Conta</a>
                                <a href="./php/logout.php" style="border-radius: 15px;">Sair da Conta</a>

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
                        <input type="text" id="dicaSenha" name="dicaSenha" placeholder="Dica de Senha">
                    </div>

                    <?php if ($errorMessage): ?>
                        <p class='message error'><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                    <?php if ($successMessage): ?>
                        <p class="message success"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></p>
                    <?php endif; ?>

                    <p style="font-size: 16px; margin:10px 0 20px 0;">
                        Recomendamos que salve uma nova dica da sua senha caso esqueça sua nova senha.
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
                    <li><a href="#">Página Inicial</a></li>
                    <li><a href="./register.php">Começar Agora</a></li>
                    <li><a href="./planos.php">Planos</a></li>
                    <li><a href="./envia_contato.php">Entrar em Contato</a></li>
                </ul>
                <ul class="box">
                    <li class="link_name">Serviços</li>
                    <li><a href="./store_password.php">Gerenciar Senhas</a></li>
                    <li><a href="./store_password.php">Gerar uma Senha</a></li>
                    <li><a href="./store_password.php">Criar uma Senha</a></li>
                    <li><a href="./store_password.php">Inserir um Documento</a></li>
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
                <span class="copyright_text">Copyright © 2024 <a href="#">Protect Key</a>Todos os direitos
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