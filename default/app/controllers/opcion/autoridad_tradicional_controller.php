
<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los sectores de programas sociales del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('opcion/autoridad_tradicional', 'opcion/tipo_autoridad_tradicional');
class AutoridadTradicionalController extends BackendController {
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Autoridades tradicionales';
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
    public function listar($order='order.autoridad_tradicional.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $autoridad_tradicionals = new AutoridadTradicional();
        $this->autoridad_tradicionals = $autoridad_tradicionals->getListadoAutoridadTradicional('todos', $order, $page);        
        $this->order = $order;        
        $this->page_title = 'Listado de Autoridades tradicionales';
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
        $autoridad_tradicional     = new AutoridadTradicional();
        $autoridad_tradicionals    = $autoridad_tradicional->getAjaxAutoridadTradicional($field, $value, $order, $page);
        if(empty($autoridad_tradicionals->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->autoridad_tradicionals= $autoridad_tradicionals;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de Autoridades tradicionales en el sistema';        
    }
    
    /**
     * Método para agregar
     */
    public function agregar() {
        if(Input::hasPost('autoridad_tradicional')) {
            $autoridad_tradicional_obj = new AutoridadTradicional();
            $autoridad_tradicional_obj = AutoridadTradicional::setAutoridadTradicional('create', Input::post('autoridad_tradicional'), array('estado'=>AutoridadTradicional::ACTIVO));
            if($autoridad_tradicional_obj)
            {     
              Flash::valid('La Autoridades tradicionales se ha registrado correctamente!');
              return Redirect::toAction('listar/');                
            }          
        }
        $this->page_title = 'Agregar Autoridades tradicionales';
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = Security::getKey($key, 'upd_autoridad_tradicional', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $autoridad_tradicional = new AutoridadTradicional();
                
        if(!$autoridad_tradicional->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la Autoridades tradicionales');
            return Redirect::toAction('listar/');
        }
        
        if(Input::hasPost('autoridad_tradicional')) {
            
            if(AutoridadTradicional::setAutoridadTradicional('update', Input::post('autoridad_tradicional'), array('id'=>$id))){
               Flash::valid('La Autoridades tradicionales se ha actualizado correctamente!');
                return Redirect::toAction('listar/');
            }            
        }
            
        $this->autoridad_tradicional = $autoridad_tradicional;       
        
        $this->page_title = 'Actualizar Autoridades tradicionales';                
        
    }
    
    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_autoridad_tradicional', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
                
        $autoridad_tradicional = new AutoridadTradicional();
        if(!$autoridad_tradicional->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la Autoridades tradicionales');            
        } else {
            if($tipo=='inactivar' && $autoridad_tradicional->estado == AutoridadTradicional::INACTIVO) {
                Flash::info('La Autoridades tradicionales ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $autoridad_tradicional->estado == AutoridadTradicional::ACTIVO) {
                Flash::info('La Autoridades tradicionales ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? AutoridadTradicional::INACTIVO : AutoridadTradicional::ACTIVO;
                if(AutoridadTradicional::setAutoridadTradicional('update', $autoridad_tradicional->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==AutoridadTradicional::ACTIVO) ? Flash::valid('La Autoridades tradicionales se ha reactivado correctamente!') : Flash::valid('La Autoridades tradicionales se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar/');
    }
    
    /**
     * Método para ver
     */
    public function ver($key, $order='order.autoridad_tradicional.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;     
        if(!$id = Security::getKey($key, 'show_autoridad_tradicional', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $autoridad_tradicional = new AutoridadTradicional();
        if(!$autoridad_tradicional->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la Autoridades tradicionales');
            return Redirect::toAction('listar/');
        }           
             
        $this->autoridad_tradicional = $autoridad_tradicional;
        $this->order = $order;        
        $this->page_title = 'Información de la Autoridades tradicionales';
        $this->key = $key;        
    }
    
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = Security::getKey($key, 'eliminar_autoridad_tradicional', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $recurso = new AutoridadTradicional();
        if(!$recurso->find_first($id)) {
            Flash::error('Lo sentimos, no se ha podido establecer la información de la Autoridades tradicionales');    
            return Redirect::toAction('listar/');
        }              
        try {
            if($recurso->delete()) {
                Flash::valid('La Autoridades tradicionales se ha eliminado correctamente!');
            } else {
                Flash::warning('Lo sentimos, pero esta Autoridades tradicionales no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Esta Autoridades tradicionales no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('listar/');
    }
    
}