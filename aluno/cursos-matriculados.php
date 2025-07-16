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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos Matriculados - Plataforma Educacional</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            margin-bottom: 32px;
        }
        .main-title {
            font-size: 28px;
            font-weight: 700;
            color: #0ea5e9;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .subtitle {
            font-size: 16px;
            color: #64748b;
            margin-bottom: 24px;
        }
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .course-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
        }
        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        .course-image {
            width: 100%;
            height: 160px;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
        }
        .bookmark-icon {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #fff;
            border-radius: 8px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0ea5e9;
            font-size: 14px;
        }
        .course-content {
            padding: 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .course-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }
        .course-meta {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .progress-bar-bg {
            background: #e5e7eb;
            border-radius: 8px;
            height: 14px;
            width: 100%;
            margin-bottom: 8px;
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
        .course-btn {
            width: 100%;
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: #fff;
            border: none;
            padding: 10px 0;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s;
        }
        .course-btn:hover {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(14, 165, 233, 0.15);
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
        @media (max-width: 768px) {
            .container {
                max-width: 100%;
                padding: 16px;
                margin: 0;
            }
            .main-title {
                font-size: 24px;
            }
            .courses-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .course-card {
                min-width: 0;
            }
            .logout-btn {
                display: none;
            }
        }
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
        <div class="header">
            <div class="main-title"><i class="fas fa-bookmark"></i> Cursos Matriculados</div>
            <div class="subtitle">Acompanhe seu progresso nos cursos</div>
        </div>
        <div class="courses-grid">
            <?php foreach ($cursos as $curso): ?>
                <div class="course-card" onclick="window.location.href='curso.php?id=<?php echo $curso['id']; ?>'">
                    <div class="course-image" style="background-image: url('<?php echo $curso['capa'] ? '../uploads/' . htmlspecialchars($curso['capa']) : 'https://via.placeholder.com/300x160/0ea5e9/ffffff?text=Curso'; ?>');">
                        <div class="bookmark-icon">
                            <i class="fas fa-bookmark"></i>
                        </div>
                    </div>
                    <div class="course-content">
                        <div class="course-title"><?php echo htmlspecialchars($curso['nome']); ?></div>
                        <div class="course-meta">
                            <span><?php echo $curso['tipo'] === 'pdf' ? 'PDF Único' : 'Aulas'; ?></span> |
                            <span><?php echo htmlspecialchars($curso['categoria']); ?></span>
                        </div>
                        <div class="course-meta">Data de lançamento: <?php echo date('d/m/Y', strtotime($curso['data_criacao'])); ?></div>
                        <div class="progress-label">Progresso:</div>
                        <div class="progress-bar-bg">
                            <?php if ($curso['tipo'] === 'pdf'): ?>
                                <?php
                                    $percent = 0;
                                    if (isset($pdf_prog[$curso['id']])) {
                                        $percent = $pdf_prog[$curso['id']]['concluido'] ? 100 : floatval($pdf_prog[$curso['id']]['percentual_lido']);
                                    }
                                ?>
                                <div class="progress-bar" style="background: #0ea5e9; width: <?php echo $percent; ?>%;"></div>
                            <?php else: ?>
                                <?php
                                    $total = $aula_prog[$curso['id']]['total'] ?? 0;
                                    $concluidas = $aula_prog[$curso['id']]['concluidas'] ?? 0;
                                    $percent = $total > 0 ? ($concluidas / $total) * 100 : 0;
                                ?>
                                <div class="progress-bar" style="background: #ffb300; width: <?php echo $percent; ?>%;"></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($curso['tipo'] === 'pdf'): ?>
                            <div class="progress-label">PDF: <?php echo number_format($percent, 1); ?>% lido</div>
                        <?php else: ?>
                            <div class="progress-label">Aulas concluídas: <?php echo $concluidas; ?> / <?php echo $total; ?> (<?php echo number_format($percent, 1); ?>%)</div>
                        <?php endif; ?>
                        <button class="course-btn" onclick="event.stopPropagation(); window.location.href='curso.php?id=<?php echo $curso['id']; ?>'">Acessar</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
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