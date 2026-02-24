<?php

/**
 * Modelo TipoProyectoCooperacion
 * 
 * @category App
 * @package Models
 */
class TipoProyectoCooperacion extends ActiveRecord {
    
      
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

        $this->has_many('cooperacion_tipo_proyecto_cooperacion');        
    }
    
    
    /**
     * Método para obtener el listado de los tipo proyecto cooperacions observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoProyectoCooperacion($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'tipo_proyecto_cooperacion.nombre ASC, tipo_proyecto_cooperacion.nombre ASC',
                'DESC' => 'tipo_proyecto_cooperacion.nombre DESC, tipo_proyecto_cooperacion.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT tipo_proyecto_cooperacion.*, 
                (SELECT COUNT(cooperacion.id) FROM cooperacion WHERE tipo_proyecto_cooperacion.id = cooperacion.tipo_proyecto_cooperacion_id) AS cant_sector
                FROM tipo_proyecto_cooperacion WHERE tipo_proyecto_cooperacion.id IS NOT NULL GROUP BY tipo_proyecto_cooperacion.id ORDER BY '.$order);
        
        
        
        
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
    public static function setTipoProyectoCooperacion($method, $data, $optData=null) {        
        $obj = new TipoProyectoCooperacion($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new TipoProyectoCooperacion();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != TipoProyectoCooperacion::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = TipoProyectoCooperacion::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un tio registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el tipo proyecto cooperacion  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del tipo proyecto cooperacion $obj->nombre");
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
    public function getListadoTipoProyectoCooperacionDBS() {                   
        $columns = 'tipo_proyecto_cooperacion.*';  
        $conditions = 'tipo_proyecto_cooperacion.id IS NOT NULL AND tipo_proyecto_cooperacion.estado = 1'; 
        $order = 'tipo_proyecto_cooperacion.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>