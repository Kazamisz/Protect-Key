<?php

// Inclui o bootstrap que carrega o Dotenv de forma segura
require_once __DIR__ . '/bootstrap.php';

// Pega as credenciais do ambiente (do .env localmente, ou da Railway em produção)
$dbHost = getenv('DB_HOST');
$dbPort = getenv('DB_PORT'); // Essencial para a conexão na Railway
$dbName = getenv('DB_DATABASE');
$dbUser = getenv('DB_USERNAME');
$dbPass = getenv('DB_PASSWORD');

// --- CÓDIGO DE DEBUG TEMPORÁRIO ---
// Este código nos ajuda a ver quais variáveis estão sendo usadas.
echo "--- DEBUG DE CONEXÃO ---<br>";
echo "Host: " . htmlspecialchars($dbHost) . "<br>";
echo "Porta: " . htmlspecialchars($dbPort) . "<br>";
echo "Banco: " . htmlspecialchars($dbName) . "<br>";
echo "Usuário: " . htmlspecialchars($dbUser) . "<br>";
echo "Senha Existe: " . (empty($dbPass) ? 'Não' : 'Sim') . "<br>";
echo "--- FIM DO DEBUG ---<br><br>";
// --- FIM DO CÓDIGO DE DEBUG ---

try {
    // Constrói a string de conexão (DSN) corretamente, incluindo a porta.
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

    // Opções do PDO para melhor manuseio de erros e resultados
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // Cria a instância do PDO
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);

} catch (PDOException $e) {
    // Em caso de erro, exibe uma mensagem clara e encerra o script.
    die("❌ Erro de conexão com o banco de dados: " . $e->getMessage());
}