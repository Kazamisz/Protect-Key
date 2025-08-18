<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Carrega as variáveis de ambiente do arquivo .env quando presente (dev) e mantém compatível com PROD
// Em produção, se não houver .env, as variáveis devem vir do ambiente do servidor (ex.: Railway, Docker, etc.)
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    // safeLoad não lança exceção se o .env não existir (compatível com versões mais novas)
    if (method_exists($dotenv, 'safeLoad')) {
        $dotenv->safeLoad();
    } else {
        $dotenv->load();
    }
    // Exporta também para getenv() para uso consistente em CLI e servidor embutido
    foreach (($_ENV ?? []) as $k => $v) {
        if (is_string($k)) {
            putenv($k . '=' . $v);
        }
    }
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // Sem .env: seguir usando variáveis reais do ambiente
}

// Mapeia variáveis de ambiente comuns do Railway para DB_* caso ainda não estejam definidas
// Railway (MySQL): MYSQLHOST, MYSQLPORT, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE
(function(){
    $env = function(string $k){ $v = getenv($k); return $v === false ? '' : $v; };
    $set = function(string $k, string $v){ if($v==='') return; putenv($k.'='.$v); $_ENV[$k]=$v; $_SERVER[$k]=$v; };

    $dbHost = $env('DB_HOST');
    $dbPort = $env('DB_PORT');
    $dbName = $env('DB_DATABASE');
    $dbUser = $env('DB_USERNAME');
    $dbPass = $env('DB_PASSWORD');

    $mysqlHost = $env('MYSQLHOST');
    $mysqlPort = $env('MYSQLPORT') ?: '3306';
    $mysqlDb   = $env('MYSQLDATABASE');
    $mysqlUser = $env('MYSQLUSER');
    $mysqlPass = $env('MYSQLPASSWORD');

    // Se DB_HOST estiver vazio ou for 'localhost' ou '127.0.0.1' (inúteis em containers), e MYSQLHOST existir, faça o binding
    if ((($dbHost === '') || (strtolower($dbHost) === 'localhost') || ($dbHost === '127.0.0.1')) && $mysqlHost !== '') {
        $set('DB_HOST', $mysqlHost);
        if ($dbPort === '') { $set('DB_PORT', $mysqlPort); }
        if ($dbName === '') { $set('DB_DATABASE', $mysqlDb); }
        if ($dbUser === '') { $set('DB_USERNAME', $mysqlUser); }
        if ($dbPass === '') { $set('DB_PASSWORD', $mysqlPass); }
        error_log('[bootstrap] DB_* variáveis mapeadas a partir de MYSQL* (Railway)');
    }
})();