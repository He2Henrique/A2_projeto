<?php

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");//evitar que o usuario acesse a pagina sem estar logado
    exit;
}

require_once __DIR__.'/../vendor/autoload.php';
use App\Core\TableBuilder;
use App\Core\ProcessData;
use App\DAO\ModalidadesDAO;
use App\DAO\TurmasDAO;
use App\DAO\AulasDAO;

$builder = new TableBuilder;
$data = new ProcessData();


$conn = new TurmasDAO();
$turmas = $conn->TurmasHJ();
$conn = new AulasDAO();
$aulas = $conn->AulasRealizadasHJ();
$conn = new ModalidadesDAO();

$aulas_turmas_ragistradas = array_column($aulas, 'id_turma');



if(!empty($turmas)) {
    
    $matriz = [];
    foreach ($turmas as $turma) {
        
        if (in_array($turma['id'], $aulas_turmas_ragistradas)) {
            // Procura o índice do id_turma correspondente no array $aulas
            $indice = array_search($turma['id'], $aulas_turmas_ragistradas);
            
            if ($indice !== false) {
                $id_aula = $aulas[$indice]['id']; // Obtém o id correspondente
                $button = $builder->CriarButao('visualizar_chamadas.php' , 'Realizada', 'btn btn-info btn-sm');
            }
        } else {
            $button = $builder->CriarButao('chamada.php?id_turma=' . $turma['id'], 'Registrar Chamada', 'btn btn-sm btn-success');
        }
        
        $linha = [$data->getDate('d-m-y'), $turma['dia_sem'], $conn->selectModalidadesbyID($turma['id_modalidade']), $turma['horario'], $button];
        $matriz[] = $linha;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Painel do Professor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .hoje {
        background-color: #e9f7ef !important;
    }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Painel do Professor</h2>
            <a href="logout.php" class="btn btn-outline-danger">Sair</a>
        </div>

        <div class="card p-3 mb-4 shadow-sm">
            <div class="d-flex flex-wrap gap-3">
                <?php if (isset($_SESSION['usuario']['admin']) && $_SESSION['usuario']['admin']): ?>
                <!-- Botões para administradores -->
                <a href="listar_alunos.php" class="btn btn-secondary">👥 Ver Lista de Alunos</a>
                <a href="cadastrar_aluno.php" class="btn btn-success">➕ Cadastrar Novo Aluno</a>
                <a href="relatoriogeral.php" class="btn btn-info">📊 Relatório Geral por Aluno</a>
                <a href="visualizar_chamadas.php" class="btn btn-warning">📑 Visualizar Chamadas</a>
                <a href="listar_modalidades.php" class="btn btn-danger">🎯 Gerenciar Modalidades</a>
                <a href="listar_turmas.php" class="btn btn-primary">📋 Gerenciar Turmas</a>
                <?php else: ?>
                <!-- Botões para usuários não-administradores -->
                <a href="visualizar_chamadas.php" class="btn btn-warning">📑 Visualizar Chamadas</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Aulas de Jiu-Jitsu -->
        <div class="card p-4 shadow-sm mb-4">
            <h5 class="mb-3">Aulas de hoje</h5>
            <div class="table-responsive">
                <?php
                    if (!empty($turmas)){
                        $builder->criar_Header(['Data', 'Dia', 'Modalidade', 'Horário', 'Ações'], "table-dark");
                        $builder->definir_corpo($matriz);
                        $result = $builder->criar_tabela("table table-hover table-bordered");
                        echo $result;
                    } else {
                        echo "<div class='alert alert-warning'>Nenhuma aula programada para hoje.</div>";
                    }
                    
                ?>
            </div>
        </div>
    </div>
</body>

</html>