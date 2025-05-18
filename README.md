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
- ✔Edição de alunos
- ✔Edição de chamadas

## 💡Informações Relevantes:

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

>> ✎Cadastro de alunos:

Ao realizar o cadastramento de alunos, é necessário que sejam informados seus dados básicos, para que ele seja registrado no banco de dados. Portanto solicitamos:
 - Nome do Responsável (Obrigatório, caso o aluno seja menor de 18 anos)
 - Nome Completo (Obrigatório)
 - Telefone (Obrigatório)
 - Nome social (Opcional)
 - E-mail (Opcional)
 - Modalidade Esportiva.

⚠Observação.: Ao invés de utilizarmos o CPF, optamos por solicitar somente o Nome completo e o Telefone como dados principais, já que ambos constituem dados únicos de um indivíduo. Dessa maneira preservamos dados importantes dos alunos. 

#### 📸Visual da Seção de Cadastramento de Alunos:

<p align="center">
  <img src= https://github.com/user-attachments/assets/d98659b0-eeff-4beb-9c23-7a7293890a5d
 width="400px">
</p>

>> ✎Histórico de Chamadas - Relatório Geral:

Optamos por fazer um código que ordenasse a lista geral de alunos seguindo a respectiva ordem:
- Alunos que possuem o maior número de faltas;
- Alunos que possuem mais faltas justificadas;
- Alunos inativos;

 ⚠Observação.: Para facilitar e otimizar o tempo do usuário (professor) ao realizar chamadas, optamos por desenvolver um código que remove alunos inativos da lista de chamadas.

#### 📸Visual da Seção de Relatório Geral de Faltas: 
  <p align="center">
      <img src=https://github.com/user-attachments/assets/f878411b-7010-495c-a269-262c643d7e13 width="400px">
  </p>

>> ✎Opções de Edição:

Adicionamos um código que permite a facilidade em edições. É possível realizar a edição de:
- Modalidades;
- Chamadas;
- Alunos;
- Matrícula;
- Turma;

Fizemos isso para facilitar o manuseio dos usuários, porém com algumas restrições, como por exemplo, a partir do momento em que um aluno é associado a uma turma, não é possível realizar edições que visam excluir essa turma. Isso evita erros inesperados.

#### 📸Visual de uma das Seções de Edição: 
  <p align="center">
      <img src=https://github.com/user-attachments/assets/0e5c3c8e-7f78-43e8-b688-ad6f6ebd200f
    width="400px">
  </p>

  >> ✎Histórico de chamadas - Visuzalizar chamada do dia:

Após ser realizado a chamada do dia, é criado uma lista de registro referente a essa chamada, e permite que o usuário possa fazer edições nessa chamada caso necessário.

#### 📸Visual da Seção de Visualizar Chamadas: 
  <p align="center">
      <img src=https://github.com/user-attachments/assets/e48e65d6-dd8f-4058-b6cf-46b490cfc538
    width="400px">
  </p>
______________________________________________________________________________________________________________________________________________________

# 💨Para finalizar...

Este sistema foi desenvolvido com foco em facilitar a rotina administrativa e pedagógica da instituição, modernizando o controle de presenças e oferecendo uma interface intuitiva para professores e gestores. A substituição do método manual por uma solução digital visa não apenas otimizar o tempo, mas também garantir maior precisão nos registros, segurança dos dados e facilidade no acompanhamento do desempenho dos alunos.

Além disso, o projeto demonstra na prática a aplicação de conceitos de desenvolvimento web, banco de dados e boas práticas de organização de código — consolidando-se como uma solução funcional e escalável para ambientes educacionais com múltiplas modalidades esportivas.

Esperamos que este sistema seja útil e que continue evoluindo conforme as necessidades da instituição.




