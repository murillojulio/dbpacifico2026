<?php
/**
 *
 * Descripcion: Clase que gestiona las comunidades
 *
 * @category
 * @package     Models
 */

class Comunidad extends ActiveRecord {
    
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
		$this->belongs_to('poblacion');
    }
    
    /**
     * Método para obtener el listado de las comunidades de los territorios observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getComunidadesByTerritorioId($territorio_id) {                   
        $columns = 'comunidad.*, territorio.nombre AS territorio';        
        $join = 'INNER JOIN territorio ON territorio.id = comunidad.territorio_id';
        $conditions = 'comunidad.id IS NOT NULL AND territorio_id='.$territorio_id; 
        $order = 'comunidad.nombre';
        $group = 'comunidad.nombre';
              
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order");
        
    }
    
     public function getComunidadById($comunidad_id) 
    {                   
        $columns = 'comunidad.*, territorio.nombre AS territorio';        
        $join = 'INNER JOIN territorio ON territorio.id = comunidad.territorio_id';
        $conditions = 'comunidad.id='.$comunidad_id;  
        return $this->find_first("columns: $columns", "join: $join", "conditions: $conditions");        
    }
    
    
    
    /**
     * Método para crear/modificar un objeto de base de datos
     * 
     * @param string $medthod: create, update
     * @param array $data: Data para autocargar el modelo
     * @param array $territorio_nombre: Data para nombre territorio
     * @param array $optData: Data adicional para autocargar
     * 
     * return object ActiveRecord
     */
    public static function setComunidad($method, $data, $territorio_nombre, $optData = null) {        
        $obj = new Comunidad($data); //Se carga los datos con los de las tablas        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otra comunidad, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre' AND territorio_id = '$obj->territorio_id'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Comunidad();
        if($old->find_first($conditions)) {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Comunidad::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Comunidad::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe una comunidad registrada bajo ese nombre.');
                return FALSE;
            }
        }        
        
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la comunidad $obj->nombre en el sistema, pertenece al territorio ".$territorio_nombre);
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de la comunidad $obj->nombre, pertenece al territorio ".$territorio_nombre);
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    public function getComunidadByConsejoId($consejo_id) 
    {                   
      return $this->find_all_by_sql("SELECT comunidad.id FROM comunidad WHERE comunidad.consejo_id =".$consejo_id);
    }
    
    public function getBarriosByTerritorioIdSelect($territorio_id=null) 
    {    
       if((int)$territorio_id)
        {           
            return $this->find_all_by_sql("SELECT comunidad.* FROM comunidad WHERE comunidad.territorio_id = $territorio_id AND comunidad.tipo = 'barrio' ORDER BY comunidad.nombre ASC");
        }else{
            return array();
        }  
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
