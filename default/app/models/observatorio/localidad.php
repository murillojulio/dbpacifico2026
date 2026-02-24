<?php
/**
 *
 * Descripcion: Clase que gestiona las localidades (corregimientos, veredas, inspecciones) observadas
 *
 * @category
 * @package     Models
 */


class Localidad extends ActiveRecord {      
    
    /**
     * Constante para definir una subregion como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir una subregion como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('municipio');
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

    public function getLocalidadByMunicipioIdSelect($municipio_id=NULL, $tipo=NULL)
    {
         if((int)$municipio_id && $tipo != NULL)
        {
            $columns = 'localidad.*';  
            $conditions = "localidad.id IS NOT NULL AND localidad.municipio_id=".$municipio_id
                            ." AND localidad.tipo='".$tipo."' AND localidad.estado=1"; 
            $order = 'localidad.nombre ASC';        
            return $this->find("columns: $columns", "conditions: $conditions", "order: $order");
        }else{
            return array();
        }        
    }
}
?>

