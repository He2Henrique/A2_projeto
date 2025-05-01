<?php
namespace App\Core;
use mysqli;
use Exception;

class DatabaseManager {
	private static $instance = null;
	private $connection;

	private $host = 'localhost';
	private $username = 'root';
	private $password = '';
	private $database = 'instituicao';

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

		$this->connection->set_charset("utf8mb4");

		// Não cria mais a tabela, pois ela já existe e você não pode alterá-la
	}

	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new DatabaseManager();
		}
		return self::$instance;
	}

	public function getConnection() {
		return $this->connection;
	}

	public function insert($table, $data) {
		$columns = implode(", ", array_keys($data));
		$placeholders = implode(", ", array_fill(0, count($data), "?"));
		$values = array_values($data);

		$sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
		$stmt = $this->connection->prepare($sql);

		if ($stmt === false) {
			die("Erro na preparação: " . $this->connection->error);
		}

		$types = str_repeat('s', count($values));
		$stmt->bind_param($types, ...$values);

		$result = $stmt->execute();
		$stmt->close();

		return $result;
	}

	public function select($table, $conditions = [], $columns = '*') {
		$sql = "SELECT $columns FROM $table";
		$values = [];

		if (!empty($conditions)) {
			$where = [];

			foreach ($conditions as $key => $value) {
				if (strpos($key, ' ') !== false) {
					$where[] = "$key ?";
				} else {
					$where[] = "$key = ?";
				}
				$values[] = $value;
			}

			$sql .= " WHERE " . implode(" AND ", $where);
		}

		$stmt = $this->connection->prepare($sql);

		if ($stmt === false) {
			die("Erro na preparação: " . $this->connection->error);
		}

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

	public function selectJoin($table1, $table2, $on, $conditions = [], $columns = '*') {
		$sql = "SELECT $columns FROM $table1 INNER JOIN $table2 ON $on";
		$values = [];

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

		$types = str_repeat('s', count($values));
		$stmt->bind_param($types, ...$values);

		$result = $stmt->execute();
		$stmt->close();

		return $result;
	}

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

		$types = str_repeat('s', count($values));
		$stmt->bind_param($types, ...$values);

		$result = $stmt->execute();
		$stmt->close();

		return $result;
	}

	public function lastRecord($table, $id) {
		$sql = "SELECT * FROM $table ORDER BY $id DESC LIMIT 1";
		$result = $this->connection->query($sql);

		if ($result && $result->num_rows > 0) {
			return $result->fetch_assoc();
		} else {
			return null;
		}
	}

	private function __clone() {}
	public function __wakeup() {
		throw new Exception("Cannot unserialize singleton");
	}
	public function __destruct() {
		if ($this->connection) {
			$this->connection->close();
		}
	}
}