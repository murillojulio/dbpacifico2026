<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de las politicas publicas
 *
 * @category    
 * @package     Controllers  
 */
Load::models('afectacion/area_natural_protegida', 
'global/fuente', 
'afectacion/ubicacion', 
        'afectacion/afectacion', 
        'observatorio/departamento', 
        'observatorio/municipio',
        'observatorio/territorio', 
        'opcion/tipo_area_natural_protegida',
        'afectacion/afectacion_area_natural_protegida', 
        'opcion/tipo_afectacion_area_natural_protegida',
        'afectacion/conflicto_uso', 
        'util/currency', 
        'opcion/dano',
        'opcion/tipo_dano',
        'afectacion/afectacion_dano_territorio',
        'observatorio/subregion');

class AreaNaturalProtegidaController extends BackendController {
    
     /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Area Natural Protegida';
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
    public function listar($order='order.area_natural_protegida.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $area_natural_protegida = new AreaNaturalProtegida();
        $this->area_natural_protegidas = $area_natural_protegida->getListadoAreaNaturalProtegida('todos', $order, $page);        
        $this->order = $order;   
        
        $this->page_module = 'Áreas Naturales Protegidas';
        $this->page_title = 'Listado';    
        
        Session::set('back', 'afectacion/area_natural_protegida/listar/'.$order.'/'.$page.'/');
    }
    
     /**
     * Método para buscar
     * 
     * @param type $field Nombre del campo a buscar
     * @param type $value Valor del campo
     * @param type $order Método de ordenamiento
     * @param type $page Número de página
     */
    public function buscar_area_natural_protegida($field='nombre', $value='none', $order='order.id.asc', $page='page.1') {        
        $page       = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field      = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value      = (Input::hasPost('value')) ? Input::post('value') : $value;
        $area_natural_protegida     = new AreaNaturalProtegida();
        $area_natural_protegida    = $area_natural_protegida->getAjaxAreaNaturalProtegida($field, $value, $order, $page);
        if(empty($area_natural_protegida->items)) {
            Flash::info('No se han encontrado registros');
        }
        
        $this->area_natural_protegidas = $area_natural_protegida;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de cooperación en el sistema';   
        $this->page_module = 'Áreas Naturales Protegidas';
        
        Session::set('back', 'afectacion/area_natural_protegida/buscar_area_natural_protegida/'.$field.'/'.$value.'/'.$order.'/'.$page.'/');
    }
    
    /**
     * Método para agregar
     */
    public function agregar() {
               
        $this->page_module = 'Áreas Naturales Protegidas';
        $this->page_title = 'Agregar área natural protegida';
               
        if(Input::hasPost('area_natural_protegida')) {
            
            $afectacion_obj = Afectacion::setAfectacion('create', array('tipo_afectacion_id'=>'7'));
           if($afectacion_obj)
           {
            $post_area_natural_protegida = Input::post('area_natural_protegida');
            $post_area_natural_protegida['area'] = Currency::comaApunto($post_area_natural_protegida['area']);
            
            $ubicacion_obj = Ubicacion::setUbicacion('create', Input::post('caso'), array('afectacion_id'=>$afectacion_obj->id));
            $area_natural_protegida_obj = new AreaNaturalProtegida();
            $area_natural_protegida_obj = AreaNaturalProtegida::setAreaNaturalProtegida('create', $post_area_natural_protegida, array('afectacion_id'=>$afectacion_obj->id, 'estado'=>AreaNaturalProtegida::ACTIVO));
            if($area_natural_protegida_obj)
            {                       
              $area_natural_protegida_id = $area_natural_protegida_obj->id;    
       
              Fuente::setFuente('create', Input::post('fuente'), 'area_natural_protegida', $area_natural_protegida_id);
                
              Flash::valid('¡El área natural protegida se ha registrado correctamente!');
              $key_upd = Security::setKey($area_natural_protegida_obj->id, 'upd_area_natural_protegida');
              return Redirect::toAction('editar/'.$key_upd.'/2/');                
            }  
           }                   
        }        
    }
    
    
    
     /**
     * Método para ver
     */
    public function ver($key, $tab = '1') { 
             
        if(!$id = Security::getKey($key, 'show_area_natural_protegida', 'int')) {
            return Redirect::toAction('listar/');
        }  
        
        //Para saber que pestaña estara activa cuando visualice un cultivo ilicito
        $this->tab_1_active = '';
        $this->tab_2_active = '';
        $this->tab_3_active = '';
        $this->tab_4_active = '';
                
        if($tab == 1 || $tab == ''){ $this->tab_1_active = 'active'; }
        if($tab == 2){ $this->tab_2_active = 'active'; }
        if($tab == 3){ $this->tab_3_active = 'active'; }
        if($tab == 4){ $this->tab_4_active = 'active'; }
        
        
        $area_natural_protegida = new AreaNaturalProtegida();
        if(!$area_natural_protegida->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del area_natural_protegida');
            return Redirect::toAction('listar');
        }   
        
        $ubicaciones = $area_natural_protegida->getAfectacion()->getUbicaciones($area_natural_protegida->afectacion_id);
        $this->ubicaciones = $ubicaciones;
        $ubicacion = $ubicaciones[0];
        $this->ubicacion = $ubicacion;
                       
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('area_natural_protegida', $area_natural_protegida->id);
        //var_dump($this->fuentes);        die();  
        
        $this->nombre = $area_natural_protegida->nombre;
        $this->area_natural_protegida = $area_natural_protegida;
        
        $conflicto_uso = new ConflictoUso();           
        $this->conflicto_usos_cn = $conflicto_uso->getConflicto($area_natural_protegida->id, 'comunidad_negra')    ;
        $this->conflicto_usos_ci = $conflicto_uso->getConflicto($area_natural_protegida->id, 'indigena')    ;
        
        $afectacion_area_natural_protegida = $area_natural_protegida->getAfectacionAreaNaturalProtegida();
        $this->afectacion_area_natural_protegidas = $afectacion_area_natural_protegida;
                
        
        $this->page_module = 'Áreas Naturales Protegidas';
        $this->page_title = 'Información área natural protegida: '.$area_natural_protegida->nombre;
        $this->key = $key;
        $this->url_redir_back = Session::get('back');
        Session::set('key_back', $key);
                
    }
    
     
    /**
     * Método para editar
     */
    public function editar($key, $tab = '1') { 
             
        if(!$id = Security::getKey($key, 'upd_area_natural_protegida', 'int')) {
            return Redirect::toAction('listar/');
        }  
        
        if(Input::hasPost('area_natural_protegida')) {
            $post_area_natural_protegida = Input::post('area_natural_protegida');
            $post_area_natural_protegida['area'] = Currency::comaApunto($post_area_natural_protegida['area']);
            
            $conflicto_uso = new ConflictoUso();
            if($post_area_natural_protegida['territorio_afro'] == 'NO')
            {
                $post_area_natural_protegida['acuerdo_uso_manejo'] = '';
                $conflicto_uso->deleteConflicto($post_area_natural_protegida['id'], 'comunidad_negra');
            }
            if($post_area_natural_protegida['territorio_indigena'] == 'NO')
            {
                $post_area_natural_protegida['regimen_especial_manejo'] = '';
                $conflicto_uso->deleteConflicto($post_area_natural_protegida['id'], 'indigena');
            }
            if(AreaNaturalProtegida::setAreaNaturalProtegida('update', $post_area_natural_protegida, array('id'=>$id)))
            {         
              /* $afectacion_id = $post_area_natural_protegida['afectacion_id'];    
              $ubicacion_id = $post_area_natural_protegida['ubicacion_id'];   */  
              
              //Ubicacion::setUbicacion('update', Input::post('caso'), array('id'=>$ubicacion_id, 'afectacion_id'=>$afectacion_id));
              Fuente::setFuente('update', Input::post('fuente'), 'area_natural_protegida', $id);          
                Flash::valid('La área natural protegida se ha actualizado correctamente!');
                return Redirect::to(Session::get('back'));
            }            
        }
        
        //Para saber que pestaña estara activa cuando visualice un cultivo ilicito
        $this->tab_1_active = '';
        $this->tab_2_active = '';
        $this->tab_3_active = '';
        $this->tab_4_active = '';
                
        if($tab == 1 || $tab == ''){ $this->tab_1_active = 'active'; }
        if($tab == 2){ $this->tab_2_active = 'active'; }
        if($tab == 3){ $this->tab_3_active = 'active'; }
        if($tab == 4){ $this->tab_4_active = 'active'; }
        
        
        $area_natural_protegida = new AreaNaturalProtegida();
        if(!$area_natural_protegida->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del area_natural_protegida');
            return Redirect::toAction('listar');
        }   
        
        $ubicaciones = $area_natural_protegida->getAfectacion()->getUbicaciones($area_natural_protegida->afectacion_id);
        $this->ubicaciones = $ubicaciones;
        /* $ubicacion = $ubicaciones[0];
        $this->ubicacion = $ubicacion; */

        $AfectacionDanoTerritorio = new AfectacionDanoTerritorio();
        $this->AfectacionDanoTerritorio = $AfectacionDanoTerritorio->getDanoTerritorioByAfectacionId($area_natural_protegida->afectacion_id);

                       
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('area_natural_protegida', $area_natural_protegida->id);
        //var_dump($this->fuentes);        die();  
        
        $this->nombre = $area_natural_protegida->nombre;
        $this->area_natural_protegida = $area_natural_protegida;
        
        $conflicto_uso = new ConflictoUso();           
        $this->conflicto_usos_cn = $conflicto_uso->getConflicto($area_natural_protegida->id, 'comunidad_negra')    ;
        $this->conflicto_usos_ci = $conflicto_uso->getConflicto($area_natural_protegida->id, 'indigena')    ;
        
        $afectacion_area_natural_protegida = $area_natural_protegida->getAfectacionAreaNaturalProtegida();
        $this->afectacion_area_natural_protegidas = $afectacion_area_natural_protegida;
                
        
        $this->page_module = 'Áreas Naturales Protegidas';
        $this->page_title = 'Actualizar área natural protegida: '.$area_natural_protegida->nombre;
        $this->key = $key;
        $this->url_redir_back = Session::get('back');
        Session::set('key_back', $key);
        
         
        
        
    }
    
     /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_area_natural_protegida', 'int')) {
            return Redirect::toAction('listar/');
        }               
        $area_natural_protegida = new AreaNaturalProtegida();
        if(!$area_natural_protegida->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la cooperación');            
        } else {
            if($tipo=='inactivar' && $area_natural_protegida->estado == AreaNaturalProtegida::INACTIVO) {
                Flash::info('La cooperación ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $area_natural_protegida->estado == AreaNaturalProtegida::ACTIVO) {
                Flash::info('La cooperación ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? AreaNaturalProtegida::INACTIVO : AreaNaturalProtegida::ACTIVO;
                if(AreaNaturalProtegida::setAreaNaturalProtegida('update', $area_natural_protegida->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==AreaNaturalProtegida::ACTIVO) ? Flash::valid('La cooperación se ha reactivado correctamente!') : Flash::valid('La cooperación se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar/');
    }
    
    
    
    public function agregar_afectacion($area_natural_protegida_id, $nombre)
    { 
        $this->area_natural_protegida_id = $area_natural_protegida_id;
        $url_redir_back = 'afectacion/area_natural_protegida/editar/'.Session::get('key_back').'/3/';
        $this->url_redir_back = $url_redir_back;
        $this->page_title = 'Agregar afectación al área natural protegida: '.$nombre;
       
        if(Input::hasPost('afectacion_area_natural_protegida')) 
        {    
            $post_afectacion_area_natural_protegida = Input::post('afectacion_area_natural_protegida');
            $obj_afectacion_area_natural_protegida = new AfectacionAreaNaturalProtegida();
            $obj_afectacion_area_natural_protegida->area_natural_protegida_id = $area_natural_protegida_id;
            $obj_afectacion_area_natural_protegida->tipo_afectacion_area_natural_protegida_id = $post_afectacion_area_natural_protegida['tipo_afectacion_area_natural_protegida_id'];
            $obj_afectacion_area_natural_protegida->descripcion = $post_afectacion_area_natural_protegida['descripcion'];
            
            $obj_afectacion_area_natural_protegida->save();
            
            Flash::valid('La afectación al área natural protegida se ha registrado correctamente!');
            return Redirect::to($url_redir_back);
        }
        
       
    }
    
    
    public function editar_afectacion($id, $nombre)
    { 
        $obj_afectacion_area_natural_protegida = new AfectacionAreaNaturalProtegida();
        
        $this->afectacion_area_natural_protegida = $obj_afectacion_area_natural_protegida->find_first($id);
        
        $url_redir_back = 'afectacion/area_natural_protegida/editar/'.Session::get('key_back').'/3/';
        $this->url_redir_back = $url_redir_back;
        $this->page_title = 'Actualizar afectación al área natural protegida: '.$nombre;  
        $this->nombre = $nombre;
       
        if(Input::hasPost('afectacion_area_natural_protegida')) 
        {    
            AfectacionAreaNaturalProtegida::setAfectacionAreaNaturalProtegida('update', Input::post('afectacion_area_natural_protegida'), NULL, $nombre);           
            
            Flash::valid('La afectación al área natural protegida se ha actualizado correctamente!');
            return Redirect::to($url_redir_back);
        }
        
       
    }
    
      public function ver_afectacion($id, $nombre)
    { 
        $obj_afectacion_area_natural_protegida = new AfectacionAreaNaturalProtegida();
        
        $this->afectacion_area_natural_protegida = $obj_afectacion_area_natural_protegida->find_first($id);
        
        $url_redir_back = 'afectacion/area_natural_protegida/ver/'.Session::get('key_back').'/3/';
        $this->url_redir_back = $url_redir_back;
        $this->page_title = 'Informacion afectación al área natural protegida: '.$nombre;  
        $this->nombre = $nombre;      
      
    }
    
    /**
     * Método para eliminar
     */
    public function eliminar_afectacion($tipo_afectacion, $nombre_area_natural_protegida, $key) {      
                
        if(!$id = Security::getKey($key, 'del_afectacion_anp', 'int')) {
            return Redirect::to($url_redir_back);
        }        
        
        $afectacion_anp = new AfectacionAreaNaturalProtegida();
                    
        try {
            if($afectacion_anp->delete($id)) {
                Flash::valid("La afectacion por $tipo_afectacion se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO la afectación por $tipo_afectacion, que pertenecia al área natural protegida $nombre_area_natural_protegida.");
            } else {
                Flash::warning('Lo sentimos, pero esta afectación no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este afectación no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::to('afectacion/area_natural_protegida/editar/'.Session::get('key_back').'/3/');
    }
    
     /**
     * Método para eliminar
     */
    public function eliminar( $nombre_area_natural_protegida, $key, $afectacion_id) {      
        
        $url_redir_back = Session::get('url_back');
        if(!$id = Security::getKey($key, 'del_area_natural_protegida', 'int')) {
            return Redirect::to($url_redir_back);
        }        
        
        $afectacion = new Afectacion();
                    
        try {
            if($afectacion->delete($afectacion_id)) {
                Flash::valid("El área natural protegida $nombre_area_natural_protegida se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO el área natural protegida $nombre_area_natural_protegida.");
            } else {
                Flash::warning('Lo sentimos, pero este área natural protegida no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este área natural protegida no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::to($url_redir_back);
    }
    
    }
    
    

?>