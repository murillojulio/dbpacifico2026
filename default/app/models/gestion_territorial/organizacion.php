<?php
/**
 *
 * Descripcion: Clase que gestiona los organizacions
 *
 * @category
 * @package     Models
 */

class Organizacion extends ActiveRecord {
    
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
        $this->has_many('organizacion_has_campo_gestion');
        $this->has_many('organizacion_has_campo_accion');        
    }
    
    /**
     * Método para obtener el listado de los organizacions de los territorios observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getOrganizacionsByTerritorioId($territorio_id) {                   
        $columns = 'organizacion.*, territorio.nombre AS territorio';        
        $join = 'INNER JOIN territorio ON territorio.id = organizacion.territorio_id';
        $conditions = 'organizacion.id IS NOT NULL AND territorio_id='.$territorio_id; 
        $order = 'organizacion.nombre ASC';
        $group = 'organizacion.nombre';
              
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order");
        
    }
    
     public function getOrganizacionById($organizacion_id) 
    {                   
        $columns = 'organizacion.*, territorio.nombre AS territorio';        
        $join = 'INNER JOIN territorio ON territorio.id = organizacion.territorio_id';
        $conditions = 'organizacion.id='.$organizacion_id;  
        return $this->find_first("columns: $columns", "join: $join", "conditions: $conditions");        
    }
    
     public function getOrganizacionByTerritorioIdAndTipoOrganizacionId($territorio_id) 
    {                   
        $columns = 'organizacion.*';        
        $join = '';
        $conditions = 'territorio_id ='.$territorio_id;  
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
    public static function setOrganizacion($method, $data, $territorio_nombre, $optData=null) {        
        $obj = new Organizacion($data); //Se carga los datos con los de las tablas   
        $obj->fecha_inicio_representante_legal = date('Y-m-d', strtotime($obj->fecha_inicio_representante_legal));     
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro organizacion, y si se encuentra inactivo lo active
        /*$conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Organizacion();
        if($old->find_first($conditions)) {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Organizacion::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Organizacion::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un organizacion registrado bajo ese nombre.');
                return FALSE;
            }
        }*/     
        
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el organizacion $obj->nombre en el sistema, pertenece al territorio ".$territorio_nombre);
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del organizacion $obj->nombre, pertenece al territorio ".$territorio_nombre);
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
    public function getListadoOrganizacionDBS() {                   
        $columns = 'organizacion.*';  
        $conditions = 'organizacion.id IS NOT NULL AND organizacion.estado = 1'; 
        $order = 'organizacion.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
}
?>
