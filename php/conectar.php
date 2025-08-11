<?php
// As variáveis de ambiente são carregadas pelo bootstrap.php.
// O código abaixo define as credenciais corretas para o ambiente local ou de produção.

if (isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1')) {
    // --- AMBIENTE LOCAL ---
    $host = 'localhost';
    $port = '3306';
    $db   = 'gerenciadorsenhas';
    $user = 'root';
    $pass = 'etec2024';
} else {
    // --- AMBIENTE DE PRODUÇÃO (RAILWAY) ---
    // Assume que bootstrap.php carregou as variáveis de ambiente...
    $host = getenv('MYSQLHOST');
    $port = getenv('MYSQLPORT');
    $db   = getenv('MYSQLDATABASE');
    $user = getenv('MYSQLUSER');
    $pass = getenv('MYSQLPASSWORD');
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
    exit('Erro interno no servidor.');
}

// Retorna objeto PDO pra uso no seu app
return $pdo;