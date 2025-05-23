<?php
session_start();
if (!isset($_SESSION['usuario']) || !is_array($_SESSION['usuario']) || !isset($_SESSION['usuario']['id'])) {
    header("Location: login.php");//evitar que o usuario acesse a pagina sem estar logado
    exit;
}
require_once __DIR__.'/../vendor/autoload.php';
use App\DAO\AlunoDAO;
use App\DAO\MatriculasDAO;
use App\DAO\TurmasDAO;
use App\DAO\LogDAO;
use App\Core\AlunoRequest;

$conne = new TurmasDAO();
$consulta = $conne->selectTurmasModalidadesALL(); // Seleciona todas as turmas com suas modalidades
$turmas = []; // Array para armazenar as turmas
foreach ($consulta as $turma) {
    $turmas[] = [
        'id' => $turma['id'],
        'nome' => $turma['nome'],
        'faixa_etaria' => $turma['faixa_etaria'],
        'horario' => $turma['horario'],
        'dia_sem' => $turma['dia_sem'],
        'idade_min' => $turma['idade_min'],
        'idade_max' => $turma['idade_max']
    ];
}

$metodo = ($_SERVER["REQUEST_METHOD"] == "POST");
if ($metodo) {
    $aluno_DAO = new AlunoDAO(); // Cria uma nova instância da classe AlunoDAO
    $matriculas = new MatriculasDAO(); // Cria uma nova instância da classe MatriculasDAO
    $logDAO = new LogDAO();
    
    try {
        $alunoRequest = new AlunoRequest($_POST);
        $ultimo_registro = $aluno_DAO->insert($alunoRequest);//insere o aluno e retorna o ID do aluno recém-cadastrado
        
        if (!is_int($ultimo_registro)) {
            throw new Exception("Erro ao cadastrar aluno: ID inválido retornado.");
        }
        
        // Registra o log do cadastro do aluno
        $detalhes_aluno = sprintf(
            "Nome: %s, Telefone: %s, Email: %s, Data Nascimento: %s",
            $_POST['nome_completo'],
            $_POST['telefone'],
            $_POST['email'] ?? 'Não informado',
            $_POST['data_nascimento']
        );
        
        $logDAO->registrarLog(
            (int)$_SESSION['usuario']['id'],
            'Cadastro de aluno',
            'alunos',
            $ultimo_registro,
            $detalhes_aluno
        );
        
        // Verifica se existem turmas selecionadas
        if(isset($_POST['turmas']) && is_array($_POST['turmas'])) {
            foreach ($_POST['turmas'] as $turma_id) {
                if (!is_numeric($turma_id)) {
                    continue; // Pula turmas inválidas
                }
                
                $id_matricula = $matriculas->insert([
                    'id_aluno' => $ultimo_registro,
                    'id_turma' => (int)$turma_id,
                    'data_matricula' => date('Y-m-d'),
                    'status_' => 1
                ]);
                
                if (!is_int($id_matricula)) {
                    continue; // Pula matrículas que falharam
                }
                
                // Registra o log da matrícula
                $detalhes_matricula = sprintf(
                    "Aluno ID: %d, Nome: %s, Turma ID: %d",
                    $ultimo_registro,
                    $_POST['nome_completo'],
                    $turma_id
                );
                
                $logDAO->registrarLog(
                    (int)$_SESSION['usuario']['id'],
                    'Cadastro de matrícula',
                    'matriculas',
                    $id_matricula,
                    $detalhes_matricula
                );
            }
        }
        
        header("Location: listar_alunos.php");
        exit;
    } catch (PDOException $e) {
        // Verifica se é um erro de chave duplicada
        if ($e->getCode() == 23000 && strpos($e->getMessage(), 'unique_numero_nome') !== false) {
            // Extrai o número e nome do erro
            preg_match("/'([^']+)'/", $e->getMessage(), $matches);
            if (isset($matches[1])) {
                list($numero, $nome) = explode('-', $matches[1]);
                $erro = "Já existe um aluno cadastrado com o número de telefone '$numero' e nome '$nome'. Por favor, verifique os dados e tente novamente.";
            } else {
                $erro = "Já existe um aluno cadastrado com este número de telefone e nome. Por favor, verifique os dados e tente novamente.";
            }
        } else {
            $erro = $e->getMessage();
        }
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }catch (InvalidArgumentException $e) {
        $erro = $e->getMessage();
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

        <?php if (isset($erro)): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST" class="card p-4 shadow-sm bg-white rounded" id="formCadastro">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Nome completo *</label>
                    <input type="text" name="nome_completo" class="form-control" required
                        value="<?= isset($_POST['nome_completo']) ? htmlspecialchars($_POST['nome_completo']) : '' ?>">
                </div>
                <div class="col-md-6">
                    <label>Nome social</label>
                    <input type="text" name="nome_social" class="form-control"
                        value="<?= isset($_POST['nome_social']) ? htmlspecialchars($_POST['nome_social']) : '' ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Data de nascimento *</label>
                    <input type="date" name="data_nascimento" class="form-control" required
                        value="<?= isset($_POST['data_nascimento']) ? htmlspecialchars($_POST['data_nascimento']) : '' ?>"
                        onchange="validarIdade(this)">
                </div>
                <div class="col-md-8">
                    <label>Nome do responsável <span id="responsavelObrigatorio" style="display: none;">*</span></label>
                    <input type="text" name="nome_responsavel" class="form-control" id="nomeResponsavel"
                        value="<?= isset($_POST['nome_responsavel']) ? htmlspecialchars($_POST['nome_responsavel']) : '' ?>">
                    <small class="text-muted" id="responsavelInfo" style="display: none;">Obrigatório para menores de 18
                        anos</small>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Telefone (DDD + número) *</label>
                    <input type="text" name="telefone" class="form-control" required maxlength="11" minlength="11"
                        value="<?= isset($_POST['telefone']) ? htmlspecialchars($_POST['telefone']) : '' ?>"
                        pattern="[0-9]{11}" title="Digite o número com DDD (11 dígitos)">
                    <small class="text-muted">Exemplo: 11999999999 (11 dígitos)</small>
                </div>
                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control"
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Turmas</label>
                    <div class="border p-3 rounded" id="turmas-container">
                        <?php foreach ($turmas as $turma): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="turmas[]" value="<?= $turma['id'] ?>"
                                id="turma_<?= $turma['id'] ?>"
                                <?= isset($_POST['turmas']) && in_array($turma['id'], $_POST['turmas']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="turma_<?= $turma['id'] ?>">
                                <?= htmlspecialchars($turma['nome'] . ' - ' . $turma['faixa_etaria'] . ' (' . $turma['dia_sem'] . ' ' . date('H:i', strtotime($turma['horario'])) . ')') ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Cadastrar Aluno</button>
            </div>
        </form>
    </div>

    <script>
    function validarIdade(input) {
        const dataNascimento = new Date(input.value);
        const hoje = new Date();
        let idade = hoje.getFullYear() - dataNascimento.getFullYear();
        const mes = hoje.getMonth() - dataNascimento.getMonth();

        if (mes < 0 || (mes === 0 && hoje.getDate() < dataNascimento.getDate())) {
            idade--;
        }

        const responsavelObrigatorio = document.getElementById('responsavelObrigatorio');
        const responsavelInfo = document.getElementById('responsavelInfo');
        const nomeResponsavel = document.getElementById('nomeResponsavel');

        if (idade < 18) {
            responsavelObrigatorio.style.display = 'inline';
            responsavelInfo.style.display = 'block';
            nomeResponsavel.required = true;
        } else {
            responsavelObrigatorio.style.display = 'none';
            responsavelInfo.style.display = 'none';
            nomeResponsavel.required = false;
        }

        // Atualiza as turmas disponíveis baseado na idade
        const turmasContainer = document.querySelector('#turmas-container');
        const turmasValidas = turmas.filter(turma => {
            return idade >= turma.idade_min && idade <= turma.idade_max;
        });

        // Limpa o container antes de adicionar as novas turmas
        turmasContainer.innerHTML = '';

        if (turmasValidas.length === 0) {
            turmasContainer.innerHTML =
                '<div class="alert alert-warning">Nenhuma turma disponível para a idade informada.</div>';
        } else {
            // Usa um Set para garantir que não haja duplicatas
            const turmasUnicas = new Set();

            turmasValidas.forEach(turma => {
                // Cria uma chave única para cada turma incluindo o dia da semana
                const turmaKey =
                    `${turma.id}-${turma.nome}-${turma.faixa_etaria}-${turma.horario}-${turma.dia_sem}`;

                // Só adiciona se ainda não existir
                if (!turmasUnicas.has(turmaKey)) {
                    turmasUnicas.add(turmaKey);

                    const div = document.createElement('div');
                    div.classList.add('form-check', 'mb-2');

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'turmas[]';
                    checkbox.value = turma.id;
                    checkbox.id = 'turma_' + turma.id;
                    checkbox.classList.add('form-check-input');

                    const label = document.createElement('label');
                    label.htmlFor = 'turma_' + turma.id;
                    label.classList.add('form-check-label');
                    label.textContent =
                        `${turma.nome} - ${turma.faixa_etaria} - ${turma.dia_sem} - ${turma.horario}`;

                    div.appendChild(checkbox);
                    div.appendChild(label);
                    turmasContainer.appendChild(div);
                }
            });
        }
    }

    // Validação do telefone
    const telefoneInput = document.querySelector('input[name="telefone"]');
    telefoneInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 11) {
            this.value = this.value.slice(0, 11);
        }
    });

    // Validação do formulário antes do envio
    document.getElementById('formCadastro').addEventListener('submit', function(e) {
        const telefone = telefoneInput.value.replace(/[^0-9]/g, '');
        if (telefone.length !== 11) {
            e.preventDefault();
            alert('O número de telefone deve conter exatamente 11 dígitos (DDD + número).');
            return false;
        }

        const dataNascimento = new Date(document.querySelector('input[name="data_nascimento"]').value);
        const hoje = new Date();
        let idade = hoje.getFullYear() - dataNascimento.getFullYear();
        const mes = hoje.getMonth() - dataNascimento.getMonth();

        if (mes < 0 || (mes === 0 && hoje.getDate() < dataNascimento.getDate())) {
            idade--;
        }

        if (idade < 18 && !document.querySelector('input[name="nome_responsavel"]').value.trim()) {
            e.preventDefault();
            alert('Para alunos menores de 18 anos, o nome do responsável é obrigatório.');
            return false;
        }
    });

    const dataNascimentoInput = document.querySelector('input[name="data_nascimento"]');
    const turmasContainer = document.querySelector('#turmas-container');
    const turmas = <?php echo json_encode($turmas, JSON_UNESCAPED_UNICODE); ?>;

    // Remove a chamada duplicada do evento change
    dataNascimentoInput.addEventListener('change', function() {
        validarIdade(this);
    });
    document.addEventListener('DOMContentLoaded', function() {
        validarIdade(dataNascimentoInput);
    });
    </script>
</body>

</html>