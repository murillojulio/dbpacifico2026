<?php
/**
 *
 * Descripcion: Clase que gestiona los municipios observados
 *
 * @category
 * @package     Models
 */


class Municipio extends ActiveRecord {    
   
    
    /**
     * Constante para definir un municipio como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un municipio como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->has_many('territorio_municipio');
        $this->belongs_to('departamento');
        $this->belongs_to('subregion');
        $this->has_many('localidad');
    }
    
//     public static function getMunicipioPorNombre($nombre)
//    {
//        $obj = new Municipio();        
//        $obj->find_first("nombre='".$nombre."'");
//        
//        return $obj;
//    }
    
     public function getMunicipioPorDepartamento($departamento_id=NULL)
    {
         if((int)$departamento_id)
        {
            $columns = 'municipio.*';  
            $conditions = 'municipio.id IS NOT NULL AND municipio.departamento_id='.$departamento_id; 
            $order = 'municipio.nombre ASC';        
            return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        }else{
            return array();
        }        
    }
    
    /**
     * Método para obtener el listado de los municipios observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoMunicipio($estado = 'todos')
{
    

    $sql = "
        SELECT 
            municipio.*,
            subregion.nombre AS subregion,
            departamento.nombre AS departamento,
            (
                SELECT COUNT(*)
                FROM territorio_municipio
                WHERE territorio_municipio.municipio_id = municipio.id
            ) AS cant_territorio
        FROM municipio
        INNER JOIN departamento ON departamento.id = municipio.departamento_id
        INNER JOIN subregion ON subregion.id = municipio.subregion_id
        WHERE municipio.id IS NOT NULL
          AND municipio.id != 0
        ORDER BY municipio.nombre ASC
    ";

    return $this->find_all_by_sql($sql);
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
    public static function setMunicipio($method, $data, $optData=null) {        
        $obj = new Municipio($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Municipio();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Municipio::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Municipio::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un municipio registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del municipio $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    
    /**
     * Método para obtener la información de un municipio
     * @return type
     */
    public function getInformacionMunicipio($municipio) {
        $municipio = Filter::get($municipio, 'int');
        if(!$municipio) {
            return NULL;
        }
        $columnas = 'municipio.*, departamento.nombre AS departamento';
        //$join = self::getInnerEstado();
        $join= 'INNER JOIN departamento ON departamento.id = municipio.departamento_id ';        
        $condicion = "municipio.id = $municipio";        
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
    
    
    public function getAjaxMunicipio($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) <= 2 OR ($value=='none') ) {
            return NULL;
        }
        $columns = 'municipio.*';
        //$join = self::getInnerEstado();
        $join = '';        
        $conditions = "municipio.id IS NOT NULL";
        
         $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'municipio.nombre ASC, municipio.nombre ASC',
                'DESC' => 'municipio.nombre DESC, municipio.nombre DESC'
            ),
             'subregion' => array(
                'ASC' => 'subregion ASC, subregion ASC',
                'DESC' => 'subregion DESC, subregion DESC'
            ),
             'departamento' => array(
                'ASC' => 'departamento ASC, departamento ASC',
                'DESC' => 'departamento DESC, departamento DESC'
            ),
            'cant_territorio' => array(
                'ASC' => 'cant_territorio ASC, cant_territorio ASC',
                'DESC' => 'cant_territorio DESC, cant_territorio DESC'
            )));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('nombre', 'subregion', 'departamento');
        if(!in_array($field, $fields)) {
            $field = 'nombre';
        }         
        
        $field_convert = '';
        if($field == 'nombre'){ $field_convert = 'municipio.nombre';}
        if($field == 'subregion'){ $field_convert = 'subregion.nombre';}
        if($field == 'departamento'){ $field_convert = 'departamento.nombre';}
        
        $conditions.= " AND $field_convert LIKE '%$value%'";
        
        if($page) {
            //return $this->paginated("columns: $columns", "join: $join", "conditions: $conditions", "order: $order", "page: $page");
            return $this->paginated_by_sql('SELECT municipio.*, subregion.nombre AS subregion, departamento.nombre AS departamento,
                (SELECT COUNT(territorio_municipio.municipio_id) FROM territorio_municipio WHERE territorio_municipio.municipio_id = municipio.id) AS cant_territorio
                FROM municipio INNER JOIN departamento ON departamento.id = municipio.departamento_id 
                INNER JOIN subregion ON subregion.id = municipio.subregion_id 
                WHERE '.$conditions.' GROUP BY municipio.nombre ORDER BY '.$order, "page: $page");
            
            //$rim = 'SELECT municipio.*, departamento.nombre AS departamento FROM municipio INNER JOIN departamento ON departamento.id = municipio.departamento_id WHERE '.$conditions.' GROUP BY municipio.id ORDER BY'.$order;
        
        } else {
            return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "order: $order");
        }  
    }

     /**
     * Método para obtener el listado de los departamentos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoMunicipioDBS() {                   
        $columns = 'municipio.*';  
        $conditions = 'municipio.id != 0 AND municipio.id IS NOT NULL AND municipio.estado = 1'; 
        $order = 'municipio.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }



    
}
?>

