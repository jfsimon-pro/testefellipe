<?php
require_once '../../includes/auth.php';
require_login('admin');
require_once '../../includes/db.php';

// Excluir aluno
if (isset($_POST['excluir_id'])) {
    $id = intval($_POST['excluir_id']);
    $stmt = $pdo->prepare('DELETE FROM usuarios WHERE id = ? AND tipo = "aluno"');
    $stmt->execute([$id]);
}
// Alterar senha
$mensagem = '';
if (isset($_POST['senha_id']) && isset($_POST['nova_senha'])) {
    $id = intval($_POST['senha_id']);
    $nova = $_POST['nova_senha'];
    if (strlen($nova) >= 6) {
        $hash = password_hash($nova, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ? AND tipo = "aluno"');
        $stmt->execute([$hash, $id]);
        $mensagem = 'Senha alterada com sucesso!';
    } else {
        $mensagem = 'A senha deve ter pelo menos 6 caracteres.';
    }
}
// Buscar alunos
$stmt = $pdo->query("SELECT id, nome, email, data_criacao FROM usuarios WHERE tipo = 'aluno' ORDER BY data_criacao DESC");
$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Alunos</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f7f8fa; font-family: 'Montserrat', Arial, sans-serif; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 12px #0001; padding: 32px 24px; }
        h2 { color: #2d3e50; text-align: center; margin-bottom: 32px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th, td { padding: 12px 8px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f9f9fb; color: #2d3e50; }
        tr:last-child td { border-bottom: none; }
        .btn { background: #2d3e50; color: #fff; border: none; padding: 7px 18px; border-radius: 8px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: background 0.2s; margin-right: 6px; }
        .btn:hover { background: #ffb300; color: #222; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; color: #fff; }
        .senha-form { display: inline-block; }
        .mensagem { color: #2d3e50; text-align: center; margin-bottom: 18px; }
        .voltar { display: inline-block; margin-top: 18px; background: #888; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gestão de Alunos</h2>
        <?php if ($mensagem): ?>
            <div class="mensagem"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <table>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Data de Cadastro</th>
                <th>Ações</th>
            </tr>
            <?php foreach ($alunos as $aluno): ?>
                <tr>
                    <td><?php echo htmlspecialchars($aluno['nome']); ?></td>
                    <td><?php echo htmlspecialchars($aluno['email']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($aluno['data_criacao'])); ?></td>
                    <td>
                        <form method="post" class="senha-form" style="margin-bottom:4px;">
                            <input type="hidden" name="senha_id" value="<?php echo $aluno['id']; ?>">
                            <input type="password" name="nova_senha" placeholder="Nova senha" style="padding:4px 8px; border-radius:6px; border:1px solid #ccc; width:110px;">
                            <button type="submit" class="btn">Alterar Senha</button>
                        </form>
                        <form method="post" style="display:inline-block;">
                            <input type="hidden" name="excluir_id" value="<?php echo $aluno['id']; ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Excluir este aluno?')">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($alunos)): ?>
                <tr><td colspan="4" style="text-align:center; color:#888;">Nenhum aluno cadastrado.</td></tr>
            <?php endif; ?>
        </table>
        <a href="../dashboard.php" class="btn voltar">&larr; Voltar</a>
    </div>
</body>
</html> 