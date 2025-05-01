<?php
session_start();
require_once __DIR__.'/../vendor/autoload.php';
use App\Core\DatabaseManager;
use App\Core\Modalidades;
use App\Core\ProcessData;


// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$conn = DatabaseManager::getInstance();

$id_aluno = $_GET['id'] ?? null;

if (!$id_aluno) {
    die("Aluno não encontrado.");
}

// Busca os dados do aluno
$aluno = $conn->select('alunos', ['id' => $id_aluno])[0];

// Aulas que o aluno já está matriculado
$turmas_atual = $conn->select('matriculas', ['id_aluno' => $id_aluno], 'id_turma');
$turmas_ids = array_column($turmas_atual, 'id_turma');
// array_column retorna um array com os valores de uma coluna específica

// Todas as aulas disponíveis
$turmas = $conn->select('turmas', [], 'id, id_modalidade, dia_sem, horario');

// Informações das modalidades
$modalidades_raw = $conn->select('modalidades', [], 'id, nome, faixa_etaria');
$modalidades = [];
foreach ($modalidades_raw as $mod) {
    $modalidades[$mod['id']] = $mod['nome'] . ' - ' . $mod['faixa_etaria'];
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novas_turmas = $_POST['turmas'] ?? [];

    // Remove todas as turma anteriores
    $conn->delete('matriculas', ['id_aluno' => $id_aluno]);

    // Insere novas turma
    foreach ($novas_turmas as $id_turma) {
        $conn->insert('matriculas', [
            'id_aluno' => $id_aluno,
            'id_turma' => $id_turma,
            'data_matricula' => ProcessData::getDate('y-m-d')
        ]);
    }

    $mensagem = "Turmas atualizadas com sucesso!";
    if ($mensagem) {
        $mensagem = "Aluno registrado nas turmas com sucesso!";
        echo "<script> alert('$mensagem'); window.location.href = 'index.php'; </script>";
    } else {
        $mensagem = "Erro ao relacionar turmas ao aluno";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Turmas do Aluno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2>Editar Turmas: <?= htmlspecialchars($aluno['nome_completo']) ?></h2>
        <a href="listar_alunos.php" class="btn btn-outline-primary mb-3">← Voltar para Lista de Alunos</a>

        <?php if (isset($mensagem)): ?>
        <div class="alert alert-danger"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="POST" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label><strong>Selecione as turma:</strong></label>
                <div class="border rounded p-3 bg-white" style="max-height: 300px; overflow-y: auto;">
                    <?php foreach ($turmas as $aula): 
                        $checked = in_array($aula['id'], $turmas_ids) ? 'checked' : '';
                        $modalidade_info = Modalidades::getModalidade_byid($aula['id_modalidade']) ?? 'Modalidade desconhecida';
                    ?>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="turmas[]" value="<?= $aula['id'] ?>"
                            id="turma_<?= $aula['id'] ?>" <?= $checked ?>>
                        <label class="form-check-label" for="turma_<?= $aula['id'] ?>">
                            <?= htmlspecialchars($modalidade_info . ' - ' . $aula['dia_sem'] . ' às ' . $aula['horario']) ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-success">Salvar Alterações</button>
            </div>
        </form>
    </div>
</body>

</html>