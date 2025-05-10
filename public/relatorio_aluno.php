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
$matriculas = $matriculasDAO->selectMatriculasFromAluno($idAluno);
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

    // Atualiza status se necessário
    if ($faltas >= 3 && $statusMatricula !== 'inativo') {
        $matriculasDAO->atulizarStatusMatricula($idMatricula, 0);
        $statusMatricula = 'inativo';
    }

    $relatorioFaltas[] = [
        'nome_aluno' => $aluno['nome_completo'],
        'turma' => $turma,
        'faltas' => $faltas,
        'status_matricula' => $statusMatricula
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
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nome do Aluno</th>
                        <th>Turma</th>
                        <th>Faltas</th>
                        <th>Status na Turma</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($relatorioFaltas as $linha): ?>
                    <tr>
                        <td><?= htmlspecialchars($linha['nome_aluno']) ?></td>
                        <td><?= htmlspecialchars($linha['turma']) ?></td>
                        <td><?= $linha['faltas'] ?></td>
                        <td><?= ucfirst($linha['status_matricula']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>