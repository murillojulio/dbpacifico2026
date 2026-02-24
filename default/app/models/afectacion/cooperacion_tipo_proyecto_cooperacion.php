<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * territorios y con su respectivo tipo_proyecto_cooperacion
 *
 * @category
 * @package     Models 
 */

class CooperacionTipoProyectoCooperacion extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    //public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('cooperacion');
        $this->belongs_to('tipo_proyecto_cooperacion');
    }
    public function guardar($dataOperadorCooperacion, $cooperacion_id)
    {        
        if ($this->delete_all("cooperacion_id = $cooperacion_id")) {
            foreach ($dataOperadorCooperacion as $value) {
                $obj_CooperacionTipoProyectoCooperacion = new CooperacionTipoProyectoCooperacion();
                $obj_CooperacionTipoProyectoCooperacion->cooperacion_id = $cooperacion_id;
                $obj_CooperacionTipoProyectoCooperacion->tipo_proyecto_cooperacion_id = $value;
                $obj_CooperacionTipoProyectoCooperacion->save();
            }
        } else {
            throw new KumbiaException('No se pudieron eliminar los tipo_proyecto_cooperacions');
        }        
    }    
}
?>