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
                throw new PDOException("Erro ao contar faltas: " . $e->getMessage(), $e->getCode());
            }
        }

        public function getRelatorioGeralFaltas($idTurma = null) {
            $sql = "SELECT 
                        a.nome_completo,
                        a.id as id_aluno,
                        m.id as id_matricula,
                        t.id as id_turma,
                        m.data_matricula,
                        CONCAT(modalidade.nome, ' - ', modalidade.faixa_etaria, ' - ', t.dia_sem, ' - ', t.horario) as turma_info,
                        COUNT(CASE WHEN f.presente = 0 THEN 1 END) as total_faltas,
                        m.status_ as status_matricula
                    FROM alunos a
                    JOIN matriculas m ON a.id = m.id_aluno
                    JOIN turmas t ON m.id_turma = t.id
                    JOIN modalidades modalidade ON t.id_modalidade = modalidade.id
                    LEFT JOIN frequencia f ON m.id = f.id_matricula";

            if ($idTurma) {
                $sql .= " AND t.id = :id_turma";
            }

            $sql .= " GROUP BY a.id, m.id, t.id, modalidade.nome, modalidade.faixa_etaria, t.dia_sem, t.horario, m.status_, m.data_matricula
                     ORDER BY total_faltas DESC, a.nome_completo";

            $stmt = $this->conn->prepare($sql);

            try {
                if ($idTurma) {
                    $stmt->bindValue(':id_turma', $idTurma, PDO::PARAM_INT);
                }
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException("Erro ao gerar relatório geral: " . $e->getMessage(), $e->getCode());
            }
        }

        public function getHistoricoFrequenciaAluno($idMatricula) {
            $sql = "SELECT 
                        f.presente,
                        f.justificativa,
                        a.data_ as data_aula,
                        a.hora as hora_aula,
                        t.dia_sem,
                        t.horario,
                        modalidade.nome as nome_modalidade,
                        modalidade.faixa_etaria
                    FROM frequencia f
                    JOIN aulas a ON f.id_aula = a.id
                    JOIN matriculas m ON f.id_matricula = m.id
                    JOIN turmas t ON m.id_turma = t.id
                    JOIN modalidades modalidade ON t.id_modalidade = modalidade.id
                    WHERE f.id_matricula = :id_matricula
                    ORDER BY a.data_ DESC, a.hora DESC";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id_matricula', $idMatricula, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException("Erro ao buscar histórico de frequência: " . $e->getMessage(), $e->getCode());
            }
        }

    }