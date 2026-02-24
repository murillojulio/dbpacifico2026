<?php

/**
 * Modelo AntecedenteViolencia
 * 
 * @category App
 * @package Models
 */
class Etnia extends ActiveRecord {
    
      
    /**
     * Constante para definir un Etnia como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un Etnia como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
       $this->has_many('victima');
    }
        
    /**
     * Método para obtener el listado de los tipo antecedente de violencia observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoEtnia($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'etnia.nombre ASC, etnia.nombre ASC',
                'DESC' => 'etnia.nombre DESC, etnia.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT etnia.*, 
                (SELECT COUNT(victima.id) FROM victima WHERE etnia.id = victima.etnia_id) AS cant_sector
                FROM etnia WHERE etnia.id IS NOT NULL GROUP BY etnia.id ORDER BY '.$order);
        
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
    public static function setEtnia($method, $data, $optData=null) {        
        $obj = new Etnia($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Etnia();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Etnia::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Etnia::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe una Etnia registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la Etnia  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de la Etnia $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    
    /**
     * Método para obtener el listado de los Etnia
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoEtniaDBS($tipo='etnia') {                   
        $columns = 'etnia.*';  
        $conditions = 'etnia.id IS NOT NULL AND etnia.estado = 1 AND etnia.tipo = "'.$tipo.'"'; 
        $order = 'etnia.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>