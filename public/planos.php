<?php
session_start();
require_once __DIR__ . '/../php/bootstrap.php';
$conn = require_once __DIR__ . '/../php/conectar.php';
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

    <title>Planos</title>
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

    <main>
        <!-- Seção de Planos e Preços -->
        <section class="pricing" id="planos"
            style="background: linear-gradient(160deg, #090c30 0%, #1e2a91 50%, #3d84d6c7 100%) !important;">
            <h2 style="color: white; text-shadow: 0px 0px 6px #ffffffc5;">Planos e Preços</h2>
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

    <!--import js-->
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="/script/scroll-reveal.js"></script>
    <script src="/script/preCarregamento.js"></script>

    <script>

    </script>
</body>

</html>