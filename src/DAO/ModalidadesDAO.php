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

        public function insert($nome, $faixa_etaria, $idade_min, $idade_max) {
            try {
                $sql = "INSERT INTO modalidades (nome, faixa_etaria, idade_min, idade_max) 
                        VALUES (:nome, :faixa_etaria, :idade_min, :idade_max)";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
                $stmt->bindValue(':faixa_etaria', $faixa_etaria, PDO::PARAM_STR);
                $stmt->bindValue(':idade_min', $idade_min, PDO::PARAM_INT);
                $stmt->bindValue(':idade_max', $idade_max, PDO::PARAM_INT);
                $stmt->execute();
                return $this->conn->lastInsertId();
            } catch (PDOException $e) {
                throw new PDOException("Erro ao inserir modalidade: " . $e->getMessage(), $e->getCode());
            }
        }

        public function update($id, $nome, $faixa_etaria, $idade_min, $idade_max) {
            try {
                $sql = "UPDATE modalidades 
                        SET nome = :nome, 
                            faixa_etaria = :faixa_etaria, 
                            idade_min = :idade_min, 
                            idade_max = :idade_max 
                        WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
                $stmt->bindValue(':faixa_etaria', $faixa_etaria, PDO::PARAM_STR);
                $stmt->bindValue(':idade_min', $idade_min, PDO::PARAM_INT);
                $stmt->bindValue(':idade_max', $idade_max, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (PDOException $e) {
                throw new PDOException("Erro ao atualizar modalidade: " . $e->getMessage(), $e->getCode());
            }
        }

        public function getById($id) {
            try {
                $sql = "SELECT * FROM modalidades WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException("Erro ao buscar modalidade: " . $e->getMessage(), $e->getCode());
            }
        }

        public function getAll() {
            try {
                $sql = "SELECT * FROM modalidades ORDER BY nome, faixa_etaria";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException("Erro ao listar modalidades: " . $e->getMessage(), $e->getCode());
            }
        }

        public function searchByName($busca) {
            try {
                $sql = "SELECT * FROM modalidades 
                        WHERE nome LIKE :busca OR faixa_etaria LIKE :busca 
                        ORDER BY nome, faixa_etaria";
                $stmt = $this->conn->prepare($sql);
                $busca = "%{$busca}%";
                $stmt->bindValue(':busca', $busca, PDO::PARAM_STR);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException("Erro ao buscar modalidades: " . $e->getMessage(), $e->getCode());
            }
        }

        public function verificarExistencia($nome, $faixa_etaria, $excludeId = null) {
            try {
                $sql = "SELECT COUNT(*) FROM modalidades 
                        WHERE nome = :nome AND faixa_etaria = :faixa_etaria";
                
                if ($excludeId !== null) {
                    $sql .= " AND id != :exclude_id";
                }

                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
                $stmt->bindValue(':faixa_etaria', $faixa_etaria, PDO::PARAM_STR);
                
                if ($excludeId !== null) {
                    $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
                }

                $stmt->execute();
                return $stmt->fetchColumn() > 0;
            } catch (PDOException $e) {
                throw new PDOException("Erro ao verificar existência da modalidade: " . $e->getMessage(), $e->getCode());
            }
        }

        public function hasTurmasAtivas($id) {
            try {
                $sql = "SELECT COUNT(*) FROM turmas 
                        WHERE id_modalidade = :id 
                        AND status = 1";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchColumn() > 0;
            } catch (PDOException $e) {
                throw new PDOException("Erro ao verificar turmas ativas: " . $e->getMessage(), $e->getCode());
            }
        }
    }