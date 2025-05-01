<?php
require_once __DIR__.'/../vendor/autoload.php';
use App\Core\DatabaseManager;
use App\Core\TableBuilder;
use App\Core\ProcessData;
use App\Core\Modalidades;
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$conn = DatabaseManager::getInstance(); // Conexão com o banco de dados

$id_turma = $_GET['id_turma'] ?? null; // Obter o ID da turma da URL

if (isset($id_turma)) {

    $turmas = $conn->select('turmas', ['id' => $id_turma]);
    $turma = $turmas[0]; // Obter a primeira aula (deve haver apenas uma)



    $matriculas = $conn->select('matriculas', ['id_turma' => $turma['id']], 'id, id_aluno'); // Obter os alunos matriculados na turma
    foreach($matriculas as $matricula){

        $resultado = $conn->select('alunos', ['id' => $matricula['id_aluno']], 'nome_completo, id');
        $alunos[] = $resultado[0]; // Adiciona o ID do aluno ao array
        $matriculas_do_aluno[$matricula['id_aluno']] = $matricula['id']; // Adiciona o ID da matrícula ao array
    }
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($id_turma)) {

    $campos = ['data_'=> ProcessData::getDate('y-m-d'),'id_turma'=>$id_turma, 'hora'=> ProcessData::getHorario()];
    $conn->insert('aulas',$campos);

    $ultimo_registro = $conn->lastRecord('aulas', 'id'); // Inserir justificativa na tabela ocorrencia
    foreach ($alunos as $aluno) {
        
        
        $presenca = ($_POST['presenca'][$aluno['id']] === 'presente') ? 1 : 0; // Converte para booleano (1 ou 0)
        $justificativa = $_POST['justificativa'][$aluno['id']] ?? null;
        
        if ($justificativa !== null && strlen(trim($justificativa)) <= 4) {
            $justificativa = null; // Define como null se tiver 4 ou menos caracteres
        }

        $campos = [
            'id_matricula' => $matriculas_do_aluno[$aluno['id']],
            'id_aula' => $ultimo_registro['id'],
            'presente' => $presenca,
            'justificativa' => $justificativa
        ];

        $result = $conn->insert('frequencia', $campos);
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