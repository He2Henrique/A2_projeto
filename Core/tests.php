<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Painel do Professor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .hoje {
        background-color: #e9f7ef !important;
    }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-5">

        <?php
session_start(); // Start the session to access session variables
require_once ('core_func.php'); // Include the database connection file
require_once ('config_serv.php'); // Include the database connection file
require_once ('classes.php'); // Include the classes file
require_once ('components.php'); // Include the components file

$conn = DatabaseManager::getInstance(); // Get the database connection instance
                        
$table = new TableBuilder;
$table->criar_Header(['Data', 'Dia', 'Turma', 'Horário', 'Ações'],"table-dark");
$table->definir_corpo([['sim', 'sim', 'sim', 'sim', 'sim'], ['data_', 'dia', 'turma', 'horario', 'acoes']]);
$result = $table->criar_tabela("table table-hover table-bordered");



print($result);
?>

    </div>
</body>

</html>