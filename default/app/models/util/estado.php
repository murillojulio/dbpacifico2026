<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Estado extends ActiveRecord
{       
    /**
     * Constante para definir un registro como publico
     */
    const PUBLICO = 1;    
    /**
     * Constante para definir un registro como privado
     */
    const PRIVADO = 2;
     /**
     * Constante para definir la informacion de un registro como completa
     */
    const COMPLETADO = 2;    
    /**
     * Constante para definir la informacion de un registro como borrador
     */
    const BORRADOR = 1;
}
?>
