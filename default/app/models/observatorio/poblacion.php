<?php
/**
 *
 * Descripcion: Clase que gestiona las poblaciones observados
 *
 * @category
 * @package     Models
 */

class Poblacion extends ActiveRecord {
   
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        $this->belongs_to('departamento');
        $this->has_many('comunidad');
    }
    
    
    public static function getPoblacion($nombre_id, $value)
    {
        $obj = new Poblacion();        
        $obj->find_first($nombre_id."=".$value);
        
        return $obj;
    }
    
    /**
     * Método para obtener el listado de los departamentos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoPoblacion($estado='todos', $order='', $page=0) {                   
        $columns = 'departamento.*';        
        $join = '';
        $conditions = 'departamento.id IS NOT NULL'; 

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'departamento.nombre ASC, departamento.nombre ASC',
                'DESC' => 'departamento.nombre DESC, departamento.nombre DESC'
            )        ));
        $group = 'departamento.nombre';
        if($page) {            
            return $this->paginated("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order", "page: $page");
        }
        
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order");
        
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
    public static function setPoblacion($method, $data, $campo, $id) {        
        $obj = new Poblacion($data); //Se carga los datos con los de las tablas      
        $obj->$campo = $id;       
        return ($obj->$method()) ? $obj : FALSE;
    }
    
    
}
?>

