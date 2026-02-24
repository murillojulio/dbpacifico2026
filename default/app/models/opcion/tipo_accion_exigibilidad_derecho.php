<?php

/**
 * Modelo TipoAccionExigibilidadDerecho
 * 
 * @category App
 * @package Models
 */
class TipoAccionExigibilidadDerecho extends ActiveRecord {
    
      
    /**
     * Constante para definir un tipo accion exigibilidad derecho como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un tipo accion exigibilidad derecho como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        $this->has_many('accion_exigibilidad_derecho');
        //$this->has_many('recurso_perfil');
    }
    
    
    /**
     * Método para obtener el listado de los tipo accion exigibilidad derechos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoAccionExigibilidadDerecho($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'tipo_accion_exigibilidad_derecho.nombre ASC, tipo_accion_exigibilidad_derecho.nombre ASC',
                'DESC' => 'tipo_accion_exigibilidad_derecho.nombre DESC, tipo_accion_exigibilidad_derecho.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT tipo_accion_exigibilidad_derecho.*, 
                (SELECT COUNT(accion_exigibilidad_derecho.id) FROM accion_exigibilidad_derecho WHERE tipo_accion_exigibilidad_derecho.id = accion_exigibilidad_derecho.tipo_accion_exigibilidad_derecho_id) AS cant_sector
                FROM tipo_accion_exigibilidad_derecho WHERE tipo_accion_exigibilidad_derecho.id IS NOT NULL GROUP BY tipo_accion_exigibilidad_derecho.id ORDER BY '.$order);
        
        
        
        
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
    public static function setTipoAccionExigibilidadDerecho($method, $data, $optData=null) {        
        $obj = new TipoAccionExigibilidadDerecho($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new TipoAccionExigibilidadDerecho();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != TipoAccionExigibilidadDerecho::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = TipoAccionExigibilidadDerecho::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un tipo accion exigibilidad derecho registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el tipo accion exigibilidad derecho  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del tipo accion exigibilidad derecho $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    /**
     * Método para obtener el listado de los tipos de accion exigibilidad derecho
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoAccionExigibilidadDerechoDBS() {                   
        $columns = 'tipo_accion_exigibilidad_derecho.*';  
        $conditions = 'tipo_accion_exigibilidad_derecho.id IS NOT NULL AND tipo_accion_exigibilidad_derecho.estado = 1'; 
        $order = 'tipo_accion_exigibilidad_derecho.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>