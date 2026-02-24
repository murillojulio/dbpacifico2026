<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * organizacions y con su respectivo campo_gestion
 *
 * @category
 * @package     Models 
 */

class OrganizacionHasCampoGestion extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('organizacion');
        $this->belongs_to('campo_gestion');
    }

    
    
    /**
     * Método para registrar los privilegios a los perfiles
     */
    public static function setOrganizacionHasCampoGestion($method, $data, $organizacion_id) {   

        $cantidad_campo_gestions = count($data);    
        $obj_OrganizacionHasCampoGestion = new OrganizacionHasCampoGestion();
        $boolean_result = FALSE;
        
        for($i = 0 ; $i < $cantidad_campo_gestions ; $i++)
        {
             $array = array(
                                "organizacion_id" => $organizacion_id,
                                "campo_gestion_id" => $data[$i],
                                );
            $obj_OrganizacionHasCampoGestion = new OrganizacionHasCampoGestion($array);
            $boolean_result = $obj_OrganizacionHasCampoGestion->$method();            
        }              
        return ($boolean_result) ? $obj_OrganizacionHasCampoGestion : FALSE;               
       
    }
    
    public function getOrganizacionHasCampoGestion($organizacion_id) 
    {                   
        $columns = 'organizacion_has_campo_gestion.*, campo_gestion.nombre AS campo_gestion';        
        $join = 'INNER JOIN campo_gestion ON campo_gestion.id = organizacion_has_campo_gestion.campo_gestion_id';
        $conditions = 'organizacion_has_campo_gestion.id IS NOT NULL AND organizacion_has_campo_gestion.organizacion_id='.$organizacion_id;  
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions");        
    }
    
     public function getTerritoriosByMunicipioId($campo_gestion_id , $order='', $page=0) 
    {    
         //SELECT campo_gestion.nombre AS campo_gestion_nombre, organizacion.* FROM organizacion_has_campo_gestion INNER JOIN organizacion ON organizacion.id = organizacion_has_campo_gestion.organizacion_id INNER JOIN campo_gestion ON campo_gestion.id = organizacion_has_campo_gestion.campo_gestion_id WHERE organizacion_has_campo_gestion.campo_gestion_id = 5
        $columns = 'campo_gestion.nombre AS campo_gestion_nombre, organizacion.*, 
                    departamento.nombre AS departamento_nombre';        
        $join = 'INNER JOIN organizacion ON organizacion.id = organizacion_has_campo_gestion.organizacion_id 
                 INNER JOIN campo_gestion ON campo_gestion.id = organizacion_has_campo_gestion.campo_gestion_id
                 INNER JOIN departamento ON departamento.id = organizacion.departamento_id';
        $conditions = 'organizacion_has_campo_gestion.campo_gestion_id ='.$campo_gestion_id; 

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
    
    
    public function guardar($dataCampoGestion, $organizacion_id)
    {        
        if ($this->delete_all("organizacion_id = $organizacion_id")) {
            foreach ($dataCampoGestion as $value) {
                $obj_OrganizacionHasCampoGestion = new OrganizacionHasCampoGestion();
                $obj_OrganizacionHasCampoGestion->organizacion_id = $organizacion_id;
                $obj_OrganizacionHasCampoGestion->campo_gestion_id = $value;
                $obj_OrganizacionHasCampoGestion->save();
            }
        } else {
            throw new KumbiaException('No se pudieron eliminar los campo_gestions');
        }
        
    }
    
     public function getOrganizacionHasCampoGestionByOrganizacionId($organizacion_id) 
    {                   
      return $this->find_all_by_sql("SELECT organizacion_has_campo_gestion.campo_gestion_id FROM organizacion_has_campo_gestion WHERE organizacion_id =".$organizacion_id);
    }
    
   
    
}
?>