<?php
session_start();
require_once 'includes/db.php';

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    if ($email && $senha) {
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];
            if ($usuario['tipo'] === 'admin') {
                header('Location: /admin/dashboard.php');
                exit;
            } else {
                header('Location: /aluno/meus-cursos.php');
                exit;
            }
        } else {
            $erro = 'E-mail ou senha inválidos.';
        }
    } else {
        $erro = 'Preencha todos os campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Plataforma Educacional</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f7f8fa; font-family: 'Montserrat', Arial, sans-serif; }
        .login-container {
            max-width: 370px;
            margin: 60px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px #0001;
            padding: 32px 28px 24px 28px;
        }
        h2 { text-align: center; color: #2d3e50; margin-bottom: 24px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; color: #444; }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #bbb;
            border-radius: 8px;
            font-size: 1rem;
        }
        .btn-login {
            width: 100%;
            background: #2d3e50;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s;
        }
        .btn-login:hover { background: #ffb300; color: #222; }
        .erro { color: #c00; text-align: center; margin-bottom: 12px; }
        .link { display: block; text-align: right; margin-top: 10px; color: #2d3e50; text-decoration: none; font-size: 0.98rem; }
        .link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Entrar na Plataforma</h2>
        <?php if ($erro): ?>
            <div class="erro"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" name="email" id="email" required autofocus>
            </div>
            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" name="senha" id="senha" required>
            </div>
            <button type="submit" class="btn-login">Entrar</button>
        </form>
        <a href="redefinir-senha.php" class="link">Esqueci minha senha</a>
    </div>
</body>
</html> 