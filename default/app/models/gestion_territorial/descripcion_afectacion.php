<?php
/**
 *
 * Descripcion: Clase que gestiona los empleos
 *
 * @category
 * @package     Models
 */

class DescripcionAfectacion extends ActiveRecord {
    
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
    public function getDescripcionAfectacionByIniciativaEmpresarialId($iniciativa_empresarial_id) {                   
        $columns = 'descripcion_afectacion.*, iniciativa_empresarial.nombre AS iniciativa_empresarial';        
        $join = 'INNER JOIN iniciativa_empresarial ON iniciativa_empresarial.id = descripcion_afectacion.iniciativa_empresarial_id';
        $conditions = 'descripcion_afectacion.id IS NOT NULL AND iniciativa_empresarial_id = '.$iniciativa_empresarial_id; 
        $order = 'descripcion_afectacion.id';
        $group = '';
              
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "order: $order");
        
    }
    
    public function getTerritorioByIniciativaEmpresarialId($iniciativa_empresarial_id) {                   
        
              
        return $this->find_all_by_sql("SELECT DISTINCT territorio.nombre AS territorio, territorio_id
                                        FROM descripcion_afectacion
                                        INNER JOIN territorio ON territorio.id = descripcion_afectacion.territorio_id
                                        WHERE descripcion_afectacion.id IS NOT NULL 
                                        AND iniciativa_empresarial_id =".$iniciativa_empresarial_id."
                                        ORDER BY descripcion_afectacion.id");

    }
    
     public function getDescripcionAfectacionById($afectacion_id) 
    {                   
        $columns = 'descripcion_afectacion.*, iniciativa_empresarial.nombre AS iniciativa_empresarial';        
        $join = 'INNER JOIN iniciativa_empresarial ON iniciativa_empresarial.id = descripcion_afectacion.iniciativa_empresarial_id';
        $conditions = 'descripcion_afectacion.id='.$afectacion_id;  
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
    public static function setDescripcionAfectacion($method, $data, $iniciativa_empresarial_nombre, $optData=null, $tipo_impacto_nombre) {        
        $obj = new DescripcionAfectacion($data); //Se carga los datos con los de las tablas        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
                
        
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la afectacion $tipo_impacto_nombre al territorio ".$data['territorio_nombre']." causada por la iniciativa empresarial ".$iniciativa_empresarial_nombre);
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la afectacion $tipo_impacto_nombre al territorio ".$data['territorio_nombre']." causada por la iniciativa empresarial ".$iniciativa_empresarial_nombre);
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
    
    
     public static function deleteDescripcionAfectacion($iniciativa_empresarial_id, $tipo_impacto_id) {        
        $obj = new DescripcionAfectacion(); //Se carga los datos con los de las tablas        
         
        
        $boolean_result = $obj->delete_all("iniciativa_empresarial_id = $iniciativa_empresarial_id AND tipo_impacto_id = $tipo_impacto_id"); 
           
        return ($boolean_result) ? $obj : FALSE;
    }


}
?>
