<?php

    
namespace App\Core\Datatypes;

use Datetime;
use InvalidArgumentException;

class DataNascimento extends Data{
    private $dataNascimento;
    private $IDADE_MINIMA = 4;


    public function __construct(string $dataNascimento){
        
        $this->validarData($dataNascimento);
        $this->validarDataNascimento($dataNascimento);
        $this->dataNascimento = $dataNascimento;
    }

    function validarDataNascimento($data) {

        $dataFormatada = DateTime::createFromFormat('Y-m-d', $data);
        $hoje = new DateTime();
        
        if ($dataFormatada > $hoje) {
            throw new InvalidArgumentException("Data de nascimento inválida: $data - não pode ser uma data futura.");
        }
    }

    public function getDataNascimento(): string{
        return $this->dataNascimento;
    }
}