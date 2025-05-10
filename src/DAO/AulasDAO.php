<?php 
    namespace App\DAO;
    use App\Core\DatabaseManager;
    use PDO;
    use PDOException;
    use App\Core\ProcessData;

    Class AulasDAO{

        private $conn;
        private $data;

        public function __construct() {
            $this->conn = DatabaseManager::getInstance()->getConnection();
            $this->data = new ProcessData();
        }

        public function AulasRealizadasHJ(){
            $sql = "SELECT * from turmas where dia_sem = :diaSemana";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':diaSemana', $this->data->getDiaSemana(), PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }


    }