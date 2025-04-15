<?php
require_once '../Core/core_func.php'; // Incluir funções do núcleo
require_once '../Core/config_serv.php'; // Incluir configuração do banco de dados
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$conn = DatabaseManager::getInstance(); // Conexão com o banco de dados

$aula = $conn->select('aulas', ['data' => $_GET['id_aula']]); // Selecionar aulas de hoje
$aula = $aula[0]; // Obter a primeira aula (deve haver apenas uma)

$id_alunos = $conn->select('alunos_aulas', ['id_aulas' => $aula['id_aulas']], 'id_alunos'); // Selecionar alunos da aula
$alunos =  $conn->select('alunos', ['id' => $id_alunos], 'nome_completo, id'); // Selecionar alunos pelo ID

print_r($alunos); // Exibir alunos para depuração
// Processar chamada enviada

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
            <?= isset($aula) ? date('d/m/Y') . " - Turma " . $turmas[$aula['id_modalidade']] . " das ". $aula['horario'] :  'Aula não encontrada' ?>
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