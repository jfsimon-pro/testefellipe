<?php
require_once '../includes/auth.php';
require_login('aluno');
require_once '../includes/db.php';

$usuario_id = $_SESSION['usuario_id'];
// Buscar cursos em que o aluno está matriculado
$stmt = $pdo->prepare('SELECT c.*, m.id AS matricula_id FROM cursos c INNER JOIN matriculas m ON m.id_curso = c.id WHERE m.id_usuario = ? ORDER BY c.data_criacao DESC');
$stmt->execute([$usuario_id]);
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar progresso dos PDFs
$pdf_prog = [];
$stmt = $pdo->prepare('SELECT id_curso, concluido, percentual_lido FROM progresso_pdf WHERE id_usuario = ?');
$stmt->execute([$usuario_id]);
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $pdf_prog[$row['id_curso']] = $row;
}
// Buscar progresso das aulas
$aula_prog = [];
$stmt = $pdo->prepare('SELECT a.id_curso, COUNT(*) as total, SUM(pa.concluida) as concluidas FROM aulas a LEFT JOIN progresso_aula pa ON pa.id_aula = a.id AND pa.id_usuario = ? GROUP BY a.id_curso');
$stmt->execute([$usuario_id]);
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $aula_prog[$row['id_curso']] = $row;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cursos Matriculados - Plataforma Educacional</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f7f8fa; font-family: 'Montserrat', Arial, sans-serif; }
        .container { max-width: 1100px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 12px #0001; padding: 32px 24px; }
        h2 { color: #2d3e50; text-align: center; margin-bottom: 32px; }
        .courses-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 28px; }
        .course-card { background: #f9f9fb; border-radius: 14px; box-shadow: 0 2px 8px #0001; padding: 22px 18px 18px 18px; display: flex; flex-direction: column; align-items: flex-start; transition: transform 0.15s, box-shadow 0.15s; }
        .course-card:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 6px 24px #0002; }
        .course-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 8px; }
        .course-meta { font-size: 0.98rem; color: #666; margin-bottom: 14px; }
        .course-type { display: inline-block; background: #ffb30022; color: #b47a00; border-radius: 8px; padding: 2px 10px; font-size: 0.92rem; margin-right: 8px; }
        .course-category { display: inline-block; background: #2d3e5011; color: #2d3e50; border-radius: 8px; padding: 2px 10px; font-size: 0.92rem; }
        .progresso { margin-top: 10px; font-size: 0.98rem; color: #2d3e50; }
        .course-btn { margin-top: auto; background: #2d3e50; color: #fff; border: none; padding: 8px 22px; border-radius: 16px; font-size: 1rem; cursor: pointer; transition: background 0.2s; }
        .course-btn:hover { background: #ffb300; color: #222; }
        @media (max-width: 600px) { h2 { font-size: 1.1rem; } }
    </style>
</head>
<body>
    <div class="container">
        <a href="../logout.php" style="float:right; margin-top:8px; background:#e74c3c; color:#fff; padding:8px 18px; border-radius:8px; text-decoration:none; font-weight:bold;">Logout</a>
        <h2>Cursos Matriculados</h2>
        <div class="courses-grid">
            <?php foreach ($cursos as $curso): ?>
                <div class="course-card">
                    <div class="course-title"><?php echo htmlspecialchars($curso['nome']); ?></div>
                    <div class="course-meta">
                        <span class="course-type"><?php echo $curso['tipo'] === 'pdf' ? 'PDF Único' : 'Aulas'; ?></span>
                        <span class="course-category"><?php echo htmlspecialchars($curso['categoria']); ?></span>
                    </div>
                    <div>Data de lançamento: <?php echo date('d/m/Y', strtotime($curso['data_criacao'])); ?></div>
                    <div class="progresso">
                        <?php if ($curso['tipo'] === 'pdf'): ?>
                            <?php
                                $percent = 0;
                                if (isset($pdf_prog[$curso['id']])) {
                                    $percent = $pdf_prog[$curso['id']]['concluido'] ? 100 : floatval($pdf_prog[$curso['id']]['percentual_lido']);
                                }
                            ?>
                            <div style="background:#eee; border-radius:8px; height:16px; width:100%; max-width:220px; margin-bottom:4px; overflow:hidden;">
                                <div style="background:#2d3e50; height:100%; width:<?php echo $percent; ?>%; transition:width 0.3s;"></div>
                            </div>
                            <span>PDF: <?php echo number_format($percent, 1); ?>% lido</span>
                        <?php else: ?>
                            <?php
                                $total = $aula_prog[$curso['id']]['total'] ?? 0;
                                $concluidas = $aula_prog[$curso['id']]['concluidas'] ?? 0;
                                $percent = $total > 0 ? ($concluidas / $total) * 100 : 0;
                            ?>
                            <div style="background:#eee; border-radius:8px; height:16px; width:100%; max-width:220px; margin-bottom:4px; overflow:hidden;">
                                <div style="background:#ffb300; height:100%; width:<?php echo $percent; ?>%; transition:width 0.3s;"></div>
                            </div>
                            <span>Aulas concluídas: <?php echo $concluidas; ?> / <?php echo $total; ?> (<?php echo number_format($percent, 1); ?>%)</span>
                        <?php endif; ?>
                    </div>
                    <a href="curso.php?id=<?php echo $curso['id']; ?>"><button class="course-btn">Acessar</button></a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html> 