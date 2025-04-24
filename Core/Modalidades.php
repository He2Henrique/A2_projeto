<?php

require_once 'DatabaseManager.php';

class Modalidades{

    private static $modalidades = [];

    
    private static function busca_modalidades() {
        $conn = DatabaseManager::getInstance();
        $consulta = $conn->select('modalidades', []);
        foreach ($consulta as $modalidade) {
            self::$modalidades[$modalidade['id']] = $modalidade['nome'] . ' - ' . $modalidade['faixa_etaria'];
        }
    }

    public static function getModalidades() {
        if (empty(self::$modalidades)) {
            self::busca_modalidades();
        }
        return self::$modalidades;
    }

    public function getModalidade_byid($id){
        return self::$modalidades[$id];
    }



}