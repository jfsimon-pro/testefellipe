<?php
// Teste manual de password_verify
$senha = '52002009';
$hash = '$2y$10$Id0byClj3d3HdVfpFZ7DTeK8EPIQHrfA57Fdul3msp1MuP7fhbsIi';

$resultado = password_verify($senha, $hash);

if ($resultado) {
    echo 'Senha OK!';
} else {
    echo 'Senha NÃO confere!';
} 