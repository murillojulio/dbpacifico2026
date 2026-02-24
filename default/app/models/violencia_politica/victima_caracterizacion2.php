<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * victimas y con su respectivo caracterizacion2
 *
 * @category
 * @package     Models 
 */

class VictimaCaracterizacion2 extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('victima');
        $this->belongs_to('caracterizacion2');
    }

    
    
    /**
     * Método para registrar los privilegios a los perfiles
     */
    public static function setVictimaCaracterizacion2($method, $data, $victima_id) {   

        $cantidad_caracterizacion2s = count($data);    
        $obj_VictimaCaracterizacion2 = new VictimaCaracterizacion2();
        $boolean_result = FALSE;
        
        for($i = 0 ; $i < $cantidad_caracterizacion2s ; $i++)
        {
             $array = array(
                                "victima_id" => $victima_id,
                                "caracterizacion2_id" => $data[$i],
                                );
            $obj_VictimaCaracterizacion2 = new VictimaCaracterizacion2($array);
            $boolean_result = $obj_VictimaCaracterizacion2->$method();            
        }              
        return ($boolean_result) ? $obj_VictimaCaracterizacion2 : FALSE;               
       
    }
    
    public function getVictimaCaracterizacion2($victima_id) 
    {                   
        $columns = 'victima_caracterizacion2.*, caracterizacion2.nombre AS caracterizacion2';        
        $join = 'INNER JOIN caracterizacion2 ON caracterizacion2.id = victima_caracterizacion2.caracterizacion2_id';
        $conditions = 'victima_caracterizacion2.id IS NOT NULL AND victima_caracterizacion2.victima_id='.$victima_id;  
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions");        
    }
    
      
    
    public function guardar($dataAntecedenteViolencia, $victima_id)
    {        
        if ($this->delete_all("victima_id = $victima_id")) {
            if($dataAntecedenteViolencia != NULL){
                foreach ($dataAntecedenteViolencia as $value) {
                    $obj_VictimaCaracterizacion2 = new VictimaCaracterizacion2();
                    $obj_VictimaCaracterizacion2->victima_id = $victima_id;
                    $obj_VictimaCaracterizacion2->caracterizacion2_id = $value;
                    $obj_VictimaCaracterizacion2->save();
                }            
            }
        } else {
            throw new KumbiaException('No se pudieron eliminar los caracterizacion2s');
        }
        
    }
    
     public function getVictimaCaracterizacion2ByVictimaId($victima_id) 
    {                   
      return $this->find_all_by_sql("SELECT victima_caracterizacion2.caracterizacion2_id FROM victima_caracterizacion2 WHERE victima_id =".$victima_id);
    }
    
   
    
}
?>