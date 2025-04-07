<?php

//lembrar de fazer validação de dados para evitar SQL Injection
//conexão com o banco de dados

require_once('../functions/config_serv.php'); //incluindo o arquivo config_serv.php
require_once('../functions/core_func.php'); //incluindo o arquivo core_func.php
//incluing the file config_serv.php
//this function includes the file and chacks if the file already included
//if the file is already included, it will not include it again
$conne = new PDO("mysql:host=".HOST.";dbname=".BASE, USER, PASS);
$conne->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$conne->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

function insert_stdt($conne){
    // insert a student into the database
    // insere um aluno no banco de dados
    $cpf_for = formatCPF($_POST['cpf']);

    $sql = "INSERT INTO alunos (nome, data_nas, cpf, curso) VALUES (:nome, :dataNas, :cpf, :curso)";

    $smt = $conne->prepare($sql);
    $smt->execute([
        ':nome' => $_POST['nome'],
        ':dataNas' => $_POST['data_nasc'],
        ':cpf' => $cpf_for,
        ':curso' => $_POST['curso']
    ]);
}

function edit_stdt(){
    // edit campus of a student in the database
}

switch($_REQUEST['action']){
    case 'cadastro':
        // insert a student into the database
        insert_stdt($conne);
        echo "Student registered successfully!";
        break;
    case 'edit':
        edit_stdt();
        break;

    case 'test':
        
    default:
        echo "Invalid action";
        break;
}