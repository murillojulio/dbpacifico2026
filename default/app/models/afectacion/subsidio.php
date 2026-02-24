<?php
/**
 *
 * Descripcion: Clase que gestiona los empleos
 *
 * @category
 * @package     Models
 */

class Subsidio extends ActiveRecord {
    
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
     * Método para obtener el listado de los empleos de los megaproyectos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getSubsidiosByMegaproyectoId($megaproyecto_id) {                   
        $columns = 'subsidio.*, megaproyecto.nombre AS megaproyecto';        
        $join = 'INNER JOIN megaproyecto ON megaproyecto.id = subsidio.megaproyecto_id';
        $conditions = 'subsidio.id IS NOT NULL AND megaproyecto_id='.$megaproyecto_id; 
        $order = 'subsidio.nombre';
        $group = 'subsidio.nombre';
              
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order");
        
    }
    
     public function getSubsidioById($subsidio_id) 
    {                   
        $columns = 'subsidio.*, megaproyecto.nombre AS megaproyecto';        
        $join = 'INNER JOIN megaproyecto ON megaproyecto.id = subsidio.megaproyecto_id';
        $conditions = 'subsidio.id='.$subsidio_id;  
        return $this->find_first("columns: $columns", "join: $join", "conditions: $conditions");        
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
    public static function setSubsidio($method, $data, $megaproyecto_nombre, $optData=null) {        
        $obj = new Subsidio($data); //Se carga los datos con los de las tablas        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro subsidio, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Subsidio();
        if($old->find_first($conditions)) {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Subsidio::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Subsidio::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un subsidio registrado bajo ese nombre.');
                return FALSE;
            }
        }        
        
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el subsidio $obj->nombre en el sistema, pertenece al megaproyecto ".$megaproyecto_nombre);
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del subsidio $obj->nombre, pertenece al megaproyecto ".$megaproyecto_nombre);
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


}
?>
