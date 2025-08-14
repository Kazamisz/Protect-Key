<?php
session_start();
$conn = require_once __DIR__ . '/../php/conectar.php';
require_once __DIR__ . '/../php/bootstrap.php';
require_once __DIR__ . '/../php/register.php';

$userID = null;

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <!--import favicon-->
    <link rel="icon" href="./img/ICON-prokey.ico">

    <!--import googleFonts-->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">

    <!--import font awesome-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!--import css/scroll-->
    <link rel="stylesheet" href="./style/styles.css">
    <link rel="stylesheet" href="./style/styles-loginReg.css">

    <script src="https://unpkg.com/scrollreveal"></script>

    <title>Registro</title>
    <style>
        .error-message {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
        }

        .success-message {
            color: green;
            font-size: 0.9em;
            margin-top: 5px;
        }
    </style>
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

    <main class="main-content">
        <section class="hero">
            <div class="wrapper">
                <form action="" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">

                    <h1>Registro</h1>

                    <div class="input-row">
                        <div class="input-box">
                            <input type="text" id="userNome" name="userNome" placeholder="Digite seu nome*" required>
                        </div>
                        <div class="input-box">
                            <input type="email" id="userEmail" name="userEmail" placeholder="Digite seu e-mail*"
                                required>
                        </div>
                    </div>

                    <div class="input-row">
                        <div class="input-box">
                            <input type="text" id="userCpf" name="userCpf" placeholder="Digite seu CPF*">
                        </div>
                        <div class="input-box">
                            <input type="text" id="userTel" name="userTel" placeholder="Digite seu telefone">
                        </div>
                    </div>

                    <div class="input-row">
                        <div class="input-box">
                            <input type="password" id="userPassword" name="userPassword" placeholder="Digite sua senha*"
                                required>
                            <span class="toggle-password" toggle="#userPassword" title="Mostrar/ocultar senha">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <div class="input-box">
                            <input type="password" id="userPasswordRepeat" name="userPasswordRepeat"
                                placeholder="Repita sua senha*" required>
                            <span class="toggle-password" toggle="#userPasswordRepeat">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>

                    <div class="input-box" style="margin: 10px 0 30px 0;">
                        <input type="text" id="dicaSenha" name="dicaSenha" placeholder="Digite uma dica para a senha*"
                            required style="margin: 0 30px;">
                        <span class="toggle-password" toggle="#dicaSenha" title="Mostrar/ocultar senha">
                        </span>
                        <br><br>
                    </div>

                    <div id="lengthMessage" class="error-message" style="display: none; margin-left: 30px;"></div>
                    <div id="uppercaseMessage" class="error-message" style="display: none; margin-left: 30px;"></div>
                    <div id="specialCharMessage" class="error-message" style="display: none; margin-left: 30px;"></div>
                    <div id="passwordMatchMessage" class="error-message" style="display: none; margin-left: 30px;">
                    </div>

                    <div class="register-link">
                        <p>Já possui uma conta?</p>
                        <a href="login.php">Login</a>
                    </div>

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <p class="message success"><?php echo htmlspecialchars($_SESSION['success_message']); ?></p>
                        <?php unset($_SESSION['success_message']); // Remove a mensagem da sessão após exibi-la ?>
                    <?php endif; ?>

                    <?php if (!empty($errorMessage)): ?>
                        <div
                            style="max-width: 400px; padding: 10px; color: red; margin-bottom: 7%; font-weight: bold; font-size: 14px; background-color: #fdd; border-radius: 10px; margin-left: 20px;">
                            <?php echo $errorMessage; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($successMessage)): ?>
                        <div
                            style="max-width: 400px; padding: 10px; color: green; margin-bottom: 7%; font-weight: bold; font-size: 14px; background-color: #ddffe0; border-radius: 10px; margin-left: 20px;">
                            <?php echo $successMessage; ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn">Registrar</button>
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

    <script src="./script/script2.js"></script>

    <script>
        // Função para verificar se as senhas coincidem
        function checkPasswordMatch() {
            const password = document.getElementById('userPassword').value;
            const passwordRepeat = document.getElementById('userPasswordRepeat').value;
            const matchMessage = document.getElementById('passwordMatchMessage');

            if (password === passwordRepeat && password !== "") {
                matchMessage.textContent = 'As senhas coincidem.';
                matchMessage.className = 'success-message';
                matchMessage.style.display = 'block';  // Torna visível
            } else {
                matchMessage.textContent = 'As senhas não coincidem.';
                matchMessage.className = 'error-message';
                matchMessage.style.display = 'block';  // Torna visível
            }
        }

        // Função para verificar os critérios da senha
        function checkPasswordCriteria() {
            const password = document.getElementById('userPassword').value;

            // Selecionar as divs de mensagens para cada critério
            const lengthMessage = document.getElementById('lengthMessage');
            const uppercaseMessage = document.getElementById('uppercaseMessage');
            const specialCharMessage = document.getElementById('specialCharMessage');

            // Verificar o comprimento da senha
            if (password.length >= 12) {
                lengthMessage.textContent = 'A senha tem pelo menos 12 caracteres.';
                lengthMessage.className = 'success-message';
                lengthMessage.style.display = 'block';  // Torna visível
            } else {
                lengthMessage.textContent = 'A senha deve ter pelo menos 12 caracteres.';
                lengthMessage.className = 'error-message';
                lengthMessage.style.display = 'block';  // Torna visível
            }

            // Verificar se a senha contém pelo menos uma letra maiúscula
            if (/[A-Z]/.test(password)) {
                uppercaseMessage.textContent = 'A senha contém pelo menos uma letra maiúscula.';
                uppercaseMessage.className = 'success-message';
                uppercaseMessage.style.display = 'block';  // Torna visível
            } else {
                uppercaseMessage.textContent = 'A senha deve conter pelo menos uma letra maiúscula.';
                uppercaseMessage.className = 'error-message';
                uppercaseMessage.style.display = 'block';  // Torna visível
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

        // Adicionar event listener aos campos de senha
        document.getElementById('userPassword').addEventListener('input', function () {
            checkPasswordCriteria();  // Verifica os critérios da senha
            checkPasswordMatch();     // Verifica se as senhas coincidem
        });

        document.getElementById('userPasswordRepeat').addEventListener('input', checkPasswordMatch);
    </script>

    <!--import js-->
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="/script/scroll-reveal.js"></script>
    <script src="/script/preCarregamento.js"></script>
</body>

</html>