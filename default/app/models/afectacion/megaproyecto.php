<?php
/**
 *
 * Descripcion: Clase que gestiona los municipios observados
 *
 * @category
 * @package     Models
 */


class Megaproyecto extends ActiveRecord {    
   
    
    /**
     * Constante para definir un megaproyecto como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un megaproyecto como inactivo
     */
    const INACTIVO = 2;
    
    
    const HIDROCARBURO = 'Megaproyecto de Hidrocarburo';
    const TIPO_HIDROCARBURO = 'hidrocarburo';
    const INFRAESTRUCTURA = 'Megaproyecto de Infraestructura';
    const TIPO_INFRAESTRUCTURA = 'infraestrucura';
    const MINERIA = 'Megaproyecto de Mineria';
    const TIPO_MINERIA = 'mineria';
    const AGROINDUSTRIA = 'Megaproyecto de Agroindustria';
    const TIPO_AGROINDUSTRIA = 'agroindustria';
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('afectacion');
        $this->has_many('desarrollo_normativo');
        $this->belongs_to('subtipo_megaproyecto');
    }
    
     public static function getMegaproyectoPorNombre($nombre)
    {
        $obj = new Megaproyecto();        
        $obj->find_first("nombre='".$nombre."'");
        
        return $obj;
    }
    
     public function getMegaproyectoPorDepartamento($departamento_id)
    {
        $columns = 'megaproyecto.*';  
        $conditions = 'megaproyecto.id IS NOT NULL AND megaproyecto.departamento_id='.$departamento_id; 
        $order = 'megaproyecto.nombre ASC';        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
    }
    
    /**
     * Método para obtener el listado de los megaproyectos observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoMegaproyecto($estado='todos', $order='', $clase_megaproyecto='', $page=0) {      
        return $this->find_all_by_sql("SELECT 
    mp.id,
    mp.nombre,
    mp.clase_megaproyecto,
    mp.nombre_empresa,
    mp.nivel,
    mp.estado,

    GROUP_CONCAT(
        DISTINCT CONCAT_WS(' - ',
            d.nombre,
            m.nombre,
            t.nombre
        )
        ORDER BY d.nombre, m.nombre, t.nombre
        SEPARATOR ' | '
    ) AS ubicacion

FROM megaproyecto mp

INNER JOIN afectacion a 
    ON a.id = mp.afectacion_id 
   AND a.estado = 1

LEFT JOIN ubicacion u 
    ON u.afectacion_id = a.id

LEFT JOIN departamento d 
    ON d.id = u.departamento_id

LEFT JOIN municipio m 
    ON m.id = u.municipio_id

LEFT JOIN territorio t 
    ON t.id = u.territorio_id

WHERE mp.clase_megaproyecto = '".$clase_megaproyecto."'

GROUP BY mp.id
ORDER BY mp.nombre;
");
       
        
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
    public static function setMegaproyecto($method, $data, $optData=null) {        
        $obj = new Megaproyecto($data); //Se carga los datos con los de las tablas  
        if($obj->fecha_iniciacion != ''){$obj->fecha_iniciacion = date('Y-m-d', strtotime($obj->fecha_iniciacion));}
        if($obj->fecha_terminacion != ''){ $obj->fecha_terminacion = date('Y-m-d', strtotime($obj->fecha_terminacion));}        
        $boolean_result = TRUE;
        
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Megaproyecto();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Megaproyecto::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Megaproyecto::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un megaproyecto registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el Megaproyecto  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del Megaproyacto $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    
    public function getAjaxMegaproyecto($field, $value, $order='', $page=0) {
        $value = Filter::get($value, 'string');
        if( strlen($value) <= 2 OR ($value=='none') ) {
            return NULL;
        }
        $columns = 'megaproyecto.*';
        //$join = self::getInnerEstado();
        $join = '';  
        $clase_megaproyecto = Session::get('clase_megaproyecto');
        $conditions =  "megaproyecto.id IS NOT NULL AND megaproyecto.clase_megaproyecto ='".$clase_megaproyecto."'";
        
         $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'megaproyecto.nombre ASC, megaproyecto.nombre ASC',
                'DESC' => 'megaproyecto.nombre DESC, megaproyecto.nombre DESC'
            ),
            'nombre_empresa' => array(
                'ASC' => 'megaproyecto.nombre_empresa ASC, megaproyecto.nombre_empresa ASC',
                'DESC' => 'megaproyecto.nombre_empresa DESC, megaproyecto.nombre_empresa DESC'
            )));
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('nombre', 'nombre_empresa');
        if(!in_array($field, $fields)) {
            $field = 'nombre';
        }                
        
        $conditions.= " AND $field LIKE '%$value%'";
        
        if($page) {
            //return $this->paginated("columns: $columns", "join: $join", "conditions: $conditions", "order: $order", "page: $page");
            return $this->paginated_by_sql("SELECT megaproyecto.* FROM megaproyecto WHERE ".$conditions." GROUP BY megaproyecto.id ORDER BY ".$order);
        
        } else {
            return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "order: $order");
        }  
    }
    
    
    
}


?>
