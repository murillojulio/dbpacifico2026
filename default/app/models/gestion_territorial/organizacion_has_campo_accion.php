<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * organizacions y con su respectivo campo_accion
 *
 * @category
 * @package     Models 
 */

class OrganizacionHasCampoAccion extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('organizacion');
        $this->belongs_to('campo_accion');
    }

    
    
    /**
     * Método para registrar los privilegios a los perfiles
     */
    public static function setOrganizacionHasCampoAccion($method, $data, $organizacion_id) {   

        $cantidad_campo_accions = count($data);    
        $obj_OrganizacionHasCampoAccion = new OrganizacionHasCampoAccion();
        $boolean_result = FALSE;
        
        for($i = 0 ; $i < $cantidad_campo_accions ; $i++)
        {
             $array = array(
                                "organizacion_id" => $organizacion_id,
                                "campo_accion_id" => $data[$i],
                                );
            $obj_OrganizacionHasCampoAccion = new OrganizacionHasCampoAccion($array);
            $boolean_result = $obj_OrganizacionHasCampoAccion->$method();            
        }              
        return ($boolean_result) ? $obj_OrganizacionHasCampoAccion : FALSE;               
       
    }
    
    public function getOrganizacionHasCampoAccion($organizacion_id) 
    {                   
        $columns = 'organizacion_has_campo_accion.*, campo_accion.nombre AS campo_accion';        
        $join = 'INNER JOIN campo_accion ON campo_accion.id = organizacion_has_campo_accion.campo_accion_id';
        $conditions = 'organizacion_has_campo_accion.id IS NOT NULL AND organizacion_has_campo_accion.organizacion_id='.$organizacion_id;  
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions");        
    }
    
     public function getTerritoriosByMunicipioId($campo_accion_id , $order='', $page=0) 
    {    
         //SELECT campo_accion.nombre AS campo_accion_nombre, organizacion.* FROM organizacion_has_campo_accion INNER JOIN organizacion ON organizacion.id = organizacion_has_campo_accion.organizacion_id INNER JOIN campo_accion ON campo_accion.id = organizacion_has_campo_accion.campo_accion_id WHERE organizacion_has_campo_accion.campo_accion_id = 5
        $columns = 'campo_accion.nombre AS campo_accion_nombre, organizacion.*, 
                    departamento.nombre AS departamento_nombre';        
        $join = 'INNER JOIN organizacion ON organizacion.id = organizacion_has_campo_accion.organizacion_id 
                 INNER JOIN campo_accion ON campo_accion.id = organizacion_has_campo_accion.campo_accion_id
                 INNER JOIN departamento ON departamento.id = organizacion.departamento_id';
        $conditions = 'organizacion_has_campo_accion.campo_accion_id ='.$campo_accion_id; 

         $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'organizacion.nombre ASC, organizacion.nombre ASC',
                'DESC' => 'organizacion.nombre DESC, organizacion.nombre DESC'
            ), 
            'titulado' => array(
                'ASC' => 'organizacion.titulado ASC, organizacion.titulado ASC',
                'DESC' => 'organizacion.titulado DESC, organizacion.titulado DESC'
            ),
            'departamento' => array(
                'ASC' => 'departamento ASC, departamento',
                'DESC' => 'departamento DESC, departamento DESC'
            )
            ));
        $group = 'organizacion.nombre';
        
       if($page) {            
            return $this->paginated("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order", "page: $page");
        }
    }
    
    
    public function guardar($dataCampoAccion, $organizacion_id)
    {        
        if ($this->delete_all("organizacion_id = $organizacion_id")) {
            foreach ($dataCampoAccion as $value) {
                $obj_OrganizacionHasCampoAccion = new OrganizacionHasCampoAccion();
                $obj_OrganizacionHasCampoAccion->organizacion_id = $organizacion_id;
                $obj_OrganizacionHasCampoAccion->campo_accion_id = $value;
                $obj_OrganizacionHasCampoAccion->save();
            }
        } else {
            throw new KumbiaException('No se pudieron eliminar los campo_accions');
        }
        
    }
    
     public function getOrganizacionHasCampoAccionByOrganizacionId($organizacion_id) 
    {                   
      return $this->find_all_by_sql("SELECT organizacion_has_campo_accion.campo_accion_id FROM organizacion_has_campo_accion WHERE organizacion_id =".$organizacion_id);
    }
    
   
    
}
?>