<?php 

namespace App\Core\Datatypes;

use InvalidArgumentException;

class NomeCompleto extends Nome{

    private $nome;

    public function __construct(string $nome){
        parent::__construct($nome);
        $this->validarNomeCompleto($nome);

    }
    function validarNomeCompleto($nome) {
        // Verifica se contém pelo menos duas palavras (nome e sobrenome)
        if (str_word_count($nome) < 2) {
            throw new InvalidArgumentException("$nome -nome completo deve conter pelo menos um nome e um sobrenome.");
        }
    }

}