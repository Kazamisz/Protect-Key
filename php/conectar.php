<?php
// Carrega Dotenv para popular variáveis a partir do .env quando disponível
require_once __DIR__ . '/bootstrap.php';

// Ler EXCLUSIVAMENTE variáveis de ambiente DB_* (conforme solicitado)
$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '';
$db   = getenv('DB_DATABASE') ?: '';
$user = getenv('DB_USERNAME') ?: '';
$pass = getenv('DB_PASSWORD') ?: '';

// Se rodando local e sem variáveis definidas, sugerir defaults comuns (sem aplicar automaticamente)
$isLocalCli = PHP_SAPI === 'cli-server' || PHP_SAPI === 'cli';

// Validação mínima para evitar conexões com parâmetros vazios
if ($host === '' || $port === '' || $db === '' || $user === '') {
    $msg = 'Configuração de banco incompleta: defina DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME e DB_PASSWORD no ambiente ou no arquivo .env na raiz do projeto.';
    error_log($msg);
    if ($isLocalCli) {
        // Ajuda para ambiente local
        $hint = "\nDica: copie .env.example para .env e ajuste os valores. Depois rode 'composer install' e reinicie o servidor embutido.";
        $msg .= $hint;
    }
    http_response_code(500);
    exit($msg);
}

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Loga erro sem expor detalhes ao usuário final (versão segura)
    error_log('Erro de conexão ao banco: ' . $e->getMessage());
    http_response_code(500);
    // Para depuração, você pode querer ver o erro exato.
    // Cuidado: não exponha isso em produção real para o usuário final.
    $publicMsg = 'Erro interno no servidor. Verifique os logs para mais detalhes.';
    if ($isLocalCli) {
        $publicMsg .= "\nVerifique se o MySQL está ativo e as variáveis DB_* estão corretas no .env.";
    }
    exit($publicMsg);
}

// Retorna objeto PDO pra uso no seu app
return $pdo;