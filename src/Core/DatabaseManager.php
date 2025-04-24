<?php
namespace App\Core;
use mysqli; // Importando a classe mysqli para conexão com o banco de dados
use Exception; // Importando a classe Exception para tratamento de erros
// Criando um singleton para gerenciar a conexão com o banco de dados
// singleton é um padrão de projeto que garante que uma classe tenha...
// Apenas uma instância e fornece um ponto de acesso global a ela.
	class DatabaseManager {
		// Instância única da classe
		private static $instance = null;
		
		// Conexão com o banco de dados
		private $connection;
		
		// Configurações do banco de dados
		private $host = 'localhost';
		private $username = 'root';
		private $password = '';
		private $database = 'instituicao_ensino';
		
		// Construtor privado para prevenir instanciação direta
		private function __construct() {
			$this->connection = new mysqli(
				$this->host, 
				$this->username, 
				$this->password, 
				$this->database
			);
			
			if ($this->connection->connect_error) {
				die("Falha na conexão: " . $this->connection->connect_error);
			}
			
			// Configurar charset se necessário
			$this->connection->set_charset("utf8mb4");
		}
		
		
		// Método para obter a instância única, metodado conectar com a instância
		public static function getInstance() {
			if (self::$instance === null) {
				self::$instance = new DatabaseManager();
			}
			return self::$instance;
		}
		
		// Método para obter a conexão
		public function getConnection() {
			return $this->connection;
		}
		
		// Método para executar consultas SQL (opcional, se necessário)
		public function query($sql, $params = []) {
			$stmt = $this->connection->prepare($sql);
			if ($stmt === false) {
				throw new Exception("Erro ao preparar a consulta: " . $this->connection->error);
			}
			$result = $stmt->execute($params);
			return $result;
		}

		
		// Para inserir data use
		//Exemplo de uso:
		/* $insertData = [
			'nome' => 'João Silva',
			'mail' => 'joao@example.com',
			'idade' => 30
		];*/
		/*$var = [
		'nome_coluna'=> 'valor',...] */
		
		// Método para inserir dados
		public function insert($table, $data) {
			$columns = implode(", ", array_keys($data));
			$placeholders = implode(", ", array_fill(0, count($data), "?"));
			$values = array_values($data);
			
			$sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
			$stmt = $this->connection->prepare($sql);
			
			if ($stmt === false) {
				die("Erro na preparação: " . $this->connection->error);
			}
			
			// Tipos de parâmetros (s = string, i = integer, d = double, b = blob)
			$types = str_repeat('s', count($values));
			$stmt->bind_param($types, ...$values);
			
			$result = $stmt->execute();
			$stmt->close();
			
			return $result;
		}
		
		// Método para selecionar dados
		
		public function select($table, $conditions = [], $columns = '*') {
			$sql = "SELECT $columns FROM $table";
			
			if (!empty($conditions)) {
				$where = [];
				$values = [];
				
				foreach ($conditions as $key => $value) {
					if (strpos($key, ' ') !== false) {
						// Permitir operadores como "LIKE", ">", "<", etc.
						$where[] = "$key ?";
					} else {
						$where[] = "$key = ?";
					}
					$values[] = $value;
				}
				
				$sql .= " WHERE " . implode(" AND ", $where);
			}
			
			$stmt = $this->connection->prepare($sql);
			
			if (!empty($conditions)) {
				$types = str_repeat('s', count($values));
				$stmt->bind_param($types, ...$values);
			}
			
			$stmt->execute();
			$result = $stmt->get_result();
			$data = $result->fetch_all(MYSQLI_ASSOC);
			$stmt->close();
			
			return $data;
		}

		// metodo para selcionar com join
		public function selectJoin($table1, $table2, $on, $conditions = [], $columns = '*') {
			$sql = "SELECT $columns FROM $table1 INNER JOIN $table2 ON $on";
			
			if (!empty($conditions)) {
				$where = [];
				$values = [];
				
				foreach ($conditions as $key => $value) {
					$where[] = "$key = ?";
					$values[] = $value;
				}
				
				$sql .= " WHERE " . implode(" AND ", $where);
			}
			
			$stmt = $this->connection->prepare($sql);
			
			if (!empty($conditions)) {
				$types = str_repeat('s', count($values));
				$stmt->bind_param($types, ...$values);
			}
			
			$stmt->execute();
			$result = $stmt->get_result();
			$data = $result->fetch_all(MYSQLI_ASSOC);
			$stmt->close();
			
			return $data;
		}


		// Método para atualizar dados
		public function update($table, $data, $conditions) {
			$set = [];
			$values = [];
			
			foreach ($data as $key => $value) {
				$set[] = "$key = ?";
				$values[] = $value;
			}
			
			$sql = "UPDATE $table SET " . implode(", ", $set);
			
			if (!empty($conditions)) {
				$where = [];
				
				foreach ($conditions as $key => $value) {
					$where[] = "$key = ?";
					$values[] = $value;
				}
				
				$sql .= " WHERE " . implode(" AND ", $where);
			}
			
			$stmt = $this->connection->prepare($sql);
			
			if ($stmt === false) {
				die("Erro na preparação: " . $this->connection->error);
			}
			
			// Tipos de parâmetros (s = string, i = integer, d = double, b = blob)
			$types = str_repeat('s', count($values));
			$stmt->bind_param($types, ...$values);
			
			$result = $stmt->execute();
			$stmt->close();
			
			return $result;
		}

		// Método para deletar dados
		public function delete($table, $conditions) {
			$where = [];
			$values = [];
			
			foreach ($conditions as $key => $value) {
				$where[] = "$key = ?";
				$values[] = $value;
			}
			
			$sql = "DELETE FROM $table WHERE " . implode(" AND ", $where);
			$stmt = $this->connection->prepare($sql);
			
			if ($stmt === false) {
				die("Erro na preparação: " . $this->connection->error);
			}
			
			// Tipos de parâmetros (s = string, i = integer, d = double, b = blob)
			$types = str_repeat('s', count($values));
			$stmt->bind_param($types, ...$values);
			
			$result = $stmt->execute();
			$stmt->close();
			
			return $result;
		}

		public function lastRecord($table, $id) {
			$sql = "SELECT * FROM $table ORDER BY $id DESC LIMIT 1";
			$result = $this->connection->query($sql);
			
			if ($result->num_rows > 0) {
				return $result->fetch_assoc();
			} else {
				return null;
			}
		}

		
		
		// Prevenir clonagem da instância
		private function __clone() {
			//No padrão Singleton, o método __clone() é deixado vazio (ou declarado como privado) por
			//uma razão fundamental: para prevenir a clonagem do objeto, o que violaria o princípio 
			//central do Singleton que garante que apenas 
			//uma única instância da classe exista em toda a aplicação.
		 }
		
		// Prevenir desserialização da instância
		public function __wakeup() {
			throw new Exception("Cannot unserialize singleton");
		}
		
		// Fechar conexão quando o objeto for destruído
		public function __destruct() {
			if ($this->connection) {
				$this->connection->close();
			}
		}
	}

?>