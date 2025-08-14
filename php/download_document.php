<?php
// Endpoint seguro para download de documentos
require_once __DIR__ . '/conectar.php';
require_once __DIR__ . '/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['userID'])) {
    http_response_code(401);
    exit('Não autorizado.');
}

$userID = (int)$_SESSION['userID'];
$documentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($documentId <= 0) {
    http_response_code(400);
    exit('Parâmetros inválidos.');
}

try {
    $stmt = $conn->prepare('SELECT file_path, document_name FROM documents WHERE documentId = :id AND user_id = :uid');
    $stmt->bindParam(':id', $documentId, PDO::PARAM_INT);
    $stmt->bindParam(':uid', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        http_response_code(404);
        exit('Documento não encontrado.');
    }

    $filePath = $doc['file_path'];
    $safeBase = realpath(__DIR__ . '/../public/uploads');
    $real = realpath($filePath);

    // Impede path traversal e acessos fora de uploads
    if ($real === false || strpos($real, $safeBase) !== 0) {
        http_response_code(403);
        exit('Acesso negado.');
    }

    if (!is_file($real) || !is_readable($real)) {
        http_response_code(404);
        exit('Arquivo não disponível.');
    }

    // Determina o mime type seguro
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($real) ?: 'application/octet-stream';

    // Força download
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . basename($doc['document_name']) . '"');
    header('Content-Length: ' . filesize($real));
    header('Pragma: public');
    header('Cache-Control: must-revalidate');

    // Log de auditoria
    log_action($conn, $userID, 'Download de Documento', 'Documento ID ' . $documentId . ' baixado.');

    readfile($real);
    exit;
} catch (PDOException $e) {
    error_log('Download error: ' . $e->getMessage());
    http_response_code(500);
    exit('Erro interno.');
}
