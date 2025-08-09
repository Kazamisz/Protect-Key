# Instruções para o Gemini Code Assist no Projeto ProtectKey

## 1. Visão Geral do Projeto
Este é o ProtectKey, um sistema de gerenciamento de senhas, documentos e licenças desenvolvido em **PHP puro, procedural e sem frameworks**. O objetivo é fornecer um cofre digital seguro para os usuários, com autenticação de dois fatores (2FA) e integração de pagamentos via Mercado Pago para planos de assinatura.

## 2. Estrutura e Padrões de Código
- **Linguagem Principal:** Use exclusivamente **PHP procedural**. Não sugira classes ou arquitetura orientada a objetos, a menos que seja para interagir com as bibliotecas do `vendor`.
- **Estrutura de Arquivos:**
    - Novas páginas visíveis ao usuário devem ser criadas na pasta `pages/`.
    - Funções de utilidade geral (sanitização, validação, etc.) devem ser adicionadas ao arquivo `includes/functions.php`.
    - A conexão com o banco de dados é gerenciada exclusivamente por `includes/db_connect.php`. Sempre o inclua com `require_once`.
    - A lógica de 2FA está centralizada em `includes/twofa.php`.
- **Ponto de Entrada:** O `index.php` na raiz é o ponto de entrada que gerencia as sessões.
- **Dependências:** O projeto usa Composer. As dependências principais são `mercadopago/dx-php` para pagamentos e `pragmarx/google2fa` para 2FA. Ao gerar código, utilize essas bibliotecas para suas respectivas finalidades.

## 3. Interação com o Banco de Dados (MySQL)
- **Conexão:** Utilize a variável de conexão PDO ou mysqli estabelecida em `db_connect.php`.
- **Segurança:** **Sempre** use `prepared statements` (declarações preparadas) para evitar SQL Injection. Não construa queries concatenando strings com variáveis de usuário.
- **Nomenclatura:**
    - Tabelas usam `snake_case_plural` (ex: `users`, `passwords`).
    - Chaves primárias são `camelCase` com sufixo "Id" ou "ID" (ex: `documentId`, `userID`).
    - Chaves estrangeiras que referenciam `users.userID` devem ser nomeadas `user_id`.
- **Tabelas Principais e Suas Funções:**
    - `users`: Armazena dados do usuário. Senhas devem ser hasheadas com `password_hash()`.
    - `passwords`: Cofre de senhas do usuário.
    - `documents`: Cofre de documentos do usuário.
    - `logs`: Registro de todas as ações importantes (login, erro, criação, etc.). Use a função `log_action($user_id, $action, $description)` para registrar eventos.
    - `verification_codes`: Códigos para verificação de email ou recuperação de conta.

## 4. Segurança e Funções Essenciais
- **Autenticação:** O login é validado contra o `userPassword` na tabela `users` usando `password_verify()`.
- **Sessões:** Gerencie sessões com as variáveis `$_SESSION` do PHP. O logout deve sempre usar `session_destroy()`.
- **Sanitização de Entradas:** Todas as entradas do usuário (`$_POST`, `$_GET`) devem ser sanitizadas antes de usadas. Utilize a função `sanitize()` que já existe em `functions.php`.
- **2FA (Autenticação de Dois Fatores):** Para gerar e validar códigos TOTP do Google Authenticator, utilize as funções disponíveis em `twofa.php`, que por sua vez usam a biblioteca `pragmarx/google2fa`.
- **API/AJAX:** Endpoints para chamadas assíncronas devem ser centralizados em `api.php`. Eles devem receber dados e sempre retornar uma resposta em formato **JSON**.

## 5. Exemplo de Tarefa
**Se eu pedir:** "Crie a funcionalidade para o usuário deletar uma senha salva."

**Sua abordagem deve ser:**
1.  Criar um `delete_password.php` em `pages/` ou adicionar uma lógica em `api.php`.
2.  Verificar se o usuário está logado e se a senha que ele quer deletar pertence a ele (comparando `passwords.user_id` com o `$_SESSION['userID']`).
3.  Usar uma `prepared statement` para executar o `DELETE FROM passwords WHERE senhaId = ? AND user_id = ?`.
4.  Registrar a ação na tabela de logs usando `log_action()`.
5.  Retornar uma mensagem de sucesso ou erro em JSON para o front-end.

## 6. Idioma e Comunicação
- **Idioma Principal:** Toda a comunicação, incluindo respostas no chat, comentários de código, documentação e sugestões de mensagens de commit, deve ser estritamente em **Português do Brasil (pt-BR)**.
- **Clareza:** As respostas devem ser claras, diretas e técnicas, evitando linguagem ambígua.

## 7. Estratégia de Execução e Confiabilidade
- **Princípio da Cautela:** Antes de executar qualquer tarefa que modifique o sistema de arquivos (criar, editar ou deletar arquivos), você deve priorizar a segurança e a previsibilidade em vez da velocidade. Evite falhas em cascata.

- **Estratégia "Ler-Modificar-Reescrever":** Para qualquer alteração no conteúdo de um arquivo existente, sua abordagem **padrão e obrigatória** deve ser:
    1.  **Ler:** Primeiro, use a ferramenta `ReadFile` para carregar o conteúdo completo do arquivo em memória.
    2.  **Planejar:** Anuncie o plano de modificação exato que você fará no conteúdo lido.
    3.  **Executar (em memória):** Realize a modificação no texto carregado.
    4.  **Reescrever:** Use a ferramenta `WriteFile` para substituir o arquivo antigo pelo novo conteúdo modificado.
    5.  **NÃO use ferramentas de edição direta (`Edit`, `substitute`, `replace`) como primeira opção.** Elas são instáveis e devem ser evitadas.

- **Plano de Ação Explícito:** Para tarefas complexas que envolvem múltiplos arquivos ou passos (ex: "refatore X e atualize suas importações em Y e Z"), você deve primeiro apresentar um **plano de ação numerado e detalhado**. Não execute nenhuma ferramenta até que o plano seja apresentado. Isso garante que sua lógica está correta antes de iniciar as modificações.
