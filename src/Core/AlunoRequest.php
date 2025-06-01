<?php

    
namespace App\Core;
use InvalidArgumentException;
use App\Core\Datatypes\DataNascimento;
use App\Core\Datatypes\Email;
use App\Core\Datatypes\NomeCompleto;
use App\Core\Datatypes\Telefone;
use App\Core\Datatypes\Idade;
use App\Core\Datatypes\Data;
use App\Core\Datatypes\Nome;

class AlunoRequest{
    // campos do formulario
    private $nome_completo;
    private $nome_responsavel;
    private $data_nascimento;
    private $telefone;
    private $email;
    private $nomesocial;
    // campos automaticos
    private $data_matricula;
    private $idade;

    public function __construct($_post){
        try {
            $data = new Data();

            //campos obrigatorios
            $this->nome_completo = new NomeCompleto($_post['nome_completo']);
            $this->telefone = new Telefone($_post['telefone']);
            $this->data_nascimento = new DataNascimento($_post['data_nascimento']);
            $this->idade = new Idade($this->data_nascimento->getDataNascimento());

            if ($this->idade->getIdade() < 18 && empty(isset($_post['nome_responsavel']))) {
                throw new InvalidArgumentException("Para alunos menores de 18 anos, o nome do responsável é obrigatório.");
            }
            // campos opcionais
            $this->email = ($_post['email'] !== '') ? new Email($_post['email']) : null;
            $this->nome_responsavel = ($_post['nome_responsavel']!== '') ? new NomeCompleto($_post['nome_responsavel']) : null;
            $this->nomesocial = ($_post['nome_social']!== '') ? new Nome($_post['nomesocial']) : null;
            $this->data_matricula = $data->getDataString();
            
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    public function getNomeCompleto(): string{
        return $this->nome_completo->getNome();
    }   
    public function getNomeResponsavel(): ?string{
        return $this->nome_responsavel ? $this->nome_responsavel->getNome() : null;
    }
    public function getNomeSocial(): ?string{
        return $this->nomesocial ? $this->nomesocial->getNome() : null;
    }
    public function getDataNascimento(): string{
        return $this->data_nascimento->getDataNascimento();
    }
    public function getTelefone(): string{
        return $this->telefone->getTelefone();
    }
    public function getEmail(): ?string{
        return $this->email ? $this->email->getEmail() : null;
    }
    public function getDataMatricula(): string{
        return $this->data_matricula;
    }
    public function getIdade(): int{
        return $this->idade->getIdade();
    }
    
    
}