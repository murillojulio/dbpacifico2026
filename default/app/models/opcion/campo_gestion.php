<?php

/**
 * Modelo CampoGestion
 * 
 * @category App
 * @package Models
 */
class CampoGestion extends ActiveRecord {
    
      
    /**
     * Constante para definir un campo_gestion como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un campo_gestion como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        //$this->has_many('usuario');
        //$this->has_many('recurso_perfil');
    }
    
    
    /**
     * Método para obtener el listado de los campo_gestions observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoCampoGestion($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'campo_gestion.nombre ASC, campo_gestion.nombre ASC',
                'DESC' => 'campo_gestion.nombre DESC, campo_gestion.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT campo_gestion.*, 
                (SELECT COUNT(organizacion_has_campo_gestion.id) FROM organizacion_has_campo_gestion WHERE organizacion_has_campo_gestion.campo_gestion_id = campo_gestion.id) AS cant_sector
                FROM campo_gestion WHERE campo_gestion.id IS NOT NULL GROUP BY campo_gestion.id ORDER BY '.$order);
        
        
        
        
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
    public static function setCampoGestion($method, $data, $optData=null) {        
        $obj = new CampoGestion($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new CampoGestion();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != CampoGestion::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = CampoGestion::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un campo_gestion registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el campo_gestion  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del campo_gestion $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    public function getCampoGestionDBS() {                   
        $columns = 'campo_gestion.*';  
        $conditions = 'campo_gestion.id IS NOT NULL AND campo_gestion.estado = 1'; 
        $order = 'campo_gestion.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>