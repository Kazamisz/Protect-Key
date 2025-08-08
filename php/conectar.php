<?php

require_once __DIR__ . '/bootstrap.php';

//VARIÁVEIS PARA CONECTAR AO BANCO DE DADOS
$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_DATABASE'];

try {
    //VERIFICA SE A CONEXÃO FOI ESTABELECIDA
    $conn = new mysqli($servername, $username, $password, $dbname);

} catch (Exception $e) {

    die("Erro:" . $e->getMessage());
}
?>