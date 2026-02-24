<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de las politicas publicas
 *
 * @category    
 * @package     Controllers  
 */
Load::models('afectacion/desarrollo_normativo', 'global/fuente', 'afectacion/ubicacion', 
        'afectacion/afectacion', 'observatorio/departamento', 'observatorio/municipio',
        'observatorio/territorio', 'observatorio/subregion');

class DesarrolloNormativoController extends BackendController {    
     /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Desarrollo Normativo';
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
    public function listar($order='order.desarrollo_normativo.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $desarrollo_normativo = new DesarrolloNormativo();
        $this->desarrollo_normativos = $desarrollo_normativo->getListadoDesarrolloNormativo('todos', $order, $page);        
        $this->order = $order;   
        
        $this->page_module = 'Desarrollo Normativo';
        $this->page_title = 'Listado de desarrollo normativo';     
        Session::set('url_back', 'afectacion/desarrollo_normativo/listar/'.$order.'/page.'.$page.'/');
    }
    
     /**
     * Método para buscar
     * 
     * @param type $field Nombre del campo a buscar
     * @param type $value Valor del campo
     * @param type $order Método de ordenamiento
     * @param type $page Número de página
     */
    public function buscar_desarrollo_normativo($field='nombre', $value='none', $order='order.id.asc', $page='page.1') {        
        $page       = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field      = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value      = (Input::hasPost('value')) ? Input::post('value') : $value;
        $desarrollo_normativo     = new DesarrolloNormativo();
        $desarrollo_normativo    = $desarrollo_normativo->getAjaxDesarrolloNormativo($field, $value, $order, $page);
        if(empty($desarrollo_normativo->items)) {
            Flash::info('No se han encontrado registros');
        }
        
        $this->desarrollo_normativos = $desarrollo_normativo;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de desarrollo normativo en el sistema';   
        $this->page_module = 'Desarrollo Normativo';
        Session::set('url_back', "afectacion/desarrollo_normativo/buscar_desarrollo_normativo/$field/$value/$order/page.$page/");
    }
    
    /**
     * Método para agregar
     */
    public function agregar() {
               
        $this->page_module = 'Desarrollo Normativo';
        $this->page_title = 'Agregar desarrollo normativo';
               
        if(Input::hasPost('desarrollo_normativo')) {
            
            $afectacion_obj = Afectacion::setAfectacion('create', array('tipo_afectacion_id'=>'1'));
           if($afectacion_obj)
           {
            $ubicacion_obj = Ubicacion::setUbicacion('create', Input::post('caso'), array('afectacion_id'=>$afectacion_obj->id));
            $desarrollo_normativo_obj = new DesarrolloNormativo();
            $desarrollo_normativo_obj = DesarrolloNormativo::setDesarrolloNormativo('create', Input::post('desarrollo_normativo'), array('afectacion_id'=>$afectacion_obj->id, 'estado'=>DesarrolloNormativo::ACTIVO));
            if($desarrollo_normativo_obj)
            {                       
              $desarrollo_normativo_id = $desarrollo_normativo_obj->id;              
              Fuente::setFuente('create', Input::post('fuente'), 'desarrollo_normativo', $desarrollo_normativo_id);
                
              Flash::valid('¡El desarrollo normativo se ha registrado correctamente!');
              return Redirect::toAction('listar/');
                
            }  
           }
                   
        }        
    }
    
    
    
        /**
     * Método para ver
     */
    public function ver($key) { 
             
        if(!$id = Security::getKey($key, 'show_desarrollo_normativo', 'int')) {
            return Redirect::toAction('listar/');
        }  
        
                
        $desarrollo_normativo = new DesarrolloNormativo();
        if(!$desarrollo_normativo->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del desarrollo_normativo');
            return Redirect::toAction('listar');
        }   
        
        $ubicaciones = $desarrollo_normativo->getAfectacion()->getUbicaciones($desarrollo_normativo->afectacion_id);
        $this->ubicaciones = $ubicaciones;
                
               
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('desarrollo_normativo', $desarrollo_normativo->id);
        //var_dump($this->fuentes);        die(); 
              
        $this->desarrollo_normativo = $desarrollo_normativo;
        $this->page_module = 'Desarrollo Normativo';
        $this->page_title = 'Información del desarrollo normativo: '.$desarrollo_normativo->nombre;        
        $this->url_redir_back = Session::get('url_back');
        
        
    }
    
     
    /**
     * Método para editar
     */
    public function editar($key) { 
             
        if(!$id = Security::getKey($key, 'upd_desarrollo_normativo', 'int')) {
            return Redirect::toAction('listar/');
        }   
        
        $desarrollo_normativo = new DesarrolloNormativo();
        if(!$desarrollo_normativo->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del desarrollo_normativo');
            return Redirect::toAction('listar');
        }   
        
        $ubicaciones = $desarrollo_normativo->getAfectacion()->getUbicaciones($desarrollo_normativo->afectacion_id);
        $this->ubicaciones = $ubicaciones;
                       
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('desarrollo_normativo', $desarrollo_normativo->id);
        //var_dump($this->fuentes);        die();  
        
        
        $this->desarrollo_normativo = $desarrollo_normativo;
        
        $this->page_module = 'Desarrollo Normativo';
        $this->page_title = "Actualizar desarrollo normativo: $desarrollo_normativo->nombre";
        $this->key = $key;
        $this->url_redir_back = Session::get('url_back');
        
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
    
     /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_desarrollo_normativo', 'int')) {
            return Redirect::toAction('listar/');
        }               
        $desarrollo_normativo = new DesarrolloNormativo();
        if(!$desarrollo_normativo->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del desarrollo normativo');            
        } else {
            if($tipo=='inactivar' && $desarrollo_normativo->estado == DesarrolloNormativo::INACTIVO) {
                Flash::info('El desarrollo normativo ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $desarrollo_normativo->estado == DesarrolloNormativo::ACTIVO) {
                Flash::info('El desarrollo normativo ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? DesarrolloNormativo::INACTIVO : DesarrolloNormativo::ACTIVO;
                if(DesarrolloNormativo::setDesarrolloNormativo('update', $desarrollo_normativo->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==DesarrolloNormativo::ACTIVO) ? Flash::valid('El desarrollo normativo se ha reactivado correctamente!') : Flash::valid('El desarrollo normativo se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar/');
    }
    

    
     /**
     * Método para eliminar
     */
    public function eliminar( $nombre_desarrollo_normativo, $key, $afectacion_id) {      
        
        $url_redir_back = Session::get('url_back');
        if(!$id = Security::getKey($key, 'del_desarrollo_normativo', 'int')) {
            return Redirect::to($url_redir_back);
        }        
        
        $afectacion = new Afectacion();
                    
        try {
            if($afectacion->delete($afectacion_id)) {
                Flash::valid("El desarrollo normativo $nombre_desarrollo_normativo se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO el desarrollo normativo $nombre_desarrollo_normativo.");
            } else {
                Flash::warning('Lo sentimos, pero este desarrollo normativo no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este desarrollo normativo no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::to($url_redir_back);
    }
    
}

?>