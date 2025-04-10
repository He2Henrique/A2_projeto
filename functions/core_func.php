<?php

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