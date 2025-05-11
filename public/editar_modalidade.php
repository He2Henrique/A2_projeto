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
$modalidade = null;

// Verifica se foi fornecido um ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];

try {
    $modalidade = $modalidadeDAO->getById($id);
    if (!$modalidade) {
        throw new Exception("Modalidade não encontrada.");
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

        // Verifica se já existe outra modalidade com o mesmo nome e faixa etária
        if ($modalidadeDAO->verificarExistencia($nome, $faixa_etaria, $id)) {
            throw new Exception("Já existe uma modalidade com este nome e faixa etária.");
        }

        // Verifica se a modalidade tem turmas ativas antes de permitir a edição
        if ($modalidadeDAO->hasTurmasAtivas($id)) {
            throw new Exception("Não é possível editar esta modalidade pois existem turmas ativas vinculadas a ela.");
        }

        // Atualiza a modalidade
        $modalidadeDAO->update($id, $nome, $faixa_etaria, $idade_min, $idade_max);

        // Registra o log
        $logDAO->registrarLog(
            (int)$_SESSION['usuario']['id'],
            'UPDATE',
            'modalidades',
            $id,
            "Modalidade atualizada: {$nome} - {$faixa_etaria} (ID: {$id})"
        );

        $sucesso = "Modalidade atualizada com sucesso!";
        $modalidade = $modalidadeDAO->getById($id); // Recarrega os dados atualizados
    }
} catch (Exception $e) {
    $erro = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Modalidade</title>
    <link href="../Dependence/Bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Editar Modalidade</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($erro): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                        <?php endif; ?>

                        <?php if ($sucesso): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($sucesso) ?></div>
                        <?php endif; ?>

                        <?php if ($modalidade): ?>
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome da Modalidade *</label>
                                <input type="text" class="form-control" id="nome" name="nome"
                                    value="<?= htmlspecialchars($modalidade['nome']) ?>" required maxlength="50">
                                <div class="invalid-feedback">
                                    Por favor, informe o nome da modalidade.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="faixa_etaria" class="form-label">Faixa Etária *</label>
                                <input type="text" class="form-control" id="faixa_etaria" name="faixa_etaria"
                                    value="<?= htmlspecialchars($modalidade['faixa_etaria']) ?>" required
                                    maxlength="50">
                                <div class="invalid-feedback">
                                    Por favor, informe a faixa etária.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="idade_min" class="form-label">Idade Mínima *</label>
                                <input type="number" class="form-control" id="idade_min" name="idade_min"
                                    value="<?= htmlspecialchars($modalidade['idade_min']) ?>" required min="0">
                                <div class="invalid-feedback">
                                    Por favor, informe a idade mínima.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="idade_max" class="form-label">Idade Máxima *</label>
                                <input type="number" class="form-control" id="idade_max" name="idade_max"
                                    value="<?= htmlspecialchars($modalidade['idade_max']) ?>" required min="0">
                                <div class="invalid-feedback">
                                    Por favor, informe a idade máxima.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Atualizar Modalidade</button>
                                <a href="index.php" class="btn btn-secondary">Voltar</a>
                            </div>
                        </form>
                        <?php endif; ?>
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