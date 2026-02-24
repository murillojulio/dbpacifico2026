<?php

/**
 * Descripcion: Controlador que se encarga de la gestión de los territorios del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models(
    'global/fuente',
    'observatorio/comunidad',
    'observatorio/territorio',
    'observatorio/territorio_municipio',
    'observatorio/municipio',
    'gestion_territorial/organizacion',
    'gestion_territorial/organizacion_has_campo_gestion',
    'gestion_territorial/organizacion_has_campo_accion',
    'gestion_territorial/iniciativa_empresarial',
    'gestion_territorial/descripcion_afectacion_impacto',
    'gestion_territorial/accion_exigibilidad_derecho',
    'opcion/tipo_accion_exigibilidad_derecho',
    'opcion/tipo_iniciativa_empresarial',
    'opcion/tipo_actividad_productiva',
    'gestion_territorial/descripcion_afectacion',
    'afectacion/afectacion',
    'afectacion/afectacion_dano_territorio',
    'opcion/dano',
    'opcion/tipo_dano'
);
class GestionTcurController extends BackendController
{

    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter()
    {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Gestión Territorial';
    }

    /**
     * Método principal
     */
    public function index()
    {
        Redirect::toAction('listar');
    }

    /**
     * Método para listar los territorios colectivos que pertenecen a un municipio
     */
    public function listar_territorio($municipio_id, $municipio_nombre, $order = 'order.territorio.asc', $page = 'page.1')
    {
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $territorios = new TerritorioMunicipio();
        $this->territorios = $territorios->getTerritoriosByMunicipioId($municipio_id, $order, $page);
        $this->order = $order;
        $this->page = $page;
        $this->municipio_id = $municipio_id;
        $this->municipio_nombre = $municipio_nombre;
        $this->page_title = 'Territorios que pertenecen al municipio: ' . $municipio_nombre;
        $this->page_module = 'Territorios Urbanos';
    }

    /**
     * Método para listar los territorios colectivos de comunidades negras
     */
    public function listar_territorio_ur($order = 'order.territorio.asc', $page = 'page.1')
    {
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $territorios = new Territorio();
        $this->territorios = $territorios->getListadoTerritorio('urbano', $order, $page);
        $this->order = $order;
        $this->page = $page;
        //$this->page_title = 'Listado de territorios monitoriados';
        $this->page_title = Territorio::TERRITORIO_URBANO;
    }

    /**
     * Método para buscar
     * 
     * @param type $field Nombre del campo a buscar
     * @param type $value Valor del campo
     * @param type $order Método de ordenamiento
     * @param type $page Número de página
     */
    public function buscar_territorio_ur($field = 'nombre', $value = 'none', $order = 'order.id.asc', $page = 'page.1')
    {
        $page       = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field      = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value      = (Input::hasPost('value')) ? Input::post('value') : $value;
        //$tipo       = (Input::hasPost('tipo')) ? Input::post('tipo') : $tipo;

        $territorio     = new Territorio();
        $territorios    = $territorio->getAjaxTerritorio($field, $value, $order, $page, $tipo = 'urbano');
        if (empty($territorios->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->territorios  = $territorios;
        $this->order = $order;
        $this->page = $page;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de: ' . Territorio::TERRITORIO_URBANO;
        //$this->page_module = Territorio::TERRITORIO_URBANO;
    }



    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key)
    {
        if (!$id = Security::getKey($key, $tipo . '_territorio', 'int')) {
            return Redirect::toAction('listar');
        }

        $territorio = new Territorio();
        if (!$territorio->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del territorio');
        } else {
            if ($tipo == 'inactivar' && $territorio->estado == Territorio::INACTIVO) {
                Flash::info('El territorio ya se encuentra inactivo');
            } else if ($tipo == 'reactivar' && $territorio->estado == Territorio::ACTIVO) {
                Flash::info('El territorio ya se encuentra activo');
            } else {
                $estado = ($tipo == 'inactivar') ? Territorio::INACTIVO : Territorio::ACTIVO;
                if (Territorio::setTerritorio('update', $territorio->to_array(), array('id' => $id, 'estado' => $estado))) {
                    ($estado == Territorio::ACTIVO) ? Flash::valid('El territorio se ha reactivado correctamente!') : Flash::valid('El territorio se ha bloqueado correctamente!');
                }
            }
        }

        return Redirect::toAction('listar');
    }

    /**
     * Método para ver
     */
    public function ver($key, $tab, $order, $page)
    {

        if (!$id = Security::getKey($key, 'show_territorio', 'int')) {
            return Redirect::toAction('listar');
        }

        //Para saber que pestaña estara activa cuando visualice un territorio
        $this->tab_1_active = '';
        $this->tab_2_active = '';
        $this->tab_3_active = '';
        $this->tab_4_active = '';
        $this->tab_5_active = '';

        if ($tab == 1) {
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

        //Para saber que subpestaña estara activa cuando visualice un vinculacion de poblacion
        $this->sub_tab = '1';


        $obj_territorio = new Territorio();
        if (!$obj_territorio->getTerritorioById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del territorio');
            return Redirect::toAction('listar_ur');
        }
        $this->obj_territorio = $obj_territorio;

        $obj_organizacions = new Organizacion();
        $this->organizacions = $obj_organizacions->getOrganizacionsByTerritorioId($id);

        $obj_accion_exigibilidad_derechos = new AccionExigibilidadDerecho();
        $this->accion_exigibilidad_derechos = $obj_accion_exigibilidad_derechos->getAccionExigibilidadDerechosByTerritorioId($id);

        $obj_iniciativa_empresarials = new IniciativaEmpresarial();
        $this->iniciativa_empresarials = $obj_iniciativa_empresarials->getIniciativaEmpresarialsByTerritorioId($id);

        $this->url_redir_back = 'gestion_territorial/gestion_tcur/listar_territorio_ur/' . $order . '/' . $page . '/';
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('territorio', $obj_territorio->id);

        $this->controller = 'gestion_tcur';
        $this->page_module = 'Gestión Territorial';
        $this->page_title = 'Información del Territorio: ' . $obj_territorio->nombre;
        $this->tab = $tab;
        $this->key = $key;
        $this->order = $order;
        $this->page = $page;
    }

    /**
     * Método para editar
     */
    public function editar($key, $tab, $order, $page)
    {

        if (!$id = Security::getKey($key, 'upd_territorio', 'int')) {
            return Redirect::toAction('listar_ur');
        }

        //Para saber que pestaña estara activa cuando visualice un territorio
        $this->tab_1_active = '';
        $this->tab_2_active = '';
        $this->tab_3_active = '';
        $this->tab_4_active = '';
        $this->tab_5_active = '';

        if ($tab == 1) {
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

        //Para saber que subpestaña estara activa cuando visualice un vinculacion de poblacion
        $this->sub_tab = '1'; //$sub_tab;


        $obj_territorio = new Territorio();
        if (!$obj_territorio->getTerritorioById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del territorio');
            return Redirect::toAction('listar_ur');
        }
        $this->obj_territorio = $obj_territorio;

        $obj_organizacions = new Organizacion();
        $this->organizacions = $obj_organizacions->getOrganizacionsByTerritorioId($id);

        $obj_accion_exigibilidad_derechos = new AccionExigibilidadDerecho();
        $this->accion_exigibilidad_derechos = $obj_accion_exigibilidad_derechos->getAccionExigibilidadDerechosByTerritorioId($id);

        $obj_iniciativa_empresarials = new IniciativaEmpresarial();
        $this->iniciativa_empresarials = $obj_iniciativa_empresarials->getIniciativaEmpresarialsByTerritorioId($id);

        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('territorio', $obj_territorio->id);

        $nombre_municipio = '';
        foreach($obj_territorio->getTerritorioMunicipio() as $ter):
            $nombre_municipio = $ter->getMunicipio()->nombre;
        endforeach;


        $this->controller = 'gestion_tcur'; //Para partial
        $this->url_redir_back = 'gestion_territorial/gestion_tcur/listar_territorio_ur/' . $order . '/' . $page . '/';
        $this->page_module = 'Gestión Territorial';
        $this->page_title = 'Actualizar Territorio: ' . $obj_territorio->nombre. ' de '.$nombre_municipio;
        $this->tab = $tab;
        $this->key = $key;
        $this->order = $order;
        $this->page = $page;
    }

    public function agregar_organizacion($territorio_id, $territorio_nombre, $key_back, $order, $page)
    {
        $obj_organizacion = new Organizacion();
        if (Input::hasPost('organizacion')) {
            $obj_organizacion = Organizacion::setOrganizacion('create', Input::post('organizacion'), $territorio_nombre, array('estado' =>  Organizacion::ACTIVO));
            $organizacion_id = $obj_organizacion->id;

            $organizacion_has_campo_gestion = new OrganizacionHasCampoGestion();
            $organizacion_has_campo_gestion->guardar(Input::post('campo_gestion'), $organizacion_id);

            $organizacion_has_campo_accion = new OrganizacionHasCampoAccion();
            $organizacion_has_campo_accion->guardar(Input::post('campo_accion'), $organizacion_id);

            Fuente::setFuente('create', Input::post('fuente'), 'organizacion', $organizacion_id);

            Flash::valid('La organizacion se ha registrado correctamente!');
            return Redirect::toAction('editar/' . $key_back . '/1/' . $order . '/' . $page . '/');
        }

        $this->territorio_id = $territorio_id;
        $this->page_title = 'Agregar Organización al territorio: ' . $territorio_nombre;
        $this->url_redir_back = 'gestion_territorial/gestion_tcur/editar/' . $key_back . '/1/' . $order . '/' . $page . '/';
        $this->page_module = 'Gestion Territorial';
    }

    public function ver_organizacion($key, $key_back, $order, $page)
    {

        if (!$id = Security::getKey($key, 'show_organizacion', 'int')) {
            return Redirect::toAction('listar');
        }

        $obj_organizacion = new Organizacion();
        if (!$obj_organizacion->getOrganizacionById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del organizacion');
            return Redirect::toAction('ver/' . $key_back . '/3/');
        }

        $this->organizacion = $obj_organizacion;

        //        $organizacion_has_campo_gestion = new OrganizacionHasCampoGestion();        
        //        foreach ($organizacion_has_campo_gestion->getOrganizacionHasCampoGestionByOrganizacionId($id) as $value) {
        //                        $this->comite[] = $value->comite_id;
        //        }

        foreach ($obj_organizacion->getOrganizacionHasCampoGestion() as $value) {
            $this->campo_gestion[] = $value->campo_gestion_id;
        }

        foreach ($obj_organizacion->getOrganizacionHasCampoAccion() as $value) {
            $this->campo_accion[] = $value->campo_accion_id;
        }

        $this->page_title = 'Información Organización del territorio: ' . $obj_organizacion->territorio;
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('organizacion', $obj_organizacion->id);

        $this->page_module = 'Gestión Territorial';

        $this->url_redir_back = 'gestion_territorial/gestion_tcur/ver/' . $key_back . '/1/' . $order . '/' . $page . '/';
        $this->key_back = $key_back;
        $this->key = $key;
    }

    public function editar_organizacion($key, $key_back, $order, $page)
    {

        if (!$id = Security::getKey($key, 'upd_organizacion', 'int')) {
            return Redirect::toAction('listar');
        }

        $obj_organizacion = new Organizacion();
        if (!$obj_organizacion->getOrganizacionById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del organizacion');
            return Redirect::toAction('ver/' . $key_back . '/3/');
        }

        if (Input::hasPost('organizacion')) {
            $obj_organizacion = Organizacion::setOrganizacion('update', Input::post('organizacion'), $obj_organizacion->territorio, array('estado' => Organizacion::ACTIVO));

            if ($obj_organizacion) {
                $organizacion_has_campo_gestion = new OrganizacionHasCampoGestion();
                $organizacion_has_campo_gestion->guardar(Input::post('campo_gestion'), $obj_organizacion->id);
                $organizacion_has_campo_accion = new OrganizacionHasCampoAccion();
                $organizacion_has_campo_accion->guardar(Input::post('campo_accion'), $obj_organizacion->id);

                Fuente::setFuente('update', Input::post('fuente'), 'organizacion', $id);
                Flash::valid('La Organizacion se ha actualizado correctamente!');
            }
            return Redirect::toAction('editar/' . $key_back . '/1/' . $order . '/' . $page . '/');
        }

        $this->organizacion = $obj_organizacion;

        $organizacion_has_campo_gestion = new OrganizacionHasCampoGestion();
        foreach ($organizacion_has_campo_gestion->getOrganizacionHasCampoGestionByOrganizacionId($id) as $value) {
            $this->campo_gestion[] = $value->campo_gestion_id;
        }

        $organizacion_has_campo_accion = new OrganizacionHasCampoAccion();
        foreach ($organizacion_has_campo_accion->getOrganizacionHasCampoAccionByOrganizacionId($id) as $value) {
            $this->campo_accion[] = $value->campo_accion_id;
        }

        $this->page_title = 'Actualizar Organización del territorio: ' . $obj_organizacion->territorio;

        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('organizacion', $id);

        $this->tipo_organizacion_id = $obj_organizacion->tipo_organizacion_id;
        $this->page_module = 'Gestión Territorial';
        $this->url_redir_back = 'gestion_territorial/gestion_tcur/editar/' . $key_back . '/1/' . $order . '/' . $page . '/';
        $this->key_back = $key_back;
        $this->key = $key;
    }

    /**
     * Método para eliminar
     */
    public function eliminar_organizacion($nombre_organizacion, $nombre_territorio, $key, $key_back, $order, $page)
    {

        if (!$id = Security::getKey($key, 'del_organizacion', 'int')) {
            return Redirect::to($url_redir_back);
        }

        $organizacion = new Organizacion();

        try {
            if ($organizacion->delete($id)) {
                Flash::valid("El organizacion $nombre_organizacion se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO el organizacion $nombre_organizacion, pertenecia al territorio $nombre_territorio.");
                
                $fuente = new Fuente();
                $fuente->deleteFuente('organizacion', $id);
            } else {
                Flash::warning('Lo sentimos, pero este organizacion no se puede eliminar.');
            }
        } catch (KumbiaException $e) {
            Flash::error('Este organizacion no se puede eliminar porque se encuentra relacionado con otro registro.');
        }

        return Redirect::toAction('editar/' . $key_back . '/1/' . $order . '/' . $page . '/');
    }


    public function agregar_accion_exigibilidad_derecho($territorio_id, $territorio_nombre, $key_back, $order, $page)
    {
        $obj_aed = new AccionExigibilidadDerecho();
        if (Input::hasPost('accion_exigibilidad_derecho')) {
            $obj_aed = AccionExigibilidadDerecho::setAccionExigibilidadDerecho('create', Input::post('accion_exigibilidad_derecho'), $territorio_nombre, array('estado' =>  AccionExigibilidadDerecho::ACTIVO));

            if ($obj_aed) {
                Fuente::setFuente('create', Input::post('fuente'), 'accion_exigibilidad_derecho', $obj_aed->id);
                Flash::valid('La acción de exigibilidad de derecho se ha registrado correctamente!');
                return Redirect::toAction('editar/' . $key_back . '/2/' . $order . '/' . $page . '/');
            }
        }

        $this->territorio_id = $territorio_id;
        $this->url_redir_back = 'gestion_territorial/gestion_tcur/editar/' . $key_back . '/2/' . $order . '/' . $page . '/';
        $this->page_title = 'Agregar Acción Exigibilidad Derecho al territorio: ' . $territorio_nombre;
        $this->page_module = 'Gestion Territorial';
    }

    public function editar_accion_exigibilidad_derecho($key, $key_back, $order, $page)
    {

        if (!$id = Security::getKey($key, 'upd_accion_exigibilidad_derecho', 'int')) {
            return Redirect::toAction('listar');
        }

        $obj_accion_exigibilidad_derecho = new AccionExigibilidadDerecho();
        if (!$obj_accion_exigibilidad_derecho->getAccionExigibilidadDerechoById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la accion exigibilidad derecho');
            return Redirect::toAction('ver/' . $key_back . '/3/');
        }

        if (Input::hasPost('accion_exigibilidad_derecho')) {
            $obj_accion_exigibilidad_derecho = AccionExigibilidadDerecho::setAccionExigibilidadDerecho('update', Input::post('accion_exigibilidad_derecho'), $obj_accion_exigibilidad_derecho->territorio, array('estado' => AccionExigibilidadDerecho::ACTIVO));

            if ($obj_accion_exigibilidad_derecho) {
                Fuente::setFuente('update', Input::post('fuente'), 'accion_exigibilidad_derecho', $id);
                Flash::valid('La Accion Exigibilidad Derecho se ha actualizado correctamente!');
            }
            return Redirect::toAction('editar/' . $key_back . '/2/' . $order . '/' . $page . '/');
        }

        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('accion_exigibilidad_derecho', $obj_accion_exigibilidad_derecho->id);

        $this->accion_exigibilidad_derecho = $obj_accion_exigibilidad_derecho;
        $this->page_title = 'Actualizar AccionExigibilidadDerecho Comunitario del territorio: ' . $obj_accion_exigibilidad_derecho->territorio;

        $this->page_module = 'Gestión Territorial';
        $this->url_redir_back = 'gestion_territorial/gestion_tcur/editar/' . $key_back . '/2/' . $order . '/' . $page . '/';
        $this->key_back = $key_back;
        $this->key = $key;
    }

    public function ver_accion_exigibilidad_derecho($key, $key_back, $order, $page)
    {

        if (!$id = Security::getKey($key, 'show_accion_exigibilidad_derecho', 'int')) {
            return Redirect::toAction('listar');
        }

        $obj_accion_exigibilidad_derecho = new AccionExigibilidadDerecho();
        if (!$obj_accion_exigibilidad_derecho->getAccionExigibilidadDerechoById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la accion exigibilidad derecho');
            return Redirect::toAction('ver/' . $key_back . '/3/');
        }

        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('accion_exigibilidad_derecho', $obj_accion_exigibilidad_derecho->id);

        $this->accion_exigibilidad_derecho = $obj_accion_exigibilidad_derecho;
        $this->page_title = 'Información Acción Exigibilidad Derecho del territorio: ' . $obj_accion_exigibilidad_derecho->territorio;

        $this->page_module = 'Gestión Territorial';
        $this->url_redir_back = 'gestion_territorial/gestion_tcur/ver/' . $key_back . '/2/' . $order . '/' . $page . '/';

        $this->key_back = $key_back;
        $this->key = $key;
    }


    /**
     * Método para eliminar
     */
    public function eliminar_accion_exigibilidad_derecho($nombre_accion_exigibilidad_derecho, $nombre_territorio, $key, $key_back, $order, $page)
    {

        if (!$id = Security::getKey($key, 'del_accion_exigibilidad_derecho', 'int')) {
            return Redirect::to($url_redir_back);
        }

        $accion_exigibilidad_derecho = new AccionExigibilidadDerecho();

        try {
            if ($accion_exigibilidad_derecho->delete($id)) {
                Flash::valid("La acción exigibilidad derecho $nombre_accion_exigibilidad_derecho se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO la acción exigibilidad derecho $nombre_accion_exigibilidad_derecho, pertenecia al territorio $nombre_territorio.");
                $fuente = new Fuente();
                $fuente->deleteFuente('accion_exigibilidad_derecho', $id);
            } else {
                Flash::warning('Lo sentimos, pero este acción exigibilidad derecho no se puede eliminar.');
            }
        } catch (KumbiaException $e) {
            Flash::error('Esta acción exigibilidad derecho no se puede eliminar porque se encuentra relacionado con otro registro.');
        }

        return Redirect::toAction('editar/' . $key_back . '/2/' . $order . '/' . $page . '/');
    }


    /************************************************
     * **********************************************
     * **********************************************
     * 
     */
    
    public function agregar_iniciativa_empresarial($territorio_id, $territorio_nombre, $key_back, $order, $page)
    {
        $obj_ie = new IniciativaEmpresarial();
        if (Input::hasPost('iniciativa_empresarial')) {
            $afectacion_obj = Afectacion::setAfectacion('create', array('tipo_afectacion_id' => '8'));
            $obj_ie = IniciativaEmpresarial::setIniciativaEmpresarial('create', Input::post('iniciativa_empresarial'), $territorio_nombre, array('afectacion_id'=>$afectacion_obj->id, 'estado' =>  IniciativaEmpresarial::ACTIVO));

            if ($obj_ie) {
                Fuente::setFuente('create', Input::post('fuente'), 'iniciativa_empresarial', $obj_ie->id);
                Flash::valid('La iniciativa empresarial se ha registrado correctamente!');
                $key_upd = Security::setKey($obj_ie->id, 'upd_iniciativa_empresarial');
                return Redirect::toAction("editar_iniciativa_empresarial/$key_upd/$key_back/$order/3/1/");
            }
        }

        $this->territorio_nombre = $territorio_nombre;
        $this->territorio_id = $territorio_id;
        $this->url_redir_back = 'gestion_territorial/gestion_tcur/editar/' . $key_back . '/3/' . $order . '/' . $page . '/';
        $this->page_title = 'Agregar Iniciativa Empresarial al territorio: ' . $territorio_nombre;
        $this->page_module = 'Gestion Territorial';
    }

    public function editar_iniciativa_empresarial($key, $key_back, $order, $page, $recien_creado)
    {
        if (!$id = Security::getKey($key, 'upd_iniciativa_empresarial', 'int')) {
            return Redirect::toAction('listar');
        }

        $obj_iniciativa_empresarial = new IniciativaEmpresarial();
        if (!$obj_iniciativa_empresarial->getIniciativaEmpresarialById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la iniciativa empresarial');
            return Redirect::toAction('ver/' . $key_back . '/3/');
        }
        
        if (Input::hasPost('iniciativa_empresarial')) {
            $obj_ie = IniciativaEmpresarial::setIniciativaEmpresarial('update', Input::post('iniciativa_empresarial'), $obj_iniciativa_empresarial->territorio, array('estado' => IniciativaEmpresarial::ACTIVO));

            if ($obj_ie) {
                Fuente::setFuente('update', Input::post('fuente'), 'iniciativa_empresarial', $id);
                Flash::valid('La Iniciativa Empresarial se ha actualizado correctamente!');
            }
            return Redirect::toAction('editar/' . $key_back . '/3/' . $order . '/' . $page . '/');
        }

        $AfectacionDanoTerritorio = new AfectacionDanoTerritorio();
        $this->AfectacionDanoTerritorio = $AfectacionDanoTerritorio->getDanoTerritorioByAfectacionId($obj_iniciativa_empresarial->afectacion_id);

        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('iniciativa_empresarial', $id);

        $this->iniciativa_empresarial = $obj_iniciativa_empresarial;
        $this->page_title = 'Actualizar Iniciativa Empresarial '.$obj_iniciativa_empresarial->nombre.' del territorio: ' . $obj_iniciativa_empresarial->territorio;
        $this->territorio_nombre = $obj_iniciativa_empresarial->territorio;
        $this->page_module = 'Gestión Territorial';
        $this->url_redir_back = 'gestion_territorial/gestion_tcur/editar/' . $key_back . '/3/' . $order . '/' . $page . '/';
        $this->key_back = $key_back;
        $this->key = $key;
        $this->recien_creado = $recien_creado;
    }

    public function ver_iniciativa_empresarial($key, $key_back, $order, $page)
    {

        if (!$id = Security::getKey($key, 'show_iniciativa_empresarial', 'int')) {
            return Redirect::toAction('listar');
        }

        $obj_iniciativa_empresarial = new IniciativaEmpresarial();
        if (!$obj_iniciativa_empresarial->getIniciativaEmpresarialById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la iniciativa empresarial');
            return Redirect::toAction('ver/' . $key_back . '/3/');
        }

        $iniciativa_empresarial_id = $obj_iniciativa_empresarial->id;
        $obj_descripcion_afectacion = new DescripcionAfectacion();
        $afectaciones_al_territorio = $obj_descripcion_afectacion->getDescripcionAfectacionByIniciativaEmpresarialId($iniciativa_empresarial_id);


        $this->si_no_ambiental = 'NO';
        $this->si_no_cultural = 'NO';
        $this->si_no_economico = 'NO';
        $this->si_no_social = 'NO';
        $this->si_no_organizacion = 'NO';
        $this->si_no_territorial = 'NO';
        $obj_descripcion_afectacion_impacto = new DescripcionAfectacionImpacto();
        foreach ($afectaciones_al_territorio as $afectaciones) {
            //$this->nombre_territorio = $afectaciones->territorio;
            $this->nombre_iniciativa_empresarial = $afectaciones->iniciativa_empresarial;
            $this->iniciativa_empresarial_id = $afectaciones->iniciativa_empresarial_id;

            if ($afectaciones->tipo_impacto_id == '1') {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_ambiental = $afectaciones->id;
                $this->si_no_ambiental = 'SI';
                $this->descripcion_ambiental = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_ambiental[] = $value->impacto_id;
                }
            }

            if ($afectaciones->tipo_impacto_id == '2') {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_cultural = $afectaciones->id;
                $this->si_no_cultural = 'SI';
                $this->descripcion_cultural = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_cultural[] = $value->impacto_id;
                }
            }
            if ($afectaciones->tipo_impacto_id == '3') {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_economico = $afectaciones->id;
                $this->si_no_economico = 'SI';
                $this->descripcion_economico = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_economico[] = $value->impacto_id;
                }
            }
            if ($afectaciones->tipo_impacto_id == '4') {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_social = $afectaciones->id;
                $this->si_no_social = 'SI';
                $this->descripcion_social = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_social[] = $value->impacto_id;
                }
            }
            if ($afectaciones->tipo_impacto_id == '5') {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_organizacion = $afectaciones->id;
                $this->si_no_organizacion = 'SI';
                $this->descripcion_organizacion = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_organizacion[] = $value->impacto_id;
                }
            }
            if ($afectaciones->tipo_impacto_id == '6') {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_territorial = $afectaciones->id;
                $this->si_no_territorial = 'SI';
                $this->descripcion_territorial = $afectaciones->descripcion;
                foreach ($impactos as $value) {
                    $this->array_impacto_territorial[] = $value->impacto_id;
                }
            }
        }


        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('iniciativa_empresarial', $obj_iniciativa_empresarial->id);

        $this->iniciativa_empresarial = $obj_iniciativa_empresarial;
        $this->page_title = 'Información Iniciativa Empresarial del territorio: ' . $obj_iniciativa_empresarial->territorio;
        $this->territorio_nombre = $obj_iniciativa_empresarial->territorio;
        $this->page_module = 'Gestión Territorial';
        $this->url_redir_back = 'gestion_territorial/gestion_tcur/ver/' . $key_back . '/3/' . $order . '/' . $page . '/';

        $this->key_back = $key_back;
        $this->key = $key;
    }


    /**
     * Método para eliminar
     */
    public function eliminar_iniciativa_empresarial($nombre_iniciativa_empresarial, $nombre_territorio, $key, $key_back, $order, $page)
    {

        if (!$id = Security::getKey($key, 'del_iniciativa_empresarial', 'int')) {
            return Redirect::to($url_redir_back);
        }

        $iniciativa_empresarial = new IniciativaEmpresarial();

        try {
            if ($iniciativa_empresarial->delete($id)) {
                Flash::valid("La iniciativa empresarial $nombre_iniciativa_empresarial se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO la iniciativa empresarial $nombre_iniciativa_empresarial, pertenecia al territorio $nombre_territorio.");
                $fuente = new Fuente();
                $fuente->deleteFuente('iniciativa_empresarial', $id);
            } else {
                Flash::warning('Lo sentimos, pero esta iniciativa empresarial no se puede eliminar.');
            }
        } catch (KumbiaException $e) {
            Flash::error('Esta iniciativa empresarial no se puede eliminar porque se encuentra relacionado con otro registro.');
        }

        return Redirect::toAction('editar/' . $key_back . '/2/' . $order . '/' . $page . '/');
    }




    /*     
     ********************************************************************************************
     * **********************************************
     * **********************************************
     */



    /**
     * Método para eliminar
     */
    public function eliminar($key, $redireccionar, $order, $page)
    {
        if (!$id = Security::getKey($key, 'eliminar_territorio', 'int')) {
            return Redirect::toAction($redireccionar . '/' . $order . '/' . $page . '/');
        }

        $territorio = new Territorio();
        if (!$territorio->find_first($id)) {
            Flash::error('Lo sentimos, no se ha podido establecer la información del territorio');
            return Redirect::toAction($redireccionar . '/' . $order . '/' . $page . '/');
        }
        try {
            $titulado_si = new TituladoSi();
            $titulado_no = new TituladoNo();
            $poblacion = new Poblacion();
            $territorio_municipio = new TerritorioMunicipio();

            $titulado_si->delete_all("territorio_id = $id");
            $titulado_no->delete_all("territorio_id = $id");
            $poblacion->delete_all("territorio_id = $id");
            $territorio_municipio->delete_all("territorio_id = $id");

            if ($territorio->delete()) {
                Flash::valid('El territorio se ha eliminado correctamente!');
                DwAudit::warning("Se ha ELIMINADO el territorio $territorio->nombre.");
            } else {
                Flash::warning('Lo sentimos, pero este territorio no se puede eliminar.');
            }
        } catch (KumbiaException $e) {
            Flash::error('Este territorio no se puede eliminar porque se encuentra relacionado con otro registro.');
        }

        return Redirect::toAction($redireccionar . '/' . $order . '/' . $page . '/');
    }
}
