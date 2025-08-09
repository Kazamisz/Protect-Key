<?php
session_start();
require_once __DIR__ . '/../php/bootstrap.php';
require_once __DIR__ . '/../php/conectar.php';
require_once __DIR__ . '/../php/functions.php';
require_once __DIR__ . '/../php/preference.php';

// Verifica se o usuário está logado
if (isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID']; // Obtém o ID do usuário da sessão
    $userPlan = getUserPlan($userID, $conn); // Obtém o plano do usuário
} else {
    $userPlan = 'Não logado'; // Valor padrão se o usuário não estiver logado
    $userID = null; // Inicializa a variável userID
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
    <link rel="stylesheet" href="./style/styles-faq.css">
    <link rel="stylesheet" href="./style/styles-background.css">
    <link rel="stylesheet" href="./style/styles-carregamento.css">

    <title>Protect Key</title>
</head>

<body>

    <div class="pre-carregamento" id="pre-carregamento">
        <div class="card">
            <div class="loader">
                <p>Carregando</p>
                <div class="words">
                    <span class="word"></span>
                    <span class="word">Criptografia de Ponta</span>
                    <span class="word">Autenticação Multifatores</span>
                    <span class="word">Proteção de Dados</span>
                    <span class="word">Segurança na Web</span>
                </div>
            </div>
        </div>

        <div class="SPINNER">
            <div class="spinner">
                <div class="spinner1"></div>
            </div>
        </div>
    </div>


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

    <main class="main-content">
        <section class="hero">
            <div class="content">
                <img src="./img/background01.png" alt="Imagem do Protect Key">

                <div class="text">
                    <h1>Seu Gerenciador de Senhas Confiável <svg xmlns="http://www.w3.org/2000/svg" width="65"
                            height="auto" viewBox="0 0 45 45">
                            <polygon fill="#42a5f5"
                                points="29.62,3 33.053,8.308 39.367,8.624 39.686,14.937 44.997,18.367 42.116,23.995 45,29.62 39.692,33.053 39.376,39.367 33.063,39.686 29.633,44.997 24.005,42.116 18.38,45 14.947,39.692 8.633,39.376 8.314,33.063 3.003,29.633 5.884,24.005 3,18.38 8.308,14.947 8.624,8.633 14.937,8.314 18.367,3.003 23.995,5.884">
                            </polygon>
                            <polygon fill="#fff"
                                points="21.396,31.255 14.899,24.76 17.021,22.639 21.428,27.046 30.996,17.772 33.084,19.926">
                            </polygon>
                        </svg>
                    </h1>
                    <p>Mantenha suas senhas seguras e acessíveis com o Protect Key. Armazene, gerencie e compartilhe
                        suas senhas de maneira segura, onde quer que esteja.</p>
                    <div class="hero-buttons">
                        <?php if (isset($_SESSION['userNome'])): ?>
                            <a href="store_password.php" class="btn btn-primary">Salvar Senha</a>
                            <a href="planos.php" class="btn btn-secondary" >Ver planos e preços</a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-primary">Iniciar um teste gratuito</a>
                            <a href="planos.php" class="btn btn-secondary">Ver planos e preços</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>



        <!-- Seção de Recursos -->
        <section class="features area">
            <div>
                <img src="./img/recursos-principais.png" alt="Recursos Principais" class="recursos-img">
            </div>

            <!-- Div para o fundo azul com cantos arredondados -->
            <div class="features blue-background">
                <!-- Título da seção "Recursos Principais" -->

                <!-- Lista de funcionalidades principais -->
                <div class="feature-list">
                    <!-- Item de funcionalidade individual -->
                    <div class="feature-item">
                        <h3>Segurança de Nível Militar</h3>
                        <img src="./img/seguranca-icon.png" alt="Icone de Segurança">
                        <p>• Proteja suas senhas com criptografia avançada e autenticação multifator.</p>
                        <ul>
                            <li><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20"
                                    viewBox="0,0,256,256">
                                    <g fill="#FFFFFF" fill-rule="nonzero" stroke="none" stroke-width="1"
                                        stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10"
                                        stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none"
                                        font-size="none" text-anchor="none" style="mix-blend-mode: normal">
                                        <g transform="scale(5.12,5.12)">
                                            <path
                                                d="M41.9375,8.625c-0.66406,0.02344 -1.27344,0.375 -1.625,0.9375l-18.8125,28.78125l-12.1875,-10.53125c-0.52344,-0.54297 -1.30859,-0.74609 -2.03125,-0.51953c-0.71875,0.22266 -1.25391,0.83203 -1.37891,1.57422c-0.125,0.74609 0.17578,1.49609 0.78516,1.94531l13.9375,12.0625c0.4375,0.37109 1.01563,0.53516 1.58203,0.45313c0.57031,-0.08594 1.07422,-0.41016 1.38672,-0.89062l20.09375,-30.6875c0.42969,-0.62891 0.46484,-1.44141 0.09375,-2.10547c-0.37109,-0.66016 -1.08594,-1.05469 -1.84375,-1.01953z">
                                            </path>
                                        </g>
                                    </g>
                                </svg> Criptografia AES-256</li>
                            <li><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20"
                                    viewBox="0,0,256,256">
                                    <g fill="#FFFFFF" fill-rule="nonzero" stroke="none" stroke-width="1"
                                        stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10"
                                        stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none"
                                        font-size="none" text-anchor="none" style="mix-blend-mode: normal">
                                        <g transform="scale(5.12,5.12)">
                                            <path
                                                d="M41.9375,8.625c-0.66406,0.02344 -1.27344,0.375 -1.625,0.9375l-18.8125,28.78125l-12.1875,-10.53125c-0.52344,-0.54297 -1.30859,-0.74609 -2.03125,-0.51953c-0.71875,0.22266 -1.25391,0.83203 -1.37891,1.57422c-0.125,0.74609 0.17578,1.49609 0.78516,1.94531l13.9375,12.0625c0.4375,0.37109 1.01563,0.53516 1.58203,0.45313c0.57031,-0.08594 1.07422,-0.41016 1.38672,-0.89062l20.09375,-30.6875c0.42969,-0.62891 0.46484,-1.44141 0.09375,-2.10547c-0.37109,-0.66016 -1.08594,-1.05469 -1.84375,-1.01953z">
                                            </path>
                                        </g>
                                    </g>
                                </svg> Autenticação multifator (MFA)</li>
                            <li><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20"
                                    viewBox="0,0,256,256">
                                    <g fill="#FFFFFF" fill-rule="nonzero" stroke="none" stroke-width="1"
                                        stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10"
                                        stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none"
                                        font-size="none" text-anchor="none" style="mix-blend-mode: normal">
                                        <g transform="scale(5.12,5.12)">
                                            <path
                                                d="M41.9375,8.625c-0.66406,0.02344 -1.27344,0.375 -1.625,0.9375l-18.8125,28.78125l-12.1875,-10.53125c-0.52344,-0.54297 -1.30859,-0.74609 -2.03125,-0.51953c-0.71875,0.22266 -1.25391,0.83203 -1.37891,1.57422c-0.125,0.74609 0.17578,1.49609 0.78516,1.94531l13.9375,12.0625c0.4375,0.37109 1.01563,0.53516 1.58203,0.45313c0.57031,-0.08594 1.07422,-0.41016 1.38672,-0.89062l20.09375,-30.6875c0.42969,-0.62891 0.46484,-1.44141 0.09375,-2.10547c-0.37109,-0.66016 -1.08594,-1.05469 -1.84375,-1.01953z">
                                            </path>
                                        </g>
                                    </g>
                                </svg> Proteção contra ataques de força bruta</li>
                        </ul>
                        <p>• Com a Segurança de Alto Nível, você pode ter Certeza de que suas Senhas estão
                            Protegidas
                            Contra qualquer Ameaça.</p>
                    </div>
                </div>

                <!-- Item de funcionalidade individual -->
                <div class="feature-item">
                    <h3>Acesso Ilimitado</h3>
                    <img src="./img/acesso-icon.png" alt="">
                    <p>• Acesse suas Senhas de Forma Ilimitada, a Qualquer Momento.</p>
                    <ul>
                        <li><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20"
                                viewBox="0,0,256,256">
                                <g fill="#FFFFFF" fill-rule="nonzero" stroke="none" stroke-width="1"
                                    stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10"
                                    stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none"
                                    font-size="none" text-anchor="none" style="mix-blend-mode: normal">
                                        <g transform="scale(5.12,5.12)">
                                            <path
                                                d="M41.9375,8.625c-0.66406,0.02344 -1.27344,0.375 -1.625,0.9375l-18.8125,28.78125l-12.1875,-10.53125c-0.52344,-0.54297 -1.30859,-0.74609 -2.03125,-0.51953c-0.71875,0.22266 -1.25391,0.83203 -1.37891,1.57422c-0.125,0.74609 0.17578,1.49609 0.78516,1.94531l13.9375,12.0625c0.4375,0.37109 1.01563,0.53516 1.58203,0.45313c0.57031,-0.08594 1.07422,-0.41016 1.38672,-0.89062l20.09375,-30.6875c0.42969,-0.62891 0.46484,-1.44141 0.09375,-2.10547c-0.37109,-0.66016 -1.08594,-1.05469 -1.84375,-1.01953z">
                                            </path>
                                        </g>
                                    </g>
                            </svg> Acesso Ilimitado</li>
                        <li><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20"
                                viewBox="0,0,256,256">
                                <g fill="#FFFFFF" fill-rule="nonzero" stroke="none" stroke-width="1"
                                    stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10"
                                    stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none"
                                    font-size="none" text-anchor="none" style="mix-blend-mode: normal">
                                        <g transform="scale(5.12,5.12)">
                                            <path
                                                d="M41.9375,8.625c-0.66406,0.02344 -1.27344,0.375 -1.625,0.9375l-18.8125,28.78125l-12.1875,-10.53125c-0.52344,-0.54297 -1.30859,-0.74609 -2.03125,-0.51953c-0.71875,0.22266 -1.25391,0.83203 -1.37891,1.57422c-0.125,0.74609 0.17578,1.49609 0.78516,1.94531l13.9375,12.0625c0.4375,0.37109 1.01563,0.53516 1.58203,0.45313c0.57031,-0.08594 1.07422,-0.41016 1.38672,-0.89062l20.09375,-30.6875c0.42969,-0.62891 0.46484,-1.44141 0.09375,-2.10547c-0.37109,-0.66016 -1.08594,-1.05469 -1.84375,-1.01953z">
                                            </path>
                                        </g>
                                    </g>
                            </svg> Sincronização em Tempo Real</li>
                        <li><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20"
                                viewBox="0,0,256,256">
                                <g fill="#FFFFFF" fill-rule="nonzero" stroke="none" stroke-width="1"
                                    stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10"
                                    stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none"
                                    font-size="none" text-anchor="none" style="mix-blend-mode: normal">
                                        <g transform="scale(5.12,5.12)">
                                            <path
                                                d="M41.9375,8.625c-0.66406,0.02344 -1.27344,0.375 -1.625,0.9375l-18.8125,28.78125l-12.1875,-10.53125c-0.52344,-0.54297 -1.30859,-0.74609 -2.03125,-0.51953c-0.71875,0.22266 -1.25391,0.83203 -1.37891,1.57422c-0.125,0.74609 0.17578,1.49609 0.78516,1.94531l13.9375,12.0625c0.4375,0.37109 1.01563,0.53516 1.58203,0.45313c0.57031,-0.08594 1.07422,-0.41016 1.38672,-0.89062l20.09375,-30.6875c0.42969,-0.62891 0.46484,-1.44141 0.09375,-2.10547c-0.37109,-0.66016 -1.08594,-1.05469 -1.84375,-1.01953z">
                                            </path>
                                        </g>
                                    </g>
                            </svg> Compatível com Dispositivos Desktop</li>
                    </ul>
                    <p>• Não importa onde esteja, você terá Acesso às suas Senhas Sempre que Precisar.</p>
                </div>

                <!-- Item de funcionalidade individual -->
                <div class="feature-item">
                    <h3>Armazenamento Ilimitado</h3>
                    <img src="./img/compartilhamento-icon.png" alt="">
                    <p>• Armazene suas Senhas com Segura e Criptografia de Ponta.</p>
                    <ul>
                        <li><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20"
                                viewBox="0,0,256,256">
                                <g fill="#FFFFFF" fill-rule="nonzero" stroke="none" stroke-width="1"
                                    stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10"
                                    stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none"
                                    font-size="none" text-anchor="none" style="mix-blend-mode: normal">
                                        <g transform="scale(5.12,5.12)">
                                            <path
                                                d="M41.9375,8.625c-0.66406,0.02344 -1.27344,0.375 -1.625,0.9375l-18.8125,28.78125l-12.1875,-10.53125c-0.52344,-0.54297 -1.30859,-0.74609 -2.03125,-0.51953c-0.71875,0.22266 -1.25391,0.83203 -1.37891,1.57422c-0.125,0.74609 0.17578,1.49609 0.78516,1.94531l13.9375,12.0625c0.4375,0.37109 1.01563,0.53516 1.58203,0.45313c0.57031,-0.08594 1.07422,-0.41016 1.38672,-0.89062l20.09375,-30.6875c0.42969,-0.62891 0.46484,-1.44141 0.09375,-2.10547c-0.37109,-0.66016 -1.08594,-1.05469 -1.84375,-1.01953z">
                                            </path>
                                        </g>
                                    </g>
                            </svg> Geração de Senhas</li>
                        <li><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20"
                                viewBox="0,0,256,256">
                                <g fill="#FFFFFF" fill-rule="nonzero" stroke="none" stroke-width="1"
                                    stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10"
                                    stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none"
                                    font-size="none" text-anchor="none" style="mix-blend-mode: normal">
                                        <g transform="scale(5.12,5.12)">
                                            <path
                                                d="M41.9375,8.625c-0.66406,0.02344 -1.27344,0.375 -1.625,0.9375l-18.8125,28.78125l-12.1875,-10.53125c-0.52344,-0.54297 -1.30859,-0.74609 -2.03125,-0.51953c-0.71875,0.22266 -1.25391,0.83203 -1.37891,1.57422c-0.125,0.74609 0.17578,1.49609 0.78516,1.94531l13.9375,12.0625c0.4375,0.37109 1.01563,0.53516 1.58203,0.45313c0.57031,-0.08594 1.07422,-0.41016 1.38672,-0.89062l20.09375,-30.6875c0.42969,-0.62891 0.46484,-1.44141 0.09375,-2.10547c-0.37109,-0.66016 -1.08594,-1.05469 -1.84375,-1.01953z">
                                            </path>
                                        </g>
                                    </g>
                            </svg> Armazenamento Criptografado</li>
                        <li><svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20"
                                viewBox="0,0,256,256">
                                <g fill="#FFFFFF" fill-rule="nonzero" stroke="none" stroke-width="1"
                                    stroke-linecap="butt" stroke-linejoin="miter" stroke-miterlimit="10"
                                    stroke-dasharray="" stroke-dashoffset="0" font-family="none" font-weight="none"
                                    font-size="none" text-anchor="none" style="mix-blend-mode: normal">
                                        <g transform="scale(5.12,5.12)">
                                            <path
                                                d="M41.9375,8.625c-0.66406,0.02344 -1.27344,0.375 -1.625,0.9375l-18.8125,28.78125l-12.1875,-10.53125c-0.52344,-0.54297 -1.30859,-0.74609 -2.03125,-0.51953c-0.71875,0.22266 -1.25391,0.83203 -1.37891,1.57422c-0.125,0.74609 0.17578,1.49609 0.78516,1.94531l13.9375,12.0625c0.4375,0.37109 1.01563,0.53516 1.58203,0.45313c0.57031,-0.08594 1.07422,-0.41016 1.38672,-0.89062l20.09375,-30.6875c0.42969,-0.62891 0.46484,-1.44141 0.09375,-2.10547c-0.37109,-0.66016 -1.08594,-1.05469 -1.84375,-1.01953z">
                                            </path>
                                        </g>
                                    </g>
                            </svg>Controle e Alteração de Senhas Ilimitado</li>
                    </ul>
                    <p>• Armazene a Quantidade de Senhas que Precisar! Teste nossa Geração de Senhas e o Controle de
                        Senhas.
                    </p>
                </div>
            </div>


            <ul class="circles">
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
                <li></li>
            </ul>


        </section>


        <!-- Seção de Testemunhos -->
        <section class="testimonials">
            <h2>O que nossos usuários dizem</h2>
            <div class="testimonial-list">
                <div class="testimonial-item">
                    <img src="./img/person01.png" alt="Foto de Pedro Santussi">
                    <div class="testimonial-text">
                        <p>"Cara o Protect Key mudou meu dia a dia. Eu sempre esquecia minhas senhas e ficava
                            naquela
                            correria pra recuperar tudo pedindo email. Agora tá tudo num lugar só, bem mais fácil.
                            Recomendo demais!"</p>
                        <h4>— Pedro Santussi</h4>
                    </div>
                </div>

                <div class="testimonial-item">
                    <img src="./img/person02.png" alt="Foto de Thiago Pereira Mendes">
                    <div class="testimonial-text">
                        <p>"Eu vivia esquecendo minhas senhas e perdendo tempo pra recuperar. O que sinceramente era
                            bem
                            chato, resolveu meu problema. Agora minha vida ficou bem mais fácil!"</p>
                        <h4>— Thiago Pereira Mendes</h4>
                    </div>
                </div>
            </div>

            <div class="testimonial-center">
                <div class="testimonial-item">
                    <img src="./img/person03.png" alt="Foto de Thiago Pereira Mendes">
                    <div class="testimonial-text">
                        <p>"Vou te contar funciona top. Eu sempre esquecia minhas senhas e era horrível ficar
                            tentando
                            recuperar. Agora tá tudo organizado, bem mais de boa."</p>
                        <h4>— Roger Souza</h4>
                    </div>
                </div>
            </div>
        </section>



        <!-- Seção de Planos e Preços -->
        <section class="pricing" id="planos">
            <h2>Planos e Preços</h2>
            <div class="pricing-list">
                <!-- Card do Plano Básico -->
                <div class="card-price">
                    <p class="price">
                        Grátis
                    </p>
                    <ul class="lists">
                        <li class="list">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path fill="#ffffff"
                                    d="M21.5821 5.54289C21.9726 5.93342 21.9726 6.56658 21.5821 6.95711L10.2526 18.2867C9.86452 18.6747 9.23627 18.6775 8.84475 18.293L2.29929 11.8644C1.90527 11.4774 1.89956 10.8443 2.28655 10.4503C2.67354 10.0562 3.30668 10.0505 3.70071 10.4375L9.53911 16.1717L20.1679 5.54289C20.5584 5.15237 21.1916 5.15237 21.5821 5.54289Z">
                                </path>
                            </svg>
                            <span>Armazenamento limitado de senhas</span>
                        </li>
                        <li class="list">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path fill="#ffffff"
                                    d="M21.5821 5.54289C21.9726 5.93342 21.9726 6.56658 21.5821 6.95711L10.2526 18.2867C9.86452 18.6747 9.23627 18.6775 8.84475 18.293L2.29929 11.8644C1.90527 11.4774 1.89956 10.8443 2.28655 10.4503C2.67354 10.0562 3.30668 10.0505 3.70071 10.4375L9.53911 16.1717L20.1679 5.54289C20.5584 5.15237 21.1916 5.15237 21.5821 5.54289Z">
                                </path>
                            </svg>
                            <span>Acesso em um dispositivo</span>
                        </li>
                        <li class="list">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path fill="#ffffff"
                                    d="M21.5821 5.54289C21.9726 5.93342 21.9726 6.56658 21.5821 6.95711L10.2526 18.2867C9.86452 18.6747 9.23627 18.6775 8.84475 18.293L2.29929 11.8644C1.90527 11.4774 1.89956 10.8443 2.28655 10.4503C2.67354 10.0562 3.30668 10.0505 3.70071 10.4375L9.53911 16.1717L20.1679 5.54289C20.5584 5.15237 21.1916 5.15237 21.5821 5.54289Z">
                                </path>
                            </svg>
                            <span>Suporte básico</span>
                        </li>
                    </ul>
                    <?php if ($userPlan === 'básico'): ?>
                        <span class="btn" style="color: white; font-size: 18px; padding: 20px 0;">Você já possui este
                            plano</span>
                    <?php else: ?>
                        <a href="#" class="action">Escolher Plano</a>
                    <?php endif; ?>
                </div>

                <!-- Card do Plano Pro -->
                <div class="card-price">
                    <p class="price">
                        R$14.99/mês
                    </p>
                    <ul class="lists">
                        <li class="list">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path fill="#ffffff"
                                    d="M21.5821 5.54289C21.9726 5.93342 21.9726 6.56658 21.5821 6.95711L10.2526 18.2867C9.86452 18.6747 9.23627 18.6775 8.84475 18.293L2.29929 11.8644C1.90527 11.4774 1.89956 10.8443 2.28655 10.4503C2.67354 10.0562 3.30668 10.0505 3.70071 10.4375L9.53911 16.1717L20.1679 5.54289C20.5584 5.15237 21.1916 5.15237 21.5821 5.54289Z">
                                </path>
                            </svg>
                            <span>Armazenamento ilimitado de senhas</span>
                        </li>
                        <li class="list">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path fill="#ffffff"
                                    d="M21.5821 5.54289C21.9726 5.93342 21.9726 6.56658 21.5821 6.95711L10.2526 18.2867C9.86452 18.6747 9.23627 18.6775 8.84475 18.293L2.29929 11.8644C1.90527 11.4774 1.89956 10.8443 2.28655 10.4503C2.67354 10.0562 3.30668 10.0505 3.70071 10.4375L9.53911 16.1717L20.1679 5.54289C20.5584 5.15237 21.1916 5.15237 21.5821 5.54289Z">
                                </path>
                            </svg>
                            <span>Acesso em múltiplos dispositivos</span>
                        </li>
                        <li class="list">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path fill="#ffffff"
                                    d="M21.5821 5.54289C21.9726 5.93342 21.9726 6.56658 21.5821 6.95711L10.2526 18.2867C9.86452 18.6747 9.23627 18.6775 8.84475 18.293L2.29929 11.8644C1.90527 11.4774 1.89956 10.8443 2.28655 10.4503C2.67354 10.0562 3.30668 10.0505 3.70071 10.4375L9.53911 16.1717L20.1679 5.54289C20.5584 5.15237 21.1916 5.15237 21.5821 5.54289Z">
                                </path>
                            </svg>
                            <span>Autenticação multifator</span>
                        </li>
                    </ul>
                    <?php if ($userPlan === 'pro'): ?>
                        <span class="btn" style="color: white; font-size: 18px; padding: 20px 0;">Você já possui este
                            plano</span>
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($paymentUrlPro); ?>" class="action">Escolher Pro</a>
                    <?php endif; ?>
                </div>

                <!-- Card do Plano Premium -->
                <div class="card-price">
                    <p class="price">
                        R$24.99/mês
                    </p>
                    <ul class="lists">
                        <li class="list">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path fill="#ffffff"
                                    d="M21.5821 5.54289C21.9726 5.93342 21.9726 6.56658 21.5821 6.95711L10.2526 18.2867C9.86452 18.6747 9.23627 18.6775 8.84475 18.293L2.29929 11.8644C1.90527 11.4774 1.89956 10.8443 2.28655 10.4503C2.67354 10.0562 3.30668 10.0505 3.70071 10.4375L9.53911 16.1717L20.1679 5.54289C20.5584 5.15237 21.1916 5.15237 21.5821 5.54289Z">
                                </path>
                            </svg>
                            <span>Backup e recuperação de dados</span>
                        </li>
                        <li class="list">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path fill="#ffffff"
                                    d="M21.5821 5.54289C21.9726 5.93342 21.9726 6.56658 21.5821 6.95711L10.2526 18.2867C9.86452 18.6747 9.23627 18.6775 8.84475 18.293L2.29929 11.8644C1.90527 11.4774 1.89956 10.8443 2.28655 10.4503C2.67354 10.0562 3.30668 10.0505 3.70071 10.4375L9.53911 16.1717L20.1679 5.54289C20.5584 5.15237 21.1916 5.15237 21.5821 5.54289Z">
                                </path>
                            </svg>
                            <span>Relatórios avançados</span>
                        </li>
                        <li class="list">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path fill="#ffffff"
                                    d="M21.5821 5.54289C21.9726 5.93342 21.9726 6.56658 21.5821 6.95711L10.2526 18.2867C9.86452 18.6747 9.23627 18.6775 8.84475 18.293L2.29929 11.8644C1.90527 11.4774 1.89956 10.8443 2.28655 10.4503C2.67354 10.0562 3.30668 10.0505 3.70071 10.4375L9.53911 16.1717L20.1679 5.54289C20.5584 5.15237 21.1916 5.15237 21.5821 5.54289Z">
                                </path>
                            </svg>
                            <span>Suporte premium 24/7</span>
                        </li>
                    </ul>
                    <?php if ($userPlan === 'premium'): ?>
                        <span class="btn" style="color: white; font-size: 18px; padding: 20px 0;">Você já possui este
                            plano</span>
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($paymentUrlPremium); ?>" class="action">Escolher
                            Premium</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>


        <!-- Seção de FAQ -->
        <section class="faq-section">
            <div class="faq-background-wrapper">
                <div class="faq-background-box">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
                <div class="faq-container">
                    <div class="container">
                        <article>
                            <div class="faq-content">
                                <h2>Perguntas Frequentes</h2>
                                <!-- Seus itens de FAQ existentes -->
                                <div class="faq-item">
                                    <div class="question">
                                        <h3 tabindex="0">Como o Protect Key protege minhas senhas?</h3>
                                        <svg width="10" height="7" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 .799l4 4 4-4" stroke="#ffffff" stroke-width="2" fill="none"
                                                fill-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <p hidden>O Protect Key utiliza criptografia AES-256 para proteger todas as suas
                                        senhas. Além disso, oferecemos autenticação multifator (MFA) para adicionar
                                        uma
                                        camada extra de segurança. Isso significa que apenas você tem acesso às suas
                                        senhas, garantindo total privacidade e proteção contra ameaças externas.</p>
                                </div>

                                <div class="faq-item">
                                    <div class="question">
                                        <h3 tabindex="0">O que acontece se eu esquecer minha senha mestre?</h3>
                                        <svg width="10" height="7" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 .799l4 4 4-4" stroke="#ffffff" stroke-width="2" fill="none"
                                                fill-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <p hidden>Por razões de segurança, o Protect Key não armazena sua senha mestre
                                        em
                                        nossos servidores. Isso significa que, se você esquecer sua senha mestre,
                                        não
                                        poderemos recuperá-la. Recomendamos que você mantenha sua senha mestre em um
                                        local seguro para evitar perder o acesso às suas contas.</p>
                                </div>

                                <div class="faq-item">
                                    <div class="question">
                                        <h3 tabindex="0">Como faço backup e recupero minhas senhas?</h3>
                                        <svg width="10" height="7" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 .799l4 4 4-4" stroke="#ffffff" stroke-width="2" fill="none"
                                                fill-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <p hidden>Os usuários do plano Premium têm acesso a recursos avançados de backup
                                        e
                                        recuperação de dados. Você pode facilmente fazer backup de todas as suas
                                        senhas
                                        e restaurá-las quando necessário, garantindo que você nunca perca acesso às
                                        suas
                                        informações importantes.</p>
                                </div>

                                <div class="faq-item">
                                    <div class="question">
                                        <h3 tabindex="0">O Protect Key oferece suporte para geração automática de
                                            senhas
                                            fortes?</h3>
                                        <svg width="10" height="7" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 .799l4 4 4-4" stroke="#ffffff" stroke-width="2" fill="none"
                                                fill-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <p hidden>Sim, o Protect Key inclui um gerador de senhas integrado que cria
                                        automaticamente senhas fortes e únicas para todas as suas contas. Você pode
                                        personalizar o comprimento da senha e escolher incluir diferentes tipos de
                                        caracteres, como letras maiúsculas, minúsculas, números e símbolos
                                        especiais.
                                        Isso garante que suas senhas sejam altamente seguras e reduz o risco de
                                        acesso
                                        não autorizado às suas contas.
                                    </p>
                                </div>

                                <div class="faq-item">
                                    <div class="question">
                                        <h3 tabindex="0">Como o Protect Key lida com tentativas de login suspeitas?
                                        </h3>
                                        <svg width="10" height="7" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M1 .799l4 4 4-4" stroke="#ffffff" stroke-width="2" fill="none"
                                                fill-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <p hidden>O Protect Key monitora continuamente todas as tentativas de login e
                                        aplica
                                        medidas de segurança avançadas para proteger sua conta. Caso seja detectada
                                        uma
                                        tentativa de login suspeita, como a partir de um novo dispositivo ou
                                        localização
                                        não reconhecida, você será imediatamente notificado via e-mail ou
                                        notificação
                                        push. Além disso, o acesso será bloqueado temporariamente até que a
                                        tentativa
                                        seja confirmada como segura por você, garantindo total controle sobre a
                                        segurança da sua conta.</p>
                                </div>
                            </div>
                        </article>
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


    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="./script/scroll-reveal.js"></script>
    <script src="./script/preCarregamento.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const preCarregamento = document.getElementById('pre-carregamento');

            // Bloquear o scroll
            document.body.style.overflow = 'hidden';

            if (preCarregamento) {
                // Fade out after 3.8 seconds
                setTimeout(() => {
                    preCarregamento.style.transition = 'opacity .8s ease-out';
                    preCarregamento.style.opacity = '0';

                    // Remove from DOM after fade out
                    setTimeout(() => {
                        preCarregamento.style.display = 'none';

                        // Liberar o scroll
                        document.body.style.overflow = '';
                    }, 800);
                }, 4000);
            }
        });
    </script>


</body>

</html>