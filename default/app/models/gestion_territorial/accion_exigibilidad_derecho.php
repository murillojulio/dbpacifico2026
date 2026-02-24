<?php
/**
 *
 * Descripcion: Clase que gestiona los empleos
 *
 * @category
 * @package     Models
 */

class AccionExigibilidadDerecho extends ActiveRecord {
    
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
        $this->has_many('territorio');
        $this->has_many('tipo_accion_exigibilidad_derecho');        
    }
    
    /**
     * Método para obtener el listado de los empleos de los megaproyectos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getAccionExigibilidadDerechosByTerritorioId($territorio_id) {                   
        /* $columns = 'accion_exigibilidad_derecho.*, territorio.nombre AS territorio, tipo_accion_exigibilidad_derecho.nombre AS accion_exigibilidad';        
        $join = ' INNER JOIN territorio ON territorio.id = accion_exigibilidad_derecho.territorio_id';
        $join .= ' INNER JOIN tipo_accion_exigibilidad_derecho ON tipo_accion_exigibilidad_derecho.id = accion_exigibilidad_derecho.tipo_accion_exigibilidad_derecho_id';
        $conditions = 'accion_exigibilidad_derecho.id IS NOT NULL AND territorio_id='.$territorio_id; 
        $order = 'accion_exigibilidad_derecho.nombre';
        $group = 'accion_exigibilidad_derecho.nombre';
              
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order"); */

        $sql = "SELECT accion_exigibilidad_derecho.*, territorio.nombre AS territorio, tipo_accion_exigibilidad_derecho.nombre AS accion_exigibilidad FROM `accion_exigibilidad_derecho` 
        INNER JOIN territorio ON territorio.id = accion_exigibilidad_derecho.territorio_id 
        INNER JOIN tipo_accion_exigibilidad_derecho ON tipo_accion_exigibilidad_derecho.id = accion_exigibilidad_derecho.tipo_accion_exigibilidad_derecho_id 
        WHERE accion_exigibilidad_derecho.id IS NOT NULL AND territorio_id = $territorio_id";

        return $this->find_all_by_sql($sql);
        
    }
    
     public function getAccionExigibilidadDerechoById($accion_exigibilidad_derecho_id) 
    {                   
        $columns = 'accion_exigibilidad_derecho.*, territorio.nombre AS territorio';        
        $join = 'INNER JOIN territorio ON territorio.id = accion_exigibilidad_derecho.territorio_id';
        $conditions = 'accion_exigibilidad_derecho.id='.$accion_exigibilidad_derecho_id;  
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
    public static function setAccionExigibilidadDerecho($method, $data, $territorio_nombre, $optData=null) {        
        $obj = new AccionExigibilidadDerecho($data); //Se carga los datos con los de las tablas        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
//        //Verifico que no exista otro accion_exigibilidad_derecho, y si se encuentra inactivo lo active
//        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
//        $old = new AccionExigibilidadDerecho();
//        if($old->find_first($conditions)) {            
//            //Si existe y se intenta crear pero si no se encuentra activo lo activa
//            if($method=='create' && $old->estado != AccionExigibilidadDerecho::ACTIVO) {
//                $obj->id        = $old->id;
//                $obj->estado    = AccionExigibilidadDerecho::ACTIVO;
//                $method         = 'update';
//            } else {
//                Flash::info('Ya existe un accion_exigibilidad_derecho registrado bajo ese nombre.');
//                return FALSE;
//            }
//        }        
        
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado una Acción de Exigibilidad de Derecho con fecha de inicio $obj->fecha_accion_inicio en el sistema, pertenece al territorio ".$territorio_nombre);
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de una Acción de Exigibilidad de Derecho con fecha de inicio $obj->fecha_accion_inicio, pertenece al territorio ".$territorio_nombre);
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
