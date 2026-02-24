<?php

/**
 * Modelo OperadorCooperacion
 * 
 * @category App
 * @package Models
 */
class OperadorCooperacion extends ActiveRecord {
    
      
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

        $this->has_many('cooperacion_operador_cooperacion');
        //$this->has_many('recurso_perfil');
    }
    
    
    /**
     * Método para obtener el listado de los operador cooperacións observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoOperadorCooperacion($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'operador_cooperacion.nombre ASC, operador_cooperacion.nombre ASC',
                'DESC' => 'operador_cooperacion.nombre DESC, operador_cooperacion.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT operador_cooperacion.*, 
                (SELECT COUNT(cultivo_ilicito.id) FROM cultivo_ilicito WHERE operador_cooperacion.id = cultivo_ilicito.operador_cooperacion_id) AS cant_sector
                FROM operador_cooperacion WHERE operador_cooperacion.id IS NOT NULL GROUP BY operador_cooperacion.id ORDER BY '.$order);
        
        
        
        
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
    public static function setOperadorCooperacion($method, $data, $optData=null) {        
        $obj = new OperadorCooperacion($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new OperadorCooperacion();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != OperadorCooperacion::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = OperadorCooperacion::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un operador cooperación registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el operador cooperación  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del operador cooperación $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    /**
     * Método para obtener el listado de los tipos de cultivo_ilicitos
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoOperadorCooperacionDBS() {                   
        $columns = 'operador_cooperacion.*';  
        $conditions = 'operador_cooperacion.id IS NOT NULL AND operador_cooperacion.estado = 1'; 
        $order = 'operador_cooperacion.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>