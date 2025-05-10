<?php
    namespace App\DAO;
    use App\Core\DatabaseManager;
    use PDO;
    use PDOException;
    use App\Core\ProcessData;

    class MatriculasDAO {
        private $conn;
        private $data;

        public function __construct() {
            $this->conn = DatabaseManager::getInstance()->getConnection();
            $this->data = new ProcessData;
        }

        public function insert($matricula) {
            $sql = "INSERT INTO matriculas (id_aluno, id_turma, data_matricula) 
                    VALUES (:id_aluno, :id_turma, :data_matricula)";
            $stmt = $this->conn->prepare($sql);

            try {
                $stmt->bindValue(':id_aluno', $matricula['id_aluno'], PDO::PARAM_INT);
                $stmt->bindValue(':id_turma', $matricula['id_turma'], PDO::PARAM_INT);
                $stmt->bindValue(':data_matricula', $this->data->getDate('y-m-d'), PDO::PARAM_STR);

                $stmt->execute();
                return $this->conn->lastInsertId();
            } catch (PDOException $e) {
                throw new PDOException("Erro ao inserir matrícula: " . $e->getMessage(), $e->getCode());
            }
        }

        public function selectMatriculasFromAluno($id){

             $sql = "SELECT * FROM matriculas WHERE id_aluno = :id";
            
            $stmt = $this->conn->prepare($sql);

            try{ 
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }catch (PDOException $e) {
                throw new PDOException("Erro ao buscar alunos: " . $e->getMessage(), $e->getCode());
            }
        }

        public function atulizarStatusMatricula($id,$status){
            $sql = "UPDATE matricula SET status_ = :valor WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            try{
                $stmt->bindValue(':valor', $status, PDO::PARAM_INT);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
            }catch (PDOException $e) {
                throw new PDOException("Erro ao buscar alunos: " . $e->getMessage(), $e->getCode());
            }
            
        }
    }