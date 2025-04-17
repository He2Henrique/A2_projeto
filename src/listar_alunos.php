<?php

require_once ('../Core/core_func.php'); // Include the database connection file
require_once ('../Core/config_serv.php'); // Include the database connection file

$conn = DatabaseManager::getInstance(); // Create a new instance of the database connection

$alunos = $conn->select('alunos', []); // Select all students from the database
$turmas = $conn->select('alunos_aulas', [], 'id_alunos, id_aulas'); // Select all classes from the database
$aulas = $conn->select('aulas', [], 'id_aulas,id_modalidade'); // Select all classes from the database
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
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nome completo</th>
                        <th>Nome social</th>
                        <th>Idade</th>
                        <th>Nome do responsavel</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>turmas</th>
                        <th>Status</th>
                        <th>Editar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alunos as $aluno): ?>
                    <tr>
                        <td><?= $aluno['nome_completo'] ?></td>
                        <td><?= $aluno['nome_soci'] == null ? $aluno['nome_soci'] : "Não possui" ?></td>
                        <td><?= Idade($aluno['data_nas']) ?></td>
                        <td><?= $aluno['nome_respon'] == null ? $aluno['nome_respon'] : "Não possui" ?></td>
                        <td><?= $aluno['numero'] ?></td>
                        <td><?= $aluno['email'] ?></td>
                        <td><?php 
                            $matriculadas = "";
                            foreach($turmas as $turma){
                                if($turma['id_alunos'] == $aluno['id']){
                                    foreach($aulas as $aula){
                                        if($turma['id_aulas'] == $aula['id_aulas']){
                                            $matriculadas = $matriculadas . $TURMAS[$aula['id_modalidade']] . ", ";
                                        }
                                    }
                                }
                            }
                            echo $matriculadas == "" ? "Nenhuma turma matriculada" : $matriculadas;
                        ?></td>
                        <td>
                            <span class="badge bg-<?= $aluno['status_'] == 1 ? 'success' : 'secondary' ?>">
                                <?= $aluno['status_'] == 1 ? 'Ativo' : 'Desativo' ?>
                            </span>
                        </td>
                        <td>
                            <a href="editar_aluno.php?id=<?= $aluno['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">Nenhum aluno cadastrado ainda.</div>
        <?php endif; ?>
    </div>
</body>

</html>