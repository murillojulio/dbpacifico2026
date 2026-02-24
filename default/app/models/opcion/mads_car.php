<?php

/**
 * Modelo MadsCar
 * 
 * @category App
 * @package Models
 */
class MadsCar extends ActiveRecord {
    
      
    /**
     * Constante para definir un MadsCar como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un MadsCar como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
       $this->has_many('accion_seguimiento_control');
    }
        
    /**
     * Método para obtener el listado de los tipo accion_seguimiento_controls observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoMadsCar($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'mads_car.nombre ASC, mads_car.nombre ASC',
                'DESC' => 'mads_car.nombre DESC, mads_car.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT mads_car.*, 
                (SELECT COUNT(accion_seguimiento_control.id) FROM accion_seguimiento_control WHERE mads_car.id = accion_seguimiento_control.mads_car_id) AS cant_sector
                FROM mads_car WHERE mads_car.id IS NOT NULL GROUP BY mads_car.id ORDER BY '.$order);
        
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
    public static function setMadsCar($method, $data, $optData=null) {        
        $obj = new MadsCar($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new MadsCar();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != MadsCar::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = MadsCar::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un MADS - CAR registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el MADS - CAR  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del MADS - CAR $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    
    /**
     * Método para obtener el listado de los MadsCar
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoMadsCarDBS() {                   
        $columns = 'mads_car.*';  
        $conditions = 'mads_car.id IS NOT NULL AND mads_car.estado = 1'; 
        $order = 'mads_car.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>