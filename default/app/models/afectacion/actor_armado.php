<?php
/**
 *
 * Descripcion: Clase que gestiona las afectaciones
 *
 * @category
 * @package     Models
 */

class ActorArmado extends ActiveRecord {
    
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
        $this->belongs_to('afectacion');     
        $this->belongs_to('presunto_responsable');     
    }
    
     public function guardar($dataActorArmado, $territorio_id)
    {        
        if ($this->delete_all("territorio_id = $territorio_id")) {
            foreach ($dataActorArmado as $value) {
                $obj_ActorArmado = new ActorArmado();
                $obj_ActorArmado->territorio_id = $territorio_id;
                $obj_ActorArmado->presunto_responsable_id = $value;
                $obj_ActorArmado->save();
            }
        } else {
            throw new KumbiaException('No se pudieron eliminar los actores armados');
        }        
    }   

    
     /**
     * Método para obtener el listado de las politicas publicas
     * @param type $estado
     * @param type $order
     * @param type $page
     * @return type
     */
    public function getListadoActorArmado($estado='todos', $order='', $page=0) {      
        
        return $this->find_all_by_sql("
        SELECT 
    aa.*,
    presunto_responsable.nombre AS presunto_responsable,
    GROUP_CONCAT(
        DISTINCT CONCAT_WS(' / ',
            d.nombre,
            m.nombre,
            t.nombre
        )
        SEPARATOR ' | '
    ) AS ubicaciones

FROM actor_armado aa

LEFT JOIN ubicacion u
    ON u.afectacion_id = aa.id

LEFT JOIN departamento d
    ON d.id = u.departamento_id

LEFT JOIN municipio m
    ON m.id = u.municipio_id

LEFT JOIN territorio t
    ON t.id = u.territorio_id
INNER JOIN presunto_responsable ON presunto_responsable.id = aa.presunto_responsable_id

GROUP BY 
    aa.id

ORDER BY 
    presunto_responsable");
       
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


    
    /**
     * Método para crear/modificar un objeto de base de datos
     * 
     * @param string $medthod: create, update
     * @param array $data: Data para autocargar el modelo
     * @param array $optData: Data adicional para autocargar
     * 
     * return object ActiveRecord
     */
    public static function setActorArmado($method, $data, $optData=null) {        
        $obj = new ActorArmado($data); //Se carga los datos con los de las tablas  
        if($obj->fecha_llegada !== ''){$obj->fecha_llegada = date('Y-m-d', strtotime($obj->fecha_llegada)); }  
        if($obj->fecha_salida !== ''){$obj->fecha_salida = date('Y-m-d', strtotime($obj->fecha_salida)); } 
           
        if($optData) { //Se carga información adicional al objeto
            $obj->dump_result_self($optData);
        }             
        
        $boolean_result = $obj->$method(); 
        
         if($boolean_result) {
            if($method == 'create')
            {
                $nombre_actor = $obj->getPresuntoResponsable()->nombre;
                DwAudit::create("Se ha registrado un Actor Armado $nombre_actor en el sistema");
            }
            elseif ($method == 'update') {
                $nombre_actor = $obj->getPresuntoResponsable()->nombre;
                DwAudit::update("Se ha modificado la información del Actor Armado $nombre_actor");
            }
           
        }
           
        return ($boolean_result) ? $obj : FALSE;
    }


}
?>
