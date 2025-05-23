<?php
namespace App\Core;
use DateTime; // Importando a classe DateTime para manipulação de datas
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

    
}