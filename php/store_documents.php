<?php
// File: php/store_documents.php
// Assumes $conn (PDO) and $userID are available.

$errorMessage = '';
$successMessage = '';

// Define constants if they are not defined elsewhere
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
}
if (!defined('ALLOWED_FILE_TYPES')) {
    define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);
}
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
}
if (!defined('MAX_DOCUMENTS_BASIC_PLAN')) {
    define('MAX_DOCUMENTS_BASIC_PLAN', 5);
}


function get_user_plan($conn, $userID) {
    try {
        $stmt = $conn->prepare("SELECT plano FROM users WHERE userID = :userID");
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() ?: 'básico';
    } catch (PDOException $e) {
        error_log("Error getting user plan: " . $e->getMessage());
        return 'básico';
    }
}

function get_document_count($conn, $userID) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM documents WHERE user_id = :userID");
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error counting documents: " . $e->getMessage());
        return 0;
    }
}


function handle_document_upload($file) {
    global $errorMessage;

    if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
            $errorMessage = 'Erro no upload do arquivo.';
        }
        return null;
    }

    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }

    $fileName = basename($file['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $newFileName = uniqid('', true) . '.' . $fileExt;
    $fileTarget = UPLOAD_DIR . $newFileName;

    if (!in_array($fileExt, ALLOWED_FILE_TYPES)) {
        $errorMessage = 'Tipo de arquivo não permitido.';
        return false;
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        $errorMessage = 'Arquivo muito grande. Limite máximo: 5MB.';
        return false;
    }

    if (move_uploaded_file($file['tmp_name'], $fileTarget)) {
        return $fileTarget;
    }

    $errorMessage = 'Erro ao mover o arquivo para o diretório de uploads.';
    return false;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($userID)) {
    $actionType = $_POST['actionType'] ?? '';
    $documentId = filter_input(INPUT_POST, 'documentId', FILTER_VALIDATE_INT);

    try {
        if ($actionType === 'delete' && $documentId) {
            // Handle deletion
            $stmt = $conn->prepare("DELETE FROM documents WHERE documentId = :docID AND user_id = :userID");
            $stmt->bindParam(':docID', $documentId, PDO::PARAM_INT);
            $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $successMessage = 'Documento deletado com sucesso!';
                log_action($userID, 'Deleção de Documento', "Documento ID {$documentId} deletado.");
            } else {
                $errorMessage = 'Erro ao deletar o documento.';
            }

        } elseif ($actionType === 'add' || $actionType === 'update') {
            // Handle add/update
            $documentName = trim($_POST['documentName'] ?? '');
            $documentType = trim($_POST['documentType'] ?? '');
            $documentNumber = trim($_POST['documentNumber'] ?? '');
            $issueDate = $_POST['issueDate'] ?? null;
            $observations = trim($_POST['observations'] ?? '');

            if (empty($documentName) || empty($documentType)) {
                $errorMessage = 'Nome do documento e tipo são obrigatórios.';
            } else {
                $filePath = handle_document_upload($_FILES['documentFile'] ?? null);

                if ($filePath !== false) { // Continues if upload was successful or no file was uploaded
                    if ($actionType === 'add') {
                        $userPlan = get_user_plan($conn, $userID);
                        $docCount = get_document_count($conn, $userID);
                        if ($userPlan === 'básico' && $docCount >= MAX_DOCUMENTS_BASIC_PLAN) {
                            $errorMessage = 'Limite de documentos para o plano básico atingido.';
                        } else {
                            $sql = "INSERT INTO documents (user_id, document_name, document_type, document_number, issue_date, file_path, observations) VALUES (:userID, :name, :type, :number, :date, :path, :obs)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':path', $filePath);
                            $stmt->bindParam(':userID', $userID);
                            $stmt->bindParam(':name', $documentName);
                            $stmt->bindParam(':type', $documentType);
                            $stmt->bindParam(':number', $documentNumber);
                            $stmt->bindParam(':date', $issueDate);
                            $stmt->bindParam(':obs', $observations);

                            if ($stmt->execute()) {
                                $successMessage = 'Documento adicionado com sucesso!';
                                log_action($userID, 'Adição de Documento', "Documento '{$documentName}' adicionado.");
                            } else {
                                $errorMessage = 'Erro ao adicionar documento.';
                            }
                        }
                    } elseif ($actionType === 'update' && $documentId) {
                        // Build query based on whether a new file was uploaded
                        if ($filePath) {
                            $sql = "UPDATE documents SET document_name = :name, document_type = :type, document_number = :number, issue_date = :date, file_path = :path, observations = :obs WHERE documentId = :docID AND user_id = :userID";
                        } else {
                            $sql = "UPDATE documents SET document_name = :name, document_type = :type, document_number = :number, issue_date = :date, observations = :obs WHERE documentId = :docID AND user_id = :userID";
                        }
                        $stmt = $conn->prepare($sql);

                        if ($filePath) {
                            $stmt->bindParam(':path', $filePath);
                        }
                        $stmt->bindParam(':name', $documentName);
                        $stmt->bindParam(':type', $documentType);
                        $stmt->bindParam(':number', $documentNumber);
                        $stmt->bindParam(':date', $issueDate);
                        $stmt->bindParam(':obs', $observations);
                        $stmt->bindParam(':docID', $documentId);
                        $stmt->bindParam(':userID', $userID);

                        if ($stmt->execute()) {
                            $successMessage = 'Documento atualizado com sucesso!';
                             log_action($userID, 'Atualização de Documento', "Documento ID {$documentId} atualizado.");
                        } else {
                            $errorMessage = 'Erro ao atualizar documento.';
                        }
                    }
                }
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "Erro de banco de dados: Por favor, tente novamente.";
        error_log("Document operation error: " . $e->getMessage());
    }
}

// Fetch all documents for the user to display
$savedDocuments = [];
if (isset($userID)) {
    try {
        $stmt = $conn->prepare("SELECT * FROM documents WHERE user_id = :userID ORDER BY document_name");
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $savedDocuments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $errorMessage = "Erro ao carregar seus documentos.";
        error_log("Error fetching documents: " . $e->getMessage());
    }
}

// Determine if the "Add" button should be shown
$userPlan = isset($userID) ? get_user_plan($conn, $userID) : 'básico';
$docCount = isset($userID) ? get_document_count($conn, $userID) : 0;
$showAddButton = !($userPlan === 'básico' && $docCount >= MAX_DOCUMENTS_BASIC_PLAN);

?>