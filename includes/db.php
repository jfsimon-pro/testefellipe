<?php
// Arquivo de conexão com o banco de dados usando PDO
$DB_HOST = 'localhost';
$DB_NAME = 'u722728962_testefer';
$DB_USER = 'u722728962_testefer'; // Troque pelo seu usuário do banco
$DB_PASS = 'testFer777!';   // Troque pela sua senha do banco

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
} 