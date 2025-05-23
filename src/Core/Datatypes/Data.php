<?php

namespace App\Core\Datatypes;
use Datetime;
use InvalidArgumentException;

class Data{

    private $dataClass;
    private $hoje;

    public function __construct(?string $data=null){
        if($data == null){
            $hoje = new DateTime();
            $this->dataClass = $hoje->format('Y-m-d');
            return;
        }
        $this->validarData($data);
        $this->dataClass = $data;
    }

    function validarData($data) {
        
        $dataFormatada = DateTime::createFromFormat('Y-m-d', $data);

        // Verifica se a data é válida e bem formatada
        if (!$dataFormatada || $dataFormatada->format('Y-m-d') !== $data) {
            throw new InvalidArgumentException("Data inválida: $data - deve estar no formato YYYY-MM-DD.");
        }
    }

    public function getData(?string $data = null): string{
        if($data == "d/m/y"){
            return $this->hoje->format('d/m/Y');
        }

        return $this->dataClass;
    }
}