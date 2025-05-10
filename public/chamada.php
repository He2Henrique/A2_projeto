<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");//evitar que o usuario acesse a pagina sem estar logado
    exit;
}
require_once __DIR__.'/../vendor/autoload.php';
use App\Core\TableBuilder;
use App\Core\ProcessData;
use App\Core\Modalidades;
use App\DAO\AulasDAO;
use App\DAO\TurmasDAO;
use App\DAO\MatriculasDAO;
use App\DAO\AlunoDAO;

$aulasDAO = new AulasDAO();
$turmasDAO = new TurmasDAO();
$matriculasDAO = new MatriculasDAO();
$alunoDAO = new AlunoDAO();

$id_turma = $_GET['id_turma'] ?? null;

if (isset($id_turma)) {
    $turma = $turmasDAO->selectTurmaModalidade($id_turma);
    $matriculas = $matriculasDAO->selectMatriculasFromAluno($id_turma);
    
    foreach($matriculas as $matricula) {
        $aluno = $alunoDAO->selectAlunoBYID($matricula['id_aluno']);
        $alunos[] = $aluno;
        $matriculas_do_aluno[$matricula['id_aluno']] = $matricula['id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($id_turma)) {
    $id_aula = $aulasDAO->registrarAula($id_turma);
    
    foreach ($alunos as $aluno) {
        $presenca = ($_POST['presenca'][$aluno['id']] === 'presente') ? 1 : 0;
        $justificativa = $_POST['justificativa'][$aluno['id']] ?? null;
        
        if ($justificativa !== null && strlen(trim($justificativa)) <= 4) {
            $justificativa = null;
        }

        $frequencia = [
            'id_matricula' => $matriculas_do_aluno[$aluno['id']],
            'id_aula' => $id_aula,
            'presente' => $presenca,
            'justificativa' => $justificativa
        ];

        $result = $aulasDAO->registrarFrequencia($frequencia);
        if ($result) {
            $mensagem = "Chamada registrada com sucesso!";
        } else {
            $mensagem = "Erro ao registrar chamada.";
        }
    }
    echo "<script> alert('$mensagem'); window.location.href = 'index.php'; </script>";
    exit;
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
            <a href="index.php" class="btn btn-outline-primary">← Voltar para o Painel</a>
        </div>

        <h4 class="mb-4">
            <?= isset($turma) ? ProcessData::getDate('d-m-y') . " - Turma " . Modalidades::getModalidade_byid($turma['id_modalidade']) . " das ". $turma['horario'] :  'Aula não encontrada' ?>
        </h4>

        <?php if (isset($mensagem)): ?>
        <div class="alert alert-success">✅ <?= $mensagem ?></div>
        <?php endif; ?>

        <?php if (!empty($alunos)): ?>
        <form method="POST" class="card p-4 shadow-sm">
            <input type="hidden" name="id_turma" value="<?= $turma['id'] ?>">

            <table class="table table-bordered">
                <?php 
                    $table = new TableBuilder;
                    echo $table->criar_Header(['Aluno', 'Presença', 'Justificativa (se ausente)'], "table-dark");
                ?>
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
        <?php elseif (isset($turma)): ?>
        <div class="alert alert-warning">Nenhum aluno encontrado para essa aula.</div>
        <?php else: ?>
        <div class="alert alert-danger">Aula não encontrada. Verifique os parâmetros da URL.</div>
        <?php endif; ?>
    </div>
</body>

</html>