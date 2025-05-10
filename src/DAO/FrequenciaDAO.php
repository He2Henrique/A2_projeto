<?php
    namespace App\DAO;
    use App\Core\DatabaseManager;
    use PDO;
    use PDOException;
    use App\Core\ProcessData;


    class FrequenciaDAO {
        private $conn;
        

        public function __construct() {
            $this->conn = DatabaseManager::getInstance()->getConnection();
            
        }

        public function countFaltas($id){
            $sql = "SELECT COUNT(*) 
                    FROM frequencia 
                    WHERE id_matricula = :id_matricula 
                    AND presente = 0;";
            $stmt = $this->conn->prepare($sql);

            try {
                $stmt->bindValue(':id_matricula', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchColumn();
            } catch (PDOException $e) {
                throw new PDOException("Erro ao inserir matrícula: " . $e->getMessage(), $e->getCode());
            }
        }



    }