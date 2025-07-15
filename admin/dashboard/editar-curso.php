<?php
require_once '../../includes/auth.php';
require_login('admin');
require_once '../../includes/db.php';

$curso_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$curso_id) die('Curso não encontrado.');

// Buscar dados do curso
$stmt = $pdo->prepare('SELECT * FROM cursos WHERE id = ?');
$stmt->execute([$curso_id]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$curso) die('Curso não encontrado.');

$mensagem = '';

// Atualizar dados do curso
if (isset($_POST['atualizar_curso'])) {
    $nome = trim($_POST['nome'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    $capa_nome = $curso['capa'];
    // Upload nova capa
    if (isset($_FILES['capa']) && $_FILES['capa']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['capa']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png'])) {
            $capa_nome = uniqid('capa_') . '.' . $ext;
            move_uploaded_file($_FILES['capa']['tmp_name'], '../../uploads/' . $capa_nome);
        } else {
            $mensagem = 'A imagem de capa deve ser JPG ou PNG.';
        }
    }
    if ($nome && $tipo && in_array($tipo, ['pdf','aulas'])) {
        $stmt = $pdo->prepare('UPDATE cursos SET nome=?, tipo=?, categoria=?, capa=? WHERE id=?');
        $stmt->execute([$nome, $tipo, $categoria, $capa_nome, $curso_id]);
        $mensagem = 'Curso atualizado!';
        $curso['nome'] = $nome;
        $curso['tipo'] = $tipo;
        $curso['categoria'] = $categoria;
        $curso['capa'] = $capa_nome;
    } else if (!$mensagem) {
        $mensagem = 'Preencha todos os campos obrigatórios.';
    }
}

// Excluir aula
if (isset($_POST['excluir_aula'])) {
    $aula_id = intval($_POST['excluir_aula']);
    $stmt = $pdo->prepare('DELETE FROM aulas WHERE id = ? AND id_curso = ?');
    $stmt->execute([$aula_id, $curso_id]);
}

// Adicionar aula
if (isset($_POST['adicionar_aula'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $video_url = trim($_POST['video_url'] ?? '');
    $texto = trim($_POST['texto'] ?? '');
    $ordem = intval($_POST['ordem'] ?? 1);
    $pdf_nome = null;
    if (isset($_FILES['aula_pdf']) && $_FILES['aula_pdf']['error'] === UPLOAD_ERR_OK) {
        $pdf_nome = uniqid('aula_') . '_' . basename($_FILES['aula_pdf']['name']);
        move_uploaded_file($_FILES['aula_pdf']['tmp_name'], '../../uploads/' . $pdf_nome);
    }
    if ($titulo) {
        $stmt = $pdo->prepare('INSERT INTO aulas (id_curso, titulo, video_url, texto, arquivo_pdf, ordem) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$curso_id, $titulo, $video_url, $texto, $pdf_nome, $ordem]);
        $mensagem = 'Aula adicionada!';
    } else {
        $mensagem = 'Título da aula é obrigatório.';
    }
}

// Buscar aulas do curso
$aulas = [];
if ($curso['tipo'] === 'aulas') {
    $stmt = $pdo->prepare('SELECT * FROM aulas WHERE id_curso = ? ORDER BY ordem, id');
    $stmt->execute([$curso_id]);
    $aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Curso</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/1hispykpkdfak92on83x9343cqag1qfknr1oswvxs2moez9g/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      tinymce.init({
        selector: 'textarea[name="texto"]',
        menubar: false,
        plugins: 'lists link',
        toolbar: 'undo redo | bold italic underline | bullist numlist | link',
        branding: false
      });
    </script>
    <style>
        body { background: #f7f8fa; font-family: 'Montserrat', Arial, sans-serif; }
        .container { max-width: 800px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 12px #0001; padding: 32px 24px; }
        h2 { color: #2d3e50; text-align: center; margin-bottom: 32px; }
        form { margin-bottom: 32px; }
        label { display: block; margin-top: 14px; color: #2d3e50; font-weight: 500; }
        input, select, textarea { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; margin-top: 6px; }
        .btn { background: #2d3e50; color: #fff; border: none; padding: 10px 28px; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; margin-top: 18px; transition: background 0.2s; }
        .btn:hover { background: #ffb300; color: #222; }
        .mensagem { color: #2d3e50; text-align: center; margin-bottom: 18px; }
        .section { margin-bottom: 38px; }
        .aulas-list { margin-top: 18px; }
        .aula-item { background: #f9f9fb; border-radius: 10px; padding: 10px 16px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; }
        .aula-title { font-weight: bold; }
        .aula-btns { display: flex; gap: 8px; }
        .voltar { display: inline-block; margin-top: 18px; background: #888; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Editar Curso</h2>
        <?php if ($mensagem): ?>
            <div class="mensagem"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <div class="section">
            <form method="post" enctype="multipart/form-data">
                <label for="nome">Nome do Curso *</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($curso['nome']); ?>" required>
                <label for="categoria">Categoria</label>
                <input type="text" id="categoria" name="categoria" value="<?php echo htmlspecialchars($curso['categoria']); ?>">
                <label for="tipo">Tipo *</label>
                <select id="tipo" name="tipo" required>
                    <option value="pdf" <?php if($curso['tipo']==='pdf') echo 'selected'; ?>>PDF Único</option>
                    <option value="aulas" <?php if($curso['tipo']==='aulas') echo 'selected'; ?>>Aulas</option>
                </select>
                <label for="capa">Imagem de Capa (JPG ou PNG)</label>
                <?php if ($curso['capa']): ?>
                    <img src="../../uploads/<?php echo htmlspecialchars($curso['capa']); ?>" alt="Capa" style="max-width:180px; display:block; margin-bottom:8px; border-radius:8px;">
                <?php endif; ?>
                <input type="file" id="capa" name="capa" accept="image/jpeg,image/png">
                <button type="submit" name="atualizar_curso" class="btn">Salvar Alterações</button>
            </form>
        </div>
        <?php if ($curso['tipo'] === 'aulas'): ?>
        <div class="section">
            <h3>Aulas do Curso</h3>
            <div class="aulas-list">
                <?php foreach ($aulas as $aula): ?>
                    <div class="aula-item">
                        <span class="aula-title"><?php echo htmlspecialchars($aula['titulo']); ?></span>
                        <div class="aula-btns">
                            <!-- Futuro: botão de editar aula -->
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="excluir_aula" value="<?php echo $aula['id']; ?>">
                                <button type="submit" class="btn" style="background:#e74c3c;">Excluir</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($aulas)): ?>
                    <div style="color:#888;">Nenhuma aula cadastrada.</div>
                <?php endif; ?>
            </div>
            <h4 style="margin-top:28px;">Adicionar Nova Aula</h4>
            <form method="post" enctype="multipart/form-data">
                <label for="titulo">Título da Aula *</label>
                <input type="text" id="titulo" name="titulo" required>
                <label for="video_url">URL do Vídeo (YouTube)</label>
                <input type="text" id="video_url" name="video_url">
                <label for="texto">Texto (opcional)</label>
                <textarea id="texto" name="texto" rows="3"></textarea>
                <label for="aula_pdf">PDF da Aula (opcional)</label>
                <input type="file" id="aula_pdf" name="aula_pdf" accept="application/pdf">
                <label for="ordem">Ordem</label>
                <input type="number" id="ordem" name="ordem" value="1" min="1">
                <button type="submit" name="adicionar_aula" class="btn">Adicionar Aula</button>
            </form>
        </div>
        <?php endif; ?>
        <a href="cursos.php" class="btn voltar">&larr; Voltar</a>
    </div>
</body>
</html> 