<?php

/**
 * Modelo SectorProgramaSocial
 * 
 * @category App
 * @package Models
 */
class SectorProgramaSocial extends ActiveRecord {
    
      
    /**
     * Constante para definir un sector programa social como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un sector programa social como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        //$this->has_many('usuario');
        //$this->has_many('recurso_perfil');
    }
    
    
    /**
     * Método para obtener el listado de los sector programa socials observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoSectorProgramaSocial($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'sector_programa_social.nombre ASC, sector_programa_social.nombre ASC',
                'DESC' => 'sector_programa_social.nombre DESC, sector_programa_social.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT sector_programa_social.*, 
                (SELECT COUNT(programa_social_sector_programa_social.id) FROM programa_social_sector_programa_social WHERE programa_social_sector_programa_social.sector_programa_social_id = sector_programa_social.id) AS cant_sector
                FROM sector_programa_social WHERE sector_programa_social.id IS NOT NULL GROUP BY sector_programa_social.id ORDER BY '.$order);
        
        
        
        
    }
    
     /**
     * Método para crear/modificar un objeto de base de datos
     * 
     * @param string $medthod: create, update
     * @param array $data: Data para autocargar el modelo
     * @param array $data_poblacion: Data para autocargar el modelo poblacion
     * @param array $optData: Data adicional para autocargar
     * 
     * return object ActiveRecord
     */
    public static function setSectorProgramaSocial($method, $data, $optData=null) {        
        $obj = new SectorProgramaSocial($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
        $old = new SectorProgramaSocial();
        if($old->find_first($conditions)) 
            {            
            //Si existe y se intenta crear pero si no se encuentra activo lo activa
            if($method=='create' && $old->estado != SectorProgramaSocial::ACTIVO) {
                $obj->id        = $old->id;
                $obj->estado    = SectorProgramaSocial::ACTIVO;
                $method         = 'update';
            } else {
                Flash::info('Ya existe un sector programa social registrado bajo ese nombre.');
                return FALSE;
            }
        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado el sector programa social  $obj->nombre en el sistema");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información del sector programa social $obj->nombre");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
}
?>