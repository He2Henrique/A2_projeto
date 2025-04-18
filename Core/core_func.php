<?php
require_once ('config_serv.php'); // Include the database connection file
$conn = DatabaseManager::getInstance(); // Create a new instance of the database connection
//definindo variáveis e funções para o sistema
date_default_timezone_set('America/Sao_Paulo');
$diasSemana = [
    1 => 'Segunda',
    2 => 'Terça',
    3 => 'Quarta',
    4 => 'Quinta',
    5 => 'Sexta',
    6 => 'Sábado'
];
$data_hojebd = date('Y-m-d');//para o banco de dados
$data_hojeFront = date('d/m/Y', strtotime($data_hojebd)); // para exibir no front-end
$dia_sem = date('w', strtotime($data_hojebd)); // 0 = domingo, 1 = segunda, ..., 6 = sabado
$horario = date('H:i:s'); // Horário atual no formato HH:MM:SS

//definido turmas
$consulta = $conn->select('modalidades', []); // Select all classes from the database
$turmas = []; // Array to store classes
foreach ($consulta as $turma) {
    $turmas[$turma['id']] = $turma['nome'] . ' - ' . $turma['faixa_etaria'];
}


//validar CPF
function validarCPF(string $cpf): bool
{
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }
    
    // Calcula dígitos verificadores
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    
    return true;
}


//formartar cpf de 000.000.000-00 para 00000000000
function formatCPF(string $cpf): string
{
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    return $cpf;
}


function calcularIdade(string $data_nasc): int
{
    $data_nasc = DateTime::createFromFormat('Y-m-d', $data_nasc);
    $hoje = new DateTime();
    
    return $hoje->diff($data_nasc)->y;
}

function validarTelefone(string $telefone): bool
{
    // Remove caracteres não numéricos
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    // Verifica se o telefone tem 10 ou 11 dígitos (DDD + número)
    return (strlen($telefone) == 10 || strlen($telefone) == 11);
}

function Idade(string $data_nasc): int
{
    $data_nasc = DateTime::createFromFormat('Y-m-d', $data_nasc);
    $hoje = new DateTime();
    
    return $hoje->diff($data_nasc)->y;
}