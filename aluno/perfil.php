<?php
require_once '../includes/auth.php';
require_login('aluno');
require_once '../includes/db.php';

$usuario_id = $_SESSION['usuario_id'];

// Buscar dados do aluno
$stmt = $pdo->prepare('SELECT nome, email FROM usuarios WHERE id = ?');
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$mensagem = '';

// Atualizar nome e e-mail
if (isset($_POST['atualizar_dados'])) {
    $novo_nome = trim($_POST['nome'] ?? '');
    $novo_email = trim($_POST['email'] ?? '');
    if ($novo_nome && $novo_email) {
        $stmt = $pdo->prepare('UPDATE usuarios SET nome = ?, email = ? WHERE id = ?');
        $stmt->execute([$novo_nome, $novo_email, $usuario_id]);
        $mensagem = 'Dados atualizados com sucesso!';
        $usuario['nome'] = $novo_nome;
        $usuario['email'] = $novo_email;
    } else {
        $mensagem = 'Preencha todos os campos.';
    }
}

// Alterar senha
if (isset($_POST['alterar_senha'])) {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $nova_senha2 = $_POST['nova_senha2'] ?? '';
    if ($senha_atual && $nova_senha && $nova_senha2) {
        $stmt = $pdo->prepare('SELECT senha_hash FROM usuarios WHERE id = ?');
        $stmt->execute([$usuario_id]);
        $hash = $stmt->fetchColumn();
        if (password_verify($senha_atual, $hash)) {
            if ($nova_senha === $nova_senha2) {
                if (strlen($nova_senha) >= 6) {
                    $novo_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?');
                    $stmt->execute([$novo_hash, $usuario_id]);
                    $mensagem = 'Senha alterada com sucesso!';
                } else {
                    $mensagem = 'A nova senha deve ter pelo menos 6 caracteres.';
                }
            } else {
                $mensagem = 'As novas senhas não coincidem.';
            }
        } else {
            $mensagem = 'Senha atual incorreta.';
        }
    } else {
        $mensagem = 'Preencha todos os campos da senha.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil do Aluno</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f7f8fa; font-family: 'Montserrat', Arial, sans-serif; }
        .container { max-width: 500px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 12px #0001; padding: 32px 24px; }
        h2 { color: #2d3e50; text-align: center; margin-bottom: 24px; }
        label { display: block; margin-top: 16px; color: #2d3e50; font-weight: 500; }
        input { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; margin-top: 6px; }
        .btn { background: #2d3e50; color: #fff; border: none; padding: 10px 28px; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; margin-top: 18px; transition: background 0.2s; }
        .btn:hover { background: #ffb300; color: #222; }
        .mensagem { margin-top: 18px; color: #2d3e50; text-align: center; }
        .section { margin-bottom: 32px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Perfil do Aluno</h2>
        <?php if ($mensagem): ?>
            <div class="mensagem"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <div class="section">
            <form method="post">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                <button type="submit" name="atualizar_dados" class="btn">Atualizar Dados</button>
            </form>
        </div>
        <div class="section">
            <form method="post">
                <label for="senha_atual">Senha atual</label>
                <input type="password" id="senha_atual" name="senha_atual" required>
                <label for="nova_senha">Nova senha</label>
                <input type="password" id="nova_senha" name="nova_senha" required>
                <label for="nova_senha2">Confirmar nova senha</label>
                <input type="password" id="nova_senha2" name="nova_senha2" required>
                <button type="submit" name="alterar_senha" class="btn">Alterar Senha</button>
            </form>
        </div>
        <a href="meus-cursos.php" class="btn" style="background:#888;">&larr; Voltar</a>
    </div>
</body>
</html> 