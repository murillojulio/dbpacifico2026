
<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los sectores de programas sociales del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('opcion/asociacion_consejo_comunitario');
class AsociacionConsejoComunitarioController extends BackendController {
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Asociación de consejos comunitarios';
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
    public function listar($order='order.asociacion_consejo_comunitario.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $asociacion_consejo_comunitarios = new AsociacionConsejoComunitario();
        $this->asociacion_consejo_comunitarios = $asociacion_consejo_comunitarios->getListadoAsociacionConsejoComunitario('todos', $order, $page);        
        $this->order = $order;        
        $this->page_title = 'Listado de Asociación de consejos comunitarios';
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
        $asociacion_consejo_comunitario     = new AsociacionConsejoComunitario();
        $asociacion_consejo_comunitarios    = $asociacion_consejo_comunitario->getAjaxAsociacionConsejoComunitario($field, $value, $order, $page);
        if(empty($asociacion_consejo_comunitarios->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->asociacion_consejo_comunitarios= $asociacion_consejo_comunitarios;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de Asociación de consejos comunitarios en el sistema';        
    }
    
    /**
     * Método para agregar
     */
    public function agregar() {
        if(Input::hasPost('asociacion_consejo_comunitario')) {
            $asociacion_consejo_comunitario_obj = new AsociacionConsejoComunitario();
            $asociacion_consejo_comunitario_obj = AsociacionConsejoComunitario::setAsociacionConsejoComunitario('create', Input::post('asociacion_consejo_comunitario'), array('estado'=>AsociacionConsejoComunitario::ACTIVO));
            if($asociacion_consejo_comunitario_obj)
            {     
              Flash::valid('La Asociación de consejos comunitarios se ha registrado correctamente!');
              return Redirect::toAction('listar/');                
            }          
        }
        $this->page_title = 'Agregar Asociación de consejos comunitarios';
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = Security::getKey($key, 'upd_asociacion_consejo_comunitario', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $asociacion_consejo_comunitario = new AsociacionConsejoComunitario();
                
        if(!$asociacion_consejo_comunitario->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la Asociación de consejos comunitarios');
            return Redirect::toAction('listar/');
        }
        
        if(Input::hasPost('asociacion_consejo_comunitario')) {
            
            if(AsociacionConsejoComunitario::setAsociacionConsejoComunitario('update', Input::post('asociacion_consejo_comunitario'), array('id'=>$id))){
               Flash::valid('La Asociación de consejos comunitarios se ha actualizado correctamente!');
                return Redirect::toAction('listar/');
            }            
        }
            
        $this->asociacion_consejo_comunitario = $asociacion_consejo_comunitario;       
        
        $this->page_title = 'Actualizar Asociación de consejos comunitarios';                
        
    }
    
    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_asociacion_consejo_comunitario', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
                
        $asociacion_consejo_comunitario = new AsociacionConsejoComunitario();
        if(!$asociacion_consejo_comunitario->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la Asociación de consejos comunitarios');            
        } else {
            if($tipo=='inactivar' && $asociacion_consejo_comunitario->estado == AsociacionConsejoComunitario::INACTIVO) {
                Flash::info('La Asociación de consejos comunitarios ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $asociacion_consejo_comunitario->estado == AsociacionConsejoComunitario::ACTIVO) {
                Flash::info('La Asociación de consejos comunitarios ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? AsociacionConsejoComunitario::INACTIVO : AsociacionConsejoComunitario::ACTIVO;
                if(AsociacionConsejoComunitario::setAsociacionConsejoComunitario('update', $asociacion_consejo_comunitario->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==AsociacionConsejoComunitario::ACTIVO) ? Flash::valid('La Asociación de consejos comunitarios se ha reactivado correctamente!') : Flash::valid('La Asociación de consejos comunitarios se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar/');
    }
    
    /**
     * Método para ver
     */
    public function ver($key, $order='order.asociacion_consejo_comunitario.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;     
        if(!$id = Security::getKey($key, 'show_asociacion_consejo_comunitario', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $asociacion_consejo_comunitario = new AsociacionConsejoComunitario();
        if(!$asociacion_consejo_comunitario->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la Asociación de consejos comunitarios');
            return Redirect::toAction('listar/');
        }           
             
        $this->asociacion_consejo_comunitario = $asociacion_consejo_comunitario;
        $this->order = $order;        
        $this->page_title = 'Información de la Asociación de consejos comunitarios';
        $this->key = $key;        
    }
    
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = Security::getKey($key, 'eliminar_asociacion_consejo_comunitario', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $recurso = new AsociacionConsejoComunitario();
        if(!$recurso->find_first($id)) {
            Flash::error('Lo sentimos, no se ha podido establecer la información de la Asociación de consejos comunitarios');    
            return Redirect::toAction('listar/');
        }              
        try {
            if($recurso->delete()) {
                Flash::valid('La Asociación de consejos comunitarios se ha eliminado correctamente!');
            } else {
                Flash::warning('Lo sentimos, pero esta Asociación de consejos comunitarios no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Esta Asociación de consejos comunitarios no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('listar/');
    }
    
}