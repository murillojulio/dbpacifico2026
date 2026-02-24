<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de las politicas publicas
 *
 * @category    
 * @package     Controllers  
 */
Load::models('afectacion/cooperacion',
'global/fuente', 
'afectacion/ubicacion', 
        'afectacion/afectacion', 
        'observatorio/departamento', 
        'observatorio/municipio',
        'observatorio/territorio', 
        'observatorio/subregion',
        'afectacion/cooperacion_municipio',
        'afectacion/cooperacion_operador_cooperacion', 
        'afectacion/cooperacion_tipo_proyecto_cooperacion',
        'afectacion/afectacion_dano_territorio',
        'opcion/dano',
        'opcion/tipo_dano');

class CooperacionController extends BackendController {
    
     /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Cooperación';
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
    public function listar($order='order.cooperacion.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $cooperacion = new Cooperacion();
        $this->cooperacions = $cooperacion->getListadoCooperacion('todos', $order, $page);        
        $this->order = $order;   
        
        $this->page_module = 'Cooperación';
        $this->page_title = 'Listado de cooperación';         
    }
    
     /**
     * Método para buscar
     * 
     * @param type $field Nombre del campo a buscar
     * @param type $value Valor del campo
     * @param type $order Método de ordenamiento
     * @param type $page Número de página
     */
    public function buscar_cooperacion($field='nombre', $value='none', $order='order.id.asc', $page='page.1') {        
        $page       = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field      = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value      = (Input::hasPost('value')) ? Input::post('value') : $value;
        $cooperacion     = new Cooperacion();
        $cooperacion    = $cooperacion->getAjaxCooperacion($field, $value, $order, $page);
        if(empty($cooperacion->items)) {
            Flash::info('No se han encontrado registros');
        }
        
        $this->cooperacions = $cooperacion;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de cooperación en el sistema';   
        $this->page_module = 'Cooperación';
    }
    
    /**
     * Método para agregar
     */
    public function agregar() {
               
        $this->page_module = 'Cooperación';
        $this->page_title = 'Agregar cooperación';
               
        if(Input::hasPost('cooperacion')) {
            
            $afectacion_obj = Afectacion::setAfectacion('create', array('tipo_afectacion_id'=>'5'));
           if($afectacion_obj)
           {
            $post_cooperacion = Input::post('cooperacion');
            $post_cooperacion['monto_inversion'] = str_replace(",", "", $post_cooperacion['monto_inversion']);
            //$ubicacion_obj = Ubicacion::setUbicacion('create', Input::post('caso'), array('afectacion_id'=>$afectacion_obj->id));
            $cooperacion_obj = new Cooperacion();
            $cooperacion_obj = Cooperacion::setCooperacion('create', $post_cooperacion, array('afectacion_id'=>$afectacion_obj->id, 'estado'=>Cooperacion::ACTIVO));
            if($cooperacion_obj)
            {                       
              $cooperacion_id = $cooperacion_obj->id;    
              $cooperacion_municipio = new CooperacionMunicipio();
              $cooperacion_municipio->guardar(Input::post('municipio'), $cooperacion_id);
              
              $cooperacion_operador_cooperacion = new CooperacionOperadorCooperacion();
              $cooperacion_operador_cooperacion->guardar(Input::post('operador'), $cooperacion_id);
              
              $cooperacion_tipo_proyecto_cooperacion = new CooperacionTipoProyectoCooperacion();
              $cooperacion_tipo_proyecto_cooperacion->guardar(Input::post('tipo_proyecto'), $cooperacion_id);
              
              Fuente::setFuente('create', Input::post('fuente'), 'cooperacion', $cooperacion_id);
                
              Flash::valid('¡La cooperación se ha registrado correctamente!');
              return Redirect::toAction('listar/');
                
            }  
           }
                   
        }        
    }
    
     /**
     * Método para agregar
     */
    public function agregar_desde_modal()
    {
        $afectacion_obj = Afectacion::setAfectacion('create', array('tipo_afectacion_id' => '5'));
        if ($afectacion_obj) {
            $Cooperacion = new Cooperacion();
            $Cooperacion->afectacion_id = $afectacion_obj->id;
            $Cooperacion->nombre_clase_cooperacion = Input::post('nombre_clase_cooperacion');
            $Cooperacion->clase_cooperacion_id = Input::post('clase_cooperacion_id');
            $Cooperacion->tipo_cooperacion_id = Input::post('tipo_cooperacion_id');     
            if($Cooperacion->create()){
                $Fuente = new Fuente();
                $Fuente->fecha = date('Y-m-d', strtotime(Input::post('fuente_fecha')));
                $Fuente->nombre = Input::post('fuente_descripcion');
                $Fuente->tabla ='cooperacion';
                $Fuente->tabla_identi = $Cooperacion->id;    
                $Fuente->create();

                $key_upd = Security::setKey($Cooperacion->id, 'upd_cooperacion');
                $array = array('key_upd'=>$key_upd);
                $this->data = $array;
                View::template(null);
            }
        }       
    }
    
    
        /**
     * Método para ver
     */
    public function ver($key) { 
             
        if(!$id = Security::getKey($key, 'show_cooperacion', 'int')) {
            return Redirect::toAction('listar/');
        }  
        
                
        $cooperacion = new Cooperacion();
        if(!$cooperacion->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del cooperacion');
            return Redirect::toAction('listar');
        }   
        
        
        $tipo_proyecto = $cooperacion->getCooperacionTipoProyectoCooperacion();               
        foreach ($tipo_proyecto as $value)
        {            
             $this->array_tipo_proyecto[] = $value->tipo_proyecto_cooperacion_id;             
        } 
        
        $operador = $cooperacion->getCooperacionOperadorCooperacion();               
        foreach ($operador as $value)
        {            
             $this->array_operador[] = $value->operador_cooperacion_id;             
        } 
        
        $municipios = $cooperacion->getCooperacionMunicipio();               
        foreach ($municipios as $value)
        {            
             $this->array_municipio[] = $value->municipio_id;             
        } 
                
               
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('cooperacion', $cooperacion->id);
        //var_dump($this->fuentes);        die(); 
              
        $this->cooperacion = $cooperacion;
        $this->page_module = 'Cooperación';
        $this->page_title = 'Información de la cooperación: '.$cooperacion->nombre;        
        $this->url_redir_back = 'afectacion/cooperacion/listar/';
        
        
    }
    
     
    /**
     * Método para editar
     */
    public function editar($key) { 
             
        if(!$id = Security::getKey($key, 'upd_cooperacion', 'int')) {
            return Redirect::toAction('listar/');
        }  
 
        
        $cooperacion = new Cooperacion();
        if(!$cooperacion->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del cooperacion');
            return Redirect::toAction('listar');
        }   
        
        $ubicaciones = $cooperacion->getAfectacion()->getUbicaciones($cooperacion->afectacion_id);
        $this->ubicaciones = $ubicaciones;

        $AfectacionDanoTerritorio = new AfectacionDanoTerritorio();
        $this->AfectacionDanoTerritorio = $AfectacionDanoTerritorio->getDanoTerritorioByAfectacionId($cooperacion->afectacion_id);

                       
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('cooperacion', $cooperacion->id);
        //var_dump($this->fuentes);        die();  
        
        $cooperacion->nombre = $cooperacion->nombre_clase_cooperacion;
        $this->cooperacion = $cooperacion;

        
        $tipo_proyecto = $cooperacion->getCooperacionTipoProyectoCooperacion();               
        foreach ($tipo_proyecto as $value)
        {            
             $this->array_tipo_proyecto[] = $value->tipo_proyecto_cooperacion_id;             
        } 
        
        $operador = $cooperacion->getCooperacionOperadorCooperacion();               
        foreach ($operador as $value)
        {            
             $this->array_operador[] = $value->operador_cooperacion_id;             
        } 
             
        $this->page_module = 'Cooperación';
        $this->page_title = 'Actualizar cooperación: '.$cooperacion->nombre_clase_cooperacion;
        $this->key = $key;
        $this->url_redir_back = 'afectacion/cooperacion/listar/';
        
         if(Input::hasPost('cooperacion')) {
            $post_cooperacion = Input::post('cooperacion');
            $post_cooperacion['monto_inversion'] = str_replace(",", "", $post_cooperacion['monto_inversion']);
            
            if(Cooperacion::setCooperacion('update', $post_cooperacion, array('id'=>$id)))
            {         
              $cooperacion_id = $post_cooperacion['id'];    
                    
              $cooperacion_operador_cooperacion = new CooperacionOperadorCooperacion();
              $cooperacion_operador_cooperacion->guardar(Input::post('operador'), $cooperacion_id);
              
              $cooperacion_tipo_proyecto_cooperacion = new CooperacionTipoProyectoCooperacion();
              $cooperacion_tipo_proyecto_cooperacion->guardar(Input::post('tipo_proyecto'), $cooperacion_id);
                
              Fuente::setFuente('update', Input::post('fuente'), 'cooperacion', $id);          
                Flash::valid('La cooperación se ha actualizado correctamente!');
                return Redirect::toAction('listar/');
            }            
        }
        
        
    }
    
     /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_cooperacion', 'int')) {
            return Redirect::toAction('listar/');
        }               
        $cooperacion = new Cooperacion();
        if(!$cooperacion->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la cooperación');            
        } else {
            if($tipo=='inactivar' && $cooperacion->estado == Cooperacion::INACTIVO) {
                Flash::info('La cooperación ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $cooperacion->estado == Cooperacion::ACTIVO) {
                Flash::info('La cooperación ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? Cooperacion::INACTIVO : Cooperacion::ACTIVO;
                if(Cooperacion::setCooperacion('update', $cooperacion->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==Cooperacion::ACTIVO) ? Flash::valid('La cooperación se ha reactivado correctamente!') : Flash::valid('La cooperación se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar/');
    }
    
     /**
     * Método para eliminar
     */
    public function eliminar( $nombre_cooperacion, $key, $afectacion_id) {      
        
        $url_redir_back = Session::get('url_back');
        if(!$id = Security::getKey($key, 'del_cooperacion', 'int')) {
            return Redirect::to($url_redir_back);
        }        
        
        $afectacion = new Afectacion();
                    
        try {
            if($afectacion->delete($afectacion_id)) {
                Flash::valid("La cooperación $nombre_cooperacion se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO la cooperación $nombre_cooperacion.");
            } else {
                Flash::warning('Lo sentimos, pero esta cooperación no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Esta cooperación no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::to($url_redir_back);
    }
    
    }
    
    

?>