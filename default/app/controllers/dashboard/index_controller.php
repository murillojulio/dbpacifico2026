<?php
/**
 *
 * Descripcion: Controlador para el panel principal de los usuarios logueados
 *
 * @category    
 * @package     Controllers 
 */

Load::models('observatorio/departamento');

class IndexController extends BackendController {
    
    public $page_title = 'Principal';
    
    public $page_module = 'Dashboard';
    
    
    public function index() { 
        $departamentos = new Departamento();
        $this->departamentos = $departamentos->getDepartamentosEstadistica('cant_territorio_reg DESC');
        
        
        $str_data = '';
        $str_categories ='';
        $str_municipios = '';
        $str_territorios = '';
        $str_comunidad_negra = '';
        $str_indigenas = '';
        
        
       foreach($this->departamentos as $departamento){
          $str_data = $str_data.'{name: "'.$departamento->nombre.'", y:'.$departamento->cant_territorio_reg.', drilldown: "'.$departamento->nombre.'"},';          
          $str_categories = $str_categories.'"'.$departamento->nombre.'" ,';
          $str_municipios = $str_municipios.$departamento->cant_municipio_reg.',';
          $str_territorios = $str_territorios.$departamento->cant_territorio_reg.',';
          $str_comunidad_negra = $str_comunidad_negra.$departamento->cant_territorio_comunidad_negra.',';
          $str_indigenas = $str_indigenas.$departamento->cant_territorio_indigena.',';
       }
       
       $this->str_data = substr($str_data, 0, -1);
       $this->str_categories = substr($str_categories, 0, -1);
       $this->str_municipios = substr($str_municipios, 0, -1);
       $this->str_territorios = substr($str_territorios, 0, -1);
       $this->str_comunidad_negra = substr($str_comunidad_negra, 0, -1);
       $this->str_indigenas = substr($str_indigenas, 0, -1);
        
    }

}
