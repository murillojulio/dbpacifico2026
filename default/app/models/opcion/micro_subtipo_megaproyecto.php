<?php

/*
 * Modelo MicroSubtipoMegaproyecto
 * 
 * @category App
 * @package Models
 * 
 */
class MicroSubtipoMegaproyecto extends ActiveRecord {
    
      
    /**
     * Constante para definir un micro_subtipo de megaproyecto como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un micro_subtipo de megaproyecto como inactivo
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
     * Método para obtener el listado de los micro_subtipo de megaproyectos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoMicroSubtipoMegaproyecto($tipo_megaproyecto_id, $estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'micro_subtipo_megaproyecto.nombre ASC, micro_subtipo_megaproyecto.nombre ASC',
                'DESC' => 'micro_subtipo_megaproyecto.nombre DESC, micro_subtipo_megaproyecto.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT micro_subtipo_megaproyecto.*, 
                (SELECT COUNT(megaproyecto.id) FROM megaproyecto WHERE micro_subtipo_megaproyecto.id = megaproyecto.micro_subtipo_megaproyecto_id) AS cant_sector
                FROM micro_subtipo_megaproyecto WHERE micro_subtipo_megaproyecto.id IS NOT NULL AND micro_subtipo_megaproyecto.tipo_megaproyecto_id = '.$tipo_megaproyecto_id.' GROUP BY micro_subtipo_megaproyecto.id ORDER BY '.$order);
        
        
        
        
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
    public static function setMicroSubtipoMegaproyecto($method, $data, $optData=null) {        
        $obj = new MicroSubtipoMegaproyecto($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new MicroSubtipoMegaproyecto();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != MicroSubtipoMegaproyecto::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = MicroSubtipoMegaproyecto::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un micro_subtipo de megaproyecto registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el micro_subtipo de megaproyecto  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del micro_subtipo de megaproyecto $obj->nombre");
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
    public function getMicroSubtipoMegaproyectoByTipoMegaproyectoIdDBS($subtipo_megaproyecto_id) {                   
        $columns = 'micro_subtipo_megaproyecto.*';  
        $conditions = 'micro_subtipo_megaproyecto.id IS NOT NULL AND micro_subtipo_megaproyecto.estado = 1 AND micro_subtipo_megaproyecto.subtipo_megaproyecto_id = '.$subtipo_megaproyecto_id; 
        $order = 'micro_subtipo_megaproyecto.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    }
?>