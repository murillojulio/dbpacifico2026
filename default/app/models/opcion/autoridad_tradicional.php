<?php

/**
 * Modelo AutoridadTradicional
 * 
 * @category App
 * @package Models
 */
class AutoridadTradicional extends ActiveRecord {
    
      
    /**
     * Constante para definir un tipo de autoridad tradicional como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un tipo de autoridad tradicional como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        $this->has_many('cabildo_autoridad_tradicional');
        //$this->has_many('recurso_perfil');
    }
    
    
    /**
     * Método para obtener el listado de los tipo autoridad_tradicionals observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoAutoridadTradicional($tipo_autoridad_tradicional_id, $estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'autoridad_tradicional.nombre ASC, autoridad_tradicional.nombre ASC',
                'DESC' => 'autoridad_tradicional.nombre DESC, autoridad_tradicional.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT autoridad_tradicional.*, tipo_autoridad_tradicional.nombre AS tipo_autoridad,
                (SELECT COUNT(cabildo_autoridad_tradicional.id) FROM cabildo_autoridad_tradicional WHERE autoridad_tradicional.id = cabildo_autoridad_tradicional.autoridad_tradicional_id) AS cant_sector
                FROM autoridad_tradicional 
                INNER JOIN tipo_autoridad_tradicional ON tipo_autoridad_tradicional.id = autoridad_tradicional.tipo_autoridad_tradicional_id 
                WHERE autoridad_tradicional.id IS NOT NULL ORDER BY autoridad_tradicional.tipo_autoridad_tradicional_id ASC');
        
        
        
        
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
    public static function setAutoridadTradicional($method, $data, $optData=null) {        
        $obj = new AutoridadTradicional($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new AutoridadTradicional();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != AutoridadTradicional::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = AutoridadTradicional::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un tipo de autoridad tradicional registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado un tipo de autoridad tradicional  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de un tipo de autoridad tradicional $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    /**
     * Método para obtener el listado de los autoridad_tradicionals
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getAutoridadTradicionalByTipoAutoridadTradicionalIdDBS($tipo_autoridad_tradicional_id) {                   
        $columns = 'autoridad_tradicional.*';  
        $conditions = 'autoridad_tradicional.id IS NOT NULL AND autoridad_tradicional.estado = 1 AND autoridad_tradicional.tipo_autoridad_tradicional_id = '.$tipo_autoridad_tradicional_id; 
        $order = 'autoridad_tradicional.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>