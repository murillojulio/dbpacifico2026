<?php
/**
 *
 * Descripcion: Clase que gestiona los empleos
 *
 * @category
 * @package     Models
 */

class Caso extends ActiveRecord {
    
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
        $this->has_many('victima');
        $this->belongs_to('territorio');        
        $this->belongs_to('municipio');
        $this->belongs_to('localidad');
    }
    
    /**
     * Método para obtener el listado de los empleos de los megaproyectos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getCasosByTerritorioId($territorio_id) {                   
        $columns = 'caso.*, territorio.nombre AS territorio';        
        $join = 'INNER JOIN territorio ON territorio.id = caso.territorio_id';
        $conditions = 'caso.id IS NOT NULL AND territorio_id='.$territorio_id; 
        $order = 'caso.titulo';
        $group = 'caso.titulo';
              
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order");
        
    }
    
     public function getCasoById($caso_id) 
    {                   
        $columns = 'caso.*, localidad.tipo AS tipo_localidad'; 
        $join = 'LEFT JOIN localidad ON localidad.id = caso.localidad_id';       
        $conditions = 'caso.id='.$caso_id;  
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
    public static function setCaso($method, $data, $optData=null) {        
        $obj = new Caso($data); //Se carga los datos con los de las tablas 
        $obj->fecha_desde = date('Y-m-d', strtotime($obj->fecha_desde));
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }     
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) 
        {            
            $territorio_nombre = $obj->getTerritorio()->nombre;
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el Caso de Violencia Política: $obj->titulo, sucedió en el territorio ".$territorio_nombre);
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del Caso de Violencia Política: $obj->titulo, sucedió en el territorio ".$territorio_nombre);
            }            
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
