<?php
session_start();
function require_login($tipo = null) {
    if (empty($_SESSION['usuario_id'])) {
        header('Location: /login.php');
        exit;
    }
    if ($tipo && ($_SESSION['usuario_tipo'] ?? null) !== $tipo) {
        header('Location: /login.php');
        exit;
    }
} 