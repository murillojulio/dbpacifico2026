<?php

/**
 * Modelo Hechovictimizante
 * 
 * @category App
 * @package Models
 */
class Hechovictimizante extends ActiveRecord {
    
      
    /**
     * Constante para definir un Hechovictimizante como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un Hechovictimizante como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
       $this->has_many('victima_hechovictimizante_presunto_responsable');
    }
        
    /**
     * Método para obtener el listado de los tipo antecedente de violencia observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoHechovictimizante($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'hechovictimizante.nombre ASC, hechovictimizante.nombre ASC',
                'DESC' => 'hechovictimizante.nombre DESC, hechovictimizante.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT hechovictimizante.*, 
                (SELECT COUNT(victima_hechovictimizante_presunto_responsable.id) FROM victima_hechovictimizante_presunto_responsable WHERE hechovictimizante.id = victima_hechovictimizante_presunto_responsable.hechovictimizante_id) AS cant_sector
                FROM hechovictimizante WHERE hechovictimizante.id IS NOT NULL GROUP BY hechovictimizante.id ORDER BY '.$order);
        
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
    public static function setHechovictimizante($method, $data, $optData=null) {        
        $obj = new Hechovictimizante($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Hechovictimizante();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Hechovictimizante::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Hechovictimizante::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un Hecho Victimizante registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el Hecho Victimizante  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de el Hecho Victimizante $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    
    /**
     * Método para obtener el listado de los Hechovictimizante
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoHechovictimizanteDBS() {                   
        $columns = 'hechovictimizante.*';  
        $conditions = 'hechovictimizante.id IS NOT NULL AND hechovictimizante.estado = 1'; 
        $order = 'hechovictimizante.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>