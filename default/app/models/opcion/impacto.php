<?php

/**
 * Modelo Impacto
 * 
 * @category App
 * @package Models
 */
class Impacto extends ActiveRecord {
    
      
    /**
     * Constante para definir un tipo impacto como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un tipo impacto como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        $this->has_many('afectacion_territorio_impacto');
        $this->has_many('descripcion_afectacion_impacto');
    }
    
    
    /**
     * Método para obtener el listado de los tipo impactos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoImpacto($tipo_impacto_id, $estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'impacto.nombre ASC, impacto.nombre ASC',
                'DESC' => 'impacto.nombre DESC, impacto.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT impacto.*, 
                (SELECT COUNT(afectacion_territorio_impacto.id) FROM afectacion_territorio_impacto WHERE impacto.id = afectacion_territorio_impacto.impacto_id) AS cant_sector
                FROM impacto WHERE impacto.id IS NOT NULL AND impacto.tipo_impacto_id = '.$tipo_impacto_id.' GROUP BY impacto.id ORDER BY '.$order, "page: $page");
        
        
        
        
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
    public static function setImpacto($method, $data, $optData=null) {        
        $obj = new Impacto($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Impacto();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Impacto::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Impacto::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un tipo impacto registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el impacto  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del impacto $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    /**
     * Método para obtener el listado de los impactos
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getImpactoByTipoImpactoIdDBS($tipo_impacto_id) {                   
        $columns = 'impacto.*';  
        $conditions = 'impacto.id IS NOT NULL AND impacto.estado = 1 AND impacto.tipo_impacto_id = '.$tipo_impacto_id; 
        $order = 'impacto.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>