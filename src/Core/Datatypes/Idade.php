<?php
 
namespace App\Core\Datatypes;

use Datetime;
use InvalidArgumentException;

class Idade{
    private $idade;
    private $IDADE_MINIMA = 4;


    public function __construct(string $dataNascimento){
        $dataObj = DateTime::createFromFormat('Y-m-d', $dataNascimento);
        $this->idade = $this->validarIdade($dataObj);

    }

    function validarIdade(Datetime $dataObj): int {

        $hoje = new DateTime();
        $idade = $hoje->diff($dataObj)->y;
        if ($idade < $this->IDADE_MINIMA) {
            throw new InvalidArgumentException("Idade inválida: $idade - idade minima é {$this->IDADE_MINIMA}.");
        }
        return $idade;
    }

    public function getIdade(): int {
        return $this->idade;
    }
}