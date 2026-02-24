<?php

/**
 * Descripcion: Controlador que se encarga de la gestión de las politicas publicas
 *
 * @category    
 * @package     Controllers  
 */
Load::models(
    'afectacion/cultivo_ilicito',
    'global/fuente',
    'afectacion/ubicacion',
    'afectacion/afectacion',
    'observatorio/departamento',
    'observatorio/municipio',
    'opcion/impacto',
    'observatorio/territorio',
    'opcion/tipo_cultivo',
    'util/currency',
    'afectacion/afectacion_territorio',
    'afectacion/afectacion_territorio_impacto',
    'observatorio/subregion',
    'afectacion/afectacion_dano_territorio',
    'opcion/dano',
    'opcion/tipo_dano',
    'afectacion/cultivo_ilicito_presunto_responsable',
    'opcion/presunto_responsable'
);

class CultivoIlicitoController extends BackendController
{

    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter()
    {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Cultivo Ilícito';
        $this->page_title = 'Listado';
    }

    /**
     * Método principal
     */
    public function index()
    {
        Redirect::toAction('listar');
    }

    /**
     * Método para listar
     */
    public function listar($order = 'order.cultivo_ilicito.asc', $page = 'page.1')
    {
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $cultivo_ilicito = new CultivoIlicito();
        $this->cultivo_ilicitos = $cultivo_ilicito->getListadoCultivoIlicito('todos', $order, $page);
        $this->order = $order;

        $this->page_module = 'Cultivos de Uso Ilícito';
        $this->page_title = 'Listado de Cultivos de Uso Ilícito';
    }

    /**
     * Método para buscar
     * 
     * @param type $field Nombre del campo a buscar
     * @param type $value Valor del campo
     * @param type $order Método de ordenamiento
     * @param type $page Número de página
     */
    public function buscar_cultivo_ilicito($field = 'nombre', $value = 'none', $order = 'order.id.asc', $page = 'page.1')
    {
        $page       = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field      = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value      = (Input::hasPost('value')) ? Input::post('value') : $value;
        $cultivo_ilicito     = new CultivoIlicito();
        $cultivo_ilicito    = $cultivo_ilicito->getAjaxCultivoIlicito($field, $value, $order, $page);
        if (empty($cultivo_ilicito->items)) {
            Flash::info('No se han encontrado registros');
        }

        $this->cultivo_ilicitos = $cultivo_ilicito;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de Cultivo Ilícito en el sistema';
        $this->page_module = 'Cultivo Ilícito';
    }

    /**
     * Método para agregar
     */
    public function agregar()
    {

        $this->page_module = 'Cultivo Ilícito';
        $this->page_title = 'Agregar Cultivo Ilícito';

        if (Input::hasPost('cultivo_ilicito')) {

            $afectacion_obj = Afectacion::setAfectacion('create', array('tipo_afectacion_id' => '4'));
            if ($afectacion_obj) {
                $ubicacion_obj = Ubicacion::setUbicacion('create', Input::post('caso'), array('afectacion_id' => $afectacion_obj->id));

                $post_cultivo_ilicito = Input::post('cultivo_ilicito');
                $post_cultivo_ilicito['area'] = Currency::comaApunto($post_cultivo_ilicito['area']);
                $post_cultivo_ilicito['area_erradicacion'] = Currency::comaApunto($post_cultivo_ilicito['area_erradicacion']);

                $cultivo_ilicito_obj = new CultivoIlicito();
                $cultivo_ilicito_obj = CultivoIlicito::setCultivoIlicito('create', $post_cultivo_ilicito, array('afectacion_id' => $afectacion_obj->id, 'estado' => CultivoIlicito::ACTIVO));
                if ($cultivo_ilicito_obj) {
                    $cultivo_ilicito_id = $cultivo_ilicito_obj->id;
                    Fuente::setFuente('create', Input::post('fuente'), 'cultivo_ilicito', $cultivo_ilicito_id);

                    Flash::valid('¡El Cultivo Ilícito se ha registrado correctamente!');
                    $key_upd = Security::setKey($cultivo_ilicito_obj->id, 'upd_cultivo_ilicito');
                    return Redirect::toAction('editar/' . $key_upd . '/');
                }
            }
        }
    }



    /**
     * Método para agregar
     */
    public function agregar_desde_modal()
    {
        $afectacion_obj = Afectacion::setAfectacion('create', array('tipo_afectacion_id' => '4'));
        if ($afectacion_obj) {
            $CultivoIlicito = new CultivoIlicito();
            $CultivoIlicito->afectacion_id = $afectacion_obj->id;
            $CultivoIlicito->tipo_cultivo_id = Input::post('tipo_cultivo_id');
            $CultivoIlicito->area = '0';
            $CultivoIlicito->area_erradicacion = '0';
            if($CultivoIlicito->create()){
                $Fuente = new Fuente();
                $Fuente->fecha = date('Y-m-d', strtotime(Input::post('fuente_fecha')));
                $Fuente->nombre = Input::post('fuente_descripcion');
                $Fuente->tabla ='cultivo_ilicito';
                $Fuente->tabla_identi = $CultivoIlicito->id;    
                $Fuente->create();

                $key_upd = Security::setKey($CultivoIlicito->id, 'upd_cultivo_ilicito');
                $array = array('key_upd'=>$key_upd);
                $this->data = $array;
                View::template(null);
            }
        }       
    }



    /**
     * Método para editar
     */
    public function ver($key, $tab = '2', $sub_tab = '')
    {

        if (!$id = Security::getKey($key, 'show_cultivo_ilicito', 'int')) {
            return Redirect::toAction('listar/');
        }

        //Para saber que pestaña estara activa cuando visualice un cultivo ilicito
        $this->tab_1_active = '';
        $this->tab_2_active = '';
        $this->tab_3_active = '';
        $this->tab_4_active = '';
        $this->tab_5_active = '';

        if ($tab == 1 || $tab == '') {
            $this->tab_1_active = 'active';
        }
        if ($tab == 2) {
            $this->tab_2_active = 'active';
        }
        if ($tab == 3) {
            $this->tab_3_active = 'active';
        }
        if ($tab == 4) {
            $this->tab_4_active = 'active';
        }
        if ($tab == 5) {
            $this->tab_5_active = 'active';
        }

        //Para saber que subpestaña estara activa
        $this->sub_tab = $sub_tab;


        $cultivo_ilicito = new CultivoIlicito();
        if (!$cultivo_ilicito->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del cultivo_ilicito');
            return Redirect::toAction('listar');
        }

        $ubicaciones = $cultivo_ilicito->getAfectacion()->getUbicaciones($cultivo_ilicito->afectacion_id);
        $this->ubicaciones = $ubicaciones;
        $this->ubicacion = $ubicaciones[0];


        $obj_afectacion_territorio = new AfectacionTerritorio();
        $this->territorios_afectados = $obj_afectacion_territorio->getTerritorioAfectadoByAfectacionId($cultivo_ilicito->getAfectacion()->id);

        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('cultivo_ilicito', $cultivo_ilicito->id);
        //var_dump($this->fuentes);        die();  


        $this->cultivo_ilicito = $cultivo_ilicito;
        $this->nombre_tipo_cultivo = $cultivo_ilicito->getTipoCultivo()->nombre;

        $this->page_module = 'Cultivo Ilícito';
        $this->page_title = 'Información Cultivo Ilícito de ' . $this->nombre_tipo_cultivo;
        $this->key = $key;
        $this->url_redir_back = 'afectacion/cultivo_ilicito/listar/';
    }



    /**
     * Método para editar
     */
    public function editar($key, $tab = '1', $sub_tab = '')
    {

        if (!$id = Security::getKey($key, 'upd_cultivo_ilicito', 'int')) {
            return Redirect::toAction('listar/');
        }

        //Para saber que pestaña estara activa cuando visualice un cultivo ilicito
        $this->tab_1_active = '';
        $this->tab_2_active = '';
        $this->tab_3_active = '';
        $this->tab_4_active = '';
        $this->tab_5_active = '';

        if ($tab == 1 || $tab == '') {
            $this->tab_1_active = 'active';
        }
        if ($tab == 2) {
            $this->tab_2_active = 'active';
        }
        if ($tab == 3) {
            $this->tab_3_active = 'active';
        }
        if ($tab == 4) {
            $this->tab_4_active = 'active';
        }
        if ($tab == 5) {
            $this->tab_5_active = 'active';
        }

        //Para saber que subpestaña estara activa
        $this->sub_tab = $sub_tab;


        $cultivo_ilicito = new CultivoIlicito();
        if (!$cultivo_ilicito->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del cultivo_ilicito');
            return Redirect::toAction('listar');
        }

        $ubicaciones = $cultivo_ilicito->getAfectacion()->getUbicaciones($cultivo_ilicito->afectacion_id);
        $this->ubicaciones = $ubicaciones;
        /* $ubicacion = $ubicaciones[0];
        $this->ubicacion = $ubicacion; */

        /*
        $obj_afectacion_territorio = new AfectacionTerritorio();
        $this->territorios_afectados = $obj_afectacion_territorio->getTerritorioAfectadoByAfectacionId($cultivo_ilicito->getAfectacion()->id);        
        */
        $AfectacionDanoTerritorio = new AfectacionDanoTerritorio();
        $this->AfectacionDanoTerritorio = $AfectacionDanoTerritorio->getDanoTerritorioByAfectacionId($cultivo_ilicito->afectacion_id);


        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('cultivo_ilicito', $cultivo_ilicito->id);
        //var_dump($this->fuentes);        die();  


        $this->cultivo_ilicito = $cultivo_ilicito;
        $this->nombre_tipo_cultivo = $cultivo_ilicito->getTipoCultivo()->nombre;
        $CultivoIlicitoPresuntoResponsable = new CultivoIlicitoPresuntoResponsable();
        $this->CultivoIlicitoPresuntoResponsable = $CultivoIlicitoPresuntoResponsable->getPromotoresByCultivoId($cultivo_ilicito->id);


        $this->page_module = 'Cultivo Ilícito';
        $this->page_title = 'Actualizar Cultivo Ilícito de ' . $this->nombre_tipo_cultivo;
        $this->key = $key;
        $this->url_redir_back = 'afectacion/cultivo_ilicito/listar/';

        if (Input::hasPost('cultivo_ilicito')) {
            $post_cultivo_ilicito = Input::post('cultivo_ilicito');
            //            $post_cultivo_ilicito['area'] = Currency::comaApunto($post_cultivo_ilicito['area']);
            //            $post_cultivo_ilicito['area_erradicacion'] = Currency::comaApunto($post_cultivo_ilicito['area_erradicacion']);            

            if (CultivoIlicito::setCultivoIlicito('update', $post_cultivo_ilicito, array('id' => $id))) {
                /*Ubicacion::setUbicacion('update', Input::post('caso'), array('id' => $ubicacion->id, 'afectacion_id' => $cultivo_ilicito->afectacion_id));*/
                Fuente::setFuente('update', Input::post('fuente'), 'cultivo_ilicito', $id);
                Flash::valid('El Cultivo de Uso Ilícito se ha actualizado correctamente!');
                return Redirect::toAction("editar/$key");
            }
        }
    }

    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key)
    {
        if (!$id = Security::getKey($key, $tipo . '_cultivo_ilicito', 'int')) {
            return Redirect::toAction('listar/');
        }
        $cultivo_ilicito = new CultivoIlicito();
        if (!$cultivo_ilicito->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del Cultivo Ilícito');
        } else {
            if ($tipo == 'inactivar' && $cultivo_ilicito->estado == CultivoIlicito::INACTIVO) {
                Flash::info('El Cultivo Ilícito ya se encuentra inactivo');
            } else if ($tipo == 'reactivar' && $cultivo_ilicito->estado == CultivoIlicito::ACTIVO) {
                Flash::info('El Cultivo Ilícito ya se encuentra activo');
            } else {
                $estado = ($tipo == 'inactivar') ? CultivoIlicito::INACTIVO : CultivoIlicito::ACTIVO;
                if (CultivoIlicito::setCultivoIlicito('update', $cultivo_ilicito->to_array(), array('id' => $id, 'estado' => $estado))) {
                    ($estado == CultivoIlicito::ACTIVO) ? Flash::valid('El Cultivo Ilícito se ha reactivado correctamente!') : Flash::valid('El Cultivo Ilícito se ha bloqueado correctamente!');
                }
            }
        }

        return Redirect::toAction('listar/');
    }




    public function agregar_afectacion_territorio($afectacion_id, $cultivo_nombre, $key_back)
    {
        $obj_afectacion_territorio = new AfectacionTerritorio();
        if (Input::hasPost('afectacion_territorio')) {
            $data_impacto_ambiental    = Input::post('impacto_ambiental');
            $data_impacto_cultural     = Input::post('impacto_cultural');
            $data_impacto_economico    = Input::post('impacto_economico');
            $data_impacto_social       = Input::post('impacto_social');
            $data_impacto_organizacion = Input::post('impacto_organizacion');
            $data_impacto_territorial  = Input::post('impacto_territorial');

            if ($data_impacto_ambiental['si_no'] == 'SI') {
                $obj_afectacion_territorio =
                    AfectacionTerritorio::setAfectacionTerritorioByCultivoIlicito(
                        'create',
                        Input::post('afectacion_territorio'),
                        $cultivo_nombre,
                        array(
                            'tipo_impacto_id' => '1',
                            'descripcion' => $data_impacto_ambiental['descripcion'],
                            'estado' =>  AfectacionTerritorio::ACTIVO
                        ),
                        'Impacto Ambiental'
                    );

                $afectacion_territorio_id = $obj_afectacion_territorio->id;

                if ($obj_afectacion_territorio) {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_ambiental'));

                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
            }
            if ($data_impacto_cultural['si_no'] == 'SI') {
                $obj_afectacion_territorio =
                    AfectacionTerritorio::setAfectacionTerritorioByCultivoIlicito(
                        'create',
                        Input::post('afectacion_territorio'),
                        $cultivo_nombre,
                        array(
                            'tipo_impacto_id' => '2',
                            'descripcion' => $data_impacto_cultural['descripcion'],
                            'estado' =>  AfectacionTerritorio::ACTIVO
                        ),
                        'Impacto Cultural'
                    );

                $afectacion_territorio_id = $obj_afectacion_territorio->id;

                if ($obj_afectacion_territorio) {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_cultural'));

                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
            }
            if ($data_impacto_economico['si_no'] == 'SI') {
                $obj_afectacion_territorio =
                    AfectacionTerritorio::setAfectacionTerritorioByCultivoIlicito(
                        'create',
                        Input::post('afectacion_territorio'),
                        $cultivo_nombre,
                        array(
                            'tipo_impacto_id' => '3',
                            'descripcion' => $data_impacto_economico['descripcion'],
                            'estado' =>  AfectacionTerritorio::ACTIVO
                        ),
                        'Impacto Económico'
                    );

                $afectacion_territorio_id = $obj_afectacion_territorio->id;

                if ($obj_afectacion_territorio) {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_economico'));

                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
            }
            if ($data_impacto_social['si_no'] == 'SI') {
                $obj_afectacion_territorio =
                    AfectacionTerritorio::setAfectacionTerritorioByCultivoIlicito(
                        'create',
                        Input::post('afectacion_territorio'),
                        $cultivo_nombre,
                        array(
                            'tipo_impacto_id' => '4',
                            'descripcion' => $data_impacto_social['descripcion'],
                            'estado' =>  AfectacionTerritorio::ACTIVO
                        ),
                        'Impacto Social'
                    );

                $afectacion_territorio_id = $obj_afectacion_territorio->id;

                if ($obj_afectacion_territorio) {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_social'));

                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
            }
            if ($data_impacto_organizacion['si_no'] == 'SI') {
                $obj_afectacion_territorio =
                    AfectacionTerritorio::setAfectacionTerritorioByCultivoIlicito(
                        'create',
                        Input::post('afectacion_territorio'),
                        $cultivo_nombre,
                        array(
                            'tipo_impacto_id' => '5',
                            'descripcion' => $data_impacto_organizacion['descripcion'],
                            'estado' =>  AfectacionTerritorio::ACTIVO
                        ),
                        'Impacto Organizacional'
                    );

                $afectacion_territorio_id = $obj_afectacion_territorio->id;

                if ($obj_afectacion_territorio) {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_organizacion'));

                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
            }
            if ($data_impacto_territorial['si_no'] == 'SI') {
                $obj_afectacion_territorio =
                    AfectacionTerritorio::setAfectacionTerritorioByCultivoIlicito(
                        'create',
                        Input::post('afectacion_territorio'),
                        $cultivo_nombre,
                        array(
                            'tipo_impacto_id' => '6',
                            'descripcion' => $data_impacto_territorial['descripcion'],
                            'estado' =>  AfectacionTerritorio::ACTIVO
                        ),
                        'Impacto Territorial'
                    );

                $afectacion_territorio_id = $obj_afectacion_territorio->id;

                if ($obj_afectacion_territorio) {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_territorial'));

                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
            }
            if ($obj_afectacion_territorio) {
                Flash::valid('La afectación al territorio se ha registrado correctamente!');
            }

            return Redirect::toAction('editar/' . $key_back . '/2/1/');
        }

        $this->afectacion_id = $afectacion_id;
        $this->page_title = 'Agregar Afectación causada por el Cultivo Ilícito: ' . $cultivo_nombre;

        $this->url_redir_back = 'afectacion/cultivo_ilicito/editar/' . $key_back . '/2/1/';
    }



    public function editar_afectacion_territorio($key,  $key_back, $afectacion_id)
    {

        if (!$id = Security::getKey($key, 'upd_afectacion_territorio', 'int')) {
            return Redirect::toAction('listar');
        }

        $this->territorio_id = $id;

        $obj_afectacion_territorio = new AfectacionTerritorio();
        $afectaciones_al_territorio = $obj_afectacion_territorio->getAfectacionTerritorioByAfectacionIdTerritorioId($afectacion_id, $id);

        $this->si_no_ambiental = 'NO';
        $this->si_no_cultural = 'NO';
        $this->si_no_economico = 'NO';
        $this->si_no_social = 'NO';
        $this->si_no_organizacion = 'NO';
        $this->si_no_territorial = 'NO';

        $this->id_ambiental = '';
        $this->id_cultural = '';
        $this->id_economico = '';
        $this->id_social = '';
        $this->id_organizacion = '';
        $this->id_territorial = '';

        $a_afectacion = $afectaciones_al_territorio[0];
        $this->nombre_cultivo = $a_afectacion->getAfectacion()->getCultivoIlicito()->getTipoCultivo()->nombre;



        $obj_afectacion_territorio_impacto = new AfectacionTerritorioImpacto();
        foreach ($afectaciones_al_territorio as $afectaciones) {
            $this->nombre_territorio = $afectaciones->territorio;
            //$this->nombre_cultivo = $afectaciones->getAfectacion()->getCultivoIlicito()->getTipoCultivo()->nombre;
            //$this->nombre_cultivo = $afectaciones->megaproyecto;
            $this->afectacion_id = $afectaciones->afectacion_id;

            if ($afectaciones->tipo_impacto_id == '1') {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_ambiental = $afectaciones->id;
                $this->si_no_ambiental = 'SI';
                $this->descripcion_ambiental = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_ambiental[] = $value->impacto_id;
                }
            }

            if ($afectaciones->tipo_impacto_id == '2') {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_cultural = $afectaciones->id;
                $this->si_no_cultural = 'SI';
                $this->descripcion_cultural = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_cultural[] = $value->impacto_id;
                }
            }
            if ($afectaciones->tipo_impacto_id == '3') {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_economico = $afectaciones->id;
                $this->si_no_economico = 'SI';
                $this->descripcion_economico = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_economico[] = $value->impacto_id;
                }
            }
            if ($afectaciones->tipo_impacto_id == '4') {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_social = $afectaciones->id;
                $this->si_no_social = 'SI';
                $this->descripcion_social = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_social[] = $value->impacto_id;
                }
            }
            if ($afectaciones->tipo_impacto_id == '5') {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_organizacion = $afectaciones->id;
                $this->si_no_organizacion = 'SI';
                $this->descripcion_organizacion = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_organizacion[] = $value->impacto_id;
                }
            }
            if ($afectaciones->tipo_impacto_id == '6') {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_territorial = $afectaciones->id;
                $this->si_no_territorial = 'SI';
                $this->descripcion_territorial = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_territorial[] = $value->impacto_id;
                }
            }
        }

        $this->page_title = 'Actualizar Afectación causada por el Cultivo Ilícito: ' . $this->nombre_cultivo;

        $this->key_back = $key_back;
        $this->key = $key;
        $this->url_redir_back = 'afectacion/cultivo_ilicito/editar/' . $key_back . '/2/1/';

        $obj_afectacion_territorio = new AfectacionTerritorio();
        if (Input::hasPost('afectacion_territorio')) {
            $data_impacto_ambiental    = Input::post('impacto_ambiental');
            $data_impacto_cultural     = Input::post('impacto_cultural');
            $data_impacto_economico    = Input::post('impacto_economico');
            $data_impacto_social       = Input::post('impacto_social');
            $data_impacto_organizacion = Input::post('impacto_organizacion');
            $data_impacto_territorial  = Input::post('impacto_territorial');

            if ($data_impacto_ambiental['si_no'] == 'SI') {
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '1');
                $obj_afectacion_territorio =
                    AfectacionTerritorio::setAfectacionTerritorioByCultivoIlicito(
                        'create',
                        Input::post('afectacion_territorio'),
                        $this->nombre_cultivo,
                        array(
                            'tipo_impacto_id' => '1',
                            'descripcion' => $data_impacto_ambiental['descripcion'],
                            'estado' =>  AfectacionTerritorio::ACTIVO
                        ),
                        'Impacto Ambiental'
                    );

                $afectacion_territorio_id = $obj_afectacion_territorio->id;

                if ($obj_afectacion_territorio) {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_ambiental'));

                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
            } else {
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '1');
            }


            if ($data_impacto_cultural['si_no'] == 'SI') {
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '2');
                $obj_afectacion_territorio =
                    AfectacionTerritorio::setAfectacionTerritorioByCultivoIlicito(
                        'create',
                        Input::post('afectacion_territorio'),
                        $this->nombre_cultivo,
                        array(
                            'tipo_impacto_id' => '2',
                            'descripcion' => $data_impacto_cultural['descripcion'],
                            'estado' =>  AfectacionTerritorio::ACTIVO
                        ),
                        'Impacto Cultural'
                    );

                $afectacion_territorio_id = $obj_afectacion_territorio->id;

                if ($obj_afectacion_territorio) {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_cultural'));

                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
            } else {
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '2');
            }


            if ($data_impacto_economico['si_no'] == 'SI') {
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '3');
                $obj_afectacion_territorio =
                    AfectacionTerritorio::setAfectacionTerritorioByCultivoIlicito(
                        'create',
                        Input::post('afectacion_territorio'),
                        $this->nombre_cultivo,
                        array(
                            'tipo_impacto_id' => '3',
                            'descripcion' => $data_impacto_economico['descripcion'],
                            'estado' =>  AfectacionTerritorio::ACTIVO
                        ),
                        'Impacto Económico'
                    );

                $afectacion_territorio_id = $obj_afectacion_territorio->id;

                if ($obj_afectacion_territorio) {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_economico'));

                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
            } else {
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '3');
            }


            if ($data_impacto_social['si_no'] == 'SI') {
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '4');
                $obj_afectacion_territorio =
                    AfectacionTerritorio::setAfectacionTerritorioByCultivoIlicito(
                        'create',
                        Input::post('afectacion_territorio'),
                        $this->nombre_cultivo,
                        array(
                            'tipo_impacto_id' => '4',
                            'descripcion' => $data_impacto_social['descripcion'],
                            'estado' =>  AfectacionTerritorio::ACTIVO
                        ),
                        'Impacto Social'
                    );

                $afectacion_territorio_id = $obj_afectacion_territorio->id;

                if ($obj_afectacion_territorio) {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_social'));

                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
            } else {
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '4');
            }


            if ($data_impacto_organizacion['si_no'] == 'SI') {
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '5');
                $obj_afectacion_territorio =
                    AfectacionTerritorio::setAfectacionTerritorioByCultivoIlicito(
                        'create',
                        Input::post('afectacion_territorio'),
                        $this->nombre_cultivo,
                        array(
                            'tipo_impacto_id' => '5',
                            'descripcion' => $data_impacto_organizacion['descripcion'],
                            'estado' =>  AfectacionTerritorio::ACTIVO
                        ),
                        'Impacto Organizacional'
                    );

                $afectacion_territorio_id = $obj_afectacion_territorio->id;

                if ($obj_afectacion_territorio) {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_organizacion'));

                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
            } else {
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '5');
            }


            if ($data_impacto_territorial['si_no'] == 'SI') {
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '6');
                $obj_afectacion_territorio =
                    AfectacionTerritorio::setAfectacionTerritorioByCultivoIlicito(
                        'create',
                        Input::post('afectacion_territorio'),
                        $this->nombre_cultivo,
                        array(
                            'tipo_impacto_id' => '6',
                            'descripcion' => $data_impacto_territorial['descripcion'],
                            'estado' =>  AfectacionTerritorio::ACTIVO
                        ),
                        'Impacto Territorial'
                    );

                $afectacion_territorio_id = $obj_afectacion_territorio->id;

                if ($obj_afectacion_territorio) {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_territorial'));

                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
            } else {
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '6');
            }


            Flash::valid('La afectación al territorio se ha actualizado correctamente!');


            return Redirect::toAction('editar/' . $key_back . '/2/1/');
        }
    }




    public function ver_afectacion_territorio($key,  $key_back, $afectacion_id)
    {

        if (!$id = Security::getKey($key, 'show_afectacion_territorio', 'int')) {
            return Redirect::toAction('listar');
        }

        $this->territorio_id = $id;

        $obj_afectacion_territorio = new AfectacionTerritorio();
        $afectaciones_al_territorio = $obj_afectacion_territorio->getAfectacionTerritorioByAfectacionIdTerritorioId($afectacion_id, $id);


        $this->si_no_ambiental = 'NO';
        $this->si_no_cultural = 'NO';
        $this->si_no_economico = 'NO';
        $this->si_no_social = 'NO';
        $this->si_no_organizacion = 'NO';
        $this->si_no_territorial = 'NO';
        $obj_afectacion_territorio_impacto = new AfectacionTerritorioImpacto();
        foreach ($afectaciones_al_territorio as $afectaciones) {
            $this->nombre_territorio = $afectaciones->territorio;
            $this->nombre_cultivo = $afectaciones->megaproyecto;
            //$this->megaproyecto_id = $afectaciones->megaproyecto_id;

            if ($afectaciones->tipo_impacto_id == '1') {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_ambiental = $afectaciones->id;
                $this->si_no_ambiental = 'SI';
                $this->descripcion_ambiental = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_ambiental[] = $value->impacto_id;
                }
            }

            if ($afectaciones->tipo_impacto_id == '2') {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_cultural = $afectaciones->id;
                $this->si_no_cultural = 'SI';
                $this->descripcion_cultural = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_cultural[] = $value->impacto_id;
                }
            }
            if ($afectaciones->tipo_impacto_id == '3') {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_economico = $afectaciones->id;
                $this->si_no_economico = 'SI';
                $this->descripcion_economico = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_economico[] = $value->impacto_id;
                }
            }
            if ($afectaciones->tipo_impacto_id == '4') {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_social = $afectaciones->id;
                $this->si_no_social = 'SI';
                $this->descripcion_social = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_social[] = $value->impacto_id;
                }
            }
            if ($afectaciones->tipo_impacto_id == '5') {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_organizacion = $afectaciones->id;
                $this->si_no_organizacion = 'SI';
                $this->descripcion_organizacion = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_organizacion[] = $value->impacto_id;
                }
            }
            if ($afectaciones->tipo_impacto_id == '6') {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_territorial = $afectaciones->id;
                $this->si_no_territorial = 'SI';
                $this->descripcion_territorial = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_territorial[] = $value->impacto_id;
                }
            }
        }

        $this->page_title = 'Información de Afectación causada por el Cultivo Ilícito: ' . $this->nombre_cultivo;

        $this->key_back = $key_back;
        $this->key = $key;
        $this->url_redir_back = 'afectacion/cultivo_ilicito/ver/' . $key_back . '/2/1/';

        $obj_afectacion_territorio = new AfectacionTerritorio();
    }

    /**
     * Método para eliminar
     */
    public function eliminar($nombre_cultivo_ilicito, $key, $afectacion_id)
    {

        $url_redir_back = Session::get('url_back');
        if (!$id = Security::getKey($key, 'del_cultivo_ilicito', 'int')) {
            return Redirect::to($url_redir_back);
        }

        $afectacion = new Afectacion();

        try {
            if ($afectacion->delete($afectacion_id)) {
                Flash::valid("El cultivo ilicito $nombre_cultivo_ilicito se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO el cultivo ilicito $nombre_cultivo_ilicito.");
            } else {
                Flash::warning('Lo sentimos, pero este cultivo ilicito no se puede eliminar.');
            }
        } catch (KumbiaException $e) {
            Flash::error('Este cultivo ilicito no se puede eliminar porque se encuentra relacionado con otro registro.');
        }

        return Redirect::to($url_redir_back);
    }

    public function agregar_promotor()
    {
        $CultivoIlicitoPresuntoResponsable = new CultivoIlicitoPresuntoResponsable();
        $cultivo_ilicito_id = Input::post('cultivo_ilicito_id');
        $presunto_responsable_id = Input::post('presunto_responsable_id');
        $descripcion = Input::post('descripcion');
        $CultivoIlicitoPresuntoResponsable->cultivo_ilicito_id = $cultivo_ilicito_id;
        $CultivoIlicitoPresuntoResponsable->presunto_responsable_id = $presunto_responsable_id;
        $CultivoIlicitoPresuntoResponsable->descripcion_presunto_responsable = $descripcion;
        $CultivoIlicitoPresuntoResponsable->save();
        $this->CultivoIlicitoPresuntoResponsable = $CultivoIlicitoPresuntoResponsable->getPromotoresByCultivoId($cultivo_ilicito_id);
        View::template(null);
    }

    public function cargar_editar_promotor()
    {
        $CultivoIlicitoPresuntoResponsable = new CultivoIlicitoPresuntoResponsable();
        $cultivo_ilicito_presunto_responsable_id = Input::post('cultivo_ilicito_presunto_responsable_id');
        $this->CultivoIlicitoPresuntoResponsable = $CultivoIlicitoPresuntoResponsable->find_first($cultivo_ilicito_presunto_responsable_id);
        $this->cultivo_ilicito_presunto_responsable_id = $cultivo_ilicito_presunto_responsable_id;
        View::template(null);
    }

    public function guardar_cambios_promotor()
    {
        $CultivoIlicitoPresuntoResponsable = new CultivoIlicitoPresuntoResponsable();
        $cultivo_ilicito_presunto_responsable_id = Input::post('cultivo_ilicito_presunto_responsable_id');
        $presunto_responsable_id = Input::post('presunto_responsable_id');
        $descripcion = Input::post('descripcion');
        $CultivoIlicitoPresuntoResponsable->find_first($cultivo_ilicito_presunto_responsable_id);
        $CultivoIlicitoPresuntoResponsable->presunto_responsable_id = $presunto_responsable_id;
        $CultivoIlicitoPresuntoResponsable->descripcion_presunto_responsable = $descripcion;
        $CultivoIlicitoPresuntoResponsable->update();
        View::template(null);
        View::select('agregar_promotor');
        $this->CultivoIlicitoPresuntoResponsable = $CultivoIlicitoPresuntoResponsable->getPromotoresByCultivoId($CultivoIlicitoPresuntoResponsable->cultivo_ilicito_id);
    }

    public function eliminar_promotor()
    {
        $CultivoIlicitoPresuntoResponsable = new CultivoIlicitoPresuntoResponsable();
        $cultivo_ilicito_presunto_responsable_id = Input::post('cultivo_ilicito_presunto_responsable_id');
        $cultivo_ilicito_id = Input::post('cultivo_ilicito_id');
        $CultivoIlicitoPresuntoResponsable->delete($cultivo_ilicito_presunto_responsable_id);
        View::template(null);
        View::select('agregar_promotor');
        $this->CultivoIlicitoPresuntoResponsable = $CultivoIlicitoPresuntoResponsable->getPromotoresByCultivoId($cultivo_ilicito_id);
    }
}
