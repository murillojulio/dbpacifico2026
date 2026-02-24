<?php

/**
 * Modelo CampoAccion
 * 
 * @category App
 * @package Models
 */
class CampoAccion extends ActiveRecord {
    
      
    /**
     * Constante para definir un campo_accion como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un campo_accion como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        //$this->has_many('usuario');
        //$this->has_many('recurso_perfil');
    }
    
    
    /**
     * Método para obtener el listado de los campo_accions observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoCampoAccion($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'campo_accion.nombre ASC, campo_accion.nombre ASC',
                'DESC' => 'campo_accion.nombre DESC, campo_accion.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT campo_accion.*, 
                (SELECT COUNT(organizacion_has_campo_accion.id) FROM organizacion_has_campo_accion WHERE organizacion_has_campo_accion.campo_accion_id = campo_accion.id) AS cant_sector
                FROM campo_accion WHERE campo_accion.id IS NOT NULL GROUP BY campo_accion.id ORDER BY '.$order);
        
        
        
        
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
    public static function setCampoAccion($method, $data, $optData=null) {        
        $obj = new CampoAccion($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new CampoAccion();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != CampoAccion::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = CampoAccion::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un campo_accion registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el campo_accion  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del campo_accion $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    public function getCampoAccionDBS() {                   
        $columns = 'campo_accion.*';  
        $conditions = 'campo_accion.id IS NOT NULL AND campo_accion.estado = 1'; 
        $order = 'campo_accion.id ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>