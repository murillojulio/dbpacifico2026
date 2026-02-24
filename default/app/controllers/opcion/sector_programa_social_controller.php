
<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los sectores de programas sociales del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('opcion/sector_programa_social');
class SectorProgramaSocialController extends BackendController {
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Sector Programa Social';
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
    public function listar($order='order.sector_programa_social.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $sector_programa_socials = new SectorProgramaSocial();
        $this->sector_programa_socials = $sector_programa_socials->getListadoSectorProgramaSocial('todos', $order, $page);        
        $this->order = $order;        
        $this->page_title = 'Listado de sector programas sociales';
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
        $sector_programa_social     = new SectorProgramaSocial();
        $sector_programa_socials    = $sector_programa_social->getAjaxSectorProgramaSocial($field, $value, $order, $page);
        if(empty($sector_programa_socials->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->sector_programa_socials= $sector_programa_socials;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de sector_programa_socials en el sistema';        
    }
    
    /**
     * Método para agregar
     */
    public function agregar() {
        if(Input::hasPost('sector_programa_social')) {
            $sector_programa_social_obj = new SectorProgramaSocial();
            $sector_programa_social_obj = SectorProgramaSocial::setSectorProgramaSocial('create', Input::post('sector_programa_social'), array('estado'=>SectorProgramaSocial::ACTIVO));
            if($sector_programa_social_obj)
            {                       
             
              Flash::valid('El Sector programa social se ha registrado correctamente!');
              return Redirect::toAction('listar/');
                
            }          
        }
        $this->page_title = 'Agregar Sector programa social';
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = Security::getKey($key, 'upd_sector_programa_social', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $sector_programa_social = new SectorProgramaSocial();
                
        if(!$sector_programa_social->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del sector programa social');
            return Redirect::toAction('listar/');
        }
        
        if(Input::hasPost('sector_programa_social')) {
            
            if(SectorProgramaSocial::setSectorProgramaSocial('update', Input::post('sector_programa_social'), array('id'=>$id))){
               Flash::valid('El sector programa social se ha actualizado correctamente!');
                return Redirect::toAction('listar/');
            }            
        }
            
        $this->sector_programa_social = $sector_programa_social;       
        
        $this->page_title = 'Actualizar sector programa social';                
        
    }
    
    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_sector_programa_social', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
                
        $sector_programa_social = new SectorProgramaSocial();
        if(!$sector_programa_social->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del sector programa social');            
        } else {
            if($tipo=='inactivar' && $sector_programa_social->estado == SectorProgramaSocial::INACTIVO) {
                Flash::info('El sector programa social ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $sector_programa_social->estado == SectorProgramaSocial::ACTIVO) {
                Flash::info('El sector programa social ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? SectorProgramaSocial::INACTIVO : SectorProgramaSocial::ACTIVO;
                if(SectorProgramaSocial::setSectorProgramaSocial('update', $sector_programa_social->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==SectorProgramaSocial::ACTIVO) ? Flash::valid('El sector programa social se ha reactivado correctamente!') : Flash::valid('El sector programa social se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar/');
    }
    
    /**
     * Método para ver
     */
    public function ver($key, $order='order.sector_programa_social.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;     
        if(!$id = Security::getKey($key, 'show_sector_programa_social', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $sector_programa_social = new SectorProgramaSocial();
        if(!$sector_programa_social->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del sector programa social');
            return Redirect::toAction('listar/');
        }           
             
        $this->sector_programa_social = $sector_programa_social;
        $this->order = $order;        
        $this->page_title = 'Información del sector programa social';
        $this->key = $key;        
    }
    
    
    /**
     * Método para eliminar
     */
    public function eliminar($key) {         
        if(!$id = Security::getKey($key, 'eliminar_sector_programa_social', 'int')) {
            return Redirect::toAction('listar/');
        }        
        
        $recurso = new SectorProgramaSocial();
        if(!$recurso->find_first($id)) {
            Flash::error('Lo sentimos, no se ha podido establecer la información del sector programa social');    
            return Redirect::toAction('listar/');
        }              
        try {
            if($recurso->delete()) {
                Flash::valid('El sector programa social se ha eliminado correctamente!');
            } else {
                Flash::warning('Lo sentimos, pero este sector programa social no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este sector programa social no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('listar/');
    }
    
}