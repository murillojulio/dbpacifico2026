<?php

/**
 * Modelo AntecedenteViolencia
 * 
 * @category App
 * @package Models
 */
class Caracterizacion extends ActiveRecord {
    
      
    /**
     * Constante para definir un Caracterizacion como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un Caracterizacion como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
       $this->has_many('victima');
    }
        
    /**
     * Método para obtener el listado de los tipo antecedente de violencia observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoCaracterizacion($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'caracterizacion.nombre ASC, caracterizacion.nombre ASC',
                'DESC' => 'caracterizacion.nombre DESC, caracterizacion.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT caracterizacion.*, 
                (SELECT COUNT(victima.id) FROM victima WHERE caracterizacion.id = victima.caracterizacion_id) AS cant_sector
                FROM caracterizacion WHERE caracterizacion.id IS NOT NULL GROUP BY caracterizacion.id ORDER BY '.$order);
        
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
    public static function setCaracterizacion($method, $data, $optData=null) {        
        $obj = new Caracterizacion($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Caracterizacion();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Caracterizacion::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Caracterizacion::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe una Caracterizacion registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la Caracterizacion  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de la Caracterizacion $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    
    /**
     * Método para obtener el listado de los Caracterizacion
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoCaracterizacionDBS() {                   
        $columns = 'caracterizacion.*';  
        $conditions = 'caracterizacion.id IS NOT NULL AND caracterizacion.estado = 1'; 
        $order = 'caracterizacion.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>