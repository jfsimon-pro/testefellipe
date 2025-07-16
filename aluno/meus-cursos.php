<?php
require_once '../includes/auth.php';
require_login('aluno');
require_once '../includes/db.php';

// Buscar todos os cursos
$stmt = $pdo->query('SELECT * FROM cursos ORDER BY data_criacao DESC');
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar cursos em que o aluno está matriculado
$usuario_id = $_SESSION['usuario_id'];
$stmt = $pdo->prepare('SELECT id_curso FROM matriculas WHERE id_usuario = ?');
$stmt->execute([$usuario_id]);
$matriculados = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id_curso');

// Obter categorias distintas
$categorias = array_unique(array_filter(array_map(function($c){ return $c['categoria']; }, $cursos)));

// Filtros e busca
$tipo = $_GET['tipo'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$busca = $_GET['busca'] ?? '';
$ordem = $_GET['ordem'] ?? 'desc';

// Filtrar cursos
$cursos_filtrados = array_filter($cursos, function($curso) use ($tipo, $categoria, $busca) {
    $ok = true;
    if ($tipo && $curso['tipo'] !== $tipo) $ok = false;
    if ($categoria && $curso['categoria'] !== $categoria) $ok = false;
    if ($busca && stripos($curso['nome'], $busca) === false) $ok = false;
    return $ok;
});
// Ordenar por data
usort($cursos_filtrados, function($a, $b) use ($ordem) {
    if ($ordem === 'asc') return strtotime($a['data_criacao']) - strtotime($b['data_criacao']);
    return strtotime($b['data_criacao']) - strtotime($a['data_criacao']);
});

// Processar matrícula manual
if (isset($_POST['matricular_id'])) {
    $curso_id = intval($_POST['matricular_id']);
    $usuario_id = $_SESSION['usuario_id'];
    $stmt = $pdo->prepare('INSERT IGNORE INTO matriculas (id_usuario, id_curso) VALUES (?, ?)');
    $stmt->execute([$usuario_id, $curso_id]);
    header('Location: curso.php?id=' . $curso_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Cursos - Plataforma Educacional</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
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
        
        .greeting {
            font-size: 16px;
            color: #6b7280;
            margin-bottom: 8px;
        }
        
        .main-title {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .title-light {
            font-weight: 400;
            color: #6b7280;
        }
        
        .search-container {
            margin-bottom: 24px;
        }
        
        .search-bar {
            position: relative;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .search-input {
            width: 100%;
            padding: 16px 16px 16px 48px;
            border: none;
            border-radius: 12px;
            background: #fff;
            font-size: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            outline: none;
        }
        
        .search-input::placeholder {
            color: #9ca3af;
        }
        
        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 18px;
        }
        
        .categories {
            display: flex;
            gap: 12px;
            margin-bottom: 32px;
            overflow-x: auto;
            padding-bottom: 8px;
        }
        
        .category-btn {
            padding: 8px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            background: #fff;
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .category-btn.active {
            background: #0ea5e9;
            color: #fff;
            border-color: #0ea5e9;
        }
        
        .category-btn:hover {
            background: #f3f4f6;
        }
        
        .category-btn.active:hover {
            background: #0284c7;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .see-all {
            color: #0ea5e9;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
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
        }
        
        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        
        .course-image {
            width: 100%;
            height: 160px;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 48px;
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
        }
        
        .course-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }
        
        .course-author {
            font-size: 14px;
            color: #6b7280;
        }
        
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
        
        .nav-btn:hover {
            background: #f3f4f6;
        }
        
        .nav-btn.active:hover {
            background: #0284c7;
        }
        
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #dc2626;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 16px;
            }
            
            .main-title {
                font-size: 24px;
            }
            
            .courses-grid {
                grid-template-columns: 1fr;
            }
            
            .categories {
                gap: 8px;
            }
            
            .category-btn {
                padding: 6px 12px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <button class="logout-btn" onclick="window.location.href='../logout.php'">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
        
        <div class="header">
            <div class="greeting">Olá, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></div>
            <div class="main-title">
                <span class="title-light">Let's Learn</span>
                <i class="fas fa-graduation-cap"></i>
                <span>Something New</span>
            </div>
        </div>
        
        <div class="search-container">
            <div class="search-bar">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search Course" value="<?php echo htmlspecialchars($busca); ?>" onchange="this.form.submit()">
            </div>
        </div>
        
        <div class="categories">
            <button class="category-btn <?php echo !$categoria ? 'active' : ''; ?>" onclick="window.location.href='?busca=<?php echo urlencode($busca); ?>&tipo=<?php echo urlencode($tipo); ?>'">
                Todos
            </button>
            <?php foreach($categorias as $cat): ?>
                <button class="category-btn <?php echo $categoria === $cat ? 'active' : ''; ?>" onclick="window.location.href='?busca=<?php echo urlencode($busca); ?>&tipo=<?php echo urlencode($tipo); ?>&categoria=<?php echo urlencode($cat); ?>'">
                    <?php echo htmlspecialchars($cat); ?>
                </button>
            <?php endforeach; ?>
        </div>
        
        <div class="section-header">
            <div class="section-title">
                <span>Trending Courses</span>
                <i class="fas fa-fire" style="color: #f97316;"></i>
            </div>
            <a href="#" class="see-all">See All</a>
        </div>
        
        <div class="courses-grid">
            <?php foreach (array_slice($cursos_filtrados, 0, 6) as $curso): ?>
                <div class="course-card" onclick="window.location.href='<?php echo in_array($curso['id'], $matriculados) ? 'curso.php?id=' . $curso['id'] : '#'; ?>'">
                    <div class="course-image" style="background-image: url('<?php echo $curso['capa'] ? '../uploads/' . htmlspecialchars($curso['capa']) : 'https://via.placeholder.com/300x160/0ea5e9/ffffff?text=Curso'; ?>'); background-size: cover; background-position: center;">
                        <div class="bookmark-icon">
                            <i class="fas fa-bookmark"></i>
                        </div>
                    </div>
                    <div class="course-content">
                        <div class="course-title"><?php echo htmlspecialchars($curso['nome']); ?></div>
                        <div class="course-author">By: Fellipe Ferini</div>
                        <?php if (!in_array($curso['id'], $matriculados)): ?>
                            <form method="post" style="margin-top: 12px;" onclick="event.stopPropagation();">
                                <input type="hidden" name="matricular_id" value="<?php echo $curso['id']; ?>">
                                <button type="submit" style="width: 100%; background: #0ea5e9; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer;">
                                    Matricular-se
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($cursos_filtrados)): ?>
                <div style="grid-column: 1/-1; text-align: center; color: #6b7280; padding: 40px;">
                    <i class="fas fa-search" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                    <p>Nenhum curso encontrado.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (count($cursos_filtrados) > 6): ?>
        <div class="section-header">
            <div class="section-title">
                <span>Popular Courses</span>
            </div>
            <a href="#" class="see-all">See All</a>
        </div>
        
        <div class="courses-grid">
            <?php foreach (array_slice($cursos_filtrados, 6) as $curso): ?>
                <div class="course-card" onclick="window.location.href='<?php echo in_array($curso['id'], $matriculados) ? 'curso.php?id=' . $curso['id'] : '#'; ?>'">
                    <div class="course-image" style="background-image: url('<?php echo $curso['capa'] ? '../uploads/' . htmlspecialchars($curso['capa']) : 'https://via.placeholder.com/300x160/0ea5e9/ffffff?text=Curso'; ?>'); background-size: cover; background-position: center;">
                        <div class="bookmark-icon">
                            <i class="fas fa-bookmark"></i>
                        </div>
                    </div>
                    <div class="course-content">
                        <div class="course-title"><?php echo htmlspecialchars($curso['nome']); ?></div>
                        <div class="course-author">By: Fellipe Ferini</div>
                        <?php if (!in_array($curso['id'], $matriculados)): ?>
                            <form method="post" style="margin-top: 12px;" onclick="event.stopPropagation();">
                                <input type="hidden" name="matricular_id" value="<?php echo $curso['id']; ?>">
                                <button type="submit" style="width: 100%; background: #0ea5e9; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer;">
                                    Matricular-se
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Menu de Navegação Flutuante -->
    <div class="floating-nav">
        <button class="nav-btn" onclick="window.location.href='meus-cursos.php'">
            <i class="fas fa-home"></i>
        </button>
        <button class="nav-btn" onclick="window.location.href='cursos-matriculados.php'">
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