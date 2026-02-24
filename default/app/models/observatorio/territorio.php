<?php
/**
 *
 * Descripcion: Clase que gestiona los territorios observados
 *
 * @category
 * @package     Models
 */
require_once APP_PATH . 'libs/dw_audit.php';
class Territorio extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    //public $logger = FALSE;
       
    /**
     * Constante para definir un territorio como publico
     */
    const PUBLICO = 1;    
    /**
     * Constante para definir un territorio como privado
     */
    const PRIVADO = 2;
     /**
     * Constante para definir la informacion de un territorio como completa
     */
    const COMPLETADO = 2;    
    /**
     * Constante para definir la informacion de un territorio como borrador
     */
    const BORRADOR = 1;
    
    const ACTIVO = 1;
    const INACTIVO = 2;




    /**
     * Constante para definir un perfil como inactivo
     */
    const TERRITORIO_COMUNIDAD_NEGRA = 'Territorios Colectivos Comunidades Negras';
    const TIPO_TERRITORIO_COMUNIDAD_NEGRA = 'comunidad_negra';
    
    /**
     * Constante para definir un perfil como inactivo
     */
    const TERRITORIO_INDIGENA = 'Territorios Colectivos Resguardos Indigena';
    const TIPO_TERRITORIO_INDIGENA = 'indigena';
    
    /**
     * Constante para definir un perfil como inactivo
     */
    const TERRITORIO_URBANO = 'Territorios Urbanos';
    const TIPO_TERRITORIO_URBANO = 'urbano';
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() 
    {
        $this->has_many('actor_armado');
        $this->has_many('territorio_municipio');
        $this->has_one('jurisdiccion_especial_indigena');
        $this->has_one('plan_de_vida');
    }
    
    /**
     * Método para obtener el listado de los territorios observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoTerritorio($tipo, $order='', $page=0) {         
        
        $usuario = Usuario::getUsuarioLogueado();
        $usuario->getUsuarioLogueado();
        $condicion=' ';
        
        if($usuario->departamento_id != 0)
        {
            $condicion = ' t.departamento_id = '.$usuario->departamento_id.' AND ';
        }

        if($page) {        
            if ($tipo == 'comunidad_negra')
            {
                return $this->find_all_by_sql('SELECT 
    t.*,
    d.nombre AS departamento,
    COUNT(DISTINCT cns.id) AS cant_consejo_reg,
    COUNT(DISTINCT ca.id)  AS cant_caso_reg
FROM territorio t
INNER JOIN departamento d 
    ON d.id = t.departamento_id
LEFT JOIN consejo cns 
    ON cns.territorio_id = t.id
LEFT JOIN caso ca 
    ON ca.territorio_id = t.id
WHERE'.$condicion.'t.tipo = "'.$tipo.'"
GROUP BY 
    t.id
ORDER BY 
    t.nombre ASC;
');                 
            }
            elseif ($tipo == 'indigena')
            {
                return $this->find_all_by_sql('SELECT 
    t.*,
    d.nombre AS departamento,
    COUNT(DISTINCT cb.id) AS cant_cabildo_reg,
    COUNT(DISTINCT ca.id) AS cant_caso_reg
FROM territorio t
INNER JOIN departamento d 
    ON d.id = t.departamento_id
LEFT JOIN cabildo cb
    ON cb.territorio_id = t.id
LEFT JOIN caso ca
    ON ca.territorio_id = t.id
WHERE'.$condicion.'t.tipo = "'.$tipo.'"
GROUP BY 
    t.id
ORDER BY 
    t.nombre ASC;
');                 
            }
            elseif ($tipo == 'urbano')
            {
                return $this->find_all_by_sql('SELECT 
    t.*,
    d.nombre AS departamento,
    COUNT(DISTINCT o.id) AS cant_organizacion_reg,
    COUNT(DISTINCT c.id) AS cant_caso_reg
FROM territorio t
INNER JOIN departamento d 
    ON d.id = t.departamento_id
LEFT JOIN organizacion o
    ON o.territorio_id = t.id
LEFT JOIN caso c
    ON c.territorio_id = t.id
WHERE'.$condicion.'t.tipo = "'.$tipo.'"
GROUP BY 
    t.id
ORDER BY 
    t.nombre ASC;
');                 
            }
            
        }
        
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order");
        
   }
   
     public function getTerritorioById($territorio_id) 
    {                   
        $columns = 'territorio.*, departamento.nombre AS departamento';        
        $join = 'INNER JOIN departamento ON departamento.id = territorio.departamento_id';
        $conditions = 'territorio.id='.$territorio_id;  
        return $this->find_first("columns: $columns", "join: $join", "conditions: $conditions");        
    }
    
    /**
     * Método para buscar un territorio
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getTerritorio($conditions) {                   
        
        return $this->find("conditions: $conditions", "order: territorio.nombre ASC");
        
    }
    
     /**
     * Método para validar la existencia de un territorio
     * @param type $territorio_nombre
     * @param type $dataMunicipio
     * @return type
     */
    public function validateExistTerritorio($territorio_nombre, $dataMunicipio) { 
        $existe = FALSE;
        $territorio = new Territorio();
        
        if($this->find_first("conditions: territorio.nombre LIKE '%".$territorio_nombre."%'"))
        {
            $territorio_municipio = new TerritorioMunicipio();
           
            for($i = 0 ; $i < count($dataMunicipio) ; $i++)
            {
                if($territorio_municipio->find("conditions: territorio_municipio.territorio_id = ".$this->id." AND territorio_municipio.municipio_id =".$dataMunicipio[$i]))
                {
                   $existe = TRUE;
                }
                else {
                   $existe = FALSE;
                }                    
            }     
        } 
        return $existe;        
    }


     /**
     * Método para validar la existencia de un territorio
     * @param type $territorio_nombre
     * @param type $dataMunicipio
     * @return type
     */
    public function validateExistTerritorioUrbano($territorio_nombre, $municipio_id) { 
        $existe = FALSE;
        $territorio = new Territorio();
        
        if($this->find_first("conditions: territorio.nombre LIKE '%".$territorio_nombre."%'"))
        {
            $territorio_municipio = new TerritorioMunicipio();
            if($territorio_municipio->find("conditions: territorio_municipio.territorio_id = ".$this->id." AND territorio_municipio.municipio_id =".$municipio_id))
            {
                $existe = TRUE;
            }
            else {
                $existe = FALSE;
            }             
        } 
        return $existe;        
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
    public static function setTerritorio($method, $data, $optData=null) {        
        $obj = new Territorio($data); //Se carga los datos con los de las tablas        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new Territorio();
        if($old->find_first($conditions)) {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != Territorio::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = Territorio::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un territorio registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method(); 
        
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el territorio  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del territorio $obj->nombre");
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
    
    
    
            
     public function getAjaxTerritorio($field, $value, $order='', $page=0, $tipo) {
        $value = Filter::get($value, 'string');
        if( strlen($value) <= 2 OR ($value=='none') ) {
            return NULL;
        }
        //$tipo = 'comunidad_negra';
        $columns = 'territorio.*, departamento.nombre AS departamento, 
            (SELECT COUNT(caso.id) FROM caso WHERE caso.territorio_id = territorio.id) AS cant_caso_reg,
            (SELECT COUNT(actor_armado.presunto_responsable_id) FROM actor_armado WHERE actor_armado.territorio_id = territorio.id) AS cant_actor_armado_reg,';        
        $join = 'INNER JOIN departamento ON departamento.id = territorio.departamento_id';
        $conditions = 'territorio.id IS NOT NULL AND tipo=\''.$tipo.'\''; 

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'territorio.nombre ASC, territorio.nombre ASC',
                'DESC' => 'territorio.nombre DESC, territorio.nombre DESC'
            ), 
            'titulado' => array(
                'ASC' => 'territorio.titulado ASC, territorio.titulado ASC',
                'DESC' => 'territorio.titulado DESC, territorio.titulado DESC'
            ),
            'departamento' => array(
                'ASC' => 'departamento ASC, departamento',
                'DESC' => 'departamento DESC, departamento DESC'
            )
            ));
        $group = 'territorio.nombre';
        
        //Defino los campos habilitados para la búsqueda
        $fields = array('nombre del terriotorio', 'titulado', 'departamento');
        if(!in_array($field, $fields)) {
            $field = 'nombre del terriotorio';
        }         
        
        $field_convert = '';
        if($field == 'nombre del terriotorio'){ $field_convert = 'territorio.nombre';}
        if($field == 'departamento'){ $field_convert = 'departamento.nombre';}
        if($field == 'titulado'){ $field_convert = 'territorio.titulado';}
        
        $conditions.= " AND $field_convert LIKE '%$value%'";
        
        if($page) {
            if ($tipo == 'comunidad_negra')
            {
                return $this->paginated_by_sql('SELECT territorio.*, departamento.nombre AS departamento,
                (SELECT COUNT(consejo.id) FROM consejo WHERE consejo.territorio_id = territorio.id) AS cant_consejo_reg,
                /*(SELECT COUNT(actor_armado.presunto_responsable_id) FROM actor_armado WHERE actor_armado.territorio_id = territorio.id) AS cant_actor_armado_reg,*/
                (SELECT COUNT(caso.id) FROM caso WHERE caso.territorio_id = territorio.id) AS cant_caso_reg FROM territorio 
                INNER JOIN departamento ON departamento.id = territorio.departamento_id                
                WHERE '.$conditions.' ORDER BY '.$order, "page: $page");                 
            }
            elseif ($tipo == 'indigena')
            {
                return $this->paginated_by_sql('SELECT territorio.*, departamento.nombre AS departamento,
                (SELECT COUNT(cabildo.id) FROM cabildo WHERE cabildo.territorio_id = territorio.id) AS cant_cabildo_reg,
                /*(SELECT COUNT(actor_armado.presunto_responsable_id) FROM actor_armado WHERE actor_armado.territorio_id = territorio.id) AS cant_actor_armado_reg,*/
                (SELECT COUNT(caso.id) FROM caso WHERE caso.territorio_id = territorio.id) AS cant_caso_reg FROM territorio 
                INNER JOIN departamento ON departamento.id = territorio.departamento_id                
                WHERE '.$conditions.' ORDER BY '.$order, "page: $page");                 
            }
            elseif ($tipo == 'urbano')
            {
                return $this->paginated_by_sql('SELECT territorio.*, departamento.nombre AS departamento,
                (SELECT COUNT(consejo.id) FROM consejo WHERE consejo.territorio_id = territorio.id) AS cant_consejo_reg,
                /*(SELECT COUNT(actor_armado.presunto_responsable_id) FROM actor_armado WHERE actor_armado.territorio_id = territorio.id) AS cant_actor_armado_reg,*/
                (SELECT COUNT(caso.id) FROM caso WHERE caso.territorio_id = territorio.id) AS cant_caso_reg FROM territorio 
                INNER JOIN departamento ON departamento.id = territorio.departamento_id                
                WHERE '.$conditions.' ORDER BY '.$order, "page: $page");                 
            }
                    
        } else {
            return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "order: $order");
        }  
    }
    
//    public function getAjaxTerritorio($field, $value, $order='', $page=0, $tipo) {
//        $value = Filter::get($value, 'string');
//        if( strlen($value) <= 2 OR ($value=='none') ) {
//            return NULL;
//        }
//        //$tipo = 'comunidad_negra';
//        $columns = 'territorio.*, departamento.nombre AS departamento, (SELECT COUNT(caso.id) FROM caso WHERE caso.territorio_id = territorio.id) AS cant_caso_reg';        
//        $join = 'INNER JOIN departamento ON departamento.id = territorio.departamento_id';
//        $conditions = 'territorio.id IS NOT NULL AND tipo=\''.$tipo.'\''; 
//
//        $order = $this->get_order($order, 'nombre', array(            
//            'nombre' => array(
//                'ASC' => 'territorio.nombre ASC, territorio.nombre ASC',
//                'DESC' => 'territorio.nombre DESC, territorio.nombre DESC'
//            ), 
//            'titulado' => array(
//                'ASC' => 'territorio.titulado ASC, territorio.titulado ASC',
//                'DESC' => 'territorio.titulado DESC, territorio.titulado DESC'
//            ),
//            'departamento' => array(
//                'ASC' => 'departamento ASC, departamento',
//                'DESC' => 'departamento DESC, departamento DESC'
//            )
//            ));
//        $group = 'territorio.nombre';
//        
//        //Defino los campos habilitados para la búsqueda
//        $fields = array('nombre del terriotorio', 'titulado', 'departamento');
//        if(!in_array($field, $fields)) {
//            $field = 'nombre del terriotorio';
//        }         
//        
//        $field_convert = '';
//        if($field == 'nombre del terriotorio'){ $field_convert = 'territorio.nombre';}
//        if($field == 'departamento'){ $field_convert = 'departamento.nombre';}
//        if($field == 'titulado'){ $field_convert = 'territorio.titulado';}
//        
//        $conditions.= " AND $field_convert LIKE '%$value%'";
//        
//        if($page) {
//            return $this->paginated("columns: $columns", "join: $join", "conditions: $conditions", "order: $order", "page: $page");      
//                    
//        } else {
//            return $this->find("columns: $columns", "join: $join", "conditions: $conditions", "order: $order");
//        }  
//    }
    
    
     public function getListadoTerritorioByDepartamentoIdDBS($departamento_id) {                   
        $columns = 'territorio.*';  
        $conditions = 'territorio.id IS NOT NULL AND territorio.estado = 1 AND territorio.departamento_id ='.$departamento_id; 
        $order = 'territorio.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        
    }
    
    public function getListadoTerritoriosDBS($afectacion_id = NULL, $territorio_id = NULL) {    
        if((int)$afectacion_id)
        {    
            $sql = "SELECT territorio.id, territorio.nombre FROM territorio WHERE territorio.id IN (SELECT ubicacion.territorio_id FROM ubicacion WHERE ubicacion.afectacion_id = $afectacion_id)";
            return $this->find_all_by_sql($sql);
        } 
        else if((int)$territorio_id){
            $sql = "SELECT territorio.* FROM territorio WHERE territorio.id = $territorio_id";
            return $this->find_all_by_sql($sql);
        }
        else{        
        $columns = 'territorio.*';  
        $conditions = 'territorio.id IS NOT NULL AND territorio.estado = 1 AND territorio.id != 0'; 
        $order = 'territorio.nombre ASC';
        
        return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        }
        
    }



}
?>
