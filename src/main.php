<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");//evitar que o usuario acesse a pagina sem estar logado
    exit;
}
require_once('../Core/config_serv.php'); // Incluindo o arquivo de configuração do banco de dados

$conn = DatabaseManager::getInstance(); // Conexão com o banco de dados

$data_hoje = date('Y-m-d');  // Definição da data de hoje

// Separar aulas por modalidade
// $aulas_jiujitsu = $conn->select('aulas', ['modalidade' => 'jiujitsu'], 'data ASC, horario ASC');
// $aulas_bale = $conn->select('aulas', ['modalidade' => 'bale'], 'data ASC, horario ASC');
// gerar depois as aulas

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
                <a href="gerar_aulas.php" class="btn btn-outline-primary">📅 Gerar Aulas do Semestre</a>
                <!-- Novos Botões -->
                <a href="relatorio_geral_aluno.php" class="btn btn-info">📊 Relatório Geral por Aluno</a>
                <a href="visualizar_chamadas.php" class="btn btn-warning">📑 Visualizar Chamadas</a>
                <a href="exportar_chamada_pdf.php" class="btn btn-danger">📄 Exportar Chamadas (PDF)</a>
                <a href="exportar_chamada_excel.php" class="btn btn-success">📊 Exportar Chamadas (Excel)</a>
            </div>
        </div>

        <!-- Aulas de Jiu-Jitsu -->
        <div class="card p-4 shadow-sm mb-4">
            <h5 class="mb-3">Aulas de Jiu-Jitsu</h5>
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
                    <?php $aula_hoje = ($aula['data'] == $data_hoje); ?>
                    <!-- <tbody>
                        <?php foreach ($aulas_jiujitsu as $aula): ?>
                        <tr class="<?= $aula_hoje ? 'hoje' : '' ?>">
                            <td><?= date('d/m/Y', strtotime($aula['data'])) ?></td>
                            <td><?= $aula['dia_semana'] ?></td>
                            <td><?= $aula['turma'] ?></td>
                            <td><?= substr($aula['horario'], 0, 5) ?></td>
                            <td>
                                <a href="registrar_chamada.php?data=<?= $aula['data'] ?>&turma=<?= urlencode($aula['turma']) ?>&modalidade=jiujitsu"
                                    class="btn btn-sm btn-<?= $aula_hoje ? 'success' : 'primary' ?>">Registrar
                                    Chamada</a>
                                <a href="visualizar_presenca.php?data=<?= $aula['data'] ?>&turma=<?= urlencode($aula['turma']) ?>&modalidade=jiujitsu"
                                    class="btn btn-sm btn-secondary">Presenças</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody> -->
                </table>
            </div>
        </div>

        <!-- Aulas de Balé -->
        <div class="card p-4 shadow-sm">
            <h5 class="mb-3">Aulas de Balé</h5>
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
                    <!-- <tbody>
                        <?php foreach ($aulas_bale as $aula): ?>
                        <?php $aula_hoje = ($aula['data'] == $data_hoje); ?>
                        <tr class="<?= $aula_hoje ? 'hoje' : '' ?>">
                            <td><?= date('d/m/Y', strtotime($aula['data'])) ?></td>
                            <td><?= $aula['dia_semana'] ?></td>
                            <td><?= $aula['turma'] ?></td>
                            <td><?= substr($aula['horario'], 0, 5) ?></td>
                            <td>
                                <a href="registrar_chamada.php?data=<?= $aula['data'] ?>&turma=<?= urlencode($aula['turma']) ?>&modalidade=bale"
                                    class="btn btn-sm btn-<?= $aula_hoje ? 'success' : 'primary' ?>">Registrar
                                    Chamada</a>
                                <a href="visualizar_presenca.php?data=<?= $aula['data'] ?>&turma=<?= urlencode($aula['turma']) ?>&modalidade=bale"
                                    class="btn btn-sm btn-secondary">Presenças</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody> -->
                </table>
            </div>
        </div>

    </div>
</body>

</html>