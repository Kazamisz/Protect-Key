<?php
require('./php/store_documents.php');

$userID = $_SESSION['userID'];

$showAddButton = true;

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
    <link rel="stylesheet" href="./style/styles-store_documents.css">
    <link rel="stylesheet" href="./style/style-doc-form.css">
    <link rel="stylesheet" href="./style/styles-buttons.css">

    <title>Controle de Documentos</title>
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
                        <a href="store_documents.php" class="navbar-item">Controle de Documentos</a>
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
                                <p>Bem-vindo,
                                    <?php echo $primeiroNome; ?>
                                </p>
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
        <!-- Formulário de adição/atualização de documento -->
        <section class="form-container" id="formContainer">
            <form id="documentForm" action="" method="post" enctype="multipart/form-data">
                <!-- Campos ocultos para identificar ação e ID do documento -->
                <input type="hidden" id="actionType" name="actionType" value="add">
                <input type="hidden" id="documentId" name="documentId" value="">

                <!-- Inputs estilizados do formulário -->
                <div class="input-group">
                    <label class="label" for="documentName">Nome do Documento</label>
                    <input autocomplete="off" name="documentName" id="documentName" class="input" type="text"
                        placeholder="Ex: Comprovante de Matrícula" maxlength="50" required>
                </div>

                <div class="input-group">
                    <label class="label" for="documentType">Tipo de Documento</label>
                    <select name="documentType" id="documentType" class="input" required>
                        <option value="">Tipo de Documento</option>
                        <option value="rg">RG</option>
                        <option value="cpf">CPF</option>
                        <option value="cnh">CNH</option>
                        <option value="passaporte">Passaporte</option>
                        <option value="certidao">Certidão</option>
                        <option value="comprovante">Comprovante</option>
                        <option value="outros">Outros</option>
                    </select>
                </div>

                <div class="input-group">
                    <label class="label" for="documentNumber">Número do Documento</label>
                    <input autocomplete="off" name="documentNumber" id="documentNumber" class="input" type="text"
                        placeholder="Ex: 12.345.678-9" maxlength="30">
                </div>

                <div class="input-group">
                    <label class="label" for="issueDate">Data de Emissão</label>
                    <input autocomplete="off" name="issueDate" id="issueDate" class="input" type="date">
                </div>

                <div class="input-group">
                    <label class="label" for="observations">Informações</label>
                    <textarea name="observations" id="observations" class="input" placeholder="Observações adicionais"
                        maxlength="255"></textarea>
                </div>



                <div class="form-buttons" style="margin-top: 50px;">
                    <!-- Botão salvar -->
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

                    <!-- Botão cancelar -->
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
            </form>
        </section>

        <!-- Tabela com documentos salvos -->
        <section id="savedTable" style="padding: 80px 150px 0 150px;">
            <?php if (!empty($savedDocuments)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nome do Documento</th>
                            <th>Tipo</th>
                            <th>Número</th>
                            <th>Data de Emissão</th>
                            <th style="width: 100px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Laço para exibir documentos salvos -->
                        <?php foreach ($savedDocuments as $document): ?>
                            <tr>
                                <td data-label="Nome">
                                    <?php echo htmlspecialchars($document['document_name']); ?>
                                </td>
                                <td data-label="Tipo">
                                    <?php echo htmlspecialchars($document['document_type']); ?>
                                </td>
                                <td data-label="Número">
                                    <?php echo htmlspecialchars($document['document_number']); ?>
                                </td>
                                <td data-label="Data de Emissão">
                                    <?php echo htmlspecialchars($document['issue_date']); ?>
                                </td>


                                <td data-label="Ações" class="buttons" style="display:flex; justify-content:center;">

                                    <!-- Botão de atualização de documento -->
                                    <button style="margin-right: 20px;" type="button" class="button" onclick="editDocument(<?php echo htmlspecialchars($document['documentId']); ?>,
                                    '<?php echo htmlspecialchars($document['document_name']); ?>', 
                                    '<?php echo htmlspecialchars($document['document_type']); ?>', 
                                    '<?php echo htmlspecialchars($document['document_number']); ?>', 
                                    '<?php echo htmlspecialchars($document['issue_date']); ?>')">
                                        <span class="button__text">Atualizar</span>
                                        <span class="button__icon">
                                            <svg class="svg" height="48" viewBox="0 0 48 48" width="48"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M35.3 12.7c-2.89-2.9-6.88-4.7-11.3-4.7-8.84 0-15.98 7.16-15.98 16s7.14 16 15.98 16c7.45 0 13.69-5.1 15.46-12h-4.16c-1.65 4.66-6.07 8-11.3 8-6.63 0-12-5.37-12-12s5.37-12 12-12c3.31 0 6.28 1.38 8.45 3.55l-6.45 6.45h14v-14l-4.7 4.7z">
                                                </path>
                                                <path d="M0 0h48v48h-48z" fill="none"></path>
                                            </svg>
                                        </span>
                                    </button>

                                    <!-- Formulário para exclusão de documento -->
                                    <form action="" method="post" style="width: fit-content;">
                                        <input type="hidden" name="documentId"
                                            value="<?php echo htmlspecialchars($document['documentId']); ?>">
                                        <input type="hidden" name="actionType" value="delete">
                                        <button type="submit" class="bin-button"
                                            onclick="return confirm('Tem certeza que deseja excluir este documento?')">
                                            <svg class="bin-top" viewBox="0 0 39 7" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <line y1="5" x2="39" y2="5" stroke="white" stroke-width="4"></line>
                                                <line x1="12" y1="1.5" x2="26.0357" y2="1.5" stroke="white" stroke-width="3">
                                                </line>
                                            </svg>
                                            <svg class="bin-bottom" viewBox="0 0 33 39" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <mask id="path-1-inside-1_8_19" fill="white">
                                                    <path
                                                        d="M0 0H33V35C33 37.2091 31.2091 39 29 39H4C1.79086 39 0 37.2091 0 35V0Z">
                                                    </path>
                                                </mask>
                                                <path
                                                    d="M0 0H33H0ZM37 35C37 39.4183 33.4183 43 29 43H4C-0.418278 43 -4 39.4183 -4 35H4H29H37ZM4 43C-0.418278 43 -4 39.4183 -4 35V0H4V35V43ZM37 0V35C37 39.4183 33.4183 43 29 43V35V0H37Z"
                                                    fill="white" mask="url(#path-1-inside-1_8_19)"></path>
                                                <path d="M12 6L12 29" stroke="white" stroke-width="4"></path>
                                                <path d="M21 6V29" stroke="white" stroke-width="4"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <!-- Pode adicionar uma mensagem ou tratamento para quando não houver documentos -->
            <?php endif; ?>
        </section>

        <!-- Imagem adicionar documento -->
        <img src="./img/sem-doc.png" alt="Adicionar Documento" class="img-no-itens" id="img-documento">

        <!-- Botão para adicionar documento ou mensagem de limite atingido -->
        <?php if ($showAddButton): ?>
            <button type="button" class="botao-adicionar" onclick="toggleForm()">
                <span class="botao-adicionar__texto">Adicionar</span>
            </button>
        <?php else: ?>
            <p
                style="color: red; font-weight: bold; font-size: 18px; padding: 10px; background-color: #fdd; border-radius: 10px; width:fit-content; margin: 60px auto 0 auto;">
                Limite de documentos salvos pelo plano básico atingido</p>
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
        document.addEventListener('DOMContentLoaded', function () {
            // Seleção de elementos principais
            const formContainer = document.getElementById('formContainer');
            const savedTable = document.getElementById('savedTable');
            const imgDocumento = document.getElementById('img-documento');
            const botaoAdicionar = document.querySelector('.botao-adicionar');
            const documentForm = document.getElementById('documentForm');
            const cancelButton = document.querySelector('.db-button.db-noselect');

            // Função para validar campos obrigatórios
            function validateRequiredFields() {
                const documentName = document.getElementById('documentName');
                const documentType = document.getElementById('documentType');
                const issueDate = document.getElementById('issueDate');

                return documentName.value.trim() !== '' &&
                    documentType.value !== '' &&
                    issueDate.value.trim() !== '';
            }

            // Função para mostrar/ocultar elementos conforme estado inicial
            function updateInitialState() {
                const hasSavedDocuments = savedTable && savedTable.querySelector('tbody')?.rows.length > 0;

                if (hasSavedDocuments) {
                    // Se há documentos salvos
                    savedTable.style.display = 'block';
                    imgDocumento.style.display = 'none';
                    botaoAdicionar.style.display = 'block';
                    formContainer.classList.remove('show');
                } else {
                    // Se não há documentos salvos
                    savedTable.style.display = 'none';
                    imgDocumento.style.display = 'flex';
                    botaoAdicionar.style.display = 'block';
                    formContainer.classList.remove('show');
                }
            }

            // Função para exibir formulário suavemente
            function showFormContainer() {
                formContainer.classList.add('show');
                savedTable.style.display = 'none';
                imgDocumento.style.display = 'none';
                botaoAdicionar.style.display = 'none';

                // Resetar formulário para novo documento
                documentForm.reset();
                document.getElementById('actionType').value = 'add';
                document.getElementById('documentId').value = '';
            }

            // Função para fechar formulário suavemente
            function hideFormContainer(forceShowSavedTable = true) {
                formContainer.classList.remove('show');

                const hasSavedDocuments = savedTable && savedTable.querySelector('tbody')?.rows.length > 0;

                if (forceShowSavedTable && hasSavedDocuments) {
                    savedTable.style.display = 'block';
                    imgDocumento.style.display = 'none';
                } else {
                    savedTable.style.display = 'none';
                    imgDocumento.style.display = 'flex';
                }

                botaoAdicionar.style.display = 'block';
            }

            // Adicionar evento no botão de adicionar
            botaoAdicionar.addEventListener('click', showFormContainer);

            // Adicionar evento no botão de cancelar
            cancelButton.addEventListener('click', () => hideFormContainer());

            // Validar e submeter formulário
            documentForm.addEventListener('submit', function (e) {
                e.preventDefault();

                if (!validateRequiredFields()) {
                    alert('Por favor, preencha todos os campos obrigatórios.');
                    return;
                }

                const formData = new FormData(this);

                fetch('', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.text())
                    .then(result => {
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = result;

                        const newRow = tempDiv.querySelector('#savedTable table tbody tr:last-child');

                        if (newRow) {
                            const tableBody = document.querySelector('#savedTable table tbody');

                            if (tableBody) {
                                tableBody.appendChild(newRow);
                                hideFormContainer(true);
                                updateInitialState();

                                // Adicionar eventos de edição e exclusão para a nova linha
                                attachDocumentRowEvents(newRow);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao salvar o documento');
                    });
            });

            // Função para adicionar eventos de edição e exclusão em linhas de documento
            function attachDocumentRowEvents(row) {
                const editButton = row.querySelector('.button');
                const deleteForm = row.querySelector('form');

                if (editButton) {
                    editButton.addEventListener('click', function () {
                        const documentId = this.closest('tr').querySelector('input[name="documentId"]').value;
                        const documentName = this.closest('tr').querySelector('td[data-label="Nome"]').textContent.trim();
                        const documentType = this.closest('tr').querySelector('td[data-label="Tipo"]').textContent.trim();
                        const documentNumber = this.closest('tr').querySelector('td[data-label="Número"]').textContent.trim();
                        const issueDate = this.closest('tr').querySelector('td[data-label="Data de Emissão"]').textContent.trim();

                        document.getElementById('actionType').value = 'update';
                        document.getElementById('documentId').value = documentId;
                        document.getElementById('documentName').value = documentName;
                        document.getElementById('documentType').value = documentType;
                        document.getElementById('documentNumber').value = documentNumber;
                        document.getElementById('issueDate').value = issueDate;

                        showFormContainer();
                    });
                }

                if (deleteForm) {
                    deleteForm.addEventListener('submit', function (e) {
                        e.preventDefault();

                        if (confirm('Tem certeza que deseja excluir este documento?')) {
                            const formData = new FormData(this);

                            fetch('', {
                                method: 'POST',
                                body: formData
                            })
                                .then(response => response.text())
                                .then(() => {
                                    this.closest('tr').remove();
                                    updateInitialState();
                                })
                                .catch(error => {
                                    console.error('Erro:', error);
                                    alert('Erro ao excluir o documento');
                                });
                        }
                    });
                }
            }

            // Adicionar eventos de edição e exclusão para linhas existentes
            document.querySelectorAll('#savedTable table tbody tr').forEach(attachDocumentRowEvents);

            // Estado inicial
            updateInitialState();
        });
    </script>
</body>