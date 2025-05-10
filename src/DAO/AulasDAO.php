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

        public function registrarAula($id_turma) {
            $sql = "INSERT INTO aulas (data_, id_turma, hora) VALUES (:data_, :id_turma, :hora)";
            $stmt = $this->conn->prepare($sql);
            
            try {
                $stmt->bindValue(':data_', $this->data->getDate('y-m-d'), PDO::PARAM_STR);
                $stmt->bindValue(':id_turma', $id_turma, PDO::PARAM_INT);
                $stmt->bindValue(':hora', $this->data->getHorario(), PDO::PARAM_STR);
                
                $stmt->execute();
                return $this->conn->lastInsertId();
            } catch (PDOException $e) {
                throw new PDOException("Erro ao registrar aula: " . $e->getMessage(), $e->getCode());
            }
        }

        public function registrarFrequencia($frequencia) {
            $sql = "INSERT INTO frequencia (id_matricula, id_aula, presente, justificativa) 
                    VALUES (:id_matricula, :id_aula, :presente, :justificativa)";
            $stmt = $this->conn->prepare($sql);
            
            try {
                $stmt->bindValue(':id_matricula', $frequencia['id_matricula'], PDO::PARAM_INT);
                $stmt->bindValue(':id_aula', $frequencia['id_aula'], PDO::PARAM_INT);
                $stmt->bindValue(':presente', $frequencia['presente'], PDO::PARAM_INT);
                $stmt->bindValue(':justificativa', $frequencia['justificativa'], PDO::PARAM_STR);
                
                return $stmt->execute();
            } catch (PDOException $e) {
                throw new PDOException("Erro ao registrar frequência: " . $e->getMessage(), $e->getCode());
            }
        }

        public function getAulaById($id) {
            $sql = "SELECT * FROM aulas WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            
            try {
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException("Erro ao buscar aula: " . $e->getMessage(), $e->getCode());
            }
        }

        public function getChamadasByAula($id_aula, $data) {
            $sql = "SELECT c.*, a.nome_completo 
                    FROM chamadas c 
                    JOIN alunos a ON c.id_aluno = a.id 
                    WHERE c.id_aula = :id_aula AND c.data = :data";
            $stmt = $this->conn->prepare($sql);
            
            try {
                $stmt->bindValue(':id_aula', $id_aula, PDO::PARAM_INT);
                $stmt->bindValue(':data', $data, PDO::PARAM_STR);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException("Erro ao buscar chamadas: " . $e->getMessage(), $e->getCode());
            }
        }

        public function atualizarChamada($id_chamada, $presente) {
            $sql = "UPDATE chamadas SET presente = :presente WHERE id_chamada = :id_chamada";
            $stmt = $this->conn->prepare($sql);
            
            try {
                $stmt->bindValue(':presente', $presente, PDO::PARAM_INT);
                $stmt->bindValue(':id_chamada', $id_chamada, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (PDOException $e) {
                throw new PDOException("Erro ao atualizar chamada: " . $e->getMessage(), $e->getCode());
            }
        }

        public function getChamadasPorTurma() {
            $sql = "SELECT c.id_aulas, c.data, COUNT(*) as total_chamadas 
                    FROM chamada c 
                    GROUP BY c.id_aulas, c.data";
            $stmt = $this->conn->prepare($sql);
            
            try {
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException("Erro ao buscar chamadas por turma: " . $e->getMessage(), $e->getCode());
            }
        }

        public function getAulasComModalidades() {
            $sql = "SELECT a.id_aulas, a.id_modalidade, a.horario, m.nome as modalidade_nome 
                    FROM aulas a 
                    JOIN modalidades m ON a.id_modalidade = m.id";
            $stmt = $this->conn->prepare($sql);
            
            try {
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $mapAula = [];
                foreach ($result as $aula) {
                    $mapAula[$aula['id_aulas']] = [
                        'modalidade' => $aula['modalidade_nome'],
                        'horario' => $aula['horario']
                    ];
                }
                return $mapAula;
            } catch (PDOException $e) {
                throw new PDOException("Erro ao buscar aulas com modalidades: " . $e->getMessage(), $e->getCode());
            }
        }
    }