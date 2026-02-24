<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * victimas y con su respectivo antecedente_violencia
 *
 * @category
 * @package     Models 
 */

class VictimaAntecedenteViolencia extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('victima');
        $this->belongs_to('antecedente_violencia');
    }

    
    
    /**
     * Método para registrar los privilegios a los perfiles
     */
    public static function setVictimaAntecedenteViolencia($method, $data, $victima_id) {   

        $cantidad_antecedente_violencias = count($data);    
        $obj_VictimaAntecedenteViolencia = new VictimaAntecedenteViolencia();
        $boolean_result = FALSE;
        
        for($i = 0 ; $i < $cantidad_antecedente_violencias ; $i++)
        {
             $array = array(
                                "victima_id" => $victima_id,
                                "antecedente_violencia_id" => $data[$i],
                                );
            $obj_VictimaAntecedenteViolencia = new VictimaAntecedenteViolencia($array);
            $boolean_result = $obj_VictimaAntecedenteViolencia->$method();            
        }              
        return ($boolean_result) ? $obj_VictimaAntecedenteViolencia : FALSE;               
       
    }
    
    public function getVictimaAntecedenteViolencia($victima_id) 
    {                   
        $columns = 'victima_antecedente_violencia.*, antecedente_violencia.nombre AS antecedente_violencia';        
        $join = 'INNER JOIN antecedente_violencia ON antecedente_violencia.id = victima_antecedente_violencia.antecedente_violencia_id';
        $conditions = 'victima_antecedente_violencia.id IS NOT NULL AND victima_antecedente_violencia.victima_id='.$victima_id;  
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions");        
    }
    
      
    
    public function guardar($dataAntecedenteViolencia, $victima_id)
    {        
        if ($this->delete_all("victima_id = $victima_id")) {
            if($dataAntecedenteViolencia != NULL){
                foreach ($dataAntecedenteViolencia as $value) {
                    $obj_VictimaAntecedenteViolencia = new VictimaAntecedenteViolencia();
                    $obj_VictimaAntecedenteViolencia->victima_id = $victima_id;
                    $obj_VictimaAntecedenteViolencia->antecedente_violencia_id = $value;
                    $obj_VictimaAntecedenteViolencia->save();
                }            
            }
        } else {
            throw new KumbiaException('No se pudieron eliminar los antecedente_violencias');
        }
        
    }
    
     public function getVictimaAntecedenteViolenciaByVictimaId($victima_id) 
    {                   
      return $this->find_all_by_sql("SELECT victima_antecedente_violencia.antecedente_violencia_id FROM victima_antecedente_violencia WHERE victima_id =".$victima_id);
    }
    
   
    
}
?>