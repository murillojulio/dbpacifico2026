<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * megaproyectos  y con su respectivo municipio
 *
 * @category
 * @package     Models 
 */

Load::models('afectacion/afectacion_territorio', 'opcion/impacto');

class AfectacionTerritorioImpacto extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('afectacion_territorio');
        $this->belongs_to('impacto');
    }
    
       public function guardar($afectacion_territorio_id, $data) {
        if ($this->delete_all("afectacion_territorio_id = $afectacion_territorio_id")) {
            foreach ($data as $value) {
                $obj = new AfectacionTerritorioImpacto();
                $obj->afectacion_territorio_id = $afectacion_territorio_id;
                $obj->impacto_id = $value;
                $obj->save();
            }
        } else {
            throw new KumbiaException('No se pudieron eliminar los impactos');
        }
    }

    
    
    /**
     * Método para registrar los privilegios a los perfiles
     */
    public static function setAfectacionTerritorioImpacto($method, $data, $programa_social_id) {   
        
        $cantidad_sector_programa_social = count($data);    
        $obj_AfectacionTerritorioImpacto = new AfectacionTerritorioImpacto();
        $boolean_result = FALSE;
        
        for($i = 0 ; $i < $cantidad_sector_programa_social ; $i++)
        {
             $array = array(
                                "programa_social_id" => $programa_social_id,
                                "sector_programa_social_id" => $data[$i],
                                );
            $obj_AfectacionTerritorioImpacto = new AfectacionTerritorioImpacto($array);
            $boolean_result = $obj_AfectacionTerritorioImpacto->$method();            
        }       
        return ($boolean_result) ? $obj_AfectacionTerritorioImpacto : FALSE;               
       
    }
    
    public function getAfectacionTerritorioImpacto($afectacion_territorio_id) 
    {                   
        return $this->find_all_by_sql("SELECT afectacion_territorio_impacto.impacto_id FROM afectacion_territorio_impacto WHERE afectacion_territorio_id = ".$afectacion_territorio_id);
    }
}
?>