<?php

/**
 * Modelo FormaPago
 * 
 * @category App
 * @package Models
 */
class FormaPago extends ActiveRecord {
    
      
    /**
     * Constante para definir un FormaPago como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un FormaPago como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
       $this->has_many('empleo');
    }
        
    /**
     * Método para obtener el listado de los tipo empleos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoFormaPago($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'forma_pago.nombre ASC, forma_pago.nombre ASC',
                'DESC' => 'forma_pago.nombre DESC, forma_pago.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT forma_pago.*, 
                (SELECT COUNT(empleo.id) FROM empleo WHERE forma_pago.id = empleo.cualificado_forma_pago_id) AS cant_sector
                FROM forma_pago WHERE forma_pago.id IS NOT NULL GROUP BY forma_pago.id ORDER BY '.$order);
        
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
    public static function setFormaPago($method, $data, $optData=null) {        
        $obj = new FormaPago($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new FormaPago();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != FormaPago::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = FormaPago::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe una Forma de Pago registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la Forma de Pago  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de la Forma de Pago $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    
    /**
     * Método para obtener el listado de los FormaPago
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoFormaPagoDBS() {                   
        $columns = 'forma_pago.*';  
        $conditions = 'forma_pago.id IS NOT NULL AND forma_pago.estado = 1'; 
        $order = 'forma_pago.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>