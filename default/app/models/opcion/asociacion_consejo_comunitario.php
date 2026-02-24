<?php

/**
 * Modelo AsociacionConsejoComunitario
 * 
 * @category App
 * @package Models
 */
class AsociacionConsejoComunitario extends ActiveRecord {
    
      
    /**
     * Constante para definir un AsociacionConsejoComunitario como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un AsociacionConsejoComunitario como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
       $this->has_many('consejo');
    }
        
    /**
     * Método para obtener el listado de los tipo consejos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoAsociacionConsejoComunitario($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'asociacion_consejo_comunitario.nombre ASC, asociacion_consejo_comunitario.nombre ASC',
                'DESC' => 'asociacion_consejo_comunitario.nombre DESC, asociacion_consejo_comunitario.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT asociacion_consejo_comunitario.*, 
                (SELECT COUNT(consejo.id) FROM consejo WHERE asociacion_consejo_comunitario.id = consejo.asociacion_consejo_comunitario_id) AS cant_sector
                FROM asociacion_consejo_comunitario WHERE asociacion_consejo_comunitario.id IS NOT NULL GROUP BY asociacion_consejo_comunitario.id ORDER BY '.$order);
        
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
    public static function setAsociacionConsejoComunitario($method, $data, $optData=null) {        
        $obj = new AsociacionConsejoComunitario($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new AsociacionConsejoComunitario();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != AsociacionConsejoComunitario::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = AsociacionConsejoComunitario::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe una Asociacion de Consejos Comunitarios registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la Asociacion de Consejos Comunitarios  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de la Asociacion de Consejos Comunitarios $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    
    /**
     * Método para obtener el listado de los AsociacionConsejoComunitario
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoAsociacionConsejoComunitarioDBS() {                   
        $columns = 'asociacion_consejo_comunitario.*';  
        $conditions = 'asociacion_consejo_comunitario.id IS NOT NULL AND asociacion_consejo_comunitario.estado = 1'; 
        $order = 'asociacion_consejo_comunitario.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>