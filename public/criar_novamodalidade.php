<?php
require_once __DIR__.'/../vendor/autoload.php';
session_start();

use App\DAO\ModalidadesDAO;
use App\DAO\LogDAO;

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario']) || !is_array($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    header('Location: login.php');
    exit;
}

$modalidadeDAO = new ModalidadesDAO();
$logDAO = new LogDAO();

$erro = null;
$sucesso = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nome = trim($_POST['nome']);
        $faixa_etaria = trim($_POST['faixa_etaria']);
        $idade_min = (int)$_POST['idade_min'];
        $idade_max = (int)$_POST['idade_max'];

        // Validações básicas
        if (empty($nome)) {
            throw new Exception("O nome da modalidade é obrigatório.");
        }

        if (strlen($nome) > 50) {
            throw new Exception("O nome da modalidade deve ter no máximo 50 caracteres.");
        }

        if (empty($faixa_etaria)) {
            throw new Exception("A faixa etária é obrigatória.");
        }

        if ($idade_min < 0 || $idade_max < 0) {
            throw new Exception("As idades não podem ser negativas.");
        }

        if ($idade_min > $idade_max) {
            throw new Exception("A idade mínima não pode ser maior que a idade máxima.");
        }

        // Verifica se já existe uma modalidade com o mesmo nome e faixa etária
        if ($modalidadeDAO->verificarExistencia($nome, $faixa_etaria)) {
            throw new Exception("Já existe uma modalidade com este nome e faixa etária.");
        }

        // Insere a modalidade
        $id = $modalidadeDAO->insert($nome, $faixa_etaria, $idade_min, $idade_max);

        // Registra o log
        $logDAO->registrarLog(
            (int)$_SESSION['usuario']['id'],
            'INSERT',
            'modalidades',
            $id,
            "Modalidade criada: {$nome} - {$faixa_etaria} (ID: {$id})"
        );

        $sucesso = "Modalidade cadastrada com sucesso!";
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Nova Modalidade</title>
    <link href="../Dependence/Bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Nova Modalidade</h4>
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
                                <label for="nome" class="form-label">Nome da Modalidade *</label>
                                <input type="text" class="form-control" id="nome" name="nome"
                                    value="<?= isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '' ?>"
                                    required maxlength="50">
                                <div class="invalid-feedback">
                                    Por favor, informe o nome da modalidade.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="faixa_etaria" class="form-label">Faixa Etária *</label>
                                <input type="text" class="form-control" id="faixa_etaria" name="faixa_etaria"
                                    value="<?= isset($_POST['faixa_etaria']) ? htmlspecialchars($_POST['faixa_etaria']) : '' ?>"
                                    required maxlength="50">
                                <div class="invalid-feedback">
                                    Por favor, informe a faixa etária.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="idade_min" class="form-label">Idade Mínima *</label>
                                <input type="number" class="form-control" id="idade_min" name="idade_min"
                                    value="<?= isset($_POST['idade_min']) ? htmlspecialchars($_POST['idade_min']) : '' ?>"
                                    required min="0">
                                <div class="invalid-feedback">
                                    Por favor, informe a idade mínima.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="idade_max" class="form-label">Idade Máxima *</label>
                                <input type="number" class="form-control" id="idade_max" name="idade_max"
                                    value="<?= isset($_POST['idade_max']) ? htmlspecialchars($_POST['idade_max']) : '' ?>"
                                    required min="0">
                                <div class="invalid-feedback">
                                    Por favor, informe a idade máxima.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Cadastrar Modalidade</button>
                                <a href="listar_modalidades.php" class="btn btn-secondary">Voltar</a>
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