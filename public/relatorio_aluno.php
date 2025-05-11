<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/../vendor/autoload.php';
use App\DAO\AlunoDAO;
use App\DAO\FrequenciaDAO;
use App\DAO\MatriculasDAO;
use App\DAO\TurmasDAO;

$idAluno = $_GET['id'] ?? null;
if (!$idAluno) {
    die("Aluno não encontrado.");
}

// Inicializa os DAOs
$alunoDAO = new AlunoDAO();
$matriculasDAO = new MatriculasDAO();
$turmasDAO = new TurmasDAO();
$frequenciaDAO = new FrequenciaDAO();

// Busca dados do aluno
$aluno = $alunoDAO->selectAlunoBYID($idAluno);
if (!$aluno) {
    die("Aluno não encontrado.");
}

// Busca matrículas do aluno
$matriculas = $matriculasDAO->selectMatriculasByAluno($idAluno);
$relatorioFaltas = [];

// Processa cada matrícula
foreach ($matriculas as $matricula) {
    $idMatricula = $matricula['id'];
    $idTurma = $matricula['id_turma'];

    // Busca informações da turma
    $turma = $turmasDAO->selectTurmaModalidade($idTurma);
    
    // Processa status da matrícula
    $statusMatricula = $matricula['status_'] == 1 ? 'Ativa' : 'inativo';

    // Conta faltas
    $faltas = $frequenciaDAO->countFaltas($idMatricula);

    // Busca histórico detalhado de frequência
    $historicoFrequencia = $frequenciaDAO->getHistoricoFrequenciaAluno($idMatricula);

    // Atualiza status se necessário
    if ($faltas >= 3 && $statusMatricula !== 'inativo') {
        $matriculasDAO->atulizarStatusMatricula($idMatricula, 0);
        $statusMatricula = 'inativo';
    }

    $relatorioFaltas[] = [
        'nome_aluno' => $aluno['nome_completo'],
        'turma_info' => $turma ? sprintf(
            '%s - %s - %s - %s',
            $turma['nome'],
            $turma['faixa_etaria'],
            $turma['dia_sem'],
            $turma['horario']
        ) : 'Turma não encontrada',
        'faltas' => $faltas,
        'status_matricula' => $statusMatricula,
        'historico' => $historicoFrequencia
    ];
}

// Verifica se todas as matrículas estão inativas
$todasInativas = !empty($relatorioFaltas) && array_reduce($relatorioFaltas, function ($carry, $item) {
    return $carry && $item['status_matricula'] === 'inativo';
}, true);

// Atualiza status do aluno se necessário
if ($todasInativas && $aluno['status_'] !== 'inativo') {
    $alunoDAO->updateStatus($idAluno, 'inativo');
    $aluno['status_'] = 'inativo';
}

// Ordena por número de faltas (decrescente)
usort($relatorioFaltas, fn($a, $b) => $b['faltas'] <=> $a['faltas']);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Relatório de Faltas</title>
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
        <h2>Relatório de Faltas - <?= htmlspecialchars($aluno['nome_completo']) ?></h2>
        <a href="listar_alunos.php" class="btn btn-outline-primary mb-3">← Voltar para Lista de Alunos</a>

        <div class="card p-4 shadow-sm">
            <h4>Frequência por Turma</h4>
            <?php foreach ($relatorioFaltas as $linha): ?>
            <div class="mb-4">
                <h5 class="border-bottom pb-2">
                    <?= htmlspecialchars($linha['turma_info']) ?>
                    <span class="badge bg-<?= $linha['status_matricula'] === 'Ativa' ? 'success' : 'secondary' ?> float-end">
                        <?= ucfirst($linha['status_matricula']) ?>
                    </span>
                </h5>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>Status</th>
                                <th>Justificativa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($linha['historico'])): ?>
                            <tr>
                                <td colspan="4" class="text-center">Nenhum registro de frequência encontrado</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($linha['historico'] as $registro): ?>
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
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2"><strong>Total de Faltas:</strong></td>
                                <td colspan="2">
                                    <span class="badge bg-<?= $linha['faltas'] >= 3 ? 'danger' : 'warning' ?>">
                                        <?= $linha['faltas'] ?> falta(s)
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>

</html>