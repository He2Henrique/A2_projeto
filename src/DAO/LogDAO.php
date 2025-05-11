<?php
namespace App\DAO;
use App\Core\DatabaseManager;
use PDO;
use PDOException;

class LogDAO {
    private $conn;

    public function __construct() {
        $this->conn = DatabaseManager::getInstance()->getConnection();
    }

    public function registrarLog($id_usuario, $acao, $tabela_afetada, $registro_id = null, $detalhes = null) {
        $sql = "INSERT INTO logs (id_usuario, acao, tabela_afetada, registro_id, detalhes) 
                VALUES (:id_usuario, :acao, :tabela_afetada, :registro_id, :detalhes)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindValue(':acao', $acao, PDO::PARAM_STR);
            $stmt->bindValue(':tabela_afetada', $tabela_afetada, PDO::PARAM_STR);
            $stmt->bindValue(':registro_id', $registro_id, PDO::PARAM_INT);
            $stmt->bindValue(':detalhes', $detalhes, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new PDOException("Erro ao registrar log: " . $e->getMessage(), $e->getCode());
        }
    }

    public function getLogs($filtro = null) {
        $sql = "SELECT l.*, u.nome as nome_usuario 
                FROM logs l 
                JOIN usuarios u ON l.id_usuario = u.id";
        
        if ($filtro) {
            $sql .= " WHERE l.tabela_afetada = :filtro";
        }
        
        $sql .= " ORDER BY l.data_hora DESC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            if ($filtro) {
                $stmt->bindValue(':filtro', $filtro, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new PDOException("Erro ao buscar logs: " . $e->getMessage(), $e->getCode());
        }
    }
} 