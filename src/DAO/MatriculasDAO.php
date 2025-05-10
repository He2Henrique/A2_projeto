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
    }