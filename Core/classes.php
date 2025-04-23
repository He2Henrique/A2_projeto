<?php
require_once '../Dependence/self/depedencias.php'; // Include the dependencies


//definindo classes 
// Defining classes
class alunos{
    
    private $nome;
    private $dataNas;
    private $cpf;
    private $curso;
    // não são todos os atributos ainda são apenas os que irei utilizar para fazer o teste.

    public function __construct($nome, $dataNas, $cpf, $curso){
        
        $this->nome = $nome;
        $this->dataNas = $dataNas;
        $this->cpf = $cpf;
        $this->curso = $curso;
    }

    public function getNome(){
        return $this->nome;
    }

    public function getIdade(){
        $dataAtual = date('Y-m-d');
        $datanas = new DateTime($this->dataNas);
        $dataAtual = new DateTime($dataAtual);
        $idade = $datanas->diff($dataAtual)->y;
        return $idade;
    }

    public function getStrValues(){
        $value = sprintf("('%s', '%s', '%s', '%s')", $this->nome, $this->dataNas, $this->cpf, $this->curso);
        return $this->cpf;
    }
}


class Modalidades{

    private static $modalidades = [];

    
    private static function busca_modalidades() {
        $conn = DatabaseManager::getInstance();
        $consulta = $conn->select('modalidades', []); // seleciona todas as modalidades do banco de dados
        foreach ($consulta as $modalidade) {
            self::$modalidades[$modalidade['id']] = $modalidade['nome'] . ' - ' . $modalidade['faixa_etaria'];
        }
    }

    public static function getModalidades() {
        if (empty(self::$modalidades)) {
            self::busca_modalidades();
        }
        return self::$modalidades;
    }

    public function getModalidade_byid($id){
        return self::$modalidades[$id];
    }



}