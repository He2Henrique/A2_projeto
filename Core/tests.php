<?php
require_once '../Dependence/self/depedencias.php'; 

$conn = DatabaseManager::getInstance(); // Get the database connection instance
                        

$alunos = $conn->selectJoin('alunos_aulas','alunos','alunos_aulas.id_alunos = alunos.id',[] ); // Select all students from the database
$turmas = $conn->selectJoin('aulas', 'modalidades', 'aulas.id_modalidade = modalidades.id',[]); // Select all classes from the database




print_r($alunos);
print_r($turmas);
?>