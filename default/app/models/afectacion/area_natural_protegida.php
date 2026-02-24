<?php
/**
 *
 * Descripcion: Clase que gestiona las politicas publicas
 *
 * @category
 * @package     Models
 */

class AreaNaturalProtegida extends ActiveRecord {
    
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
        $this->has_many('afectacion_area_natural_protegida');
        $this->has_many('conflicto_uso');
        $this->belongs_to('tipo_area_natural_protegida'); 
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
    public static function setAreaNaturalProtegida($method, $data, $optData=null) {        
        $obj = new AreaNaturalProtegida($data); //Se carga los datos con los de las tablas        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
       
        $boolean_result = $obj->$method(); 
        
         if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la Área Natural Protegida  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del Área Natural Protegida $obj->nombre");
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
    
    
     /**
     * Método para obtener el listado de las politicas publicas
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoAreaNaturalProtegida($estado='todos', $order='', $page=0) {                   
       
        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'area_natural_protegida.nombre ASC, area_natural_protegida.nombre ASC',
                'DESC' => 'area_natural_protegida.nombre DESC, area_natural_protegida.nombre DESC'
            )));
        
        if($page) { 
            
            return $this->paginated_by_sql("SELECT area_natural_protegida.*,
                tipo_area_natural_protegida.nombre AS tipo_area_natural_protegida_nombre FROM area_natural_protegida
                INNER JOIN tipo_area_natural_protegida ON tipo_area_natural_protegida.id = area_natural_protegida.tipo_area_natural_protegida_id 
                WHERE area_natural_protegida.id IS NOT NULL ORDER BY ".$order, "page: $page");
            /*   
             *             return $this->paginated_by_sql("SELECT area_natural_protegida.*, tipo_area_natural_protegida.nombre AS tipo_area_natural_protegida FROM area_natural_protegida
                INNER JOIN tipo_area_natural_protegida ON tipo_area_natural_protegida.id = area_natural_protegida.tipo_area_natural_protegida_id                
WHERE area_natural_protegida.id IS NOT NULL AND area_natural_protegida.clase_area_natural_protegida ='".$clase_area_natural_protegida."' GROUP BY area_natural_protegida.nombre ORDER BY ".$order);
             */
        }
        
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order");
        
    }
    
     public function getAjaxAreaNaturalProtegida($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) <= 2 OR ($value=='none') ) {
            return NULL;
        }
        $columns = 'area_natural_protegida.*';
        //$join = self::getInnerEstado();
        $join = '';  
        $conditions =  "area_natural_protegida.id IS NOT NULL";
        
         $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'area_natural_protegida.nombre ASC, area_natural_protegida.nombre ASC',
                'DESC' => 'area_natural_protegida.nombre DESC, area_natural_protegida.nombre DESC'
            ),
            'nombre_empresa' => array(
                'ASC' => 'area_natural_protegida.nombre_empresa ASC, area_natural_protegida.nombre_empresa ASC',
                'DESC' => 'area_natural_protegida.nombre_empresa DESC, area_natural_protegida.nombre_empresa DESC'
            )));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('nombre', 'nombre_empresa');
        if(!in_array($field, $fields)) {
            $field = 'nombre';
        }                
        
        $conditions.= " AND area_natural_protegida.$field LIKE '%$value%'";
        
        if($page) {
            
            return $this->paginated_by_sql("SELECT area_natural_protegida.*,
                tipo_area_natural_protegida.nombre AS tipo_area_natural_protegida_nombre FROM area_natural_protegida
                INNER JOIN tipo_area_natural_protegida ON tipo_area_natural_protegida.id = area_natural_protegida.tipo_area_natural_protegida_id 
                WHERE ".$conditions." GROUP BY area_natural_protegida.id ORDER BY ".$order);
        
        } else {
            return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "order: $order");
        }  
    }


}
?>