<?php
session_start();
require_once __DIR__ . '/../php/bootstrap.php';
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/conta.php';

// Verifica se o usuário está logado
if (isset($_SESSION['userID'])) {
    $userId = $_SESSION['userID']; // Obtém o ID do usuário da sessão
    $userPlan = getUserPlan($userId, $conn); // Obtém o plano do usuário
} else {
    $userPlan = 'Não logado'; // Valor padrão se o usuário não estiver logado
}
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
    <link rel="stylesheet" href="./style/styles-account.css">

    <style>
        #securityWordContainer {
            display: none;
        }

        #qrCodeContainer {
            display: none;
            text-align: center;
            margin: 0 0 0 -150px;
        }

        #qrCodeContainer p {
            margin: 20px;
        }
    </style>

    <title>Configurações da Conta</title>
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

    <div class="settings-page">
        <div class="settings-container card">
            <div class="card__border"></div>
            <h1 class="page-title">Configurações da Conta</h1>

            <!-- Mensagem de sucesso ou erro -->
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message"
                    style="padding: 10px; color: red; width: fit-content; margin: 10px auto 0 auto; font-weight: bold; font-size: 14px; background-color: #fdd; border-radius: 10px;">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($successMessage)): ?>
                <div class="success-message"
                    style="padding: 10px; color: green; width: fit-content; margin: 10px auto 0 auto; font-weight: bold; font-size: 14px; background-color: #ddffe0; border-radius: 10px;">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <form id="updateForm" action="" method="post" class="my-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">

                <!-- Informações Gerais -->
                <div class="settings-section">
                    <h2 class="settings-title">Informações Gerais</h2>

                    <div class="form-group">
                        <div class="input-group">
                            <label class="label" for="userNome">Nome</label>
                            <input type="text" id="userNome" name="userNome" class="input"
                                value="<?php echo htmlspecialchars($userNome); ?>" required autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <label class="label" for="userEmail">Email</label>
                            <input type="email" id="userEmail" name="userEmail" class="input"
                                value="<?php echo htmlspecialchars($userEmail); ?>" required autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <label class="label" for="userCpf">CPF</label>
                            <input type="text" id="userCpf" name="userCpf" class="input"
                                value="<?php echo htmlspecialchars($userCpf ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <label class="label" for="userTel">Telefone</label>
                            <input type="text" id="userTel" name="userTel" class="input"
                                value="<?php echo htmlspecialchars($userTel ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                autocomplete="off">
                        </div>
                    </div>
                </div>

                <!-- Seção de Senha -->
                <div class="settings-section">
                    <h2 class="settings-title">Senha</h2>

                    <div class="form-group">
                        <div class="input-group">
                            <label class="label" for="userPassword" style="margin-left:30px;">Senha</label>

                            <input type="password" id="userPassword" name="userPassword" class="input"
                                placeholder="Deixe em branco para manter a mesma" style="margin-left:30px;">

                            <!-- Botão para alternar a visualização da senha -->
                            <span type="button" id="togglePassword" class="toggle-password" onclick="verSenha()"
                                style="padding-left: 10px; transform: translateY(-50%); cursor: pointer;">
                                <i class="fas fa-eye" id="togglePasswordImage"></i>
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <label class="label" for="dicaSenha">Dica da Senha</label>
                            <input type="text" id="dicaSenha" name="dicaSenha" class="input"
                                value="<?php echo htmlspecialchars($dicaSenha ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>

                    <?php if ($hasSecurityWord): ?>
                        <div class="form-group">
                            <div class="input-group">
                                <label class="label" for="oldSecurityWord" style="margin-left:30px;">Palavra de Segurança
                                    Atual</label>

                                <!-- Input da antiga palavra de segurança -->
                                <div style="position: relative; margin-top: 20px;">
                                    <input type="password" id="oldSecurityWord" name="oldSecurityWord" class="input"
                                        style="padding-right: 40px; margin-left:30px;"
                                        placeholder="Palavra de Segurança Antiga">

                                    <!-- Botão para alternar a visualização -->
                                    <span type="button" id="toggleOldSecurityWord" class="toggle-password"
                                        onclick="toggleOldSecurityWord()"
                                        style="padding-left: 10px; transform: translateY(-50%); cursor: pointer;">
                                        <i class="fas fa-eye" id="toggleOldSecurityWordIcon"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                    <?php endif; ?>

                    <div id="palavra-segura" style="width: fit-content; margin: 0 0 50px 34%;">
                        <label class="label" for="newSecurityWord" style="margin: 10px 0px 0px -60px;">Adicionar/Alterar
                            Palavra de Segurança</label>
                        <label class="container">
                            <!-- Checkbox com PHP -->
                            <input type="checkbox" id="addSecurityWord" name="addSecurityWord" <?php echo $hasSecurityWord ? '' : ''; ?> onchange="toggleSecurityWordField()">
                            <div class="checkmark"></div>
                        </label>
                    </div>

                    <div id="securityWordContainer" class="palavra-segura"
                        style="display: <?php echo $hasSecurityWord ? 'flex' : 'none'; ?>; width: fit-content; flex-direction: column !important; margin: 0 auto;">
                        <label for="newSecurityWord" style="font-size: 18px; font-weight: 600; margin-left:35px;">
                            Nova Palavra de Segurança:
                        </label>
                        <br>
                        <div
                            style="position: relative; margin-top: 20px; margin-left: 35px; display: flex; align-items: center;">
                            <!-- Input de palavra de segurança -->
                            <input type="password" id="newSecurityWord" name="newSecurityWord" class="input"
                                style="padding-right: 40px; flex-grow: 1;">

                            <!-- Botão para alternar a visualização da palavra de segurança -->
                            <span type="button" id="toggleSecurityWord" class="toggle-password"
                                onclick="toggleSecurityWord()"
                                style="padding-left: 20px; padding-bottom: 25px; cursor: pointer;">
                                <i class="fas fa-eye" id="toggleSecurityWordIcon"></i>
                            </span>
                        </div>
                    </div>


                    <div id="palavra-segura" style="width: fit-content; margin: 0 0 50px 40%;">
                        <label class="label" for="newSecurityWord" style="margin: 10px 0px 0px -60px;">Autenticação de
                            Dois Fatores</label>
                        <label class="container" style="margin: 20px 0 30px 58px;">
                            <input type="checkbox" id="enableTwoFactor" name="enableTwoFactor" <?php echo $enableTwoFactor ? 'checked' : ''; ?> onchange="toggleSecurityWordField()">
                            <div class="checkmark"></div>
                        </label>
                    </div>

                    <?php if (!$enableTwoFactor): ?>
                        <div class="form-group">
                            <button type="button" id="showQRCodeButton">Mostrar QR Code</button>
                        </div>

                        <div id="qrCodeContainer" style="display: none; margin: 0 auto;">
                            <p class="label" style="margin: 0 auto 30px auto;">Escaneie o QR code com o Google Authenticator
                            </p>
                            <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code Google Authenticator">
                            <p class="label" style="margin: 40px auto 30px auto;">Ou insira essa chave manualmente:</p>
                            <p class="label"><?php echo htmlspecialchars($secret); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="form-submit">
                        <button type="submit">Confirmar Informações</button>
                    </div>
            </form>

            <form class="form-button" method="POST"
                onsubmit="return confirm('Tem certeza de que deseja desativar sua conta?')">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                <button type="submit" name="deactivateAccount" class="btn btn-warning">Desativar Conta</button>
            </form>
        </div>
    </div>


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



    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>


        document.getElementById("enableTwoFactor").addEventListener("change", function () {
            document.getElementById("initialConfirmButton").style.display = this.checked ? "none" : "inline";
        });

        document.getElementById("showQRCodeButton").addEventListener("click", function () {
            document.getElementById("qrCodeContainer").style.display = "block";
            document.getElementById("initialConfirmButton").style.display = "none";
        });


        document.getElementById('addSecurityWord').addEventListener('change', function () {
            document.getElementById('securityWordContainer').style.display = this.checked ? 'block' : 'none';
        });

        document.getElementById('enableTwoFactor').addEventListener('change', function () {
            const qrCodeContainer = document.getElementById('qrCodeContainer');
            const showQRCodeButton = document.getElementById('showQRCodeButton');

            if (this.checked) {
                showQRCodeButton.style.display = 'block';
                qrCodeContainer.style.display = 'none'; // Initially hide QR code
            } else {
                showQRCodeButton.style.display = 'none';
                qrCodeContainer.style.display = 'none'; // Ensure QR code is hidden
            }
        });


        document.getElementById('showQRCodeButton').addEventListener('click', function () {
            const qrCodeContainer = document.getElementById('qrCodeContainer');
            qrCodeContainer.style.display = 'block';


        });


        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('addSecurityWord').dispatchEvent(new Event('change'));
            document.getElementById('enableTwoFactor').dispatchEvent(new Event('change'));
        });

        $(document).ready(function () {
            // Máscara para CPF
            $('#userCpf').on('input', function () {
                $(this).val($(this).val().replace(/\D/g, '').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{3})(\d)/, '$1.$2-$3'));
            });

            // Máscara para Telefone
            $('#userTel').on('input', function () {
                $(this).val($(this).val().replace(/\D/g, '').replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2'));
            });
        });

        function toggleSecurityWordField() {
            var checkbox = document.getElementById('addSecurityWord');
            var securityWordField = document.getElementById('securityWordContainer'); // Corrigido para securityWordContainer

            if (checkbox.checked) {
                securityWordField.style.display = 'flex'; // Use 'flex' para corresponder ao estilo PHP
            } else {
                securityWordField.style.display = 'none';
            }
        }

        // Inicializa o estado do checkbox ao carregar a página
        document.addEventListener('DOMContentLoaded', function () {
            toggleSecurityWordField();
        });


        function verSenha() {
            const passwordInput = document.getElementById("userPassword");
            const togglePasswordIcon = document.getElementById("togglePasswordImage");

            // Alterna entre os tipos "password" e "text"
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                togglePasswordIcon.classList.remove("fa-eye");
                togglePasswordIcon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                togglePasswordIcon.classList.remove("fa-eye-slash");
                togglePasswordIcon.classList.add("fa-eye");
            }
        }


        function toggleSecurityWord() {
            const securityWordInput = document.getElementById("newSecurityWord");
            const togglePasswordIcon = document.getElementById("toggleSecurityWordIcon");

            if (securityWordInput.type === "password") {
                securityWordInput.type = "text";
                togglePasswordIcon.classList.remove("fa-eye");
                togglePasswordIcon.classList.add("fa-eye-slash");
            } else {
                securityWordInput.type = "password";
                togglePasswordIcon.classList.remove("fa-eye-slash");
                togglePasswordIcon.classList.add("fa-eye");
            }
        }

        function toggleOldSecurityWord() {
            const oldSecurityWordInput = document.getElementById("oldSecurityWord");
            const togglePasswordIcon = document.getElementById("toggleOldSecurityWordIcon");

            if (oldSecurityWordInput.type === "password") {
                oldSecurityWordInput.type = "text";
                togglePasswordIcon.classList.remove("fa-eye");
                togglePasswordIcon.classList.add("fa-eye-slash");
            } else {
                oldSecurityWordInput.type = "password";
                togglePasswordIcon.classList.remove("fa-eye-slash");
                togglePasswordIcon.classList.add("fa-eye");
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/scrollreveal"></script>


    <script>
        // Inicialização do ScrollReveal
        const sr = ScrollReveal({
            reset: true, // As animações ocorrerão sempre que o elemento entrar na viewport
            distance: '50px',
            duration: 1000,
            easing: 'ease-in-out',
        });

        sr.reveal('.page-title', {
            origin: 'top',
            distance: '50px'
        });

        sr.reveal('.settings-title', {
            origin: 'left',
            distance: '50px'
        });

        sr.reveal('.form-group', {
            origin: 'rigth',
            distance: '50px'
        });

        sr.reveal('#palavra-segura', {
            origin: 'left',
            distance: '50px'
        });



        //nav/footer
        // Animações para a Navegação e Hero
        sr.reveal('.navbar-item', {
            origin: 'top',
            distance: '20px',
            interval: 100
        });

        sr.reveal('.logo, .logo-hover', {
            origin: 'left',
            distance: '20px'
        });

        // Animações para o Footer
        sr.reveal('.logo-details', {
            origin: 'top',
            distance: '30px'
        });

        sr.reveal('.box', {
            origin: 'left',
            distance: '50px',
            interval: 200
        });

        sr.reveal('.copyright_text', {
            origin: 'top',
            distance: '30px'
        });


        sr.reveal('.wrapper', {
            origin: 'rigth',
            distance: '50px'
        });


        sr.reveal('.wrapper', {
            origin: 'rigth',
            distance: '50px'
        });


    </script>

    <!--import js-->
    <script src="../script/script2.js"></script>
</body>

</html>