<?php
require_once 'includes/db.php';
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email) {
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            // Aqui futuramente será enviado o e-mail com o token
            $mensagem = 'Se o e-mail estiver cadastrado, você receberá instruções para redefinir sua senha.';
        } else {
            $mensagem = 'Se o e-mail estiver cadastrado, você receberá instruções para redefinir sua senha.';
        }
    } else {
        $mensagem = 'Informe seu e-mail.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f7f8fa; font-family: 'Montserrat', Arial, sans-serif; }
        .container { max-width: 370px; margin: 60px auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 12px #0001; padding: 32px 28px 24px 28px; }
        h2 { text-align: center; color: #2d3e50; margin-bottom: 24px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; color: #444; }
        input[type="email"] { width: 100%; padding: 10px 12px; border: 1px solid #bbb; border-radius: 8px; font-size: 1rem; }
        .btn { width: 100%; background: #2d3e50; color: #fff; border: none; padding: 12px; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; margin-top: 8px; transition: background 0.2s; }
        .btn:hover { background: #ffb300; color: #222; }
        .mensagem { color: #2d3e50; text-align: center; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Redefinir Senha</h2>
        <?php if ($mensagem): ?>
            <div class="mensagem"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="form-group">
                <label for="email">E-mail cadastrado</label>
                <input type="email" name="email" id="email" required autofocus>
            </div>
            <button type="submit" class="btn">Enviar instruções</button>
        </form>
    </div>
</body>
</html> 