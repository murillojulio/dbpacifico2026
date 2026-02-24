

<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los departamentos del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('observatorio/departamento', 'observatorio/municipio', 'observatorio/subregion',
'observatorio/localidad', 'observatorio/fuente', 'observatorio/poblacion', 'util/currency');
class MunicipiosController extends BackendController {
    
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
   public function listar()
    {
    
        $municipios = new Municipio();
        $this->municipios = $municipios->getListadoMunicipio('todos');
    
        $this->order = $order;
        $this->page_title = 'Listado de municipios monitoriados';
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
        $municipio     = new Municipio();
        $municipios    = $municipio->getAjaxMunicipio($field, $value, $order, $page);
        if(empty($municipios->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->municipios = $municipios;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;
        $this->page_title   = 'Búsqueda de municipios en el sistema';        
    }
    
    /**
     * Método para agregar
     */
    public function agregar($departamento_id = NULL, $departamento_nombre = NULL) {
        
        if(Input::hasPost('municipio')) {
            $post_municipio = Input::post('municipio');
           
            $post_municipio['area_total'] = Currency::comaApunto($post_municipio['area_total']);
            $post_municipio['area_cabecera'] = Currency::comaApunto($post_municipio['area_cabecera']);
            $post_municipio['area_rural'] = Currency::comaApunto($post_municipio['area_rural']);
            
            $municipio_obj = new Municipio();
            $municipio_obj = Municipio::setMunicipio('create', $post_municipio, array('estado'=>Municipio::ACTIVO));
            if($municipio_obj)
            {                       
              $municipio_id = $municipio_obj->id;
              
              Poblacion::setPoblacion('create', Input::post('poblacion'),'municipio_id', $municipio_id);
              
              Fuente::setFuente('create', Input::post('fuente'), 'municipio', $municipio_id);
              
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
        
        if(!$municipio->getInformacionMunicipio($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del municipio');
            return Redirect::toAction('listar');
        }
        
        if(Input::hasPost('municipio') && Input::hasPost('poblacion')) {
            
            $post_municipio = Input::post('municipio');
           
            $post_municipio['area_total'] = Currency::comaApunto($post_municipio['area_total']);
            $post_municipio['area_cabecera'] = Currency::comaApunto($post_municipio['area_cabecera']);
            $post_municipio['area_rural'] = Currency::comaApunto($post_municipio['area_rural']);
            
            if(Municipio::setMunicipio('update', $post_municipio, array('id'=>$id))){
                Poblacion::setPoblacion('update', Input::post('poblacion'), 'municipio_id', $id); 
                
                Fuente::setFuente('update', Input::post('fuente'), 'municipio', $id);
                
                Flash::valid('El municipio se ha actualizado correctamente!');
                return Redirect::toAction('listar');
            }            
        }
            
        $this->municipio = $municipio;
        $this->poblacion = $poblacion;
        $this->localidades = $municipio->getLocalidad();
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

    public function agregar_localidad()
    {
        $Localidad = new Localidad();
        $Municipio = new Municipio();
        $municipio_id = Input::post('municipio_id');
        $tipo = Input::post('tipo');
        $nombre = Input::post('nombre');

        $Municipio->find_first($municipio_id);

        $Localidad->municipio_id = $municipio_id;
        $Localidad->tipo = $tipo;
        $Localidad->nombre = $nombre;
        $Localidad->save();        
        $this->municipio_id = $municipio_id;
        $this->tipo = $tipo;
        $this->localidad_id = $Localidad->id;
        $this->localidades = $Municipio->getLocalidad();
        View::template(null);
        View::select('list_localidades');            
    }

    public function editar_localidad()
    {
        $Localidad = new Localidad();
        $Municipio = new Municipio();
        $municipio_id = Input::post('municipio_id');
        $localidad_id = Input::post('localidad_id');
        $tipo = Input::post('tipo');
        $localidad_nombre = Input::post('localidad_nombre');
        $Localidad->find_first($localidad_id);
        $Localidad->nombre = $localidad_nombre;
        $Localidad->update();

        $Municipio->find_first($municipio_id);   
        $this->municipio_id = $municipio_id;
        $this->tipo = $tipo;
        $this->localidad_id = $Localidad->id;
        $this->localidades = $Municipio->getLocalidad();
        View::template(null);
        View::select('list_localidades');            
    }

   public function eliminar_localidad()
{
    Load::lib('DeleteService');
    $Municipio = new Municipio();
    $delete    = new DeleteService();

    $municipio_id = Input::post('municipio_id');
    $localidad_id = Input::post('localidad_id');
    $tipo         = Input::post('tipo');

    $this->alert = null;

    if (!$localidad_id) {
        $this->alert = [
            'type' => 'warning',
            'text' => 'Localidad inválida.'
        ];
    } else {

        $result = $delete->delete('localidad', $localidad_id);

        if (is_array($result) && isset($result['error'])) {

            $msgs = [];

            foreach ($result['bloqueos'] as $b) {
                $msgs[] = ucfirst($b['tabla']) .
                          ' (IDs: ' . implode(', ', $b['ids']) . ')';
            }

            $this->alert = [
                'type' => 'warning',
                'text' => 'No se puede eliminar la localidad porque está asociada a: ' .
                          implode(' | ', $msgs)
            ];

        } else {
            $this->alert = [
                'type' => 'success',
                'text' => 'Localidad eliminada correctamente.'
            ];
        }
    }

    // Continuar flujo
    $Municipio->find_first($municipio_id);

    $this->municipio_id = $municipio_id;
    $this->tipo         = $tipo;
    $this->localidad_id = $localidad_id;
    $this->localidades  = $Municipio->getLocalidad();

    View::template(null);
    View::select('list_localidades');
}




    public function list_localidades(){ View::template(null);}

    public function cargar_modal_editar_localidad(){
        $localidad_id = Input::post('localidad_id');
        $Localidad = new Localidad();
        $this->Localidad = $Localidad->find_first($localidad_id);
        View::template(null);
    }
    
}