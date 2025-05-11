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

        public function registrarAula($id_turma, $id_usuario) {
            $sql = "INSERT INTO aulas (data_, id_turma, hora, id_usuario) 
                    VALUES (:data_, :id_turma, :hora, :id_usuario)";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':data_', $this->data->getDate('y-m-d'), PDO::PARAM_STR);
                $stmt->bindValue(':id_turma', $id_turma, PDO::PARAM_INT);
                $stmt->bindValue(':hora', $this->data->getHorario(), PDO::PARAM_STR);
                $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
                
                $stmt->execute();
                return $this->conn->lastInsertId();
            } catch (PDOException $e) {
                throw new PDOException("Erro ao registrar aula: " . $e->getMessage(), $e->getCode());
            }
        }

        public function registrarFrequencia($id_matricula, $id_aula, $presente, $justificativa = null) {
            $sql = "INSERT INTO frequencia (id_matricula, id_aula, presente, justificativa) 
                    VALUES (:id_matricula, :id_aula, :presente, :justificativa)";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id_matricula', $id_matricula, PDO::PARAM_INT);
                $stmt->bindValue(':id_aula', $id_aula, PDO::PARAM_INT);
                $stmt->bindValue(':presente', $presente, PDO::PARAM_INT);
                $stmt->bindValue(':justificativa', $justificativa, PDO::PARAM_STR);
                
                $stmt->execute();
                return $this->conn->lastInsertId();
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
            $sql = "SELECT 
                        f.id as id_chamada,
                        a.nome_completo,
                        f.presente,
                        f.justificativa
                    FROM frequencia f
                    JOIN matriculas m ON f.id_matricula = m.id
                    JOIN alunos a ON m.id_aluno = a.id
                    JOIN aulas au ON f.id_aula = au.id
                    WHERE au.id = :id_aula 
                    AND DATE(au.data_) = :data
                    ORDER BY a.nome_completo";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id_aula', $id_aula, PDO::PARAM_INT);
                $stmt->bindValue(':data', $data, PDO::PARAM_STR);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException("Erro ao buscar chamadas: " . $e->getMessage(), $e->getCode());
            }
        }

        public function atualizarChamada($id_chamada, $presente, $justificativa = null) {
            $sql = "UPDATE frequencia 
                    SET presente = :presente, 
                        justificativa = :justificativa 
                    WHERE id = :id_chamada";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':presente', $presente, PDO::PARAM_INT);
                $stmt->bindValue(':justificativa', $justificativa, PDO::PARAM_STR);
                $stmt->bindValue(':id_chamada', $id_chamada, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (PDOException $e) {
                throw new PDOException("Erro ao atualizar chamada: " . $e->getMessage(), $e->getCode());
            }
        }

        public function getChamadasPorTurma() {
            $sql = "SELECT 
                        a.id as id_aula,
                        a.data_ as data,
                        a.hora,
                        t.id as id_turma,
                        CONCAT(m.nome, ' - ', m.faixa_etaria, ' - ', t.dia_sem, ' - ', t.horario) as turma_info,
                        COUNT(f.id) as total_alunos,
                        SUM(CASE WHEN f.presente = 1 THEN 1 ELSE 0 END) as total_presentes
                    FROM aulas a
                    JOIN turmas t ON a.id_turma = t.id
                    JOIN modalidades m ON t.id_modalidade = m.id
                    LEFT JOIN frequencia f ON a.id = f.id_aula
                    GROUP BY a.id, a.data_, a.hora, t.id, m.nome, m.faixa_etaria, t.dia_sem, t.horario
                    ORDER BY a.data_ DESC, a.hora DESC";
            
            try {
                $stmt = $this->conn->prepare($sql);
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