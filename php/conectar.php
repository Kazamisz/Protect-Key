<?php
// conectar.php — conexão segura usando variáveis de ambiente

// Carregue .env no local, se necessário (via vlucas/phpdotenv)
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Prioriza suas variáveis definidas (DB_*), depois as internas do Railway (MYSQL*), depois valores padrão
$host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306';
$db = getenv('DB_DATABASE') ?: getenv('MYSQLDATABASE') ?: 'railway';
$user = getenv('DB_USERNAME') ?: getenv('MYSQLUSER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: getenv('MYSQLPASSWORD') ?: '';

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Loga erro sem expor detalhes ao usuário final
    error_log('Erro de conexão ao banco: ' . $e->getMessage());
    http_response_code(500);
    exit('Erro interno no servidor.');
}

// Retorna objeto PDO pra uso no seu app
return $pdo;
