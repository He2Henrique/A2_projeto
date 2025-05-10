<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");//evitar que o usuario acesse a pagina sem estar logado
    exit;
}
require_once __DIR__.'/../vendor/autoload.php';
use App\DAO\AlunoDAO;
use App\DAO\MatriculasDAO;
use App\DAO\TurmasDAO;



$conne = new TurmasDAO();
$consulta = $conne->selectTurmasModalidadesALL(); // Seleciona todas as modalidades
$modalidades = []; // Array para armazenar as modalidades
foreach ($consulta as $modalidade) {
    $modalidades[$modalidade['id_modalidade']] = $modalidade['nome'] . ' - ' . $modalidade['faixa_etaria'];
}

$metodo = ($_SERVER["REQUEST_METHOD"] == "POST");
if ($metodo) {
    $aluno_DAO = new AlunoDAO(); // Cria uma nova instância da classe AlunoDAO
    $matriculas = new MatriculasDAO(); // Cria uma nova instância da classe MatriculasDAO
    $ultimo_registro = $aluno_DAO->insert($_POST);//insere o aluno e retorna o ID do aluno recém-cadastrado
    
    if(isset($_POST['opcoes'])){
        foreach ($_POST['opcoes'] as $opcao) {
            $matriculas->insert([
                'id_aluno' => $ultimo_registro, // ID do aluno recém-cadastrado
                'id_turma' => $opcao]);
        }
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
            <a href="index.php" class="btn btn-outline-primary">← Voltar para o Painel</a>
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
                    <label>Telefone (seu ou do responsavel)</label>
                    <input type="text" name="telefone" class="form-control" required>
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
                    <label>Turmas</label>
                    <div class="border p-3 rounded" id="turmas-container">
                        <!-- As turmas serão carregadas dinamicamente aqui -->
                    </div>
                </div>
            </div>

            <div class=" d-grid">
                <button type="submit" class="btn btn-primary">Cadastrar Aluno</button>
            </div>

            <div class="row mb-3 mt-3">
                <div class="col-md-12">
                    <?php 
                    
                        if(isset($ultimo_registro)) {
                            echo '<div class="alert alert-success">';
                            echo "Aluno cadastrado com sucesso!";
                            echo '</div>';

                        } else if($metodo){
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


<script>
const dataNascimentoInput = document.querySelector('input[name="data_nascimento"]');
const turmasContainer = document.querySelector('#turmas-container');
const modalidades = <?php echo json_encode($consulta, JSON_UNESCAPED_UNICODE); ?>;

console.log(modalidades);
dataNascimentoInput.addEventListener('change', function() {
    const dataNascimento = new Date(this.value);
    const hoje = new Date();
    let idade = hoje.getFullYear() - dataNascimento.getFullYear();
    const mes = hoje.getMonth() - dataNascimento.getMonth();

    if (mes < 0 || (mes === 0 && hoje.getDate() < dataNascimento.getDate())) {
        idade--;
    }

    console.log('Idade calculada:', idade); // Verifica a idade no console

    // Filtra as modalidades com base na idade
    const turmasValidas = modalidades.filter(modalidade => {
        return idade >= modalidade.idade_min && idade <= modalidade.idade_max;
    });

    console.log(turmasValidas);

    // Atualiza o HTML com as turmas válidas
    turmasContainer.innerHTML = ''; // Limpa as turmas existentes
    if (turmasValidas.length === 0) {
        turmasContainer.innerHTML =
            '<div class="alert alert-warning">Nenhuma turma disponível para a idade informada.</div>';
    } else {
        turmasValidas.forEach(turma => {
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'opcoes[]';
            checkbox.value = turma.id;
            checkbox.id = 'turma_' + turma.id;
            checkbox.classList.add('form-check-input');

            const label = document.createElement('label');
            label.htmlFor = 'turma_' + turma.id;
            label.classList.add('form-check-label');
            label.textContent = `${turma.nome} - ${turma.faixa_etaria} - ${turma.horario}`;

            const div = document.createElement('div');
            div.classList.add('form-check', 'mb-2');
            div.appendChild(checkbox);
            div.appendChild(label);

            turmasContainer.appendChild(div);
        });
    }
});
</script>