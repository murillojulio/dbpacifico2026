<?php
/**
 *
 * Descripcion: Clase que gestiona las politicas publicas
 *
 * @category
 * @package     Models
 */

class PoliticaPublica extends ActiveRecord {
    
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
    public static function setPoliticaPublica($method, $data, $optData=null) {        
        $obj = new PoliticaPublica($data); //Se carga los datos con los de las tablas  
        if($obj->fecha_vigencia_inicio != ''){$obj->fecha_vigencia_inicio = date('Y-m-d', strtotime($obj->fecha_vigencia_inicio));}
        if($obj->fecha_vigencia_fin != ''){ $obj->fecha_vigencia_fin = date('Y-m-d', strtotime($obj->fecha_vigencia_fin));}
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
       
        $boolean_result = $obj->$method(); 
        
        
         if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la Política Pública  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de la Política Pública $obj->nombre");
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
    
    
     /**
     * Método para obtener el listado de las politicas publicas
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoPoliticaPublica($estado='todos', $order='', $page=0) {                   
       
        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'politica_publica.nombre ASC, politica_publica.nombre ASC',
                'DESC' => 'politica_publica.nombre DESC, politica_publica.nombre DESC'
            )));
        
        if($page) { 
            
            return $this->paginated_by_sql("SELECT politica_publica.*, tipo_megaproyecto.nombre AS sector FROM politica_publica 
                INNER JOIN tipo_megaproyecto ON tipo_megaproyecto.id = politica_publica.tipo_megaproyecto_id WHERE politica_publica.id IS NOT NULL GROUP BY politica_publica.nombre ORDER BY ".$order, "page: $page");
            /*   
             *             return $this->paginated_by_sql("SELECT politica_publica.*, tipo_politica_publica.nombre AS tipo_politica_publica FROM politica_publica
                INNER JOIN tipo_politica_publica ON tipo_politica_publica.id = politica_publica.tipo_politica_publica_id                
WHERE politica_publica.id IS NOT NULL AND politica_publica.clase_politica_publica ='".$clase_politica_publica."' GROUP BY politica_publica.nombre ORDER BY ".$order);
             */
        }
        
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order");
        
    }
    
     public function getAjaxPoliticaPublica($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) <= 2 OR ($value=='none') ) {
            return NULL;
        }
        $columns = 'politica_publica.*';
        //$join = self::getInnerEstado();
        $join = '';  
        $conditions =  "politica_publica.id IS NOT NULL";
        
         $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'politica_publica.nombre ASC, politica_publica.nombre ASC',
                'DESC' => 'politica_publica.nombre DESC, politica_publica.nombre DESC'
            ),
            'nombre_empresa' => array(
                'ASC' => 'politica_publica.nombre_empresa ASC, politica_publica.nombre_empresa ASC',
                'DESC' => 'politica_publica.nombre_empresa DESC, politica_publica.nombre_empresa DESC'
            )));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('nombre', 'nombre_empresa');
        if(!in_array($field, $fields)) {
            $field = 'nombre';
        }                
        
        $conditions.= " AND politica_publica.$field LIKE '%$value%'";
        
        if($page) {
            
            return $this->paginated_by_sql("SELECT politica_publica.*, tipo_megaproyecto.nombre AS sector
                FROM politica_publica INNER JOIN tipo_megaproyecto ON tipo_megaproyecto.id = politica_publica.tipo_megaproyecto_id
                WHERE ".$conditions." GROUP BY politica_publica.id ORDER BY ".$order);
        
        } else {
            return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "order: $order");
        }  
    }

    public function getPoliticaPublicaByMegaproyectoId($megaproyecto_id='') {               
        return $this->find_all_by_sql("SELECT politica_publica.*, tipo_megaproyecto.nombre AS sector FROM politica_publica 
        INNER JOIN tipo_megaproyecto ON tipo_megaproyecto.id = politica_publica.tipo_megaproyecto_id WHERE politica_publica.id IS NOT NULL AND politica_publica.megaproyecto_id = $megaproyecto_id ORDER BY politica_publica.nombre ASC");
           
    }


}
?>