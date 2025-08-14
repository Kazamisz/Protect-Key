# Instruções para Agentes (Protect-Key)

Este guia define como contribuir com segurança e consistência no projeto Protect-Key usando agentes de IA.

Consulte também: `README.md`, `GEMINI.md`, `php/functions.php`, `php/conectar.php`, `php/bootstrap.php` e páginas em `public/`.

## Stack e versões
- PHP 8.3 (Composer bloqueado para 8.3)
- MySQL (PDO)
- Dependências principais: vlucas/phpdotenv, pragmarx/google2fa, phpmailer/phpmailer, bacon/bacon-qr-code, mercadopago/dx-php
- Servidor: Apache (Dockerfile define DocumentRoot em `public/`, porta 8080)

## Estrutura
- `public/`: páginas acessíveis (HTML/JS/CSS + includes de scripts `php/`)
- `php/`: scripts de backend (login, registro, conta, gerenciador, armazenamento, etc.)
- `vendor/`: dependências do Composer
- `.github/instructions/*`: guias de contribuição para agentes

## Decisões e padrões obrigatórios
1) Banco de dados e conexões
   - Use apenas PDO. A conexão padrão é retornada por `php/conectar.php` (retorna `PDO`).
   - Não introduza MySQLi. Qualquer código MySQLi deve ser migrado para PDO.

2) Logs de auditoria
   - Padrão único: `log_action(PDO $conn, int $userID, string $actionType, string $description): void` definido em `php/functions.php`.
   - Não usar `logAction()` (camelCase). Se encontrado, padronizar para `log_action` e remover `php/logAction.php` quando possível.
   - Registrar IP (`getUserIP()`), `user_id`, ação, descrição e `NOW()`.

3) Autenticação e senhas
   - Hash de senha de usuário: sempre `password_hash()`/`password_verify()` (NUNCA SHA-256 puro).
   - 2FA: `pragmarx/google2fa` com segredo em `users.secret`. Verificação com `verifyKey()`.
   - Nunca exibir hashes/senhas em respostas. Ao exibir senhas do cofre, descriptografar no servidor e minimizar exposição no DOM.

4) Criptografia de senhas salvas (cofre)
   - AES-256-CBC via OpenSSL. A chave DEVE vir de variável de ambiente (ex.: `ENCRYPTION_KEY`) e ter 32 bytes reais. Gere a chave com base64 ou derive com `hash('sha256', $envKey, true)`.
   - IV aleatório por registro; armazenar junto (padrão `data::iv`). Não reutilizar IV.

5) Geração de tokens e códigos
   - Use `random_int()`/`random_bytes()` (criptograficamente seguro). Não usar `rand()`.
   - Garanta unicidade no DB quando necessário (ex.: `verification_codes`).

6) SQL e validação
   - Sempre `prepare` + `bind`/`execute`. Nunca interpolar.
   - Escapar saída em HTML com `htmlspecialchars`. Evitar ecoar dados sem sanitização.
   - Não usar prefixo de schema fixo (ex.: `gerenciadorsenhas.passwords`) nas queries; use o DB ativo.

7) Sessões e CSRF
   - `session_start()` apenas uma vez por request, preferencialmente na página de entrada em `public/`.
   - Use e valide token CSRF (há `generateCsrfToken()` em `functions.php`; formular e validar ao processar POSTs sensíveis).

8) E-mails
   - Envio via PHPMailer (SMTP) com variáveis de ambiente: `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_PORT`, `MAIL_FROM_ADDRESS`.
   - Não logar credenciais nem mensagens de erro completas para o usuário final; use `error_log`.

## Fluxos principais (referências)
- Registro: `public/register.php` → `php/register.php` (gera `userToken` + hash de senha; envia e-mail de token; loga `Registro`).
- Login: `public/login.php` → `php/login.php` (CPF ou token; se 2FA ativo, verifica código; loga `Login`).
- Conta: `php/conta.php` (atualiza dados, ativa/desativa 2FA, valida duplicidades; gera segredo 2FA se ausente).
- Cofre de senhas: `public/store_password.php` → `php/store_password.php` (CRUD de senhas com AES; limites por plano; logs).
- Documentos: `public/store_documents.php` → `php/store_documents.php` (CRUD com upload validado; limites por plano; logs).
- Planos/pagamentos: `php/preference.php` (Mercado Pago), `public/index.php` (links). Notificações: `php/notification.php`.

## Checklist de segurança para PRs/alterações
- [ ] Usa PDO e statements preparados? Sem concatenação.
- [ ] Tokens/códigos com `random_int`/`random_bytes`.
- [ ] Chave AES via ENV (32 bytes). Sem chaves hardcoded.
- [ ] `password_hash`/`password_verify` para credenciais de usuário (nunca SHA-256 puro).
- [ ] CSRF token presente e validado em POST sensível.
- [ ] Saída escapada com `htmlspecialchars` no HTML.
- [ ] Logs via `log_action` com `user_id`, ação, descrição e IP.
- [ ] Sem prefixo de schema no SQL.
- [ ] Upload: validar extensão + MIME (via `finfo_file`), tamanho e usar `move_uploaded_file`.

## Antipadrões identificados (migrar/evitar)
- Função `logAction()` (MySQLi). Padronizar para `log_action` (PDO) e remover `php/logAction.php`.
- Uso de `rand()` em geração de tokens/códigos (`functions.php`, `gerenciador.php`). Migrar para `random_int()`.
- Chave AES hardcoded em `php/functions.php` (`$key = 'my_secret_key'`). Migrar para ENV (32 bytes) e usar `hash('sha256', ...)` binário se necessário.
- Hash de senha SHA-256 em `php/conta.php`. Migrar para `password_hash`/`password_verify`.
- Queries com schema fixo em `php/store_password.php`. Remover o schema prefixo.
- Geração de senhas no front com `Math.random()`. Se necessário forte, usar `crypto.getRandomValues`.

## Como propor alterações (agente)
1) Entenda o fluxo afetado e identifique os scripts em `public/` e `php/` envolvidos.
2) Se alterar contratos (assinaturas, nomes), ajuste todos os usos (busca por referências).
3) Garanta compatibilidade com PDO e padrões acima. Evite mudanças invasivas sem necessidade.
4) Valide manualmente: Docker (Apache 8080), `composer install`, fluxos registro/login/2FA/cofre/documentos/pagamentos.
5) Documente no PR o que mudou, riscos, rollback e passos de teste.

## Snippets úteis
- Chave AES a partir de ENV (32 bytes):
  - `$raw = getenv('ENCRYPTION_KEY');`
  - `$key = strlen((string)$raw) === 32 ? $raw : hash('sha256', (string)$raw, true);`

- Token seguro de 6 dígitos único:
  - `do { $token = (string)random_int(100000, 999999); /* verifica duplicidade */ } while($exists);`

## Observações
- Sempre documente em pt-BR.
- Não exponha mensagens de erro detalhadas ao usuário; use `error_log`.
- Respeite LGPD: minimize dados pessoais logados/exibidos.
- Sempre deixarei o arquivo '.gitignore' sem informações enquanto estiver precisando da sua ajuda, mas antes de fazer push, vou garantir que ele esteja atualizado.