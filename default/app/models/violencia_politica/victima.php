<?php

/**
 * Modelo Victima
 * 
 * @category App
 * @package Models
 */
class Victima extends ActiveRecord {
    
      
    /**
     * Constante para definir un Victima como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un Victima como inactivo
     */
    const INACTIVO = 2;
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
       $this->has_many('victima_antecedente_violencia');
       $this->has_many('victima_hechovictimizante_presunto_responsable');
       $this->has_many('victima_caracterizacion2');
       $this->has_many('victima_etnia2');
       $this->belongs_to('etnia');
       $this->belongs_to('caracterizacion');
       $this->belongs_to('caso');
       $this->belongs_to('genero');
    }
        
    /**
     * Método para obtener el listado de los tipo antecedente de violencia observados
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoVictima($estado='todos', $order='', $page=0) {                   
       

        $order = $this->get_order($order, 'nombre', array(            
            'nombre' => array(
                'ASC' => 'victima.nombre ASC, victima.nombre ASC',
                'DESC' => 'victima.nombre DESC, victima.nombre DESC'
            )));
        
       
        return $this->paginated_by_sql('SELECT victima.*, 
                (SELECT COUNT(victima.id) FROM victima WHERE victima.id = victima.victima_id) AS cant_sector
                FROM victima WHERE victima.id IS NOT NULL GROUP BY victima.id ORDER BY '.$order);
        
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
    public static function setVictima($method, $data, $optData=null) {        
        $obj = new Victima($data); //Se carga los datos con los de las tablas          
        $boolean_result = TRUE;
        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        //Verifico que no exista otro perfil, y si se encuentra inactivo lo active
//        $conditions = empty($obj->id) ? "nombre = '$obj->nombre'" : "nombre = '$obj->nombre' AND id != '$obj->id'";
//        $old = new Victima();
//        if($old->find_first($conditions)) 
//            {            
//            //Si existe y se intenta crear pero si no se encuentra activo lo activa
//            if($method=='create' && $old->estado != Victima::ACTIVO) {
//                $obj->id        = $old->id;
//                $obj->estado    = Victima::ACTIVO;
//                $method         = 'update';
//            } else {
//                Flash::info('Ya existe una Victima registrado bajo ese nombre.');
//                return FALSE;
//            }
//        }
        
        $boolean_result = $obj->$method();   
        if($boolean_result) {
            $titulo_caso = $obj->getCaso()->titulo;
            if($method == 'create')
            {
                DwAudit::create("Se ha registrado la Victima  $obj->nombre, pertenece al caso de violencia política $titulo_caso.");
            }
            elseif ($method == 'update') {
                DwAudit::update("Se ha modificado la información de la Victima $obj->nombre, pertenece al caso de violencia política $titulo_caso.");
            }
            //($method == 'create') ? DwAudit::create("Se ha registrado el municipio  $obj->nombre en el sistema") : DwAudit::edit("Se ha modificado la información del municipio $obj->nombre");
        }
        
        return ($boolean_result) ? $obj : FALSE;
    }
    
    
    /**
     * Método para obtener el listado de las Victimas que pertenecen a un caso
     * @param type $caso_id
     */
    public function getVictimasByCasoId($caso_id) {                   
        $sqlQuery = "SELECT victima.* FROM victima WHERE victima.caso_id = $caso_id";        
        return $this->find_all_by_sql($sqlQuery);        
    }
    
}
?>
