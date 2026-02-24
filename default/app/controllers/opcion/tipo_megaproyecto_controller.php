
<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los sectores de programas sociales del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('opcion/tipo_megaproyecto');
class TipoMegaproyectoController extends BackendController {
    
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
    public function listar($tipo_megaproyecto_id='', $order='order.tipo_megaproyecto.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $tipo_megaproyectos = new TipoMegaproyecto();
        $this->tipo_megaproyecto_id = $tipo_megaproyecto_id;
        $this->tipo_megaproyectos = $tipo_megaproyectos->getListadoTipoMegaproyecto($tipo_megaproyecto_id,'todos', $order, $page);        
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
        $tipo_megaproyecto     = new TipoMegaproyecto();
        $tipo_megaproyectos    = $tipo_megaproyecto->getAjaxTipoMegaproyecto($field, $value, $order, $page);
        if(empty($tipo_megaproyectos->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->tipo_megaproyectos= $tipo_megaproyectos;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de tipo megaproyecto en el sistema';        
    }
    
    /**
     * Método para agregar
     */
    public function agregar($tipo_megaproyecto_id) {
        
        $this->tipo_megaproyecto_id = $tipo_megaproyecto_id;
        if($tipo_megaproyecto_id=='1'){$this->page_module = 'Tipos de Megaproyecto Obras de Infraestructura';}
        if($tipo_megaproyecto_id=='2'){$this->page_module = 'Tipos de Megaproyecto Economía Extractiva';}
        if($tipo_megaproyecto_id=='3'){$this->page_module = 'Tipos de Megaproyecto Economía de Transformación';}
          
        if(Input::hasPost('tipo_megaproyecto')) {
            $tipo_megaproyecto_obj = new TipoMegaproyecto();
            $tipo_megaproyecto_obj = TipoMegaproyecto::setTipoMegaproyecto('create', Input::post('tipo_megaproyecto'), array('estado'=>TipoMegaproyecto::ACTIVO));
            if($tipo_megaproyecto_obj)
            {         
              Flash::valid('El tipo megaproyecto se ha registrado correctamente!');
              return Redirect::toAction('listar/'.$tipo_megaproyecto_id.'/');                
            }          
        }
        $this->page_title = 'Agregar tipo megaproyecto';
    }
    
    /**
     * Método para editar
     */
    public function editar($key, $tipo_megaproyecto_id) {     
        
        $this->tipo_megaproyecto_id = $tipo_megaproyecto_id;
        if($tipo_megaproyecto_id=='1'){$this->page_module = 'Tipos de Megaproyecto Obras de Infraestructura';}
        if($tipo_megaproyecto_id=='2'){$this->page_module = 'Tipos de Megaproyecto Economía Extractiva';}
        if($tipo_megaproyecto_id=='3'){$this->page_module = 'Tipos de Megaproyecto Economía de Transformación';}
         
        if(!$id = Security::getKey($key, 'upd_tipo_megaproyecto', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $tipo_megaproyecto = new TipoMegaproyecto();
                
        if(!$tipo_megaproyecto->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del tipo megaproyecto');
            return Redirect::toAction('listar/');
        }
        
        if(Input::hasPost('tipo_megaproyecto')) {
            
            if(TipoMegaproyecto::setTipoMegaproyecto('update', Input::post('tipo_megaproyecto'), array('id'=>$id))){
               Flash::valid('El tipo megaproyecto se ha actualizado correctamente!');
               return Redirect::toAction('listar/'.$tipo_megaproyecto_id.'/');     
            }            
        }
            
        $this->tipo_megaproyecto = $tipo_megaproyecto;       
        
        $this->page_title = 'Actualizar tipo megaproyecto';                
        
    }
    
    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_tipo_megaproyecto', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
                
        $tipo_megaproyecto = new TipoMegaproyecto();
        if(!$tipo_megaproyecto->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del tipo megaproyecto');            
        } else {
            if($tipo=='inactivar' && $tipo_megaproyecto->estado == TipoMegaproyecto::INACTIVO) {
                Flash::info('El tipo megaproyecto ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $tipo_megaproyecto->estado == TipoMegaproyecto::ACTIVO) {
                Flash::info('El tipo megaproyecto ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? TipoMegaproyecto::INACTIVO : TipoMegaproyecto::ACTIVO;
                if(TipoMegaproyecto::setTipoMegaproyecto('update', $tipo_megaproyecto->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==TipoMegaproyecto::ACTIVO) ? Flash::valid('El tipo megaproyecto se ha reactivado correctamente!') : Flash::valid('El tipo megaproyecto se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar/');
    }
    
    /**
     * Método para ver
     */
    public function ver($key, $order='order.tipo_megaproyecto.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;     
        if(!$id = Security::getKey($key, 'show_tipo_megaproyecto', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $tipo_megaproyecto = new TipoMegaproyecto();
        if(!$tipo_megaproyecto->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del tipo megaproyecto');
            return Redirect::toAction('listar/');
        }           
             
        $this->tipo_megaproyecto = $tipo_megaproyecto;
        $this->order = $order;        
        $this->page_title = 'Información del tipo megaproyecto';
        $this->key = $key;        
    }
    
    
    /**
     * Método para eliminar
     */
    public function eliminar($key, $tipo_megaproyecto_id) {         
        if(!$id = Security::getKey($key, 'eliminar_tipo_megaproyecto', 'int')) {
            return Redirect::toAction('listar/'.$tipo_megaproyecto_id.'/');
        }        
        
        $recurso = new TipoMegaproyecto();
        if(!$recurso->find_first($id)) {
            Flash::error('Lo sentimos, no se ha podido establecer la información del tipo megaproyecto');    
            return Redirect::toAction('listar/'.$tipo_megaproyecto_id.'/');
        }              
        try {
            if($recurso->delete()) {
                Flash::valid('El tipo megaproyecto se ha eliminado correctamente!');
            } else {
                Flash::warning('Lo sentimos, pero este tipo megaproyecto no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este tipo megaproyecto no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('listar/'.$tipo_megaproyecto_id.'/');
    }
    
}