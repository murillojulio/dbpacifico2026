<?php
header("Access-Control-Allow-Origin:*");
Load::model('observatorio/municipio');
class MunicipiosController extends RestController
{    
    public function get($departamento_id)
    {
        $municipio = new Municipio();
        $array = $municipio->find_all_by_sql("SELECT municipio.id, municipio.nombre, municipio.area_total,  municipio.area_rural, municipio.cabecera, municipio.area_cabecera, municipio.certificado, municipio.fecha_creacion FROM municipio WHERE municipio.departamento_id=$departamento_id ORDER BY municipio.nombre ASC");
        $this->data = $array;
    }
}
