<?php
session_start();
require_once '../Dependence/self/depedencias.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$conn = DatabaseManager::getInstance();

$id_aluno = $_GET['id'] ?? null;

if (!$id_aluno) {
    die("Aluno não encontrado.");
}

$aluno = $conn-> select('alunos', ['id' => $id_aluno])[0];

$chamadas = $conn-> select('chamadas', ['id_aluno' => $id_aluno], 'id_chamada, data, presente');

$notas = $conn-> select('notas', ['id_aluno' => $id_aluno], 'disciplina, nota');

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
            <p><strong>Data de Nascimento:</strong> <?= date('d/m/Y', strtotime($aluno['data_nascimento'])) ?></p>
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