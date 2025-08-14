<?php
// As variáveis de ambiente são carregadas pelo bootstrap.php.
// O código abaixo define as credenciais corretas para o ambiente local ou de produção.

if (isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1')) {
    // --- AMBIENTE LOCAL ---
    // Valores podem ser sobrescritos por variáveis de ambiente.
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $db   = getenv('DB_DATABASE') ?: 'gerenciadorsenhas';
    $user = getenv('DB_USERNAME') ?: 'root';
    // Evite senhas hardcoded em repositório público; use envs.
    $pass = getenv('DB_LOCAL_PASSWORD') ?: (getenv('DB_PASSWORD') ?: '');
} else {
    // --- AMBIENTE DE PRODUÇÃO (RAILWAY) ---
    // A forma mais robusta é usar a URL de conexão fornecida pelo Railway.
    $db_url = getenv('MYSQL_URL');
    
    if ($db_url) {
        $db_parts = parse_url($db_url);
        $host = $db_parts['host'];
        $port = $db_parts['port'];
        $user = $db_parts['user'];
        $pass = $db_parts['pass'];
        $db   = ltrim($db_parts['path'], '/');
    } else {
        // Fallback para variáveis individuais se a URL não estiver definida
        $host = getenv('MYSQLHOST');
        $port = getenv('MYSQLPORT');
        $db   = getenv('MYSQLDATABASE');
        $user = getenv('MYSQLUSER');
        $pass = getenv('MYSQLPASSWORD');
    }
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