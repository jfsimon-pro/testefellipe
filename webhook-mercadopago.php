<?php
require_once 'includes/db.php';
require_once 'includes/mp-config.php';
require_once 'includes/email.php';

// Log simples para debug
file_put_contents('webhook.log', date('c') . ' - ' . file_get_contents('php://input') . "\n", FILE_APPEND);

// Mercado Pago envia POST com JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['type']) || $input['type'] !== 'payment') {
    http_response_code(200);
    exit('ok');
}

// Buscar detalhes do pagamento
$id = $input['data']['id'] ?? null;
if (!$id) {
    http_response_code(200);
    exit('ok');
}

$ch = curl_init("https://api.mercadopago.com/v1/payments/$id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . MP_ACCESS_TOKEN
]);
$resp = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($httpcode < 200 || $httpcode >= 300) {
    http_response_code(200);
    exit('ok');
}
$pagamento = json_decode($resp, true);
file_put_contents('webhook.log', "Dados do pagamento: " . print_r($pagamento, true) . "\n", FILE_APPEND);
if (($pagamento['status'] ?? '') !== 'approved') {
    http_response_code(200);
    exit('ok');
}

$email = $pagamento['payer']['email'] ?? '';
$nome = $pagamento['payer']['first_name'] ?? '';
$valor = $pagamento['transaction_amount'] ?? 0;
$mp_id = $pagamento['id'] ?? '';
file_put_contents('webhook.log', "DEBUG preference_id: " .
    'preference_id=' . ($pagamento['preference_id'] ?? 'NULL') . ' | ' .
    'metadata=' . (($pagamento['metadata']['preference_id'] ?? 'NULL')) . ' | ' .
    'additional_info=' . (($pagamento['additional_info']['preference_id'] ?? 'NULL')) . "\n", FILE_APPEND);

$preference_id = $pagamento['preference_id']
    ?? ($pagamento['metadata']['preference_id'] ?? null)
    ?? ($pagamento['additional_info']['preference_id'] ?? null);

// Buscar nome e email reais na tabela compras_pendentes
if ($preference_id) {
    $stmt = $pdo->prepare('SELECT nome, email FROM compras_pendentes WHERE preference_id = ? LIMIT 1');
    $stmt->execute([$preference_id]);
    $pendente = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($pendente) {
        $nome = $pendente['nome'];
        $email = $pendente['email'];
    }
}

if (!$email) {
    http_response_code(200);
    exit('ok');
}

// Verifica se usuário já existe
$stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$senha_temporaria = null;
if (!$usuario) {
    // Cria usuário aluno
    $senha_temporaria = bin2hex(random_bytes(4));
    $senha_hash = password_hash($senha_temporaria, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash, tipo) VALUES (?, ?, ?, "aluno")');
    $stmt->execute([$nome ?: $email, $email, $senha_hash]);
    $usuario_id = $pdo->lastInsertId();
    // Salva senha temporária para log/debug (remover depois)
    file_put_contents('webhook.log', "Senha temporária para $email: $senha_temporaria\n", FILE_APPEND);
} else {
    $usuario_id = $usuario['id'];
}

// Registra pagamento
$stmt = $pdo->prepare('INSERT IGNORE INTO pagamentos (id_usuario, status, valor, mercado_pago_id) VALUES (?, ?, ?, ?)');
$stmt->execute([$usuario_id, 'approved', $valor, $mp_id]);

// Matricula o aluno em todos os cursos disponíveis (exemplo)
$stmt = $pdo->query('SELECT id FROM cursos');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $curso) {
    $stmt2 = $pdo->prepare('INSERT IGNORE INTO matriculas (id_usuario, id_curso) VALUES (?, ?)');
    $stmt2->execute([$usuario_id, $curso['id']]);
}

// Envia e-mail com dados de acesso
if ($senha_temporaria) {
    $body = "<p>Olá, $nome!</p>\n<p>Seu acesso à plataforma foi liberado.</p>\n<p><b>Login:</b> $email<br><b>Senha temporária:</b> $senha_temporaria</p>\n<p>Acesse: <a href='https://" . $_SERVER['HTTP_HOST'] . "/login.php'>https://" . $_SERVER['HTTP_HOST'] . "/login.php</a></p>\n<p>Recomende alterá-la após o primeiro acesso.</p>";
    $ok = sendMail($email, 'Acesso liberado - Plataforma Educacional', $body);
    file_put_contents('webhook.log', "Envio de e-mail para $email: " . ($ok ? 'OK' : 'FALHA') . "\n", FILE_APPEND);
}

http_response_code(200);
echo 'ok'; 