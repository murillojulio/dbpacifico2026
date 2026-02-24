<?php
/**
 *
 * Descripcion: Clase que gestiona los cabildos
 *
 * @category
 * @package     Models
 */

class Cabildo extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    //public $logger = FALSE;
       
    /**
     * Constante para definir un perfil como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un perfil como inactivo
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
     * Método para obtener el listado de los cabildos de los territorios observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getCabildosByTerritorioId($territorio_id) {                   
        $columns = 'cabildo.*, territorio.nombre AS territorio, tipo_cabildo.nombre AS tipo_cabildo';        
        $join = 'INNER JOIN territorio ON territorio.id = cabildo.territorio_id';
        $join .= ' INNER JOIN tipo_cabildo ON tipo_cabildo.id = cabildo.tipo_cabildo_id';
        $conditions = 'cabildo.id IS NOT NULL AND territorio_id='.$territorio_id; 
        $order = 'cabildo.tipo_cabildo_id ASC';
        $group = 'cabildo.nombre';
              
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order");
        
    }
    
     public function getCabildoById($cabildo_id) 
    {                   
        $columns = 'cabildo.*, territorio.nombre AS territorio';        
        $join = 'INNER JOIN territorio ON territorio.id = cabildo.territorio_id';
        $conditions = 'cabildo.id='.$cabildo_id;  
        return $this->find_first("columns: $columns", "join: $join", "conditions: $conditions");        
    }
    
     public function getCabildoByTerritorioIdAndTipoCabildoId($territorio_id, $tipo_cabildo_id) 
    {                   
        $columns = 'cabildo.*';        
        $join = '';
        $conditions = 'territorio_id ='.$territorio_id.' AND tipo_cabildo_id = '.$tipo_cabildo_id;  
        return $this->count("columns: $columns", "join: $join", "conditions: $conditions");    
    }
    
    
    
    /**
     * Método para crear/modificar un objeto de base de datos
     * 
     * @param string $medthod: create, update
     * @param array $data: Data para autocargar el modelo
     * @param array $optData: Data adicional para autocargar
     * 
     * return object ActiveRecord
     */
    public static function setCabildo($method, $data, $territorio_nombre, $optData=null) {        
        $obj = new Cabildo($data); //Se carga los datos con los de las tablas        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro cabildo, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Cabildo();
        if($old->find_first($conditions)) {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Cabildo::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Cabildo::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un cabildo registrado bajo ese nombre.');
                return FALSE;
            }
        }        
        
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el cabildo $obj->nombre en el sistema, pertenece al territorio ".$territorio_nombre);
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del cabildo $obj->nombre, pertenece al territorio ".$territorio_nombre);
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    /**
     * Callback que se ejecuta antes de guardar/modificar
     */
    public function before_save() {
       
    }
    
    /**
     * Callback que se ejecuta después de guardar/modificar un perfil
     */
    protected function after_save() {
        
    }
    
    /**
     * Método para obtener el listado de los Genero
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoCabildoDBS() {                   
        $columns = 'cabildo.*';  
        $conditions = 'cabildo.id IS NOT NULL AND cabildo.estado = 1'; 
        $order = 'cabildo.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }


}
?>
