
<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los sectores de programas sociales del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('opcion/subtipo_megaproyecto');
class SubtipoMegaproyectoController extends BackendController {
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Tipos de Megaproyecto';
    }
    
    /**
     * Método principal
     */
    public function index() {
        //Redirect::toAction('listar');
        $this->page_title = 'Configuración de Tipos de Megaproyecto';
    }
    
    /**
     * Método para listar
     */
    public function listar($tipo_megaproyecto_id='', $order='order.subtipo_megaproyecto.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $subtipo_megaproyectos = new SubtipoMegaproyecto();
        $this->tipo_megaproyecto_id = $tipo_megaproyecto_id;
        $this->subtipo_megaproyectos = $subtipo_megaproyectos->getListadoSubtipoMegaproyecto($tipo_megaproyecto_id,'todos', $order, $page);        
        $this->order = $order;        
        $this->page_title = 'Listado de Tipos de Megaproyecto';
        
        if($tipo_megaproyecto_id=='1'){$this->page_module = 'Tipos de Megaproyecto Obras de Infraestructura';}
        if($tipo_megaproyecto_id=='2'){$this->page_module = 'Tipos de Megaproyecto Economía Extractiva';}
        if($tipo_megaproyecto_id=='3'){$this->page_module = 'Tipos de Megaproyecto Economía de Transformación';}
        
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
        $subtipo_megaproyecto     = new SubtipoMegaproyecto();
        $subtipo_megaproyectos    = $subtipo_megaproyecto->getAjaxSubtipoMegaproyecto($field, $value, $order, $page);
        if(empty($subtipo_megaproyectos->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->subtipo_megaproyectos= $subtipo_megaproyectos;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de subtipo megaproyecto en el sistema';        
    }
    
    /**
     * Método para agregar
     */
    public function agregar($tipo_megaproyecto_id) {
        
        $this->tipo_megaproyecto_id = $tipo_megaproyecto_id;
        if($tipo_megaproyecto_id=='1'){$this->page_module = 'Tipos de Megaproyecto Obras de Infraestructura';}
        if($tipo_megaproyecto_id=='2'){$this->page_module = 'Tipos de Megaproyecto Economía Extractiva';}
        if($tipo_megaproyecto_id=='3'){$this->page_module = 'Tipos de Megaproyecto Economía de Transformación';}
          
        if(Input::hasPost('subtipo_megaproyecto')) {
            $subtipo_megaproyecto_obj = new SubtipoMegaproyecto();
            $subtipo_megaproyecto_obj = SubtipoMegaproyecto::setSubtipoMegaproyecto('create', Input::post('subtipo_megaproyecto'), array('estado'=>SubtipoMegaproyecto::ACTIVO));
            if($subtipo_megaproyecto_obj)
            {         
              Flash::valid('El subtipo megaproyecto se ha registrado correctamente!');
              return Redirect::toAction('listar/'.$tipo_megaproyecto_id.'/');                
            }          
        }
        $this->page_title = 'Agregar subtipo megaproyecto';
    }
    
    /**
     * Método para editar
     */
    public function editar($key, $tipo_megaproyecto_id) {     
        
        $this->tipo_megaproyecto_id = $tipo_megaproyecto_id;
        if($tipo_megaproyecto_id=='1'){$this->page_module = 'Tipos de Megaproyecto Obras de Infraestructura';}
        if($tipo_megaproyecto_id=='2'){$this->page_module = 'Tipos de Megaproyecto Economía Extractiva';}
        if($tipo_megaproyecto_id=='3'){$this->page_module = 'Tipos de Megaproyecto Economía de Transformación';}
         
        if(!$id = Security::getKey($key, 'upd_subtipo_megaproyecto', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $subtipo_megaproyecto = new SubtipoMegaproyecto();
                
        if(!$subtipo_megaproyecto->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del subtipo megaproyecto');
            return Redirect::toAction('listar/');
        }
        
        if(Input::hasPost('subtipo_megaproyecto')) {
            
            if(SubtipoMegaproyecto::setSubtipoMegaproyecto('update', Input::post('subtipo_megaproyecto'), array('id'=>$id))){
               Flash::valid('El subtipo megaproyecto se ha actualizado correctamente!');
               return Redirect::toAction('listar/'.$tipo_megaproyecto_id.'/');     
            }            
        }
            
        $this->subtipo_megaproyecto = $subtipo_megaproyecto;       
        
        $this->page_title = 'Actualizar subtipo megaproyecto';                
        
    }
    
    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_subtipo_megaproyecto', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
                
        $subtipo_megaproyecto = new SubtipoMegaproyecto();
        if(!$subtipo_megaproyecto->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del subtipo megaproyecto');            
        } else {
            if($tipo=='inactivar' && $subtipo_megaproyecto->estado == SubtipoMegaproyecto::INACTIVO) {
                Flash::info('El subtipo megaproyecto ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $subtipo_megaproyecto->estado == SubtipoMegaproyecto::ACTIVO) {
                Flash::info('El subtipo megaproyecto ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? SubtipoMegaproyecto::INACTIVO : SubtipoMegaproyecto::ACTIVO;
                if(SubtipoMegaproyecto::setSubtipoMegaproyecto('update', $subtipo_megaproyecto->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==SubtipoMegaproyecto::ACTIVO) ? Flash::valid('El subtipo megaproyecto se ha reactivado correctamente!') : Flash::valid('El subtipo megaproyecto se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar/');
    }
    
    /**
     * Método para ver
     */
    public function ver($key, $order='order.subtipo_megaproyecto.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;     
        if(!$id = Security::getKey($key, 'show_subtipo_megaproyecto', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $subtipo_megaproyecto = new SubtipoMegaproyecto();
        if(!$subtipo_megaproyecto->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del subtipo megaproyecto');
            return Redirect::toAction('listar/');
        }           
             
        $this->subtipo_megaproyecto = $subtipo_megaproyecto;
        $this->order = $order;        
        $this->page_title = 'Información del subtipo megaproyecto';
        $this->key = $key;        
    }
    
    
    /**
     * Método para eliminar
     */
    public function eliminar($key, $tipo_megaproyecto_id) {         
        if(!$id = Security::getKey($key, 'eliminar_subtipo_megaproyecto', 'int')) {
            return Redirect::toAction('listar/'.$tipo_megaproyecto_id.'/');
        }        
        
        $recurso = new SubtipoMegaproyecto();
        if(!$recurso->find_first($id)) {
            Flash::error('Lo sentimos, no se ha podido establecer la información del subtipo megaproyecto');    
            return Redirect::toAction('listar/'.$tipo_megaproyecto_id.'/');
        }              
        try {
            if($recurso->delete()) {
                Flash::valid('El subtipo megaproyecto se ha eliminado correctamente!');
            } else {
                Flash::warning('Lo sentimos, pero este subtipo megaproyecto no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este subtipo megaproyecto no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('listar/'.$tipo_megaproyecto_id.'/');
    }
    
}