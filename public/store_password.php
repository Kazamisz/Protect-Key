<?php
session_start();
$conn = require_once __DIR__ . '/../php/conectar.php';
require_once __DIR__ . '/../php/bootstrap.php';
require_once __DIR__ . '/../php/store_password.php';

$userID = $_SESSION['userID'] ?? null;

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
    <link rel="stylesheet" href="./style/styles-store.css">
    <link rel="stylesheet" href="./style/styles-store-form.css">
    <link rel="stylesheet" href="./style/styles-buttons.css">

    <title>Controle de Senhas</title>
</head>

<body>
    <!-- Cabeçalho -->
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
        <!-- Formulário de adição/atualização de senha -->
        <section class="form-container" id="formContainer">
            <form id="passwordForm" action="" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                <!-- Campos ocultos para identificar ação e ID da senha -->
                <input type="hidden" id="actionType" name="actionType" value="add">
                <input type="hidden" id="passwordId" name="passwordId" value="">

                <!-- Inputs estilizados do formulário -->
                <div class="input-group">
                    <label class="label" for="siteName">Nome da Senha</label>
                    <input autocomplete="off" name="siteName" id="siteName" class="input" type="text"
                        placeholder="Ex: Conta do Gmail" maxlength="30" required>
                </div>

                <div class="input-group">
                    <label class="label" for="url">URL do Site</label>
                    <input autocomplete="off" name="url" id="url" class="input" type="text"
                        placeholder="Ex: www.gmail.com" maxlength="255">
                </div>

                <div class="input-group">
                    <label class="label" for="loginName">Nome de Usuário/Login</label>
                    <input autocomplete="off" name="loginName" id="loginName" class="input" type="text"
                        placeholder="Ex: usuario123" maxlength="30">
                </div>

                <div class="input-group">
                    <label class="label" for="email">E-mail</label>
                    <input autocomplete="off" name="email" id="email" class="input" type="email"
                        placeholder="Ex: usuario@exemplo.com" maxlength="50">
                </div>

                <div class="input-group">
                    <label class="label" for="password">Senha</label>
                    <input autocomplete="off" name="password" id="password" class="input" type="password"
                        placeholder="Digite sua senha" maxlength="70" required>
                </div>

                <span type="button" id="togglePassword" class="toggle-password" onclick="verSenha()"
                    style="position: absolute; right: 33%; top: 64%; cursor: pointer;">
                    <i class="fas fa-eye" id="togglePasswordImage"></i>
                </span>

                <!-- Mensagens de erro e sucesso -->
                <?php
                if (!empty($errorMessage)) {
                    echo "<p class='message error'>$errorMessage</p>";
                }
                if (!empty($successMessage)) {
                    echo "<p class='message success'>$successMessage</p>";
                }
                ?>


                <div class="form-buttons" style="margin-top: 50px;">
                    <!-- Botões do formulário -->

                    <!--Botão salvar-->
                    <button type="submit" class="csb-button">
                        <div class="csb-svg-wrapper-1">
                            <div class="csb-svg-wrapper">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="30" height="30"
                                    class="csb-icon">
                                    <path
                                        d="M22,15.04C22,17.23 20.24,19 18.07,19H5.93C3.76,19 2,17.23 2,15.04C2,13.07 3.43,11.44 5.31,11.14C5.28,11 5.27,10.86 5.27,10.71C5.27,9.33 6.38,8.2 7.76,8.2C8.37,8.2 8.94,8.43 9.37,8.8C10.14,7.05 11.13,5.44 13.91,5.44C17.28,5.44 18.87,8.06 18.87,10.83C18.87,10.94 18.87,11.06 18.86,11.17C20.65,11.54 22,13.13 22,15.04Z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <span class="csb-text">Salvar</span>
                    </button>

                    <!--Botão cancelar-->
                    <button type="button" class="db-button db-noselect" onclick="cancelForm()">
                        <span class="db-text">Cancelar</span>
                        <span class="db-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path
                                    d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z">
                                </path>
                            </svg>
                        </span>
                    </button>

                </div>

                <!--Botão gerar senha-->
                <button type="button" onclick="gerarSenha()" class="btn">
                    <svg height="24" width="24" fill="#FFFFFF" viewBox="0 0 24 24" data-name="Layer 1" id="Layer_1"
                        class="sparkle">
                        <path
                            d="M10,21.236,6.755,14.745.264,11.5,6.755,8.255,10,1.764l3.245,6.491L19.736,11.5l-6.491,3.245ZM18,21l1.5,3L21,21l3-1.5L21,18l-1.5-3L18,18l-3,1.5ZM19.333,4.667,20.5,7l1.167-2.333L24,3.5,21.667,2.333,20.5,0,19.333,2.333,17,3.5Z">
                        </path>
                    </svg>

                    <span class="text">Gerar Senha</span>
                </button>
            </form>
        </section>

        <!-- Tabela com senhas salvas -->
        <section id="savedTable" style="padding: 80px 150px 0 150px;">
            <?php if (!empty($savedPasswords)): ?>
                <table class="vault-table">
                    <thead>
                        <tr>
                            <th>Site</th>
                            <th>Login</th>
                            <th>E-mail</th>
                            <th>Senha</th>
                            <th style="width: 120px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Laço para exibir senhas salvas -->
                        <?php foreach ($savedPasswords as $password): ?>
                            <tr>
                                <td data-label="Site" class="cell-site">
                                    <a href="<?php echo htmlspecialchars($password['url']); ?>" target="_blank"
                                        class="vault-link">
                                        <?php echo htmlspecialchars($password['site_name']); ?>
                                    </a>
                                </td>
                                <td data-label="Login" class="cell-login">
                                    <?php echo htmlspecialchars($password['name']); ?>
                                </td>
                                <td data-label="E-mail" class="cell-email"><?php echo htmlspecialchars($password['email']); ?>
                                </td>
                                <td data-label="Senha" class="cell-password">
                                    <div class="pw-wrapper">
                                        <span class="pw-value" aria-live="polite">••••••</span>
                                        <div class="pw-actions">
                                            <button type="button" class="icon-btn show-btn" title="Mostrar senha"
                                                aria-label="Mostrar senha"
                                                onclick="showPassword(this, '<?php echo htmlspecialchars($password['password']); ?>')">
                                                <img class="icon-img" style="padding-left: 0px; margin: 0 !important;"
                                                    src="./img/inspecIcon.png" alt="Mostrar">
                                            </button>
                                            <button type="button" class="icon-btn copy-btn" title="Copiar senha"
                                                aria-label="Copiar senha"
                                                onclick="copyPassword('<?php echo htmlspecialchars($password['password']); ?>')">
                                                <img class="icon-img" style="padding-left: 0px; margin: 0 !important;"
                                                    src="./img/copyIcon.png" alt="Copiar">
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Ações" class="cell-actions">
                                    <!-- Editar -->
                                    <button type="button" class="icon-btn" style="width: 42px; height: auto;" title="Editar"
                                        onclick="editPassword(<?php echo htmlspecialchars($password['senhaId']); ?>,
                        '<?php echo htmlspecialchars($password['site_name']); ?>', 
                        '<?php echo htmlspecialchars($password['url']); ?>', 
                        '<?php echo htmlspecialchars($password['name']); ?>', 
                        '<?php echo htmlspecialchars($password['email']); ?>', 
                        '<?php echo htmlspecialchars($password['password']); ?>')">
                                        <img class="icon-img"
                                            style="width: 33px; height: auto; padding: 2px 0; margin: 0 !important;"
                                            src="./img/editKey.png" alt="Copiar">
                                    </button>

                                    <!-- Excluir -->
                                    <form action="" method="post" class="inline-form"
                                        onsubmit="return confirm('Tem certeza que deseja excluir esta senha?')">
                                        <input type="hidden" name="csrf_token"
                                            value="<?php echo htmlspecialchars(generateCsrfToken()); ?>">
                                        <input type="hidden" name="passwordId"
                                            value="<?php echo htmlspecialchars($password['senhaId']); ?>">
                                        <input type="hidden" name="actionType" value="delete">
                                        <button type="submit" class="vault-action danger" title="Excluir">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path fill="currentColor"
                                                    d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>

            <?php endif; ?>
        </section>


        <!-- Imagem quando não há itens -->
        <?php if (empty($savedPasswords)): ?>
            <img src="./img/sem-itens.png" alt="Sem itens" class="img-no-itens" id="img-senha">
        <?php else: ?>
            <img src="./img/sem-itens.png" alt="Sem itens" class="img-no-itens is-hidden" id="img-senha"
                style="display:none;">
        <?php endif; ?>



        <!-- Botão para adicionar senha ou mensagem de limite atingido -->
        <?php if ($showAddButton): ?>
            <button type="button" class="botao-adicionar" onclick="toggleForm()">
                <span class="botao-adicionar__texto">Adicionar</span>
            </button>
        <?php else: ?>
            <p
                style="color: red; font-weight: bold; font-size: 18px; padding: 10px; background-color: #fdd; border-radius: 10px; width:fit-content; margin: 60px auto 6vh auto;">
                Limite de Senhas Atingido.</p>
        <?php endif; ?>


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

    <!-- JS da página extraído para arquivo dedicado -->
    <script src="/script/store_password.js"></script>

    <!--import js-->
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="/script/scroll-reveal.js"></script>

</body>

</html>