<?php
session_start();
require_once __DIR__.'/../vendor/autoload.php';
use App\Core\DatabaseManager;

// Proteção de acesso
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Conexão com banco
$conn = DatabaseManager::getInstance();

// Obter aulas com chamadas registradas
$chamadas = $conn->select('aulas', [], 'id, id_turma, data_, hora');

// Obter informações auxiliares
$turmas = $conn->select('turmas', [], 'id, id_modalidade, horario');
$modalidades = array_column($conn->select('modalidades', [], 'id, nome'), 'nome', 'id');

// Criar mapa de turmas
$mapTurma = [];
foreach ($turmas as $turma) {
    $mapTurma[$turma['id']] = [
        'modalidade' => $modalidades[$turma['id_modalidade']] ?? 'Desconhecida',
        'horario' => $turma['horario']
    ];
}

// LOG de visualização
$logMsg = "[" . date('Y-m-d H:i:s') . "] " . $_SESSION['usuario'] . " visualizou a lista de chamadas\n";
file_put_contents(__DIR__ . '/logs/log_acoes.txt', $logMsg, FILE_APPEND);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Visualizar Chamadas por Turma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container mt-5">
    <h2>Chamadas por Turma</h2>
    <a href="index.php" class="btn btn-outline-primary mb-3">← Voltar para o Painel</a>

    <div class="card shadow-sm p-4">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
            <tr>
                <th>Turma</th>
                <th>Horário</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($chamadas as $chamada): ?>
                <?php
                $turma = $mapTurma[$chamada['id_turma']] ?? ['modalidade' => 'Desconhecida', 'horario' => '---'];
                ?>
                <tr>
                    <td><?= $turma['modalidade'] ?></td>
                    <td><?= $turma['horario'] ?></td>
                    <td><?= date('d/m/Y', strtotime($chamada['data_'])) ?></td>
                    <td>
                        <a href="editar_chamada.php?id_aulas=<?= $chamada['id'] ?>&data=<?= $chamada['data_'] ?>"
                           class="btn btn-sm btn-warning">Editar Chamada</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
