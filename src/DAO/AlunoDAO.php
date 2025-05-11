<?php
    namespace App\DAO;
    use App\Core\DatabaseManager;
    use PDO;
    use PDOException;
    use App\Core\ProcessData;


    class AlunoDAO {
        private $conn;
        private $data;

        public function __construct() {
            $this->conn = DatabaseManager::getInstance()->getConnection();
            $this->data = new ProcessData();
        }

        public function insert($aluno) {
            $sql = "INSERT INTO alunos (nome_completo, nome_soci, data_nas, nome_respon, numero, email, data_cadastro) 
                    VALUES (:nome_completo, :nome_soci, :data_nas, :nome_respon, :numero, :email, :data_cadastro)";
            $stmt = $this->conn->prepare($sql);
    
            try{
                $stmt->bindValue(':nome_completo', $aluno['nome_completo'], PDO::PARAM_STR);
                $stmt->bindValue(':nome_soci', $aluno['nome_social']?? null, PDO::PARAM_STR);
                $stmt->bindValue(':data_nas', $aluno['data_nascimento'], PDO::PARAM_STR);
                $stmt->bindValue(':nome_respon', $aluno['nome_responsavel']?? null, PDO::PARAM_STR);
                $stmt->bindValue(':numero', $aluno['telefone'], PDO::PARAM_STR);
                $stmt->bindValue(':email', $aluno['email']?? null, PDO::PARAM_STR);
                $stmt->bindValue(':data_cadastro', $this->data->getDate('y-m-d'), PDO::PARAM_STR);

                $stmt->execute();
                return (int)$this->conn->lastInsertId();
            }catch (PDOException $e) {
                
                throw new PDOException("Erro ao inserir aluno: " . $e->getMessage(), $e->getCode());
            }
        }

        public function selectAlunosALL() {
            $sql = "SELECT * FROM alunos ORDER BY nome_completo";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function selectAlunosBYnameLIKE($string) {
            $sql = "SELECT * FROM alunos WHERE nome_completo LIKE :nome ORDER BY nome_completo";
            $stmt = $this->conn->prepare($sql);

            try {
                $stmt->bindValue(':nome', '%' . $string . '%', PDO::PARAM_STR);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException("Erro ao buscar alunos: " . $e->getMessage(), $e->getCode());
            }
        }

        public function selectAlunoBYID($id){
            $sql = "SELECT * FROM alunos WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);

            try{ 
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }catch (PDOException $e) {
                throw new PDOException("Erro ao buscar aluno: " . $e->getMessage(), $e->getCode());
            }
        }

        public function update($id, $dados) {
            $sql = "UPDATE alunos SET 
                    nome_completo = :nome_completo,
                    nome_soci = :nome_soci,
                    data_nas = :data_nas,
                    nome_respon = :nome_respon,
                    numero = :numero,
                    email = :email
                    WHERE id = :id";
            
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':nome_completo', $dados['nome_completo'], PDO::PARAM_STR);
                $stmt->bindValue(':nome_soci', $dados['nome_social'] ?? null, PDO::PARAM_STR);
                $stmt->bindValue(':data_nas', $dados['data_nascimento'], PDO::PARAM_STR);
                $stmt->bindValue(':nome_respon', $dados['nome_responsavel'] ?? null, PDO::PARAM_STR);
                $stmt->bindValue(':numero', $dados['telefone'], PDO::PARAM_STR);
                $stmt->bindValue(':email', $dados['email'] ?? null, PDO::PARAM_STR);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                
                return $stmt->execute();
            } catch (PDOException $e) {
                throw new PDOException("Erro ao atualizar aluno: " . $e->getMessage(), $e->getCode());
            }
        }

        public function delete($id) {
            $sql = "DELETE FROM alunos WHERE id = :id";
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (PDOException $e) {
                throw new PDOException("Erro ao deletar aluno: " . $e->getMessage(), $e->getCode());
            }
        }
    }


        