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

        public function selectMatriculaByAluno($idAluno) {
            $sql = "SELECT m.*, t.dia_sem, t.horario, 
                           modalidade.nome as nome_modalidade, modalidade.faixa_etaria
                    FROM matriculas m
                    JOIN turmas t ON m.id_turma = t.id
                    JOIN modalidades modalidade ON t.id_modalidade = modalidade.id
                    WHERE m.id_aluno = :id_aluno";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $errorCode = (int)$e->getCode();
                throw new PDOException("Erro ao buscar matrícula: " . $e->getMessage(), $errorCode);
            }
        }

        public function selectMatriculaById($idMatricula) {
            $sql = "SELECT m.*, t.dia_sem, t.horario, 
                           modalidade.nome as nome_modalidade, modalidade.faixa_etaria,
                           a.nome_completo as nome_aluno
                    FROM matriculas m
                    JOIN turmas t ON m.id_turma = t.id
                    JOIN modalidades modalidade ON t.id_modalidade = modalidade.id
                    JOIN alunos a ON m.id_aluno = a.id
                    WHERE m.id = :id_matricula";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id_matricula', $idMatricula, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $errorCode = (int)$e->getCode();
                throw new PDOException("Erro ao buscar matrícula: " . $e->getMessage(), $errorCode);
            }
        }

        public function insert($matricula) {
            $sql = "INSERT INTO matriculas (id_aluno, id_turma, data_matricula, status_) 
                    VALUES (:id_aluno, :id_turma, :data_matricula, :status_)";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id_aluno', $matricula['id_aluno'], PDO::PARAM_INT);
                $stmt->bindValue(':id_turma', $matricula['id_turma'], PDO::PARAM_INT);
                $stmt->bindValue(':data_matricula', $matricula['data_matricula']);
                $stmt->bindValue(':status_', $matricula['status_'], PDO::PARAM_INT);
                $stmt->execute();
                return $this->conn->lastInsertId();
            } catch (PDOException $e) {
                $errorCode = (int)$e->getCode();
                throw new PDOException("Erro ao inserir matrícula: " . $e->getMessage(), $errorCode);
            }
        }

        public function updateStatus($id, $novoStatus) {
            $sql = "UPDATE matriculas 
                    SET  status_ = :status_
                    WHERE id = :id";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':status_', $novoStatus, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (PDOException $e) {
                $errorCode = (int)$e->getCode();
                throw new PDOException("Erro ao atualizar matrícula: " . $e->getMessage(), $errorCode);
            }
        }

        public function delete($id) {
            $sql = "DELETE FROM matriculas WHERE id = :id";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (PDOException $e) {
                $errorCode = (int)$e->getCode();
                throw new PDOException("Erro ao deletar matrícula: " . $e->getMessage(), $errorCode);
            }
        }

        public function verificarMatriculaExistente($idAluno, $idTurma, $idMatricula = null) {
            $sql = "SELECT COUNT(*) FROM matriculas 
                    WHERE id_aluno = :id_aluno 
                    AND id_turma = :id_turma 
                    AND status_ = 1";
            
            if ($idMatricula) {
                $sql .= " AND id != :id_matricula";
            }
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
                $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
                if ($idMatricula) {
                    $stmt->bindValue(':id_matricula', $idMatricula, PDO::PARAM_INT);
                }
                $stmt->execute();
                return $stmt->fetchColumn() > 0;
            } catch (PDOException $e) {
                $errorCode = (int)$e->getCode();
                throw new PDOException("Erro ao verificar matrícula: " . $e->getMessage(), $errorCode);
            }
        }

        public function selectMatriculasByAluno($idAluno) {
            $sql = "SELECT * FROM matriculas WHERE id_aluno = :id";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $idAluno, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $errorCode = (int)$e->getCode();
                throw new PDOException("Erro ao buscar matrículas do aluno: " . $e->getMessage(), $errorCode);
            }
        }

        public function atulizarStatusMatricula($id, $status) {
            $sql = "UPDATE matriculas SET status_ = :valor WHERE id = :id";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':valor', $status, PDO::PARAM_INT);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (PDOException $e) {
                $errorCode = (int)$e->getCode();
                throw new PDOException("Erro ao atualizar status da matrícula: " . $e->getMessage(), $errorCode);
            }
        }

        public function selectMatriculasByTurma($idTurma) {
            $sql = "SELECT m.*, a.nome_completo, a.id as id_aluno
                    FROM matriculas m
                    JOIN alunos a ON m.id_aluno = a.id
                    WHERE m.id_turma = :id_turma AND m.status_ = 1";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $errorCode = (int)$e->getCode();
                throw new PDOException("Erro ao buscar matrículas da turma: " . $e->getMessage(), $errorCode);
            }
        }
    }