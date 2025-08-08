<?php 

// Suprimir avisos de depreciação
error_reporting(E_ALL & ~E_DEPRECATED);

// Incluir arquivos de configuração e autoload
$config = require_once 'config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

// Configurar o token de acesso
MercadoPagoConfig::setAccessToken($config['accesstoken']);

$client = new PreferenceClient();

// Função para criar uma preferência de pagamento
function createPreference($client, $title, $description, $price, $currency = 'BRL') {
    try {
        $preference = $client->create([
            "external_reference" => "teste_" . $title, // Referência externa única para cada plano
            "notification_url" => "https://6ee0-200-95-221-148.ngrok-free.app/notification.php", // URL para notificações de pagamento
            "items" => [
                [
                    "id" => uniqid(), // ID único para o item
                    "title" => $title,
                    "description" => $description,
                    "picture_url" => "http://www.myapp.com/myimage.jpg",
                    "category_id" => "eletronico",
                    "quantity" => 1,
                    "currency_id" => $currency,
                    "unit_price" => $price
                ]
            ],
            "default_payment_method_id" => "master",
            "excluded_payment_types" => [
                ["id" => "ticket"]
            ],
            "installments" => 12,
            "default_installments" => 1
        ]);

        // Retornar a URL de pagamento da preferência criada
        return $preference->init_point;

    } catch (MPApiException $e) {
        echo 'Erro: ' . htmlspecialchars($e->getMessage());
        exit;
    }
}

// Criar preferências para os planos
$paymentUrlPro = createPreference(
    $client, 
    "Pro", 
    "Armazenamento ilimitado de senhas, acesso em múltiplos dispositivos, autenticação multifator, suporte prioritário e relatórios de segurança.", 
    14.99
);

$paymentUrlPremium = createPreference(
    $client, 
    "Premium", 
    "Armazenamento ilimitado de senhas, acesso em múltiplos dispositivos, autenticação multifator, suporte premium 24/7, relatórios avançados e backup e recuperação de dados.", 
    24.99
);

?>