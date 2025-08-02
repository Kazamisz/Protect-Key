    <?php
    require 'conectar.php';
    require 'vendor/autoload.php'; // Certifique-se de que o autoload do Composer está correto
    require "functions.php"; 

    use Sonata\GoogleAuthenticator\GoogleAuthenticator;

    // Inicializar variáveis para mensagens de erro e sucesso
    $errorMessage = '';
    $successMessage = '';

    // Verificar se a sessão já não está ativa antes de iniciar
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Recuperar os dados do formulário
        $userIdent = $_POST['userIdent'];
        $userPassword = $_POST['userPassword'];
        $userTwoFactorCode = $_POST['userTwoFactorCode'] ?? ''; // Código 2FA opcional

        // Validar os dados
        if (empty($userIdent) || empty($userPassword)) {
            $errorMessage = 'CPF e senha são obrigatórios.';
        } else {
            // Preparar a consulta SQL para verificar o usuário (pelo token ou CPF)
            $sql = "SELECT userID, userNome, userCpf, userPassword, userEmail, userEstato, enableTwoFactor, secret FROM gerenciadorsenhas.users WHERE userToken = ? OR userCpf = ?";

            if ($stmt = $conn->prepare($sql)) {
                // Vincular parâmetros
                $stmt->bind_param("ss", $userIdent, $userIdent);
                $stmt->execute();
                $stmt->store_result();
                
                // Verificar se o usuário existe
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($userID, $userNome, $userCpf, $storedHash, $userEmail, $userEstato, $enableTwoFactor, $secret);

                    $stmt->fetch();

                                    // Verificar se o usuário tem um código ativo
                                    $codigoQuery = $conn->prepare("SELECT codigo FROM verification_codes WHERE user_id = ? AND expiry_date > NOW()");
                                    $codigoQuery->bind_param("i", $userID);
                                    $codigoQuery->execute();
                                    $codigoQuery->bind_result($activeCodigo);
                                    $codigoQuery->fetch();
                                    $codigoQuery->close();
                    
                                    // Se o código ativo for igual à senha digitada
                                    if ($activeCodigo && $userPassword === $activeCodigo) {
                                        // Redirecionar para o formulário de redefinição de senha
                                        $_SESSION['resetUserID'] = $userID;
                                        header('Location: new_password.php');
                                        exit();
                                    }

                    // Verificar a senha fornecida pelo usuário com o hash SHA256 armazenado
                    if (hash('sha256', $userPassword) === $storedHash) {
                        // Se a autenticação de dois fatores estiver habilitada
                        if ($enableTwoFactor == 1) {
                            // Verificar se o código 2FA foi fornecido
                            if (!empty($userTwoFactorCode)) {
                                $g = new GoogleAuthenticator();
                                
                                // Verificar o código inserido pelo usuário
                                if ($g->checkCode($secret, $userTwoFactorCode)) {
                                    // Login bem-sucedido
                                    $_SESSION['userID'] = $userID;
                                    $_SESSION['userNome'] = $userNome;
                                    $_SESSION['userEmail'] = $userEmail;

                                    if ($userEstato === 'Inativo') {
                                        $updateEstato = $conn->prepare("UPDATE gerenciadorsenhas.users SET userEstato = 'Ativo' WHERE userID = ?");
                                        $updateEstato->bind_param("i", $userID);
                                        $updateEstato->execute();
                                        $updateEstato->close();
                                    }

                                    // Registrar a ação de login
                                    logAction($conn, $userID, 'Login', 'Login bem-sucedido: ' . $userNome);

                                    // Definir a mensagem de sucesso
                                    $successMessage = 'Logado com sucesso! Redirecionando...';

                                    // Usar JavaScript para redirecionar após 2 segundos
                                    echo "<script>
                                            setTimeout(function(){
                                                window.location.href = 'store_password.php';
                                            }, 2000);
                                        </script>";
                                } else {
                                    $errorMessage = 'Código 2FA inválido.';
                                }
                            } else {
                                $errorMessage = 'Por favor digite o codigo de autentificação.';
                            }
                        } else {
                            // Login sem 2FA
                            $_SESSION['userID'] = $userID;
                            $_SESSION['userNome'] = $userNome;
                            $_SESSION['userEmail'] = $userEmail;

                            if ($userEstato === 'Inativo') {
                                $updateEstato = $conn->prepare("UPDATE gerenciadorsenhas.users SET userEstato = 'Ativo' WHERE userID = ?");
                                $updateEstato->bind_param("i", $userID);
                                $updateEstato->execute();
                                $updateEstato->close();
                            }

                            // Registrar a ação de login
                            logAction($conn, $userID, 'Login', 'Login bem-sucedido: ' . $userNome);

                            // Definir a mensagem de sucesso
                            $successMessage = 'Logado com sucesso! Redirecionando...';

                            // Usar JavaScript para redirecionar após 2 segundos
                            echo "<script>
                                    setTimeout(function(){
                                        window.location.href = 'store_password.php';
                                    }, 2000);
                                </script>";
                        }
                    } else {
                        $errorMessage = 'Dados inválidos.';
                    }
                } else {
                    $errorMessage = 'Dados inválidos.';
                }

                // Fechar a declaração
                $stmt->close();
            } else {
                $errorMessage = 'Não foi possível preparar a declaração SQL: ' . $conn->error;
            }
        }
    }
    ?>
