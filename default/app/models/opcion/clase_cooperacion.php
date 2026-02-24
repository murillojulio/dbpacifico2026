<?php

/**
 * Modelo ClaseCooperacion
 * 
 * @category App
 * @package Models
 */
class ClaseCooperacion extends ActiveRecord {
    
      
    /**
     * Constante para definir un tipo subsidio como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un tipo subsidio como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        $this->has_many('cooperacion');
        //$this->has_many('recurso_perfil');
    }
    
    
    /**
     * Método para obtener el listado de los tipo cooperacions observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoClaseCooperacion($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'clase_cooperacion.nombre ASC, clase_cooperacion.nombre ASC',
                'DESC' => 'clase_cooperacion.nombre DESC, clase_cooperacion.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT clase_cooperacion.*, 
                (SELECT COUNT(cooperacion.id) FROM cooperacion WHERE clase_cooperacion.id = cooperacion.clase_cooperacion_id) AS cant_sector
                FROM clase_cooperacion WHERE clase_cooperacion.id IS NOT NULL GROUP BY clase_cooperacion.id ORDER BY '.$order);
        
        
        
        
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
    public static function setClaseCooperacion($method, $data, $optData=null) {        
        $obj = new ClaseCooperacion($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new ClaseCooperacion();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != ClaseCooperacion::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = ClaseCooperacion::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe una clase cooperacion registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la clase cooperacion  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información la clase cooperacion $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    /**
     * Método para obtener el listado de los tipos de cooperacions
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoClaseCooperacionDBS() {                   
        $columns = 'clase_cooperacion.*';  
        $conditions = 'clase_cooperacion.id IS NOT NULL AND clase_cooperacion.estado = 1'; 
        $order = 'clase_cooperacion.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>