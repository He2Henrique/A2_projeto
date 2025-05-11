<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__.'/../vendor/autoload.php';
use App\DAO\ModalidadesDAO;

$modalidadeDAO = new ModalidadesDAO();

$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';

try {
    if (!empty($busca)) {
        $modalidades = $modalidadeDAO->searchByName($busca);
    } else {
        $modalidades = $modalidadeDAO->getAll();
    }
} catch (PDOException $e) {
    $erro = "Erro ao buscar modalidades: " . $e->getMessage();
    $modalidades = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lista de Modalidades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Listar Modalidades</h2>
            <div>
                <a href="criar_novamodalidade.php" class="btn btn-success me-2">+ Nova Modalidade</a>
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
                <a href="listar_modalidades.php" class="btn btn-outline-secondary">Limpar</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if (count($modalidades) > 0): ?>
        <div class="table-responsive card p-4 shadow-sm">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nome</th>
                        <th>Faixa Etária</th>
                        <th>Idade Mínima</th>
                        <th>Idade Máxima</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($modalidades as $modalidade): ?>
                    <tr>
                        <td><?= htmlspecialchars($modalidade['nome']) ?></td>
                        <td><?= htmlspecialchars($modalidade['faixa_etaria']) ?></td>
                        <td><?= htmlspecialchars($modalidade['idade_min']) ?> anos</td>
                        <td><?= htmlspecialchars($modalidade['idade_max']) ?> anos</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="editar_modalidade.php?id=<?= $modalidade['id'] ?>" class="btn btn-warning btn-sm" title="Editar modalidade">
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
        <div class="alert alert-info">Nenhuma modalidade encontrada.</div>
        <?php endif; ?>
    </div>
</body>

</html> 