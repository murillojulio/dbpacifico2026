<?php
/**
 *
 * Descripcion: Clase que gestiona los cabildos
 *
 * @category
 * @package     Models
 */

class PlanDeVida extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    //public $logger = FALSE;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() 
    {
        $this->belongs_to('territorio'); 
    }
    
    /**
     * Callback que se ejecuta antes de guardar/modificar
     */
    public function before_save() {
       
    }
    
    /**
     * Callback que se ejecuta después de guardar/modificar un perfil
     */
    protected function after_save() {
        
    }
    

}
?>
