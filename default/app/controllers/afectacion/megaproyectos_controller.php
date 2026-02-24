<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los departamentos del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('afectacion/megaproyecto', 
'global/fuente', 
'afectacion/empleo', 
'afectacion/accion_seguimiento_control',
        'opcion/mads_car', 
        'afectacion/ubicacion', 
        'afectacion/afectacion', 
        'afectacion/programa_social', 
        'afectacion/programa_social_sector_programa_social',
        'afectacion/subsidio', 
        'afectacion/afectacion_territorio',
        'opcion/tipo_subsidio',
        'observatorio/departamento', 
        'observatorio/territorio', 
        'observatorio/municipio', 
        'opcion/impacto', 
        'opcion/forma_pago', 
        'afectacion/afectacion_territorio_impacto',
        'observatorio/subregion', 
        'util/currency', 
        'afectacion/afectacion_dano_territorio',
        'opcion/dano',
        'opcion/tipo_dano',
        'afectacion/desarrollo_normativo',
        'afectacion/politica_publica'
    );

class MegaproyectosController extends BackendController {
            
     /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = Session::get('page_module');
        $this->page_title = 'Clases de megaproyectos';   
    }
    
    /**
     * Método principal
     */
    public function index() {
       $this->page_module = 'Megaproyectos';
       $this->page_title = 'Clases de megaproyectos';   


//Redirect::toAction('listar');
    }
    
    /**
     * Método para listar
     */
    public function listar($clase_megaproyecto='', $order='order.megaproyecto.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $megaproyectos = new Megaproyecto();
        $this->megaproyectos = $megaproyectos->getListadoMegaproyecto('todos', $order, $clase_megaproyecto, $page);        
        $this->order = $order;   
        $this->clase_megaproyecto = $clase_megaproyecto;
        $titulo_modulo = '';
        if($clase_megaproyecto == 'infraestructura'){$titulo_modulo = 'Obras de infraestructura';}
        if($clase_megaproyecto == 'economia_extractiva'){$titulo_modulo = 'Economía extractiva';}
        if($clase_megaproyecto == 'economia_transformacion'){$titulo_modulo = 'Economía de transformación';}
        $this->page_module = 'Megaproyectos de '.$titulo_modulo;
        $this->page_title = 'Listado de megaproyectos'; 
        Session::set('clase_megaproyecto', $clase_megaproyecto);
        Session::set('page_module', 'Megaproyectos de '.$titulo_modulo);    
        Session::set('url_back', "afectacion/megaproyectos/listar/$clase_megaproyecto/$order/page.$page/");
    }
    
     /**
     * Método para buscar
     * 
     * @param type $field Nombre del campo a buscar
     * @param type $value Valor del campo
     * @param type $order Método de ordenamiento
     * @param type $page Número de página
     */
    public function buscar_megaproyecto($field='nombre', $value='none', $order='order.id.asc', $page='page.1') {        
        $page       = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field      = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value      = (Input::hasPost('value')) ? Input::post('value') : $value;
        $megaproyecto     = new Megaproyecto();
        $megaproyectos    = $megaproyecto->getAjaxMegaproyecto($field, $value, $order, $page);
        if(empty($megaproyectos->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->clase_megaproyecto = Session::get('clase_megaproyecto');
        $this->megaproyectos= $megaproyectos;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de megaproyectos en el sistema';   
        $this->page_module = 'Megaproyectos de '.Session::get('clase_megaproyecto');
        Session::set('url_back', "afectacion/megaproyectos/buscar_megaproyecto/$field/$value/$order/page.$page/");
    }
    
    /**
     * Método para agregar
     */
    public function agregar($clase_megaproyecto = '') {
        $titulo_modulo = '';
        if($clase_megaproyecto == 'infraestructura'){$titulo_modulo = 'Obras de infraestructura';}
        if($clase_megaproyecto == 'economia_extractiva'){$titulo_modulo = 'Economía extractiva';}
        if($clase_megaproyecto == 'economia_transformacion'){$titulo_modulo = 'Economía de transformación';}
        
        $this->page_module = 'Megaproyecto de '.$titulo_modulo;
        $this->page_title = 'Agregar megaproyecto';
        $this->clase_megaproyecto = $clase_megaproyecto; 

        
        if(Input::hasPost('megaproyecto')) {
            
            $afectacion_obj = Afectacion::setAfectacion('create', array('tipo_afectacion_id'=>'3'));
           if($afectacion_obj)
           {
            $ubicacion_obj = Ubicacion::setUbicacion('create', Input::post('caso'), array('afectacion_id'=>$afectacion_obj->id));
            $megaproyecto_obj = new Megaproyecto();
            
            $post_megaproyecto = Input::post('megaproyecto');
            $post_megaproyecto['monto_inversion'] = str_replace(",", "", $post_megaproyecto['monto_inversion']);
            $post_megaproyecto['area_terrestre'] = Currency::comaApunto($post_megaproyecto['area_terrestre']);
            $post_megaproyecto['area_maritima'] = Currency::comaApunto($post_megaproyecto['area_maritima']);
             
            $megaproyecto_obj = Megaproyecto::setMegaproyecto('create', $post_megaproyecto, array('afectacion_id'=>$afectacion_obj->id, 'estado'=>Megaproyecto::ACTIVO));
            if($megaproyecto_obj)
            {                       
              $megaproyecto_id = $megaproyecto_obj->id;              
              Fuente::setFuente('create', Input::post('fuente'), 'megaproyecto', $megaproyecto_id);
                
              Flash::valid('El megaproyecto de '.$megaproyecto_obj->nombre.' se ha registrado correctamente!');
              return Redirect::to(Session::get('url_back'));                
            }  
           }
                   
        }
        
    }

     /**
     * Método para agregar
     */
    public function agregar_desde_modal()
    {
        $afectacion_obj = Afectacion::setAfectacion('create', array('tipo_afectacion_id' => '3'));
        if ($afectacion_obj) {
            $Megaproyecto = new Megaproyecto();
            $Megaproyecto->afectacion_id = $afectacion_obj->id;
            $Megaproyecto->nombre = Input::post('megaproyecto_nombre');
            $Megaproyecto->tipo_megaproyecto_id = Input::post('megaproyecto_tipo_megaproyecto_id');
            $Megaproyecto->subtipo_megaproyecto_id = Input::post('megaproyecto_subtipo_megaproyecto_id');     
            $Megaproyecto->clase_megaproyecto = Input::post('megaproyecto_clase_megaproyecto');  
            if($Megaproyecto->create()){
                $Fuente = new Fuente();
                $Fuente->fecha = date('Y-m-d', strtotime(Input::post('fuente_fecha')));
                $Fuente->nombre = Input::post('fuente_descripcion');
                $Fuente->tabla ='megaproyecto';
                $Fuente->tabla_identi = $Megaproyecto->id;    
                $Fuente->create();

                $key_upd = Security::setKey($Megaproyecto->id, 'upd_megaproyecto');
                $array = array('key_upd'=>$key_upd);
                $this->data = $array;
                $this->clase_megaproyecto = $Megaproyecto->clase_megaproyecto;
                View::template(null);
            }
        }       
    }
    
        /**
     * Método para ver
     */
    public function ver($key, $clase_megaproyecto, $tab='', $sub_tab='', $order='order.megaproyecto.asc') { 
             
        if(!$id = Security::getKey($key, 'show_megaproyecto', 'int')) {
            return Redirect::toAction('listar/');
        } 
        
        $titulo_modulo = '';
        if($clase_megaproyecto == 'infraestructura'){$titulo_modulo = 'Obras de infraestructura';}
        if($clase_megaproyecto == 'economia_extractiva'){$titulo_modulo = 'Economía extractiva';}
        if($clase_megaproyecto == 'economia_transformacion'){$titulo_modulo = 'Economía de transformación';}
        
        $this->page_module = 'Megaproyecto de '.$titulo_modulo;
        $this->clase_megaproyecto = $clase_megaproyecto;
        
        //Para saber que pestaña estara activa cuando visualice un territorio
        $this->tab_1_active = '';
        $this->tab_2_active = '';
        $this->tab_3_active = '';
        $this->tab_4_active = '';
        $this->tab_5_active = '';
        
        if($tab == 1 || $tab == ''){ $this->tab_1_active = 'active'; }
        if($tab == 2){ $this->tab_2_active = 'active'; }
        if($tab == 3){ $this->tab_3_active = 'active'; }
        if($tab == 4){ $this->tab_4_active = 'active'; }
        if($tab == 5){ $this->tab_5_active = 'active'; }
        
         //Para saber que subpestaña estara activa cuando visualice un vinculacion de poblacion
         $this->sub_tab = $sub_tab;
 
        
        $megaproyecto = new Megaproyecto();
        if(!$megaproyecto->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del megaproyecto');
            return Redirect::toAction('listar');
        }   
        
        $ubicaciones = $megaproyecto->getAfectacion()->getUbicaciones($megaproyecto->afectacion_id);
        $this->ubicaciones = $ubicaciones;
        
        $obj_empleos = new Empleo();
        $this->empleos = $obj_empleos->getEmpleosByMegaproyectoId($id);
        
        $obj_programas_sociales = new ProgramaSocial();
        $this->programa_sociales = $obj_programas_sociales->getProgramaSocialsByMegaproyectoId($id);
        
        $obj_subsidio = new Subsidio();
        $this->subsidios = $obj_subsidio->getSubsidiosByMegaproyectoId($id);
        
        
        $obj_afectacion_territorio = new AfectacionTerritorio();
        $this->territorios_afectados = $obj_afectacion_territorio->getTerritorioAfectadoByAfectacionId($megaproyecto->getAfectacion()->id);
        
        
        $obj_accion_seguimiento_controls = new AccionSeguimientoControl();
        $this->accion_seguimiento_controls = $obj_accion_seguimiento_controls->getAccionSeguimientoControlsByMegaproyectoId($id);
                                
               
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('megaproyecto', $megaproyecto->id);
        //var_dump($this->fuentes);        die(); 
              
        $this->megaproyecto = $megaproyecto;
        $this->order = $order;        
        
        $this->page_title = 'Información del megaproyecto '.$megaproyecto->nombre;
        $this->key = $key;
        $this->url_redir_back = Session::get('url_back');
        
//         if(Input::hasPost('megaproyecto')) {
//            
//            if(Megaproyecto::setMegaproyecto('update', Input::post('megaproyecto'), array('id'=>$id)))
//                {
//                                
//                Fuente::setFuente('update', Input::post('fuente'), 'territorio', $id);          
//                Flash::valid('El megaproyecto se ha actualizado correctamente!');
//                return Redirect::toAction('listar/'.$clase_megaproyecto.'/');
//            }            
//        }
        
        
    }
    
     
    /**
     * Método para editar
     */
    public function editar($key, $clase_megaproyecto, $tab='', $sub_tab='', $order='order.megaproyecto.asc') { 
             
        if(!$id = Security::getKey($key, 'upd_megaproyecto', 'int')) {
            return Redirect::toAction('listar/');
        }  
        
        $titulo_modulo = '';
        if($clase_megaproyecto == 'infraestructura'){$titulo_modulo = 'Obras de infraestructura';}
        if($clase_megaproyecto == 'economia_extractiva'){$titulo_modulo = 'Economía extractiva';}
        if($clase_megaproyecto == 'economia_transformacion'){$titulo_modulo = 'Economía de transformación';}
        
        $this->page_module = 'Megaproyecto de '.$titulo_modulo;

        //Para saber que pestaña estara activa cuando visualice un territorio
        $this->tab_1_active = '';
        $this->tab_2_active = '';
        $this->tab_3_active = '';
        $this->tab_4_active = '';
        $this->tab_5_active = '';
        $this->tab_6_active = '';
        $this->tab_7_active = '';
        
        if($tab == 1 || $tab == ''){ $this->tab_1_active = 'in active'; }
        if($tab == 2){ $this->tab_2_active = 'in active'; }
        if($tab == 3){ $this->tab_3_active = 'in active'; }
        if($tab == 4){ $this->tab_4_active = 'in active'; }
        if($tab == 5){ $this->tab_5_active = 'in active'; }
        if($tab == 6){ $this->tab_6_active = 'in active'; }
        if($tab == 7){ $this->tab_7_active = 'in active'; }
        
         //Para saber que subpestaña estara activa cuando visualice un vinculacion de poblacion
         $this->sub_tab = $sub_tab;
 
         if(Input::hasPost('megaproyecto')) {
            $post_megaproyecto = Input::post('megaproyecto');
            $post_megaproyecto['monto_inversion'] = str_replace(",", "", $post_megaproyecto['monto_inversion']);
            $post_megaproyecto['area_terrestre'] = Currency::comaApunto($post_megaproyecto['area_terrestre']);
            $post_megaproyecto['area_maritima'] = Currency::comaApunto($post_megaproyecto['area_maritima']);
            
            if(Megaproyecto::setMegaproyecto('update', $post_megaproyecto, array('id'=>$id)))
                {                
                Fuente::setFuente('update', Input::post('fuente'), 'megaproyecto', $id);          
                Flash::valid('El megaproyecto se ha actualizado correctamente!');
                return Redirect::to(Session::get('url_back'));
            }            
        }
        
        $megaproyecto = new Megaproyecto();
        if(!$megaproyecto->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del megaproyecto');
            return Redirect::toAction('listar');
        }   
        
        $DesarrolloNormativo = new DesarrolloNormativo();
        $desarrollo_normativos = $DesarrolloNormativo->getDesarrolloNormativoByMegaproyectoId($megaproyecto->id);
        $this->desarrollo_normativos = $desarrollo_normativos;

        $PoliticaPublica = new PoliticaPublica();
        $politicas_publicas = $PoliticaPublica->getPoliticaPublicaByMegaproyectoId($megaproyecto->id);
        $this->politicas_publicas = $politicas_publicas;
        
        $obj_empleos = new Empleo();
        $this->empleos = $obj_empleos->getEmpleosByMegaproyectoId($id);
        
        $obj_programas_sociales = new ProgramaSocial();
        $this->programa_sociales = $obj_programas_sociales->getProgramaSocialsByMegaproyectoId($id);
        
        $obj_subsidio = new Subsidio();
        $this->subsidios = $obj_subsidio->getSubsidiosByMegaproyectoId($id);        
        
//        $obj_afectacion_territorio = new AfectacionTerritorio();
//        $this->territorios_afectados = $obj_afectacion_territorio->getTerritorioAfectadoByAfectacionId($megaproyecto->getAfectacion()->id);        
        $AfectacionDanoTerritorio = new AfectacionDanoTerritorio();
        $this->AfectacionDanoTerritorio = $AfectacionDanoTerritorio->getDanoTerritorioByAfectacionId($megaproyecto->afectacion_id);
        
        $obj_accion_seguimiento_controls = new AccionSeguimientoControl();
        $this->accion_seguimiento_controls = $obj_accion_seguimiento_controls->getAccionSeguimientoControlsByMegaproyectoId($id);
        
        $ubicaciones = $megaproyecto->getAfectacion()->getUbicaciones($megaproyecto->afectacion_id);
        $this->ubicaciones = $ubicaciones;
               
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('megaproyecto', $megaproyecto->id);
        //var_dump($this->fuentes);        die();  
        
        $this->clase_megaproyecto = $clase_megaproyecto;
        $this->megaproyecto = $megaproyecto;
        $this->order = $order;                
        $this->page_title = 'Actualizar megaproyecto '.$megaproyecto->nombre;
        $this->key = $key;
        $this->url_redir_back = Session::get('url_back');
        
        
        
        
    }
    
    
    public function agregar_empleo($megaproyecto_id, $megaproyecto_nombre, $tipo_megaproyecto, $key_back)
    { 
        $obj_empleo = new Empleo();
        if(Input::hasPost('empleo')) 
        {        
            $obj_empleo = Empleo::setEmpleo('create', Input::post('empleo'), $megaproyecto_nombre, array('estado'=>  Empleo::ACTIVO));
            $empleo_id = $obj_empleo->id;            
            //Poblacion::setPoblacion('create', Input::post('poblacion'), 'empleo_id', $empleo_id);    
            
            Flash::valid('El empleo se ha registrado correctamente!');
            return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/1/');
        }
        
        $this->megaproyecto_id = $megaproyecto_id;
        $this->page_title = 'Agregar empleo al Megaproyecto: '.$megaproyecto_nombre;
       
            
        $this->url_redir_back = 'afectacion/megaproyectos/editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/1/';
    }
    
     public function editar_empleo($key, $tipo_megaproyecto, $key_back) { 
       
        if(!$id = Security::getKey($key, 'upd_empleo', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $obj_empleo = new Empleo();        
        if(!$obj_empleo->getEmpleoById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del empleo');
            return Redirect::toAction('ver/'.$key_back.'/3/');
        }   
        
        $this->obj_empleo = $obj_empleo; 
        $this->page_title = 'Actualizar Empleo del Megaproyecto: '.$obj_empleo->megaproyecto;
      
        if(Input::hasPost('empleo')) 
        {        
            $obj_empleo = Empleo::setEmpleo('update', Input::post('empleo'), $obj_empleo->megaproyecto, array('estado'=>  Empleo::ACTIVO));
            
            if($obj_empleo)
            {
                Flash::valid('El empleo se ha actualizado correctamente!');
            }
            return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/1/');
           
        }
            
        $this->key_back = $key_back;    
        $this->key = $key;      
        $this->url_redir_back = 'afectacion/megaproyectos/editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/1/';
    }
    
    public function ver_empleo($key, $tipo_megaproyecto, $key_back) { 
       
        if(!$id = Security::getKey($key, 'show_empleo', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $obj_empleo = new Empleo();        
        if(!$obj_empleo->getEmpleoById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del empleo');
            return Redirect::toAction('ver/'.$key_back.'/3/');
        }   
        
        $this->obj_empleo = $obj_empleo; 
        $this->page_title = 'Información Empleo del Megaproyecto: '.$obj_empleo->megaproyecto;
        
                 
        $this->key_back = $key_back;    
        $this->key = $key;   
        $this->url_redir_back = 'afectacion/megaproyectos/ver/'.$key_back.'/'.$tipo_megaproyecto.'/2/1/';
        
    }
    
     public function agregar_programa_social($megaproyecto_id, $megaproyecto_nombre, $tipo_megaproyecto, $key_back)
    { 
        $obj_programa_social = new ProgramaSocial();
        if(Input::hasPost('programa_social')) 
        {        
            $obj_programa_social = ProgramaSocial::setProgramaSocial('create', Input::post('programa_social'), $megaproyecto_nombre, array('estado'=>  ProgramaSocial::ACTIVO));
            $programa_social_id = $obj_programa_social->id;            
            if($obj_programa_social)
            {
                $obj_pssps = new ProgramaSocialSectorProgramaSocial();
                $obj_pssps->guardar($programa_social_id, Input::post('sector_programa_social'));
                
                Flash::valid('El Programa Social se ha registrado correctamente!');                
            }
            
            
            return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/2/');
        }
        
        $this->megaproyecto_id = $megaproyecto_id;
        $this->page_title = 'Agregar Programa Social al Megaproyecto: '.$megaproyecto_nombre;
                   
        $this->url_redir_back = 'afectacion/megaproyectos/editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/2/';
    }
    
     public function editar_programa_social($key, $tipo_megaproyecto, $key_back) { 
       
        if(!$id = Security::getKey($key, 'upd_programa_social', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $obj_programa_social = new ProgramaSocial();        
        if(!$obj_programa_social->getProgramaSocialById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del Programa Social');
            return Redirect::toAction('ver/'.$key_back.'/3/');
        }   
        
        $this->obj_programa_social = $obj_programa_social; 
        $this->page_title = 'Actualizar Programa Social del Megaproyecto: '.$obj_programa_social->megaproyecto;
        
        foreach ($this->obj_programa_social->getProgramaSocialSectorProgramaSocial() as $value) {
                        $this->sector_programa_social[] = $value->sector_programa_social_id;
                    }                    
                 
        if(Input::hasPost('programa_social')) 
        {        
            $obj_programa_social = ProgramaSocial::setProgramaSocial('update', Input::post('programa_social'), $obj_programa_social->megaproyecto, array('estado'=>  ProgramaSocial::ACTIVO));
            
            if($obj_programa_social)
            {
                $obj_pssps = new ProgramaSocialSectorProgramaSocial();
                $obj_pssps->guardar($obj_programa_social->id, Input::post('sector_programa_social'));
                Flash::valid('El Programa Social se ha actualizado correctamente!');
            }
            return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/2/');
           
        }
            
        $this->key_back = $key_back;    
        $this->key = $key;        
        $this->url_redir_back = 'afectacion/megaproyectos/editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/2/';
    }
    
    public function ver_programa_social($key, $tipo_megaproyecto, $key_back) { 
       
        if(!$id = Security::getKey($key, 'show_programa_social', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $obj_programa_social = new ProgramaSocial();        
        if(!$obj_programa_social->getProgramaSocialById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del Programa Social');
            return Redirect::toAction('ver/'.$key_back.'/3/');
        }   
        
        $this->obj_programa_social = $obj_programa_social; 
        $this->page_title = 'Información Programa Social del Megaproyecto: '.$obj_programa_social->megaproyecto;
        
         foreach ($this->obj_programa_social->getProgramaSocialSectorProgramaSocial() as $value) {
                        $this->sector_programa_social[] = $value->sector_programa_social_id;
                    }        
                
        $this->key_back = $key_back;    
        $this->key = $key;        
        $this->url_redir_back = 'afectacion/megaproyectos/ver/'.$key_back.'/'.$tipo_megaproyecto.'/2/2/';
    }
    
     public function agregar_subsidio($megaproyecto_id, $megaproyecto_nombre, $tipo_megaproyecto, $key_back)
    { 
        $obj_subsidio = new Subsidio();
        if(Input::hasPost('subsidio')) 
        {        
            $obj_subsidio = Subsidio::setSubsidio('create', Input::post('subsidio'), $megaproyecto_nombre, array('estado'=>  Subsidio::ACTIVO));
            $subsidio_id = $obj_subsidio->id;            
            if($obj_subsidio)
            {
                Flash::valid('El Subsidio se ha registrado correctamente!');                
            }
            
            
            return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/3/');
        }
        
        $this->megaproyecto_id = $megaproyecto_id;
        $this->page_title = 'Agregar Subsidio al Megaproyecto: '.$megaproyecto_nombre;
                   
        $this->url_redir_back = 'afectacion/megaproyectos/editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/3/';
    }
    
     public function editar_subsidio($key, $tipo_megaproyecto, $key_back) { 
       
        if(!$id = Security::getKey($key, 'upd_subsidio', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $obj_subsidio = new Subsidio();        
        if(!$obj_subsidio->getSubsidioById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del Subsidio');
            return Redirect::toAction('ver/'.$key_back.'/3/');
        }   
        
        $this->obj_subsidio = $obj_subsidio; 
        $this->page_title = 'Actualizar Subsidio del Megaproyecto: '.$obj_subsidio->megaproyecto;
                    
        if(Input::hasPost('subsidio')) 
        {        
            $obj_subsidio = Subsidio::setSubsidio('update', Input::post('subsidio'), $obj_subsidio->megaproyecto, array('estado'=>  Subsidio::ACTIVO));
            
            if($obj_subsidio)
            {                
                Flash::valid('El Subsidio se ha actualizado correctamente!');
            }
            return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/3/');
           
        }
            
        $this->key_back = $key_back;    
        $this->key = $key;        
        $this->url_redir_back = 'afectacion/megaproyectos/editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/3/';
    }
    
    public function ver_subsidio($key, $tipo_megaproyecto, $key_back) { 
       
        if(!$id = Security::getKey($key, 'show_subsidio', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $obj_subsidio = new Subsidio();        
        if(!$obj_subsidio->getSubsidioById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del Subsidio');
            return Redirect::toAction('ver/'.$key_back.'/3/');
        }   
        
        $this->obj_subsidio = $obj_subsidio; 
        $this->page_title = 'Información Subsidio del Megaproyecto: '.$obj_subsidio->megaproyecto;
        
        $this->key_back = $key_back;    
        $this->key = $key;        
        $this->url_redir_back = 'afectacion/megaproyectos/ver/'.$key_back.'/'.$tipo_megaproyecto.'/2/3/';
    }
    
     public function agregar_afectacion_territorio($afectacion_id, $megaproyecto_nombre, $tipo_megaproyecto, $key_back)
    { 
        $obj_afectacion_territorio = new AfectacionTerritorio();
        if(Input::hasPost('afectacion_territorio')) 
        {
            $data_impacto_ambiental    = Input::post('impacto_ambiental');
            $data_impacto_cultural     = Input::post('impacto_cultural');
            $data_impacto_economico    = Input::post('impacto_economico');
            $data_impacto_social       = Input::post('impacto_social');
            $data_impacto_organizacion = Input::post('impacto_organizacion');
            $data_impacto_territorial  = Input::post('impacto_territorial');
            
            if($data_impacto_ambiental['si_no'] == 'SI')
            { 
                $obj_afectacion_territorio = 
                AfectacionTerritorio::setAfectacionTerritorio('create', 
                        Input::post('afectacion_territorio'), 
                        $megaproyecto_nombre, 
                        array('tipo_impacto_id'=>'1',
                            'descripcion'=>$data_impacto_ambiental['descripcion'],
                              'estado'=>  AfectacionTerritorio::ACTIVO),
                        'Impacto Ambiental'); 
                
                $afectacion_territorio_id = $obj_afectacion_territorio->id;                
                
                if($obj_afectacion_territorio)
                {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_ambiental'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            if($data_impacto_cultural['si_no'] == 'SI')
            { 
                $obj_afectacion_territorio = 
                AfectacionTerritorio::setAfectacionTerritorio('create', 
                        Input::post('afectacion_territorio'), 
                        $megaproyecto_nombre, 
                        array('tipo_impacto_id'=>'2',
                            'descripcion'=>$data_impacto_cultural['descripcion'],
                              'estado'=>  AfectacionTerritorio::ACTIVO),
                        'Impacto Cultural'); 
                
                $afectacion_territorio_id = $obj_afectacion_territorio->id;                
                
                if($obj_afectacion_territorio)
                {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_cultural'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            if($data_impacto_economico['si_no'] == 'SI')
            { 
                $obj_afectacion_territorio = 
                AfectacionTerritorio::setAfectacionTerritorio('create', 
                        Input::post('afectacion_territorio'), 
                        $megaproyecto_nombre, 
                        array('tipo_impacto_id'=>'3',
                            'descripcion'=>$data_impacto_economico['descripcion'],
                              'estado'=>  AfectacionTerritorio::ACTIVO),
                        'Impacto Económico'); 
                
                $afectacion_territorio_id = $obj_afectacion_territorio->id;                
                
                if($obj_afectacion_territorio)
                {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_economico'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            if($data_impacto_social['si_no'] == 'SI')
            { 
                $obj_afectacion_territorio = 
                AfectacionTerritorio::setAfectacionTerritorio('create', 
                        Input::post('afectacion_territorio'), 
                        $megaproyecto_nombre, 
                        array('tipo_impacto_id'=>'4',
                            'descripcion'=>$data_impacto_social['descripcion'],
                              'estado'=>  AfectacionTerritorio::ACTIVO),
                        'Impacto Social'); 
                
                $afectacion_territorio_id = $obj_afectacion_territorio->id;                
                
                if($obj_afectacion_territorio)
                {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_social'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            if($data_impacto_organizacion['si_no'] == 'SI')
            { 
                $obj_afectacion_territorio = 
                AfectacionTerritorio::setAfectacionTerritorio('create', 
                        Input::post('afectacion_territorio'), 
                        $megaproyecto_nombre, 
                        array('tipo_impacto_id'=>'5',
                            'descripcion'=>$data_impacto_organizacion['descripcion'],
                              'estado'=>  AfectacionTerritorio::ACTIVO),
                        'Impacto Organizacional'); 
                
                $afectacion_territorio_id = $obj_afectacion_territorio->id;                
                
                if($obj_afectacion_territorio)
                {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_organizacion'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            if($data_impacto_territorial['si_no'] == 'SI')
            { 
                $obj_afectacion_territorio = 
                AfectacionTerritorio::setAfectacionTerritorio('create', 
                        Input::post('afectacion_territorio'), 
                        $megaproyecto_nombre, 
                        array('tipo_impacto_id'=>'6',
                            'descripcion'=>$data_impacto_territorial['descripcion'],
                              'estado'=>  AfectacionTerritorio::ACTIVO),
                        'Impacto Territorial'); 
                
                $afectacion_territorio_id = $obj_afectacion_territorio->id;                
                
                if($obj_afectacion_territorio)
                {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_territorial'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
             if($obj_afectacion_territorio)
                {
                   Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
            
            return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/3/1/');
        }
        
        $this->afectacion_id = $afectacion_id;
        $this->page_title = 'Agregar Afectación causada por el Megaproyecto: '.$megaproyecto_nombre;
       
            
        $this->url_redir_back = 'afectacion/megaproyectos/editar/'.$key_back.'/'.$tipo_megaproyecto.'/3/1/';
    }
    


        public function editar_afectacion_territorio($key, $tipo_megaproyecto, $key_back, $afectacion_id) { 
       
        if(!$id = Security::getKey($key, 'upd_afectacion_territorio', 'int')) {
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
        $this->nombre_megaproyecto = $a_afectacion->getAfectacion()->getMegaproyecto()->nombre;
        
        $objeto_estado_nivel = new AfectacionTerritorio();
        $objeto_estado_nivel->id = $a_afectacion->id;
        $objeto_estado_nivel->estado = $a_afectacion->estado;
        $objeto_estado_nivel->nivel = $a_afectacion->nivel;
        $objeto_estado_nivel->nombre = $a_afectacion->territorio;
        $this->objeto_estado_nivel=$objeto_estado_nivel;        
        
        $obj_afectacion_territorio_impacto = new AfectacionTerritorioImpacto();
        foreach ($afectaciones_al_territorio as $afectaciones)
        {
            $this->nombre_territorio = $afectaciones->territorio;
            //$this->nombre_megaproyecto = $afectaciones->getAfectacion()->getMegaproyecto()->nombre;
            //$this->nombre_megaproyecto = $afectaciones->megaproyecto;
            $this->afectacion_id = $afectaciones->afectacion_id;
            
            
            if($afectaciones->tipo_impacto_id == '1')
            {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_ambiental = $afectaciones->id;
                $this->si_no_ambiental = 'SI';
                $this->descripcion_ambiental = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_ambiental[] = $value->impacto_id;
                }                
            }
            
            if($afectaciones->tipo_impacto_id == '2')
            {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_cultural = $afectaciones->id;
                $this->si_no_cultural = 'SI';
                $this->descripcion_cultural = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_cultural[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '3')
            {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_economico = $afectaciones->id;
                $this->si_no_economico = 'SI';
                $this->descripcion_economico = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_economico[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '4')
            {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_social = $afectaciones->id;
                $this->si_no_social = 'SI';
                $this->descripcion_social = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_social[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '5')
            {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_organizacion = $afectaciones->id;
                $this->si_no_organizacion = 'SI';
                $this->descripcion_organizacion = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_organizacion[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '6')
            {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_territorial = $afectaciones->id;
                $this->si_no_territorial = 'SI';
                $this->descripcion_territorial = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_territorial[] = $value->impacto_id;
                }                
            }
            
        }       
        
        $this->page_title = 'Actualizar Afectación causada por el Megaproyecto: '.$this->nombre_megaproyecto;
        if ($tipo_megaproyecto == Megaproyecto::TIPO_AGROINDUSTRIA){
        $this->page_module = Megaproyecto::AGROINDUSTRIA;  
        }
        elseif ($tipo_megaproyecto == Megaproyecto::TIPO_HIDROCARBURO) 
            {$this->page_module = Megaproyecto::HIDROCARBURO;}
            elseif ($tipo_megaproyecto == Megaproyecto::TIPO_INFRAESTRUCTURA) 
            {$this->page_module = Megaproyecto::INFRAESTRUCTURA;}
            elseif ($tipo_megaproyecto == Megaproyecto::TIPO_MINERIA) 
            {$this->page_module = Megaproyecto::MINERIA;}            
        $this->key_back = $key_back;    
        $this->key = $key;        
        $this->url_redir_back = 'afectacion/megaproyectos/editar/'.$key_back.'/'.$tipo_megaproyecto.'/3/1/';
        
        $obj_afectacion_territorio = new AfectacionTerritorio();
        if(Input::hasPost('afectacion_territorio')) 
        {
            $data_impacto_ambiental    = Input::post('impacto_ambiental');
            $data_impacto_cultural     = Input::post('impacto_cultural');
            $data_impacto_economico    = Input::post('impacto_economico');
            $data_impacto_social       = Input::post('impacto_social');
            $data_impacto_organizacion = Input::post('impacto_organizacion');
            $data_impacto_territorial  = Input::post('impacto_territorial');
            
            if($data_impacto_ambiental['si_no'] == 'SI')
            { 
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '1');
                $obj_afectacion_territorio = 
                AfectacionTerritorio::setAfectacionTerritorio('create', 
                        Input::post('afectacion_territorio'), 
                        $this->nombre_megaproyecto, 
                        array('tipo_impacto_id'=>'1',
                            'descripcion'=>$data_impacto_ambiental['descripcion'],
                              'estado'=>  AfectacionTerritorio::ACTIVO),
                        'Impacto Ambiental'); 
                
                $afectacion_territorio_id = $obj_afectacion_territorio->id;                
                
                if($obj_afectacion_territorio)
                {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_ambiental'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            else {AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '1');}
            
            
            if($data_impacto_cultural['si_no'] == 'SI')
            { 
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '2');
                $obj_afectacion_territorio = 
                AfectacionTerritorio::setAfectacionTerritorio('create', 
                        Input::post('afectacion_territorio'), 
                        $this->nombre_megaproyecto, 
                        array('tipo_impacto_id'=>'2',
                            'descripcion'=>$data_impacto_cultural['descripcion'],
                              'estado'=>  AfectacionTerritorio::ACTIVO),
                        'Impacto Cultural'); 
                
                $afectacion_territorio_id = $obj_afectacion_territorio->id;                
                
                if($obj_afectacion_territorio)
                {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_cultural'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            else {AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '2');}
            
            
            if($data_impacto_economico['si_no'] == 'SI')
            {
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '3');
                $obj_afectacion_territorio = 
                AfectacionTerritorio::setAfectacionTerritorio('create', 
                        Input::post('afectacion_territorio'), 
                        $this->nombre_megaproyecto, 
                        array('tipo_impacto_id'=>'3',
                            'descripcion'=>$data_impacto_economico['descripcion'],
                              'estado'=>  AfectacionTerritorio::ACTIVO),
                        'Impacto Económico'); 
                
                $afectacion_territorio_id = $obj_afectacion_territorio->id;                
                
                if($obj_afectacion_territorio)
                {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_economico'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            else {AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '3');}
            
            
            if($data_impacto_social['si_no'] == 'SI')
            { 
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '4');
                $obj_afectacion_territorio = 
                AfectacionTerritorio::setAfectacionTerritorio('create', 
                        Input::post('afectacion_territorio'), 
                        $this->nombre_megaproyecto, 
                        array('tipo_impacto_id'=>'4',
                            'descripcion'=>$data_impacto_social['descripcion'],
                              'estado'=>  AfectacionTerritorio::ACTIVO),
                        'Impacto Social'); 
                
                $afectacion_territorio_id = $obj_afectacion_territorio->id;                
                
                if($obj_afectacion_territorio)
                {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_social'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            else {AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '4');}
            
            
            if($data_impacto_organizacion['si_no'] == 'SI')
            { 
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '5');
                $obj_afectacion_territorio = 
                AfectacionTerritorio::setAfectacionTerritorio('create', 
                        Input::post('afectacion_territorio'), 
                        $this->nombre_megaproyecto, 
                        array('tipo_impacto_id'=>'5',
                            'descripcion'=>$data_impacto_organizacion['descripcion'],
                              'estado'=>  AfectacionTerritorio::ACTIVO),
                        'Impacto Organizacional'); 
                
                $afectacion_territorio_id = $obj_afectacion_territorio->id;                
                
                if($obj_afectacion_territorio)
                {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_organizacion'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            else {AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '5');}
            
            
            if($data_impacto_territorial['si_no'] == 'SI')
            { 
                AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '6');
                $obj_afectacion_territorio = 
                AfectacionTerritorio::setAfectacionTerritorio('create', 
                        Input::post('afectacion_territorio'), 
                        $this->nombre_megaproyecto, 
                        array('tipo_impacto_id'=>'6',
                            'descripcion'=>$data_impacto_territorial['descripcion'],
                              'estado'=>  AfectacionTerritorio::ACTIVO),
                        'Impacto Territorial'); 
                
                $afectacion_territorio_id = $obj_afectacion_territorio->id;                
                
                if($obj_afectacion_territorio)
                {
                    $obj_mti = new AfectacionTerritorioImpacto();
                    $obj_mti->guardar($obj_afectacion_territorio->id, Input::post('checks_impacto_territorial'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            else {AfectacionTerritorio::deleteAfectacionTerritorio($afectacion_id, '6');}            
            
            
                   Flash::valid('La afectación al territorio se ha actualizado correctamente!');                
             
            
            return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/3/1/');
        }
        
    }      
    
    

    
        public function ver_afectacion_territorio($key, $tipo_megaproyecto, $key_back, $megaproyecto_id) { 
       
        if(!$id = Security::getKey($key, 'show_afectacion_territorio', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $this->territorio_id = $id;
        
        $obj_afectacion_territorio = new AfectacionTerritorio();
        $afectaciones_al_territorio = $obj_afectacion_territorio->getAfectacionTerritorioByAfectacionIdTerritorioId($megaproyecto_id, $id);
        
        
        $this->si_no_ambiental = 'NO';
        $this->si_no_cultural = 'NO';
        $this->si_no_economico = 'NO';
        $this->si_no_social = 'NO';
        $this->si_no_organizacion = 'NO';
        $this->si_no_territorial = 'NO';
        $obj_afectacion_territorio_impacto = new AfectacionTerritorioImpacto();
        foreach ($afectaciones_al_territorio as $afectaciones)
        {
            $this->nombre_territorio = $afectaciones->territorio;
            $this->nombre_megaproyecto = $afectaciones->megaproyecto;
            $this->megaproyecto_id = $afectaciones->megaproyecto_id;
            
            if($afectaciones->tipo_impacto_id == '1')
            {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_ambiental = $afectaciones->id;
                $this->si_no_ambiental = 'SI';
                $this->descripcion_ambiental = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_ambiental[] = $value->impacto_id;
                }                
            }
            
            if($afectaciones->tipo_impacto_id == '2')
            {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_cultural = $afectaciones->id;
                $this->si_no_cultural = 'SI';
                $this->descripcion_cultural = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_cultural[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '3')
            {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_economico = $afectaciones->id;
                $this->si_no_economico = 'SI';
                $this->descripcion_economico = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_economico[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '4')
            {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_social = $afectaciones->id;
                $this->si_no_social = 'SI';
                $this->descripcion_social = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_social[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '5')
            {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_organizacion = $afectaciones->id;
                $this->si_no_organizacion = 'SI';
                $this->descripcion_organizacion = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_organizacion[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '6')
            {
                $impactos = $obj_afectacion_territorio_impacto->getAfectacionTerritorioImpacto($afectaciones->id);
                $this->id_territorial = $afectaciones->id;
                $this->si_no_territorial = 'SI';
                $this->descripcion_territorial = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_territorial[] = $value->impacto_id;
                }                
            }
            
        }       
        
        $this->page_title = 'Información de Afectación causada por el Megaproyecto: '.$this->nombre_megaproyecto;
        if ($tipo_megaproyecto == Megaproyecto::TIPO_AGROINDUSTRIA){
        $this->page_module = Megaproyecto::AGROINDUSTRIA;  
        }
        elseif ($tipo_megaproyecto == Megaproyecto::TIPO_HIDROCARBURO) 
            {$this->page_module = Megaproyecto::HIDROCARBURO;}
            elseif ($tipo_megaproyecto == Megaproyecto::TIPO_INFRAESTRUCTURA) 
            {$this->page_module = Megaproyecto::INFRAESTRUCTURA;}
            elseif ($tipo_megaproyecto == Megaproyecto::TIPO_MINERIA) 
            {$this->page_module = Megaproyecto::MINERIA;}            
        $this->key_back = $key_back;    
        $this->key = $key;        
        $this->url_redir_back = 'afectacion/megaproyectos/ver/'.$key_back.'/'.$tipo_megaproyecto.'/3/1/';
        
        $obj_afectacion_territorio = new AfectacionTerritorio();        
        
    }     

     /**
     * Método para agregar
     */
    public function agregar_desarrollo_normativo($megaproyecto_id, $megaproyecto_nombre, $tipo_megaproyecto, $key_back) {
         
        $this->megaproyecto_id = $megaproyecto_id;                    
        $this->url_redir_back = 'afectacion/megaproyectos/editar/'.$key_back.'/'.$tipo_megaproyecto.'/4/1/';
        $this->page_title = 'Agregar Desarrollo Normativo al Megaproyecto: '.$megaproyecto_nombre;
               
        if(Input::hasPost('desarrollo_normativo')) {            
                       
            $desarrollo_normativo_obj = new DesarrolloNormativo();
            $desarrollo_normativo_obj = DesarrolloNormativo::setDesarrolloNormativo('create', Input::post('desarrollo_normativo'), array('megaproyecto_id'=>$megaproyecto_id, 'estado'=>DesarrolloNormativo::ACTIVO));
            if($desarrollo_normativo_obj)
            {                       
              $desarrollo_normativo_id = $desarrollo_normativo_obj->id;              
              Fuente::setFuente('create', Input::post('fuente'), 'desarrollo_normativo', $desarrollo_normativo_id);
                
              Flash::valid('¡El desarrollo normativo se ha registrado correctamente!');
              return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/4/1/');
                
            }  
           }            
    }

    public function editar_desarrollo_normativo($key, $tipo_megaproyecto, $key_back) { 
             
        if(!$id = Security::getKey($key, 'upd_desarrollo_normativo', 'int')) {
            return Redirect::toAction('listar/');
        }   
        
        $desarrollo_normativo = new DesarrolloNormativo();
        if(!$desarrollo_normativo->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del desarrollo_normativo');
            return Redirect::toAction('listar');
        }   
               
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('desarrollo_normativo', $desarrollo_normativo->id);
            
        $this->desarrollo_normativo = $desarrollo_normativo;
        
        $this->page_title = 'Actualizar Desarrollo Normativo del Megaproyecto: '.$desarrollo_normativo->getMegaproyecto()->nombre;        
        $this->key_back = $key_back;    
        $this->key = $key;      
        $this->url_redir_back = 'afectacion/megaproyectos/editar/'.$key_back.'/'.$tipo_megaproyecto.'/4/1/';
        
        if(Input::hasPost('desarrollo_normativo')) 
        {            
            if(DesarrolloNormativo::setDesarrolloNormativo('update', Input::post('desarrollo_normativo'), array('id'=>$id)))
            {                
                Fuente::setFuente('update', Input::post('fuente'), 'desarrollo_normativo', $id);          
                Flash::valid('El desarrollo normativo se ha actualizado correctamente!');
                return Redirect::to($this->url_redir_back);
            }            
        }    
    }

    public function ver_desarrollo_normativo($key, $tipo_megaproyecto, $key_back) { 
       
        if(!$id = Security::getKey($key, 'show_desarrollo_normativo', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $DesarrolloNormativo = new DesarrolloNormativo();        
        if(!$DesarrolloNormativo->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del Desarrollo Normativo');
            return Redirect::toAction("ver/$key_back/4/");
        }   
        
        $this->desarrollo_normativo = $DesarrolloNormativo; 
        $this->page_title = 'Información Desarrollo Normativo del Megaproyecto: '.$DesarrolloNormativo->getMegaproyecto()->nombre;
        
                 
        $this->key_back = $key_back;    
        $this->key = $key;   
        $this->url_redir_back = 'afectacion/megaproyectos/ver/'.$key_back.'/'.$tipo_megaproyecto.'/4/1/';
        
    }

    /**
     * Método para agregar
     */
    public function agregar_politica_publica($megaproyecto_id, $megaproyecto_nombre, $tipo_megaproyecto, $key_back) {
         
        $this->megaproyecto_id = $megaproyecto_id;                    
        $this->url_redir_back = 'afectacion/megaproyectos/editar/'.$key_back.'/'.$tipo_megaproyecto.'/5/1/';
        $this->page_title = 'Agregar Política Pública al Megaproyecto: '.$megaproyecto_nombre;
               
        if(Input::hasPost('politica_publica')) {            
                       
            $politica_publica_obj = new PoliticaPublica();
            $politica_publica_obj = PoliticaPublica::setPoliticaPublica('create', Input::post('politica_publica'), array('megaproyecto_id'=>$megaproyecto_id, 'estado'=>PoliticaPublica::ACTIVO));
            if($politica_publica_obj)
            {                       
              $politica_publica_id = $politica_publica_obj->id;              
              Fuente::setFuente('create', Input::post('fuente'), 'politica_publica', $politica_publica_id);
                
              Flash::valid('¡La Política Pública se ha registrado correctamente!');
              return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/5/1/');                
            }  
           }            
    }

    public function editar_politica_publica($key, $tipo_megaproyecto, $key_back) { 
             
        if(!$id = Security::getKey($key, 'upd_politica_publica', 'int')) {
            return Redirect::toAction('listar/');
        }   
        
        $politica_publica = new PoliticaPublica();
        if(!$politica_publica->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la Política Pública');
            return Redirect::toAction('listar');
        }   
               
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('politica_publica', $politica_publica->id);
            
        $this->politica_publica = $politica_publica;
        
        $this->page_title = 'Actualizar Política Pública del Megaproyecto: '.$politica_publica->getMegaproyecto()->nombre;        
        $this->key_back = $key_back;    
        $this->key = $key;      
        $this->url_redir_back = 'afectacion/megaproyectos/editar/'.$key_back.'/'.$tipo_megaproyecto.'/5/1/';
        
        if(Input::hasPost('politica_publica')) 
        {            
            if(PoliticaPublica::setPoliticaPublica('update', Input::post('politica_publica'), array('id'=>$id)))
            {                
                Fuente::setFuente('update', Input::post('fuente'), 'politica_publica', $id);          
                Flash::valid('La Política Pública se ha actualizado correctamente!');
                return Redirect::to($this->url_redir_back);
            }            
        }    
    }

     /**
     * Método para eliminar
     */
    public function eliminar_politica_publica($key, $tipo_megaproyecto, $key_back, $nombre_empleo, $nombre_megaproyecto) {      
        
        if(!$id = Security::getKey($key, 'del_politica_publica', 'int')) {
            return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/5/1/');
        }        
        
        $PoliticaPublica = new PoliticaPublica();
                    
        try {
            if($PoliticaPublica->delete($id)) {
                Flash::valid("La Política Pública $nombre_empleo se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO la Política Pública $nombre_empleo, pertenecia al megaproyecto $nombre_megaproyecto.");
            } else {
                Flash::warning('Lo sentimos, pero esta Política Pública no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Esta Política Pública no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/5/1/');
    }
    
    public function agregar_accion_seguimiento_control($megaproyecto_id, $megaproyecto_nombre, $tipo_megaproyecto, $key_back)
    { 
        $obj_accion_seguimiento_control = new AccionSeguimientoControl();
        if(Input::hasPost('accion_seguimiento_control')) 
        {        
            $obj_accion_seguimiento_control = AccionSeguimientoControl::setAccionSeguimientoControl('create', Input::post('accion_seguimiento_control'), $megaproyecto_nombre, array('estado'=>  AccionSeguimientoControl::ACTIVO));
            $accion_seguimiento_control_id = $obj_accion_seguimiento_control->id;            
            if($obj_accion_seguimiento_control)
            {
                Flash::valid('La Acción seguimiento control se ha registrado correctamente!');                
            }            
            
            return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/6/1/');
        }
        
        $this->megaproyecto_id = $megaproyecto_id;
        $this->page_title = 'Agregar Acción seguimiento control al Megaproyecto: '.$megaproyecto_nombre;
                    
        $this->url_redir_back = 'afectacion/megaproyectos/editar/'.$key_back.'/'.$tipo_megaproyecto.'/6/1/';
    }
    
    public function editar_accion_seguimiento_control($key, $tipo_megaproyecto, $key_back) { 
       
        if(!$id = Security::getKey($key, 'upd_accion_seguimiento_control', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $obj_accion_seguimiento_control = new AccionSeguimientoControl();        
        if(!$obj_accion_seguimiento_control->getAccionSeguimientoControlById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la accion seguimiento control');
            return Redirect::toAction('ver/'.$key_back.'/4/');
        }   
        
        $this->accion_seguimiento_control = $obj_accion_seguimiento_control; 
        $this->page_title = 'Actualizar Acción de seguimiento control del Megaproyecto: '.$obj_accion_seguimiento_control->megaproyecto;
       
        if(Input::hasPost('accion_seguimiento_control')) 
        {        
            $obj_accion_seguimiento_control = AccionSeguimientoControl::setAccionSeguimientoControl('update', Input::post('accion_seguimiento_control'), $obj_accion_seguimiento_control->megaproyecto, array('estado'=> AccionSeguimientoControl::ACTIVO));
            
            if($obj_accion_seguimiento_control)
            {
                Flash::valid('La accion seguimiento control se ha actualizado correctamente!');
            }
            return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/6/1/');
           
        }
            
        $this->key_back = $key_back;    
        $this->key = $key;      
        $this->url_redir_back = 'afectacion/megaproyectos/editar/'.$key_back.'/'.$tipo_megaproyecto.'/6/1/';
    }
    
     public function ver_accion_seguimiento_control($key, $tipo_megaproyecto, $key_back) { 
       
        if(!$id = Security::getKey($key, 'show_accion_seguimiento_control', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $obj_accion_seguimiento_control = new AccionSeguimientoControl();        
        if(!$obj_accion_seguimiento_control->getAccionSeguimientoControlById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la accion seguimiento control');
            return Redirect::toAction('ver/'.$key_back.'/4/');
        }   
        
        $this->accion_seguimiento_control = $obj_accion_seguimiento_control; 
        $this->page_title = 'Información de la Acción de seguimiento control del Megaproyecto: '.$obj_accion_seguimiento_control->megaproyecto;
       
        $this->key_back = $key_back;    
        $this->key = $key;   
        $this->url_redir_back = 'afectacion/megaproyectos/ver/'.$key_back.'/'.$tipo_megaproyecto.'/4/1/';
        
    }
    
     /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key, $clase_megaproyecto) {
        if(!$id = Security::getKey($key, $tipo.'_megaproyecto', 'int')) {
            return Redirect::toAction('listar/'.$clase_megaproyecto);
        }               
        $megaproyecto = new Megaproyecto();
        if(!$megaproyecto->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del megaproyecto');            
        } else {
            if($tipo=='inactivar' && $megaproyecto->estado == Megaproyecto::INACTIVO) {
                Flash::info('El megaproyecto ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $megaproyecto->estado == Megaproyecto::ACTIVO) {
                Flash::info('El megaproyecto ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? Megaproyecto::INACTIVO : Megaproyecto::ACTIVO;
                if(Megaproyecto::setMegaproyecto('update', $megaproyecto->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==Megaproyecto::ACTIVO) ? Flash::valid('El megaproyecto se ha reactivado correctamente!') : Flash::valid('El megaproyecto se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar/'.$clase_megaproyecto);
    }
    
     /**
     * Método para eliminar
     */
    public function eliminar($nombre_megaproyecto, $key, $afectacion_id) {      
        
        $url_redir_back = Session::get('url_back');
        if(!$id = Security::getKey($key, 'del_megaproyecto', 'int')) {
            return Redirect::to($url_redir_back);
        }        
        
        $afectacion = new Afectacion();
                    
        try {
            if($afectacion->delete($afectacion_id)) {
                Flash::valid("El megaproyecto $nombre_megaproyecto se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO el megaproyecto $nombre_megaproyecto.");
            } else {
                Flash::warning('Lo sentimos, pero este megaproyecto no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este megaproyecto no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::to($url_redir_back);
    }
    
     /**
     * Método para eliminar
     */
    public function eliminar_empleo($key, $tipo_megaproyecto, $key_back, $nombre_empleo, $nombre_megaproyecto) {      
        
        if(!$id = Security::getKey($key, 'del_empleo', 'int')) {
            return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/1/');
        }        
        
        $empleo = new Empleo();
                    
        try {
            if($empleo->delete($id)) {
                Flash::valid("El empleo $nombre_empleo se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO el empleo $nombre_empleo, pertenecia al megaproyecto $nombre_megaproyecto.");
            } else {
                Flash::warning('Lo sentimos, pero este empleo no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este empleo no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/1/');
    }


     /**
     * Método para eliminar
     */
    public function eliminar_desarrollo_normativo($key, $tipo_megaproyecto, $key_back, $nombre_empleo, $nombre_megaproyecto) {      
        
        if(!$id = Security::getKey($key, 'del_desarrollo_normativo', 'int')) {
            return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/4/1/');
        }        
        
        $DesarrolloNormativo = new DesarrolloNormativo();
                    
        try {
            if($DesarrolloNormativo->delete($id)) {
                Flash::valid("El Desarrollo Normativo $nombre_empleo se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO el Desarrollo Normativo $nombre_empleo, pertenecia al megaproyecto $nombre_megaproyecto.");
            } else {
                Flash::warning('Lo sentimos, pero este Desarrollo Normativo no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este Desarrollo Normativo no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/4/1/');
    }
    
     /**
     * Método para eliminar
     */
    public function eliminar_subsidio($key, $tipo_megaproyecto, $key_back, $nombre_subsidio, $nombre_megaproyecto) {      
        
        if(!$id = Security::getKey($key, 'del_subsidio', 'int')) {
            return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/1/');
        }        
        
        $subsidio = new Subsidio();
                    
        try {
            if($subsidio->delete($id)) {
                Flash::valid("El subsidio $nombre_subsidio se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO el subsidio $nombre_subsidio, pertenecia al megaproyecto $nombre_megaproyecto.");
            } else {
                Flash::warning('Lo sentimos, pero este subsidio no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este subsidio no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/3/');
    }
    
     /**
     * Método para eliminar
     */
    public function eliminar_programa_social($key, $tipo_megaproyecto, $key_back, $nombre_programa_social, $nombre_megaproyecto) {      
        
        if(!$id = Security::getKey($key, 'del_programa_social', 'int')) {
            return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/1/');
        }        
        
        $programa_social = new ProgramaSocial();
                    
        try {
            if($programa_social->delete($id)) {
                Flash::valid("El programa social $nombre_programa_social se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO el programa social $nombre_programa_social, pertenecia al megaproyecto $nombre_megaproyecto.");
            } else {
                Flash::warning('Lo sentimos, pero este programa social no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este programa social no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('editar/'.$key_back.'/'.$tipo_megaproyecto.'/2/2/');
    }
}
