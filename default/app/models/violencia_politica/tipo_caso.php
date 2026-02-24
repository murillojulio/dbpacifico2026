<?php
/**
 *
 * Descripcion: Clase que gestiona los tipos de caso
 *
 * @category
 * @package     Models
 */


class TipoCaso extends ActiveRecord {   
     //protected $logger = true;
    /**
     * Constante para definir un tipo de caso como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un tipo de caso como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->has_many('caso');
    }
    
    
    /**
     * Método para obtener el listado de los departamentos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoCasoDBS() {                   
        $columns = 'tipo_caso.*';  
        $conditions = 'tipo_caso.id IS NOT NULL AND tipo_caso.estado = 1'; 
        $order = 'tipo_caso.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }   
    
}
?>

