<?php 

namespace App\Core\Datatypes;

use InvalidArgumentException;

class Nome{

    private $nome;

    public function __construct(string $nome){
        // Remove espaços extras no início/fim e reduz espaços múltiplos
        $nome = trim(preg_replace('/\s+/', ' ', $nome));
        $this->validarNome($nome);
        $this->nome = $nome;

    }
    function validarNome($nome) {
        // Expressão regular: apenas letras (com acento) e espaços
        if (!preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/u', $nome)) {
            throw new InvalidArgumentException("$nome - nome deve conter apenas letras e espaços.");
        }
    }

    public function getNome(): string{
        return $this->nome;
    }
}