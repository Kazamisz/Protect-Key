<?php

require_once __DIR__ . '/bootstrap.php';

/**
 * Classe para gerenciar conexão com banco de dados
 * Implementa padrão Singleton para garantir uma única conexão
 */
class Database 
{
    private static $instance = null;
    private $connection = null;
    
    // Configurações do banco
    private $host;
    private $port;
    private $database;
    private $username;
    private $password;
    private $charset = 'utf8mb4';
    
    /**
     * Construtor privado para implementar Singleton
     */
    private function __construct() 
    {
        $this->loadConfig();
        $this->connect();
    }
    
    /**
     * Carrega as configurações do .env
     */
    private function loadConfig()
    {
        // Carrega variáveis obrigatórias
        $this->host = $_ENV['DB_HOST'] ?? null;
        $this->port = $_ENV['DB_PORT'] ?? '3306';
        $this->database = $_ENV['DB_DATABASE'] ?? null;
        $this->username = $_ENV['DB_USERNAME'] ?? null;
        $this->password = $_ENV['DB_PASSWORD'] ?? null;
        
        // Valida se todas as configurações necessárias estão presentes
        $required = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
        $missing = [];
        
        foreach ($required as $key) {
            if (empty($_ENV[$key])) {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception('Configurações obrigatórias não encontradas no .env: ' . implode(', ', $missing));
        }
    }
    
    /**
     * Estabelece a conexão com o banco
     */
    private function connect()
    {
        try {
            // String de conexão PDO
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset={$this->charset}";
            
            // Opções do PDO para maior segurança e performance
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false, // Evita problemas em shared hosting
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset} COLLATE utf8mb4_unicode_ci"
            ];
            
            // Cria a conexão
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
            // Log de sucesso (apenas em desenvolvimento)
            if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
                error_log("✅ Conexão com banco estabelecida com sucesso!");
            }
            
        } catch (PDOException $e) {
            $this->handleConnectionError($e);
        }
    }
    
    /**
     * Trata erros de conexão de forma segura
     */
    private function handleConnectionError(PDOException $e)
    {
        // Em produção, não expor detalhes do erro
        if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
            error_log("❌ Erro de conexão com banco: " . $e->getMessage());
            die("Erro interno do servidor. Tente novamente mais tarde.");
        } else {
            // Em desenvolvimento, mostra detalhes para debug
            $errorDetails = [
                'Mensagem' => $e->getMessage(),
                'Arquivo' => $e->getFile(),
                'Linha' => $e->getLine(),
                'Host' => $this->host,
                'Port' => $this->port,
                'Database' => $this->database,
                'Username' => $this->username
            ];
            
            echo "<h1 style='color: red;'>❌ Erro na conexão com o banco</h1>";
            echo "<pre style='background: #f5f5f5; padding: 15px; border-left: 4px solid #ff0000;'>";
            foreach ($errorDetails as $key => $value) {
                echo "<strong>{$key}:</strong> {$value}\n";
            }
            echo "</pre>";
            die();
        }
    }
    
    /**
     * Retorna a instância única da classe (Singleton)
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Retorna a conexão PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    /**
     * Testa se a conexão está funcionando
     */
    public function testConnection()
    {
        try {
            $stmt = $this->connection->query("SELECT 1 as test");
            $result = $stmt->fetch();
            return $result['test'] === 1;
        } catch (PDOException $e) {
            error_log("Erro no teste de conexão: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retorna informações sobre a conexão
     */
    public function getConnectionInfo()
    {
        try {
            $info = [
                'server_info' => $this->connection->getAttribute(PDO::ATTR_SERVER_INFO),
                'server_version' => $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION),
                'client_version' => $this->connection->getAttribute(PDO::ATTR_CLIENT_VERSION),
                'connection_status' => $this->connection->getAttribute(PDO::ATTR_CONNECTION_STATUS),
                'charset' => $this->charset,
                'database' => $this->database
            ];
            return $info;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Previne clonagem da instância
     */
    private function __clone() {}
    
    /**
     * Previne deserialização da instância
     */
    public function __wakeup() 
    {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Função helper para facilitar o uso
function getDB() 
{
    return Database::getInstance()->getConnection();
}

// Função para testar conexão rapidamente
function testDBConnection() 
{
    $db = Database::getInstance();
    return $db->testConnection();
}

// Inicializa a conexão automaticamente
try {
    $database = Database::getInstance();
    
    // Em desenvolvimento, mostra status da conexão
    if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
        echo "<!-- Conexão com banco inicializada -->\n";
    }
    
} catch (Exception $e) {
    // Erro já foi tratado na classe
}