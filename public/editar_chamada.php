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
    die("Aula ou data inválida.");
}

// Busca chamadas da turma no dia
$chamadas = $aulasDAO->getChamadasByAula($id_aula, $data);
$alunos = $aulasDAO->getAlunos();

// Indexa alunos
$mapAlunos = array_column($alunos, 'nome_completo', 'id');

// Atualiza chamadas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST['presencas'] as $id_chamada => $presente) {
            $resultado = $aulasDAO->atualizarChamada($id_chamada, $presente);
            
            if ($resultado) {
                // Registra o log da atualização da frequência
                $logDAO->registrarLog(
                    $_SESSION['usuario']['id'],
                    'Atualização de frequência',
                    'frequencia',
                    $id_chamada,
                    "Chamada ID: $id_chamada, Presente: " . ($presente ? 'Sim' : 'Não')
                );
            }
        }
        $mensagem = "Chamadas atualizadas com sucesso!";
    } catch (PDOException $e) {
        $erro = "Erro ao atualizar chamadas: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Editar Chamada da Turma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2>Editar Chamada da Turma</h2>
        <a href="visualizar_chamadas.php" class="btn btn-outline-primary mb-3">← Voltar</a>

        <?php if (isset($mensagem)): ?>
        <div class="alert alert-success"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="POST" class="card p-4 shadow-sm">
            <h4>Data: <?= date('d/m/Y', strtotime($data)) ?></h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Presente</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chamadas as $chamada): ?>
                    <tr>
                        <td><?= $mapAlunos[$chamada['id_aluno']] ?? 'Desconhecido' ?></td>
                        <td>
                            <input type="checkbox" name="presencas[<?= $chamada['id_chamada'] ?>]" value="1"
                                <?= $chamada['presente'] ? 'checked' : '' ?>>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="d-grid mt-3">
                <button type="submit" class="btn btn-success">Salvar Alterações</button>
            </div>
        </form>
    </div>
</body>

</html>