<?php
session_start();
require("php/conectar.php");
require("php/functions.php");

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
    <link rel="stylesheet" href="./style/gerador-senhas.css">

    <title>Gerador de Senhas e Palavras-Senha</title>
</head>

<body>
    <header class="header" style="width: 100%;">
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


    <main>
        <div class="container">
            <div class="tabs">
                <button class="tab active" onclick="switchTab('password')">Senha</button>
                <button class="tab" onclick="switchTab('passphrase')">Palavras-Senha</button>
            </div>

            <div id="password-tab" class="tab-content active">
                <h1 class="title">Gerador de Senhas</h1>

                <div class="field-group">
                    <label for="generated-password" class="label" style="padding-left: 35px;">Senha Gerada:</label>
                    <input type="text" id="generated-password" class="password-input" readonly
                        placeholder="Clique em 'Gerar Senha'">
                </div>

                <div class="options-container">
                    <div class="options">
                        <h3 class="label">Opções Adicionais</h3>
                        <label class="checkbox-container">
                            <input type="checkbox" id="use-lowercase" class="checkbox-input" checked>
                            <div class="checkbox-checkmark"></div>
                            <p>a - z</p>
                        </label>
                        <label class="checkbox-container">
                            <input type="checkbox" id="use-uppercase" class="checkbox-input" checked>
                            <div class="checkbox-checkmark"></div>
                            <p>A - Z</p>
                        </label>
                        <label class="checkbox-container">
                            <input type="checkbox" id="use-numbers" class="checkbox-input" checked>
                            <div class="checkbox-checkmark"></div>
                            <p>1 - 9</p>
                        </label>
                        <label class="checkbox-container">
                            <input type="checkbox" id="use-symbols" class="checkbox-input">
                            <div class="checkbox-checkmark"></div>
                            <p>!@#$%^&*</p>
                        </label>
                    </div>


                    <div class="slider-container">
                        <label for="custom-length-slider" class="label">Comprimento da Senha</label>
                        <div class="custom-range-slider">
                            <div id="custom-slider_thumb" class="custom-range-slider_thumb">16</div>
                            <div class="custom-range-slider_line">
                                <div id="custom-slider_line" class="custom-range-slider_line-fill"></div>
                            </div>
                            <input id="custom-length-slider" class="custom-range-slider_input" type="range" min="8"
                                max="128" value="16">
                        </div>
                        <div class="slider-value">Comprimento: <span id="length-value">16 Caracteres</span></div>
                    </div>

                </div>

                <div class="actions">
                    <button class="button" onclick="generatePassword()">
                        <div class="dots_border"></div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="sparkle">
                            <path class="path" stroke-linejoin="round" stroke-linecap="round" stroke="black"
                                fill="black"
                                d="M14.187 8.096L15 5.25L15.813 8.096C16.0231 8.83114 16.4171 9.50062 16.9577 10.0413C17.4984 10.5819 18.1679 10.9759 18.903 11.186L21.75 12L18.904 12.813C18.1689 13.0231 17.4994 13.4171 16.9587 13.9577C16.4181 14.4984 16.0241 15.1679 15.814 15.903L15 18.75L14.187 15.904C13.9769 15.1689 13.5829 14.4994 13.0423 13.9587C12.5016 13.4181 11.8321 13.0241 11.097 12.814L8.25 12L11.096 11.187C11.8311 10.9769 12.5006 10.5829 13.0413 10.0423C13.5819 9.50162 13.9759 8.83214 14.186 8.097L14.187 8.096Z">
                            </path>
                            <path class="path" stroke-linejoin="round" stroke-linecap="round" stroke="black"
                                fill="black"
                                d="M6 14.25L5.741 15.285C5.59267 15.8785 5.28579 16.4206 4.85319 16.8532C4.42059 17.2858 3.87853 17.5927 3.285 17.741L2.25 18L3.285 18.259C3.87853 18.4073 4.42059 18.7142 4.85319 19.1468C5.28579 19.5794 5.59267 20.1215 5.741 20.715L6 21.75L6.259 20.715C6.40725 20.1216 6.71398 19.5796 7.14639 19.147C7.5788 18.7144 8.12065 18.4075 8.714 18.259L9.75 18L8.714 17.741C8.12065 17.5925 7.5788 17.2856 7.14639 16.853C6.71398 16.4204 6.40725 15.8784 6.259 15.285L6 14.25Z">
                            </path>
                            <path class="path" stroke-linejoin="round" stroke-linecap="round" stroke="black"
                                fill="black"
                                d="M6.5 4L6.303 4.5915C6.24777 4.75718 6.15472 4.90774 6.03123 5.03123C5.90774 5.15472 5.75718 5.24777 5.5915 5.303L5 5.5L5.5915 5.697C5.75718 5.75223 5.90774 5.84528 6.03123 5.96877C6.15472 6.09226 6.24777 6.24282 6.303 6.4085L6.5 7L6.697 6.4085C6.75223 6.24282 6.84528 6.09226 6.96877 5.96877C7.09226 5.84528 7.24282 5.75223 7.4085 5.697L8 5.5L7.4085 5.303C7.24282 5.24777 7.09226 5.15472 6.96877 5.03123C6.84528 4.90774 6.75223 4.75718 6.697 4.5915L6.5 4Z">
                            </path>
                        </svg>
                        <span class="text_button">Gerar Senha</span>
                    </button>


                    <input class="copy-toggle-checkbox" id="copy-toggle-checkbox" type="checkbox" />
                    <label class="copy-button" for="copy-toggle-checkbox"
                        onclick="copyToClipboard('generated-password')">
                        <div class="copy-content">
                            <span class="copy-letters">
                                <span style="--i: 1" data-label="C">C</span>
                                <span style="--i: 2" data-label="o">o</span>
                                <span style="--i: 3" data-label="p">p</span>
                                <span style="--i: 4" data-label="i">i</span>
                                <span style="--i: 5" data-label="a">a</span>
                                <span style="--i: 6" data-label="r">r</span>
                                <span style="--i: 7" data-label="S">S</span>
                                <span style="--i: 8" data-label="e">e</span>
                                <span style="--i: 7" data-label="n">n</span>
                                <span style="--i: 8" data-label="h">h</span>
                                <span style="--i: 8" data-label="a">a</span>
                            </span>
                            <div class="copy-icon-container">
                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.6" fill="none" data-slot="icon"
                                    class="copy-icon">
                                    <path class="copy-bm"
                                        d="M12.0017 6V4M8.14886 7.40371L6.86328 5.87162M15.864 7.40367L17.1496 5.87158">
                                    </path>
                                    <path
                                        d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"
                                        stroke-linejoin="round" stroke-linecap="round" class="copy-link"></path>
                                </svg>
                            </div>
                        </div>
                    </label>

                </div>

                <div class="label" id="time-estimation" style="width: fit-content; margin: 20px auto 10px;"></div>
            </div>

            <div id="passphrase-tab" class="tab-content">
                <h1 class="title">Gerador de Palavras-Senha</h1>

                <div class="field-group">
                    <label for="generated-passphrase" class="label" style="padding-left: 35px;">Palavras-Senha
                        Gerada</label>
                    <input type="text" id="generated-passphrase" class="password-input" readonly
                        placeholder="Clique em 'Gerar' para iniciar">
                </div>

                <div class="options-container02">
                    <div class="additional-options">
                        <h3 class="label" style="padding-left: 26px; font-size:19px;">Opções Adicionais</h3>
                        <div class="options" style="margin-bottom: 1vh;">

                            <label class="checkbox-container">
                                <input type="checkbox" id="capitalize-first" class="checkbox-input" checked>
                                <div class="checkbox-checkmark"></div>
                                <p>1° Letra Maiúscula</p>
                            </label>

                            <label class="checkbox-container">
                                <input type="checkbox" id="capitalize-all" class="checkbox-input" checked>
                                <div class="checkbox-checkmark"></div>
                                <p>Toda 1° Letra Maiúscula</p>
                            </label>

                            <label class="checkbox-container">
                                <input type="checkbox" id="include-number" class="checkbox-input" checked>
                                <div class="checkbox-checkmark"></div>
                                <p>Incluir Número</p>
                            </label>
                        </div>
                    </div>

                    <div class="slider-container" style="width: fit-content;margin-top: 2.6vh; margin-left: 2.5vw;">
                        <label for="words-slider" class="label">Número de Palavras</label>
                        <div class="custom-range-slider">
                            <div id="words-slider-thumb" class="custom-range-slider_thumb"></div>
                            <div class="custom-range-slider_line">
                                <div id="words-slider-line" class="custom-range-slider_line-fill"></div>
                            </div>
                            <input type="range" id="words-slider" class="custom-range-slider_input slider" min="0"
                                max="10" value="0">
                        </div>
                        <div class="slider-value">Palavras: <span id="words-value">0</span></div>
                    </div>
                </div>

                <div class="field-group" style="width:fit-content; margin: 0 auto 40px; display: grid;">
                    <label for="word-separator" class="label" style="width:fit-content; margin: 5px auto;">Separador de
                        Palavras</label>

                    <div class="input-container">
                        <input type="text" id="word-separator" class="word-separator" maxlength="1" value="-"
                            pattern=".{0,1}" oninput="this.value = this.value.slice(0, 1)">
                    </div>


                    <small class="label" style="font-size: 16px; width: fit-content; margin: 0 auto;">Digite um único
                        caractere para separar as palavras</small>
                </div>




                <div class="actions">

                    <button class="button" onclick="generatePassphrase()" style="margin-rigth: 50px;">
                        <div class="dots_border"></div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="sparkle">
                            <path class="path" stroke-linejoin="round" stroke-linecap="round" stroke="black"
                                fill="black"
                                d="M14.187 8.096L15 5.25L15.813 8.096C16.0231 8.83114 16.4171 9.50062 16.9577 10.0413C17.4984 10.5819 18.1679 10.9759 18.903 11.186L21.75 12L18.904 12.813C18.1689 13.0231 17.4994 13.4171 16.9587 13.9577C16.4181 14.4984 16.0241 15.1679 15.814 15.903L15 18.75L14.187 15.904C13.9769 15.1689 13.5829 14.4994 13.0423 13.9587C12.5016 13.4181 11.8321 13.0241 11.097 12.814L8.25 12L11.096 11.187C11.8311 10.9769 12.5006 10.5829 13.0413 10.0423C13.5819 9.50162 13.9759 8.83214 14.186 8.097L14.187 8.096Z">
                            </path>
                            <path class="path" stroke-linejoin="round" stroke-linecap="round" stroke="black"
                                fill="black"
                                d="M6 14.25L5.741 15.285C5.59267 15.8785 5.28579 16.4206 4.85319 16.8532C4.42059 17.2858 3.87853 17.5927 3.285 17.741L2.25 18L3.285 18.259C3.87853 18.4073 4.42059 18.7142 4.85319 19.1468C5.28579 19.5794 5.59267 20.1215 5.741 20.715L6 21.75L6.259 20.715C6.40725 20.1216 6.71398 19.5796 7.14639 19.147C7.5788 18.7144 8.12065 18.4075 8.714 18.259L9.75 18L8.714 17.741C8.12065 17.5925 7.5788 17.2856 7.14639 16.853C6.71398 16.4204 6.40725 15.8784 6.259 15.285L6 14.25Z">
                            </path>
                            <path class="path" stroke-linejoin="round" stroke-linecap="round" stroke="black"
                                fill="black"
                                d="M6.5 4L6.303 4.5915C6.24777 4.75718 6.15472 4.90774 6.03123 5.03123C5.90774 5.15472 5.75718 5.24777 5.5915 5.303L5 5.5L5.5915 5.697C5.75718 5.75223 5.90774 5.84528 6.03123 5.96877C6.15472 6.09226 6.24777 6.24282 6.303 6.4085L6.5 7L6.697 6.4085C6.75223 6.24282 6.84528 6.09226 6.96877 5.96877C7.09226 5.84528 7.24282 5.75223 7.4085 5.697L8 5.5L7.4085 5.303C7.24282 5.24777 7.09226 5.15472 6.96877 5.03123C6.84528 4.90774 6.75223 4.75718 6.697 4.5915L6.5 4Z">
                            </path>
                        </svg>
                        <span class="text_button" style="font-size: 1rem; font-weight: 800;">Gerar Senha</span>
                    </button>

                    <input class="copy-toggle-checkbox" id="copy-toggle-checkbox" type="checkbox" />
                    <label class="copy-button" for="copy-toggle-checkbox"
                        onclick="copyToClipboard('generated-passphrase')">
                        <div class="copy-content">
                            <span class="copy-letters">
                                <span style="--i: 1" data-label="C">C</span>
                                <span style="--i: 2" data-label="o">o</span>
                                <span style="--i: 3" data-label="p">p</span>
                                <span style="--i: 4" data-label="i">i</span>
                                <span style="--i: 5" data-label="a">a</span>
                                <span style="--i: 6" data-label="r">r</span>
                                <span style="--i: 7" data-label="S">S</span>
                                <span style="--i: 8" data-label="e">e</span>
                                <span style="--i: 7" data-label="n">n</span>
                                <span style="--i: 8" data-label="h">h</span>
                                <span style="--i: 8" data-label="a">a</span>
                            </span>
                            <div class="copy-icon-container">
                                <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.6" fill="none" data-slot="icon"
                                    class="copy-icon">
                                    <path class="copy-bm"
                                        d="M12.0017 6V4M8.14886 7.40371L6.86328 5.87162M15.864 7.40367L17.1496 5.87158">
                                    </path>
                                    <path
                                        d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"
                                        stroke-linejoin="round" stroke-linecap="round" class="copy-link"></path>
                                </svg>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>
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
        // Predefined word list for passphrase generation
        const palavras = [
            "casa", "carro", "livro", "mesa", "café", "água", "porta", "chave",
            "papel", "lápis", "vida", "amor", "tempo", "terra", "fogo", "vento",
            "noite", "tarde", "festa", "praia", "monte", "chuva", "ponte", "flor",
            "peixe", "pedra", "verde", "azul", "gato", "copo", "prato", "faca",
            "bola", "jogo", "arte", "foto", "lago", "rio", "mar", "sol",
            "lua", "céu", "nuvem", "pão", "leite", "fruta", "doce", "sal",
            "ouro", "prata", "vidro", "metal", "roupa", "meia", "cinto", "bolsa"
        ];

        // Character sets for password generation
        const lowercase = "abcdefghijklmnopqrstuvwxyz";
        const uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        const numbers = "0123456789";
        const symbols = "!@#$%^&*()_+[]{}<>?|~";

        // Tab Switching Function
        function switchTab(tabName) {
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            // Add active class to selected tab
            document.querySelector(`.tab[onclick="switchTab('${tabName}')"]`).classList.add('active');

            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            // Show selected tab content
            document.getElementById(`${tabName}-tab`).classList.add('active');
        }

        // Password Generation Function
        function generatePassword() {
            // Get checkbox states
            const useLowercase = document.getElementById("use-lowercase").checked;
            const useUppercase = document.getElementById("use-uppercase").checked;
            const useNumbers = document.getElementById("use-numbers").checked;
            const useSymbols = document.getElementById("use-symbols").checked;

            // Get password length from slider
            const length = document.getElementById("custom-length-slider").value;

            // Build character set based on selected options
            let charSet = "";
            if (useLowercase) charSet += lowercase;
            if (useUppercase) charSet += uppercase;
            if (useNumbers) charSet += numbers;
            if (useSymbols) charSet += symbols;

            // Validate character set
            if (!charSet) {
                alert("Selecione pelo menos uma opção de caracteres.");
                return;
            }

            // Generate password
            let password = "";
            for (let i = 0; i < length; i++) {
                const randomIndex = Math.floor(Math.random() * charSet.length);
                password += charSet[randomIndex];
            }

            // Display generated password
            document.getElementById("generated-password").value = password;

            // Calcular e exibir a estimativa de tempo de quebra
            calculateBreakTime(password);
        }

        // Passphrase Generation Function
        function generatePassphrase() {
            // Get passphrase generation options
            const wordCount = parseInt(document.getElementById("words-slider").value);
            const capitalizeFirst = document.getElementById("capitalize-first").checked;
            const capitalizeAll = document.getElementById("capitalize-all").checked;
            const includeNumber = document.getElementById("include-number").checked;
            const separator = document.getElementById("word-separator").value;

            // Generate passphrase
            let selectedWords = [];
            let usedIndexes = new Set();

            // Select unique words
            while (selectedWords.length < wordCount) {
                const index = Math.floor(Math.random() * palavras.length);
                if (!usedIndexes.has(index)) {
                    let word = palavras[index];

                    // Apply capitalization
                    if (capitalizeAll || (capitalizeFirst && selectedWords.length === 0)) {
                        word = word.charAt(0).toUpperCase() + word.slice(1);
                    }

                    selectedWords.push(word);
                    usedIndexes.add(index);
                }
            }

            // Optionally add a random number
            if (includeNumber) {
                const randomNumber = Math.floor(Math.random() * 100);
                selectedWords.push(randomNumber.toString());
            }

            // Join words with separator
            const passphrase = selectedWords.join(separator);
            document.getElementById("generated-passphrase").value = passphrase;
        }

        // Break Time Calculation Function
        function calculateBreakTime(password) {
            const guessesPerSecond = 1e9; // 1 billion guesses per second
            const charSetSize = getCharacterSetSize(password);
            const combinations = Math.pow(charSetSize, password.length);
            const seconds = combinations / guessesPerSecond;

            let estimation = "";

            // Determine appropriate time units for estimation
            if (seconds < 60) {
                estimation = `Quebrável em ${seconds.toFixed(2)} segundos.`;
            } else if (seconds < 3600) {
                estimation = `Quebrável em ${(seconds / 60).toFixed(2)} minutos.`;
            } else if (seconds < 86400) {
                estimation = `Quebrável em ${(seconds / 3600).toFixed(2)} horas.`;
            } else if (seconds < 604800) { // less than a week
                estimation = `Quebrável em ${(seconds / 86400).toFixed(2)} dias.`;
            } else if (seconds < 2629746) { // less than a month (avg 30.44 days)
                estimation = `Quebrável em ${(seconds / 604800).toFixed(1)} semanas.`;
            } else if (seconds < 31557600) { // less than a year
                estimation = `Quebrável em ${(seconds / 2629746).toFixed(1)} meses.`;
            } else if (seconds < 315576000) { // less than a decade
                estimation = `Quebrável em ${(seconds / 31557600).toFixed(1)} anos.`;
            } else if (seconds < 3155760000) { // less than a century
                estimation = `Quebrável em ${(seconds / 315576000).toFixed(1)} décadas.`;
            } else {
                estimation = "Quebrável em séculos.";
            }

            // Display estimation
            document.getElementById("time-estimation").innerText = estimation;
        }

        // Character Set Size Calculation Function
        function getCharacterSetSize(password) {
            let charSet = 0;
            if (/[a-z]/.test(password)) charSet += 26; // lowercase letters
            if (/[A-Z]/.test(password)) charSet += 26; // uppercase letters
            if (/[0-9]/.test(password)) charSet += 10; // numbers
            if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) charSet += 32; // special characters
            return charSet;
        }

        // Copy to Clipboard Function
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            element.select();
            document.execCommand("copy");
            alert("Senha Copiada!");
        }

        // Slider Styling and Value Display Function for Password Length
        function showSliderValue() {
            const slider_input = document.getElementById('custom-length-slider');
            const slider_thumb = document.getElementById('custom-slider_thumb');
            const slider_line = document.getElementById('custom-slider_line');

            const length = slider_input.value;
            slider_thumb.innerHTML = length;  // Update visible thumb value

            const bulletPosition = (length / slider_input.max),
                space = slider_input.offsetWidth - slider_thumb.offsetWidth;

            slider_thumb.style.left = (bulletPosition * space) + 'px';  // Align thumb to slider value
            slider_line.style.width = (length / slider_input.max) * 100 + '%';  // Fill line with password length selection

            document.getElementById("length-value").innerText = length + " Caracteres";  // Display password length

            // Automatically generate a new password when slider changes
            generatePassword();
        }

        // Passphrase Words Slider Value Display
        function showPassphraseWordsValue() {
            const wordsSlider = document.getElementById('words-slider');
            const wordsValue = document.getElementById('words-value');
            const wordsSliderThumb = document.getElementById('words-slider-thumb');
            const wordsSliderLine = document.getElementById('words-slider-line');

            const words = wordsSlider.value;
            wordsValue.textContent = words;
            wordsSliderThumb.innerHTML = words;  // Update visible thumb value

            const bulletPosition = (words / wordsSlider.max),
                space = wordsSlider.offsetWidth - wordsSliderThumb.offsetWidth;

            wordsSliderThumb.style.left = (bulletPosition * space) + 'px';  // Align thumb to slider value
            wordsSliderLine.style.width = (words / wordsSlider.max) * 100 + '%';  // Fill line with word count selection

            // Automatically generate a new passphrase when slider changes
            generatePassphrase();
        }

        // Event Listeners Setup Function
        function setupEventListeners() {
            // Attach event listener to custom length slider
            const slider_input = document.getElementById('custom-length-slider');
            slider_input.addEventListener("input", showSliderValue);

            // Attach event listener to words slider
            const words_slider = document.getElementById('words-slider');
            words_slider.addEventListener("input", showPassphraseWordsValue);

            // Initialize slider values
            showSliderValue();
            showPassphraseWordsValue();

            // Attach event listeners to password checkboxes to regenerate password
            const passwordCheckboxes = document.querySelectorAll('#use-lowercase, #use-uppercase, #use-numbers, #use-symbols');
            passwordCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', generatePassword);
            });

            // Attach event listeners to passphrase checkboxes
            const passphraseCheckboxes = document.querySelectorAll('#capitalize-first, #capitalize-all, #include-number');
            passphraseCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', generatePassphrase);
            });

            // Add event listener to word separator
            const wordSeparator = document.getElementById('word-separator');
            wordSeparator.addEventListener('input', generatePassphrase);

            // Initial password and passphrase generation
            generatePassword();
            generatePassphrase();
        }

        // Run event listeners setup when DOM is fully loaded
        document.addEventListener('DOMContentLoaded', setupEventListeners);
    </script>

    <script src="./script/script.js"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="/script/scroll-reveal.js"></script>

</body>

</html>