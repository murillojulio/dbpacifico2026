<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * cabildos y con su respectivo autoridad tradicional
 *
 * @category
 * @package     Models 
 */

class CabildoAutoridadTradicional extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('cabildo');
        $this->belongs_to('autoridad_tradicional');
    }

    
    
    /**
     * Método para registrar los privilegios a los perfiles
     */
    public static function setCabildoAutoridadTradicional($method, $data, $cabildo_id) {   

        $cantidad_autoridad_tradicionals = count($data);    
        $obj_CabildoAutoridadTradicional = new CabildoAutoridadTradicional();
        $boolean_result = FALSE;
        
        for($i = 0 ; $i < $cantidad_autoridad_tradicionals ; $i++)
        {
             $array = array(
                                "cabildo_id" => $cabildo_id,
                                "autoridad_tradicional_id" => $data[$i],
                                );
            $obj_CabildoAutoridadTradicional = new CabildoAutoridadTradicional($array);
            $boolean_result = $obj_CabildoAutoridadTradicional->$method();            
        }              
        return ($boolean_result) ? $obj_CabildoAutoridadTradicional : FALSE;               
       
    }
    
    public function getCabildoAutoridadTradicional($cabildo_id) 
    {                   
        $columns = 'cabildo_autoridad_tradicional.*, autoridad_tradicional.nombre AS autoridad_tradicional';        
        $join = 'INNER JOIN autoridad_tradicional ON autoridad_tradicional.id = cabildo_autoridad_tradicional.autoridad_tradicional_id';
        $conditions = 'cabildo_autoridad_tradicional.id IS NOT NULL AND cabildo_autoridad_tradicional.cabildo_id='.$cabildo_id;  
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions");        
    }
    
     public function getTerritoriosByMunicipioId($autoridad_tradicional_id , $order='', $page=0) 
    {    
         //SELECT autoridad_tradicional.nombre AS autoridad_tradicional_nombre, cabildo.* FROM cabildo_autoridad_tradicional INNER JOIN cabildo ON cabildo.id = cabildo_autoridad_tradicional.cabildo_id INNER JOIN autoridad_tradicional ON autoridad_tradicional.id = cabildo_autoridad_tradicional.autoridad_tradicional_id WHERE cabildo_autoridad_tradicional.autoridad_tradicional_id = 5
        $columns = 'autoridad_tradicional.nombre AS autoridad_tradicional_nombre, cabildo.*, 
                    departamento.nombre AS departamento_nombre';        
        $join = 'INNER JOIN cabildo ON cabildo.id = cabildo_autoridad_tradicional.cabildo_id 
                 INNER JOIN autoridad_tradicional ON autoridad_tradicional.id = cabildo_autoridad_tradicional.autoridad_tradicional_id
                 INNER JOIN departamento ON departamento.id = cabildo.departamento_id';
        $conditions = 'cabildo_autoridad_tradicional.autoridad_tradicional_id ='.$autoridad_tradicional_id; 

         $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'cabildo.nombre ASC, cabildo.nombre ASC',
                'DESC' => 'cabildo.nombre DESC, cabildo.nombre DESC'
            ), 
            'titulado' => array(
                'ASC' => 'cabildo.titulado ASC, cabildo.titulado ASC',
                'DESC' => 'cabildo.titulado DESC, cabildo.titulado DESC'
            ),
            'departamento' => array(
                'ASC' => 'departamento ASC, departamento',
                'DESC' => 'departamento DESC, departamento DESC'
            )
            ));
        $group = 'cabildo.nombre';
        
       if($page) {            
            return $this->paginated("columns: $columns", "join: $join", "conditions: $conditions", "group: $group", "order: $order", "page: $page");
        }
    }
    
    
    public function guardar($dataAutoridadTradicional, $cabildo_id)
    {        
        if ($this->delete_all("cabildo_id = $cabildo_id")) {
                        
            foreach ($dataAutoridadTradicional as $clave => $value) {
                $obj_CabildoAutoridadTradicional = new CabildoAutoridadTradicional();
                $obj_CabildoAutoridadTradicional->cabildo_id = $cabildo_id;
                $obj_CabildoAutoridadTradicional->autoridad_tradicional_id = $clave;
                $obj_CabildoAutoridadTradicional->cantidad = $value;
                $obj_CabildoAutoridadTradicional->save();
            }
        } else {
            throw new KumbiaException('No se pudieron eliminar los autoridad_tradicionals');
        }
        
    }
    
     public function getCabildoAutoridadTradicionalByCabildoId($cabildo_id) 
    {                   
      return $this->find_all_by_sql("SELECT cabildo_autoridad_tradicional.* FROM cabildo_autoridad_tradicional WHERE cabildo_id =".$cabildo_id);
    }
    
   
    
}
?>