<?php
header("Access-Control-Allow-Origin:*");
Load::model('observatorio/comunidad');
class ComunidadesController extends RestController
{    
    public function get($territorio_id)
    {
        $obj_comunidades = new Comunidad();
        $array_comunidades = $obj_comunidades->getComunidadesByTerritorioId($territorio_id);        
        $this->data = $array_comunidades;
    }
}
