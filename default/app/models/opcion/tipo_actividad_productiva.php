<?php

/**
 * Modelo TipoActividadProductiva
 * 
 * @category App
 * @package Models
 */
class TipoActividadProductiva extends ActiveRecord {    
      
    /**
     * Constante para definir un tipo actividad productiva como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un tipo actividad productiva como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        $this->has_many('iniciativa_empresarial');
        //$this->has_many('recurso_perfil');
    }
    
    
    /**
     * Método para obtener el listado de los tipo actividad productivas observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoActividadProductiva($estado='todos', $order='', $page=0) {  
        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'tipo_actividad_productiva.nombre ASC, tipo_actividad_productiva.nombre ASC',
                'DESC' => 'tipo_actividad_productiva.nombre DESC, tipo_actividad_productiva.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT tipo_actividad_productiva.*, 
                (SELECT COUNT(iniciativa_empresarial.id) FROM iniciativa_empresarial WHERE tipo_actividad_productiva.id = iniciativa_empresarial.tipo_actividad_productiva_id) AS cant_sector
                FROM tipo_actividad_productiva WHERE tipo_actividad_productiva.id IS NOT NULL GROUP BY tipo_actividad_productiva.id ORDER BY '.$order);
             
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
    public static function setTipoActividadProductiva($method, $data, $optData=null) {        
        $obj = new TipoActividadProductiva($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new TipoActividadProductiva();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != TipoActividadProductiva::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = TipoActividadProductiva::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un tipo actividad productiva registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el tipo actividad productiva  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del tipo actividad productiva $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    /**
     * Método para obtener el listado de los tipos de iniciativa empresarial derecho
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoActividadProductivaDBS($excluir_extraccion) {                   
        $columns = 'tipo_actividad_productiva.*';  
        $conditions = 'tipo_actividad_productiva.id IS NOT NULL AND tipo_actividad_productiva.estado = 1'; 
        if($excluir_extraccion == 2){
        $conditions .= " AND tipo_actividad_productiva.id != 2"; 
        }
        $order = 'tipo_actividad_productiva.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>