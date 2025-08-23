<?php 

require_once __DIR__ . '/bootstrap.php';

$env = static function(string $k, $default = '') {
	$v = getenv($k);
	if ($v === false || $v === null || $v === '') {
		$v = $_ENV[$k] ?? $_SERVER[$k] ?? $default;
	}
	return $v;
};

$cfg = [
	'db' => [
		'host' => (string)$env('DB_HOST', ''),
		'port' => (int)$env('DB_PORT', 3306),
		'name' => (string)$env('DB_DATABASE', ''),
		'user' => (string)$env('DB_USERNAME', ''),
		'pass' => (string)$env('DB_PASSWORD', ''),
	],
	'mail' => [
		'host' => (string)$env('MAIL_HOST', ''),
		'port' => (int)$env('MAIL_PORT', 587),
		'user' => (string)$env('MAIL_USERNAME', ''),
		'pass' => (string)$env('MAIL_PASSWORD', ''),
		'from' => (string)$env('MAIL_FROM_ADDRESS', ''),
		'name' => (string)$env('MAIL_FROM_NAME', 'Protect Key'),
		'encryption' => (string)$env('MAIL_ENCRYPTION', 'tls'),
	],
	'mercadopago' => [
		'token' => (string)$env('MERCADOPAGO_ACCESS_TOKEN', ''),
		'notification_url' => (string)$env('MP_NOTIFICATION_URL', ''),
	],
];

// Compatibilidade com código legado: manter a chave 'accesstoken'
if (empty($cfg['mercadopago']['token'])) {
	error_log('[config] MERCADOPAGO_ACCESS_TOKEN não definido. Configure no .env ou ambiente.');
}
$cfg['accesstoken'] = $cfg['mercadopago']['token'];

return $cfg;