<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__.'/../vendor/autoload.php';
use App\DAO\TurmasDAO;
use App\DAO\ModalidadesDAO;

$turmaDAO = new TurmasDAO();
$modalidadeDAO = new ModalidadesDAO();

$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';

try {
    if (!empty($busca)) {
        $turmas = $turmaDAO->searchByName($busca);
    } else {
        $turmas = $turmaDAO->selectTurmasModalidadesALL();
    }
} catch (PDOException $e) {
    $erro = "Erro ao buscar turmas: " . $e->getMessage();
    $turmas = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lista de Turmas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Listar Turmas</h2>
            <div>
                <a href="criar_novaturma.php" class="btn btn-success me-2">+ Nova Turma</a>
                <a href="index.php" class="btn btn-outline-primary">← Voltar para o Painel</a>
            </div>
        </div>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <!-- Formulário de busca -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="busca" class="form-control" placeholder="Buscar por modalidade..."
                    value="<?= htmlspecialchars($busca) ?>">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <?php if (!empty($busca)): ?>
                <a href="listar_turmas.php" class="btn btn-outline-secondary">Limpar</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (count($turmas) > 0): ?>
        <div class="table-responsive card p-4 shadow-sm">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Modalidade</th>
                        <th>Faixa Etária</th>
                        <th>Dia da Semana</th>
                        <th>Horário</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($turmas as $turma): ?>
                    <tr>
                        <td><?= htmlspecialchars($turma['nome']) ?></td>
                        <td><?= htmlspecialchars($turma['faixa_etaria']) ?></td>
                        <td><?= htmlspecialchars($turma['dia_sem']) ?></td>
                        <td><?= htmlspecialchars($turma['horario']) ?></td>
                        <td>
                            <?php if ($turma['status']): ?>
                                <span class="badge bg-success">Ativa</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inativa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="editar_turma.php?id=<?= $turma['id'] ?>" class="btn btn-warning btn-sm" title="Editar turma">
                                    ✏️
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">Nenhuma turma encontrada.</div>
        <?php endif; ?>
    </div>
</body>

</html> 