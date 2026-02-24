<?php
header("Access-Control-Allow-Origin:*");
Load::model('observatorio/territorio_municipio');
class TerritoriosController extends RestController
{    
    public function get($municipio_id)
    {
        $territorios = new TerritorioMunicipio();
        $array = $territorios->find_all_by_sql("SELECT territorio.id, territorio.nombre, territorio.tipo FROM territorio_municipio INNER JOIN territorio ON territorio.id = territorio_municipio.territorio_id INNER JOIN municipio ON municipio.id = territorio_municipio.municipio_id WHERE territorio_municipio.municipio_id = $municipio_id ORDER BY nombre ASC");
        $this->data = $array;
    }	
	
	public function get_departamento($departamento_id)
    {
        $territorios = new TerritorioMunicipio();
        $array = $territorios->find_all_by_sql("SELECT territorio.id, territorio.nombre, territorio.tipo FROM territorio WHERE territorio.departamento_id = $departamento_id ORDER BY territorio.nombre ASC");
        $this->data = $array;
    }
}
