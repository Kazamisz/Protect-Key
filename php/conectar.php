<?php

//VARIÁVEIS PARA CONECTAR AO BANCO DE DADOS
$servername = "localhost:3308";
$username = "root";
$password = "etec2023";
$dbname = "gerenciadorsenhas";

try {
    //VERIFICA SE A CONEXÃO FOI ESTABELECIDA
    $conn = new mysqli($servername, $username, $password, $dbname);

} catch (Exception $e) {

    die("Erro:" . $e->getMessage());
}
?>