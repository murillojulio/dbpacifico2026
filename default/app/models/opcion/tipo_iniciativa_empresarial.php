<?php

/**
 * Modelo TipoIniciativaEmpresarial
 * 
 * @category App
 * @package Models
 */
class TipoIniciativaEmpresarial extends ActiveRecord {    
      
    /**
     * Constante para definir un tipo iniciativa empresarial como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un tipo iniciativa empresarial como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        $this->has_many('iniciativa_empresarial');
        //$this->has_many('recurso_perfil');
    }
    
    
    /**
     * Método para obtener el listado de los tipo iniciativa empresarials observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoIniciativaEmpresarial($estado='todos', $order='', $page=0) {  
        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'tipo_iniciativa_empresarial.nombre ASC, tipo_iniciativa_empresarial.nombre ASC',
                'DESC' => 'tipo_iniciativa_empresarial.nombre DESC, tipo_iniciativa_empresarial.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT tipo_iniciativa_empresarial.*, 
                (SELECT COUNT(iniciativa_empresarial.id) FROM iniciativa_empresarial WHERE tipo_iniciativa_empresarial.id = iniciativa_empresarial.tipo_iniciativa_empresarial_id) AS cant_sector
                FROM tipo_iniciativa_empresarial WHERE tipo_iniciativa_empresarial.id IS NOT NULL GROUP BY tipo_iniciativa_empresarial.id ORDER BY '.$order);
             
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
    public static function setTipoIniciativaEmpresarial($method, $data, $optData=null) {        
        $obj = new TipoIniciativaEmpresarial($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new TipoIniciativaEmpresarial();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != TipoIniciativaEmpresarial::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = TipoIniciativaEmpresarial::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un tipo iniciativa empresarial registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el tipo iniciativa empresarial  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del tipo iniciativa empresarial $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    /**
     * Método para obtener el listado de los tipos de iniciativa empresarial derecho
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoIniciativaEmpresarialDBS() {                   
        $columns = 'tipo_iniciativa_empresarial.*';  
        $conditions = 'tipo_iniciativa_empresarial.id IS NOT NULL AND tipo_iniciativa_empresarial.estado = 1'; 
        $order = 'tipo_iniciativa_empresarial.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>