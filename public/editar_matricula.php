<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';
use App\DAO\MatriculasDAO;
use App\DAO\TurmasDAO;
use App\DAO\AlunoDAO;

// Inicializa os DAOs
$matriculasDAO = new MatriculasDAO();
$turmasDAO = new TurmasDAO();
$alunoDAO = new AlunoDAO();

$idAluno = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mensagem = '';
$erro = '';

try {
    // Busca dados do aluno
    $aluno = $alunoDAO->selectAlunoBYID($idAluno);
    if (!$aluno) {
        header("Location: listar_alunos.php");
        exit;
    }

    // Busca matrículas do aluno
    $matriculas = $matriculasDAO->selectMatriculaByAluno($idAluno);
    
    // Busca todas as turmas para o formulário
    $turmas = $turmasDAO->selectTurmasModalidadesALL();

    // Processa o formulário
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update':
                    $idMatricula = (int)$_POST['id_matricula'];
                    $matricula = [
                        'id_turma' => (int)$_POST['id_turma'],
                        'data_matricula' => $_POST['data_matricula'],
                        'status_' => (int)$_POST['status']
                    ];

                    // Verifica se já existe matrícula ativa para esta turma
                    if ($matriculasDAO->verificarMatriculaExistente($idAluno, $matricula['id_turma'], $idMatricula)) {
                        $erro = "Já existe uma matrícula ativa para esta turma.";
                    } else {
                        if ($matriculasDAO->update($idMatricula, $matricula)) {
                            $mensagem = "Matrícula atualizada com sucesso!";
                            $matriculas = $matriculasDAO->selectMatriculaByAluno($idAluno);
                        } else {
                            $erro = "Erro ao atualizar matrícula.";
                        }
                    }
                    break;

                case 'delete':
                    $idMatricula = (int)$_POST['id_matricula'];
                    if ($matriculasDAO->delete($idMatricula)) {
                        $mensagem = "Matrícula removida com sucesso!";
                        $matriculas = $matriculasDAO->selectMatriculaByAluno($idAluno);
                    } else {
                        $erro = "Erro ao remover matrícula.";
                    }
                    break;

                case 'insert':
                    $novaMatricula = [
                        'id_aluno' => $idAluno,
                        'id_turma' => (int)$_POST['id_turma'],
                        'data_matricula' => $_POST['data_matricula'],
                        'status_' => 1
                    ];

                    // Verifica se já existe matrícula ativa para esta turma
                    if ($matriculasDAO->verificarMatriculaExistente($idAluno, $novaMatricula['id_turma'])) {
                        $erro = "Já existe uma matrícula ativa para esta turma.";
                    } else {
                        if ($matriculasDAO->insert($novaMatricula)) {
                            $mensagem = "Nova matrícula cadastrada com sucesso!";
                            $matriculas = $matriculasDAO->selectMatriculaByAluno($idAluno);
                        } else {
                            $erro = "Erro ao cadastrar nova matrícula.";
                        }
                    }
                    break;
            }
        }
    }
} catch (PDOException $e) {
    $erro = "Erro: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Matrículas - <?= htmlspecialchars($aluno['nome_completo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gerenciar Matrículas - <?= htmlspecialchars($aluno['nome_completo']) ?></h2>
            <div>
                <a href="editar_aluno.php?id=<?= $idAluno ?>" class="btn btn-outline-primary">← Voltar para Aluno</a>
                <a href="listar_alunos.php" class="btn btn-outline-secondary">← Lista de Alunos</a>
            </div>
        </div>

        <?php if ($mensagem): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <?php if ($erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <!-- Formulário de Nova Matrícula -->
        <div class="card p-4 shadow-sm mb-4">
            <h4 class="mb-3">Nova Matrícula</h4>
            <form method="POST" class="row g-3">
                <input type="hidden" name="action" value="insert">

                <div class="col-md-6">
                    <label class="form-label">Turma</label>
                    <select name="id_turma" class="form-select" required>
                        <option value="">Selecione uma turma</option>
                        <?php foreach ($turmas as $turma): ?>
                        <option value="<?= $turma['id'] ?>">
                            <?= htmlspecialchars($turma['nome'] . ' - ' . $turma['faixa_etaria'] . ' - ' . $turma['dia_sem'] . ' - ' . $turma['horario']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Data da Matrícula</label>
                    <input type="date" name="data_matricula" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Matricular</button>
                </div>
            </form>
        </div>

        <!-- Lista de Matrículas -->
        <?php if (!empty($matriculas)): ?>
        <div class="card p-4 shadow-sm">
            <h4 class="mb-3">Matrículas Atuais</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Turma</th>
                            <th>Data da Matrícula</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matriculas as $matricula): ?>
                        <tr>
                            <td><?= htmlspecialchars($matricula['nome_modalidade'] . ' - ' . $matricula['faixa_etaria'] . ' - ' . $matricula['dia_sem'] . ' - ' . $matricula['horario']) ?></td>
                            <td><?= date('d/m/Y', strtotime($matricula['data_matricula'])) ?></td>
                            <td>
                                <span class="badge bg-<?= $matricula['status_'] == 1 ? 'success' : 'secondary' ?>">
                                    <?= $matricula['status_'] == 1 ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#editModal<?= $matricula['id'] ?>">
                                        ✏️
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal<?= $matricula['id'] ?>">
                                        🗑️
                                    </button>
                                </div>

                                <!-- Modal de Edição -->
                                <div class="modal fade" id="editModal<?= $matricula['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="id_matricula"
                                                    value="<?= $matricula['id'] ?>">

                                                <div class="modal-header">
                                                    <h5 class="modal-title">Editar Matrícula</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Turma</label>
                                                        <select name="id_turma" class="form-select" required>
                                                            <?php foreach ($turmas as $turma): ?>
                                                            <option value="<?= $turma['id'] ?>"
                                                                <?= $turma['id'] == $matricula['id_turma'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($turma['nome'] . ' - ' . $turma['faixa_etaria'] . ' - ' . $turma['dia_sem'] . ' - ' . $turma['horario']) ?>
                                                            </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Data da Matrícula</label>
                                                        <input type="date" name="data_matricula" class="form-control"
                                                            value="<?= $matricula['data_matricula'] ?>" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label">Status</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="1"
                                                                <?= $matricula['status_'] == 1 ? 'selected' : '' ?>>
                                                                Ativo</option>
                                                            <option value="0"
                                                                <?= $matricula['status_'] == 0 ? 'selected' : '' ?>>
                                                                Inativo</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal de Confirmação de Exclusão -->
                                <div class="modal fade" id="deleteModal<?= $matricula['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id_matricula"
                                                    value="<?= $matricula['id'] ?>">

                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirmar Exclusão</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>

                                                <div class="modal-body">
                                                    <p>Tem certeza que deseja remover esta matrícula?</p>
                                                    <p><strong>Turma:</strong>
                                                        <?= htmlspecialchars($matricula['nome_modalidade'] . ' - ' . $matricula['faixa_etaria'] . ' - ' . $matricula['dia_sem'] . ' - ' . $matricula['horario']) ?>
                                                    </p>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-danger">Confirmar
                                                        Exclusão</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            Nenhuma matrícula encontrada para este aluno.
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>