<?php
    namespace App\DAO;
    use App\Core\DatabaseManager;
    use PDO;
    use PDOException;

    Class UsuariosDAO{

        private $conn;
        
        public function __construct() {
            $this->conn = DatabaseManager::getInstance()->getConnection();
        }

        public function selectUsuariosBYemail($email){
             $sql = "SELECT * from usuarios WHERE email = :email";
            $stmt = $this->conn->prepare($sql);

            try {
                $stmt->bindValue(':email', $email, PDO::PARAM_STR);
                
               
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                throw new PDOException("Erro ao procurar usuario: " . $e->getMessage(), $e->getCode());
            }
        }

        public function cadastrando_usuario($form_post) : bool {
            $sql = "INSERT INTO usuarios (email, senha, nome) VALUES (:email, :senha, :nome)";
            $stmt = $this->conn->prepare($sql);

            //criar um hash
            $hash = password_hash($form_post['senha'], PASSWORD_DEFAULT);

            try{
                $stmt->bindValue(':email', $form_post['email'], PDO::PARAM_STR);
                $stmt->bindValue(':senha', $hash, PDO::PARAM_STR);
                $stmt->bindValue(':nome', $form_post['nome'], PDO::PARAM_STR);

                
                return $stmt->execute();
            }catch (PDOException $e) {
                
                throw new PDOException("Erro ao inserir Usuario: " . $e->getMessage(), $e->getCode());
            }
        }
    }