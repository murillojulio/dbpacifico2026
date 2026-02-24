
<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los sectores de programas sociales del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('opcion/mads_car');
class MadsCarController extends BackendController {
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'MADS - CAR';
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
    public function listar($order='order.mads_car.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $mads_cars = new MadsCar();
        $this->mads_cars = $mads_cars->getListadoMadsCar('todos', $order, $page);        
        $this->order = $order;        
        $this->page_title = 'Listado de MADS - CAR';
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
        $mads_car     = new MadsCar();
        $mads_cars    = $mads_car->getAjaxMadsCar($field, $value, $order, $page);
        if(empty($mads_cars->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->mads_cars= $mads_cars;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de MADS - CAR en el sistema';        
    }
    
    /**
     * Método para agregar
     */
    public function agregar() {
        if(Input::hasPost('mads_car')) {
            $mads_car_obj = new MadsCar();
            $mads_car_obj = MadsCar::setMadsCar('create', Input::post('mads_car'), array('estado'=>MadsCar::ACTIVO));
            if($mads_car_obj)
            {     
              Flash::valid('El MADS - CAR se ha registrado correctamente!');
              return Redirect::toAction('listar/');                
            }          
        }
        $this->page_title = 'Agregar MADS - CAR';
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = Security::getKey($key, 'upd_mads_car', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $mads_car = new MadsCar();
                
        if(!$mads_car->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del MADS - CAR');
            return Redirect::toAction('listar/');
        }
        
        if(Input::hasPost('mads_car')) {
            
            if(MadsCar::setMadsCar('update', Input::post('mads_car'), array('id'=>$id))){
               Flash::valid('El MADS - CAR se ha actualizado correctamente!');
                return Redirect::toAction('listar/');
            }            
        }
            
        $this->mads_car = $mads_car;       
        
        $this->page_title = 'Actualizar MADS - CAR';                
        
    }
    
    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_mads_car', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
                
        $mads_car = new MadsCar();
        if(!$mads_car->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del MADS - CAR');            
        } else {
            if($tipo=='inactivar' && $mads_car->estado == MadsCar::INACTIVO) {
                Flash::info('El MADS - CAR ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $mads_car->estado == MadsCar::ACTIVO) {
                Flash::info('El MADS - CAR ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? MadsCar::INACTIVO : MadsCar::ACTIVO;
                if(MadsCar::setMadsCar('update', $mads_car->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==MadsCar::ACTIVO) ? Flash::valid('El MADS - CAR se ha reactivado correctamente!') : Flash::valid('El MADS - CAR se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar/');
    }
    
    /**
     * Método para ver
     */
    public function ver($key, $order='order.mads_car.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;     
        if(!$id = Security::getKey($key, 'show_mads_car', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $mads_car = new MadsCar();
        if(!$mads_car->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del MADS - CAR');
            return Redirect::toAction('listar/');
        }           
             
        $this->mads_car = $mads_car;
        $this->order = $order;        
        $this->page_title = 'Información del MADS - CAR';
        $this->key = $key;        
    }
    
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = Security::getKey($key, 'eliminar_mads_car', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $recurso = new MadsCar();
        if(!$recurso->find_first($id)) {
            Flash::error('Lo sentimos, no se ha podido establecer la información del MADS - CAR');    
            return Redirect::toAction('listar/');
        }              
        try {
            if($recurso->delete()) {
                Flash::valid('El MADS - CAR se ha eliminado correctamente!');
            } else {
                Flash::warning('Lo sentimos, pero este MADS - CAR no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este MADS - CAR no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('listar/');
    }
    
}