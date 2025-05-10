<?php
    namespace App\DAO;
    use App\Core\DatabaseManager;
    use PDO;
    use PDOException;
    use App\Core\ProcessData;

    class ModalidadesDAO{

        private $conn;

        public function __construct() {
            $this->conn = DatabaseManager::getInstance()->getConnection();
        }

        public function selectModalidadesALL() {
            $sql = "SELECT * FROM modalidades";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function selectModalidadesbyID($id) {
            $sql = "SELECT * FROM modalidades where id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $rusltado = $stmt->fetch(PDO::FETCH_ASSOC);
            $modalidade = $rusltado['id_modalidade'] = $rusltado['nome'] . ' - ' . $rusltado['faixa_etaria'];
            return $modalidade;
        }


        
    }