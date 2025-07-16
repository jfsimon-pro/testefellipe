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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #fafafa;
            font-family: 'Inter', sans-serif;
            color: #1f2937;
            padding-bottom: 100px;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 40px 32px 32px 32px;
            position: relative;
        }
        .logout-btn {
            position: absolute;
            top: 24px;
            right: 24px;
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }
        .logout-btn:hover { background: #dc2626; }
        .back-btn {
            background: #e5e7eb;
            color: #0ea5e9;
            margin-top: 0;
            margin-bottom: 18px;
            border: none;
            padding: 10px 28px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            display: inline-block;
        }
        .back-btn:hover { background: #cbd5e1; color: #0284c7; }
        .course-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .course-title {
            font-size: 2rem;
            font-weight: 700;
            color: #0ea5e9;
            margin-bottom: 8px;
        }
        .course-meta {
            font-size: 1rem;
            color: #64748b;
            margin-bottom: 8px;
        }
        .progress-bar-bg {
            background: #e5e7eb;
            border-radius: 8px;
            height: 14px;
            width: 100%;
            margin: 18px 0 8px 0;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            border-radius: 8px;
            transition: width 0.3s;
        }
        .progress-label {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .pdf-viewer {
            width: 100%;
            height: 480px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 18px;
            background: #f3f4f6;
        }
        .btn {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 12px;
            margin-bottom: 8px;
            transition: all 0.3s;
            display: inline-block;
        }
        .btn:hover { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); }
        .aulas-list {
            margin-bottom: 24px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .aula-item {
            background: #f3f4f6;
            border-radius: 10px;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 1rem;
        }
        .aula-atual {
            background: #e0f2fe;
            border: 2px solid #0ea5e9;
        }
        .aula-title { font-weight: 600; color: #1f2937; }
        .aula-concluida { color: #0ea5e9; font-size: 0.98rem; margin-left: 8px; }
        .aula-btn {
            margin-left: 12px;
            background: #fff;
            color: #0ea5e9;
            border: 1px solid #0ea5e9;
            border-radius: 8px;
            padding: 6px 18px;
            font-size: 0.98rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .aula-btn:hover {
            background: #0ea5e9;
            color: #fff;
        }
        .aula-content {
            margin-top: 18px;
            background: #f9fafb;
            border-radius: 14px;
            box-shadow: 0 2px 8px #0001;
            padding: 24px 18px;
        }
        .video-embed {
            width: 100%;
            max-width: 600px;
            height: 340px;
            margin-bottom: 18px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #000;
        }
        .pdf-link {
            display: inline-block;
            margin-top: 8px;
            color: #0ea5e9;
            font-weight: 500;
            text-decoration: none;
        }
        .pdf-link:hover { text-decoration: underline; }
        /* Floating nav */
        .floating-nav {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            padding: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            display: flex;
            gap: 8px;
            z-index: 1000;
        }
        .aula-content ol, .aula-content ul {
            padding: 30px;
        }
        .aula-content img {
            padding: 10px;
        }
        .nav-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            background: #fff;
            color: #6b7280;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .alinha-elementos-concluidos a {
            text-decoration: none;
        }
        .nav-btn.active {
            background: #0ea5e9;
            color: #fff;
        }
        .nav-btn:hover { background: #f3f4f6; }
        .nav-btn.active:hover { background: #0284c7; }
    </style>
</head>
<body>
    <div class="container">
        <button class="logout-btn" onclick="window.location.href='../logout.php'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
        <button class="back-btn" onclick="window.location.href='cursos-matriculados.php'">
            <i class="fas fa-arrow-left"></i> Voltar
        </button>
        <div class="course-header">
            <div class="course-title"><?php echo htmlspecialchars($curso['nome']); ?></div>
            <div class="course-meta">
                <b>Categoria:</b> <?php echo htmlspecialchars($curso['categoria']); ?> |
                <b>Tipo:</b> <?php echo $curso['tipo'] === 'pdf' ? 'PDF Único' : 'Aulas'; ?>
            </div>
        </div>
        <?php if ($curso['tipo'] === 'pdf'): ?>
            <?php if ($pdf && $pdf['arquivo_pdf']): ?>
                <iframe class="pdf-viewer" src="../uploads/<?php echo htmlspecialchars($pdf['arquivo_pdf']); ?>#toolbar=0" frameborder="0"></iframe>
                <form method="post">
                    <button type="submit" name="marcar_lido" class="btn" <?php echo ($progresso && $progresso['concluido']) ? 'disabled' : ''; ?>><?php echo ($progresso && $progresso['concluido']) ? 'PDF já lido' : 'Marcar como lido'; ?></button>
                </form>
                <?php
                    $percent = $progresso ? ($progresso['concluido'] ? 100 : floatval($progresso['percentual_lido'])) : 0;
                ?>
                <div class="progress-bar-bg">
                    <div class="progress-bar" style="background: #0ea5e9; width:<?php echo $percent; ?>%;"></div>
                </div>
                <div class="progress-label">Progresso: <?php echo number_format($percent, 1); ?>%</div>
            <?php else: ?>
                <div style="color:#dc2626;">PDF não disponível.</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="aulas-list">
                <?php foreach ($aulas as $a): ?>
                    <div class="aula-item<?php echo ($aula_atual && $aula_atual['id'] == $a['id']) ? ' aula-atual' : ''; ?>">
                        <span class="aula-title"><?php echo htmlspecialchars($a['titulo']); ?></span>
                        <div class="alinha-elementos-concluidos">
                            <?php if (!empty($prog_aula[$a['id']])): ?><span class="aula-concluida"><i class="fas fa-check-circle"></i> Concluída</span><?php endif; ?>
                            <a href="?id=<?php echo $curso_id; ?>&aula=<?php echo $a['id']; ?>" class="aula-btn">Ver</a>
                        </div>
                        
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
                $total = count($aulas);
                $concluidas = 0;
                foreach ($aulas as $a) if (!empty($prog_aula[$a['id']])) $concluidas++;
                $percent = $total > 0 ? ($concluidas / $total) * 100 : 0;
            ?>
            <div class="progress-bar-bg">
                <div class="progress-bar" style="background: #ffb300; width:<?php echo $percent; ?>%;"></div>
            </div>
            <div class="progress-label">Progresso: <?php echo $concluidas; ?> / <?php echo $total; ?> aulas (<?php echo number_format($percent, 1); ?>%)</div>
            <?php if ($aula_atual): ?>
                <div class="aula-content">
                    <h3 style="margin-bottom: 16px; color:#0ea5e9; font-size:1.3rem; font-weight:700;"><?php echo htmlspecialchars($aula_atual['titulo']); ?></h3>
                    <div style="margin-bottom: 12px; display:flex; gap:8px; flex-wrap:wrap;">
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
                        <a href="?id=<?php echo $curso_id; ?>&aula=<?php echo $aula_anterior; ?>" class="btn" style="background:#e5e7eb; color:#0ea5e9;"><i class="fas fa-arrow-left"></i> Aula anterior</a>
                    <?php endif; ?>
                    <?php if ($aula_proxima !== null): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="aula_id" value="<?php echo $aula_atual['id']; ?>">
                            <input type="hidden" name="proxima_aula" value="<?php echo $aula_proxima; ?>">
                            <button type="submit" name="concluir_e_avancar" class="btn">Próxima aula <i class="fas fa-arrow-right"></i></button>
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
                        <a class="pdf-link" href="../uploads/<?php echo htmlspecialchars($aula_atual['arquivo_pdf']); ?>" target="_blank"><i class="fas fa-file-pdf"></i> Baixar PDF da aula</a>
                    <?php endif; ?>
                    <form method="post" style="margin-top:16px; display:inline-block;">
                        <input type="hidden" name="aula_id" value="<?php echo $aula_atual['id']; ?>">
                        <?php if (!empty($prog_aula[$aula_atual['id']])): ?>
                            <button type="submit" name="desmarcar_aula" class="btn" style="background:#e5e7eb; color:#0ea5e9;">Desmarcar concluída</button>
                        <?php else: ?>
                            <button type="submit" name="concluir_aula" class="btn">Marcar como concluída</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <div class="floating-nav">
        <button class="nav-btn" onclick="window.location.href='meus-cursos.php'">
            <i class="fas fa-home"></i>
        </button>
        <button class="nav-btn active" onclick="window.location.href='cursos-matriculados.php'">
            <i class="fas fa-bookmark"></i>
        </button>
        <button class="nav-btn" onclick="window.location.href='perfil.php'">
            <i class="fas fa-user"></i>
        </button>
        <button class="nav-btn" onclick="window.location.href='../logout.php'">
            <i class="fas fa-sign-out-alt"></i>
        </button>
    </div>
    <script>
        // Marcar botão ativo no menu
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname;
            const navButtons = document.querySelectorAll('.nav-btn');
            navButtons.forEach(btn => {
                if (btn.onclick.toString().includes(currentPage.split('/').pop())) {
                    btn.classList.add('active');
                }
            });
        });
    </script>
</body>
</html> 