<?php
    namespace App\DAO;
    use App\Core\DatabaseManager;
    use PDO;
    use PDOException;
    use App\Core\ProcessData;


    class AlunoDAO {
        private $conn;
        private $data;

        public function __construct() {
            $this->conn = DatabaseManager::getInstance()->getConnection();
            $this->data = new ProcessData();
        }

        public function insert($aluno) {
            $sql = "INSERT INTO alunos (nome_completo, nome_soci, data_nas, nome_respon, numero, email, data_cadastro) 
                    VALUES (:nome_completo, :nome_soci, :data_nas, :nome_respon, :numero, :email, :data_cadastro)";
            $stmt = $this->conn->prepare($sql);
    
            try{
                $stmt->bindValue(':nome_completo', $aluno['nome_completo'], PDO::PARAM_STR);
                $stmt->bindValue(':nome_soci', $aluno['nome_social']?? null, PDO::PARAM_STR);
                $stmt->bindValue(':data_nas', $aluno['data_nascimento'], PDO::PARAM_STR);
                $stmt->bindValue(':nome_respon', $aluno['nome_responsavel']?? null, PDO::PARAM_STR);
                $stmt->bindValue(':numero', $aluno['telefone'], PDO::PARAM_STR);
                $stmt->bindValue(':email', $aluno['email']?? null, PDO::PARAM_STR);
                $stmt->bindValue(':data_cadastro', $this->data->getDate('y-m-d'), PDO::PARAM_STR);


                $stmt->execute();
                return $this->conn->lastInsertId();
            }catch (PDOException $e) {
                
                throw new PDOException("Erro ao inserir aluno: " . $e->getMessage(), $e->getCode());
            }
        }

        public function selectAlunosALL() {
            $sql = "SELECT * FROM alunos";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function selectAlunosBYnameLIKE($string) {
            $sql = "SELECT * FROM alunos WHERE nome_completo LIKE :nome";
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
                throw new PDOException("Erro ao buscar alunos: " . $e->getMessage(), $e->getCode());
            }
        }

        public function updateStatus($id, $status) {
            $sql = "UPDATE alunos SET status_ = :status WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            
            try {
                $stmt->bindValue(':status', $status, PDO::PARAM_STR);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (PDOException $e) {
                throw new PDOException("Erro ao atualizar status do aluno: " . $e->getMessage(), $e->getCode());
            }
        }
    }


        