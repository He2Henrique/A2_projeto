<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");//evitar que o usuario acesse a pagina sem estar logado
    exit;
}
require_once __DIR__.'/../vendor/autoload.php';
use App\DAO\AulasDAO;
use App\DAO\LogDAO;

$aulasDAO = new AulasDAO();
$logDAO = new LogDAO();

$id_aula = $_GET['id_aula'] ?? null;
$data = $_GET['data'] ?? null;

if (!$id_aula || !$data) {
    header("Location: visualizar_chamadas.php");
    exit;
}

try {
    // Busca informações da aula
    $aula = $aulasDAO->getAulaById($id_aula);
    if (!$aula) {
        throw new Exception("Aula não encontrada.");
    }

    // Busca chamadas da turma no dia
    $chamadas = $aulasDAO->getChamadasByAula($id_aula, $data);
    if (empty($chamadas)) {
        throw new Exception("Nenhuma chamada encontrada para esta aula.");
    }

    // Atualiza chamadas
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($_POST['presencas'] as $id_chamada => $presente) {
            $justificativa = $_POST['justificativas'][$id_chamada] ?? null;
            
            // Se a justificativa for muito curta ou vazia, define como null
            if ($justificativa !== null && strlen(trim($justificativa)) <= 4) {
                $justificativa = null;
            }
            
            $resultado = $aulasDAO->atualizarChamada($id_chamada, $presente, $justificativa);
            
            if ($resultado) {
                // Registra o log da atualização da frequência
                $logDAO->registrarLog(
                    $_SESSION['usuario']['id'],
                    'Atualização de frequência',
                    'frequencia',
                    $id_chamada,
                    "Chamada ID: $id_chamada, Presente: " . ($presente ? 'Sim' : 'Não') . 
                    ($justificativa ? ", Justificativa: $justificativa" : "")
                );
            }
        }
        $mensagem = "Chamadas atualizadas com sucesso!";
        // Recarrega as chamadas após a atualização
        $chamadas = $aulasDAO->getChamadasByAula($id_aula, $data);
    }
} catch (Exception $e) {
    $erro = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Chamada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Editar Chamada</h2>
            <a href="visualizar_chamadas.php" class="btn btn-outline-primary">← Voltar</a>
        </div>

        <?php if (isset($erro)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <?php if (isset($mensagem)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($mensagem) ?></div>
        <?php endif; ?>

        <?php if (!isset($erro) && !empty($chamadas)): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        Chamada de <?= date('d/m/Y', strtotime($data)) ?> - 
                        <?= date('H:i', strtotime($aula['hora'])) ?>
                    </h5>
                </div>
                <form method="POST" class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Aluno</th>
                                    <th>Presença</th>
                                    <th>Justificativa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($chamadas as $chamada): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($chamada['nome_completo']) ?></td>
                                        <td>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" 
                                                       name="presencas[<?= $chamada['id_chamada'] ?>]" 
                                                       value="1" 
                                                       <?= $chamada['presente'] ? 'checked' : '' ?> 
                                                       id="presente_<?= $chamada['id_chamada'] ?>">
                                                <label class="form-check-label" for="presente_<?= $chamada['id_chamada'] ?>">Presente</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" 
                                                       name="presencas[<?= $chamada['id_chamada'] ?>]" 
                                                       value="0" 
                                                       <?= !$chamada['presente'] ? 'checked' : '' ?> 
                                                       id="ausente_<?= $chamada['id_chamada'] ?>">
                                                <label class="form-check-label" for="ausente_<?= $chamada['id_chamada'] ?>">Ausente</label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" 
                                                   name="justificativas[<?= $chamada['id_chamada'] ?>]" 
                                                   value="<?= htmlspecialchars($chamada['justificativa'] ?? '') ?>" 
                                                   placeholder="Justificativa (opcional)">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>