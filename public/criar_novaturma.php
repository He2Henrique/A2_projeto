<?php
require_once __DIR__.'/../vendor/autoload.php';
session_start();

use App\DAO\TurmasDAO;
use App\DAO\ModalidadesDAO;
use App\DAO\LogDAO;

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario']) || !is_array($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    header('Location: login.php');
    exit;
}

$turmaDAO = new TurmasDAO();
$modalidadeDAO = new ModalidadesDAO();
$logDAO = new LogDAO();

$erro = null;
$sucesso = null;

// Carrega as modalidades para o select
$modalidades = $modalidadeDAO->getAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $modalidadeId = (int)$_POST['modalidade_id'];
        $diaSemana = $_POST['dia_semana'];
        $horario = $_POST['horario'];

        // Validações básicas
        if ($modalidadeId <= 0) {
            throw new Exception("Selecione uma modalidade válida.");
        }

        if (!in_array($diaSemana, ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'])) {
            throw new Exception("Dia da semana inválido.");
        }

        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $horario)) {
            throw new Exception("Horário inválido. Use o formato HH:MM.");
        }

        // Verifica se já existe uma turma com o mesmo horário e dia
        if ($turmaDAO->verificarHorarioExistente($diaSemana, $horario)) {
            throw new Exception("Já existe uma turma cadastrada neste horário e dia.");
        }

        // Insere a turma
        $id = $turmaDAO->insert($modalidadeId, $diaSemana, $horario);

        // Registra o log
        $modalidade = $modalidadeDAO->getById($modalidadeId);
        $logDAO->registrarLog(
            (int)$_SESSION['usuario']['id'],
            'INSERT',
            'turmas',
            $id,
            "Turma criada: {$modalidade['nome']} - {$modalidade['faixa_etaria']} - {$diaSemana} às {$horario} (ID: {$id})"
        );

        $sucesso = "Turma cadastrada com sucesso!";
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Nova Turma</title>
    <link href="../Dependence/Bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Nova Turma</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($erro): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                        <?php endif; ?>

                        <?php if ($sucesso): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="modalidade_id" class="form-label">Modalidade *</label>
                                <select class="form-select" id="modalidade_id" name="modalidade_id" required>
                                    <option value="">Selecione uma modalidade</option>
                                    <?php foreach ($modalidades as $modalidade): ?>
                                    <option value="<?= $modalidade['id'] ?>"
                                        <?= isset($_POST['modalidade_id']) && $_POST['modalidade_id'] == $modalidade['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($modalidade['nome'] . ' - ' . $modalidade['faixa_etaria']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor, selecione uma modalidade.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="dia_semana" class="form-label">Dia da Semana *</label>
                                <select class="form-select" id="dia_semana" name="dia_semana" required>
                                    <option value="">Selecione um dia</option>
                                    <?php
                                    $dias = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                                    foreach ($dias as $dia):
                                    ?>
                                    <option value="<?= $dia ?>"
                                        <?= isset($_POST['dia_semana']) && $_POST['dia_semana'] == $dia ? 'selected' : '' ?>>
                                        <?= $dia ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Por favor, selecione um dia da semana.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="horario" class="form-label">Horário *</label>
                                <input type="time" class="form-control" id="horario" name="horario"
                                    value="<?= isset($_POST['horario']) ? htmlspecialchars($_POST['horario']) : '' ?>"
                                    required>
                                <div class="invalid-feedback">
                                    Por favor, informe o horário.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Cadastrar Turma</button>
                                <a href="listar_turmas.php" class="btn btn-secondary">Voltar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../Dependence/Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
    // Validação do formulário
    (function() {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>

</html>