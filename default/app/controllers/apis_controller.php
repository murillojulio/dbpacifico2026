<?php
header("Access-Control-Allow-Origin:*");
/*
 * API REST DBPACIFICO, choose Tools | Templates
 * and open the template in the editor.
 */
Load::models('observatorio/territorio', 'observatorio/departamento', 'observatorio/municipio', 
'observatorio/poblacion', 'observatorio/titulado_si', 'observatorio/titulado_no', 
'observatorio/territorio_municipio', 'observatorio/comunidad', 'observatorio/conflicto',
'violencia_politica/caso');
class ApisController extends RestController
{
    public function get($departamento_id)
    {
        $territorio = new Territorio();
        $array = array('records' => $territorio->find_all_by('departamento_id', $departamento_id));
        $this->data = $array;
    }

    public function get_departamentos()
    {
        $departamento = new Departamento();
        //$array = array($departamento->find_all_by_sql('SELECT departamento.id, departamento.nombre, departamento.area_total, departamento.capital, departamento.cant_municipio_pacifico  FROM departamento WHERE departamento.id !=0 ORDER BY departamento.nombre ASC'));
        $array = $departamento->find_all_by_sql('SELECT departamento.id, departamento.nombre, departamento.area_total, departamento.capital, departamento.cant_municipio_pacifico  FROM departamento WHERE departamento.id !=0 ORDER BY departamento.nombre ASC');
        $this->data = $array;
    }

    public function get_municipios($departamento_id)
    {
        $municipio = new Municipio();
        $array = $municipio->find_all_by_sql("SELECT municipio.id, municipio.nombre, municipio.area_total,  municipio.area_rural, municipio.cabecera, municipio.area_cabecera, municipio.certificado, municipio.fecha_creacion FROM municipio WHERE municipio.departamento_id=$departamento_id ORDER BY municipio.nombre ASC");
        $this->data = $array;
    }

    public function get_territorios($municipio_id)
    {
        $territorios = new TerritorioMunicipio();
        /* 
	   $array = array('territorios'=>$territorios->find_all_by_sql("SELECT municipio.nombre AS municipio_nombre, territorio.*, departamento.nombre AS departamento_nombre FROM territorio_municipio INNER JOIN territorio ON territorio.id = territorio_municipio.territorio_id INNER JOIN municipio ON municipio.id = territorio_municipio.municipio_id INNER JOIN departamento ON departamento.id = territorio.departamento_id WHERE territorio_municipio.municipio_id = $municipio_id GROUP BY territorio.nombre ORDER BY nombre ASC")); 
	   
        $array = array('territorios' => $territorios->find_all_by_sql("SELECT territorio.id, territorio.nombre, territorio.tipo FROM territorio_municipio INNER JOIN territorio ON territorio.id = territorio_municipio.territorio_id INNER JOIN municipio ON municipio.id = territorio_municipio.municipio_id WHERE territorio_municipio.municipio_id = $municipio_id ORDER BY nombre ASC"));
        */
        $array = $territorios->find_all_by_sql("SELECT territorio.id, territorio.nombre, territorio.tipo FROM territorio_municipio INNER JOIN territorio ON territorio.id = territorio_municipio.territorio_id INNER JOIN municipio ON municipio.id = territorio_municipio.municipio_id WHERE territorio_municipio.municipio_id = $municipio_id ORDER BY nombre ASC");
        $this->data = $array;
    }

    public function get_comunidades($territorio_id)
    {
        $obj_comunidades = new Comunidad();
        $array_comunidades = $obj_comunidades->getComunidadesByTerritorioId($territorio_id);
        
        $this->data = $array_comunidades;
    }


    public function get_territorio($id)
    {
        $obj_territorio = new Territorio();
        $obj_territorio->getTerritorioById($id);
        $array_territorio = $obj_territorio;

        $obj_territorio_municipio = new TerritorioMunicipio();
        $array_ubicaciones = $obj_territorio_municipio->getDepartamentoMunicipioByTerritorioId($id);
        $array_titulado = array();

        if ($array_territorio->titulado == 'SI') {
            $obj_titulado_si = new TituladoSi();
            $obj_titulado_si->getTituladoSiByTerritorioId($id);
            $array_titulado = $obj_titulado_si;
        }

        if ($obj_territorio->titulado == 'NO') {
            $obj_titulado_no = new TituladoNo();
            $obj_titulado_no->getTituladoNoByTerritorioId($id);
            $array_titulado = $obj_titulado_no;
        }

        $poblacion = new Poblacion();
        $poblacion = Poblacion::getPoblacion('territorio_id', $id);
        $array_poblacion = $poblacion;

        $obj_comunidades = new Comunidad();
        $array_comunidades = $obj_comunidades->getComunidadesByTerritorioId($id);

        $cantidad_comunidad = 0;
        foreach ($this->$array_comunidades as $registros) :
            $cantidad_comunidad++;
        endforeach;
        $this->cantidad_comunidad = $cantidad_comunidad;

        $obj_conflictos = new Conflicto();
        $array_conflictos = $obj_conflictos->getConflictosByTerritorioId($id);

        $array = array('informacion_basica' => array($array_territorio, $array_titulado), 'ubicacion' => $array_ubicaciones, 'poblacion' => $array_poblacion, 'comunidades' => $array_comunidades, 'conflictos' => $array_conflictos);
        $this->data = $array;
    }

    public function get_territorio_informacion($id)
    {
        $obj_territorio = new Territorio();
        $obj_territorio->getTerritorioById($id);
        $array_territorio = $obj_territorio;

        $obj_territorio_municipio = new TerritorioMunicipio();
        $array_ubicaciones = $obj_territorio_municipio->getDepartamentoMunicipioByTerritorioId($id);
        $array_titulado = array();

        if ($array_territorio->titulado == 'SI') {
            $obj_titulado_si = new TituladoSi();
            $obj_titulado_si->getTituladoSiByTerritorioId($id);
            $array_titulado = $obj_titulado_si;
        }

        if ($obj_territorio->titulado == 'NO') {
            $obj_titulado_no = new TituladoNo();
            $obj_titulado_no->getTituladoNoByTerritorioId($id);
            $array_titulado = $obj_titulado_no;
        }



        $array = array('informacion_basica' => array($array_territorio, $array_titulado));
        $this->data = $array;
    }

    public function get_casos()
    {
        $caso = new Caso();  
        $sql_query = 'SELECT caso.id, caso.titulo, caso.fecha_desde, caso.departamento_id, territorio.nombre AS territorio, municipio.nombre AS municipio 
        FROM caso INNER JOIN territorio ON caso.territorio_id = territorio.id INNER JOIN
        municipio ON caso.municipio_id = municipio.id
        WHERE caso.id !=0 ORDER BY caso.titulo ASC';      
        $array = $caso->find_all_by_sql($sql_query);
        $this->data = $array;
    }
}
