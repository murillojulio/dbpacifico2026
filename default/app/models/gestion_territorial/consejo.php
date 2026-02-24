<?php
/**
 *
 * Descripcion: Clase que gestiona los consejos
 *
 * @category
 * @package     Models
 */

class Consejo extends ActiveRecord {
    
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

        $this->has_many('consejo_comite');
        //$this->has_many('recurso_perfil');
        
    }
    
    /**
     * Método para obtener el listado de los consejos de los territorios observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getConsejosByTerritorioId($territorio_id) {                   
        $columns = 'consejo.*, territorio.nombre AS territorio, tipo_consejo.nombre AS tipo_consejo';        
        $join = 'INNER JOIN territorio ON territorio.id = consejo.territorio_id';
        $join .= ' INNER JOIN tipo_consejo ON tipo_consejo.id = consejo.tipo_consejo_id';
        $conditions = 'consejo.id IS NOT NULL AND territorio_id='.$territorio_id; 
        $order = 'consejo.tipo_consejo_id ASC';
        $group = 'consejo.nombre';
              
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order");
        
    }
    
     public function getConsejoById($consejo_id) 
    {                   
        $columns = 'consejo.*, territorio.nombre AS territorio';        
        $join = 'INNER JOIN territorio ON territorio.id = consejo.territorio_id';
        $conditions = 'consejo.id='.$consejo_id;  
        return $this->find_first("columns: $columns", "join: $join", "conditions: $conditions");        
    }
    
     public function getConsejoByTerritorioIdAndTipoConsejoId($territorio_id, $tipo_consejo_id) 
    {                   
        $columns = 'consejo.*';        
        $join = '';
        $conditions = 'territorio_id ='.$territorio_id.' AND tipo_consejo_id = '.$tipo_consejo_id;  
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
    public static function setConsejo($method, $data, $territorio_nombre, $optData=null) {        
        $obj = new Consejo($data); //Se carga los datos con los de las tablas        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro consejo, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Consejo();
        if($old->find_first($conditions)) {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Consejo::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Consejo::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un consejo registrado bajo ese nombre.');
                return FALSE;
            }
        }        
        
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el consejo $obj->nombre en el sistema, pertenece al territorio ".$territorio_nombre);
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del consejo $obj->nombre, pertenece al territorio ".$territorio_nombre);
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
    public function getListadoConsejoDBS() {                   
        $columns = 'consejo.*';  
        $conditions = 'consejo.id IS NOT NULL AND consejo.estado = 1'; 
        $order = 'consejo.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
}
?>
