<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/../vendor/autoload.php';
use App\DAO\FrequenciaDAO;
use App\DAO\MatriculasDAO;
use App\DAO\TurmasDAO;

$idMatricula = $_GET['id_matricula'] ?? null;
if (!$idMatricula) {
    header("Location: relatoriogeral.php");
    exit;
}

// Inicializa os DAOs
$frequenciaDAO = new FrequenciaDAO();
$matriculasDAO = new MatriculasDAO();
$turmasDAO = new TurmasDAO();

try {
    // Busca dados da matrícula
    $matricula = $matriculasDAO->selectMatriculaById($idMatricula);
    if (!$matricula) {
        throw new Exception("Matrícula não encontrada.");
    }

    // Busca informações da turma
    $turma = $turmasDAO->selectTurmaModalidade($matricula['id_turma']);
    if (!$turma) {
        throw new Exception("Turma não encontrada.");
    }

    // Conta faltas
    $faltas = $frequenciaDAO->countFaltas($idMatricula);

    // Busca histórico detalhado de frequência
    $historicoFrequencia = $frequenciaDAO->getHistoricoFrequenciaAluno($idMatricula);

    // Formata informações da turma
    $turmaInfo = sprintf(
        '%s - %s - %s - %s',
        $turma['nome'],
        $turma['faixa_etaria'],
        $turma['dia_sem'],
        $turma['horario']
    );

} catch (Exception $e) {
    $erro = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Relatório Detalhado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .table td,
    .table th {
        vertical-align: middle;
    }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <a href="relatoriogeral.php" class="btn btn-outline-primary">← Voltar para Relatório Geral</a>
        <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Relatório Detalhado</h2>
                <h5 class="text-muted">
                    <?= htmlspecialchars($matricula['nome_aluno']) ?> - 
                    <?= htmlspecialchars($turmaInfo) ?>
                </h5>
            </div>
            <a href="relatoriogeral.php" class="btn btn-outline-primary">← Voltar para Relatório Geral</a>
        </div>

        <div class="card p-4 shadow-sm mb-4">
            <div class="row">
                <div class="col-md-4">
                    <h5>Informações da Matrícula</h5>
                    <p><strong>Data de Matrícula:</strong> <?= date('d/m/Y', strtotime($matricula['data_matricula'])) ?></p>
                    <p>
                        <strong>Status:</strong>
                        <span class="badge bg-<?= $matricula['status_'] == 1 ? 'success' : 'secondary' ?>">
                            <?= $matricula['status_'] == 1 ? 'Ativo' : 'Inativo' ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-4">
                    <h5>Estatísticas</h5>
                    <p>
                        <strong>Total de Faltas:</strong>
                        <span class="badge bg-<?= $faltas >= 3 ? 'danger' : 'warning' ?>">
                            <?= $faltas ?> falta(s)
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="card p-4 shadow-sm">
            <h4 class="mb-4">Histórico de Frequência</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Status</th>
                            <th>Justificativa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historicoFrequencia)): ?>
                        <tr>
                            <td colspan="4" class="text-center">Nenhum registro de frequência encontrado</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($historicoFrequencia as $registro): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($registro['data_aula'])) ?></td>
                                <td><?= date('H:i', strtotime($registro['hora_aula'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $registro['presente'] ? 'success' : 'danger' ?>">
                                        <?= $registro['presente'] ? 'Presente' : 'Ausente' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!$registro['presente'] && $registro['justificativa']): ?>
                                        <small class="text-muted"><?= htmlspecialchars($registro['justificativa']) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">-</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
