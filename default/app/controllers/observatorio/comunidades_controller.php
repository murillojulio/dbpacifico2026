<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los departamentos del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('observatorio/departamento', 'observatorio/municipio', 'observatorio/fuente', 'observatorio/poblacion');
class ComunidadesController extends BackendController {
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Municipio';
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
    public function listar($order='order.municipio.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $municipios = new Municipio();
        $this->municipios = $municipios->getListadoMunicipio('todos', $order, $page);        
        $this->order = $order;        
        $this->page_title = 'Listado de municipios monitoriados';
    }
    
    /**
     * Método para agregar
     */
    public function agregar($departamento_id = NULL, $departamento_nombre = NULL) {
        
        if(Input::hasPost('municipio')) {
            $municipio_obj = new Municipio();
            $municipio_obj = Municipio::setMunicipio('create', Input::post('municipio'), array('estado'=>Municipio::ACTIVO));
            if($municipio_obj)
            {                       
              $municipio_id = $municipio_obj->id;
              
              Poblacion::setPoblacion('create', Input::post('poblacion'),'municipio_id', $municipio_id);
              
              $dataFuente = Input::post('fuente');
              $cantidad_fuente = $dataFuente['cantidad'];
              
              for($i = 1 ; $i <= $cantidad_fuente ; $i++)
              {
                  $array = array(
                                "fecha" => $dataFuente['fecha'.$i],
                                "nombre" => $dataFuente['nombre'.$i],
                                "tabla" => "municipio",
                                "tabla_identi" => $municipio_id,
                                );
                  Fuente::setFuente('create', $array);
              }
              
              Flash::valid('El municipio se ha registrado correctamente!');
              return Redirect::toAction('listar');
                
            }          
        }
        if($departamento_id)
        {
            $this->page_title = 'Agregar municipio al departamento '.$departamento_nombre;            
        }
        else
            { $this->page_title = 'Agregar municipio';}
           
       
        $this->departamento_id = $departamento_id;
        $this->departamento_nombre = $departamento_nombre;
    }
    
    /**
     * Método para editar
     */
    public function editar($key) {        
        if(!$id = Security::getKey($key, 'upd_municipio', 'int')) {
            return Redirect::toAction('listar');
        }        
        
        $municipio = new Municipio();
        $poblacion = new Poblacion();
        $poblacion = Poblacion::getPoblacion('municipio_id',$id);
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('municipio', $id);
        
        if(!$municipio->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del municipio');
            return Redirect::toAction('listar');
        }
        
        if(Input::hasPost('municipio') && Input::hasPost('poblacion')) {
            
            if(Municipio::setMunicipio('update', Input::post('municipio'), array('id'=>$id))){
                Poblacion::setPoblacion('update', Input::post('poblacion'), 'municipio_id', $id); 
                
              $dataFuente = Input::post('fuente');
              $cantidad_fuente = $dataFuente['cantidad'];
              
              for($i = 1 ; $i <= $cantidad_fuente ; $i++)
              {
                  $array = array(
                                "id" => $dataFuente['id'.$i],
                                "fecha" => $dataFuente['fecha'.$i],
                                "nombre" => $dataFuente['nombre'.$i],
                                "tabla" => "municipio",
                                "tabla_identi" => $id,
                                );
                  Fuente::setFuente('update', $array);
              }
                
                Flash::valid('El municipio se ha actualizado correctamente!');
                return Redirect::toAction('listar');
            }            
        }
            
        $this->municipio = $municipio;
        $this->poblacion = $poblacion;
        //$this->fuente = $fuente;
        
        $this->page_title = 'Actualizar municipio';                
        
    }
    
    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_municipio', 'int')) {
            return Redirect::toAction('listar');
        }        
        
                
        $municipio = new Municipio();
        if(!$municipio->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del municipio');            
        } else {
            if($tipo=='inactivar' && $municipio->estado == Municipio::INACTIVO) {
                Flash::info('El municipio ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $municipio->estado == Municipio::ACTIVO) {
                Flash::info('El municipio ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? Municipio::INACTIVO : Municipio::ACTIVO;
                if(Municipio::setMunicipio('update', $municipio->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==Municipio::ACTIVO) ? Flash::valid('El municipio se ha reactivado correctamente!') : Flash::valid('El municipio se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar');
    }
    
    /**
     * Método para ver
     */
    public function ver($key, $order='order.municipio.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;     
        if(!$id = Security::getKey($key, 'show_municipio', 'int')) {
            return Redirect::toAction('listar');
        }    
        
        $municipio = new Municipio();
        if(!$municipio->getInformacionMunicipio($id)) {
            Flash::error('Lo sentimos, no se ha podido establecer la información del municipio');    
            return Redirect::toAction('listar');
        }   
        
        $poblacion = new Poblacion();
        $poblacion = Poblacion::getPoblacion('municipio_id', $id);        
        $this->poblacion = $poblacion;
                                
               
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('municipio', $municipio->id);
        //var_dump($this->fuentes);        die();
        
              
        $this->municipio = $municipio;
        $this->order = $order;        
        $this->page_title = 'Información del Municipio';
        $this->key = $key;        
    }
    
}