<?php
/**
 *
 * Descripcion: Clase que gestiona las subregiones observadas
 *
 * @category
 * @package     Models
 */


class Subregion extends ActiveRecord {    
   
    
    /**
     * Constante para definir una subregion como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir una subregion como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->has_many('municipio');
        $this->belongs_to('departamento');
    }
    
//     public static function getSubregionPorNombre($nombre)
//    {
//        $obj = new Subregion();        
//        $obj->find_first("nombre='".$nombre."'");
//        
//        return $obj;
//    }
    
     public function getSubregionPorDepartamento($departamento_id=NULL)
    {
         if((int)$departamento_id)
        {
            $columns = 'subregion.*';  
            $conditions = 'subregion.id IS NOT NULL AND subregion.departamento_id='.$departamento_id; 
            $order = 'subregion.nombre ASC';        
            return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        }else{
            return array();
        }        
    }
    
    /**
     * Método para obtener el listado de las subregiones observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoSubregion($estado='todos', $order='', $page=0) {                   
       
            return $this->find_all_by_sql('SELECT 
    subregion.*,
    departamento.nombre AS departamento,
    COUNT(municipio.id) AS cant_municipio
FROM subregion
INNER JOIN departamento ON departamento.id = subregion.departamento_id
LEFT JOIN municipio ON municipio.subregion_id = subregion.id
WHERE subregion.id IS NOT NULL
GROUP BY subregion.id
ORDER BY subregion.nombre ASC');
        
        
    }
    
    /**
     * Método para crear/modificar un objeto de base de datos
     * 
     * @param string $medthod: create, update
     * @param array $data: Data para autocargar el modelo
     * @param array $data_poblacion: Data para autocargar el modelo poblacion
     * @param array $optData: Data adicional para autocargar
     * 
     * return object ActiveRecord
     */
    public static function setSubregion($method, $data, $optData=null) {        
        $obj = new Subregion($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Subregion();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Subregion::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Subregion::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe una subregion registrada bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la subregion  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de la subregion $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el subregion  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del subregion $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    
    /**
     * Método para obtener la información de una subregion
     * @return type
     */
    public function getInformacionSubregion($subregion) {
        $subregion = Filter::get($subregion, 'int');
        if(!$subregion) {
            return NULL;
        }
        $columnas = 'subregion.*, departamento.nombre AS departamento';
        //$join = self::getInnerEstado();
        $join= 'INNER JOIN departamento ON departamento.id = subregion.departamento_id ';        
        $condicion = "subregion.id = $subregion";        
        return $this->find_first("columns: $columnas", "join: $join", "conditions: $condicion");
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
    
    
    public function getAjaxSubregion($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) <= 2 OR ($value=='none') ) {
            return NULL;
        }
        $columns = 'subregion.*';
        //$join = self::getInnerEstado();
        $join = '';        
        $conditions = "subregion.id IS NOT NULL";
        
         $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'subregion.nombre ASC, subregion.nombre ASC',
                'DESC' => 'subregion.nombre DESC, subregion.nombre DESC'
            ),
             'departamento' => array(
                'ASC' => 'departamento ASC, departamento ASC',
                'DESC' => 'departamento DESC, departamento DESC'
            ),
            'cant_territorio' => array(
                'ASC' => 'cant_municipio ASC, cant_municipio ASC',
                'DESC' => 'cant_municipio DESC, cant_municipio DESC'
            )));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('nombre', 'departamento');
        if(!in_array($field, $fields)) {
            $field = 'nombre';
        }         
        
        $field_convert = '';
        if($field == 'nombre'){ $field_convert = 'subregion.nombre';}
        if($field == 'departamento'){ $field_convert = 'departamento.nombre';}
        
        $conditions.= " AND $field_convert LIKE '%$value%'";
        
        if($page) {
            //return $this->paginated("columns: $columns", "join: $join", "conditions: $conditions", "order: $order", "page: $page");
            return $this->paginated_by_sql('SELECT subregion.*, departamento.nombre AS departamento,
                (SELECT COUNT(municipio.subregion_id) FROM municipio WHERE municipio.subregion_id = subregion.id) AS cant_municipio
                FROM subregion INNER JOIN departamento ON departamento.id = subregion.departamento_id 
                WHERE '.$conditions.' GROUP BY subregion.nombre ORDER BY '.$order);
            
            //$rim = 'SELECT subregion.*, departamento.nombre AS departamento FROM subregion INNER JOIN departamento ON departamento.id = subregion.departamento_id WHERE '.$conditions.' GROUP BY subregion.id ORDER BY'.$order;
        
        } else {
            return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "order: $order");
        }  
    }



    
}
?>

