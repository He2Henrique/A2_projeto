<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");//evitar que o usuario acesse a pagina sem estar logado
    exit;
}
require_once('../Core/config_serv.php'); // Incluindo o arquivo de configuração do banco de dados
require_once('../Core/core_func.php'); // Incluindo o arquivo de funções do banco de dados


$conn = DatabaseManager::getInstance(); // Conexão com o banco de dados  // Definição da data de hoje


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
                <a href="relatorio_geral_aluno.php" class="btn btn-info">📊 Relatório Geral por Aluno</a>
                <a href="visualizar_chamadas.php" class="btn btn-warning">📑 Visualizar Chamadas</a>
            </div>
        </div>

        <!-- Aulas de Jiu-Jitsu -->
        <div class="card p-4 shadow-sm mb-4">
            <h5 class="mb-3">Aulas de hoje</h5>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Data</th>
                            <th>Dia</th>
                            <th>Turma</th>
                            <th>Horário</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            print_r($diasSemana[$dia_sem]);
                            $aulas_hoje = $conn->select('aulas', ['dia_sem' => $diasSemana[$dia_sem]], 'id_aulas, id_modalidade, dia_sem, horario');
                        ?>
                        <?php foreach($aulas_hoje as $aula): ?>
                        <tr>
                            <td><?= $data_hojeFront ?></td>
                            <td><?= $aula['dia_sem'] ?></td>
                            <td><?= $turmas[$aula['id_modalidade']]?></td>
                            <td><?= $aula['horario']?></td>
                            <td>
                                <a href="chamada.php?id_aula=<?= $aula['id_aulas'] ?>"
                                    class="btn btn-sm btn-success">Registrar
                                    Chamada</a>

                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>