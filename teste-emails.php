<?php
require_once 'includes/email.php';

$mensagem = '';
$log = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = trim($_POST['to'] ?? '');
    if ($to) {
        $assunto = 'Teste de envio de e-mail - Plataforma Educacional';
        $corpo = '<p>Este é um teste de envio de e-mail via PHPMailer/Gmail.</p>';
        $ok = sendMail($to, $assunto, $corpo);
        $mensagem = $ok ? 'E-mail enviado com sucesso para ' . htmlspecialchars($to) : 'Falha ao enviar e-mail para ' . htmlspecialchars($to);
        $log = date('c') . " - Envio para $to: " . ($ok ? 'OK' : 'FALHA') . "\n";
        file_put_contents('teste-emails.log', $log, FILE_APPEND);
    } else {
        $mensagem = 'Informe um e-mail de destino.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Teste de Envio de E-mail</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f8fa; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px #0001; padding: 32px 24px; }
        h2 { text-align: center; color: #2d3e50; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; color: #444; }
        input[type="email"] { width: 100%; padding: 10px 12px; border: 1px solid #bbb; border-radius: 8px; font-size: 1rem; }
        .btn { width: 100%; background: #2d3e50; color: #fff; border: none; padding: 12px; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; margin-top: 8px; transition: background 0.2s; }
        .btn:hover { background: #ffb300; color: #222; }
        .msg { text-align: center; margin-bottom: 16px; color: #2d3e50; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Teste de Envio de E-mail</h2>
        <?php if ($mensagem): ?>
            <div class="msg"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label for="to">E-mail de destino</label>
                <input type="email" name="to" id="to" required>
            </div>
            <button type="submit" class="btn">Enviar e-mail de teste</button>
        </form>
    </div>
</body>
</html> 