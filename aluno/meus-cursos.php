<?php
require_once '../includes/auth.php';
require_login('aluno');
require_once '../includes/db.php';

// Buscar todos os cursos
$stmt = $pdo->query('SELECT * FROM cursos ORDER BY data_criacao DESC');
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meus Cursos - Plataforma Educacional</title>
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
        .course-btn { margin-top: auto; background: #2d3e50; color: #fff; border: none; padding: 8px 22px; border-radius: 16px; font-size: 1rem; cursor: pointer; transition: background 0.2s; }
        .course-btn:hover { background: #ffb300; color: #222; }
        @media (max-width: 600px) { h2 { font-size: 1.1rem; } }
    </style>
</head>
<body>
    <div class="container">
        <h2>Todos os Cursos Disponíveis</h2>
        <form method="get" style="margin-bottom:24px; display:flex; flex-wrap:wrap; gap:12px; align-items:center; justify-content:center;">
            <input type="text" name="busca" placeholder="Buscar por nome" value="<?php echo htmlspecialchars($busca); ?>" style="padding:8px 12px; border-radius:8px; border:1px solid #ccc; min-width:180px;">
            <select name="tipo" style="padding:8px 12px; border-radius:8px; border:1px solid #ccc;">
                <option value="">Tipo</option>
                <option value="pdf" <?php if($tipo==='pdf') echo 'selected'; ?>>PDF Único</option>
                <option value="aulas" <?php if($tipo==='aulas') echo 'selected'; ?>>Aulas</option>
            </select>
            <select name="categoria" style="padding:8px 12px; border-radius:8px; border:1px solid #ccc;">
                <option value="">Categoria</option>
                <?php foreach($categorias as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php if($categoria===$cat) echo 'selected'; ?>><?php echo htmlspecialchars($cat); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="ordem" style="padding:8px 12px; border-radius:8px; border:1px solid #ccc;">
                <option value="desc" <?php if($ordem==='desc') echo 'selected'; ?>>Mais recentes</option>
                <option value="asc" <?php if($ordem==='asc') echo 'selected'; ?>>Mais antigos</option>
            </select>
            <button type="submit" class="course-btn" style="padding:8px 22px;">Filtrar</button>
        </form>
        <div class="courses-grid">
            <?php foreach ($cursos_filtrados as $curso): ?>
                <div class="course-card">
                    <div class="course-title"><?php echo htmlspecialchars($curso['nome']); ?></div>
                    <div class="course-meta">
                        <span class="course-type"><?php echo $curso['tipo'] === 'pdf' ? 'PDF Único' : 'Aulas'; ?></span>
                        <span class="course-category"><?php echo htmlspecialchars($curso['categoria']); ?></span>
                    </div>
                    <div>Data de lançamento: <?php echo date('d/m/Y', strtotime($curso['data_criacao'])); ?></div>
                    <a href="curso.php?id=<?php echo $curso['id']; ?>"><button class="course-btn">Acessar</button></a>
                </div>
            <?php endforeach; ?>
            <?php if (empty($cursos_filtrados)): ?>
                <div style="grid-column:1/-1; text-align:center; color:#888;">Nenhum curso encontrado.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 