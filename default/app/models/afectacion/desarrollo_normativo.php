<?php
/**
 *
 * Descripcion: Clase que gestiona las politicas publicas
 *
 * @category
 * @package     Models
 */

class DesarrolloNormativo extends ActiveRecord {
    
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
        $this->belongs_to('megaproyecto');
        $this->belongs_to('subtipo_megaproyecto');
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
    public static function setDesarrolloNormativo($method, $data, $optData=null) {        
        $obj = new DesarrolloNormativo($data); //Se carga los datos con los de las tablas  
        if($obj->fecha_vigencia_inicio != ''){$obj->fecha_vigencia_inicio = date('Y-m-d', strtotime($obj->fecha_vigencia_inicio));}
        if($obj->fecha_vigencia_fin != ''){ $obj->fecha_vigencia_fin = date('Y-m-d', strtotime($obj->fecha_vigencia_fin));}
        if($obj->fecha != ''){ $obj->fecha = date('Y-m-d', strtotime($obj->fecha));}
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
       
        $boolean_result = $obj->$method(); 
        
         if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el Desarrollo Normativo  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del Desarrollo Normativo $obj->nombre");
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
    public function getListadoDesarrolloNormativo($estado='todos', $order='', $page=0) {                   
       
        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'desarrollo_normativo.nombre ASC, desarrollo_normativo.nombre ASC',
                'DESC' => 'desarrollo_normativo.nombre DESC, desarrollo_normativo.nombre DESC'
            )));
        
        if($page) { 
            
            return $this->paginated_by_sql("SELECT desarrollo_normativo.*, tipo_megaproyecto.nombre AS sector FROM desarrollo_normativo 
                INNER JOIN tipo_megaproyecto ON tipo_megaproyecto.id = desarrollo_normativo.tipo_megaproyecto_id WHERE desarrollo_normativo.id IS NOT NULL GROUP BY desarrollo_normativo.nombre ORDER BY ".$order, "page: $page");
            /*   
             *             return $this->paginated_by_sql("SELECT desarrollo_normativo.*, tipo_desarrollo_normativo.nombre AS tipo_desarrollo_normativo FROM desarrollo_normativo
                INNER JOIN tipo_desarrollo_normativo ON tipo_desarrollo_normativo.id = desarrollo_normativo.tipo_desarrollo_normativo_id                
WHERE desarrollo_normativo.id IS NOT NULL AND desarrollo_normativo.clase_desarrollo_normativo ='".$clase_desarrollo_normativo."' GROUP BY desarrollo_normativo.nombre ORDER BY ".$order);
             */
        }
        
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order");
        
    }

    public function getDesarrolloNormativoByMegaproyectoId($megaproyecto_id='') {               
        return $this->find_all_by_sql("SELECT desarrollo_normativo.*, tipo_megaproyecto.nombre AS sector FROM desarrollo_normativo 
        INNER JOIN tipo_megaproyecto ON tipo_megaproyecto.id = desarrollo_normativo.tipo_megaproyecto_id WHERE desarrollo_normativo.id IS NOT NULL AND desarrollo_normativo.megaproyecto_id = $megaproyecto_id ORDER BY desarrollo_normativo.nombre ASC");
           
    }
    
     public function getAjaxDesarrolloNormativo($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) <= 2 OR ($value=='none') ) {
            return NULL;
        }
        $columns = 'desarrollo_normativo.*';
        //$join = self::getInnerEstado();
        $join = '';  
        $conditions =  "desarrollo_normativo.id IS NOT NULL";
        
         $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'desarrollo_normativo.nombre ASC, desarrollo_normativo.nombre ASC',
                'DESC' => 'desarrollo_normativo.nombre DESC, desarrollo_normativo.nombre DESC'
            ),
            'nombre_empresa' => array(
                'ASC' => 'desarrollo_normativo.nombre_empresa ASC, desarrollo_normativo.nombre_empresa ASC',
                'DESC' => 'desarrollo_normativo.nombre_empresa DESC, desarrollo_normativo.nombre_empresa DESC'
            )));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('nombre', 'nombre_empresa');
        if(!in_array($field, $fields)) {
            $field = 'nombre';
        }                
        
        $conditions.= " AND desarrollo_normativo.$field LIKE '%$value%'";
        
        if($page) {
            
            return $this->paginated_by_sql("SELECT desarrollo_normativo.*, tipo_megaproyecto.nombre AS sector
                FROM desarrollo_normativo INNER JOIN tipo_megaproyecto ON tipo_megaproyecto.id = desarrollo_normativo.tipo_megaproyecto_id
                WHERE ".$conditions." GROUP BY desarrollo_normativo.id ORDER BY ".$order);
        
        } else {
            return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "order: $order");
        }  
    }


}
?>