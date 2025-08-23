<?php 

// Suprimir avisos de depreciação
error_reporting(E_ALL & ~E_DEPRECATED);

// Incluir arquivos de configuração e autoload
$config = require_once 'config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

// Verifica token e prepara cliente somente se habilitado
$accessToken = (string)($config['accesstoken'] ?? '');
$mpEnabled = $accessToken !== '';
if ($mpEnabled) {
    // Configurar o token de acesso
    MercadoPagoConfig::setAccessToken($accessToken);
    $client = new PreferenceClient();
} else {
    // Em ambiente local sem token, evitamos chamadas externas
    $client = null; // marcador
    error_log('[mercadopago] MERCADOPAGO_ACCESS_TOKEN ausente. Preferências serão desabilitadas (usando link inativo).');
}

// Função para criar uma preferência de pagamento
function createPreference($client, $title, $description, $price, $currency = 'BRL') {
    $notificationUrl = getenv('MP_NOTIFICATION_URL') ?: '';
    if ($notificationUrl === '') {
        error_log('MP_NOTIFICATION_URL não está definida no ambiente. Defina em produção para receber notificações.');
    }
    // Se cliente for nulo, estamos em modo desabilitado (ex.: local sem token)
    if ($client === null) {
        return '#';
    }
    try {
        $preference = $client->create([
            "external_reference" => "teste_" . $title,
            "notification_url" => ($notificationUrl !== '' ? $notificationUrl : null),
            "items" => [
                [
                    "id" => uniqid(),
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
        return $preference->init_point ?? '#';

    } catch (MPApiException $e) {
        error_log('[mercadopago] MPApiException ao criar preferência: ' . $e->getMessage());
        return '#';
    } catch (\Throwable $t) {
        // Captura erros inesperados, incluindo respostas nulas do SDK
        error_log('[mercadopago] Erro inesperado ao criar preferência: ' . $t->getMessage());
        return '#';
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