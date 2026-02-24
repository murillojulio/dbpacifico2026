<?php

/**
 * Modelo AntecedenteViolencia
 * 
 * @category App
 * @package Models
 */
class Caracterizacion2 extends ActiveRecord {
    
      
    /**
     * Constante para definir un Caracterizacion2 como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un Caracterizacion2 como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
       $this->has_many('victima_caracterizacion2');
    }
        
    /**
     * Método para obtener el listado de los tipo antecedente de violencia observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoCaracterizacion2($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'caracterizacion2.nombre ASC, caracterizacion2.nombre ASC',
                'DESC' => 'caracterizacion2.nombre DESC, caracterizacion2.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT caracterizacion2.*, 
                (SELECT COUNT(victima.id) FROM victima WHERE caracterizacion2.id = victima.caracterizacion2_id) AS cant_sector
                FROM caracterizacion2 WHERE caracterizacion2.id IS NOT NULL GROUP BY caracterizacion2.id ORDER BY '.$order);
        
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
    public static function setCaracterizacion2($method, $data, $optData=null) {        
        $obj = new Caracterizacion2($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Caracterizacion2();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Caracterizacion2::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Caracterizacion2::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe una Caracterizacion2 registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la Caracterizacion2  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de la Caracterizacion2 $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    
    /**
     * Método para obtener el listado de los Caracterizacion2
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoCaracterizacion2DBS() {                   
        $columns = 'caracterizacion2.*';  
        $conditions = 'caracterizacion2.id IS NOT NULL AND caracterizacion2.estado = 1'; 
        $order = 'caracterizacion2.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>