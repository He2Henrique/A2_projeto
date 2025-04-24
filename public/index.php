<?php

require_once '../Core/DatabaseManager.php';
require_once '../Core/ProcessData.php';
require_once '../Core/TableBuilder.php';
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");//evitar que o usuario acesse a pagina sem estar logado
    exit;
}
require_once __DIR__.'/../vendor/autoload.php';
use App\Core\DatabaseManager;
use App\Core\TableBuilder;
use App\Core\ProcessData;
use App\Core\Modalidades;


$builder = new TableBuilder;
$conn = DatabaseManager::getInstance();

$aulas_hoje = $conn->select('aulas', ['dia_sem' => ProcessData::getDiaSemana()], 'id_aulas, id_modalidade, dia_sem, horario');

$tem = !empty($aulas_hoje);
if($tem) {
    $matriz = [];
    foreach ($aulas_hoje as $aula) {
        $button = $builder->CriarButao('chamada.php?id_aula=' . $aula['id_aulas'], 'Registrar Chamada', 'btn btn-sm btn-success');
        $linha = [ProcessData::getDate('d/m/y'), $aula['dia_sem'], Modalidades::getModalidade_byid($aula['id_modalidade']), $aula['horario'], $button];
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
                <a href="listar_alunos.php" class="btn btn-secondary">👥 Ver Lista de Alunos</a>
                <a href="cadastrar_aluno.php" class="btn btn-success">➕ Cadastrar Novo Aluno</a>
                <a href="relatorio_aluno.php" class="btn btn-info">📊 Relatório Geral por Aluno</a>
                <a href="visualizar_chamadas.php" class="btn btn-warning">📑 Visualizar Chamadas</a>
            </div>
        </div>

        <!-- Aulas de Jiu-Jitsu -->
        <div class="card p-4 shadow-sm mb-4">
            <h5 class="mb-3">Aulas de hoje</h5>
            <div class="table-responsive">
                <?php
                    if ($tem){
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