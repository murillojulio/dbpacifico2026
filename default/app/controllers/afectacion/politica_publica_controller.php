<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de las politicas publicas
 *
 * @category    
 * @package     Controllers  
 */
Load::models('afectacion/politica_publica', 'global/fuente', 'afectacion/ubicacion', 
        'afectacion/afectacion', 'observatorio/departamento', 'observatorio/subregion',
        'observatorio/municipio', 'observatorio/territorio');

class PoliticaPublicaController extends BackendController {
    
     /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Políticas Públicas';
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
    public function listar($order='order.politica_publica.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $politica_publica = new PoliticaPublica();
        $this->politica_publicas = $politica_publica->getListadoPoliticaPublica('todos', $order, $page);        
        $this->order = $order;   
        
        $this->page_module = 'Políticas Públicas';
        $this->page_title = 'Listado de políticas públicas';  
        Session::set('url_back', 'afectacion/politica_publica/listar/'.$order.'/page.'.$page.'/');
    }
    
     /**
     * Método para buscar
     * 
     * @param type $field Nombre del campo a buscar
     * @param type $value Valor del campo
     * @param type $order Método de ordenamiento
     * @param type $page Número de página
     */
    public function buscar_politica_publica($field='nombre', $value='none', $order='order.id.asc', $page='page.1') {        
        $page       = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field      = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value      = (Input::hasPost('value')) ? Input::post('value') : $value;
        $politica_publica     = new PoliticaPublica();
        $politica_publica    = $politica_publica->getAjaxPoliticaPublica($field, $value, $order, $page);
        if(empty($politica_publica->items)) {
            Flash::info('No se han encontrado registros');
        }
        
        $this->politica_publicas = $politica_publica;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de políticas públicas en el sistema';   
        $this->page_module = 'Políticas Públicas';
        Session::set('url_back', "afectacion/politica_publica/buscar_politica_publica/$field/$value/$order/page.$page/");
    }
    
    /**
     * Método para agregar
     */
    public function agregar() {
               
        $this->page_module = 'Políticas Públicas';
        $this->page_title = 'Agregar política pública';
               
        if(Input::hasPost('politica_publica')) {
            
            $afectacion_obj = Afectacion::setAfectacion('create', array('tipo_afectacion_id'=>'1'));
           if($afectacion_obj)
           {
            $ubicacion_obj = Ubicacion::setUbicacion('create', Input::post('caso'), array('afectacion_id'=>$afectacion_obj->id));
            $politica_publica_obj = new PoliticaPublica();
            $politica_publica_obj = PoliticaPublica::setPoliticaPublica('create', Input::post('politica_publica'), array('afectacion_id'=>$afectacion_obj->id, 'estado'=>PoliticaPublica::ACTIVO));
            if($politica_publica_obj)
            {                       
              $politica_publica_id = $politica_publica_obj->id;              
              Fuente::setFuente('create', Input::post('fuente'), 'politica_publica', $politica_publica_id);
                
              Flash::valid('¡La política pública se ha registrado correctamente!');
              return Redirect::toAction(Session::get('url_back'));
                
            }  
           }
                   
        }        
    }
    
    
    
        /**
     * Método para ver
     */
    public function ver($key) { 
             
        if(!$id = Security::getKey($key, 'show_politica_publica', 'int')) {
            return Redirect::toAction('listar/');
        }  
        
                
        $politica_publica = new PoliticaPublica();
        if(!$politica_publica->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del politica_publica');
            return Redirect::toAction('listar');
        }   
        
        $ubicaciones = $politica_publica->getAfectacion()->getUbicaciones($politica_publica->afectacion_id);
        $this->ubicaciones = $ubicaciones;
                
               
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('politica_publica', $politica_publica->id);
        //var_dump($this->fuentes);        die(); 
              
        $this->politica_publica = $politica_publica;
        $this->page_module = 'Política Pública';
        $this->page_title = 'Información de la política pública: '.$politica_publica->nombre;        
        $this->url_redir_back = Session::get('url_back');
    }
    
     
    /**
     * Método para editar
     */
    public function editar($key) { 
             
        if(!$id = Security::getKey($key, 'upd_politica_publica', 'int')) {
            return Redirect::toAction('listar/');
        }  
        
        
        $politica_publica = new PoliticaPublica();
        if(!$politica_publica->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del politica_publica');
            return Redirect::toAction('listar');
        }   
         
        $this->page_module = 'Política Pública';
        $this->page_title = "Actualizar política pública: $politica_publica->nombre";
        $this->key = $key;
        $this->url_redir_back = Session::get('url_back');
        
         if(Input::hasPost('politica_publica')) {
            
            if(PoliticaPublica::setPoliticaPublica('update', Input::post('politica_publica'), array('id'=>$id)))
                {                
                Fuente::setFuente('update', Input::post('fuente'), 'politica_publica', $id);          
                Flash::valid('La política pública se ha actualizado correctamente!');
                return Redirect::to($this->url_redir_back);
            }            
        }
        
        
        $ubicaciones = $politica_publica->getAfectacion()->getUbicaciones($politica_publica->afectacion_id);
        $this->ubicaciones = $ubicaciones;
                       
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('politica_publica', $politica_publica->id);
        //var_dump($this->fuentes);        die();  
        
        
        $this->politica_publica = $politica_publica;       
    }
    
     /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_politica_publica', 'int')) {
            return Redirect::toAction('listar/');
        }               
        $politica_publica = new PoliticaPublica();
        if(!$politica_publica->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del política pública');            
        } else {
            if($tipo=='inactivar' && $politica_publica->estado == PoliticaPublica::INACTIVO) {
                Flash::info('La política pública ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $politica_publica->estado == PoliticaPublica::ACTIVO) {
                Flash::info('La política pública ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? PoliticaPublica::INACTIVO : PoliticaPublica::ACTIVO;
                if(PoliticaPublica::setPoliticaPublica('update', $politica_publica->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==PoliticaPublica::ACTIVO) ? Flash::valid('La política pública se ha reactivado correctamente!') : Flash::valid('La política pública se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar/');
    }
    
     /**
     * Método para eliminar
     */
    public function eliminar( $nombre_politica_publica, $key, $afectacion_id) {      
        
        $url_redir_back = Session::get('url_back');
        if(!$id = Security::getKey($key, 'del_politica_publica', 'int')) {
            return Redirect::to($url_redir_back);
        }        
        
        $afectacion = new Afectacion();
                    
        try {
            if($afectacion->delete($afectacion_id)) {
                Flash::valid("La política publica $nombre_politica_publica se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO la política publica $nombre_politica_publica.");
            } else {
                Flash::warning('Lo sentimos, pero esta política publica no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Esta política publica no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::to($url_redir_back);
    }
    
    /**
     * Método para subir documentos
     */
    public function upload($documento = '') {   
        
        $upload = new DwUpload($documento, 'files/upload/politica_publica/');
        $upload->setAllowedTypes('pdf|doc|docx');
        //$upload->setAllowedTypes('*');
        $upload->setEncryptNameWithLogin(TRUE);
        //$upload->setSize('50MB', 170, 200, TRUE);
        if(!$data = $upload->save()) { //retorna un array('path'=>'ruta', 'name'=>'nombre.ext');
            $data = array('error'=>TRUE, 'message'=>$upload->getError());
        }
        sleep(1);//Por la velocidad del script no permite que se actualize el archivo
        $this->data = $data;
        View::json();
    }
    
    }
    
    

?>