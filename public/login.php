<?php
session_start();
$conn = require_once __DIR__ . '/../php/conectar.php';
require_once __DIR__ . '/../php/bootstrap.php';
require_once __DIR__ . '/../php/login.php';

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

    <title>Login</title>
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


    <main class="main-content">
        <section class="hero" style="height: 100vh;">
            <div class="wrapper" style="width:500px">

                <form action="" method="post">
                    <h1 style="margin-bottom: 8vh;">Login</h1>

                    <div class="input-box">
                        <input type="text" id="userCpf" name="userIdent" placeholder="Digite seu CPF ou Token" required
                            value="<?php
                            // Se o formulário foi submetido e o 2FA está ativado, preserva o CPF/Token
                            echo (isset($enableTwoFactor) && $enableTwoFactor == 1 && $_SERVER['REQUEST_METHOD'] == 'POST')
                                ? htmlspecialchars($userIdent)
                                : '';
                            ?>" style="margin: 0 30px 50px 30px;">
                        <br><br>
                    </div>

                    <div class="input-box">
                        <input type="password" id="userPassword" name="userPassword" placeholder="Digite sua senha"
                            required value="<?php
                            // Se o formulário foi submetido e o 2FA está ativado, preserva a senha
                            echo (isset($enableTwoFactor) && $enableTwoFactor == 1 && $_SERVER['REQUEST_METHOD'] == 'POST')
                                ? htmlspecialchars($userPassword)
                                : '';
                            ?>" style="margin: 0 30px 50px 30px;">
                        <span class="toggle-password" toggle="#userPasswordRepeat" style="left: 330px;">
                            <i class="fas fa-eye"></i>
                        </span>
                        <br><br>
                    </div>

                    <?php if (isset($enableTwoFactor) && $enableTwoFactor == 1): ?>
                        <div class="input-box">
                            <input type="text" id="userTwoFactorCode" name="userTwoFactorCode"
                                placeholder="Digite seu código 2FA" required pattern="[0-9]{1,6}" maxlength="6"
                                title="O código deve ter até 6 dígitos e não deve conter espaços ou letras"
                                oninput="this.value = this.value.replace(/\D/g, '').substring(0, 6);">
                            <br><br>
                        </div>
                    <?php endif; ?>



                    <div class="register-link">
                        <p>Não possui uma conta?<br> <a href="./register.php">Registre-se</a></p>
                        <p>Esqueceu a senha?<br> <a href="esqueceu_senha.php">Obter Dica</a></p>
                    </div>

                    <?php if (!empty($errorMessage)): ?>
                        <div
                            style="padding: 10px; color: red; margin-bottom: 7%; font-weight: bold; font-size: 14px; background-color: #fdd; border-radius: 10px;">
                            <?php echo $errorMessage; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($successMessage)): ?>
                        <div
                            style="padding: 10px; color: green; margin-bottom: 7%; font-weight: bold; font-size: 14px; background-color: #ddffe0; border-radius: 10px;">
                            <?php echo $successMessage; ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn"
                        style="margin: 0 0px 0 120px; width:40%; margin-bottom: 4vh;">Login</button>
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
        document.addEventListener('DOMContentLoaded', function () {
            var twoFactorInput = document.getElementById('userTwoFactorCode');
            if (twoFactorInput) {
                twoFactorInput.addEventListener('input', function () {
                    this.value = this.value.replace(/\s/g, ''); // Remove todos os espaços
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Seleciona o campo de senha e o ícone do olho
            const passwordField = document.getElementById('userPassword');
            const togglePasswordButton = document.querySelector('.toggle-password');

            // Verifica se os elementos existem
            if (passwordField && togglePasswordButton) {
                // Adiciona o evento de clique no ícone
                togglePasswordButton.addEventListener('click', function () {
                    // Alterna o tipo do campo entre "password" e "text"
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);

                    // Alterna o ícone entre olho aberto e fechado
                    const icon = togglePasswordButton.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>

    <!--import js-->
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="/script/scroll-reveal.js"></script>
    <script src="/script/preCarregamento.js"></script>
</body>

</html>