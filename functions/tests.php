<?php
require_once ('core_func.php'); // Include the database connection file
print_r($_POST);  

echo "\n";
echo preg_replace('/[^0-9]/', '', $_POST['cpf']);
echo "\n";
echo formatCPF($_POST['cpf']);
echo "\n";
echo validarCPF($_POST['cpf']) ? 'CPF Válido' : 'CPF Inválido';
echo "\n";