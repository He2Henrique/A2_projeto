<?php

require_once '../Dependence/self/depedencias.php';

$conn = DatabaseManager::getInstance(); // Create a new instance of the database connection

$alunos = $conn->select('alunos', []); // Select all students from the database
$turmas = $conn->selectJoin('alunos_aulas','aulas','alunos_aulas.id_aulas = aulas.id_aulas',[] ); 

$matriz = [];
foreach ($alunos as $aluno){
    $nome_soci = ($aluno['nome_soci'] == null || '') ? "Não possui" : $aluno['nome_soci'];
    $nome_respon = ($aluno['nome_respon'] == null || '')? "Não possui" : $aluno['nome_respon'];
    $matriculadas = "";
    // Ta muito ruim essa parte podemos melhorar isso depois, mas por enquanto ta funcionando.
    foreach($turmas as $turma){
        if($turma['id_alunos'] == $aluno['id']){
            
            $matriculadas = $matriculadas . $MODALIDADES[$turma['id_modalidade']] . $turma['dia_sem'] . $turma['horario'] . "<br>";
                
        }
    }
    $matriculadas == "" ? "Nenhuma turma matriculada" : $matriculadas;

    $status = ($aluno['status_'] == 1 ? 'Ativo' : 'Desativo');
    $span = '<span class="badge bg-' . ($status == 'Ativo' ? 'success' : 'secondary') . '">' . $status . '</span>';
    $btn = CriarButao('editar_aluno.php?id=' . $aluno['id'], 'Editar', 'btn btn-warning btn-sm');


    $linha = [$aluno['nome_completo'], $nome_soci, Idade($aluno['data_nas']), $nome_respon, $aluno['numero'],
    $aluno['email'],$matriculadas, $span, $btn];
    

    $matriz[] = $linha;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lista de Alunos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Listar alunos</h2>
            <a href="main.php" class="btn btn-outline-primary">← Voltar para o Painel</a>
        </div>
        <?php if (count($alunos) > 0): ?>
        <div class="table-responsive card p-4 shadow-sm">
            <?php
                $table = new TableBuilder;
                $table->criar_Header(['Nome completo', 'Nome social', 'Idade', 'Nome do responsavel', 'Telefone', 'Email', 'Turmas', 'Status', 'Editar'], "table-dark");
                $table->definir_corpo($matriz,1);
                $result = $table->criar_tabela("table table-bordered table-striped table-hover");
                echo $result;

            ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info">Nenhum aluno cadastrado ainda.</div>
        <?php endif; ?>
    </div>
</body>

</html>