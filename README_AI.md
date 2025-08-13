# Instruções para Agentes de IA do GitHub Copilot para o projeto Protect-Key

## Referências
- GEMINI.md: instruções detalhadas para agentes.
- README.md: visão geral do projeto.
- php/functions.php: utilidades, sanitização, criptografia e envio de e-mails.
- php/conectar.php: conexão MySQL (PDO e MySQLi, dependendo do script).
- php/conta.php e php/login.php: autenticação 2FA (Google Authenticator).
- php/logAction.php: logging de ações (atenção à assinatura: pode variar entre logAction e log_action).

> Consulte sempre este arquivo e GEMINI.md antes de propor mudanças estruturais ou fluxos não convencionais.

## Visão Geral
Protect-Key é um cofre digital de senhas, documentos e licenças, feito em **PHP procedural puro** (sem frameworks), com autenticação 2FA, criptografia AES para senhas salvas, e pagamentos via Mercado Pago. O objetivo é máxima segurança, simplicidade e portabilidade.

## Estrutura e Arquitetura
- **Pasta php/**: scripts principais do backend (login, registro, gerenciamento, etc).
- **Pasta public/**: arquivos acessíveis ao usuário (páginas, assets, scripts JS, CSS).
- **Banco de Dados**: MySQL, conexão via php/conectar.php (usa variáveis de ambiente carregadas por bootstrap.php e .env).
- **Sessões**: gerenciadas em PHP, ponto de entrada é public/index.php.
- **Funções utilitárias**: centralizadas em php/functions.php (criptografia AES, validação, envio de e-mail, geração de tokens, etc).
- **2FA**: lógica implementada diretamente em scripts como php/conta.php e php/login.php, utilizando a biblioteca pragmarx/google2fa.
- **Pagamentos**: integração Mercado Pago via mercadopago/dx-php (Composer).
- **API/AJAX**: endpoints distribuídos pelos scripts PHP individuais, retornam JSON.
- **Controle de planos**: limites de senhas/documentos por plano definidos nos scripts.

## Convenções e Padrões
- **PHP procedural**: não use classes exceto para bibliotecas de terceiros.
- **Prepared Statements**: obrigatório para queries SQL (atenção: há uso misto de PDO e MySQLi).
- **Sanitização**: use filter_var(), htmlspecialchars() e outras funções nativas do PHP para sanitização de entradas.
- **Nomenclatura**:
  - Tabelas: snake_case_plural (ex: users, passwords).
  - PK: camelCase com sufixo Id/ID (ex: userID).
  - FK para usuário: user_id.
- **Sessão**: use $_SESSION, logout sempre com session_destroy().
- **Logs**: registre ações críticas com log_action($conn, $userID, $actionType, $description) (ou logAction em scripts MySQLi).
- **Criptografia**: senhas salvas são criptografadas com AES-256 (funções em functions.php).
- **Idioma**: todo código, comentários e respostas em **pt-BR**.

## Fluxos e Workflows
- **Build/Deploy**: Heroku, via Procfile (Nginx + PHP-FPM). Não há build manual.
- **Dependências**: Composer (composer install).
- **Testes**: Não há suíte automatizada; valide manualmente via browser.
- **Debug**: Use logs em php/logAction.php e mensagens JSON.
- **Variáveis sensíveis**: sempre use variáveis de ambiente para credenciais e chaves.

## Exemplos de Padrão
- **Deletar senha**:
  1. Verifique se o usuário está logado e é dono da senha.
  2. Use prepared statement para DELETE (atenção ao driver: PDO ou MySQLi).
  3. Registre no log.
  4. Retorne JSON de sucesso/erro (ou redirecione, nunca exponha detalhes sensíveis).

## Segurança
- Nunca concatene variáveis de usuário em SQL.
- Sempre sanitize entradas usando filter_var(), htmlspecialchars() ou funções nativas.
- Use prepared statements.
- Não exponha detalhes sensíveis em respostas JSON.
- Sempre apague dados em ordem correta para respeitar chaves estrangeiras (exclusão de conta).

## Estrutura de Arquivos Detalhada

### Pasta php/ (Backend)
- **bootstrap.php**: carregamento de variáveis de ambiente (.env)
- **conectar.php**: conexão com banco de dados (retorna objeto PDO)
- **functions.php**: funções utilitárias (criptografia, email, validação, logs)
- **logAction.php**: função de logging usando MySQLi
- **login.php**: autenticação e 2FA
- **register.php**: registro de usuários
- **conta.php**: gerenciamento de conta e 2FA
- **gerenciador.php**: painel administrativo
- **store_password.php**: salvamento de senhas
- **store_documents.php**: salvamento de documentos
- **planos.php**: gerenciamento de planos
- **preference.php**: configurações de pagamento Mercado Pago
- **logout.php**: logout seguro

### Pasta public/ (Frontend)
- **index.php**: página inicial (ponto de entrada)
- **login.php**: interface de login
- **register.php**: interface de registro
- **conta.php**: interface de gerenciamento de conta
- **gerenciador.php**: interface administrativa
- **store_password.php**: interface para salvar senhas
- **store_documents.php**: interface para salvar documentos
- **gerador_senha.php**: gerador de senhas
- **planos.php**: visualização de planos
- **logs.php**: visualização de logs (admin)
- **style/**: arquivos CSS
- **script/**: arquivos JavaScript
- **img/**: imagens e recursos

## Banco de Dados (MySQL)

### Tabelas Principais
- **users**: dados do usuário (userID, userNome, userEmail, userPassword, plano, role, enableTwoFactor, secret, etc)
- **passwords**: senhas criptografadas (user_id, site, username, encrypted_password, etc)
- **documents**: documentos salvos (user_id, title, content, etc)
- **logs**: registro de ações (user_id, action_type, description, ip_address, created_at)
- **verification_codes**: códigos de verificação para reset de senha

### Conexões de Banco
- **PDO**: usado em php/functions.php e alguns scripts principais
- **MySQLi**: usado em php/logAction.php e alguns scripts específicos
- **Configuração**: ambiente local vs produção (Railway) configurado em conectar.php

## Dependências (Composer)
- **mercadopago/dx-php**: integração de pagamentos
- **pragmarx/google2fa**: autenticação 2FA (Google Authenticator)
- **phpmailer/phpmailer**: envio de emails
- **bacon/bacon-qr-code**: geração de QR codes para 2FA
- **vlucas/phpdotenv**: carregamento de variáveis de ambiente

## Autenticação e Segurança

### Sistema de Login
1. Verificação de credenciais (userEmail + userPassword hash)
2. Verificação 2FA opcional (se enableTwoFactor = 1)
3. Criação de sessão ($_SESSION['userID'], $_SESSION['userNome'])
4. Log da ação de login

### Criptografia de Senhas
- **Senhas de usuário**: password_hash() e password_verify()
- **Senhas salvas**: AES-256-CBC (encryptPassword/decryptPassword em functions.php)
- **Chave de criptografia**: definida em functions.php (deve ser em variável de ambiente)

### Sistema de Logs
- **log_action()**: função PDO em functions.php
- **logAction()**: função MySQLi em logAction.php
- **Campos**: user_id, action_type, description, ip_address, created_at

## Controle de Planos
- **básico**: limitações definidas nos scripts
- **pro**: funcionalidades expandidas
- **premium**: todas as funcionalidades + recursos avançados
- **Verificação**: getUserPlan() em functions.php

## Exemplo de Implementação: Nova Funcionalidade

### Para adicionar uma nova funcionalidade (ex: compartilhar senha):
1. **Backend (php/share_password.php)**:
   ```php
   <?php
   session_start();
   require_once 'conectar.php';
   require_once 'functions.php';
   
   if (!isset($_SESSION['userID'])) {
       http_response_code(401);
       echo json_encode(['error' => 'Não autorizado']);
       exit;
   }
   
   // Sanitizar e validar entradas
   $passwordId = filter_var($_POST['password_id'], FILTER_VALIDATE_INT);
   $targetEmail = filter_var($_POST['target_email'], FILTER_VALIDATE_EMAIL);
   
   if (!$passwordId || !$targetEmail) {
       echo json_encode(['error' => 'Dados inválidos']);
       exit;
   }
   
   // Verificar se a senha pertence ao usuário
   $stmt = $pdo->prepare("SELECT * FROM passwords WHERE passwordId = ? AND user_id = ?");
   $stmt->execute([$passwordId, $_SESSION['userID']]);
   $password = $stmt->fetch();
   
   if (!$password) {
       echo json_encode(['error' => 'Senha não encontrada']);
       exit;
   }
   
   // Lógica de compartilhamento...
   
   // Log da ação
   log_action($pdo, $_SESSION['userID'], 'share_password', "Senha compartilhada para: $targetEmail");
   
   echo json_encode(['success' => 'Senha compartilhada com sucesso']);
   ?>
   ```

2. **Frontend (public/share_password.php)**: interface de usuário
3. **JavaScript**: chamadas AJAX para o backend
4. **Validação**: sempre no frontend e backend
5. **Logs**: registrar todas as ações importantes

## Debugging e Troubleshooting
- **Logs de erro**: error_log() no PHP
- **Logs de ação**: tabela logs no banco
- **Variáveis de ambiente**: verificar bootstrap.php e .env
- **Conexão DB**: verificar conectar.php
- **Sessões**: verificar se session_start() foi chamado

## Boas Práticas para Agentes de IA
1. **Sempre preserve a arquitetura procedural** - não introduza classes
2. **Use prepared statements** - nunca concatene SQL
3. **Sanitize todas as entradas** - use funções de functions.php
4. **Registre ações importantes** - use log_action() ou logAction()
5. **Mantenha compatibilidade** - teste com PDO e MySQLi conforme necessário
6. **Respeite os planos** - implemente limitações adequadas
7. **Mantenha segurança** - nunca exponha dados sensíveis
8. **Use português** - comentários, mensagens e documentação em pt-BR