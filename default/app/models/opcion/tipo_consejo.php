<?php

/**
 * Modelo TipoConsejo
 * 
 * @category App
 * @package Models
 */
class TipoConsejo extends ActiveRecord {
    
      
    /**
     * Constante para definir un tipo consejo como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un tipo consejo como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        $this->has_many('consejo');
        //$this->has_many('recurso_perfil');
    }
    
    
    /**
     * Método para obtener el listado de los tipo consejos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoConsejo($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'tipo_consejo.nombre ASC, tipo_consejo.nombre ASC',
                'DESC' => 'tipo_consejo.nombre DESC, tipo_consejo.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT tipo_consejo.*, 
                (SELECT COUNT(consejo.id) FROM consejo WHERE tipo_consejo.id = consejo.tipo_consejo_id) AS cant_sector
                FROM tipo_consejo WHERE tipo_consejo.id IS NOT NULL GROUP BY tipo_consejo.id ORDER BY '.$order);
        
        
        
        
    }
    
     /**
     * Método para crear/modificar un objeto de base de datos
     * 
     * @param string $medthod: create, update
     * @param array $data: Data para autocargar el modelo
     * @param array $data_poblacion: Data para autocargar el modelo poblacion
     * @param array $optData: Data adicional para autocargar
     * 
     * return object ActiveRecord
     */
    public static function setTipoConsejo($method, $data, $optData=null) {        
        $obj = new TipoConsejo($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new TipoConsejo();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != TipoConsejo::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = TipoConsejo::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un tipo consejo registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el tipo consejo  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del tipo consejo $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    /**
     * Método para obtener el listado de los tipos de consejos
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoConsejoDBS() {                   
        $columns = 'tipo_consejo.*';  
        $conditions = 'tipo_consejo.id IS NOT NULL AND tipo_consejo.estado = 1'; 
        $order = 'tipo_consejo.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>