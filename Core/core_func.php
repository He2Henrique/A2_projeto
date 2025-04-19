<?php
require_once ('config_serv.php'); // Include the database connection file
require_once ('classes.php'); // Include the classes file
$conn = DatabaseManager::getInstance(); // Create a new instance of the database connection
//definindo variáveis e funções para o sistema
date_default_timezone_set('America/Sao_Paulo');


$SEMANA = [
    1 => 'Segunda',
    2 => 'Terça',
    3 => 'Quarta',
    4 => 'Quinta',
    5 => 'Sexta',
    6 => 'Sábado'
];

$DATA_YMD = date('Y-m-d');//para o banco de dados
$DATA_DMY = date('d/m/Y', strtotime($DATA_YMD)); // para exibir no front-end
$DIA_SEM = date('w', strtotime($DATA_YMD)); // 0 = domingo, 1 = segunda, ..., 6 = sabado
$HORARIO = date('H:i:s'); // Horário atual no formato HH:MM:SS

$MODALIDADES = Modalidades::getModalidades(); // Instância da classe Modalidades



//validar CPF
function validarCPF(string $cpf): bool{
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
function formatCPF(string $cpf): string{
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    
    return $cpf;
}

function calcularIdade(string $data_nasc): int{
    $data_nasc = DateTime::createFromFormat('Y-m-d', $data_nasc);
    $hoje = new DateTime();
    
    return $hoje->diff($data_nasc)->y;
}

function validarTelefone(string $telefone): bool{
    // Remove caracteres não numéricos
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    
    // Verifica se o telefone tem 10 ou 11 dígitos (DDD + número)
    return (strlen($telefone) == 10 || strlen($telefone) == 11);
}

function Idade(string $data_nasc): int{
    $data_nasc = DateTime::createFromFormat('Y-m-d', $data_nasc);
    $hoje = new DateTime();
    
    return $hoje->diff($data_nasc)->y;
}