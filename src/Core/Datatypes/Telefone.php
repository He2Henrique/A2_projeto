<?php
namespace App\Core\Datatypes;
use InvalidArgumentException;

class Telefone{
    

    private $telefone;

    public function __construct(string $telefone){
        $this->validarTelefone($telefone);
        $this->telefone = $telefone;
    }

    function validarTelefone($telefone) {
        // Remove tudo que não for número
        $telefoneLimpo = preg_replace('/\D/', '', $telefone);

        // Verifica se tem 10 ou 11 dígitos (com DDD)
        if (strlen($telefoneLimpo) < 10 || strlen($telefoneLimpo) > 11) {
            throw new InvalidArgumentException("$telefone - numero deve conter 10 ou 11 dígitos.");
        }
        // Se for celular (11 dígitos), verifica se o 3º dígito é 9
        if (strlen($telefoneLimpo) === 11 && $telefoneLimpo[2] !== '9') {
            throw new InvalidArgumentException("$telefone - números celulares devem começar com 9.");
        }
    }

    public function getTelefone(): string{
        return $this->telefone;
    }
}