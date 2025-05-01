<?php 
session_start();
require_once __DIR__.'/../vendor/autoload.php';

use App\Core\DatabaseManager;

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$conn = DatabaseManager::getInstance();

$ocorrencia_chamada = $conn->selectJoin(
    'chamada',
    'ocorrencia',
    ['id_ocorrencia' => 'id'],
    ['id_aluno','id_ocorrencia', 'presente', 'data_']
);

$idAluno = $_GET['id'] ?? null;

if (!$idAluno) {
    die("Aluno não encontrado.");
}

// Busca os dados do aluno, incluindo campos utilizados no HTML abaixo
$aluno = $conn->select('alunos', ['id' => $idAluno], 'id, nome_completo, matricula, data_nas as data_nascimento, curso')[0];

// Busca chamadas do aluno
$chamadas = $conn->select('chamadas', ['id_aluno' => $idAluno], 'id_chamada, data, presente');

// Busca notas do aluno
$notas = $conn->select('notas', ['id_aluno' => $idAluno], 'disciplina, nota');

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Relatório Geral do Aluno</title>
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
        <h2>Relatório Geral do Aluno: <?= htmlspecialchars($aluno['nome_completo']) ?></h2>
        <a href="listar_alunos.php" class="btn btn-outline-primary mb-3">← Voltar para Lista de Alunos</a>

        <!-- Dados do Aluno -->
        <div class="card p-4 shadow-sm mb-4">
            <h4>Informações Pessoais</h4>
            <p><strong>Nome:</strong> <?= htmlspecialchars($aluno['nome_completo']) ?></p>
            <p><strong>Matrícula:</strong> <?= htmlspecialchars($aluno['matricula']) ?></p>
            <p><strong>Data de Nascimento:</strong> <?= date('d/m/Y', strtotime($aluno['data_nas'])) ?></p>
            <p><strong>Curso:</strong> <?= htmlspecialchars($aluno['curso']) ?></p>
        </div>

        <!-- Histórico de Chamadas -->
        <div class="card p-4 shadow-sm mb-4">
            <h4>Histórico de Chamadas</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Presença</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chamadas as $chamada): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($chamada['data'])) ?></td>
                        <td><?= $chamada['presente'] ? 'Presente' : 'Faltou' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Notas (Opcional) -->
        <div class="card p-4 shadow-sm">
            <h4>Notas do Aluno</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Disciplina</th>
                        <th>Nota</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notas as $nota): ?>
                    <tr>
                        <td><?= htmlspecialchars($nota['disciplina']) ?></td>
                        <td><?= htmlspecialchars($nota['nota']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>