<?php

namespace App\Core\Datatypes;
use Datetime;
use InvalidArgumentException;
date_default_timezone_set('America/Sao_Paulo'); 

class Data{

    private DateTime $DatetimeOBJ;

    private string $DataStringFMT;
    
    private $diasSemana = [
        0 => 'Domingo',
        1 => 'Segunda',
        2 => 'Terça',
        3 => 'Quarta',
        4 => 'Quinta',
        5 => 'Sexta',
        6 => 'Sábado'
    ];

    public function __construct(?string $DataStringFMT=null){
        
        if($DataStringFMT == null){
            $this->DatetimeOBJ = new DateTime();
            $this->DataStringFMT = $this->DatetimeOBJ->format('Y-m-d');
            return;
        }
        $this->validarData($DataStringFMT);
        $this->DataStringFMT = $DataStringFMT;
    }

    function validarData($DataFMT) {
        
        $DataFMTFormatada = DateTime::createFromFormat('Y-m-d', $DataFMT);

        // Verifica se a DataFMT é válida e bem formatada
        if (!$DataFMTFormatada || $DataFMTFormatada->format('Y-m-d') !== $DataFMT) {
            throw new InvalidArgumentException("Data inválida: $DataFMT - deve estar no formato YYYY-MM-DD.");
        }
    }

    public function getDataString(?string $format = null): string{
        if($format == "d/m/y"){
            return $this->DatetimeOBJ->format('d/m/Y');
        }
        return $this->DataStringFMT;
    }

    public function getHorario(): string {
    if (!isset($this->DatetimeOBJ)) {
        throw new \LogicException('Horário não disponível para datas passadas.');
    }
        return $this->DatetimeOBJ->format('H:i:s');
    }

    public function getDiaSemana(int $diaInt): string {
        if (array_key_exists($diaInt, $this->diasSemana)) {
            return $this->diasSemana[$diaInt];
        }
        throw new \InvalidArgumentException("Dia da semana inválido: $diaInt. Deve ser um número entre 0 (Domingo) e 6 (Sábado).");
    }

    public function getDiaSemanaAtual(): string {
        $diaSemanaAtual = date('w'); // 0 = domingo, 1 = segunda, ..., 6 = sábado
        return $this->getDiaSemana($diaSemanaAtual);
    }
}