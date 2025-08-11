
# Instruções para Agentes de IA no Protect-Key

## Referências
- `GEMINI.md`: instruções detalhadas para agentes.
- `README.md`: visão geral do projeto.
- `php/functions.php`: utilidades, sanitização, criptografia e envio de e-mails.
- `php/conectar.php`: conexão MySQL (PDO e MySQLi, dependendo do script).
- `php/conta.php` e `php/login.php`: autenticação 2FA (Google Authenticator).
- `php/logAction.php`: logging de ações (atenção à assinatura: pode variar entre logAction e log_action).

> Consulte sempre este arquivo e `GEMINI.md` antes de propor mudanças estruturais ou fluxos não convencionais.

## Visão Geral
Protect-Key é um cofre digital de senhas, documentos e licenças, feito em **PHP procedural puro** (sem frameworks), com autenticação 2FA, criptografia AES para senhas salvas, e pagamentos via Mercado Pago. O objetivo é máxima segurança, simplicidade e portabilidade.

## Estrutura e Arquitetura
- **Pasta `php/`**: scripts principais do backend (login, registro, gerenciamento, etc).
- **Pasta `public/`**: arquivos acessíveis ao usuário (páginas, assets, scripts JS, CSS).
- **Banco de Dados**: MySQL, conexão via `php/conectar.php` (usa variáveis de ambiente carregadas por `bootstrap.php` e `.env`).
- **Sessões**: gerenciadas em PHP, ponto de entrada é `public/index.php`.
- **Funções utilitárias**: centralizadas em `php/functions.php` (criptografia AES, validação, envio de e-mail, geração de tokens, etc).
- **2FA**: lógica implementada diretamente em scripts como `php/conta.php` e `php/login.php`, utilizando a biblioteca `pragmarx/google2fa`.
- **Pagamentos**: integração Mercado Pago via `mercadopago/dx-php` (Composer).
- **API/AJAX**: endpoints centralizados, retornam JSON.
- **Controle de planos**: limites de senhas/documentos por plano definidos nos scripts.

## Convenções e Padrões
- **PHP procedural**: não use classes exceto para bibliotecas de terceiros.
- **Prepared Statements**: obrigatório para queries SQL (atenção: há uso misto de PDO e MySQLi).
- **Sanitização**: sempre use `sanitize()` de `functions.php` para entradas do usuário.
- **Nomenclatura**:
  - Tabelas: `snake_case_plural` (ex: `users`, `passwords`).
  - PK: `camelCase` com sufixo `Id`/`ID` (ex: `userID`).
  - FK para usuário: `user_id`.
- **Sessão**: use `$_SESSION`, logout sempre com `session_destroy()`.
- **Logs**: registre ações críticas com `log_action($conn, $userID, $actionType, $description)` (ou `logAction` em scripts MySQLi).
- **Criptografia**: senhas salvas são criptografadas com AES-256 (funções em `functions.php`).
- **Idioma**: todo código, comentários e respostas em **pt-BR**.

## Fluxos e Workflows
- **Build/Deploy**: Heroku, via `Procfile` (Nginx + PHP-FPM). Não há build manual.
- **Dependências**: Composer (`composer install`).
- **Testes**: Não há suíte automatizada; valide manualmente via browser.
- **Debug**: Use logs em `php/logAction.php` e mensagens JSON.
- **Variáveis sensíveis**: sempre use variáveis de ambiente para credenciais e chaves.

## Exemplos de Padrão
- **Deletar senha**:
  1. Verifique se o usuário está logado e é dono da senha.
  2. Use prepared statement para `DELETE` (atenção ao driver: PDO ou MySQLi).
  3. Registre no log.
  4. Retorne JSON de sucesso/erro (ou redirecione, nunca exponha detalhes sensíveis).

## Segurança
- Nunca concatene variáveis de usuário em SQL.
- Sempre sanitize entradas.
- Use prepared statements.
- Não exponha detalhes sensíveis em respostas JSON.
- Sempre apague dados em ordem correta para respeitar chaves estrangeiras (exclusão de conta).