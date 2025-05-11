<?php
require_once __DIR__.'/../vendor/autoload.php';
session_start();

use App\DAO\UsuariosDAO;



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $conne = new UsuariosDAO();
    $usuario = $conne->selectUsuariosBYemail($email);
   

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario'] = [
            'id' => $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'admin' => $usuario['admin']
        ];
        header("Location: index.php"); // Redireciona para a página principal
        exit;
    } else {
        $erro = "Email ou senha incorretos.";
    }  
      
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="../Dependence/Bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4 shadow-sm">
                    <h2 class="text-center mb-4">Login</h2>

                    <?php if (isset($erro)): ?>
                    <div class="alert alert-danger"><?= $erro ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Senha</label>
                            <input type="password" name="senha" class="form-control" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Entrar</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <a href="login_cadastro.php">Criar conta</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>