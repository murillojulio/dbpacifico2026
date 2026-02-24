<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * territorios y con su respectivo municipio
 *
 * @category
 * @package     Models 
 */

class AfectacionAreaNaturalProtegida extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('tipo_afectacion_area_natural_protegida');
        $this->belongs_to('area_natural_protegida');
    }
public static function setAfectacionAreaNaturalProtegida($method, $data, $optData=null,$nombre_area = '') {        
        $obj = new AfectacionAreaNaturalProtegida($data); //Se carga los datos con los de las tablas        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
       
        $boolean_result = $obj->$method(); 
        
         if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado una Afectación al Área Natural Protegida $nombre_area en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado una Afectación al Área Natural Protegida $nombre_area");
            }
           
        }
           
        return ($boolean_result) ? $obj : FALSE;
    }
}
?>