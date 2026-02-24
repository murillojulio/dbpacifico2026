<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * territorios y con su respectivo municipio
 *
 * @category
 * @package     Models 
 */

class TerritorioMunicipio extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    protected $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('territorio');
        $this->belongs_to('municipio');
    }

    
    
    /**
     * Método para registrar los privilegios a los perfiles
     */
    public static function setTerritorioMunicipio($method, $data, $territorio_id) {   

        $cantidad_municipios = count($data);    
        $obj_TerritorioMunicipio = new TerritorioMunicipio();
        $boolean_result = FALSE;
        
        for($i = 0 ; $i < $cantidad_municipios ; $i++)
        {
             $array = array(
                                "territorio_id" => $territorio_id,
                                "municipio_id" => $data[$i],
                                );
            $obj_TerritorioMunicipio = new TerritorioMunicipio($array);
            $boolean_result = $obj_TerritorioMunicipio->$method();            
        }              
        return ($boolean_result) ? $obj_TerritorioMunicipio : FALSE;               
       
    }
    
    public function getTerritorioMunicipio($territorio_id) 
    {                   
        $columns = 'territorio_municipio.*, municipio.nombre AS municipio';        
        $join = 'INNER JOIN municipio ON municipio.id = territorio_municipio.municipio_id';
        $conditions = 'territorio_municipio.id IS NOT NULL AND territorio_municipio.territorio_id='.$territorio_id;  
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions");        
    }
    
     public function getTerritoriosByMunicipioId($municipio_id , $order='', $page=0) 
    {    
        $municipio_id = (int) $municipio_id;
        $sql = "SELECT 
                    municipio.nombre AS municipio_nombre,
                    territorio.*,
                    departamento.nombre AS departamento_nombre
                FROM territorio_municipio
                INNER JOIN territorio 
                    ON territorio.id = territorio_municipio.territorio_id
                INNER JOIN municipio 
                    ON municipio.id = territorio_municipio.municipio_id
                INNER JOIN departamento 
                    ON departamento.id = territorio.departamento_id
                WHERE territorio_municipio.municipio_id = $municipio_id
                ORDER BY territorio.nombre ASC";

        return $this->find_all_by_sql($sql);
    }
    
    
    public function guardar($dataMunicipio, $territorio_id)
    {        
        if ($this->delete_all("territorio_id = $territorio_id")) {
            foreach ($dataMunicipio as $value) {
                $obj_TerritorioMunicipio = new TerritorioMunicipio();
                $obj_TerritorioMunicipio->territorio_id = $territorio_id;
                $obj_TerritorioMunicipio->municipio_id = $value;
                $obj_TerritorioMunicipio->save();
            }
        } else {
            throw new KumbiaException('No se pudieron eliminar los municipios');
        }
        
    }
    
    public function getTerritorioMunicipioByTerritorioId($territorio_id) 
    {                   
      return $this->find_all_by_sql("SELECT territorio_municipio.municipio_id FROM territorio_municipio WHERE territorio_id =".$territorio_id);
    }

    /*
     @param string $medthod: create, update
     * @param array $data: Data para autocargar el modelo
     * @param array $optData: Data adicional para autocargar
     * 
     * return object ActiveRecord
     */
    public function getDepartamentoMunicipioByTerritorioId($territorio_id) 
    {                   
      return $this->find_all_by_sql("SELECT territorio_municipio.*, municipio.nombre AS municipio_nombre, 
          (SELECT departamento.nombre FROM municipio 
          INNER JOIN departamento ON departamento.id=municipio.departamento_id 
          WHERE municipio.id=territorio_municipio.municipio_id) AS departamento_nombre, 
          (SELECT subregion.nombre FROM municipio 
          INNER JOIN subregion ON subregion.id = municipio.subregion_id 
          WHERE municipio.id = territorio_municipio.municipio_id) AS subregion_nombre FROM territorio_municipio 
          INNER JOIN municipio ON municipio.id = territorio_municipio.municipio_id WHERE territorio_id =".$territorio_id);
    }
    
    public function getTerritoriosByMunicipioIdSelect($municipio_id=null) 
    {    
       if((int)$municipio_id)
        {
            $case = "CASE WHEN territorio.tipo = 'comunidad_negra' THEN 'N'";
            $case .= " WHEN territorio.tipo = 'indigena' THEN 'I'";
            $case .= " ELSE 'U' END AS tipo_de_territorio";

            $columns = 'municipio.nombre AS municipio_nombre, territorio.*, 
                        departamento.nombre AS departamento_nombre';        
            $join = 'INNER JOIN territorio ON territorio.id = territorio_municipio.territorio_id 
                    INNER JOIN municipio ON municipio.id = territorio_municipio.municipio_id
                    INNER JOIN departamento ON departamento.id = territorio.departamento_id';
            $conditions = 'territorio_municipio.municipio_id ='.$municipio_id;
            $order ='ORDER BY territorio.nombre ASC';
       
            /*return $this->find("columns: $columns", "join: $join", "conditions: $conditions");*/
            return $this->find_all_by_sql("SELECT territorio.*, municipio.nombre AS municipio_nombre, departamento.nombre AS departamento_nombre, CASE WHEN territorio.tipo = 'comunidad_negra' THEN 'N' WHEN territorio.tipo = 'indigena' THEN 'I' WHEN territorio.tipo ='urbano' THEN 'U' END AS tipo_de_territorio FROM territorio_municipio INNER JOIN territorio ON territorio.id = territorio_municipio.territorio_id INNER JOIN municipio ON municipio.id = territorio_municipio.municipio_id INNER JOIN departamento ON departamento.id = territorio.departamento_id WHERE municipio.id = $municipio_id ORDER BY territorio.nombre ASC");
        }else{
            return array();
        }
        
      
    }
    
}
?>