<?php

/**
 * Modelo Comite
 * 
 * @category App
 * @package Models
 */
class Comite extends ActiveRecord {
    
      
    /**
     * Constante para definir un comite como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un comite como inactivo
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
     * Método para obtener el listado de los comites observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoComite($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'comite.nombre ASC, comite.nombre ASC',
                'DESC' => 'comite.nombre DESC, comite.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT comite.*, 
                (SELECT COUNT(consejo_comite.id) FROM consejo_comite WHERE consejo_comite.comite_id = comite.id) AS cant_sector
                FROM comite WHERE comite.id IS NOT NULL GROUP BY comite.id ORDER BY '.$order);
        
        
        
        
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
    public static function setComite($method, $data, $optData=null) {        
        $obj = new Comite($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Comite();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Comite::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Comite::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un comite registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el comite  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del comite $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    public function getComiteDBS() {                   
        $columns = 'comite.*';  
        $conditions = 'comite.id IS NOT NULL AND comite.estado = 1'; 
        $order = 'comite.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
}
?>