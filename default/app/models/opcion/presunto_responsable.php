<?php

/**
 * Modelo PresuntoResponsable
 * 
 * @category App
 * @package Models
 */
class PresuntoResponsable extends ActiveRecord {
    
      
    /**
     * Constante para definir un PresuntoResponsable como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un PresuntoResponsable como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
       $this->has_many('victima_hechovictimizante_presunto_responsable');
       $this->has_many('cultivo_ilicito_presunto_responsable');
    }
        
    /**
     * Método para obtener el listado de los tipo antecedente de violencia observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoPresuntoResponsable($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'presunto_responsable.nombre ASC, presunto_responsable.nombre ASC',
                'DESC' => 'presunto_responsable.nombre DESC, presunto_responsable.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT presunto_responsable.*, 
                (SELECT COUNT(victima_hechovictimizante_presunto_responsable.id) FROM victima_hechovictimizante_presunto_responsable WHERE presunto_responsable.id = victima_hechovictimizante_presunto_responsable.presunto_responsable_id) AS cant_sector
                FROM presunto_responsable WHERE presunto_responsable.id IS NOT NULL GROUP BY presunto_responsable.id ORDER BY '.$order);
        
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
    public static function setPresuntoResponsable($method, $data, $optData=null) {        
        $obj = new PresuntoResponsable($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new PresuntoResponsable();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != PresuntoResponsable::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = PresuntoResponsable::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un Presunto Responsable registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el Presunto Responsable  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de el Presunto Responsable $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    
    /**
     * Método para obtener el listado de los PresuntoResponsable
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoPresuntoResponsableDBS() {                   
        $columns = 'presunto_responsable.*';  
        $conditions = 'presunto_responsable.id IS NOT NULL AND presunto_responsable.estado = 1'; 
        $order = 'presunto_responsable.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>