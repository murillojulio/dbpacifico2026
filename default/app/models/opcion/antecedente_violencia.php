<?php

/**
 * Modelo AntecedenteViolencia
 * 
 * @category App
 * @package Models
 */
class AntecedenteViolencia extends ActiveRecord {
    
      
    /**
     * Constante para definir un AntecedenteViolencia como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un AntecedenteViolencia como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
       $this->has_many('victima_antecedente_violencia');
    }
        
    /**
     * Método para obtener el listado de los tipo antecedente de violencia observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoAntecedenteViolencia($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'antecedente_violencia.nombre ASC, antecedente_violencia.nombre ASC',
                'DESC' => 'antecedente_violencia.nombre DESC, antecedente_violencia.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT antecedente_violencia.*, 
                (SELECT COUNT(victima_antecedente_violencia.id) FROM victima_antecedente_violencia WHERE antecedente_violencia.id = victima_antecedente_violencia.antecedente_violencia_id) AS cant_sector
                FROM antecedente_violencia WHERE antecedente_violencia.id IS NOT NULL GROUP BY antecedente_violencia.id ORDER BY '.$order);
        
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
    public static function setAntecedenteViolencia($method, $data, $optData=null) {        
        $obj = new AntecedenteViolencia($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new AntecedenteViolencia();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != AntecedenteViolencia::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = AntecedenteViolencia::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un Antecedente Violencia registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el Antecedente Violencia  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de el Antecedente Violencia $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    
    /**
     * Método para obtener el listado de los AntecedenteViolencia
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoAntecedenteViolenciaDBS() {                   
        $columns = 'antecedente_violencia.*';  
        $conditions = 'antecedente_violencia.id IS NOT NULL AND antecedente_violencia.estado = 1'; 
        $order = 'antecedente_violencia.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>