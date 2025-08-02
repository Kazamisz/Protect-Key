<?php
class DocumentManager
{
    private $conn;
    private $userID;
    private $userNome;
    private $errorMessage = '';
    private $successMessage = '';

    public function __construct($connection, $userId)
    {
        $this->conn = $connection;
        $this->userID = $userId;
        $this->getUserName();
    }

    private function getUserName()
    {
        try {
            $stmt = $this->prepareStatement("SELECT userNome FROM users WHERE userID = ?");
            $stmt->bind_param("i", $this->userID);
            $stmt->execute();
            $stmt->bind_result($this->userNome);
            $stmt->fetch();
            $stmt->close();
        } catch (Exception $e) {
            $this->errorMessage = "Erro ao recuperar nome do usuário: " . $e->getMessage();
        }
    }

    private function prepareStatement($sql)
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro ao preparar statement: " . $this->conn->error);
        }
        return $stmt;
    }

    private function validateFile($file)
    {
        if (empty($file['name']))
            return null;

        // Criar diretório de uploads se não existir
        if (!file_exists(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0777, true);
        }

        $fileName = basename($file['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = uniqid() . '.' . $fileExt;
        $fileTarget = UPLOAD_DIR . $newFileName;

        // Validar tipo de arquivo
        if (!in_array($fileExt, ALLOWED_FILE_TYPES)) {
            $this->errorMessage = 'Tipo de arquivo não permitido.';
            return false;
        }

        // Validar tamanho do arquivo
        if ($file['size'] > MAX_FILE_SIZE) {
            $this->errorMessage = 'Arquivo muito grande. Limite máximo: 5MB';
            return false;
        }

        // Mover arquivo
        if (move_uploaded_file($file['tmp_name'], $fileTarget)) {
            return $fileTarget;
        }

        $this->errorMessage = 'Erro ao fazer upload do arquivo.';
        return false;
    }

    private function logAction($action, $description)
    {
        try {
            $stmt = $this->prepareStatement("INSERT INTO user_logs (user_id, action, description, log_date) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iss", $this->userID, $action, $description);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            // Log de erro silencioso
            error_log("Erro ao registrar log: " . $e->getMessage());
        }
    }

    public function processDocument($post, $files)
    {
        // Validar dados do formulário
        $documentName = $post['documentName'] ?? '';
        $documentType = $post['documentType'] ?? '';
        $documentNumber = $post['documentNumber'] ?? '';
        $issueDate = $post['issueDate'] ?? null;
        $documentId = intval($post['documentId'] ?? 0);
        $observations = $post['observations'] ?? '';
        $actionType = $post['actionType'] ?? '';

        // Validações básicas
        if (empty($documentName) || empty($documentType)) {
            $this->errorMessage = 'Nome do documento e tipo são obrigatórios.';
            return false;
        }

        // Processar upload de arquivo
        $fileTarget = $this->validateFile($files['documentFile'] ?? null);
        if ($fileTarget === false) {
            return false;
        }

        try {
            switch ($actionType) {
                case 'add':
                    return $this->addDocument($documentName, $documentType, $documentNumber, $issueDate, $fileTarget, $observations);

                case 'update':
                    return $this->updateDocument($documentId, $documentName, $documentType, $documentNumber, $issueDate, $fileTarget, $observations);

                case 'delete':
                    return $this->deleteDocument($documentId);

                default:
                    $this->errorMessage = 'Ação inválida.';
                    return false;
            }
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            return false;
        }
    }

    private function addDocument($documentName, $documentType, $documentNumber, $issueDate, $fileTarget, $observations)
    {
        // Verificar limite de documentos para plano básico
        $totalDocuments = $this->getDocumentCount();
        if ($this->getUserPlan() == 'básico' && $totalDocuments >= MAX_DOCUMENTS_BASIC_PLAN) {
            $this->errorMessage = 'Limite de documentos para o plano básico atingido.';
            return false;
        }

        $sql = "INSERT INTO documents (user_id, document_name, document_type, document_number, issue_date, file_path, observations) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->prepareStatement($sql);
        $stmt->bind_param("issssss", $this->userID, $documentName, $documentType, $documentNumber, $issueDate, $fileTarget, $observations);

        if ($stmt->execute()) {
            $this->successMessage = 'Documento adicionado com sucesso!';
            $this->logAction('Adicionar Documento', "Usuário {$this->userNome} adicionou um documento.");
            $stmt->close();
            return true;
        }

        $this->errorMessage = 'Erro ao adicionar documento: ' . $stmt->error;
        $stmt->close();
        return false;
    }

    private function updateDocument($documentId, $documentName, $documentType, $documentNumber, $issueDate, $fileTarget, $observations)
    {
        $sql = $fileTarget ?
            "UPDATE documents SET document_name = ?, document_type = ?, document_number = ?, issue_date = ?, file_path = ?, observations = ? WHERE documentId = ? AND user_id = ?" :
            "UPDATE documents SET document_name = ?, document_type = ?, document_number = ?, issue_date = ?, observations = ? WHERE documentId = ? AND user_id = ?";

        $stmt = $this->prepareStatement($sql);

        if ($fileTarget) {
            $stmt->bind_param("sssssssi", $documentName, $documentType, $documentNumber, $issueDate, $fileTarget, $observations, $documentId, $this->userID);
        } else {
            $stmt->bind_param("ssssssi", $documentName, $documentType, $documentNumber, $issueDate, $observations, $documentId, $this->userID);
        }

        if ($stmt->execute()) {
            $this->successMessage = 'Documento atualizado com sucesso!';
            $this->logAction('Atualizar Documento', "Usuário {$this->userNome} atualizou um documento.");
            $stmt->close();
            return true;
        }

        $this->errorMessage = 'Erro ao atualizar documento: ' . $stmt->error;
        $stmt->close();
        return false;
    }

    private function deleteDocument($documentId)
    {
        $sql = "DELETE FROM documents WHERE documentId = ? AND user_id = ?";
        $stmt = $this->prepareStatement($sql);
        $stmt->bind_param("ii", $documentId, $this->userID);

        if ($stmt->execute()) {
            $this->successMessage = 'Documento deletado com sucesso!';
            $this->logAction('Deletar Documento', "Usuário {$this->userNome} deletou um documento.");
            $stmt->close();
            return true;
        }

        $this->errorMessage = 'Erro ao deletar documento: ' . $stmt->error;
        $stmt->close();
        return false;
    }

    private function getDocumentCount()
    {
        $sql = "SELECT COUNT(*) as doc_count FROM documents WHERE user_id = ?";
        $stmt = $this->prepareStatement($sql);
        $stmt->bind_param("i", $this->userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['doc_count'];
    }

    private function getUserPlan()
    {
        // Implemente a lógica para recuperar o plano do usuário
        // Substitua com sua implementação real
        $stmt = $this->prepareStatement("SELECT user_plan FROM users WHERE userID = ?");
        $stmt->bind_param("i", $this->userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        return $row['user_plan'] ?? 'básico';
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getSuccessMessage()
    {
        return $this->successMessage;
    }

    public function getDocuments()
    {
        $sql = "SELECT * FROM documents WHERE user_id = ?";
        $stmt = $this->prepareStatement($sql);
        $stmt->bind_param("i", $this->userID);
        $stmt->execute();
        $result = $stmt->get_result();
        $documents = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $documents;
    }
}

// Script principal
session_start();

// Incluir arquivos necessários
require_once('conectar.php');
require_once('functions.php');

// Verificar autenticação
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}

// Processar documento
try {
    $documentManager = new DocumentManager($conn, $_SESSION['userID']);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($documentManager->processDocument($_POST, $_FILES)) {
            // Processamento bem-sucedido
            $successMessage = $documentManager->getSuccessMessage();
            $savedDocuments = $documentManager->getDocuments();
        } else {
            // Processamento falhou
            $errorMessage = $documentManager->getErrorMessage();
            $savedDocuments = $documentManager->getDocuments();
        }
    } else {
        // Carregar documentos existentes
        $savedDocuments = $documentManager->getDocuments();
    }
} catch (Exception $e) {
    $errorMessage = "Erro crítico: " . $e->getMessage();
}
?>