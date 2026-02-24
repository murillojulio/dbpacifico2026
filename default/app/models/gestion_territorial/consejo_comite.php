<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * consejos y con su respectivo comite
 *
 * @category
 * @package     Models 
 */

class ConsejoComite extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('consejo');
        $this->belongs_to('comite');
    }

    
    
    /**
     * Método para registrar los privilegios a los perfiles
     */
    public static function setConsejoComite($method, $data, $consejo_id) {   

        $cantidad_comites = count($data);    
        $obj_ConsejoComite = new ConsejoComite();
        $boolean_result = FALSE;
        
        for($i = 0 ; $i < $cantidad_comites ; $i++)
        {
             $array = array(
                                "consejo_id" => $consejo_id,
                                "comite_id" => $data[$i],
                                );
            $obj_ConsejoComite = new ConsejoComite($array);
            $boolean_result = $obj_ConsejoComite->$method();            
        }              
        return ($boolean_result) ? $obj_ConsejoComite : FALSE;               
       
    }
    
    public function getConsejoComite($consejo_id) 
    {                   
        $columns = 'consejo_comite.*, comite.nombre AS comite';        
        $join = 'INNER JOIN comite ON comite.id = consejo_comite.comite_id';
        $conditions = 'consejo_comite.id IS NOT NULL AND consejo_comite.consejo_id='.$consejo_id;  
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions");        
    }
    
     public function getTerritoriosByMunicipioId($comite_id , $order='', $page=0) 
    {    
         //SELECT comite.nombre AS comite_nombre, consejo.* FROM consejo_comite INNER JOIN consejo ON consejo.id = consejo_comite.consejo_id INNER JOIN comite ON comite.id = consejo_comite.comite_id WHERE consejo_comite.comite_id = 5
        $columns = 'comite.nombre AS comite_nombre, consejo.*, 
                    departamento.nombre AS departamento_nombre';        
        $join = 'INNER JOIN consejo ON consejo.id = consejo_comite.consejo_id 
                 INNER JOIN comite ON comite.id = consejo_comite.comite_id
                 INNER JOIN departamento ON departamento.id = consejo.departamento_id';
        $conditions = 'consejo_comite.comite_id ='.$comite_id; 

         $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'consejo.nombre ASC, consejo.nombre ASC',
                'DESC' => 'consejo.nombre DESC, consejo.nombre DESC'
            ), 
            'titulado' => array(
                'ASC' => 'consejo.titulado ASC, consejo.titulado ASC',
                'DESC' => 'consejo.titulado DESC, consejo.titulado DESC'
            ),
            'departamento' => array(
                'ASC' => 'departamento ASC, departamento',
                'DESC' => 'departamento DESC, departamento DESC'
            )
            ));
        $group = 'consejo.nombre';
        
       if($page) {            
            return $this->paginated("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order", "page: $page");
        }
    }
    
    
    public function guardar($dataComite, $consejo_id)
    {        
        if ($this->delete_all("consejo_id = $consejo_id")) {
            foreach ($dataComite as $value) {
                $obj_ConsejoComite = new ConsejoComite();
                $obj_ConsejoComite->consejo_id = $consejo_id;
                $obj_ConsejoComite->comite_id = $value;
                $obj_ConsejoComite->save();
            }
        } else {
            throw new KumbiaException('No se pudieron eliminar los comites');
        }
        
    }
    
     public function getConsejoComiteByConsejoId($consejo_id) 
    {                   
      return $this->find_all_by_sql("SELECT consejo_comite.comite_id FROM consejo_comite WHERE consejo_id =".$consejo_id);
    }
    
   
    
}
?>