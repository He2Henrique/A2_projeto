<?php
session_start();
require_once '../Core/DatabaseManager.php';
require_once '../Core/ProcessData.php';
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$conn = DatabaseManager::getInstance();

$id_aula = $_GET['id_aula'] ?? null;
$data = $_GET['data'] ?? null;

if (!$id_aula || !$data) {
    die("Aula ou data inválida.");
}

// Busca chamadas da turma no dia
$chamadas = $conn->select('chamadas', ['id_aula' => $id_aula, 'data' => $data], 'id_chamada, id_aluno, presente');
$alunos = $conn->select('alunos', [], 'id, nome_completo');

// Indexa alunos
$mapAlunos = array_column($alunos, 'nome_completo', 'id');

// Atualiza chamadas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['presencas'] as $id_chamada => $presente) {
        $conn->update('chamadas', ['presente' => $presente], ['id_chamada' => $id_chamada]);
    }
    $mensagem = "Chamadas atualizadas com sucesso!";
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Chamada da Turma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2>Editar Chamada da Turma</h2>
        <a href="visualizar_chamadas.php" class="btn btn-outline-primary mb-3">← Voltar</a>

        <?php if (isset($mensagem)): ?>
        <div class="alert alert-success"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="POST" class="card p-4 shadow-sm">
            <h4>Data: <?= date('d/m/Y', strtotime($data)) ?></h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Presente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chamadas as $chamada): ?>
                    <tr>
                        <td><?= $mapAlunos[$chamada['id_aluno']] ?? 'Desconhecido' ?></td>
                        <td>
                            <input type="checkbox" name="presencas[<?= $chamada['id_chamada'] ?>]" value="1"
                                <?= $chamada['presente'] ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="d-grid mt-3">
                <button type="submit" class="btn btn-success">Salvar Alterações</button>
            </div>
        </form>
    </div>
</body>

</html>