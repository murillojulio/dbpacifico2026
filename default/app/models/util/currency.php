<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Currency
{
    /**
     * Recibe una cadena como: 745.000,55 y retorna una como: 745000.55.
     *
     * @param string $valor Cadena como: 745.000,55.
     * @return string $parse con retorna una como: 745000.55.
     */
    public static function comaApunto($valor)
    {
        $parse = str_replace(",", ".", str_replace(".", "", $valor));        
        return $parse;
    }
    
    
    /**
     * Recibe una cadena como: 745.000 y retorna una como: 745000
     *
     * @param string $valor Cadena como: 745.000
     * @return string $parse con retorna una como: 745000
     */
    public static function sinpunto($valor)
    {
        $parse = str_replace(".", "", $valor);  
        return $parse;
    }
}
?>
