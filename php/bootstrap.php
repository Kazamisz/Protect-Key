<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Carrega as variáveis de ambiente apenas em ambiente de desenvolvimento local.
// No Railway, as variáveis são injetadas diretamente no ambiente.
if (isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1')) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
    } catch (\Dotenv\Exception\InvalidPathException $e) {
        // O arquivo .env não é obrigatório no desenvolvimento, pode-se usar variáveis de ambiente locais.
        // Não fazer nada se o arquivo não existir.
    }
}