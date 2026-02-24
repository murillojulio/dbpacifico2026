
<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los sectores de programas sociales del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('opcion/tipo_subsidio');
class TipoSubsidioController extends BackendController {
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Tipo Subsidio';
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
    public function listar($order='order.tipo_subsidio.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $tipo_subsidios = new TipoSubsidio();
        $this->tipo_subsidios = $tipo_subsidios->getListadoTipoSubsidio('todos', $order, $page);        
        $this->order = $order;        
        $this->page_title = 'Listado de tipos de subsidios';
    }
    
     /**
     * Método para buscar
     * 
     * @param type $field Nombre del campo a buscar
     * @param type $value Valor del campo
     * @param type $order Método de ordenamiento
     * @param type $page Número de página
     */
    public function buscar($field='nombre', $value='none', $order='order.id.asc', $page='page.1') {        
        $page       = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field      = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value      = (Input::hasPost('value')) ? Input::post('value') : $value;
        $tipo_subsidio     = new TipoSubsidio();
        $tipo_subsidios    = $tipo_subsidio->getAjaxTipoSubsidio($field, $value, $order, $page);
        if(empty($tipo_subsidios->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->tipo_subsidios= $tipo_subsidios;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de tipo subsidios en el sistema';        
    }
    
    /**
     * Método para agregar
     */
    public function agregar() {
        if(Input::hasPost('tipo_subsidio')) {
            $tipo_subsidio_obj = new TipoSubsidio();
            $tipo_subsidio_obj = TipoSubsidio::setTipoSubsidio('create', Input::post('tipo_subsidio'), array('estado'=>TipoSubsidio::ACTIVO));
            if($tipo_subsidio_obj)
            {                       
             
              Flash::valid('El tipo de subsidio se ha registrado correctamente!');
              return Redirect::toAction('listar/');
                
            }          
        }
        $this->page_title = 'Agregar Tipo de Subsidio';
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = Security::getKey($key, 'upd_tipo_subsidio', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $tipo_subsidio = new TipoSubsidio();
                
        if(!$tipo_subsidio->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del tipo de subsidio');
            return Redirect::toAction('listar/');
        }
        
        if(Input::hasPost('tipo_subsidio')) {
            
            if(TipoSubsidio::setTipoSubsidio('update', Input::post('tipo_subsidio'), array('id'=>$id))){
               Flash::valid('El tipo de subsidio se ha actualizado correctamente!');
                return Redirect::toAction('listar/');
            }            
        }
            
        $this->tipo_subsidio = $tipo_subsidio;       
        
        $this->page_title = 'Actualizar tipo de subsidio';                
        
    }
    
    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_tipo_subsidio', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
                
        $tipo_subsidio = new TipoSubsidio();
        if(!$tipo_subsidio->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del tipo de subsidio');            
        } else {
            if($tipo=='inactivar' && $tipo_subsidio->estado == TipoSubsidio::INACTIVO) {
                Flash::info('El tipo de subsidio ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $tipo_subsidio->estado == TipoSubsidio::ACTIVO) {
                Flash::info('El tipo de subsidio ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? TipoSubsidio::INACTIVO : TipoSubsidio::ACTIVO;
                if(TipoSubsidio::setTipoSubsidio('update', $tipo_subsidio->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==TipoSubsidio::ACTIVO) ? Flash::valid('El tipo de subsidio se ha reactivado correctamente!') : Flash::valid('El tipo de subsidio se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar/');
    }
    
    /**
     * Método para ver
     */
    public function ver($key, $order='order.tipo_subsidio.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;     
        if(!$id = Security::getKey($key, 'show_tipo_subsidio', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $tipo_subsidio = new TipoSubsidio();
        if(!$tipo_subsidio->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del tipo de subsidio');
            return Redirect::toAction('listar/');
        }           
             
        $this->tipo_subsidio = $tipo_subsidio;
        $this->order = $order;        
        $this->page_title = 'Información del tipo de subsidio';
        $this->key = $key;        
    }
    
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = Security::getKey($key, 'eliminar_tipo_subsidio', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $recurso = new TipoSubsidio();
        if(!$recurso->find_first($id)) {
            Flash::error('Lo sentimos, no se ha podido establecer la información del tipo de subsidio');    
            return Redirect::toAction('listar/');
        }              
        try {
            if($recurso->delete()) {
                Flash::valid('El tipo de subsidio se ha eliminado correctamente!');
            } else {
                Flash::warning('Lo sentimos, pero este tipo de subsidio no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este tipo de subsidio no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('listar/');
    }
    
}