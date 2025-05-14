# 📋SISTEMA DE CHAMADAS PARA CONTROLE DE PRESENÇAS
Este projeto foi desenvolvido como parte de um trabalho acadêmico, com o objetivo de criar uma aplicação web para o controle de presenças dos alunos de uma instituição que oferece aulas de balé e luta, substituindo o antigo processo manual em papel.

## ⚙️Tecnologias Utilizadas:
- PHP (BackEnd)
- MySQL (Banco de Dados)
- Bootstrap5 (Interface)

## 🧩Funcionalidades:
- ✔Página de login
- ✔Cadastro de alunos
- ✔Registro de presença por modalidade esportiva (Balé ou Luta)
- ✔Pesquisa e edição de alunos
- ✔Visualização da lista de alunos por turma
- ✔Histórico de chamadas
- ✔Relatório dos alunos (Notas e Faltas)
- ✔Edição de matrícula
- ✔Edição de chamadas

## 💡Trecho de Código Relevantes:

>> Conexão com Banco de Dados:
```php
use PDO;
use PDOException;
use Exception;

class DatabaseManager {
    private static $instance = null;
    private $connection;

    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'instituicao';
```
A conexão com o banco de dados é gerenciada pela classe `DatabaseManager` (em `src/Core/DatabaseManager.php`), que utiliza o padrão Singleton para garantir que apenas uma instância da conexão `PDO` seja utilizada em toda a aplicação.

>> Registro de Presença:
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($id_turma)) {
    try {
        $id_aula = $aulasDAO->registrarAula($id_turma, $_SESSION['usuario']['id']);
```
Aqui é registrada uma nova aula no banco de dados.

```php
foreach ($alunos as $aluno) {
    $presenca = ($_POST['presenca'][$aluno['id']] === 'presente') ? 1 : 0;
    $justificativa = $_POST['justificativa'][$aluno['id']] ?? null;
    
    if ($justificativa !== null && strlen(trim($justificativa)) <= 4) {
        $justificativa = null;
    }

    $id_frequencia = $aulasDAO->registrarFrequencia(
        $matriculas_do_aluno[$aluno['id']],
        $id_aula,
        $presenca,
        $justificativa
    );
```
Esse trecho é o responsável pela inserção da presença do aluno no banco de dados, utilizando o método ```registrarFrequencia()```.
```php
 if ($id_frequencia) {
        $logDAO->registrarLog(
            $_SESSION['usuario']['id'],
            'Registro de frequência',
            'frequencia',
            $id_frequencia,
            "Aluno ID: {$aluno['id']}, Aula ID: $id_aula, Presente: " . ($presenca ? 'Sim' : 'Não')
        );
    }
}
```
Por fim, esse trecho é o responsável por amazenar cada registro de frequência.

#### 📸Visual da Seção de Registro de Presença:
<p align="center">
  <img src="https://github.com/user-attachments/assets/5297727f-286f-4d20-ab69-e3ec469229db" width="400px">
</p>




