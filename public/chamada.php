<?php
require_once __DIR__.'/../vendor/autoload.php';
use App\Core\DatabaseManager;
use App\Core\TableBuilder;
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$conn = DatabaseManager::getInstance(); // Conexão com o banco de dados

$id_aula = $_GET['id_aula'] ?? null; // Obter o ID da aula da URL

if (isset($id_aula)) {

    $aula = $conn->select('aulas', ['id_aulas' => $id_aula]);
    $aula = $aula[0]; // Obter a primeira aula (deve haver apenas uma)

    $id_alunos = $conn->select('alunos_aulas', ['id_aulas' => $aula['id_aulas']], 'id_alunos');
    foreach($id_alunos as $id){
        $resultado = $conn->select('alunos', ['id' => $id['id_alunos']], 'nome_completo, id');
        $alunos[] = $resultado[0]; // Adiciona o ID do aluno ao array
    }
    
    
} 


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($id_aula)) {

    $campos = ['data_'=> $data_hojebd,'id_aula'=>$id_aula, 'hora'=> $horario];
    $conn->insert('ocorrencia',$campos);

    $ultimo_registro = $conn->lastRecord('ocorrencia', 'id'); // Inserir justificativa na tabela ocorrencia
    foreach ($alunos as $aluno) {
        $presenca = $_POST['presenca'][$aluno['id']];
        if($presenca == 'ausente'){
            $justificativa = $_POST['justificativa'][$aluno['id']] ?? null;
            if($justificativa){
                $presenca = $justificativa;
            }
        }

        $campos = [
            'id_aluno' => $aluno['id'],
            'id_ocorrencia' => $ultimo_registro['id'],
            'presença' => $presenca
        ];

        $result = $conn->insert('chamada', $campos);
        if ($result) {
            $mensagem = "Chamada registrada com sucesso!";
        } else {
            $mensagem = "Erro ao registrar chamada.";
        }
    }
    echo "<script> alert('$mensagem'); window.location.href = 'main.php'; </script>";
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
            <a href="main.php" class="btn btn-outline-primary">← Voltar para o Painel</a>
        </div>

        <h4 class="mb-4">
            <?= isset($aula) ? $data_hojeFront . " - Turma " . $MODALIDADES[$aula['id_modalidade']] . " das ". $aula['horario'] :  'Aula não encontrada' ?>
        </h4>

        <?php if (isset($mensagem)): ?>
        <div class="alert alert-success">✅ <?= $mensagem ?></div>
        <?php endif; ?>

        <?php if (!empty($alunos)): ?>
        <form method="POST" class="card p-4 shadow-sm">
            <input type="hidden" name="id_aula" value="<?= $aula['id_aulas'] ?>">

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
        <?php elseif (isset($aula)): ?>
        <div class="alert alert-warning">Nenhum aluno encontrado para essa aula.</div>
        <?php else: ?>
        <div class="alert alert-danger">Aula não encontrada. Verifique os parâmetros da URL.</div>
        <?php endif; ?>
    </div>
</body>

</html>