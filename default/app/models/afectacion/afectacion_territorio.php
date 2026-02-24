<?php
/**
 *
 * Descripcion: Clase que gestiona los empleos
 *
 * @category
 * @package     Models
 */

class AfectacionTerritorio extends ActiveRecord {
    
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

        $this->belongs_to('afectacion');
        $this->belongs_to('territorio');
        $this->has_many('afectacion_territorio_impacto');
    }
    
    
    
    /**
     * Método para obtener el listado de los empleos de los megaproyectos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getAfectacionTerritorioByAfectacionIdTerritorioId($afectacion_id, $territorio_id) {                   
        $columns = 'afectacion_territorio.*, territorio.nombre AS territorio';        
        $join = 'INNER JOIN territorio ON territorio.id = afectacion_territorio.territorio_id';
        $conditions = 'afectacion_territorio.id IS NOT NULL AND afectacion_id = '.$afectacion_id.' AND territorio_id ='.$territorio_id; 
        $order = 'afectacion_territorio.id';
        $group = '';
              
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "order: $order");
        
    }
    
    public function getTerritorioAfectadoByAfectacionId($afectacion_id) {                   
        
              
        return $this->find_all_by_sql("SELECT DISTINCT territorio.nombre AS territorio, territorio_id, afectacion_territorio.estado, afectacion_territorio.nivel
                                        FROM afectacion_territorio
                                        INNER JOIN territorio ON territorio.id = afectacion_territorio.territorio_id
                                        WHERE afectacion_territorio.id IS NOT NULL 
                                        AND afectacion_id =".$afectacion_id."
                                        ORDER BY afectacion_territorio.id");

    }
    
     public function getAfectacionTerritorioById($afectacion_id) 
    {                   
        $columns = 'afectacion_territorio.*, megaproyecto.nombre AS megaproyecto';        
        $join = 'INNER JOIN megaproyecto ON megaproyecto.id = afectacion_territorio.afectacion_id';
        $conditions = 'afectacion_territorio.id='.$afectacion_id;  
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
    public static function setAfectacionTerritorio($method, $data, $megaproyecto_nombre, $optData=null, $tipo_impacto_nombre) {        
        $obj = new AfectacionTerritorio($data); //Se carga los datos con los de las tablas        
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
     * Método para crear/modificar un objeto de base de datos
     * 
     * @param string $medthod: create, update
     * @param array $data: Data para autocargar el modelo
     * @param array $optData: Data adicional para autocargar
     * 
     * return object ActiveRecord
     */
    public static function setAfectacionTerritorioByCultivoIlicito($method, $data, $cultivo_nombre, $optData=null, $tipo_impacto_nombre) {        
        $obj = new AfectacionTerritorio($data); //Se carga los datos con los de las tablas        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
                
        
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la afectación $tipo_impacto_nombre al territorio ".$data['territorio_nombre']." causada por el cultivo ilícito ".$cultivo_nombre);
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la afectación $tipo_impacto_nombre al territorio ".$data['territorio_nombre']." causada por el cultivo ilícito ".$cultivo_nombre);
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
    
       public static function deleteAfectacionTerritorio($afectacion_id, $tipo_impacto_id) {        
        $obj = new AfectacionTerritorio(); //Se carga los datos con los de las tablas        
         
        
        $boolean_result = $obj->delete_all("afectacion_id = $afectacion_id AND tipo_impacto_id = $tipo_impacto_id"); 
           
        return ($boolean_result) ? $obj : FALSE;
    }


}
?>
