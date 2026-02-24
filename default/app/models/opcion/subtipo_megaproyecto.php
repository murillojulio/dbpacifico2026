<?php

/**
 * Modelo SubtipoMegaproyecto
 * 
 * @category App
 * @package Models
 */
class SubtipoMegaproyecto extends ActiveRecord {
    
      
    /**
     * Constante para definir un subtipo de megaproyecto como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un subtipo de megaproyecto como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        //$this->has_many('megaproyecto_territorio_impacto');
        //$this->has_many('recurso_perfil');
    }
    
    
    /**
     * Método para obtener el listado de los subtipo de megaproyectos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoSubtipoMegaproyecto($tipo_megaproyecto_id, $estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'subtipo_megaproyecto.nombre ASC, subtipo_megaproyecto.nombre ASC',
                'DESC' => 'subtipo_megaproyecto.nombre DESC, subtipo_megaproyecto.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT subtipo_megaproyecto.*, 
                (SELECT COUNT(megaproyecto.id) FROM megaproyecto WHERE subtipo_megaproyecto.id = megaproyecto.subtipo_megaproyecto_id) AS cant_sector
                FROM subtipo_megaproyecto WHERE subtipo_megaproyecto.id IS NOT NULL AND subtipo_megaproyecto.tipo_megaproyecto_id = '.$tipo_megaproyecto_id.' GROUP BY subtipo_megaproyecto.id ORDER BY '.$order);
        
        
        
        
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
    public static function setSubtipoMegaproyecto($method, $data, $optData=null) {        
        $obj = new SubtipoMegaproyecto($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new SubtipoMegaproyecto();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != SubtipoMegaproyecto::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = SubtipoMegaproyecto::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un subtipo de megaproyecto registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el subtipo de megaproyecto  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del subtipo de megaproyecto $obj->nombre");
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
    public function getSubtipoMegaproyectoByTipoMegaproyectoIdDBS($tipo_megaproyecto_id) {                   
        $columns = 'subtipo_megaproyecto.*';  
        $conditions = 'subtipo_megaproyecto.id IS NOT NULL AND subtipo_megaproyecto.estado = 1 AND subtipo_megaproyecto.tipo_megaproyecto_id = '.$tipo_megaproyecto_id; 
        $order = 'subtipo_megaproyecto.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>