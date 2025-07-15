<?php
require_once '../includes/auth.php';
require_login('aluno');
require_once '../includes/db.php';

$usuario_id = $_SESSION['usuario_id'];
$curso_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$curso_id) { die('Curso não encontrado.'); }

// Verifica se o aluno está matriculado
$stmt = $pdo->prepare('SELECT * FROM matriculas WHERE id_usuario = ? AND id_curso = ?');
$stmt->execute([$usuario_id, $curso_id]);
if (!$stmt->fetch()) { die('Acesso não permitido.'); }

// Busca dados do curso
$stmt = $pdo->prepare('SELECT * FROM cursos WHERE id = ?');
$stmt->execute([$curso_id]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$curso) { die('Curso não encontrado.'); }

// Tracking PDF
if ($curso['tipo'] === 'pdf') {
    // Busca PDF
    $stmt = $pdo->prepare('SELECT * FROM curso_pdf WHERE id_curso = ?');
    $stmt->execute([$curso_id]);
    $pdf = $stmt->fetch(PDO::FETCH_ASSOC);
    // Progresso
    $stmt = $pdo->prepare('SELECT * FROM progresso_pdf WHERE id_usuario = ? AND id_curso = ?');
    $stmt->execute([$usuario_id, $curso_id]);
    $progresso = $stmt->fetch(PDO::FETCH_ASSOC);
    // Marcar como lido
    if (isset($_POST['marcar_lido'])) {
        if ($progresso && !$progresso['concluido']) {
            $stmt = $pdo->prepare('UPDATE progresso_pdf SET concluido = 1, percentual_lido = 100, data_conclusao = NOW() WHERE id = ?');
            $stmt->execute([$progresso['id']]);
        } elseif (!$progresso) {
            $stmt = $pdo->prepare('INSERT INTO progresso_pdf (id_usuario, id_curso, concluido, percentual_lido, data_conclusao) VALUES (?, ?, 1, 100, NOW())');
            $stmt->execute([$usuario_id, $curso_id]);
        }
        header('Location: curso.php?id=' . $curso_id);
        exit;
    }
}
// Tracking Aulas
if ($curso['tipo'] === 'aulas') {
    // Busca aulas
    $stmt = $pdo->prepare('SELECT * FROM aulas WHERE id_curso = ? ORDER BY ordem, id');
    $stmt->execute([$curso_id]);
    $aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Progresso das aulas
    $prog_aula = [];
    $stmt = $pdo->prepare('SELECT id_aula, concluida FROM progresso_aula WHERE id_usuario = ?');
    $stmt->execute([$usuario_id]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $prog_aula[$row['id_aula']] = $row['concluida'];
    }
    // Marcar aula como concluída
    if (isset($_POST['concluir_aula']) && isset($_POST['aula_id'])) {
        $aula_id = intval($_POST['aula_id']);
        if (!isset($prog_aula[$aula_id]) || !$prog_aula[$aula_id]) {
            $stmt = $pdo->prepare('INSERT INTO progresso_aula (id_usuario, id_aula, concluida, data_conclusao) VALUES (?, ?, 1, NOW()) ON DUPLICATE KEY UPDATE concluida = 1, data_conclusao = NOW()');
            $stmt->execute([$usuario_id, $aula_id]);
        }
        header('Location: curso.php?id=' . $curso_id . '&aula=' . $aula_id);
        exit;
    }
    // Desmarcar aula como concluída
    if (isset($_POST['desmarcar_aula']) && isset($_POST['aula_id'])) {
        $aula_id = intval($_POST['aula_id']);
        $stmt = $pdo->prepare('UPDATE progresso_aula SET concluida = 0, data_conclusao = NULL WHERE id_usuario = ? AND id_aula = ?');
        $stmt->execute([$usuario_id, $aula_id]);
        header('Location: curso.php?id=' . $curso_id . '&aula=' . $aula_id);
        exit;
    }
    // Marcar aula como concluída E avançar para próxima
    if (isset($_POST['concluir_e_avancar']) && isset($_POST['aula_id']) && isset($_POST['proxima_aula'])) {
        $aula_id = intval($_POST['aula_id']);
        $proxima_aula = intval($_POST['proxima_aula']);
        if (!isset($prog_aula[$aula_id]) || !$prog_aula[$aula_id]) {
            $stmt = $pdo->prepare('INSERT INTO progresso_aula (id_usuario, id_aula, concluida, data_conclusao) VALUES (?, ?, 1, NOW()) ON DUPLICATE KEY UPDATE concluida = 1, data_conclusao = NOW()');
            $stmt->execute([$usuario_id, $aula_id]);
        }
        header('Location: curso.php?id=' . $curso_id . '&aula=' . $proxima_aula);
        exit;
    }
    $aula_id = isset($_GET['aula']) ? intval($_GET['aula']) : ($aulas[0]['id'] ?? 0);
    $aula_atual = null;
    foreach ($aulas as $a) { if ($a['id'] == $aula_id) $aula_atual = $a; }
}

function youtube_embed_url($url) {
    if (preg_match('~youtu\.be/([\w-]+)~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (preg_match('~youtube\.com/watch\?v=([\w-]+)~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (preg_match('~youtube\.com/embed/([\w-]+)~', $url, $m)) {
        return $url;
    }
    return $url;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($curso['nome']); ?> - Plataforma Educacional</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f7f8fa; font-family: 'Montserrat', Arial, sans-serif; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 12px #0001; padding: 32px 24px; }
        h2 { color: #2d3e50; text-align: center; margin-bottom: 24px; }
        .pdf-viewer { width: 100%; height: 600px; border: 1px solid #ccc; border-radius: 8px; margin-bottom: 18px; }
        .btn { background: #2d3e50; color: #fff; border: none; padding: 10px 28px; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; margin-top: 12px; transition: background 0.2s; }
        .btn:hover { background: #ffb300; color: #222; }
        .aulas-list { margin-bottom: 24px; }
        .aula-item { padding: 10px 0; border-bottom: 1px solid #eee; }
        .aula-atual { background: #fffbec; border-radius: 8px; }
        .aula-title { font-weight: bold; }
        .aula-concluida { color: #2d3e50; font-size: 0.98rem; margin-left: 8px; }
        .aula-btn { margin-left: 12px; }
        .aula-content { margin-top: 18px; }
        .video-embed { width: 100%; max-width: 600px; height: 340px; margin-bottom: 18px; }
        .pdf-link { display: inline-block; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="../logout.php" style="float:right; margin-top:8px; background:#e74c3c; color:#fff; padding:8px 18px; border-radius:8px; text-decoration:none; font-weight:bold;">Logout</a>
        <a href="cursos-matriculados.php" class="btn" style="margin-bottom:18px; display:inline-block;">&larr; Voltar</a>
        <h2><?php echo htmlspecialchars($curso['nome']); ?></h2>
        <div><b>Categoria:</b> <?php echo htmlspecialchars($curso['categoria']); ?> | <b>Tipo:</b> <?php echo $curso['tipo'] === 'pdf' ? 'PDF Único' : 'Aulas'; ?></div>
        <hr style="margin: 18px 0;">
        <?php if ($curso['tipo'] === 'pdf'): ?>
            <?php if ($pdf && $pdf['arquivo_pdf']): ?>
                <iframe class="pdf-viewer" src="../uploads/<?php echo htmlspecialchars($pdf['arquivo_pdf']); ?>#toolbar=0" frameborder="0"></iframe>
                <form method="post">
                    <button type="submit" name="marcar_lido" class="btn" <?php echo ($progresso && $progresso['concluido']) ? 'disabled' : ''; ?>><?php echo ($progresso && $progresso['concluido']) ? 'PDF já lido' : 'Marcar como lido'; ?></button>
                </form>
                <?php
                    $percent = $progresso ? ($progresso['concluido'] ? 100 : floatval($progresso['percentual_lido'])) : 0;
                ?>
                <div style="background:#eee; border-radius:8px; height:16px; width:100%; max-width:320px; margin:14px 0 4px 0; overflow:hidden;">
                    <div style="background:#2d3e50; height:100%; width:<?php echo $percent; ?>%; transition:width 0.3s;"></div>
                </div>
                <div style="color:#2d3e50;">Progresso: <?php echo number_format($percent, 1); ?>%</div>
            <?php else: ?>
                <div>PDF não disponível.</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="aulas-list">
                <?php foreach ($aulas as $a): ?>
                    <div class="aula-item<?php echo ($aula_atual && $aula_atual['id'] == $a['id']) ? ' aula-atual' : ''; ?>">
                        <span class="aula-title"><?php echo htmlspecialchars($a['titulo']); ?></span>
                        <?php if (!empty($prog_aula[$a['id']])): ?><span class="aula-concluida">(Concluída)</span><?php endif; ?>
                        <a href="?id=<?php echo $curso_id; ?>&aula=<?php echo $a['id']; ?>" class="aula-btn btn" style="padding:4px 16px; font-size:0.98rem;">Ver</a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
                $total = count($aulas);
                $concluidas = 0;
                foreach ($aulas as $a) if (!empty($prog_aula[$a['id']])) $concluidas++;
                $percent = $total > 0 ? ($concluidas / $total) * 100 : 0;
            ?>
            <div style="background:#eee; border-radius:8px; height:16px; width:100%; max-width:320px; margin:14px 0 4px 0; overflow:hidden;">
                <div style="background:#ffb300; height:100%; width:<?php echo $percent; ?>%; transition:width 0.3s;"></div>
            </div>
            <div style="color:#2d3e50; margin-bottom:18px;">Progresso: <?php echo $concluidas; ?> / <?php echo $total; ?> aulas (<?php echo number_format($percent, 1); ?>%)</div>
            <?php if ($aula_atual): ?>
                <div class="aula-content">
                    <h3><?php echo htmlspecialchars($aula_atual['titulo']); ?></h3>
                    <div style="margin-bottom: 12px;">
                    <?php
                        // Navegação entre aulas
                        $idx_atual = 0;
                        foreach ($aulas as $idx => $a) {
                            if ($a['id'] == $aula_atual['id']) {
                                $idx_atual = $idx;
                                break;
                            }
                        }
                        $aula_anterior = $aulas[$idx_atual - 1]['id'] ?? null;
                        $aula_proxima = $aulas[$idx_atual + 1]['id'] ?? null;
                    ?>
                    <?php if ($aula_anterior !== null): ?>
                        <a href="?id=<?php echo $curso_id; ?>&aula=<?php echo $aula_anterior; ?>" class="btn" style="margin-right:10px; padding:6px 18px; font-size:0.98rem;">&larr; Aula anterior</a>
                    <?php endif; ?>
                    <?php if ($aula_proxima !== null): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="aula_id" value="<?php echo $aula_atual['id']; ?>">
                            <input type="hidden" name="proxima_aula" value="<?php echo $aula_proxima; ?>">
                            <button type="submit" name="concluir_e_avancar" class="btn" style="padding:6px 18px; font-size:0.98rem;">Próxima aula &rarr;</button>
                        </form>
                    <?php endif; ?>
                    </div>
                    <?php if ($aula_atual['video_url']): ?>
                        <iframe class="video-embed" src="<?php echo htmlspecialchars(youtube_embed_url($aula_atual['video_url'])); ?>" frameborder="0" allowfullscreen></iframe>
                    <?php endif; ?>
                    <?php if ($aula_atual['texto']): ?>
                        <div style="margin-bottom:12px;"><?php echo $aula_atual['texto']; ?></div>
                    <?php endif; ?>
                    <?php if ($aula_atual['arquivo_pdf']): ?>
                        <a class="pdf-link" href="../uploads/<?php echo htmlspecialchars($aula_atual['arquivo_pdf']); ?>" target="_blank">Baixar PDF da aula</a>
                    <?php endif; ?>
                    <form method="post" style="margin-top:16px; display:inline-block;">
                        <input type="hidden" name="aula_id" value="<?php echo $aula_atual['id']; ?>">
                        <?php if (!empty($prog_aula[$aula_atual['id']])): ?>
                            <button type="submit" name="desmarcar_aula" class="btn" style="background:#888;">Desmarcar concluída</button>
                        <?php else: ?>
                            <button type="submit" name="concluir_aula" class="btn">Marcar como concluída</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html> 