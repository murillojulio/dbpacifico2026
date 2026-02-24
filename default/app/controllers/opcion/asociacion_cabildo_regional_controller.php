
<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los sectores de programas sociales del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('opcion/asociacion_cabildo_regional');
class AsociacionCabildoRegionalController extends BackendController {
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Asociación de cabildos regional';
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
    public function listar($order='order.asociacion_cabildo_regional.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $asociacion_cabildo_regionals = new AsociacionCabildoRegional();
        $this->asociacion_cabildo_regionals = $asociacion_cabildo_regionals->getListadoAsociacionCabildoRegional('todos', $order, $page);        
        $this->order = $order;        
        $this->page_title = 'Listado de Asociación de cabildos regional';
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
        $asociacion_cabildo_regional     = new AsociacionCabildoRegional();
        $asociacion_cabildo_regionals    = $asociacion_cabildo_regional->getAjaxAsociacionCabildoRegional($field, $value, $order, $page);
        if(empty($asociacion_cabildo_regionals->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->asociacion_cabildo_regionals= $asociacion_cabildo_regionals;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de Asociación de cabildos regional en el sistema';        
    }
    
    /**
     * Método para agregar
     */
    public function agregar() {
        if(Input::hasPost('asociacion_cabildo_regional')) {
            $asociacion_cabildo_regional_obj = new AsociacionCabildoRegional();
            $asociacion_cabildo_regional_obj = AsociacionCabildoRegional::setAsociacionCabildoRegional('create', Input::post('asociacion_cabildo_regional'), array('estado'=>AsociacionCabildoRegional::ACTIVO));
            if($asociacion_cabildo_regional_obj)
            {     
              Flash::valid('La Asociación de cabildos regional se ha registrado correctamente!');
              return Redirect::toAction('listar/');                
            }          
        }
        $this->page_title = 'Agregar Asociación de cabildos regional';
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = Security::getKey($key, 'upd_asociacion_cabildo_regional', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $asociacion_cabildo_regional = new AsociacionCabildoRegional();
                
        if(!$asociacion_cabildo_regional->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la Asociación de cabildos regional');
            return Redirect::toAction('listar/');
        }
        
        if(Input::hasPost('asociacion_cabildo_regional')) {
            
            if(AsociacionCabildoRegional::setAsociacionCabildoRegional('update', Input::post('asociacion_cabildo_regional'), array('id'=>$id))){
               Flash::valid('La Asociación de cabildos regional se ha actualizado correctamente!');
                return Redirect::toAction('listar/');
            }            
        }
            
        $this->asociacion_cabildo_regional = $asociacion_cabildo_regional;       
        
        $this->page_title = 'Actualizar Asociación de cabildos regional';                
        
    }
    
    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_asociacion_cabildo_regional', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
                
        $asociacion_cabildo_regional = new AsociacionCabildoRegional();
        if(!$asociacion_cabildo_regional->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la Asociación de cabildos regional');            
        } else {
            if($tipo=='inactivar' && $asociacion_cabildo_regional->estado == AsociacionCabildoRegional::INACTIVO) {
                Flash::info('La Asociación de cabildos regional ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $asociacion_cabildo_regional->estado == AsociacionCabildoRegional::ACTIVO) {
                Flash::info('La Asociación de cabildos regional ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? AsociacionCabildoRegional::INACTIVO : AsociacionCabildoRegional::ACTIVO;
                if(AsociacionCabildoRegional::setAsociacionCabildoRegional('update', $asociacion_cabildo_regional->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==AsociacionCabildoRegional::ACTIVO) ? Flash::valid('La Asociación de cabildos regional se ha reactivado correctamente!') : Flash::valid('La Asociación de cabildos regional se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar/');
    }
    
    /**
     * Método para ver
     */
    public function ver($key, $order='order.asociacion_cabildo_regional.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;     
        if(!$id = Security::getKey($key, 'show_asociacion_cabildo_regional', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $asociacion_cabildo_regional = new AsociacionCabildoRegional();
        if(!$asociacion_cabildo_regional->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la Asociación de cabildos regional');
            return Redirect::toAction('listar/');
        }           
             
        $this->asociacion_cabildo_regional = $asociacion_cabildo_regional;
        $this->order = $order;        
        $this->page_title = 'Información de la Asociación de cabildos regional';
        $this->key = $key;        
    }
    
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = Security::getKey($key, 'eliminar_asociacion_cabildo_regional', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $recurso = new AsociacionCabildoRegional();
        if(!$recurso->find_first($id)) {
            Flash::error('Lo sentimos, no se ha podido establecer la información de la Asociación de cabildos regional');    
            return Redirect::toAction('listar/');
        }              
        try {
            if($recurso->delete()) {
                Flash::valid('La Asociación de cabildos regional se ha eliminado correctamente!');
            } else {
                Flash::warning('Lo sentimos, pero esta Asociación de cabildos regional no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Esta Asociación de cabildos regional no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('listar/');
    }
    
}