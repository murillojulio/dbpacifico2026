
<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los departamentos del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('observatorio/departamento', 'observatorio/fuente', 'observatorio/poblacion', 'util/currency');
class DepartamentosController extends BackendController {
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Departamento';
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
    public function listar($order='order.departamento.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $departamentos = new Departamento();
        $this->departamentos = $departamentos->getListadoDepartamento('todos', $order, $page);        
        $this->order = $order;        
        $this->page_title = 'Listado de departamentos monitoriados';
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
        $departamento     = new Departamento();
        $departamentos    = $departamento->getAjaxDepartamento($field, $value, $order, $page);
        if(empty($departamentos->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->departamentos= $departamentos;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de departamentos en el sistema';        
    }
    
    /**
     * Método para agregar
     */
    public function agregar() {
        if(Input::hasPost('departamento')) {
            $post_departamento = Input::post('departamento');
           
            $post_departamento['area_total'] = Currency::comaApunto($post_departamento['area_total']);
            $post_departamento['area_urbana'] = Currency::comaApunto($post_departamento['area_urbana']);
            $post_departamento['area_rural'] = Currency::comaApunto($post_departamento['area_rural']);
            
            $departamento_obj = new Departamento();
            $departamento_obj = Departamento::setDepartamento('create', $post_departamento, array('estado'=>Departamento::ACTIVO));
            if($departamento_obj)
            {                       
              $departamento_id = $departamento_obj->id;
              
              Poblacion::setPoblacion('create', Input::post('poblacion'), 'departamento_id', $departamento_id);       
              
              Fuente::setFuente('create', Input::post('fuente'), 'departamento', $departamento_id);
                
              Flash::valid('El departamento se ha registrado correctamente!');
              return Redirect::toAction('listar');
                
            }          
        }
        $this->page_title = 'Agregar departamento';
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = Security::getKey($key, 'upd_departamento', 'int')) {
            return Redirect::toAction('listar');
        }        
        
        $departamento = new Departamento();
        $poblacion = new Poblacion();
        $poblacion = Poblacion::getPoblacion('departamento_id', $id);
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('departamento', $id);
        
        if(!$departamento->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del departamento');
            return Redirect::toAction('listar');
        }
        
        if(Input::hasPost('departamento') && Input::hasPost('poblacion')) {
            
            $post_departamento = Input::post('departamento');
           
            $post_departamento['area_total'] = Currency::comaApunto($post_departamento['area_total']);
            $post_departamento['area_urbana'] = Currency::comaApunto($post_departamento['area_urbana']);
            $post_departamento['area_rural'] = Currency::comaApunto($post_departamento['area_rural']);
            
            if(Departamento::setDepartamento('update', $post_departamento, array('id'=>$id))){
                Poblacion::setPoblacion('update', Input::post('poblacion'), 'departamento_id', $id); 
                
                Fuente::setFuente('update', Input::post('fuente'), 'departamento', $id);
                                
                Flash::valid('El departamento se ha actualizado correctamente!');
                return Redirect::toAction('listar');
            }            
        }
            
        $this->departamento = $departamento;
        $this->poblacion = $poblacion;
        //$this->fuente = $fuente;
        
        $this->page_title = 'Actualizar departamento';                
        
    }
    
    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_departamento', 'int')) {
            return Redirect::toAction('listar');
        }        
        
                
        $departamento = new Departamento();
        if(!$departamento->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del departamento');            
        } else {
            if($tipo=='inactivar' && $departamento->estado == Departamento::INACTIVO) {
                Flash::info('El departamento ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $departamento->estado == Departamento::ACTIVO) {
                Flash::info('El departamento ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? Departamento::INACTIVO : Departamento::ACTIVO;
                if(Departamento::setDepartamento('update', $departamento->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==Departamento::ACTIVO) ? Flash::valid('El departamento se ha reactivado correctamente!') : Flash::valid('El departamento se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar');
    }
    
    /**
     * Método para ver
     */
    public function ver($key, $order='order.departamento.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;     
        if(!$id = Security::getKey($key, 'show_departamento', 'int')) {
            return Redirect::toAction('listar');
        }        
        
        $departamento = new Departamento();
        if(!$departamento->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del departamento');
            return Redirect::toAction('listar');
        }   
        
        $poblacion = new Poblacion();
        $poblacion = Poblacion::getPoblacion('departamento_id', $id);        
        $this->poblacion = $poblacion;
                                
               
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('departamento', $departamento->id);
        //var_dump($this->fuentes);        die();
        
             
        $this->departamento = $departamento;
        $this->order = $order;        
        $this->page_title = 'Información del Departamento';
        $this->key = $key;        
    }
    
}