<?php

require_once '../Dependence/self/depedencias.php';

$conn = DatabaseManager::getInstance(); // Create a new instance of the database connection

$alunos = $conn->select('alunos', []); // Select all students from the database
$turmas = $conn->select('alunos_aulas', [], 'id_alunos, id_aulas'); // Select all classes from the database
$aulas = $conn->select('aulas', [], 'id_aulas,id_modalidade'); // Select all classes from the database

$matriz = [];
foreach ($alunos as $aluno){
    $nome_soci = $aluno['nome_soci'] == null ? "Não possui" : $aluno['nome_soci'];
    $nome_respon = $aluno['nome_respon'] == null ? "Não possui" : $aluno['nome_respon'];
    $linha = [$aluno['nome_completo'], $nome_soci, Idade($aluno['data_nas']), $nome_respon, $aluno['numero'], $aluno['email']];
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
            <!-- completando tabela -->
            <td><?php 
                    $matriculadas = "";
                    foreach($turmas as $turma){
                        if($turma['id_alunos'] == $aluno['id']){
                            foreach($aulas as $aula){
                                if($turma['id_aulas'] == $aula['id_aulas']){
                                    $matriculadas = $matriculadas . $MODALIDADES[$aula['id_modalidade']] . ", ";
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
            </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">Nenhum aluno cadastrado ainda.</div>
        <?php endif; ?>
    </div>
</body>

</html>