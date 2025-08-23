<?php

// 1) Autoload opcional do Composer (não fataliza se ausente)
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (is_file($autoloadPath)) {
    require_once $autoloadPath;
    if (!defined('VENDOR_AUTOLOADED')) {
        define('VENDOR_AUTOLOADED', true);
    }
} else {
    if (!defined('VENDOR_AUTOLOADED')) {
        define('VENDOR_AUTOLOADED', false);
    }
    error_log('[bootstrap] Aviso: vendor/autoload.php não encontrado. Execute "composer install" na raiz do projeto.');
}

// 2) Carrega variáveis do .env se a dependência vlucas/phpdotenv estiver disponível
if (class_exists(\Dotenv\Dotenv::class)) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        // safeLoad não lança exceção se o .env não existir
        if (method_exists($dotenv, 'safeLoad')) {
            $dotenv->safeLoad();
        } else {
            $dotenv->load();
        }
        // Exporta também para getenv() e $_SERVER
        foreach (($_ENV ?? []) as $k => $v) {
            if (is_string($k)) {
                putenv($k . '=' . $v);
                $_SERVER[$k] = $v;
            }
        }
    } catch (\Throwable $e) {
        error_log('[bootstrap] Falha ao carregar .env: ' . $e->getMessage());
    }
}
