<?php
// upload-image.php para TinyMCE
$targetDir = __DIR__ . '/../../uploads/';
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}
if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato de imagem não permitido.']);
        exit;
    }
    $filename = uniqid('img_') . '.' . $ext;
    $targetFile = $targetDir . $filename;
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        // Retorna a URL relativa da imagem
        echo json_encode(['location' => '/uploads/' . $filename]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Falha no upload']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Nenhum arquivo enviado']);
} 