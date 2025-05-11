<?php
require_once __DIR__.'/../vendor/autoload.php';
use App\DAO\UsuariosDAO;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    

    $conne = new UsuariosDAO();
    $result = $conne->cadastrando_usuario($_POST);

    if ($result) {
        header("Location: login.php");
        exit;
    } else {
        $erro = "Erro ao cadastrar. E-mail já pode estar em uso.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4 shadow-sm">
                    <h2 class="text-center mb-4">Criar conta</h2>

                    <?php if (isset($erro)): ?>
                    <div class="alert alert-danger"><?= $erro ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label>Nome</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Senha</label>
                            <input type="password" name="senha" class="form-control" required>
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="admin" id="adminSwitch" value="1">
                            <label class="form-check-label" for="adminSwitch">Usuário Administrador</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">Cadastrar</button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="login.php">Voltar ao login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>