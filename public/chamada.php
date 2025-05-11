<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");//evitar que o usuario acesse a pagina sem estar logado
    exit;
}
require_once __DIR__.'/../vendor/autoload.php';

use App\Core\TableBuilder;
use App\Core\ProcessData;
use App\DAO\AulasDAO;
use App\DAO\TurmasDAO;
use App\DAO\MatriculasDAO;
use App\DAO\AlunoDAO;
use App\DAO\LogDAO;

$aulasDAO = new AulasDAO();
$turmasDAO = new TurmasDAO();
$matriculasDAO = new MatriculasDAO();
$alunoDAO = new AlunoDAO();
$logDAO = new LogDAO();
$data = new ProcessData();

$id_turma = $_GET['id_turma'] ?? null;

if (isset($id_turma)) {
    $turma = $turmasDAO->selectTurmaModalidade($id_turma);
    if (!$turma) {
        $erro = "Turma não encontrada.";
    } else {
        $matriculas = $matriculasDAO->selectMatriculasByTurma($id_turma);
        
        // Debug para verificar os dados
        if (empty($matriculas)) {
            $erro = "Nenhuma matrícula encontrada para esta turma.";
        } else {
            // Não precisamos mais buscar os alunos separadamente pois já vêm com as matrículas
            $alunos = array_map(function($matricula) {
                return [
                    'id' => $matricula['id_aluno'],
                    'nome_completo' => $matricula['nome_completo']
                ];
            }, $matriculas);
            
            // Criar um array associativo de id_aluno => id_matricula
            $matriculas_do_aluno = array_column($matriculas, 'id', 'id_aluno');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($id_turma)) {
    try {
        $id_aula = $aulasDAO->registrarAula($id_turma, $_SESSION['usuario']['id']);
        
        // Registra o log da criação da aula
        $logDAO->registrarLog(
            $_SESSION['usuario']['id'],
            'Registro de aula',
            'aulas',
            $id_aula,
            "Turma ID: $id_turma"
        );
        
        foreach ($alunos as $aluno) {
            $presenca = ($_POST['presenca'][$aluno['id']] === 'presente') ? 1 : 0;
            $justificativa = $_POST['justificativa'][$aluno['id']] ?? null;
            
            if ($justificativa !== null && strlen(trim($justificativa)) <= 4) {
                $justificativa = null;
            }
            
            $id_frequencia = $aulasDAO->registrarFrequencia(
                $matriculas_do_aluno[$aluno['id']],
                $id_aula,
                $presenca,
                $justificativa
            );
            
            // Registra o log da frequência
            if ($id_frequencia) {
                $logDAO->registrarLog(
                    $_SESSION['usuario']['id'],
                    'Registro de frequência',
                    'frequencia',
                    $id_frequencia,
                    "Aluno ID: {$aluno['id']}, Aula ID: $id_aula, Presente: " . ($presenca ? 'Sim' : 'Não')
                );
            }
        }
        
        header("Location: visualizar_chamadas.php");
        exit;
    } catch (PDOException $e) {
        $erro = "Erro ao registrar chamada: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Registrar Chamada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .justificativa-field {
            display: none;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Registrar Chamada</h2>
            <a href="index.php" class="btn btn-outline-primary">← Voltar para o Painel</a>
        </div>

        <h4 class="mb-4">
            <?php if (isset($turma)): ?>
                <?= $data->getDate('d-m-y') ?> - 
                <?= htmlspecialchars($turma['nome'] . ' - ' . $turma['faixa_etaria'] . ' - ' . $turma['dia_sem'] . ' - ' . $turma['horario']) ?>
            <?php else: ?>
                Aula não encontrada
            <?php endif; ?>
        </h4>

        <?php if (isset($erro)): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <?php if (isset($mensagem)): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($mensagem) ?></div>
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
                            <select name="presenca[<?= $aluno['id'] ?>]" class="form-select presenca-select" required 
                                    onchange="toggleJustificativa(this, <?= $aluno['id'] ?>)">
                                <option value="presente">Presente</option>
                                <option value="ausente">Ausente</option>
                            </select>
                        </td>
                        <td>
                            <textarea name="justificativa[<?= $aluno['id'] ?>]" 
                                      class="form-control justificativa-field" 
                                      id="justificativa_<?= $aluno['id'] ?>"
                                      rows="1"
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
    <script>
        function toggleJustificativa(selectElement, alunoId) {
            const justificativaField = document.getElementById('justificativa_' + alunoId);
            if (selectElement.value === 'ausente') {
                justificativaField.style.display = 'block';
            } else {
                justificativaField.style.display = 'none';
                justificativaField.value = ''; // Limpa o campo quando presente
            }
        }

        // Inicializa os campos quando a página carrega
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.presenca-select').forEach(select => {
                const alunoId = select.getAttribute('onchange').match(/\d+/)[0];
                toggleJustificativa(select, alunoId);
            });
        });
    </script>
</body>

</html>