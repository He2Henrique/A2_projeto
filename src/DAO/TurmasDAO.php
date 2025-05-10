<?php
    namespace App\DAO;
    use App\Core\DatabaseManager;
    use PDO;
    use PDOException;
    use App\Core\ProcessData;

    Class TurmasDAO{

        private $conn;
        private $data;

        public function __construct() {
            $this->conn = DatabaseManager::getInstance()->getConnection();
            $this->data = new ProcessData();
        }

        public function selectTurmasALL() {
            $sql = "SELECT * FROM turmas";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function selectTurmasModalidadesALL() {
            $sql = "SELECT t.*, m.* FROM turmas t
                    JOIN modalidades m ON t.id_modalidade = m.id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function TurmasHJ(){
            $sql = "SELECT * from turmas where dia_sem = :diaSemana";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':diaSemana', $this->data->getDiaSemana(), PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }