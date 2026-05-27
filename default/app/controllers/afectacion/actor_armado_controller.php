<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de las politicas publicas
 *Julio Murillo
 * @category    
 * @package     Controllers  
 */
Load::models('global/fuente', 
'opcion/impacto', 
'observatorio/territorio', 
'afectacion/ubicacion',
'observatorio/departamento',
'observatorio/municipio',
'afectacion/afectacion',
'observatorio/subregion',
'afectacion/afectacion_dano_territorio',
        'opcion/tipo_cultivo', 
        'util/currency', 
        'opcion/presunto_responsable',
        'afectacion/actor_armado',
        'opcion/dano',
        'opcion/tipo_dano');

class ActorArmadoController extends BackendController {
    
     /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Actores Armados';
        $this->page_title = 'Listado';   
    }
    
    /**
     * Método principal
     */
    public function index() {
    Redirect::toAction('listar');
    }

    /**
     * Método para listar
     */
    public function listar($order = 'order.actor_armado.asc', $page = 'page.1')
    {
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $actor_armado = new ActorArmado();
        $this->actor_armados = $actor_armado->getListadoActorArmado('todos', $order, $page);
        $this->order = $order;

        $this->page_module = 'Actores Armado';
        $this->page_title = 'Listado de Actores Armados';
    }
    
       /**
     * Método para listar los territorios colectivos de comunidades negras
     */
    public function listar_territorio_cn($order='order.territorio.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $territorios = new Territorio();
        $this->territorios = $territorios->getListadoTerritorio('comunidad_negra', $order, $page);        
        $this->order = $order;      
        $this->page = $page;
        $this->page_title = Territorio::TERRITORIO_COMUNIDAD_NEGRA;
        $this->page_module = 'Actores Armados';
        Session::set('back', 'afectacion/actor_armado/listar_territorio_cn/'.$order.'/'.$page.'/');
    }
    
    /**
     * Método para listar los territorios colectivos indigenas
     */
    public function listar_territorio_ci($order='order.territorio.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $territorios = new Territorio();
        $this->territorios = $territorios->getListadoTerritorio('indigena', $order, $page);        
        $this->order = $order;  
        $this->page = $page;
        $this->page_title = Territorio::TERRITORIO_INDIGENA;
        $this->page_module = 'Actores Armados';
        Session::set('back', 'afectacion/actor_armado/listar_territorio_ci/'.$order.'/'.$page.'/');
    }
    

     /**
     * Método para buscar
     * 
     * @param type $field Nombre del campo a buscar
     * @param type $value Valor del campo
     * @param type $order Método de ordenamiento
     * @param type $page Número de página
     */
    public function buscar_territorio_cn($field='nombre', $value='none', $order='order.id.asc', $page='page.1') {        
        $page       = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field      = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value      = (Input::hasPost('value')) ? Input::post('value') : $value;
        //$tipo       = (Input::hasPost('tipo')) ? Input::post('tipo') : $tipo;
        
        $territorio     = new Territorio();
        $territorios    = $territorio->getAjaxTerritorio($field, $value, $order, $page, $tipo='comunidad_negra');
        if(empty($territorios->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->territorios  = $territorios;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de territorios en el sistema';  
        $this->page_module  = 'Actores Armados';
        Session::set('back', 'afectacion/actor_armado/buscar_territorio_cn/'.$field.'/'.$value.'/'.$order.'/'.$page.'/');
    }
    
    /**
     * Método para buscar
     * 
     * @param type $field Nombre del campo a buscar
     * @param type $value Valor del campo
     * @param type $order Método de ordenamiento
     * @param type $page Número de página
     */
    public function buscar_territorio_ci($field='nombre', $value='none', $order='order.id.asc', $page='page.1') {        
        $page       = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field      = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value      = (Input::hasPost('value')) ? Input::post('value') : $value;
                
        $territorio     = new Territorio();
        $territorios    = $territorio->getAjaxTerritorio($field, $value, $order, $page, $tipo='indigena');
        if(empty($territorios->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->territorios  = $territorios;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de territorios en el sistema';  
        $this->page_module  = 'Actores Armados';
        Session::set('back', 'afectacion/actor_armado/buscar_territorio_ci/'.$field.'/'.$value.'/'.$order.'/'.$page.'/');
    }
    
    
    /**
     * Método para editar
     */
    public function ver($key) { 
             
        if(!$id = Security::getKey($key, 'show_actor_armado', 'int')) {
            return Redirect::toAction('listar/');
        }  
        
        $obj_territorio = new Territorio();        
        if(!$obj_territorio->find($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del territorio');
            return Redirect::toAction('listar_cn');
        } 
        $this->obj_territorio = $obj_territorio;
        $actor_armado = $obj_territorio->getActorArmado();
        foreach ($actor_armado as $value)
        {            
             $this->array_actor_armado[] = $value->presunto_responsable_id;             
        } 
      
        $this->page_module = 'Actores Armados';
        $this->page_title = 'Información Actores Armados del territorio: '.$obj_territorio->nombre;
        $this->key = $key;
        $this->url_redir_back = Session::get('back');
    }
    
    
     
   /**
     * Método para editar
     */
    public function editar($key, $tab = '1', $sub_tab = '')
    {

        if (!$id = Security::getKey($key, 'upd_actor_armado', 'int')) {
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


        $actor_armado = new ActorArmado();
        if (!$actor_armado->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del actor armado');
            return Redirect::toAction('listar');
        }

        $ubicaciones = $actor_armado->getAfectacion()->getUbicaciones($actor_armado->afectacion_id);
        $this->ubicaciones = $ubicaciones;
        
        $AfectacionDanoTerritorio = new AfectacionDanoTerritorio();
        $this->AfectacionDanoTerritorio = $AfectacionDanoTerritorio->getDanoTerritorioByAfectacionId($actor_armado->afectacion_id);

        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('actor_armado', $actor_armado->id);

        $this->actor_armado = $actor_armado;
        $this->nombre_presunto_responsable = $actor_armado->getPresuntoResponsable()->nombre;

        $this->page_module = 'Actor Armado';
        $this->page_title = 'Actualizar Actor Armado de ' . $this->nombre_presunto_responsable;
        $this->key = $key;
        $this->url_redir_back = 'afectacion/actor_armado/listar/';

        if (Input::hasPost('actor_armado')) {
            $post_actor_armado = Input::post('actor_armado');
           
            if (ActorArmado::setActorArmado('update', $post_actor_armado, array('id' => $id))) {
                
                Fuente::setFuente('update', Input::post('fuente'), 'actor_armado', $id);
                Flash::valid('El Actor Armado se ha actualizado correctamente!');
                return Redirect::toAction("editar/$key");
            }
        }
    }
    
     /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_actor_armado', 'int')) {
            return Redirect::toAction('listar/');
        }               
        $actor_armado = new ActorArmado();
        if(!$actor_armado->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del Actores Armados');            
        } else {
            if($tipo=='inactivar' && $actor_armado->estado == ActorArmado::INACTIVO) {
                Flash::info('El Actores Armados ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $actor_armado->estado == ActorArmado::ACTIVO) {
                Flash::info('El Actores Armados ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? ActorArmado::INACTIVO : ActorArmado::ACTIVO;
                if(ActorArmado::setActorArmado('update', $actor_armado->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==ActorArmado::ACTIVO) ? Flash::valid('El Actores Armados se ha reactivado correctamente!') : Flash::valid('El Actores Armados se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar/');
    }

    
    /**
     * Método para agregar
     */
    public function agregar_desde_modal()
    {
        $afectacion_obj = Afectacion::setAfectacion('create', array('tipo_afectacion_id' => '6'));
        if ($afectacion_obj) {
            $ActorArmado = new ActorArmado();
            $ActorArmado->afectacion_id = $afectacion_obj->id;
            $ActorArmado->presunto_responsable_id = Input::post('presunto_responsable_id');
            $ActorArmado->descripcion = Input::post('descripcion');
            $ActorArmado->fecha_llegada = Input::post('fecha_llegada');
            $ActorArmado->fecha_salida = Input::post('fecha_salida');
            if($ActorArmado->fecha_llegada !== ''){$ActorArmado->fecha_llegada = date('Y-m-d', strtotime($ActorArmado->fecha_llegada)); }  
            if($ActorArmado->fecha_salida !== ''){$ActorArmado->fecha_salida = date('Y-m-d', strtotime($ActorArmado->fecha_salida)); } 

            if($ActorArmado->create()){
                $Fuente = new Fuente();
                $Fuente->fecha = date('Y-m-d', strtotime(Input::post('fuente_fecha')));
                $Fuente->nombre = Input::post('fuente_descripcion');
                $Fuente->tabla ='actor_armado';
                $Fuente->tabla_identi = $ActorArmado->id;    
                $Fuente->create();

                $key_upd = Security::setKey($ActorArmado->id, 'upd_actor_armado');
                $array = array('key_upd'=>$key_upd);
                $this->data = $array;
                View::template(null);
            }
        }       
    }
    
    
    
    
    }
?>