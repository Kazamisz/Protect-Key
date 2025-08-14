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