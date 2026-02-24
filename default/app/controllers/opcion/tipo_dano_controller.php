
<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los sectores de programas sociales del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('opcion/tipo_dano', 'opcion/dano');
class TipoDanoController extends BackendController {
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Tipos de daños';
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
    public function listar() { 
        $tipos_danos = new TipoDano();
        $this->tipos_danos = $tipos_danos->getListadoTipoDano();   
        $this->page_title = 'Listado de tipos de daños';
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
        if(Input::hasPost('tipo_dano')) {
            $TipoDano = new TipoDano();
            $TipoDano = TipoDano::setTipoDano('create', Input::post('tipo_dano'), array('estado'=> TipoDano::ACTIVO));
            if($TipoDano)
            {     
              Flash::valid('El Tipo de Daño se ha registrado correctamente!');
              return Redirect::toAction('listar/');                
            }          
        }
        $this->page_title = 'Agregar Tipo de Daño';
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = Security::getKey($key, 'upd_tipo_dano', 'int')) {
            return Redirect::toAction('listar/');
        }        
               
        $tipo_dano = new TipoDano();
                
        if(!$tipo_dano->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del Tipo de Daño');
            return Redirect::toAction('listar/');
        }
        
        if(Input::hasPost('tipo_dano')) {
            
            if(TipoDano::setTipoDano('update', Input::post('tipo_dano'), array('id'=>$id))){
               Flash::valid('El Tipo de Daño se ha actualizado correctamente!');
                return Redirect::toAction('listar/');
            }            
        }
            
        $this->tipo_dano = $tipo_dano;    
        $this->danos = $tipo_dano->getDano();
        
        $this->page_title = 'Actualizar Tipo de Daño';                
        
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
    
    /**
     * Método para subir documentos
     */
    public function upload($documento = '') {   
        
        $upload = new DwUpload($documento, 'files/upload/opcion/tipo_dano/'.$documento);
        $upload->setAllowedTypes('pdf|doc|docx');
        //$upload->setAllowedTypes('*');
        $upload->setEncryptNameWithLogin(TRUE);
        //$upload->setSize('50MB', 170, 200, TRUE);
        if(!$data = $upload->save()) { //retorna un array('path'=>'ruta', 'name'=>'nombre.ext');
            $data = array('error'=>TRUE, 'message'=>$upload->getError());
        }
        sleep(1);//Por la velocidad del script no permite que se actualize el archivo
        $this->data = $data;
        View::json();
    }
    
    public function gestion_dano()
    {
        $accion = Input::post('accion');
        if($accion === 'crear'){
            $tipo_dano_id = Input::post('tipo_dano_id');
            $nombre_dano = Input::post('nombre_dano');
            $Dano = new Dano();
            $Dano->tipo_dano_id = $tipo_dano_id;
            $Dano->nombre = $nombre_dano;
            if($Dano->create()){ 
                $this->danos = $Dano->getListadoDano($tipo_dano_id);
                $this->tipo_dano_id = $tipo_dano_id;
            }
        }elseif ($accion === 'eliminar') {
                $tipo_dano_id = Input::post('tipo_dano_id');
                $dano_id = Input::post('dano_id');                
                $Dano = new Dano();
                if($Dano->delete("dano.id = $dano_id")){ 
                    $this->danos = $Dano->getListadoDano($tipo_dano_id);
                    $this->tipo_dano_id = $tipo_dano_id;
                } else {
                }
            }elseif ($accion === 'editar') {
                $tipo_dano_id = Input::post('tipo_dano_id');
                $dano_id = Input::post('dano_id');
                $dano_nombre = Input::post('nombre_dano');
                $Dano = new Dano();
                if($Dano->update_all("dano.nombre = '$dano_nombre'", "conditions: dano.id = $dano_id")){ 
                    $this->danos = $Dano->getListadoDano($tipo_dano_id);
                    $this->tipo_dano_id = $tipo_dano_id;
                } else {
                }
            }
        }
         
       
}