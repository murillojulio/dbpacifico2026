<?php

/**
 * Modelo TipoAreaNaturalProtegida
 * 
 * @category App
 * @package Models
 */
class TipoAreaNaturalProtegida extends ActiveRecord {
    
      
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

        $this->has_many('area_natural_protegida');
        //$this->has_many('recurso_perfil');
    }
    
    
    /**
     * Método para obtener el listado de los tipo area_natural_protegidas observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoAreaNaturalProtegida($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'tipo_area_natural_protegida.nombre ASC, tipo_area_natural_protegida.nombre ASC',
                'DESC' => 'tipo_area_natural_protegida.nombre DESC, tipo_area_natural_protegida.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT tipo_area_natural_protegida.*, 
                (SELECT COUNT(area_natural_protegida.id) FROM area_natural_protegida WHERE tipo_area_natural_protegida.id = area_natural_protegida.tipo_area_natural_protegida_id) AS cant_sector
                FROM tipo_area_natural_protegida WHERE tipo_area_natural_protegida.id IS NOT NULL GROUP BY tipo_area_natural_protegida.id ORDER BY '.$order);
        
        
        
        
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
    public static function setTipoAreaNaturalProtegida($method, $data, $optData=null) {        
        $obj = new TipoAreaNaturalProtegida($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new TipoAreaNaturalProtegida();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != TipoAreaNaturalProtegida::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = TipoAreaNaturalProtegida::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un tipo area natural protegida registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el tipo area natural protegida  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del tipo area natural protegida $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    /**
     * Método para obtener el listado de los tipos de area_natural_protegidas
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoAreaNaturalProtegidaDBS() {                   
        $columns = 'tipo_area_natural_protegida.*';  
        $conditions = 'tipo_area_natural_protegida.id IS NOT NULL AND tipo_area_natural_protegida.estado = 1'; 
        $order = 'tipo_area_natural_protegida.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>