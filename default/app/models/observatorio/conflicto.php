<?php
/**
 *
 * Descripcion: Clase que gestiona los conflictos
 *
 * @category
 * @package     Models
 */

class Conflicto extends ActiveRecord {
    
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
     * Método para obtener el listado de los conflictos de los territorios observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getConflictosByTerritorioId($territorio_id) {                   
        $columns = 'conflicto.*, territorio.nombre AS territorio';        
        $join = 'INNER JOIN territorio ON territorio.id = conflicto.territorio_id';
        $conditions = 'conflicto.id IS NOT NULL AND territorio_id='.$territorio_id; 
        $order = 'conflicto.nombre';
        $group = 'conflicto.nombre';
              
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order");
        
    }
    
     public function getConflictoById($conflicto_id) 
    {                   
        $columns = 'conflicto.*, territorio.nombre AS territorio';        
        $join = 'INNER JOIN territorio ON territorio.id = conflicto.territorio_id';
        $conditions = 'conflicto.id='.$conflicto_id;  
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
    public static function setConflicto($method, $data, $territorio_nombre, $optData=null) {        
        $obj = new Conflicto($data); //Se carga los datos con los de las tablas        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro conflicto, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Conflicto();
        if($old->find_first($conditions)) {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Conflicto::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Conflicto::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un conflicto registrado bajo ese nombre.');
                return FALSE;
            }
        }  
        if($method == 'update')
        {
            //para setear los check box y volverlos a llenar
             $obj_setear = new Conflicto($data);
             $obj_setear->intra_etnico = 0;
             $obj_setear->inter_etnico = 0;
             $obj_setear->cultural = 0;
             $obj_setear->politico_violencia = 0;
             $obj_setear->politico_grupo_armado = 0;
             $obj_setear->politico_electoral = 0;
             $obj_setear->economico = 0;
             $obj_setear->recurso_natural = 0;
             $obj_setear->territorial_uso = 0;
             $obj_setear->territorial_delimitacion = 0;
             $obj_setear->otro = 0;    
             $obj_setear->update();
             
        }
        
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el conflicto $obj->nombre en el sistema, pertenece al territorio ".$territorio_nombre);
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del conflicto $obj->nombre, pertenece al territorio ".$territorio_nombre);
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
