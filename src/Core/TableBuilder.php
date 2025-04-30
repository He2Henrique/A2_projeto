<?php
namespace App\Core;
//criando classe de serviço para criar tabelas HTML
class TableBuilder {
    private string $header = '';
    private string $corpo = '';

    
    public function criar_Header(array $colunas, ?string $css_class = null) {
        $html = '<thead' . ($css_class == null ? '' : ' class="' . $css_class . '"') . '><tr>';
        foreach ($colunas as $coluna) {
            $html .= '<th>' . htmlspecialchars($coluna) . '</th>';
        }
        $html .= '</tr></thead>';

        $this->header = $html;
        return $html;
        
    }

    
    public function definir_corpo(array $matriz, int $nao_completa = 0) {
        $html = '<tbody>';
        foreach ($matriz as $coluna) {
            $html .= '<tr>';
            foreach ($coluna as $celula) {
                // Verifica se o conteúdo contém HTML
                if (strip_tags($celula) != $celula) {
                    $html .= '<td>' . $celula . '</td>'; // Não escapa HTML
                } else {
                    $html .= '<td>' . htmlspecialchars($celula) . '</td>'; // Escapa texto normal
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $this->corpo = $html;
        return $html;
    }

    public function criar_tabela(?string $css_class = null) {
        return '<table class="' . ($css_class == null ? '' : ' ' . $css_class) . '">' . $this->header . $this->corpo . '</table>';
    }

    function CriarButao(string $link, string $texto, ?string $css_class = null) {
        return '<a href="' . htmlspecialchars($link) . '" class="' . ($css_class == null ? '' : ' ' . $css_class) . '">' . htmlspecialchars($texto) . '</a>';
    }

}