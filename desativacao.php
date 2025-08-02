<?php
session_start();
session_destroy(); // Garantir que a sessão seja encerrada
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conta Desativada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .container {
            max-width: 800px;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        h1 {
            color: #ff6f61;
            font-size: 2.5rem;
        }
        p {
            color: #555;
            line-height: 1.8;
            font-size: 1.2rem;
            margin: 20px 0;
        }
        .feedback-form {
            margin-top: 30px;
            text-align: left;
        }
        .feedback-form label {
            display: block;
            font-size: 1rem;
            margin-bottom: 10px;
            color: #333;
        }
        .feedback-form select {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .feedback-form textarea {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: none;
        }
        .submit-btn {
            background-color: #ff6f61;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }
        .submit-btn:hover {
            background-color: #ff5a4d;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
            border-radius: 5px;
        }
        a:hover {
            background-color: #0056b3;
        }
    </style>
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
        <form class="feedback-form" action="./php/salvar_feedback.php" method="POST">
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
            <button type="submit" class="submit-btn">Enviar Feedback</button>
        </form>
        <a href="../index.php">Voltar para a Página Inicial</a>
    </div>

    
</body>
</html>
