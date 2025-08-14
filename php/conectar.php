<?php
// Carrega Dotenv para popular variáveis a partir do .env quando disponível
require_once __DIR__ . '/bootstrap.php';

// Ler EXCLUSIVAMENTE variáveis de ambiente DB_* (conforme solicitado)
$host = getenv('DB_HOST') ?: '';
$port = getenv('DB_PORT') ?: '';
$db   = getenv('DB_DATABASE') ?: '';
$user = getenv('DB_USERNAME') ?: '';
$pass = getenv('DB_PASSWORD') ?: '';

// Validação mínima para evitar conexões com parâmetros vazios
if ($host === '' || $port === '' || $db === '' || $user === '') {
    error_log('Configuração de banco incompleta: verifique DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME e DB_PASSWORD no ambiente/.env');
    http_response_code(500);
    exit('Configuração do banco ausente. Atualize o .env.');
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
    exit('Erro interno no servidor. Verifique os logs para mais detalhes.');
}

// Retorna objeto PDO pra uso no seu app
return $pdo;