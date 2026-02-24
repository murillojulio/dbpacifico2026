<?php
/**
 *
 * Descripcion: Clase que gestiona los empleos
 *
 * @category
 * @package     Models
 */

class MegaproyectoTerritorio extends ActiveRecord {
    
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
    public function getMegaproyectoTerritorioByMegaproyectoIdTerritorioId($megaproyecto_id, $territorio_id) {                   
        $columns = 'megaproyecto_territorio.*, megaproyecto.nombre AS megaproyecto, territorio.nombre AS territorio';        
        $join = 'INNER JOIN megaproyecto ON megaproyecto.id = megaproyecto_territorio.megaproyecto_id INNER JOIN territorio ON territorio.id = megaproyecto_territorio.territorio_id';
        $conditions = 'megaproyecto_territorio.id IS NOT NULL AND megaproyecto_id = '.$megaproyecto_id.' AND territorio_id ='.$territorio_id; 
        $order = 'megaproyecto_territorio.id';
        $group = '';
              
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "order: $order");
        
    }
    
    public function getTerritorioByMegaproyectoId($megaproyecto_id) {                   
        
              
        return $this->find_all_by_sql("SELECT DISTINCT territorio.nombre AS territorio, territorio_id
                                        FROM megaproyecto_territorio
                                        INNER JOIN territorio ON territorio.id = megaproyecto_territorio.territorio_id
                                        WHERE megaproyecto_territorio.id IS NOT NULL 
                                        AND megaproyecto_id =".$megaproyecto_id."
                                        ORDER BY megaproyecto_territorio.id");

    }
    
     public function getMegaproyectoTerritorioById($afectacion_id) 
    {                   
        $columns = 'megaproyecto_territorio.*, megaproyecto.nombre AS megaproyecto';        
        $join = 'INNER JOIN megaproyecto ON megaproyecto.id = megaproyecto_territorio.megaproyecto_id';
        $conditions = 'megaproyecto_territorio.id='.$afectacion_id;  
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
    public static function setMegaproyectoTerritorio($method, $data, $megaproyecto_nombre, $optData=null, $tipo_impacto_nombre) {        
        $obj = new MegaproyectoTerritorio($data); //Se carga los datos con los de las tablas        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
                
        
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la afectacion $tipo_impacto_nombre al territorio ".$data['territorio_nombre']." causada por el megaproyecto ".$megaproyecto_nombre);
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la afectacion $tipo_impacto_nombre al territorio ".$data['territorio_nombre']." causada por el megaproyecto ".$megaproyecto_nombre);
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
    
       public static function deleteMegaproyectoTerritorio($megaproyecto_id, $tipo_impacto_id) {        
        $obj = new MegaproyectoTerritorio(); //Se carga los datos con los de las tablas        
         
        
        $boolean_result = $obj->delete_all("megaproyecto_id = $megaproyecto_id AND tipo_impacto_id = $tipo_impacto_id"); 
           
        return ($boolean_result) ? $obj : FALSE;
    }


}
?>
