
<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los sectores de programas sociales del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('opcion/impacto');
class ImpactosController extends BackendController {
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Daños';
    }
    
    /**
     * Método principal
     */
    public function index() {
       $this->page_module = 'Tipos de Daño';
       $this->page_title = 'Opciones';
    }
    
    /**
     * Método para listar
     */
    public function listar($tipo_impacto_id='', $order='order.impacto.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $impactos = new Impacto();
        $this->tipo_impacto_id = $tipo_impacto_id;
        $this->impactos = $impactos->getListadoImpacto($tipo_impacto_id,'todos', $order, $page);        
        $this->order = $order;        
        $this->page_title = 'Listado de Daños';
        
        if($tipo_impacto_id=='1'){$this->page_module = 'Daños Ambientales';}
        if($tipo_impacto_id=='2'){$this->page_module = 'Daños Espirituales';}
        if($tipo_impacto_id=='3'){$this->page_module = 'Daños Uso territorio';}
        if($tipo_impacto_id=='4'){$this->page_module = 'Daños Organizacional';}
        if($tipo_impacto_id=='5'){$this->page_module = 'Daños en Relaciones sociales';}
        if($tipo_impacto_id=='6'){$this->page_module = 'Daños en Control territorio';}
        
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
        $impacto     = new Impacto();
        $impactos    = $impacto->getAjaxImpacto($field, $value, $order, $page);
        if(empty($impactos->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->impactos= $impactos;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de daños en el sistema';        
    }
    
    /**
     * Método para agregar
     */
    public function agregar($tipo_impacto_id) {
        
        $this->tipo_impacto_id = $tipo_impacto_id;
         if($tipo_impacto_id=='1'){$this->page_module = 'Daños Ambientales';}
        if($tipo_impacto_id=='2'){$this->page_module = 'Daños Espirituales';}
        if($tipo_impacto_id=='3'){$this->page_module = 'Daños Uso territorio';}
        if($tipo_impacto_id=='4'){$this->page_module = 'Daños Organizacional';}
        if($tipo_impacto_id=='5'){$this->page_module = 'Daños en Relaciones sociales';}
        if($tipo_impacto_id=='6'){$this->page_module = 'Daños en Control territorio';}
        
        if(Input::hasPost('impacto')) {
            $impacto_obj = new Impacto();
            $impacto_obj = Impacto::setImpacto('create', Input::post('impacto'), array('estado'=>Impacto::ACTIVO));
            if($impacto_obj)
            {         
              Flash::valid('El daño se ha registrado correctamente!');
              return Redirect::toAction('listar/'.$tipo_impacto_id.'/');                
            }          
        }
        $this->page_title = 'Agregar daño';
    }
    
    /**
     * Método para editar
     */
    public function editar($key, $tipo_impacto_id) {     
        
        $this->tipo_impacto_id = $tipo_impacto_id;
        if($tipo_impacto_id=='1'){$this->page_module = 'Daños Ambientales';}
        if($tipo_impacto_id=='2'){$this->page_module = 'Daños Espirituales';}
        if($tipo_impacto_id=='3'){$this->page_module = 'Daños Uso territorio';}
        if($tipo_impacto_id=='4'){$this->page_module = 'Daños Organizacional';}
        if($tipo_impacto_id=='5'){$this->page_module = 'Daños en Relaciones sociales';}
        if($tipo_impacto_id=='6'){$this->page_module = 'Daños en Control territorio';}
        
        if(!$id = Security::getKey($key, 'upd_impacto', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $impacto = new Impacto();
                
        if(!$impacto->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del daño');
            return Redirect::toAction('listar/');
        }
        
        if(Input::hasPost('impacto')) {
            
            if(Impacto::setImpacto('update', Input::post('impacto'), array('id'=>$id))){
               Flash::valid('El daño se ha actualizado correctamente!');
               return Redirect::toAction('listar/'.$tipo_impacto_id.'/');     
            }            
        }
            
        $this->impacto = $impacto;       
        
        $this->page_title = 'Actualizar daño';                
        
    }
    
    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_impacto', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
                
        $impacto = new Impacto();
        if(!$impacto->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del daño');            
        } else {
            if($tipo=='inactivar' && $impacto->estado == Impacto::INACTIVO) {
                Flash::info('El impacto ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $impacto->estado == Impacto::ACTIVO) {
                Flash::info('El impacto ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? Impacto::INACTIVO : Impacto::ACTIVO;
                if(Impacto::setImpacto('update', $impacto->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==Impacto::ACTIVO) ? Flash::valid('El daño se ha reactivado correctamente!') : Flash::valid('El daño se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar/');
    }
    
    /**
     * Método para ver
     */
    public function ver($key, $order='order.impacto.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;     
        if(!$id = Security::getKey($key, 'show_impacto', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $impacto = new Impacto();
        if(!$impacto->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del daño');
            return Redirect::toAction('listar/');
        }           
             
        $this->impacto = $impacto;
        $this->order = $order;        
        $this->page_title = 'Información del daño';
        $this->key = $key;        
    }
    
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = Security::getKey($key, 'eliminar_impacto', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $recurso = new Impacto();
        if(!$recurso->find_first($id)) {
            Flash::error('Lo sentimos, no se ha podido establecer la información del daño');    
            return Redirect::toAction('listar/');
        }              
        try {
            if($recurso->delete()) {
                Flash::valid('El daño se ha eliminado correctamente!');
            } else {
                Flash::warning('Lo sentimos, pero este daño no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este daño no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('listar/');
    }
    
}