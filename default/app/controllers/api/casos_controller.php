<?php
header("Access-Control-Allow-Origin:*");
Load::model('violencia_politica/caso');
class CasosController extends RestController
{    
    public function getAll()
    {
        $caso = new Caso();  
        $sql_query = 'SELECT caso.id, caso.titulo, caso.fecha_desde, caso.departamento_id, territorio.nombre AS territorio, municipio.nombre AS municipio 
        FROM caso INNER JOIN territorio ON caso.territorio_id = territorio.id INNER JOIN
        municipio ON caso.municipio_id = municipio.id
        WHERE caso.estado = 2 AND caso.nivel = 1 ORDER BY caso.titulo ASC';      
        $array = $caso->find_all_by_sql($sql_query);
        $this->data = $array;
    }
}
