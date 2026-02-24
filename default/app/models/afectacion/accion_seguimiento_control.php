<?php
/**
 *
 * Descripcion: Clase que gestiona las acciones de seguimiento control
 *
 * @category
 * @package     Models
 */

class AccionSeguimientoControl extends ActiveRecord {
    
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
    public function getAccionSeguimientoControlsByMegaproyectoId($megaproyecto_id) {                   
        $columns = 'accion_seguimiento_control.*, megaproyecto.nombre AS megaproyecto, mads_car.nombre AS mads_car';        
        $join = 'INNER JOIN megaproyecto ON megaproyecto.id = accion_seguimiento_control.megaproyecto_id';
        $join .= ' INNER JOIN mads_car ON mads_car.id = accion_seguimiento_control.mads_car_id';
        $conditions = 'accion_seguimiento_control.id IS NOT NULL AND megaproyecto_id='.$megaproyecto_id; 
        $order = 'accion_seguimiento_control.fecha_accion';
        $group = 'accion_seguimiento_control.fecha_accion';
              
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order");
        
    }
    
     public function getAccionSeguimientoControlById($accion_seguimiento_control_id) 
    {                   
        $columns = 'accion_seguimiento_control.*, megaproyecto.nombre AS megaproyecto';        
        $join = 'INNER JOIN megaproyecto ON megaproyecto.id = accion_seguimiento_control.megaproyecto_id';
        $conditions = 'accion_seguimiento_control.id='.$accion_seguimiento_control_id;  
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
    public static function setAccionSeguimientoControl($method, $data, $megaproyecto_nombre, $optData=null) {        
        $obj = new AccionSeguimientoControl($data); //Se carga los datos con los de las tablas        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
                        
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) {
            $accion = substr($obj->descripcion_accion, 0, 20);
            if($method == 'create')
            {                
                DwAudit::create("Se ha registrado la accion seguimiento control $accion en el sistema, pertenece al megaproyecto ".$megaproyecto_nombre);
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de la accion seguimiento control $accion, pertenece al megaproyecto ".$megaproyecto_nombre);
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
