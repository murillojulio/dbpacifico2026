<?php

/**
 * Modelo TipoDano
 * 
 * @category App
 * @package Models
 */
class TipoDano extends ActiveRecord {
    
      
    /**
     * Constante para definir un tipo daño como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un tipo daño como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        $this->has_many('dano');
        //$this->has_many('recurso_perfil');
    }
    
    
    /**
     * Método para obtener el listado de los tipo de daños observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoDano() {   
        $order = ' tipo_dano.nombre ASC';
        return $this->paginated_by_sql('SELECT tipo_dano.*, 
                (SELECT COUNT(dano.id) FROM dano WHERE tipo_dano.id = dano.tipo_dano_id) AS veces_usado
                FROM tipo_dano WHERE tipo_dano.id IS NOT NULL GROUP BY tipo_dano.id ORDER BY '.$order);
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
    public static function setTipoDano($method, $data, $optData=null) {        
        $obj = new TipoDano($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new TipoDano();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != TipoDano::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = TipoDano::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un tipo de daño registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el tipo de daño  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del tipo de daño $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    /**
     * Método para obtener el listado de los tipos de daños
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoDanoDBS() {                   
        $columns = 'tipo_dano.*';  
        $conditions = 'tipo_dano.id IS NOT NULL AND tipo_dano.estado = 1'; 
        $order = 'tipo_dano.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>