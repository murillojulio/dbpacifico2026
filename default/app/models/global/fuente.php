<?php
/**
 *
 * Descripcion: Clase que gestiona las fuentes de la informacion
 *
 * @category
 * @package     Models
 */


class Fuente extends ActiveRecord {
   
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {

        $this->belongs_to('departamento');       
    }
    
    
    public function getFuente()
    {
        $obj = new Fuente(); 
        $obj->find("conditions: tabla_identi=2", "order: fecha desc");
        
        return $obj;
    }    
    
     public function getListadoFuente($tabla, $tabla_id)
    {  
        
        
       
        $order = 'fuente.id ASC';
    $conditions = "fuente.tabla = '$tabla' AND fuente.tabla_identi = $tabla_id";

    $listado = $this->find(
        "conditions: $conditions",
        "order: $order"
    );

    // Si no hay registros, crear uno vacío
    if (empty($listado)) {
        $fuente = new self();
        $fuente->tabla = $tabla;
        $fuente->tabla_identi = $tabla_id;
        $listado = [];
    }

    // ESTE ES EL CAMBIO CLAVE
    return (object) [
        'tabla'    => $tabla,
        'tabla_id' => $tabla_id,
        'fuentes'  => $listado
    ];           
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
    public static function setFuente($method, $data, $tabla, $tabla_identi) {   
        
        $obj = new Fuente();
        $bool_result = FALSE;
        
        if ($method == 'create')
        {
            $dataFuente = $data;
            $cantidad_fuente = $dataFuente['cantidad'];              
            for($i = 1 ; $i <= $cantidad_fuente ; $i++)
            {
                $array = array(
                              "fecha" => date('Y-m-d', strtotime($dataFuente['fecha'.$i])),
                              "nombre" => $dataFuente['nombre'.$i],
                              "tabla" => $tabla,
                              "tabla_identi" => $tabla_identi,
                              );
                $obj = new Fuente($array);
                $bool_result = $obj->$method();                  
            }        
        }
        elseif ($method == 'update') 
            {            
            $dataFuente = $data;
            $cantidad_fuente = $dataFuente['cantidad'];
              
              for($i = 1 ; $i <= $cantidad_fuente ; $i++)
              {
                  $array = array(
                                "id" => $dataFuente['id'.$i],
                                "fecha" => date('Y-m-d', strtotime($dataFuente['fecha'.$i])),
                                "nombre" => $dataFuente['nombre'.$i],
                                "tabla" => $tabla,
                                "tabla_identi" => $tabla_identi,
                                );
                  $obj = new Fuente($array);
                $bool_result = $obj->$method();  
              }
        
    }
        return ($bool_result) ? $obj : FALSE;
    }

    public function deleteFuente($tabla, $tabla_identi){
        $obj = new Fuente();
        $obj->delete_all("tabla = '$tabla' AND tabla_identi = $tabla_identi" );
    }
    
    
}
?>

