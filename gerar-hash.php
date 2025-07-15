<?php
// Gera e exibe o hash da senha 52002009
$senha = '52002009';
$hash = password_hash($senha, PASSWORD_DEFAULT);
echo 'Hash gerado para 52002009:<br><b>' . $hash . '</b>'; 