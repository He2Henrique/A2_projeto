<?php
session_start();
require_once __DIR__.'/../vendor/autoload.php';
use App\Core\DatabaseManager;

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$conn = DatabaseManager::getInstance();

$chamadas = $conn->select('chamada', [], 'id_aulas, data, COUNT(*) as total_chamadas');

$aulas = $conn->select('aulas', [], 'id_aulas, id_modalidade, horario');
$turmas = array_column($conn->select('modalidades', [], 'id, nome'), 'nome', 'id');

$mapAula = [];
foreach ($aulas as $aula) {
    $mapAula[$aula['id_aulas']] = [
        'modalidade' => $turmas[$aula['id_modalidade']] ?? 'Desconhecida',
        'horario' => $aula['horario']
    ];
}
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
                        $aula = $mapAula[$chamada['id_aulas']] ?? ['modalidade' => 'Desconhecida', 'horario' => '---'];
                    ?>
                    <tr>
                        <td><?= $aula['modalidade'] ?></td>
                        <td><?= $aula['horario'] ?></td>
                        <td><?= date('d/m/Y', strtotime($chamada['data'])) ?></td>
                        <td>
                        <a href="editar_chamada.php?id_aulas=<?= $chamada['id_aulas'] ?>&data=<?= $chamada['data'] ?>"
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