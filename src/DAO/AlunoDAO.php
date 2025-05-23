<?php
    namespace App\DAO;

use APP\Core\AlunoRequest;
use App\Core\DatabaseManager;
use PDO;
use PDOException;
    


class AlunoDAO {
    private $conn;
    
    public function __construct() {
        $this->conn = DatabaseManager::getInstance()->getConnection();
    }

    public function insert(AlunoRequest $aluno) {
        $sql = "INSERT INTO alunos (nome_completo, nome_soci, data_nas, nome_respon, numero, email, data_cadastro) 
                VALUES (:nome_completo, :nome_soci, :data_nas, :nome_respon, :numero, :email, :data_cadastro)";
        $stmt = $this->conn->prepare($sql);

        try{
            $stmt->bindValue(':nome_completo',$aluno->getNomeCompleto(),      PDO::PARAM_STR);
            $stmt->bindValue(':nome_soci'    ,$aluno->getNomeSocial(),        PDO::PARAM_STR);
            $stmt->bindValue(':data_nas'     ,$aluno->getDataNascimento(),    PDO::PARAM_STR);
            $stmt->bindValue(':nome_respon'  ,$aluno->getNomeResponsavel(),   PDO::PARAM_STR);
            $stmt->bindValue(':numero'       ,$aluno->getTelefone(),          PDO::PARAM_STR);
            $stmt->bindValue(':email'        ,$aluno->getEmail() ,            PDO::PARAM_STR);
            $stmt->bindValue(':data_cadastro',$aluno->getDataMatricula() ,    PDO::PARAM_STR);

            $stmt->execute();
            return (int)$this->conn->lastInsertId();
        }catch (PDOException $e) {
            
            throw new PDOException("Erro ao inserir aluno: " . $e->getMessage(), $e->getCode());
        }
    }

    public function selectAlunosALL() {
        $sql = "SELECT * FROM alunos ORDER BY nome_completo";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectAlunosBYnameLIKE($string) {
        $sql = "SELECT * FROM alunos WHERE nome_completo LIKE :nome ORDER BY nome_completo";
        $stmt = $this->conn->prepare($sql);

        try {
            $stmt->bindValue(':nome', '%' . $string . '%', PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Erro ao buscar alunos: " . $e->getMessage(), $e->getCode());
        }
    }

    public function selectAlunoBYID($id){
        $sql = "SELECT * FROM alunos WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);

        try{ 
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e) {
            throw new PDOException("Erro ao buscar aluno: " . $e->getMessage(), $e->getCode());
        }
    }

    public function update($id, AlunoRequest $aluno) {
        $sql = "UPDATE alunos SET 
                nome_completo = :nome_completo,
                nome_soci = :nome_soci,
                data_nas = :data_nas,
                nome_respon = :nome_respon,
                numero = :numero,
                email = :email
                WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nome_completo',$aluno->getNomeCompleto(),      PDO::PARAM_STR);
            $stmt->bindValue(':nome_soci'    ,$aluno->getNomeSocial(),        PDO::PARAM_STR);
            $stmt->bindValue(':data_nas'     ,$aluno->getDataNascimento(),    PDO::PARAM_STR);
            $stmt->bindValue(':nome_respon'  ,$aluno->getNomeResponsavel(),   PDO::PARAM_STR);
            $stmt->bindValue(':numero'       ,$aluno->getTelefone(),          PDO::PARAM_STR);
            $stmt->bindValue(':email'        ,$aluno->getEmail() ,            PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Erro ao atualizar aluno: " . $e->getMessage(), $e->getCode());
        }
    }

    public function delete($id) {
        $sql = "DELETE FROM alunos WHERE id = :id";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Erro ao deletar aluno: " . $e->getMessage(), $e->getCode());
        }
    }
}


        