<?php


date_default_timezone_set('America/Sao_Paulo');
class ProcessData{
   
    private $SEMANA = [
        1 => 'Segunda',
        2 => 'Terça',
        3 => 'Quarta',
        4 => 'Quinta',
        5 => 'Sexta',
        6 => 'Sábado'
    ];

    public $DATA_YMD;
    public $DATA_DMY;
    public $DIA_SEM;
    public $HORARIO;

    public function __construct() {
        $this->DATA_YMD = date('Y-m-d'); // para o banco de dados
        $this->DATA_DMY = date('d/m/Y', strtotime($this->DATA_YMD)); // para exibir no front-end
        $this->DIA_SEM = date('w', strtotime($this->DATA_YMD)); // 0 = domingo, 1 = segunda, ..., 6 = sábado
        $this->HORARIO = date('H:i:s'); // Horário atual no formato HH:MM:SS
    }
    function getDiaSemana(int $dia): string{
        return $this->SEMANA[$dia] ?? 'Dia inválido';
    }


    //validar CPF
    function validarCPF(string $cpf): bool{
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }
        
        // Calcula dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        
        return true;
    }
    
    function validarTelefone(string $telefone): bool{
        // Remove caracteres não numéricos
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        
        // Verifica se o telefone tem 10 ou 11 dígitos (DDD + número)
        return (strlen($telefone) == 10 || strlen($telefone) == 11);
    }

    //exemplo cpf de 000.000.000-00 para 00000000000
    function ApenasNumeros(string $string): string{
        $string_nums = preg_replace('/[^0-9]/', '', $string);
        
        return $string_nums;
    }

    function Idade(string $data_nasc): int{
        $data_nasc = DateTime::createFromFormat('Y-m-d', $data_nasc);
        $hoje = new DateTime();
        
        return $hoje->diff($data_nasc)->y;
    }
    
}