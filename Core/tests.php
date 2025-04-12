<?php
require_once ('core_func.php'); // Include the database connection file
require_once ('config_serv.php'); // Include the database connection file



$conn = DatabaseManager::getInstance();


$email = 'admin@gmail.com';
$senha = 'admin';

$resultado = $conn->select('usuarios', ['email' => $email, 'senha' => $senha]);
if($resultado) {
    echo "Usuário encontrado!";
} else {
    echo "Usuário não encontrado.";
}

   

    