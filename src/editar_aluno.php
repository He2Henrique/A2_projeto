<?php
require_once '../Dependence/self/depedencias.php';// Include the core functions file
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$conn = DatabaseManager::getInstance(); //instaciando

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $aluno = $conn->select('alunos', ['id' => $id]); // Select the student by ID

    $aluno = $aluno[0]; // Get the first result (there should only be one)
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $data = [
            'nome_completo' => $_POST['nome_completo'],
            'nome_soci' => $_POST['nome_social'],
            'data_nas' => $_POST['data_nascimento'],
            'nome_respon' => $_POST['nome_responsavel'],
            'numero' => $_POST['telefone'],
            'email' => $_POST['email'],
        ];
        $conn->update('alunos', $data, ['id' => $id]); // Update the student data
        header("Location: listar_alunos.php");
        exit;
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
            <a href="listar_alunos.php" class="btn btn-outline-primary">← Voltar para o lista</a>
        </div>
        <form method="POST" class="card p-4 shadow-sm">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Nome completo</label>
                    <input type="text" name="nome_completo" class="form-control" value="<?= $aluno['nome_completo'] ?>"
                        required>
                </div>
                <div class="col-md-6">
                    <label>Nome social</label>
                    <input type="text" name="nome_social" class="form-control" value="<?= $aluno['nome_soci'] ?>">
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
                        value="<?= $aluno['nome_respon'] ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Telefone</label>
                    <input type="text" name="telefone" class="form-control" value="<?= $aluno['numero'] ?>">
                </div>
                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= $aluno['email'] ?>">
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-success">Salvar Alterações</button>
                <a href="editar_aluno_aulas.php" class="btn btn-outline-primary">← Voltar para o Painel</a>
            </div>
        </form>
    </div>
</body>

</html>