<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los departamentos del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('observatorio/departamento', 'observatorio/subregion', 
       'observatorio/municipio', 'observatorio/fuente', 'util/currency');
class SubregionesController extends BackendController {
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Subregión';
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
    public function listar($order='order.subregion.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $subregiones = new Subregion();
        $this->subregiones = $subregiones->getListadoSubregion('todos', $order, $page);        
        $this->order = $order;        
        $this->page_title = 'Listado de subregiones monitoriados';
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
        $subregion     = new Subregion();
        $subregiones    = $subregion->getAjaxSubregion($field, $value, $order, $page);
        if(empty($subregiones->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->subregiones = $subregiones;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de subregiones en el sistema';        
    }
    
    /**
     * Método para agregar
     */
    /**
     * Método para agregar
     */
    public function agregar() {
        $this->page_title = 'Agregar subregión';
        
        if(Input::hasPost('subregion')) {
            $post_subregion = Input::post('subregion');
           
            $post_subregion['area_total'] = Currency::comaApunto($post_subregion['area_total']);
            $post_subregion['area_cabecera'] = Currency::comaApunto($post_subregion['area_cabecera']);
            $post_subregion['area_rural'] = Currency::comaApunto($post_subregion['area_rural']);
            
            $subregion_obj = new Subregion();
            $subregion_obj = Subregion::setSubregion('create', $post_subregion, array('estado'=>Subregion::ACTIVO));
            if($subregion_obj)
            {                       
              $subregion_id = $subregion_obj->id;
              
              Fuente::setFuente('create', Input::post('fuente'), 'subregion', $subregion_id);
              
              Flash::valid('La subregión '.$post_subregion['nombre'].' se ha registrado correctamente!');
              return Redirect::toAction('listar');
                
            }          
        }
    }
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = Security::getKey($key, 'upd_subregion', 'int')) {
            return Redirect::toAction('listar');
        }        
        
        $subregion = new Subregion();        
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('subregion', $id);
        
        if(!$subregion->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del subregion');
            return Redirect::toAction('listar');
        }
        
        if(Input::hasPost('subregion')) {
            
            $post_subregion = Input::post('subregion');
            
            if(Subregion::setSubregion('update', $post_subregion, array('id'=>$id))){                
                Fuente::setFuente('update', Input::post('fuente'), 'subregion', $id);                
                Flash::valid('La subregion se ha actualizado correctamente!');
                return Redirect::toAction('listar');
            }            
        }
            
        $this->subregion = $subregion;
        //$this->fuente = $fuente;
        
        $this->page_title = 'Actualizar subregion';               
        
    }
    
    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_subregion', 'int')) {
            return Redirect::toAction('listar');
        }        
        
                
        $subregion = new Subregion();
        if(!$subregion->getInformacionSubregion($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la subregion');            
        } else {
            if($tipo=='inactivar' && $subregion->estado == Subregion::INACTIVO) {
                Flash::info('La subregion ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $subregion->estado == Subregion::ACTIVO) {
                Flash::info('La subregion ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? Subregion::INACTIVO : Subregion::ACTIVO;
                if(Subregion::setSubregion('update', $subregion->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==Subregion::ACTIVO) ? Flash::valid('La subregion se ha reactivado correctamente!') : Flash::valid('La subregion se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar');
    }
    
    /**
     * Método para ver
     */
    public function ver($key) { 
            
        if(!$id = Security::getKey($key, 'show_subregion', 'int')) {
            return Redirect::toAction('listar');
        }    
        
        $subregion = new Subregion();
        if(!$subregion->getInformacionSubregion($id)) {
            Flash::error('Lo sentimos, no se ha podido establecer la información de la subregion');    
            return Redirect::toAction('listar');
        }   
        
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('subregion', $id);
        //var_dump($this->fuentes);        die();        
              
        $this->subregion = $subregion;
        $this->page_title = 'Información de la Subregión';
        $this->key = $key;        
    }
    
}