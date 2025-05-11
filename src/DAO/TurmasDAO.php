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

        public function insert($modalidadeId, $diaSemana, $horario) {
            try {
                $sql = "INSERT INTO turmas (id_modalidade, dia_sem, horario, status) 
                        VALUES (:modalidade_id, :dia_semana, :horario, 1)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':modalidade_id', $modalidadeId, PDO::PARAM_INT);
                $stmt->bindValue(':dia_semana', $diaSemana, PDO::PARAM_STR);
                $stmt->bindValue(':horario', $horario, PDO::PARAM_STR);
                $stmt->execute();
                return $this->conn->lastInsertId();
            } catch (PDOException $e) {
                throw new PDOException("Erro ao inserir turma: " . $e->getMessage(), $e->getCode());
            }
        }

        public function update($id, $modalidadeId, $diaSemana, $horario, $status = 1) {
            try {
                $sql = "UPDATE turmas 
                        SET id_modalidade = :modalidade_id,
                            dia_sem = :dia_semana,
                            horario = :horario,
                            status = :status
                        WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':modalidade_id', $modalidadeId, PDO::PARAM_INT);
                $stmt->bindValue(':dia_semana', $diaSemana, PDO::PARAM_STR);
                $stmt->bindValue(':horario', $horario, PDO::PARAM_STR);
                $stmt->bindValue(':status', $status, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (PDOException $e) {
                throw new PDOException("Erro ao atualizar turma: " . $e->getMessage(), $e->getCode());
            }
        }

        public function verificarHorarioExistente($diaSemana, $horario, $excludeId = null) {
            try {
                $sql = "SELECT COUNT(*) FROM turmas 
                        WHERE dia_sem = :dia_semana 
                        AND horario = :horario 
                        AND status = 1";
                
                if ($excludeId !== null) {
                    $sql .= " AND id != :exclude_id";
                }

                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':dia_semana', $diaSemana, PDO::PARAM_STR);
                $stmt->bindValue(':horario', $horario, PDO::PARAM_STR);
                
                if ($excludeId !== null) {
                    $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
                }

                $stmt->execute();
                return $stmt->fetchColumn() > 0;
            } catch (PDOException $e) {
                throw new PDOException("Erro ao verificar horário: " . $e->getMessage(), $e->getCode());
            }
        }

        public function getConnection() {
            return $this->conn;
        }

        public function getById($id) {
            try {
                $sql = "SELECT * FROM turmas WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException("Erro ao buscar turma: " . $e->getMessage(), $e->getCode());
            }
        }

        public function hasMatriculas($id) {
            try {
                $sql = "SELECT COUNT(*) FROM matriculas WHERE id_turma = :id AND status_ = 1";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchColumn() > 0;
            } catch (PDOException $e) {
                throw new PDOException("Erro ao verificar matrículas: " . $e->getMessage(), $e->getCode());
            }
        }

        public function hasAulas($id) {
            try {
                $sql = "SELECT COUNT(*) FROM aulas WHERE id_turma = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchColumn() > 0;
            } catch (PDOException $e) {
                throw new PDOException("Erro ao verificar aulas: " . $e->getMessage(), $e->getCode());
            }
        }

        public function softDelete($id) {
            try {
                $sql = "UPDATE turmas SET status = 0 WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (PDOException $e) {
                throw new PDOException("Erro ao excluir turma: " . $e->getMessage(), $e->getCode());
            }
        }

        public function searchByName($busca) {
            try {
                $sql = "SELECT t.*, m.nome, m.faixa_etaria 
                        FROM turmas t 
                        JOIN modalidades m ON t.id_modalidade = m.id 
                        WHERE m.nome LIKE :busca OR m.faixa_etaria LIKE :busca 
                        ORDER BY m.nome, m.faixa_etaria, t.dia_sem, t.horario";
                $stmt = $this->conn->prepare($sql);
                $busca = "%{$busca}%";
                $stmt->bindValue(':busca', $busca, PDO::PARAM_STR);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException("Erro ao buscar turmas: " . $e->getMessage(), $e->getCode());
            }
        }
    }