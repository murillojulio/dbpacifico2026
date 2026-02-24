<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * territorios y con su respectivo municipio
 *
 * @category
 * @package     Models 
 */

class CooperacionMunicipio extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('cooperacion');
        $this->belongs_to('municipio');
    }
    public function guardar($dataMunicipio, $cooperacion_id)
    {        
        if ($this->delete_all("cooperacion_id = $cooperacion_id")) {
            foreach ($dataMunicipio as $value) {
                $obj_CooperacionMunicipio = new CooperacionMunicipio();
                $obj_CooperacionMunicipio->cooperacion_id = $cooperacion_id;
                $obj_CooperacionMunicipio->municipio_id = $value;
                $obj_CooperacionMunicipio->save();
            }
        } else {
            throw new KumbiaException('No se pudieron eliminar los municipios');
        }        
    }   
}
?>