<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
Load::models('observatorio/territorio');
class ApiController extends RestController
{
    public function get($departamento_id)
    {
        $territorio = new Territorio();
        $array = array('records'=>$territorio->find_all_by('departamento_id', $departamento_id));
        $this->data = $array;
    }
}
?>
