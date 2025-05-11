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
            $sql = "SELECT t.*, m.nome, m.faixa_etaria, m.idade_min, m.idade_max 
                    FROM turmas t
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

        public function selectTurmaModalidade($id) {
            $sql = "SELECT t.*, m.nome, m.faixa_etaria, m.idade_min, m.idade_max 
                    FROM turmas t 
                    JOIN modalidades m ON t.id_modalidade = m.id 
                    WHERE t.id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function selectTurmasCompatibleisComIdade($idade) {
            $sql = "SELECT t.*, m.nome, m.faixa_etaria, m.idade_min, m.idade_max 
                    FROM turmas t
                    JOIN modalidades m ON t.id_modalidade = m.id
                    WHERE :idade BETWEEN m.idade_min AND m.idade_max
                    ORDER BY m.nome, m.faixa_etaria, t.dia_sem, t.horario";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':idade', $idade, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException("Erro ao buscar turmas compatíveis: " . $e->getMessage(), $e->getCode());
            }
        }

    }