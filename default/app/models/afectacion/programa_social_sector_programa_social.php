<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * megaproyectos  y con su respectivo municipio
 *
 * @category
 * @package     Models 
 */

Load::models('afectacion/programa_social', 'opcion/sector_programa_social');

class ProgramaSocialSectorProgramaSocial extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('programa_social');
        $this->belongs_to('sector_programa_social');
    }
    
       public function guardar($programa_social_id, $data) {
        if ($this->delete_all("programa_social_id=$programa_social_id")) {
            foreach ($data as $value) {
                $obj = new ProgramaSocialSectorProgramaSocial();
                $obj->programa_social_id = $programa_social_id;
                $obj->sector_programa_social_id = $value;
                $obj->save();
            }
        } else {
            throw new KumbiaException('No se pudieron eliminar los sectores de programas sociales');
        }
    }

    
    
    /**
     * Método para registrar los privilegios a los perfiles
     */
    public static function setProgramaSocialSectorProgramaSocial($method, $data, $programa_social_id) {   
        
        $cantidad_sector_programa_social = count($data);    
        $obj_ProgramaSocialSectorProgramaSocial = new ProgramaSocialSectorProgramaSocial();
        $boolean_result = FALSE;
        
        for($i = 0 ; $i < $cantidad_sector_programa_social ; $i++)
        {
             $array = array(
                                "programa_social_id" => $programa_social_id,
                                "sector_programa_social_id" => $data[$i],
                                );
            $obj_ProgramaSocialSectorProgramaSocial = new ProgramaSocialSectorProgramaSocial($array);
            $boolean_result = $obj_ProgramaSocialSectorProgramaSocial->$method();            
        }       
        return ($boolean_result) ? $obj_ProgramaSocialSectorProgramaSocial : FALSE;               
       
    }
    
    public function getProgramaSocialSectorProgramaSocial($programa_social_id) 
    {                   
        $columns = 'programa_social_sector_programa_social.*, sector_programa_social.nombre AS sector_programa_social';        
        $join = 'INNER JOIN sector_programa_social ON sector_programa_social.id = programa_social_sector_programa_social.sector_programa_social_id';
        $conditions = 'programa_social_sector_programa_social.id IS NOT NULL AND programa_social_sector_programa_social.programa_social_id='.$programa_social_id;  
//        return $this->find_all_by_sql("columns: $columns", "join: $join", "conditions: $conditions");        
        return $this->find_all_by_sql("SELECT programa_social_sector_programa_social.sector_programa_social_id FROM programa_social_sector_programa_social WHERE programa_social_id =".$programa_social_id);
    }
}
?>