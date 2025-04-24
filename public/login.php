<?php
require_once __DIR__.'/../vendor/autoload.php';
session_start();

use App\Core\DatabaseManager;

$conn = DatabaseManager::getInstance();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

   $resultado = $conn->select('usuarios', ['email' => $email, 'senha' => $senha]);
    // O método select deve retornar um array com os dados do usuário ou false se não existir
   //usando metodos do select para verificar se o usuario existe, va em config_serv.php e veja como funciona o select
    //se existir o usuario, ele retorna um array com os dados do usuario, se não existir retorna false
   if ($resultado) {
        print_r($resultado);
        $_SESSION['usuario'] = $resultado[0]['nome'];
        header("Location: main.php"); // Redireciona para a página principal
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
                        <a href="cadastro.php">Criar conta</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>