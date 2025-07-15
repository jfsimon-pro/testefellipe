<?php
require_once '../../includes/auth.php';
require_login('admin');
require_once '../../includes/db.php';

$mensagem = '';

// Cadastro do curso
if (isset($_POST['criar_curso'])) {
    $nome = trim($_POST['nome'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    $capa_nome = null;
    // Upload da capa
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
        $stmt = $pdo->prepare('INSERT INTO cursos (nome, tipo, categoria, capa) VALUES (?, ?, ?, ?)');
        $stmt->execute([$nome, $tipo, $categoria, $capa_nome]);
        $curso_id = $pdo->lastInsertId();
        if ($tipo === 'pdf' && isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
            $pdf_nome = uniqid('pdf_') . '_' . basename($_FILES['pdf']['name']);
            move_uploaded_file($_FILES['pdf']['tmp_name'], '../../uploads/' . $pdf_nome);
            $stmt = $pdo->prepare('INSERT INTO curso_pdf (id_curso, arquivo_pdf) VALUES (?, ?)');
            $stmt->execute([$curso_id, $pdf_nome]);
        }
        $mensagem = 'Curso criado com sucesso!';
    } else if (!$mensagem) {
        $mensagem = 'Preencha todos os campos obrigatórios.';
    }
}
// Cadastro de aula (apenas se curso já criado e tipo aulas)
if (isset($_POST['adicionar_aula']) && isset($_POST['curso_id'])) {
    $curso_id = intval($_POST['curso_id']);
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
// Buscar cursos criados recentemente para adicionar aulas
$cursos_aulas = $pdo->query("SELECT * FROM cursos WHERE tipo = 'aulas' ORDER BY data_criacao DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Curso</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: #f7f8fa; font-family: 'Montserrat', Arial, sans-serif; }
        .container { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 12px #0001; padding: 32px 24px; }
        h2 { color: #2d3e50; text-align: center; margin-bottom: 32px; }
        form { margin-bottom: 32px; }
        label { display: block; margin-top: 14px; color: #2d3e50; font-weight: 500; }
        input, select, textarea { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ccc; margin-top: 6px; }
        .btn { background: #2d3e50; color: #fff; border: none; padding: 10px 28px; border-radius: 8px; font-size: 1.1rem; font-weight: bold; cursor: pointer; margin-top: 18px; transition: background 0.2s; }
        .btn:hover { background: #ffb300; color: #222; }
        .mensagem { color: #2d3e50; text-align: center; margin-bottom: 18px; }
        .section { margin-bottom: 38px; }
        .aulas-list { margin-top: 18px; }
        .aula-item { background: #f9f9fb; border-radius: 10px; padding: 10px 16px; margin-bottom: 10px; }
        .voltar { display: inline-block; margin-top: 18px; background: #888; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Cadastrar Curso</h2>
        <?php if ($mensagem): ?>
            <div class="mensagem"><?php echo htmlspecialchars($mensagem); ?></div>
        <?php endif; ?>
        <div class="section">
            <form method="post" enctype="multipart/form-data">
                <label for="nome">Nome do Curso *</label>
                <input type="text" id="nome" name="nome" required>
                <label for="categoria">Categoria</label>
                <input type="text" id="categoria" name="categoria">
                <label for="tipo">Tipo *</label>
                <select id="tipo" name="tipo" required onchange="document.getElementById('pdf-upload').style.display = this.value==='pdf' ? 'block' : 'none';">
                    <option value="">Selecione</option>
                    <option value="pdf">PDF Único</option>
                    <option value="aulas">Aulas</option>
                </select>
                <label for="capa">Imagem de Capa (JPG ou PNG)</label>
                <input type="file" id="capa" name="capa" accept="image/jpeg,image/png">
                <div id="pdf-upload" style="display:none;">
                    <label for="pdf">Upload do PDF *</label>
                    <input type="file" id="pdf" name="pdf" accept="application/pdf">
                </div>
                <button type="submit" name="criar_curso" class="btn">Criar Curso</button>
            </form>
        </div>
        <div class="section">
            <h3>Adicionar Aulas a Cursos do Tipo "Aulas"</h3>
            <form method="post" enctype="multipart/form-data">
                <label for="curso_id">Curso</label>
                <select id="curso_id" name="curso_id" required>
                    <option value="">Selecione</option>
                    <?php foreach($cursos_aulas as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
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
        <a href="../dashboard.php" class="btn voltar">&larr; Voltar</a>
    </div>
    <script>
        // Mostrar/ocultar upload de PDF conforme tipo
        document.getElementById('tipo').addEventListener('change', function() {
            document.getElementById('pdf-upload').style.display = this.value==='pdf' ? 'block' : 'none';
        });
    </script>
</body>
</html> 