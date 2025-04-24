<?php
session_start();
require_once('../Core/config_serv.php');
require_once('../Core/core_func.php');

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
$aulas_atual = $conn->select('alunos_aulas', ['id_alunos' => $id_aluno], 'id_aulas');
$aulas_ids = array_column($aulas_atual, 'id_aulas');

// Todas as aulas disponíveis
$aulas = $conn->select('aulas', [], 'id_aulas, id_modalidade, dia_sem, horario');

// Informações das modalidades
$modalidades_raw = $conn->select('modalidades', [], 'id, nome, faixa_etaria');
$modalidades = [];
foreach ($modalidades_raw as $mod) {
    $modalidades[$mod['id']] = $mod['nome'] . ' - ' . $mod['faixa_etaria'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novas_aulas = $_POST['aulas'] ?? [];

    // Remove todas as aulas anteriores
    $conn->delete('alunos_aulas', ['id_alunos' => $id_aluno]);

    // Insere novas aulas
    foreach ($novas_aulas as $id_aula) {
        $conn->insert('alunos_aulas', [
            'id_alunos' => $id_aluno,
            'id_aulas' => $id_aula
        ]);
    }

    $mensagem = "Turmas atualizadas com sucesso!";
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
            <div class="alert alert-success"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="POST" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label><strong>Selecione as aulas:</strong></label>
                <div class="border rounded p-3 bg-white" style="max-height: 300px; overflow-y: auto;">
                    <?php foreach ($aulas as $aula): 
                        $checked = in_array($aula['id_aulas'], $aulas_ids) ? 'checked' : '';
                        $modalidade_info = $modalidades[$aula['id_modalidade']] ?? 'Modalidade desconhecida';
                    ?>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="aulas[]" 
                                   value="<?= $aula['id_aulas'] ?>" id="aula_<?= $aula['id_aulas'] ?>" <?= $checked ?>>
                            <label class="form-check-label" for="aula_<?= $aula['id_aulas'] ?>">
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
