<?php
require_once '../../includes/auth.php';
require_login('admin');
require_once '../../includes/db.php';

// Excluir curso
if (isset($_POST['excluir_id'])) {
    $id = intval($_POST['excluir_id']);
    $stmt = $pdo->prepare('DELETE FROM cursos WHERE id = ?');
    $stmt->execute([$id]);
}
// Buscar cursos
$stmt = $pdo->query('SELECT * FROM cursos ORDER BY data_criacao DESC');
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Cursos</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f7f8fa; font-family: 'Montserrat', Arial, sans-serif; }
        .container { max-width: 1100px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 12px #0001; padding: 32px 24px; }
        h2 { color: #2d3e50; text-align: center; margin-bottom: 32px; }
        .courses-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 28px; }
        .course-card { background: #f9f9fb; border-radius: 14px; box-shadow: 0 2px 8px #0001; padding: 22px 18px 18px 18px; display: flex; flex-direction: column; align-items: flex-start; transition: transform 0.15s, box-shadow 0.15s; }
        .course-card:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 6px 24px #0002; }
        .course-capa { width: 100%; max-width: 220px; height: 120px; object-fit: cover; border-radius: 10px; margin-bottom: 10px; background: #eee; }
        .course-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 8px; }
        .course-meta { font-size: 0.98rem; color: #666; margin-bottom: 14px; }
        .course-type { display: inline-block; background: #ffb30022; color: #b47a00; border-radius: 8px; padding: 2px 10px; font-size: 0.92rem; margin-right: 8px; }
        .course-category { display: inline-block; background: #2d3e5011; color: #2d3e50; border-radius: 8px; padding: 2px 10px; font-size: 0.92rem; }
        .course-btn { background: #e74c3c; color: #fff; border: none; padding: 8px 22px; border-radius: 16px; font-size: 1rem; cursor: pointer; transition: background 0.2s; margin-top: 10px; }
        .course-btn:hover { background: #c0392b; color: #fff; }
        .data { color: #888; font-size: 0.97rem; margin-bottom: 6px; }
        .voltar { display: inline-block; margin-top: 18px; background: #888; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Gerenciar Cursos</h2>
        <div class="courses-grid">
            <?php foreach ($cursos as $curso): ?>
                <div class="course-card">
                    <img class="course-capa" src="<?php echo $curso['capa'] ? '../../uploads/' . htmlspecialchars($curso['capa']) : 'https://via.placeholder.com/220x120?text=Sem+Capa'; ?>" alt="Capa do curso">
                    <div class="course-title"><?php echo htmlspecialchars($curso['nome']); ?></div>
                    <div class="course-meta">
                        <span class="course-type"><?php echo $curso['tipo'] === 'pdf' ? 'PDF Único' : 'Aulas'; ?></span>
                        <span class="course-category"><?php echo htmlspecialchars($curso['categoria']); ?></span>
                    </div>
                    <div class="data">Data: <?php echo date('d/m/Y', strtotime($curso['data_criacao'])); ?></div>
                    <form method="post" onsubmit="return confirm('Excluir este curso?');">
                        <input type="hidden" name="excluir_id" value="<?php echo $curso['id']; ?>">
                        <button type="submit" class="course-btn">Excluir</button>
                    </form>
                </div>
            <?php endforeach; ?>
            <?php if (empty($cursos)): ?>
                <div style="grid-column:1/-1; text-align:center; color:#888;">Nenhum curso cadastrado.</div>
            <?php endif; ?>
        </div>
        <a href="../dashboard.php" class="btn voltar">&larr; Voltar</a>
    </div>
</body>
</html> 