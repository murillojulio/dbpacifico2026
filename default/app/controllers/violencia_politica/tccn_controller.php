<?php
/**
 * Descripcion: Controlador que se encarga de los casos de violencia politica
 *
 * @category    
 * @package     Controllers  
 */
Load::models('violencia_politica/victima', 'violencia_politica/caso', 'observatorio/territorio');
class TccnController extends BackendController {
    
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Violencia Política';
        $this->page_title = 'Territorios Colectivos Comunidades Negras';
    }
    
     /**
     * Método principal
     */
    public function index() {
        Redirect::toAction('listar_territorio_cn');
    }
    
    /**
     * Método para listar los territorios colectivos de comunidades negras
     */
    public function listar_territorio_cn($order='order.territorio.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $territorios = new Territorio();
        $this->territorios = $territorios->getListadoTerritorio('comunidad_negra', $order, $page);        
        $this->order = $order;      
        $this->page = $page;
        Session::set('redir_back', 'violencia_politica/tccn/listar_territorio_cn/'.$order.'/'.$page.'/');
        Session::set('query_busqueda_caso', '');
    }
    
    /**
     * Método para listar los territorios colectivos de comunidades negras
     */
    public function listar_caso($territorio_id, $territorio_nombre) { 
        $obj_caso = new Caso();
        $casos = $obj_caso->getCasosByTerritorioId($territorio_id);
        $this->casos = $casos;
        
        $this->page_title = 'Listado de casos del territorio: '.$territorio_nombre;
        $this->page_module = 'Violencia Política';
        //Session::set('redir_back', 'violencia_politica/tccn/listar_caso/'.$territorio_id.'/'.$territorio_nombre.'/');
    }   
    
    /**
     * Método para buscar
     * 
     * @param type $field Nombre del campo a buscar
     * @param type $value Valor del campo
     * @param type $order Método de ordenamiento
     * @param type $page Número de página
     */
    public function buscar_territorio_cn($field='nombre del terriotorio', $value='none', $order='order.id.asc', $page='page.1') {        
        $page       = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field      = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value      = (Input::hasPost('value')) ? Input::post('value') : $value;
        //$tipo       = (Input::hasPost('tipo')) ? Input::post('tipo') : $tipo;
        
        $territorio     = new Territorio();
        $territorios    = $territorio->getAjaxTerritorio($field, $value, $order, $page, $tipo='comunidad_negra');
        if(empty($territorios->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->territorios  = $territorios;
        $this->order        = $order;
        $this->field        = $field;
        $this->value        = $value;  
        $this->page         = $page;
        Session::set('redir_back', 'violencia_politica/tccn/buscar_territorio_cn/'.$field.'/'.$value.'/'.$order.'/'.$page.'/');
    }

}
?>
