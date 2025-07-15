<?php
require_once '../includes/auth.php';
require_login('admin');
require_once '../includes/db.php';

// Total de alunos
$stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE tipo = 'aluno'");
$total_alunos = $stmt->fetchColumn();
// Total de cursos
$stmt = $pdo->query("SELECT COUNT(*) FROM cursos");
$total_cursos = $stmt->fetchColumn();
// Total de receita
$stmt = $pdo->query("SELECT SUM(valor) FROM pagamentos WHERE status = 'approved'");
$total_receita = $stmt->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f7f8fa; font-family: 'Montserrat', Arial, sans-serif; }
        .container { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 12px #0001; padding: 32px 24px; }
        h2 { color: #2d3e50; text-align: center; margin-bottom: 32px; }
        .stats { display: flex; gap: 32px; justify-content: center; margin-bottom: 32px; flex-wrap: wrap; }
        .stat { background: #f9f9fb; border-radius: 14px; box-shadow: 0 2px 8px #0001; padding: 28px 36px; text-align: center; min-width: 160px; }
        .stat-label { color: #888; font-size: 1.05rem; margin-bottom: 8px; }
        .stat-value { color: #2d3e50; font-size: 2.2rem; font-weight: bold; }
        .admin-links { text-align: center; margin-top: 24px; }
        .admin-links a { display: inline-block; margin: 0 12px; color: #2d3e50; background: #ffb30022; border-radius: 8px; padding: 8px 18px; text-decoration: none; font-weight: 500; transition: background 0.2s; }
        .admin-links a:hover { background: #ffb300; color: #222; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Dashboard do Admin</h2>
        <div class="stats">
            <div class="stat">
                <div class="stat-label">Total de Alunos</div>
                <div class="stat-value"><?php echo $total_alunos; ?></div>
            </div>
            <div class="stat">
                <div class="stat-label">Total de Cursos</div>
                <div class="stat-value"><?php echo $total_cursos; ?></div>
            </div>
            <div class="stat">
                <div class="stat-label">Receita Total</div>
                <div class="stat-value">R$ <?php echo number_format($total_receita, 2, ',', '.'); ?></div>
            </div>
        </div>
        <div class="admin-links">
            <a href="dashboard/alunos.php">Gestão de Alunos</a>
            <a href="dashboard/lancar-curso.php">Cadastrar Curso</a>
        </div>
    </div>
</body>
</html> 