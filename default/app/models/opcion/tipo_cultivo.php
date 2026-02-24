<?php

/**
 * Modelo TipoCultivo
 * 
 * @category App
 * @package Models
 */
class TipoCultivo extends ActiveRecord {
    
      
    /**
     * Constante para definir un tipo subsidio como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un tipo subsidio como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        $this->has_many('cultivo_ilicito');
        //$this->has_many('recurso_perfil');
    }
    
    
    /**
     * Método para obtener el listado de los tipo de cultivos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoCultivo($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'tipo_cultivo.nombre ASC, tipo_cultivo.nombre ASC',
                'DESC' => 'tipo_cultivo.nombre DESC, tipo_cultivo.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT tipo_cultivo.*, 
                (SELECT COUNT(cultivo_ilicito.id) FROM cultivo_ilicito WHERE tipo_cultivo.id = cultivo_ilicito.tipo_cultivo_id) AS cant_sector
                FROM tipo_cultivo WHERE tipo_cultivo.id IS NOT NULL GROUP BY tipo_cultivo.id ORDER BY '.$order);
        
        
        
        
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
    public static function setTipoCultivo($method, $data, $optData=null) {        
        $obj = new TipoCultivo($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new TipoCultivo();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != TipoCultivo::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = TipoCultivo::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un tipo de cultivo registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el tipo de cultivo  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del tipo de cultivo $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    /**
     * Método para obtener el listado de los tipos de cultivo_ilicitos
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTipoCultivoDBS() {                   
        $columns = 'tipo_cultivo.*';  
        $conditions = 'tipo_cultivo.id IS NOT NULL AND tipo_cultivo.estado = 1'; 
        $order = 'tipo_cultivo.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>