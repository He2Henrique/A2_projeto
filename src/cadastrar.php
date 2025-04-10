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

function inserir_aluno_no_bd($conne){
    // insert a student into the database
    // insere um aluno no banco de dados
    $cpf_for = formatCPF($_POST['cpf']);

    // comando SQL para inserir os dados do aluno no banco de dados
    $sql = "INSERT INTO alunos (nome, data_nas, cpf, curso) VALUES (:nome, :dataNas, :cpf, :curso)";

    $smt = $conne->prepare($sql);

    $smt->execute([
        ':nome' => $_POST['nome'],
        ':dataNas' => $_POST['data_nasc'],
        ':cpf' => $cpf_for,
        ':curso' => $_POST['curso']
    ]);
}

function inserir_aula_no_bd($conne){
    // insert a class into the database
    // insere uma aula no banco de dados
    $sql = "INSERT INTO aulas (aula, prof, dia_sem, hora_ini, hora_fim) VALUES (:aula, :prof, :dia_sem, :hora_ini, :hora_fim)";

    $smt = $conne->prepare($sql);

    $smt->execute([
        ':aula' => $_POST['aula'],
        ':prof' => $_POST['prof'],
        ':dia_sem' => $_POST['dia_sem'],
        ':hora_ini' => $_POST['hora_ini'],
        ':hora_fim' => $_POST['hora_fim']
    ]);
}

function check_aluno_no_bd($conne, $cpf){
    // check if the student already exists in the database
    // verifica se o aluno já existe no banco de dados
    $sql = "SELECT * FROM alunos WHERE cpf = :cpf";
    $smt = $conne->prepare($sql);
    $smt->execute([':cpf' => $cpf]);
    
    if($smt->rowCount() > 0){
        echo "Student already exists!";
        exit;
    }
}

function edit_aluno(){
    // edit campus of a student in the database
}

switch($_REQUEST['action']){
    case 'cadastro_aluno':
        // insert a student into the database
        // insere um aluno no banco de dados
        // check if the student already exists in the database
        check_aluno_no_bd($conne, $_POST['cpf']);
        
        inserir_aluno_no_bd($conne);
        echo "Student registered successfully!";
        break;
    case 'cadastro_aula':
        // insert a class into the database
        // insere uma aula no banco de dados
        inserir_aula_no_bd($conne);
        echo "Class registered successfully!";
        break;

    case 'test':
        
    default:
        echo "Invalid action";
        break;
}