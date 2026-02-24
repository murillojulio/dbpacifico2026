<?php
header("Access-Control-Allow-Origin:*");
Load::model('observatorio/departamento');
class DepartamentosController extends RestController
{    
    public function getAll()
    {
        $departamento = new Departamento();        
        $array = $departamento->find_all_by_sql('SELECT departamento.id, departamento.nombre, departamento.area_total, departamento.capital, departamento.cant_municipio_pacifico  FROM departamento WHERE departamento.id !=0 ORDER BY departamento.nombre ASC');
        $this->data = $array;
    }
}
