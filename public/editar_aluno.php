<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");//evitar que o usuario acesse a pagina sem estar logado
    exit;
}
require_once __DIR__.'/../vendor/autoload.php';
use App\DAO\AlunoDAO;
use App\DAO\LogDAO;
use App\Core\AlunoRequest;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $alunoDAO = new AlunoDAO();
    $logDAO = new LogDAO();

    // Se foi solicitado deletar
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
        try {
            // Busca dados do aluno antes de deletar para o log
            $aluno = $alunoDAO->selectAlunoBYID($id);
            
            $alunoDAO->delete($id);
            
            // Registra o log da exclusão
            $logDAO->registrarLog(
                $_SESSION['usuario']['id'],
                'Exclusão de aluno',
                'alunos',
                $id,
                "Aluno excluído: " . $aluno['nome_completo']
            );
            
            header("Location: listar_alunos.php");
            exit;
        } catch (PDOException $e) {
            $erro = "Erro ao deletar aluno: " . $e->getMessage();
        }
    }

    try {
        $aluno = $alunoDAO->selectAlunoBYID($id);
        if (empty($aluno)) {
            die("Aluno não encontrado.");
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete'])) {
            
            $dadosAtualizadosAluno = new AlunoRequest($_POST);
        
            $alunoDAO->update($id, $dadosAtualizadosAluno);
            
            $logDAO->registrarLog(
                $_SESSION['usuario']['id'],
                'Edição de aluno',
                'alunos',
                $id,
                "Aluno: " . $dadosAtualizados['nome_completo']
            );
            
            header("Location: listar_alunos.php");
            exit;
        }
    } catch (PDOException $e) {
        $erro = "Erro ao processar aluno: " . $e->getMessage();
    }
} else {
    die("Aluno não encontrado.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Aluno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Editar alunos</h2>
            <a href="listar_alunos.php" class="btn btn-outline-primary">← Voltar para a lista</a>
        </div>

        <?php if (isset($erro)): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
        <?php endif; ?>

        <form method="POST" class="card p-4 shadow-sm">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Nome completo</label>
                    <input type="text" name="nome_completo" class="form-control"
                        value="<?= htmlspecialchars($aluno['nome_completo']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label>Nome social</label>
                    <input type="text" name="nome_social" class="form-control"
                        value="<?= htmlspecialchars($aluno['nome_soci'] ?? '') ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Data de nascimento</label>
                    <input type="date" name="data_nascimento" class="form-control" value="<?= $aluno['data_nas'] ?>"
                        required>
                </div>
                <div class="col-md-8">
                    <label>Nome do responsável</label>
                    <input type="text" name="nome_responsavel" class="form-control"
                        value="<?= htmlspecialchars($aluno['nome_respon'] ?? '') ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Telefone</label>
                    <input type="text" name="telefone" class="form-control"
                        value="<?= htmlspecialchars($aluno['numero']) ?>" required>
                    <script>
                    // validador de telefone
                    const telefoneInput = document.querySelector('input[name="telefone"]');
                    telefoneInput.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9]/g, '');
                    });
                    telefoneInput.addEventListener('blur', function() {
                        if (this.value.length > 11) {
                            this.value = this.value.slice(0, 11);
                        }
                    });
                    </script>
                </div>
                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control"
                        value="<?= htmlspecialchars($aluno['email'] ?? '') ?>">
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-success">Salvar Alterações</button>
            </div>
        </form>

        <!-- Botão de deletar -->
        <form method="POST" class="mt-3">
            <input type="hidden" name="delete" value="1">
            <button type="submit" class="btn btn-danger w-100"
                onclick="return confirm('Tem certeza que deseja excluir este aluno? Esta ação não pode ser desfeita.')">🗑️
                Excluir Aluno</button>
        </form>
    </div>
</body>

</html>