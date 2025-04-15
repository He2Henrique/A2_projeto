<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}


// Processar chamada enviada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_aulas'])) {
    $id_aula = $_POST['id_aula'];
    foreach ($_POST['presenca'] as $id_aluno => $status) {
        $justificativa = $_POST['justificativa'][$id_aluno] ?? null;
        $stmt = $conn->prepare("INSERT INTO chamada (id_aula, id_aluno, presente, justificativa) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $id_aula, $id_aluno, $status, $justificativa);
        $stmt->execute();
    }
    $mensagem = "Chamada registrada com sucesso!";
}

// Carregar dados da aula
if (isset($_GET['data']) && isset($_GET['turma']) && isset($_GET['modalidade'])) {
    $data = $_GET['data'];
    $turma = $_GET['turma'];
    $modalidade = $_GET['modalidade'];

    $stmt = $conn->prepare("SELECT * FROM aulas WHERE data = ? AND turma = ? AND modalidade = ? LIMIT 1");
    $stmt->bind_param("sss", $data, $turma, $modalidade);
    $stmt->execute();
    $aula = $stmt->get_result()->fetch_assoc();

    if ($aula) {
        $id_aula = $aula['id'];
        $stmt_alunos = $conn->prepare("SELECT * FROM alunos WHERE turma = ? AND modalidade = ? ORDER BY nome_completo ASC");
        $stmt_alunos->bind_param("ss", $turma, $modalidade);
        $stmt_alunos->execute();
        $alunos = $stmt_alunos->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Registrar Chamada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Registrar Chamada</h2>
            <a href="painel_professor.php" class="btn btn-outline-primary">← Voltar para o Painel</a>
        </div>

        <h4 class="mb-4">
            <?= isset($aula) ? date('d/m/Y', strtotime($aula['data'])) . " - Turma " . $aula['turma'] : 'Aula não encontrada' ?>
        </h4>

        <?php if (isset($mensagem)): ?>
        <div class="alert alert-success">✅ <?= $mensagem ?></div>
        <?php endif; ?>

        <?php if (!empty($alunos)): ?>
        <form method="POST" class="card p-4 shadow-sm">
            <input type="hidden" name="id_aula" value="<?= $id_aula ?>">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Aluno</th>
                        <th>Presença</th>
                        <th>Justificativa (se ausente)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alunos as $aluno): ?>
                    <tr>
                        <td><?= $aluno['nome_completo'] ?></td>
                        <td>
                            <select name="presenca[<?= $aluno['id'] ?>]" class="form-select" required>
                                <option value="presente">Presente</option>
                                <option value="ausente">Ausente</option>
                            </select>
                        </td>
                        <td>
                            <textarea name="justificativa[<?= $aluno['id'] ?>]" class="form-control" rows="1"
                                placeholder="Se houver justificativa..."></textarea>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="d-grid">
                <button type="submit" class="btn btn-success">Salvar Chamada</button>
            </div>
        </form>
        <?php elseif (isset($aula)): ?>
        <div class="alert alert-warning">Nenhum aluno encontrado para essa aula.</div>
        <?php else: ?>
        <div class="alert alert-danger">Aula não encontrada. Verifique os parâmetros da URL.</div>
        <?php endif; ?>
    </div>
</body>

</html>