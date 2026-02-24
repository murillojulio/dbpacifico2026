<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * megaproyectos  y con su respectivo municipio
 *
 * @category
 * @package     Models 
 */

Load::models('afectacion/megaproyecto_territorio', 'opcion/impacto');

class MegaproyectoTerritorioImpacto extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('megaproyecto_territorio');
        $this->belongs_to('impacto');
    }
    
       public function guardar($megaproyecto_territorio_id, $data) {
        if ($this->delete_all("megaproyecto_territorio_id = $megaproyecto_territorio_id")) {
            foreach ($data as $value) {
                $obj = new MegaproyectoTerritorioImpacto();
                $obj->megaproyecto_territorio_id = $megaproyecto_territorio_id;
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
    public static function setMegaproyectoTerritorioImpacto($method, $data, $programa_social_id) {   
        
        $cantidad_sector_programa_social = count($data);    
        $obj_MegaproyectoTerritorioImpacto = new MegaproyectoTerritorioImpacto();
        $boolean_result = FALSE;
        
        for($i = 0 ; $i < $cantidad_sector_programa_social ; $i++)
        {
             $array = array(
                                "programa_social_id" => $programa_social_id,
                                "sector_programa_social_id" => $data[$i],
                                );
            $obj_MegaproyectoTerritorioImpacto = new MegaproyectoTerritorioImpacto($array);
            $boolean_result = $obj_MegaproyectoTerritorioImpacto->$method();            
        }       
        return ($boolean_result) ? $obj_MegaproyectoTerritorioImpacto : FALSE;               
       
    }
    
    public function getMegaproyectoTerritorioImpacto($megaproyecto_territorio_id) 
    {                   
        return $this->find_all_by_sql("SELECT megaproyecto_territorio_impacto.impacto_id FROM megaproyecto_territorio_impacto WHERE megaproyecto_territorio_id = ".$megaproyecto_territorio_id);
    }
}
?>