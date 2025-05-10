<?php
namespace App\Core;
use Datetime;
use Exception; // Importando a classe Exception para tratamento de erros

date_default_timezone_set('America/Sao_Paulo'); // Definindo o fuso horário para São Paulo
class ProcessData{
   
    
    private $SEMANA = [
        0 => 'Domingo',
        1 => 'Segunda',
        2 => 'Terça',
        3 => 'Quarta',
        4 => 'Quinta',
        5 => 'Sexta',
        6 => 'Sábado'
    ];

    public function getDate($op){
        if($op == 'y-m-d'){
            return date('y-m-d');
        }else if($op == 'd-m-y'){
            return date('d/m/y', strtotime(date('Y-m-d')));
        }else{
            throw new Exception("Formato inválido. Use 'y-m-d' ou 'd-m-y'.");
        }
    }

    public function getHorario() : string{
        return date('H:i:s');
        
    }

    public function getDiaSemana(): string{
        $DIA_SEM = date('w', strtotime(date('Y-m-d'))); // 0 = domingo, 1 = segunda, ..., 6 = sábado
        return $this->SEMANA[$DIA_SEM];
    }


    public function validarCPF(string $cpf): bool{
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
    
    public function validarTelefone(string $telefone): bool{
        // Remove caracteres não numéricos
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        
        // Verifica se o telefone tem 10 ou 11 dígitos (DDD + número)
        return (strlen($telefone) == 10 || strlen($telefone) == 11);
    }

    //exemplo cpf de 000.000.000-00 para 00000000000
    public function ApenasNumeros(string $string): string{
        $string_nums = preg_replace('/[^0-9]/', '', $string);
        
        return $string_nums;
    }

    public function Idade(string $data_nasc): int{
        $data_nasc = DateTime::createFromFormat('Y-m-d', $data_nasc);
        $hoje = new DateTime();
        
        return $hoje->diff($data_nasc)->y;
    }
    
}