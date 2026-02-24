<?php

/**
 * Modelo Genero
 * 
 * @category App
 * @package Models
 */
class Genero extends ActiveRecord {
    
      
    /**
     * Constante para definir un Genero como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un Genero como inactivo
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
    public function getListadoGenero($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'genero.nombre ASC, genero.nombre ASC',
                'DESC' => 'genero.nombre DESC, genero.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT genero.*, 
                (SELECT COUNT(victima.id) FROM victima WHERE genero.id = victima.genero_id) AS cant_sector
                FROM genero WHERE genero.id IS NOT NULL GROUP BY genero.id ORDER BY '.$order);
        
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
    public static function setGenero($method, $data, $optData=null) {        
        $obj = new Genero($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Genero();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Genero::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Genero::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe una Genero registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la Genero  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de la Genero $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    
    /**
     * Método para obtener el listado de los Genero
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoGeneroDBS() {                   
        $columns = 'genero.*';  
        $conditions = 'genero.id IS NOT NULL AND genero.estado = 1'; 
        $order = 'genero.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>