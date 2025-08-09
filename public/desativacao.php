<?php
session_start();

// Manter o userID antes de destruir a sessão
$userID = $_SESSION['user_id'] ?? null;

// Destruir a sessão somente após o envio do feedback
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // session_destroy(); // Movido para salvar_feedback.php
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conta Desativada</title>
    <link rel="stylesheet" href="style/styles-desativacao.css">
</head>
<body>
    <div class="container">
        <h1>Sentimos Muito!</h1>
        <p>
    Sua conta foi desativada com sucesso. Sentiremos sua falta! 
    Note que quaisquer pagamentos realizados na conta não são reembolsáveis.
</p>
<p>
    Caso deseje reativar sua conta, basta fazer login novamente dentro de **30 dias**. 
    Após esse período, todos os dados associados à sua conta serão excluídos permanentemente, 
    e não será possível recuperá-la.
</p>

        <p>
            Por favor, conte-nos o motivo da sua decisão para que possamos melhorar nossos serviços.
        </p>
        <form class="feedback-form" action="<?php echo __DIR__ . '/../php/salvar_feedback.php'; ?>" method="POST">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userID); ?>">
            <label for="reason">Por que você desativou sua conta?</label>
            <select name="reason" id="reason" required>
                <option value="" disabled selected>Selecione um motivo</option>
                <option value="Dificuldade de uso">Dificuldade de uso</option>
                <option value="Problemas técnicos">Problemas técnicos</option>
                <option value="Não preciso mais do serviço">Não preciso mais do serviço</option>
                <option value="Preocupações com privacidade">Preocupações com privacidade</option>
                <option value="Custo elevado">Custo elevado</option>
                <option value="Outro">Outro</option>
            </select>
            <label for="comments">Comentários adicionais (opcional)</label>
            <textarea name="comments" id="comments" rows="4" placeholder="Gostaríamos de saber mais sobre sua experiência..."></textarea>
            <button type="submit" class="submit-btn">Enviar Feedback e Sair</button>
        </form>
        <a href="index.php">Voltar para a Página Inicial</a>
    </div>

    
</body>
</html>
