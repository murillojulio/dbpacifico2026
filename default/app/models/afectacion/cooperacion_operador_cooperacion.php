<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * territorios y con su respectivo operador_cooperacion
 *
 * @category
 * @package     Models 
 */

class CooperacionOperadorCooperacion extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('cooperacion');
        $this->belongs_to('operador_cooperacion');
    }
    public function guardar($dataOperadorCooperacion, $cooperacion_id)
    {        
        if ($this->delete_all("cooperacion_id = $cooperacion_id")) {
            foreach ($dataOperadorCooperacion as $value) {
                $obj_CooperacionOperadorCooperacion = new CooperacionOperadorCooperacion();
                $obj_CooperacionOperadorCooperacion->cooperacion_id = $cooperacion_id;
                $obj_CooperacionOperadorCooperacion->operador_cooperacion_id = $value;
                $obj_CooperacionOperadorCooperacion->save();
            }
        } else {
            throw new KumbiaException('No se pudieron eliminar los operador_cooperacions');
        }        
    }   
}
?>