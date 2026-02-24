<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * iniciativas empresarial  y con su respectivo impacto
 *
 * @category
 * @package     Models 
 */

Load::models('gestion_territorial/descripcion_afectacion', 'opcion/impacto');

class DescripcionAfectacionImpacto extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('descripcion_afectacion');
        $this->belongs_to('impacto');
    }
    
       public function guardar($descripcion_afectacion_id, $data) {
        if ($this->delete_all("descripcion_afectacion_id = $descripcion_afectacion_id")) {
            foreach ($data as $value) {
                $obj = new DescripcionAfectacionImpacto();
                $obj->descripcion_afectacion_id = $descripcion_afectacion_id;
                $obj->impacto_id = $value;
                $obj->save();
            }
        } else {
            throw new KumbiaException('No se pudieron eliminar los impactos');
        }
    }

    
    
    public function getDescripcionAfectacionImpacto($descripcion_afectacion_id) 
    {                   
        return $this->find_all_by_sql("SELECT descripcion_afectacion_impacto.impacto_id FROM descripcion_afectacion_impacto WHERE descripcion_afectacion_id = ".$descripcion_afectacion_id);
    }
}
?>