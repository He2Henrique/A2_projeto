<?php
require_once __DIR__.'/../vendor/autoload.php';
use App\Core\DatabaseManager;
use App\Core\Process_data;

$conn = DatabaseManager::getInstance();
$prossdata = new Process_data();
$busca = $_GET['busca'] ?? '';

// Consulta com filtro
if (!empty($busca)) {
    $alunos = $conn->query("SELECT * FROM alunos WHERE nome_completo LIKE ?", ["%$busca%"]);
} else {
    $alunos = $conn->select('alunos', []);
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lista de Alunos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Listar alunos</h2>
            <a href="main.php" class="btn btn-outline-primary">← Voltar para o Painel</a>
        </div>

        <!-- Formulário de busca -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="busca" class="form-control" placeholder="Buscar por nome..."
                    value="<?= htmlspecialchars($busca) ?>">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <?php if (!empty($busca)): ?>
                <a href="listar_alunos.php" class="btn btn-outline-secondary">Limpar</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (count($alunos) > 0): ?>
        <div class="table-responsive card p-4 shadow-sm">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nome completo</th>
                        <th>Nome social</th>
                        <th>Idade</th>
                        <th>Nome do responsável</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alunos as $aluno): ?>
                    <tr>
                        <td><?= htmlspecialchars($aluno['nome_completo']) ?></td>
                        <td><?= $aluno['nome_soci'] ?? 'Não possui' ?></td>
                        <td><?= $process->Idade($aluno['data_nas']) ?></td>
                        <td><?= $aluno['nome_respon'] ?? 'Não possui' ?></td>
                        <td><?= htmlspecialchars($aluno['numero']) ?></td>
                        <td><?= htmlspecialchars($aluno['email']) ?></td>
                        <td>
                            <span class="badge bg-<?= $aluno['status_'] == 1 ? 'success' : 'secondary' ?>">
                                <?= $aluno['status_'] == 1 ? 'Ativo' : 'Desativo' ?>
                            </span>
                        </td>
                        <td>
                            <a href="editar_aluno.php?id=<?= $aluno['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="editar_aluno_aulas.php?id=<?= $aluno['id'] ?>"
                                class="btn btn-info btn-sm">Turmas</a>
                            <a href="relatorio_aluno.php?id=<?= $aluno['id'] ?>"
                                class="btn btn-primary btn-sm">Relatório</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">Nenhum aluno encontrado.</div>
        <?php endif; ?>
    </div>
</body>

</html>