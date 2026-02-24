<?php

/**
 * Modelo TipoAutoridadTradicional
 * 
 * @category App
 * @package Models
 */
class TipoAutoridadTradicional extends ActiveRecord {    
      
    /**
     * Constante para definir un tipo autoridad tradicional como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un tipo autoridad tradicional como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        $this->has_many('autoridad_tradicional');
        //$this->has_many('recurso_perfil');
    }
    
    
    /**
     * Método para obtener el listado de los tipo autoridad tradicionals observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoAutoridadTradicional($estado='todos', $order='', $page=0) {  
        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'tipo_autoridad_tradicional.nombre ASC, tipo_autoridad_tradicional.nombre ASC',
                'DESC' => 'tipo_autoridad_tradicional.nombre DESC, tipo_autoridad_tradicional.nombre DESC'
            )));        
       
        return $this->paginated_by_sql('SELECT tipo_autoridad_tradicional.* FROM tipo_autoridad_tradicional WHERE tipo_autoridad_tradicional.id IS NOT NULL GROUP BY tipo_autoridad_tradicional.id ORDER BY '.$order);             
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
    public static function setTipoAutoridadTradicional($method, $data, $optData=null) {        
        $obj = new TipoAutoridadTradicional($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new TipoAutoridadTradicional();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != TipoAutoridadTradicional::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = TipoAutoridadTradicional::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un tipo autoridad tradicional registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el tipo autoridad tradicional  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del tipo autoridad tradicional $obj->nombre");
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
    public function getListadoTipoAutoridadTradicionalDBS() {                   
        $columns = 'tipo_autoridad_tradicional.*';  
        $conditions = 'tipo_autoridad_tradicional.id IS NOT NULL AND tipo_autoridad_tradicional.estado = 1'; 
        $order = 'tipo_autoridad_tradicional.nombre DESC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>