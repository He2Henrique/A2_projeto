<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__.'/../vendor/autoload.php';
use App\DAO\AlunoDAO;
use App\Core\Datatypes\Idade;


$alunoDAO = new AlunoDAO();

$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';

try {
    if (!empty($busca)) {
        $alunos = $alunoDAO->selectAlunosBYnameLIKE($busca);
    } else {
        $alunos = $alunoDAO->selectAlunosALL();
    }
} catch (PDOException $e) {
    $erro = "Erro ao buscar alunos: " . $e->getMessage();
    $alunos = [];
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
            <div>
                <a href="cadastrar_aluno.php" class="btn btn-success me-2">+ Novo Aluno</a>
                <a href="index.php" class="btn btn-outline-primary">← Voltar para o Painel</a>
            </div>
        </div>

        <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

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
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alunos as $aluno):
                        $idade = new Idade($aluno['data_nas']); ?>
                    <tr>
                        <td><?= htmlspecialchars($aluno['nome_completo']) ?></td>
                        <td><?= !empty($aluno['nome_soci']) ? htmlspecialchars($aluno['nome_soci']) : 'Não possui' ?>
                        </td>
                        <td><?= $idade->getIdade() ?></td>
                        <td><?= !empty($aluno['nome_respon']) ? htmlspecialchars($aluno['nome_respon']) : 'Não possui' ?>
                        </td>
                        <td><?= htmlspecialchars($aluno['numero']) ?></td>
                        <td><?= !empty($aluno['email']) ? htmlspecialchars($aluno['email']) : 'Não possui' ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="editar_aluno.php?id=<?= $aluno['id'] ?>" class="btn btn-warning btn-sm"
                                    title="Editar aluno">
                                    ✏️
                                </a>
                                <a href="editar_matricula.php?id=<?= $aluno['id'] ?>" class="btn btn-info btn-sm"
                                    title="Gerenciar turmas">
                                    🎓
                                </a>
                                <a href="relatorio_aluno.php?id=<?= $aluno['id'] ?>" class="btn btn-primary btn-sm"
                                    title="Ver relatório">
                                    📊
                                </a>
                            </div>
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