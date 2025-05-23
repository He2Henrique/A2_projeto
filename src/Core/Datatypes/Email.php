<?php 

namespace App\Core\Datatypes;

use InvalidArgumentException;

class Email
{
    private $email;

    public function __construct(string $email)
    {
        $this->validateEmail($email);
        $this->email = $email;
    }

    public function validateEmail(string $email): void
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)=== false) {
            throw new InvalidArgumentException("Email inválido: $email");
        }
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}