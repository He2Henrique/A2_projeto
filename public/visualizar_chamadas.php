<?php
session_start();
require_once __DIR__.'/../vendor/autoload.php';
use App\DAO\AulasDAO;

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$aulasDAO = new AulasDAO();

try {
    $chamadas = $aulasDAO->getChamadasPorTurma();
} catch (PDOException $e) {
    $erro = "Erro ao carregar chamadas: " . $e->getMessage();
    $chamadas = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Visualizar Chamadas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table td, .table th {
            vertical-align: middle;
        }
        .badge-presenca {
            font-size: 0.9em;
            padding: 0.5em 0.8em;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Chamadas Realizadas</h2>
            <a href="index.php" class="btn btn-outline-primary">← Voltar para o Painel</a>
        </div>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <?php if (empty($chamadas)): ?>
            <div class="alert alert-info">
                Nenhuma chamada registrada ainda.
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>Turma</th>
                                <th>Presença</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chamadas as $chamada): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($chamada['data'])) ?></td>
                                    <td><?= date('H:i', strtotime($chamada['hora'])) ?></td>
                                    <td><?= htmlspecialchars($chamada['turma_info']) ?></td>
                                    <td>
                                        <?php 
                                            $total_alunos = $chamada['total_alunos'];
                                            $total_presentes = $chamada['total_presentes'];
                                            $percentual = $total_alunos > 0 ? round(($total_presentes / $total_alunos) * 100) : 0;
                                            $classe_badge = $percentual >= 75 ? 'bg-success' : ($percentual >= 50 ? 'bg-warning' : 'bg-danger');
                                        ?>
                                        <span class="badge <?= $classe_badge ?> badge-presenca">
                                            <?= $total_presentes ?>/<?= $total_alunos ?> (<?= $percentual ?>%)
                                        </span>
                                    </td>
                                    <td>
                                        <a href="editar_chamada.php?id_aula=<?= $chamada['id_aula'] ?>&data=<?= $chamada['data'] ?>" 
                                           class="btn btn-sm btn-warning" 
                                           title="Editar chamada">
                                            ✏️ Editar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>