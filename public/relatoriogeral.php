<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/../vendor/autoload.php';
use App\DAO\FrequenciaDAO;
use App\DAO\TurmasDAO;

// Inicializa os DAOs
$frequenciaDAO = new FrequenciaDAO();
$turmasDAO = new TurmasDAO();

// Busca todas as turmas para o filtro
$turmas = $turmasDAO->selectTurmasModalidadesALL();

// Processa o filtro
$idTurma = isset($_GET['turma']) ? (int)$_GET['turma'] : null;
$turmaSelecionada = null;


if ($idTurma) {
    foreach ($turmas as $t) {
        if ($t['id'] == $idTurma) {
            $turmaSelecionada = $t;
            break;
        }
    }
}

try {
    $relatorio = $frequenciaDAO->getRelatorioGeralFaltas($idTurma);
} catch (PDOException $e) {
    $erro = "Erro ao gerar relatório: " . $e->getMessage();
    $relatorio = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Relatório Geral de Faltas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .table td,
    .table th {
        vertical-align: middle;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #000;
    }

    .badge-danger {
        background-color: #dc3545;
        color: #fff;
    }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Relatório Geral de Faltas</h2>
            <a href="index.php" class="btn btn-outline-primary">← Voltar para o Painel</a>
        </div>

        <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <!-- Filtro de Turma -->
        <div class="card p-4 shadow-sm mb-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Filtrar por Turma</label>
                    <select name="turma" class="form-select">
                        <option value="">Todas as Turmas</option>
                        <?php foreach ($turmas as $turma): ?>
                        <option value="<?= $turma['id'] ?>" <?= $idTurma == $turma['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($turma['nome'] . ' - ' . $turma['faixa_etaria'] . ' - ' . $turma['dia_sem'] . ' - ' . $turma['horario']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
                <?php if ($idTurma): ?>
                <div class="col-md-2">
                    <a href="relatoriogeral.php" class="btn btn-outline-secondary w-100">Limpar Filtro</a>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!empty($relatorio)): ?>
        <div class="card p-4 shadow-sm">
            <h4 class="mb-4">
                <?php if ($turmaSelecionada): ?>
                Relatório da Turma:
                <?= htmlspecialchars($turmaSelecionada['nome'] . ' - ' . $turmaSelecionada['faixa_etaria'] . ' - ' . $turmaSelecionada['dia_sem'] . ' - ' . $turmaSelecionada['horario']) ?>
                <?php else: ?>
                Relatório de Todas as Turmas
                <?php endif; ?>
            </h4>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Nome do Aluno</th>
                            <th>Turma</th>
                            <th>Total de Faltas</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($relatorio as $linha): ?>
                        <tr>
                            <td><?= htmlspecialchars($linha['nome_completo']) ?></td>
                            <td><?= htmlspecialchars($linha['turma_info']) ?></td>
                            <td>
                                <span
                                    class="badge <?= $linha['total_faltas'] >= 3 ? 'badge-danger' : 'badge-warning' ?>">
                                    <?= $linha['total_faltas'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $linha['status_matricula'] == 1 ? 'success' : 'secondary' ?>">
                                    <?= $linha['status_matricula'] == 1 ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="relatorio_aluno.php?id=<?= $linha['id_aluno'] ?>"
                                        class="btn btn-primary btn-sm" title="Ver relatório detalhado">
                                        📊
                                    </a>
                                    <a href="editar_matricula.php?id=<?= $linha['id_aluno'] ?>"
                                        class="btn btn-info btn-sm" title="Gerenciar matrícula">
                                        🎓
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <?php if ($idTurma): ?>
            Nenhum aluno encontrado para esta turma.
            <?php else: ?>
            Nenhum aluno encontrado no sistema.
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>

</html>