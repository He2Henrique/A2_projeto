<?php
require_once __DIR__.'/../vendor/autoload.php';
use App\Core\DatabaseManager; // Importando a classe DatabaseManager
use App\Core\ProcessData;

//id	nome_completo	nome_soci	data_nas	nome_respon	numero	email	data_matri	
// numero deve conter apenas 11 char apenas os numeros
$data_hoje = processData::getDate('Y-m-d'); // Data atual no formato YYYY-MM-DD
$conne = DatabaseManager::getInstance(); //instanciando a classe DatabaseManager
$consulta = $conne->select('modalidades', [], 'id, nome, faixa_etaria'); // Seleciona todas as modalidades
$modalidades = []; // Array para armazenar as modalidades
foreach ($consulta as $modalidade) {
    $modalidades[$modalidade['id']] = $modalidade['nome'] . ' - ' . $modalidade['faixa_etaria'];
}

$result = null; // Inicializa a variável result como nula
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome_completo'] ?? null;
    $nome_social = $_POST['nome_social'] ?? null;
    $data_nascimento = $_POST['data_nascimento'] ?? null;
    $nome_responsavel = $_POST['nome_responsavel'] ?? null;
    $telefone = $_POST['telefone'] ?? null;
    $email = $_POST['email'] ?? null;
    $data_matricula = $data_hoje; // Data de matrícula é a data atual

    $result = $conne->insert('alunos', [
        'nome_completo' => $nome,
        'nome_soci' => $nome_social,
        'data_nas' => $data_nascimento,
        'nome_respon' => $nome_responsavel,
        'numero' => $telefone,
        'email' => $email,
        'data_matri' => $data_matricula
    ]); // Retorna o ID do último registro inserido

    $ultimo_registro = $conne->lastRecord('alunos', 'id'); // Obtém o ID do último registro inserido
    foreach ($_POST['opcoes'] as $opcao) {
        $conne->insert('alunos_aulas', [
            'id_alunos' => $ultimo_registro['id'], // ID do aluno recém-cadastrado
            'id_aulas' => $opcao // ID da aula selecionada
        ]);
    }

    
    
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastrar Aluno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Cadastro de Aluno</h2>
            <a href="main.php" class="btn btn-outline-primary">← Voltar para o Painel</a>
        </div>

        <?php if (isset($mensagem)): ?>
        <div class="alert alert-info"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="POST" class="card p-4 shadow-sm bg-white rounded">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Nome completo</label>
                    <input type="text" name="nome_completo" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label>Nome social</label>
                    <input type="text" name="nome_social" class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Data de nascimento</label>
                    <input type="date" name="data_nascimento" class="form-control" required>
                </div>
                <div class="col-md-8">
                    <label>Nome do responsável</label>
                    <input type="text" name="nome_responsavel" class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Telefone</label>
                    <input type="text" name="telefone" class="form-control">
                    <script>
                    // validador de telefone
                    const telefoneInput = document.querySelector('input[name="telefone"]');
                    // se o telefoene nao tiver so os numeros, ele vai tirar os caracteres especiais
                    telefoneInput.addEventListener('input', function() {
                        this.value = this.value.replace(/[^0-9]/g, '');
                    });
                    // se o telefone tiver mais de 11 digitos, ele vai tirar os digitos a mais
                    telefoneInput.addEventListener('blur', function() {
                        if (this.value.length > 11) {
                            this.value = this.value.slice(0, 11);
                        }
                    });
                    </script>
                </div>
                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Modalidades</label>
                    <div class="border p-3 rounded">
                        <?php
                        $aulas = $conne->select('aulas', [], 'id_aulas, id_modalidade, dia_sem, horario'); 
                        foreach ($aulas as $aula) {
                            $modalidade_info = isset($modalidades[$aula['id_modalidade']]) ? 
                                            $modalidades[$aula['id_modalidade']] : 
                                            'Modalidade Desconhecida';
                            echo '<div class="form-check mb-2">';
                            echo '<input class="form-check-input" sytle="b"type="checkbox" name="opcoes[]" value="'.$aula['id_aulas'].'" id="aula_'.$aula['id_aulas'].'">';
                            echo '<label class="form-check-label" for="aula_'.$aula['id_aulas'].'">';
                            echo htmlspecialchars($modalidade_info . ' - ' . $aula['dia_sem'] . ' às ' . $aula['horario']);
                            echo '</label>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class=" d-grid">
                <button type="submit" class="btn btn-primary">Cadastrar Aluno</button>
            </div>

            <div class="row mb-3 mt-3">
                <div class="col-md-12">
                    <?php 
                    
                        if($result) {
                            echo '<div class="alert alert-success">';
                            echo "Aluno cadastrado com sucesso!";
                            echo '</div>';

                        } else if ($result === false) {
                            echo '<div class="alert alert-danger">';
                            echo "Aluno não cadastrado!";
                            echo '</div>';
                        }
                    ?></div>


            </div>


        </form>
    </div>
</body>

</html>


<Script>

</Script>