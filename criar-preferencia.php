<?php
header('Content-Type: application/json');
require_once 'includes/mp-config.php';
require_once 'includes/db.php';

// Recebe dados do POST
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
if (!$nome || !$email) {
    http_response_code(400);
    echo json_encode(['erro' => 'Nome e e-mail obrigatórios.']);
    exit;
}

// Dados da preferência
$preference = [
    'items' => [[
        'title' => 'Acesso à Plataforma Educacional',
        'quantity' => 1,
        'currency_id' => 'BRL',
        'unit_price' => 2.00
    ]],
    'payer' => [
        'name' => $nome,
        'email' => $email
    ],
    'payment_methods' => [
        'excluded_payment_types' => [['id' => 'ticket']],
        'installments' => 1
    ],
    'back_urls' => [
        'success' => 'https://' . $_SERVER['HTTP_HOST'] . '/comprar-acesso.php?sucesso=1',
        'failure' => 'https://' . $_SERVER['HTTP_HOST'] . '/comprar-acesso.php?erro=1',
        'pending' => 'https://' . $_SERVER['HTTP_HOST'] . '/comprar-acesso.php?pendente=1'
    ],
    'auto_return' => 'approved',
    'notification_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/webhook-mercadopago.php'
];

// Chama API do Mercado Pago
$ch = curl_init('https://api.mercadopago.com/checkout/preferences');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . MP_ACCESS_TOKEN
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preference));
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode >= 200 && $httpcode < 300) {
    $data = json_decode($response, true);
    // Salva compra pendente
    if (!empty($data['id'])) {
        require_once 'includes/db.php';
        $stmt = $pdo->prepare('INSERT IGNORE INTO compras_pendentes (preference_id, nome, email) VALUES (?, ?, ?)');
        $stmt->execute([$data['id'], $nome, $email]);
    }
    echo json_encode(['id' => $data['id'] ?? null, 'init_point' => $data['init_point'] ?? null]);
} else {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao criar preferência de pagamento.']);
} 