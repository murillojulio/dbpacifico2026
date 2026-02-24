<?php
/**
 *
 * Descripcion: Clase que gestiona los empleos
 *
 * @category
 * @package     Models
 */

class IniciativaEmpresarial extends ActiveRecord {
    
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
        $this->has_many('tipo_iniciativa_empresarial');        
    }
    
    /**
     * Método para obtener el listado de los empleos de los megaproyectos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getIniciativaEmpresarialsByTerritorioId($territorio_id) {                   
        $columns = 'iniciativa_empresarial.*, territorio.nombre AS territorio, tipo_iniciativa_empresarial.nombre AS tipo_iniciativa, tipo_actividad_productiva.nombre AS tipo_actividad';        
        $join = 'INNER JOIN territorio ON territorio.id = iniciativa_empresarial.territorio_id';
        $join .= ' INNER JOIN tipo_iniciativa_empresarial ON tipo_iniciativa_empresarial.id = iniciativa_empresarial.tipo_iniciativa_empresarial_id';
        $join .= ' INNER JOIN tipo_actividad_productiva ON tipo_actividad_productiva.id = iniciativa_empresarial.tipo_actividad_productiva_id';
        $conditions = 'iniciativa_empresarial.id IS NOT NULL AND territorio_id='.$territorio_id; 
        $order = 'iniciativa_empresarial.nombre';
        $group = 'iniciativa_empresarial.nombre';
              
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order");
        
    }
    
     public function getIniciativaEmpresarialById($iniciativa_empresarial_id) 
    {                   
        $columns = 'iniciativa_empresarial.*, territorio.nombre AS territorio';        
        $join = 'INNER JOIN territorio ON territorio.id = iniciativa_empresarial.territorio_id';
        $conditions = 'iniciativa_empresarial.id='.$iniciativa_empresarial_id;  
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
    public static function setIniciativaEmpresarial($method, $data, $territorio_nombre, $optData=null) {        
        $obj = new IniciativaEmpresarial($data); //Se carga los datos con los de las tablas        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
   
        
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la Iniciativa Empresarial: $obj->nombre en el sistema, pertenece al territorio ".$territorio_nombre);
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de la Iniciativa Empresarial: $obj->ombre, pertenece al territorio ".$territorio_nombre);
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
