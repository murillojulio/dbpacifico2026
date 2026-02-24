<?php
/**
 *
 * Descripcion: Clase que gestiona las fuentes de la informacion
 *
 * @category
 * @package     Models
 */


class TituladoNo extends ActiveRecord {
   
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        $this->belongs_to('territorio');       
    }
    
     public function getTituladoNoByTerritorioId($territorio_id) 
    {                   
        $columns = 'titulado_no.*';   
        $conditions = 'titulado_no.territorio_id='.$territorio_id;  
        return $this->find_first("columns: $columns", "conditions: $conditions");        
    }
    
    /**
     * Método para crear/modificar un objeto de base de datos
     * 
     * @param string $medthod: create, update
     * @param array $data: Data para autocargar el modelo
     * @param array $optData: Data adicional para autocargar
     * 
     * return object ActiveRecord
     */
    public static function setTituladoNo($method, $data, $territorio_id, $optData=null) {        
        $obj = new TituladoNo($data); //Se carga los datos con los de las tablas  
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }  
        $obj->territorio_id = $territorio_id;
        
        return ($obj->$method()) ? $obj : FALSE;
    }
    
    
}
?>

