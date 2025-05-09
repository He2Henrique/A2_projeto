<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\DatabaseManager;

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$conn = DatabaseManager::getInstance();

$idAluno = $_GET['id'] ?? null;
if (!$idAluno) {
    die("Aluno não encontrado.");
}

$aluno = $conn->select('alunos', ['id' => $idAluno], 'id, nome_completo, status')[0];

$matriculas = $conn->select('matricula', ['id_aluno' => $idAluno]);

$relatorioFaltas = [];

foreach ($matriculas as $matricula) {
    $idMatricula = $matricula['id'];
    $idTurma = $matricula['id_turma'];

    $turma = $conn->select('turmas', ['id' => $idTurma], 'nome')[0];

    $faltas = $conn->count('frequencia', [
        'id_matricula' => $idMatricula,
        'presente' => false
    ]);

    if ($faltas >= 3 && $matricula['status'] !== 'inativo') {
        $conn->update('matricula', ['status' => 'inativo'], ['id' => $idMatricula]);
        $matricula['status'] = 'inativo';
    }

    $relatorioFaltas[] = [
        'nome_aluno' => $aluno['nome_completo'],
        'turma' => $turma['nome'],
        'faltas' => $faltas,
        'status_matricula' => $matricula['status']
    ];
}

$todasInativas = !empty($relatorioFaltas) && array_reduce($relatorioFaltas, function ($carry, $item) {
    return $carry && $item['status_matricula'] === 'inativo';
}, true);

if ($todasInativas && $aluno['status'] !== 'inativo') {
    $conn->update('alunos', ['status' => 'inativo'], ['id' => $idAluno]);
    $aluno['status'] = 'inativo';
}

usort($relatorioFaltas, fn($a, $b) => $b['faltas'] <=> $a['faltas']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Faltas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>.table td, .table th { vertical-align: middle; }</style>
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
