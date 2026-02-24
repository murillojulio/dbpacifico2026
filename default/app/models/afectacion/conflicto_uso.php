<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * territorios y con su respectivo municipio
 *
 * @category
 * @package     Models 
 */

class ConflictoUso extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('area_natural_protegida');        
        $this->belongs_to('territorio');
    }
//    public function guardar($dataMunicipio, $cooperacion_id)
//    {        
//        if ($this->delete_all("cooperacion_id = $cooperacion_id")) {
//            foreach ($dataMunicipio as $value) {
//                $obj_ConflictoUso = new ConflictoUso();
//                $obj_ConflictoUso->cooperacion_id = $cooperacion_id;
//                $obj_ConflictoUso->municipio_id = $value;
//                $obj_ConflictoUso->save();
//            }
//        } else {
//            throw new KumbiaException('No se pudieron eliminar los municipios');
//        }        
//    }  
    
    public function getConflicto($area_natural_protegida_id, $tipo_territorio)
    {
        return $this->find_all_by_sql(
        "SELECT conflicto_uso.* FROM conflicto_uso 
            INNER JOIN territorio ON territorio.id = conflicto_uso.territorio_id WHERE conflicto_uso.area_natural_protegida_id=".$area_natural_protegida_id." AND territorio.tipo = '$tipo_territorio'");
    }
    
    public function deleteConflicto($area_natural_protegida_id, $tipo_territorio)
    {
        return $this->sql("DELETE conflicto_uso, territorio FROM conflicto_uso INNER JOIN territorio ON territorio.id = conflicto_uso.territorio_id 
            WHERE conflicto_uso.area_natural_protegida_id=".$area_natural_protegida_id." AND territorio.tipo = '$tipo_territorio'");
    }

        /**
     * Callback que se ejecuta antes de guardar/modificar
     */
    public function before_save() {
       
    }
    
    /**
     * Callback que se ejecuta después de guardar/modificar un perfil
     */
    protected function after_save() {
        
    }

}
?>