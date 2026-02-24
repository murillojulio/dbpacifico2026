<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * victimas y con su respectivo etnia2
 *
 * @category
 * @package     Models 
 */

class VictimaEtnia2 extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('victima');
        $this->belongs_to('etnia2');
    }

    
    
    /**
     * Método para registrar los privilegios a los perfiles
     */
    public static function setVictimaEtnia2($method, $data, $victima_id) {   

        $cantidad_etnia2s = count($data);    
        $obj_VictimaEtnia2 = new VictimaEtnia2();
        $boolean_result = FALSE;
        
        for($i = 0 ; $i < $cantidad_etnia2s ; $i++)
        {
             $array = array(
                                "victima_id" => $victima_id,
                                "etnia2_id" => $data[$i],
                                );
            $obj_VictimaEtnia2 = new VictimaEtnia2($array);
            $boolean_result = $obj_VictimaEtnia2->$method();            
        }              
        return ($boolean_result) ? $obj_VictimaEtnia2 : FALSE;               
       
    }
    
    public function getVictimaEtnia2($victima_id) 
    {                   
        $columns = 'victima_etnia2.*, etnia2.nombre AS etnia2';        
        $join = 'INNER JOIN etnia2 ON etnia2.id = victima_etnia2.etnia2_id';
        $conditions = 'victima_etnia2.id IS NOT NULL AND victima_etnia2.victima_id='.$victima_id;  
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions");        
    }
    
      
    
    public function guardar($dataVictimaEtnia2, $victima_id)
    {        
        if ($this->delete_all("victima_id = $victima_id")) {
            if($dataVictimaEtnia2 != NULL){
                foreach ($dataVictimaEtnia2 as $value) {
                    $obj_VictimaEtnia2 = new VictimaEtnia2();
                    $obj_VictimaEtnia2->victima_id = $victima_id;
                    $obj_VictimaEtnia2->etnia2_id = $value;
                    $obj_VictimaEtnia2->save();
                }            
            }
        } else {
            throw new KumbiaException('No se pudieron eliminar los etnia2s');
        }
        
    }
    
     public function getVictimaEtnia2ByVictimaId($victima_id) 
    {                   
      return $this->find_all_by_sql("SELECT victima_etnia2.etnia2_id FROM victima_etnia2 WHERE victima_id =".$victima_id);
    }
    
   
    
}
?>