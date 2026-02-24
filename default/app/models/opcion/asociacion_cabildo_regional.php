<?php

/**
 * Modelo AsociacionCabildoRegional
 * 
 * @category App
 * @package Models
 */
class AsociacionCabildoRegional extends ActiveRecord {
    
      
    /**
     * Constante para definir un AsociacionCabildoRegional como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un AsociacionCabildoRegional como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
       $this->has_many('cabildo');
    }
        
    /**
     * Método para obtener el listado de los tipo cabildos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoAsociacionCabildoRegional($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'asociacion_cabildo_regional.nombre ASC, asociacion_cabildo_regional.nombre ASC',
                'DESC' => 'asociacion_cabildo_regional.nombre DESC, asociacion_cabildo_regional.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT asociacion_cabildo_regional.*, 
                (SELECT COUNT(cabildo.id) FROM cabildo WHERE asociacion_cabildo_regional.id = cabildo.asociacion_cabildo_regional_id) AS cant_sector
                FROM asociacion_cabildo_regional WHERE asociacion_cabildo_regional.id IS NOT NULL GROUP BY asociacion_cabildo_regional.id ORDER BY '.$order);
        
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
    public static function setAsociacionCabildoRegional($method, $data, $optData=null) {        
        $obj = new AsociacionCabildoRegional($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new AsociacionCabildoRegional();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != AsociacionCabildoRegional::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = AsociacionCabildoRegional::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe una Asociacion de Cabildos Regional registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la Asociacion de Cabildos Regional  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de la Asociacion de Cabildos Regional $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    
    /**
     * Método para obtener el listado de los AsociacionCabildoRegional
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoAsociacionCabildoRegionalDBS() {                   
        $columns = 'asociacion_cabildo_regional.*';  
        $conditions = 'asociacion_cabildo_regional.id IS NOT NULL AND asociacion_cabildo_regional.estado = 1'; 
        $order = 'asociacion_cabildo_regional.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>