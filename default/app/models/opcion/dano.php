<?php

/**
 * Modelo Daño
 * 
 * @category App
 * @package Models
 */
class Dano extends ActiveRecord {
    
      
    /**
     * Constante para definir un Daño como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un Daño como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('tipo_dano');
        $this->has_many('afectacion_territorio_dano');
        $this->has_many('descripcion_afectacion_dano');
    }
    
    
    /**
     * Método para obtener el listado de los Daños observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoDano($tipo_dano_id) {  
        /*return $this->paginated_by_sql('SELECT dano.*, 
                (SELECT COUNT(afectacion_territorio_dano.id) FROM afectacion_territorio_dano WHERE dano.id = afectacion_territorio_dano.dano_id) AS cant_sector
                FROM dano WHERE dano.id IS NOT NULL AND dano.tipo_dano_id = '.$tipo_dano_id.' GROUP BY dano.id ORDER BY '.$order, "page: $page");
        */
        return $this->find_all_by('tipo_dano_id', $tipo_dano_id);
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
    public static function setDano($method, $data, $optData=null) {        
        $obj = new Dano($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Dano();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Dano::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Dano::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un Daño registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el daño  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del daño $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    /**
     * Método para obtener el listado de los Daños
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getDanoByTipoDanoIdDBS($tipo_dano_id = NULL) {        
        if((int)$tipo_dano_id)
        {
            $columns = 'dano.*';  
            $conditions = 'dano.id IS NOT NULL AND dano.estado = 1 AND dano.tipo_dano_id = '.$tipo_dano_id; 
            $order = 'dano.nombre ASC';
            return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        }else{
            return array();
        }          
    }
    
}
?>