<?php
/**
 *
 * Descripcion: Clase que gestiona las CultivoIlicitoPresuntoResponsable
 *
 * @category
 * @package     Models
 */

class CultivoIlicitoPresuntoResponsable extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    //public $logger = FALSE;
       
    /**
     * Constante para definir un perfil como activo
     */
    const ACTIVO = 1;
    
    /**
     * Constante para definir un perfil como inactivo
     */
    const INACTIVO = 2;
    
    
    
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('cultivo_ilicito');
        $this->belongs_to('presunto_responsable');
    }
    
   
    /**
     * Método para crear/modificar un objeto de base de datos
     * 
     * @param string $medthod: create, update
     * @param array $data: Data para autocargar el modelo
     * @param array $optData: Data adicional para autocargar
     * 
     * return object ActiveRecord
     */
    public static function setUbicacion($method, $data, $optData=null) {        
        $obj = new CultivoIlicitoPresuntoResponsable($data); //Se carga los datos con los de las tablas        
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
       
        $boolean_result = $obj->$method(); 
           
        return ($boolean_result) ? $obj : FALSE;
    }
    
    public function getUbicaciones($afectacion_id){
        $sqlQuery = 'SELECT ubicacion.*, departamento.nombre AS departamento, '
                . '(SELECT subregion.nombre FROM municipio INNER JOIN subregion '
                . 'ON subregion.id = municipio.subregion_id '
                . 'WHERE municipio.id = ubicacion.municipio_id) AS subregion, '
                . 'municipio.nombre AS municipio, territorio.nombre AS territorio '
                . 'FROM `ubicacion` LEFT JOIN departamento ON departamento.id = ubicacion.departamento_id '
                . 'LEFT JOIN municipio ON municipio.id = ubicacion.municipio_id '
                . 'LEFT JOIN territorio ON territorio.id = ubicacion.territorio_id '
                . "WHERE afectacion_id = $afectacion_id";
        return $this->find_all_by_sql($sqlQuery);
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

    public function getPromotoresByCultivoId($cultivo_id) 
    {                   
        $columns = 'cultivo_ilicito_presunto_responsable.*, presunto_responsable.nombre AS responsable';        
        $join = 'INNER JOIN presunto_responsable ON presunto_responsable.id = cultivo_ilicito_presunto_responsable.presunto_responsable_id';
        $conditions = 'cultivo_ilicito_presunto_responsable.id IS NOT NULL AND cultivo_ilicito_presunto_responsable.cultivo_ilicito_id='.$cultivo_id;  
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions");        
    }
}
?>
