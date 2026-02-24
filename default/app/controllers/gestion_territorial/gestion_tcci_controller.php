<?php
/**
 * Descripcion: Controlador que se encarga de la gestión de los territorios del observatorio
 *
 * @category    
 * @package     Controllers  
 */
Load::models('observatorio/fuente', 'observatorio/comunidad','observatorio/territorio', 
        'gestion_territorial/cabildo', 'gestion_territorial/cabildo_autoridad_tradicional',
        'gestion_territorial/jurisdiccion_especial_indigena', 'gestion_territorial/plan_de_vida',
         'gestion_territorial/iniciativa_empresarial', 'gestion_territorial/descripcion_afectacion_impacto',
        'opcion/asociacion_cabildo_regional', 'gestion_territorial/accion_exigibilidad_derecho',
        'opcion/tipo_accion_exigibilidad_derecho', 'opcion/tipo_iniciativa_empresarial', 
        'opcion/tipo_actividad_productiva', 'gestion_territorial/descripcion_afectacion',
        'opcion/asociacion_cabildo_zonal', 'opcion/tipo_autoridad_tradicional',
        'opcion/autoridad_tradicional',
        'afectacion/afectacion',
        'afectacion/afectacion_dano_territorio',
        'opcion/dano',
        'opcion/tipo_dano');
class GestionTcciController extends BackendController {
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Gestión Territorial';
    }
    
     /**
     * Método principal
     */
    public function index() {
        Redirect::toAction('listar');
    }
    
     /**
     * Método para listar los territorios colectivos que pertenecen a un municipio
     */
    public function listar_territorio($municipio_id, $municipio_nombre, $order='order.territorio.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $territorios = new TerritorioMunicipio();
        $this->territorios = $territorios->getTerritoriosByMunicipioId($municipio_id, $order, $page);        
        $this->order = $order;      
        $this->page = $page;
        $this->municipio_id = $municipio_id;
        $this->municipio_nombre = $municipio_nombre;
        $this->page_title = 'Territorios que pertenecen al municipio: '.$municipio_nombre;
        $this->page_module = 'Territorios Colectivos';
    }
    
    /**
     * Método para listar los territorios colectivos de comunidades negras
     */
    public function listar_territorio_ci($order='order.territorio.asc', $page='page.1') { 
        $page = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $territorios = new Territorio();
        $this->territorios = $territorios->getListadoTerritorio('indigena', $order, $page);        
        $this->order = $order;      
        $this->page = $page;
        //$this->page_title = 'Listado de territorios monitoriados';
        $this->page_title = Territorio::TERRITORIO_INDIGENA;
    }
        
     /**
     * Método para buscar
     * 
     * @param type $field Nombre del campo a buscar
     * @param type $value Valor del campo
     * @param type $order Método de ordenamiento
     * @param type $page Número de página
     */
    public function buscar_territorio_ci($field='nombre', $value='none', $order='order.id.asc', $page='page.1') {        
        $page       = (Filter::get($page, 'page') > 0) ? Filter::get($page, 'page') : 1;
        $field      = (Input::hasPost('field')) ? Input::post('field') : $field;
        $value      = (Input::hasPost('value')) ? Input::post('value') : $value;
        //$tipo       = (Input::hasPost('tipo')) ? Input::post('tipo') : $tipo;
        
        $territorio     = new Territorio();
        $territorios    = $territorio->getAjaxTerritorio($field, $value, $order, $page, $tipo='indigena');
        if(empty($territorios->items)) {
            Flash::info('No se han encontrado registros');
        }
        $this->territorios  = $territorios;
        $this->order = $order;      
        $this->page = $page;
        $this->field        = $field;
        $this->value        = $value;
        //$this->page_title   = 'Búsqueda de territorios en el sistema';  
        $this->page_title = 'Búsqueda de '.Territorio::TERRITORIO_INDIGENA;
    }  
    
    
       
    /**
     * Método para inactivar/reactivar
     */
    public function estado($tipo, $key) {
        if(!$id = Security::getKey($key, $tipo.'_territorio', 'int')) {
            return Redirect::toAction('listar');
        }        
        
        $territorio = new Territorio();
        if(!$territorio->find_first($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del territorio');            
        } else {
            if($tipo=='inactivar' && $territorio->estado == Territorio::INACTIVO) {
                Flash::info('El territorio ya se encuentra inactivo');
            } else if($tipo=='reactivar' && $territorio->estado == Territorio::ACTIVO) {
                Flash::info('El territorio ya se encuentra activo');
            } else {
                $estado = ($tipo=='inactivar') ? Territorio::INACTIVO : Territorio::ACTIVO;
                if(Territorio::setTerritorio('update', $territorio->to_array(), array('id'=>$id, 'estado'=>$estado))){
                    ($estado==Territorio::ACTIVO) ? Flash::valid('El territorio se ha reactivado correctamente!') : Flash::valid('El territorio se ha bloqueado correctamente!');
                }
            }                
        }
        
        return Redirect::toAction('listar');
    }
    
    /**
     * Método para ver
     */
    public function ver($key, $tab, $order, $page) { 
       
        if(!$id = Security::getKey($key, 'show_territorio', 'int')) {
            return Redirect::toAction('listar');
        }            
        
        //Para saber que pestaña estara activa cuando visualice un territorio
        $this->tab_1_active = '';
        $this->tab_2_active = '';
        $this->tab_3_active = '';
        $this->tab_4_active = '';
        $this->tab_5_active = '';
        
        if($tab == 1){ $this->tab_1_active = 'active'; }
        if($tab == 2){ $this->tab_2_active = 'active'; }
        if($tab == 3){ $this->tab_3_active = 'active'; }
        if($tab == 4){ $this->tab_4_active = 'active'; }
        if($tab == 5){ $this->tab_5_active = 'active'; }
        
        //Para saber que subpestaña estara activa cuando visualice un vinculacion de poblacion
         $this->sub_tab = '1';
            
        
        $obj_territorio = new Territorio();        
        if(!$obj_territorio->getTerritorioById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del territorio');
            return Redirect::toAction('listar_ci');
        } 
        $this->obj_territorio = $obj_territorio;
        
        $obj_cabildos = new Cabildo();
        $this->cabildos = $obj_cabildos->getCabildosByTerritorioId($id);
        
        $obj_accion_exigibilidad_derechos = new AccionExigibilidadDerecho();
        $this->accion_exigibilidad_derechos = $obj_accion_exigibilidad_derechos->getAccionExigibilidadDerechosByTerritorioId($id);
         
        $obj_iniciativa_empresarials = new IniciativaEmpresarial();
        $this->iniciativa_empresarials = $obj_iniciativa_empresarials->getIniciativaEmpresarialsByTerritorioId($id);
        
        $this->url_redir_back = 'gestion_territorial/gestion_tcci/listar_territorio_ci/'.$order.'/'.$page.'/';
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('territorio', $obj_territorio->id);
        
        $this->controller = 'gestion_tcci';
        $this->page_module = 'Gestión Territorial';
        $this->page_title = 'Información del Territorio: '.$obj_territorio->nombre;
        $this->tab = $tab;
        $this->key = $key;        
        $this->order = $order;
        $this->page = $page;        
    }
    
     /**
     * Método para editar
     */
    public function editar($key, $tab, $order, $page) { 
       
        if(!$id = Security::getKey($key, 'upd_territorio', 'int')) {
            return Redirect::toAction('listar_ci');
        }   
         
        //Para saber que pestaña estara activa cuando visualice un territorio
        $this->tab_1_active = '';
        $this->tab_2_active = '';
        $this->tab_3_active = '';
        $this->tab_4_active = '';
        $this->tab_5_active = '';
        
        if($tab == 1){ $this->tab_1_active = 'active'; }
        if($tab == 2){ $this->tab_2_active = 'active'; }
        if($tab == 3){ $this->tab_3_active = 'active'; }
        if($tab == 4){ $this->tab_4_active = 'active'; }
        if($tab == 5){ $this->tab_5_active = 'active'; }
        
        //Para saber que subpestaña estara activa cuando visualice un vinculacion de poblacion
        $this->sub_tab = '1';//$sub_tab;
                      
        $obj_territorio = new Territorio();        
        if(!$obj_territorio->getTerritorioById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del territorio');
            return Redirect::toAction('listar_ci');
        } 
        $this->obj_territorio = $obj_territorio;
        
        $jei = new JurisdiccionEspecialIndigena();
        //$jei = $obj_territorio->getJurisdiccionEspecialIndigena();
        $jei->find_first("territorio_id = $id");
        $this->jurisdiccion_especial_indigena = $jei;
        
        $pdv = new PlanDeVida();
        //$pdv = $obj_territorio->getPlanDeVida();
        $pdv->find_first("territorio_id = $id");
        $this->plan_de_vida = $pdv;
        
        if(Input::hasPost('jurisdiccion_especial_indigena')) 
        {     
            if ($jei->id == '')
            {
                $jei = new JurisdiccionEspecialIndigena(Input::post('jurisdiccion_especial_indigena'));
                $jei->create();
            }
            else 
            {             
                $jei = new JurisdiccionEspecialIndigena(Input::post('jurisdiccion_especial_indigena'));
                $jei->update();               
            }
             if ($pdv->id == '')
            {
                $pdv = new PlanDeVida(Input::post('plan_de_vida'));
                $pdv->create();
            }
            else 
            {                   
                $pdv = new PlanDeVida(Input::post('plan_de_vida'));
                $pdv->update();               
            }
                          
            Flash::valid('La gestión territorial se ha actualizado correctamente!');
            //  return Redirect::toAction('listar');
                
                  
        }
        
        $obj_cabildos = new Cabildo();
        $this->cabildos = $obj_cabildos->getCabildosByTerritorioId($id);
        
        $obj_accion_exigibilidad_derechos = new AccionExigibilidadDerecho();
        $this->accion_exigibilidad_derechos = $obj_accion_exigibilidad_derechos->getAccionExigibilidadDerechosByTerritorioId($id);
        
        $obj_iniciativa_empresarials = new IniciativaEmpresarial();
        $this->iniciativa_empresarials = $obj_iniciativa_empresarials->getIniciativaEmpresarialsByTerritorioId($id);
        
        $cabildo = $obj_cabildos->getCabildoByTerritorioIdAndTipoCabildoId($id, 1);
        $cabildo_mayor = $obj_cabildos->getCabildoByTerritorioIdAndTipoCabildoId($id, 2);
        $cabildo_local = $obj_cabildos->getCabildoByTerritorioIdAndTipoCabildoId($id, 3);
        $this->array_validacion_cabildo = array('cabildo'=>$cabildo, 'cabildo_mayor'=>$cabildo_mayor, 'cabildo_local'=>$cabildo_local);
                 
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('territorio', $obj_territorio->id);
        
        $this->controller = 'gestion_tcci';
        $this->url_redir_back = 'gestion_territorial/gestion_tcci/listar_territorio_ci/'.$order.'/'.$page.'/';
        $this->page_module = 'Gestión Territorial';
        $this->page_title = 'Actualizar Territorio: '.$obj_territorio->nombre;
        $this->tab = $tab;
        $this->key = $key;        
        $this->order = $order;
        $this->page = $page;
    }
    
    public function agregar_cabildo($tipo_cabildo_id, $territorio_id, $territorio_nombre, $key_back, $order, $page)
    { 
        $obj_cabildo = new Cabildo();
        if(Input::hasPost('cabildo')) 
        {        
            $obj_cabildo = Cabildo::setCabildo('create', Input::post('cabildo'), $territorio_nombre, array('tipo_cabildo_id'=>$tipo_cabildo_id, 'estado'=>  Cabildo::ACTIVO));
            $cabildo_id = $obj_cabildo->id;   
            
            $cabildo_autoridad_tradicional = new CabildoAutoridadTradicional();
            $cabildo_autoridad_tradicional->guardar(Input::post('autoridad_tradicional'), $cabildo_id);
            
            Fuente::setFuente('create', Input::post('fuente'), 'cabildo', $cabildo_id);
            
//            if($tipo_cabildo_id == 3){
//                $dataComunidad = Input::post('comunidad');
//                foreach ($dataComunidad as $value) {
//                $obj_Comunidad = new Comunidad();
//                $obj_Comunidad->id = $value;
//                $obj_Comunidad->cabildo_id = $cabildo_id;
//                $obj_Comunidad->sql('UPDATE comunidad SET comunidad.cabildo_id = '.$cabildo_id.' WHERE comunidad.id ='.$value);  
//                }
//            }
            
            Flash::valid('El cabildo se ha registrado correctamente!');
            return Redirect::toAction('editar/'.$key_back.'/1/'.$order.'/'.$page.'/');
        }
        
        $this->territorio_id = $territorio_id;
        $this->tipo_cabildo_id = $tipo_cabildo_id;
        if ($tipo_cabildo_id == '1')
        {
            $this->page_title = 'Agregar Cabildo al territorio: '.$territorio_nombre;
        }
        elseif ($tipo_cabildo_id == '2')
        {
            $this->page_title = 'Agregar Cabildo Mayor al territorio: '.$territorio_nombre;
        }
        elseif ($tipo_cabildo_id == '3')
        {
            $obj_cabildo->find_by_sql("SELECT * FROM cabildo WHERE territorio_id=".$territorio_id." AND tipo_cabildo_id = 2");
            $this->cabildo_id = $obj_cabildo->id;
            $this->cabildo_mayor_nombre = $obj_cabildo->nombre;
            $this->page_title = 'Agregar Cabildo Local al territorio: '.$territorio_nombre;
        }
        $this->url_redir_back = 'gestion_territorial/gestion_tcci/editar/'.$key_back.'/1/'.$order.'/'.$page.'/';        
        $this->page_module = 'Gestion Territorial';        
    }
    
   public function ver_cabildo($key, $key_back, $order, $page) { 
       
        if(!$id = Security::getKey($key, 'show_cabildo', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $obj_cabildo = new Cabildo();        
        if(!$obj_cabildo->getCabildoById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del cabildo');
            return Redirect::toAction('ver/'.$key_back.'/3/');
        }   
        
        $this->cabildo = $obj_cabildo; 
        
        if($obj_cabildo->tipo_cabildo_id == 3 || $obj_cabildo->tipo_cabildo_id == 1)
        {
             $cabildo_autoridad_tradicional = new CabildoAutoridadTradicional();        
        foreach ($cabildo_autoridad_tradicional->getCabildoAutoridadTradicionalByCabildoId($id) as $value) {
            $clave = $value->autoridad_tradicional_id;           
            $this->autoridad_tradicional[$clave] = $value->cantidad;
        }
            
        }        
        if ($obj_cabildo->tipo_cabildo_id == '1')
        {
            $this->page_title = 'Información Cabildo del territorio: '.$obj_cabildo->territorio;
        }
        elseif ($obj_cabildo->tipo_cabildo_id == '2')
        {
            $this->page_title = 'Información Cabildo Mayor del territorio: '.$obj_cabildo->territorio;
        }
        elseif ($obj_cabildo->tipo_cabildo_id == '3')
        {   
            $this->cabildo_mayor_nombre = $obj_cabildo->nombre;
            $this->page_title = 'Información Cabildo Local del territorio: '.$obj_cabildo->territorio;
        }
        
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('cabildo', $obj_cabildo->id);
        
        $this->page_module = 'Gestión Territorial'; 
                   
        $this->url_redir_back = 'gestion_territorial/gestion_tcci/ver/'.$key_back.'/1/'.$order.'/'.$page.'/';        
        $this->tipo_cabildo_id = $obj_cabildo->tipo_cabildo_id;           
        $this->key_back = $key_back;    
        $this->key = $key;           
        
    }
    
     public function editar_cabildo($key, $key_back, $order, $page) { 
       
        if(!$id = Security::getKey($key, 'upd_cabildo', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $obj_cabildo = new Cabildo();        
        if(!$obj_cabildo->getCabildoById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del cabildo');
            return Redirect::toAction('ver/'.$key_back.'/3/');
        }   
        
        if(Input::hasPost('cabildo')) 
        {        
            $obj_cabildo = Cabildo::setCabildo('update', Input::post('cabildo'), $obj_cabildo->territorio, array('estado'=> Cabildo::ACTIVO));
            
            if($obj_cabildo)
            {
                
                if($obj_cabildo->tipo_cabildo_id == 3 || $obj_cabildo->tipo_cabildo_id == 1)
                {
                    $cabildo_autoridad_tradicional = new CabildoAutoridadTradicional();
                    $cabildo_autoridad_tradicional->guardar(Input::post('autoridad_tradicional'), $obj_cabildo->id);
                }
                Fuente::setFuente('update', Input::post('fuente'), 'cabildo', $id);
                Flash::valid('El Cabildo se ha actualizado correctamente!');
            }
            return Redirect::toAction('editar/'.$key_back.'/1/'.$order.'/'.$page.'/');
           
        }
        
        $this->cabildo = $obj_cabildo; 
        
        if($obj_cabildo->tipo_cabildo_id == 3 || $obj_cabildo->tipo_cabildo_id == 1)
        {
             $cabildo_autoridad_tradicional = new CabildoAutoridadTradicional();        
        foreach ($cabildo_autoridad_tradicional->getCabildoAutoridadTradicionalByCabildoId($id) as $value) {
            $clave = $value->autoridad_tradicional_id;           
            $this->autoridad_tradicional[$clave] = $value->cantidad;
        }
            
        } 
         
        
        if ($obj_cabildo->tipo_cabildo_id == '1')
        {
            $this->page_title = 'Actualizar Cabildo del territorio: '.$obj_cabildo->territorio;
        }
        elseif ($obj_cabildo->tipo_cabildo_id == '2')
        {
            $this->page_title = 'Actualizar Cabildo Mayor del territorio: '.$obj_cabildo->territorio;
        }
        elseif ($obj_cabildo->tipo_cabildo_id == '3')
        {   $this->cabildo_mayor_nombre = $obj_cabildo->nombre;         
            $this->page_title = 'Actualizar Cabildo Local del territorio: '.$obj_cabildo->territorio;
        }
        
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('cabildo', $id);
        
        $this->tipo_cabildo_id = $obj_cabildo->tipo_cabildo_id; 
        $this->page_module = 'Gestión Territorial';        
        $this->url_redir_back = 'gestion_territorial/gestion_tcci/editar/'.$key_back.'/1/'.$order.'/'.$page.'/';                           
        $this->key_back = $key_back;    
        $this->key = $key;    
    }
    
     /**
     * Método para eliminar
     */
    public function eliminar_cabildo($nombre_cabildo, $nombre_territorio, $key, $key_back, $order, $page) {      
                
        if(!$id = Security::getKey($key, 'del_cabildo', 'int')) {
        return Redirect::to(/*$url_redir_back*/);
        }        
        
        $cabildo = new Cabildo();
                    
        try {
            if($cabildo->delete($id)) {
                Flash::valid("El cabildo $nombre_cabildo se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO el cabildo $nombre_cabildo, pertenecia al territorio $nombre_territorio.");
            } else {
                Flash::warning('Lo sentimos, pero este cabildo no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este cabildo no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('editar/'.$key_back.'/1/'.$order.'/'.$page.'/');
    }
    
    
    
    
    public function agregar_accion_exigibilidad_derecho($territorio_id, $territorio_nombre, $key_back, $order, $page)
    { 
        $obj_aed = new AccionExigibilidadDerecho();
        if(Input::hasPost('accion_exigibilidad_derecho')) 
        {        
            $obj_aed = AccionExigibilidadDerecho::setAccionExigibilidadDerecho('create', Input::post('accion_exigibilidad_derecho'), $territorio_nombre, array('estado'=>  AccionExigibilidadDerecho::ACTIVO));
            
            if($obj_aed)
            {
                Fuente::setFuente('create', Input::post('fuente'), 'accion_exigibilidad_derecho', $obj_aed->id);
                Flash::valid('La acción de exigibilidad de derecho se ha registrado correctamente!');
                return Redirect::toAction('editar/'.$key_back.'/2/'.$order.'/'.$page.'/');
            }
        }
        
        $this->territorio_id = $territorio_id;
        $this->url_redir_back = 'gestion_territorial/gestion_tcci/editar/'.$key_back.'/2/'.$order.'/'.$page.'/';
        $this->page_title = 'Agregar Acción Exigibilidad Derecho al territorio: '.$territorio_nombre;
        $this->page_module = 'Gestion Territorial';        
    }
    
         public function editar_accion_exigibilidad_derecho($key, $key_back, $order, $page) { 
       
        if(!$id = Security::getKey($key, 'upd_accion_exigibilidad_derecho', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $obj_accion_exigibilidad_derecho = new AccionExigibilidadDerecho();        
        if(!$obj_accion_exigibilidad_derecho->getAccionExigibilidadDerechoById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la accion exigibilidad derecho');
            return Redirect::toAction('ver/'.$key_back.'/3/');
        }   
        
        if(Input::hasPost('accion_exigibilidad_derecho')) 
        {        
            $obj_accion_exigibilidad_derecho = AccionExigibilidadDerecho::setAccionExigibilidadDerecho('update', Input::post('accion_exigibilidad_derecho'), $obj_accion_exigibilidad_derecho->territorio, array('estado'=> AccionExigibilidadDerecho::ACTIVO));
            
            if($obj_accion_exigibilidad_derecho)
            {
                Fuente::setFuente('update', Input::post('fuente'), 'accion_exigibilidad_derecho', $id);
                Flash::valid('La Accion Exigibilidad Derecho se ha actualizado correctamente!');
            }
            return Redirect::toAction('editar/'.$key_back.'/2/'.$order.'/'.$page.'/');
           
        }
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('accion_exigibilidad_derecho', $obj_accion_exigibilidad_derecho->id);

        
        $this->accion_exigibilidad_derecho = $obj_accion_exigibilidad_derecho;   
        $this->page_title = 'Actualizar Accion Exigibilidad Derecho del territorio: '.$obj_accion_exigibilidad_derecho->territorio;        
                
        $this->page_module = 'Gestión Territorial';        
        $this->url_redir_back = 'gestion_territorial/gestion_tcci/editar/'.$key_back.'/2/'.$order.'/'.$page.'/';                           
        $this->key_back = $key_back;    
        $this->key = $key;           
    }
    
     public function ver_accion_exigibilidad_derecho($key, $key_back, $order, $page) { 
       
        if(!$id = Security::getKey($key, 'show_accion_exigibilidad_derecho', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $obj_accion_exigibilidad_derecho = new AccionExigibilidadDerecho();        
        if(!$obj_accion_exigibilidad_derecho->getAccionExigibilidadDerechoById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la accion exigibilidad derecho');
            return Redirect::toAction('ver/'.$key_back.'/3/');
        }   
        
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('accion_exigibilidad_derecho', $obj_accion_exigibilidad_derecho->id);
                
        $this->accion_exigibilidad_derecho = $obj_accion_exigibilidad_derecho;   
        $this->page_title = 'Información Acción Exigibilidad Derecho del territorio: '.$obj_accion_exigibilidad_derecho->territorio;        
                
        $this->page_module = 'Gestión Territorial';        
        $this->url_redir_back = 'gestion_territorial/gestion_tcci/ver/'.$key_back.'/2/'.$order.'/'.$page.'/';  
        
        $this->key_back = $key_back;    
        $this->key = $key;                   
    }
    
    
    /**
     * Método para eliminar
     */
    public function eliminar_accion_exigibilidad_derecho($nombre_accion_exigibilidad_derecho, $nombre_territorio, $key, $key_back, $order, $page) {      
                
        if(!$id = Security::getKey($key, 'del_accion_exigibilidad_derecho', 'int')) {
        return Redirect::to(/*$url_redir_back*/);
        }        
        
        $accion_exigibilidad_derecho = new AccionExigibilidadDerecho();
                    
        try {
            if($accion_exigibilidad_derecho->delete($id)) {
                Flash::valid("La acción exigibilidad derecho $nombre_accion_exigibilidad_derecho se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO la acción exigibilidad derecho $nombre_accion_exigibilidad_derecho, pertenecia al territorio $nombre_territorio.");
            } else {
                Flash::warning('Lo sentimos, pero este acción exigibilidad derecho no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Esta acción exigibilidad derecho no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('editar/'.$key_back.'/2/'.$order.'/'.$page.'/');
    }
    
    
    /************************************************
     * **********************************************
     * **********************************************
     * 
     */

    public function agregar_iniciativa_empresarial($territorio_id, $territorio_nombre, $key_back, $order, $page)
    {
        $obj_ie = new IniciativaEmpresarial();
        if (Input::hasPost('iniciativa_empresarial')) {
            $afectacion_obj = Afectacion::setAfectacion('create', array('tipo_afectacion_id' => '8'));
            $obj_ie = IniciativaEmpresarial::setIniciativaEmpresarial('create', Input::post('iniciativa_empresarial'), $territorio_nombre, array('afectacion_id'=>$afectacion_obj->id, 'estado' =>  IniciativaEmpresarial::ACTIVO));

            if ($obj_ie) {
                Fuente::setFuente('create', Input::post('fuente'), 'iniciativa_empresarial', $obj_ie->id);
                Flash::valid('La iniciativa empresarial se ha registrado correctamente!');
                $key_upd = Security::setKey($obj_ie->id, 'upd_iniciativa_empresarial');
                return Redirect::toAction("editar_iniciativa_empresarial/$key_upd/$key_back/$order/3/1/");
            }
        }

        $this->territorio_nombre = $territorio_nombre;
        $this->territorio_id = $territorio_id;
        $this->url_redir_back = 'gestion_territorial/gestion_tcci/editar/' . $key_back . '/3/' . $order . '/' . $page . '/';
        $this->page_title = 'Agregar Iniciativa Empresarial al territorio: ' . $territorio_nombre;
        $this->page_module = 'Gestion Territorial';
    }

    public function editar_iniciativa_empresarial($key, $key_back, $order, $page, $recien_creado)
    {
        if (!$id = Security::getKey($key, 'upd_iniciativa_empresarial', 'int')) {
            return Redirect::toAction('listar');
        }

        $obj_iniciativa_empresarial = new IniciativaEmpresarial();
        if (!$obj_iniciativa_empresarial->getIniciativaEmpresarialById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la iniciativa empresarial');
            return Redirect::toAction('ver/' . $key_back . '/3/');
        }
        
        if (Input::hasPost('iniciativa_empresarial')) {
            $obj_ie = IniciativaEmpresarial::setIniciativaEmpresarial('update', Input::post('iniciativa_empresarial'), $obj_iniciativa_empresarial->territorio, array('estado' => IniciativaEmpresarial::ACTIVO));

            if ($obj_ie) {
                Fuente::setFuente('update', Input::post('fuente'), 'iniciativa_empresarial', $id);
                Flash::valid('La Iniciativa Empresarial se ha actualizado correctamente!');
            }
            return Redirect::toAction('editar/' . $key_back . '/3/' . $order . '/' . $page . '/');
        }

        $AfectacionDanoTerritorio = new AfectacionDanoTerritorio();
        $this->AfectacionDanoTerritorio = $AfectacionDanoTerritorio->getDanoTerritorioByAfectacionId($obj_iniciativa_empresarial->afectacion_id);

        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('iniciativa_empresarial', $id);

        $this->iniciativa_empresarial = $obj_iniciativa_empresarial;
        $this->page_title = 'Actualizar Iniciativa Empresarial '.$obj_iniciativa_empresarial->nombre.' del territorio: ' . $obj_iniciativa_empresarial->territorio;
        $this->territorio_nombre = $obj_iniciativa_empresarial->territorio;
        $this->page_module = 'Gestión Territorial';
        $this->url_redir_back = 'gestion_territorial/gestion_tcci/editar/' . $key_back . '/3/' . $order . '/' . $page . '/';
        $this->key_back = $key_back;
        $this->key = $key;
        $this->recien_creado = $recien_creado;
    }

     
     public function agregar_iniciativa_empresarial_($territorio_id, $territorio_nombre, $key_back, $order, $page)
    { 
        $obj_ie = new IniciativaEmpresarial();
        if(Input::hasPost('iniciativa_empresarial')) 
        {        
            $obj_ie = IniciativaEmpresarial::setIniciativaEmpresarial('create', Input::post('iniciativa_empresarial'), $territorio_nombre, array('estado'=>  IniciativaEmpresarial::ACTIVO));
            
            if($obj_ie)
            {
            $iniciativa_empresarial_id = $obj_ie->id;
            $iniciativa_empresarial_nombre = $obj_ie->nombre;
            $data_impacto_ambiental    = Input::post('impacto_ambiental');
            $data_impacto_cultural     = Input::post('impacto_cultural');
            $data_impacto_economico    = Input::post('impacto_economico');
            $data_impacto_social       = Input::post('impacto_social');
            $data_impacto_organizacion = Input::post('impacto_organizacion');
            $data_impacto_territorial  = Input::post('impacto_territorial');
            
            $obj_dai = new DescripcionAfectacionImpacto();
            if($data_impacto_ambiental['si_no'] == 'SI')
            { 
                $obj_descripcion_afectacion = 
                DescripcionAfectacion::setDescripcionAfectacion('create',                          
                        array('iniciativa_empresarial_id'=>$iniciativa_empresarial_id,
                            'tipo_impacto_id'=>'1',
                            'descripcion'=>$data_impacto_ambiental['descripcion'],
                              'estado'=>  DescripcionAfectacion::ACTIVO), 
                        $iniciativa_empresarial_nombre, null, 'Impacto Ambiental'); 
                
                $descripcion_afectacion_id = $obj_descripcion_afectacion->id;                
                
                if($obj_descripcion_afectacion)
                {
                    
                    $obj_dai->guardar($obj_descripcion_afectacion->id, Input::post('checks_impacto_ambiental'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            if($data_impacto_cultural['si_no'] == 'SI')
            { 
                $obj_descripcion_afectacion = 
                DescripcionAfectacion::setDescripcionAfectacion('create', 
                        array('iniciativa_empresarial_id'=>$iniciativa_empresarial_id,
                            'tipo_impacto_id'=>'2',
                            'descripcion'=>$data_impacto_cultural['descripcion'],
                              'estado'=>  DescripcionAfectacion::ACTIVO), 
                        $iniciativa_empresarial_nombre, 
                        null,
                        'Impacto Cultural'); 
                
                $descripcion_afectacion_id = $obj_descripcion_afectacion->id;                
                
                if($obj_descripcion_afectacion)
                {
                    
                    $obj_dai->guardar($obj_descripcion_afectacion->id, Input::post('checks_impacto_cultural'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            if($data_impacto_economico['si_no'] == 'SI')
            { 
                $obj_descripcion_afectacion = 
                DescripcionAfectacion::setDescripcionAfectacion('create', 
                        array('iniciativa_empresarial_id'=>$iniciativa_empresarial_id,
                            'tipo_impacto_id'=>'3',
                            'descripcion'=>$data_impacto_economico['descripcion'],
                              'estado'=>  DescripcionAfectacion::ACTIVO), 
                        $iniciativa_empresarial_nombre, 
                        null,
                        'Impacto Económico'); 
                
                $descripcion_afectacion_id = $obj_descripcion_afectacion->id;                
                
                if($obj_descripcion_afectacion)
                {
                    
                    $obj_dai->guardar($obj_descripcion_afectacion->id, Input::post('checks_impacto_economico'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            if($data_impacto_social['si_no'] == 'SI')
            { 
                $obj_descripcion_afectacion = 
                DescripcionAfectacion::setDescripcionAfectacion('create', 
                         array('iniciativa_empresarial_id'=>$iniciativa_empresarial_id,
                             'tipo_impacto_id'=>'4',
                            'descripcion'=>$data_impacto_social['descripcion'],
                              'estado'=>  DescripcionAfectacion::ACTIVO), 
                        $iniciativa_empresarial_nombre, 
                        null,
                        'Impacto Social'); 
                
                $descripcion_afectacion_id = $obj_descripcion_afectacion->id;                
                
                if($obj_descripcion_afectacion)
                {
                    
                    $obj_dai->guardar($obj_descripcion_afectacion->id, Input::post('checks_impacto_social'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            if($data_impacto_organizacion['si_no'] == 'SI')
            { 
                $obj_descripcion_afectacion = 
                DescripcionAfectacion::setDescripcionAfectacion('create', 
                        array('iniciativa_empresarial_id'=>$iniciativa_empresarial_id,
                            'tipo_impacto_id'=>'5',
                            'descripcion'=>$data_impacto_organizacion['descripcion'],
                              'estado'=>  DescripcionAfectacion::ACTIVO), 
                        $iniciativa_empresarial_nombre, 
                        null,
                        'Impacto Organizacional'); 
                
                $descripcion_afectacion_id = $obj_descripcion_afectacion->id;                
                
                if($obj_descripcion_afectacion)
                {
                    
                    $obj_dai->guardar($obj_descripcion_afectacion->id, Input::post('checks_impacto_organizacion'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            if($data_impacto_territorial['si_no'] == 'SI')
            { 
                $obj_descripcion_afectacion = 
                DescripcionAfectacion::setDescripcionAfectacion('create', 
                        array('iniciativa_empresarial_id'=>$iniciativa_empresarial_id,
                            'tipo_impacto_id'=>'6',
                            'descripcion'=>$data_impacto_territorial['descripcion'],
                              'estado'=>  DescripcionAfectacion::ACTIVO), 
                        $iniciativa_empresarial_nombre, 
                        null,
                        'Impacto Territorial'); 
                
                $descripcion_afectacion_id = $obj_descripcion_afectacion->id;                
                
                if($obj_descripcion_afectacion)
                {
                    
                    $obj_dai->guardar($obj_descripcion_afectacion->id, Input::post('checks_impacto_territorial'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }         
            
            Fuente::setFuente('create', Input::post('fuente'), 'iniciativa_empresarial', $obj_ie->id);
            Flash::valid('La iniciativa empresarial se ha registrado correctamente!');
            return Redirect::toAction('editar/'.$key_back.'/3/'.$order.'/'.$page.'/');
            }
        }
        
        $this->territorio_nombre = $territorio_nombre;
        $this->territorio_id = $territorio_id;
        $this->url_redir_back = 'gestion_territorial/gestion_tcci/editar/'.$key_back.'/3/'.$order.'/'.$page.'/';
        $this->page_title = 'Agregar Iniciativa Empresarial al territorio: '.$territorio_nombre;
        $this->page_module = 'Gestion Territorial';        
    }
    
         public function editar_iniciativa_empresarial_($key, $key_back, $order, $page) 
         { 
       
        if(!$id = Security::getKey($key, 'upd_iniciativa_empresarial', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $obj_iniciativa_empresarial = new IniciativaEmpresarial();        
        if(!$obj_iniciativa_empresarial->getIniciativaEmpresarialById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la iniciativa empresarial');
            return Redirect::toAction('ver/'.$key_back.'/3/');
        }   
        
        
        
        $iniciativa_empresarial_id = $obj_iniciativa_empresarial->id;
        $obj_descripcion_afectacion = new DescripcionAfectacion();
        $afectaciones_al_territorio = $obj_descripcion_afectacion->getDescripcionAfectacionByIniciativaEmpresarialId($iniciativa_empresarial_id);
        
        
        $this->si_no_ambiental = 'NO';
        $this->si_no_cultural = 'NO';
        $this->si_no_economico = 'NO';
        $this->si_no_social = 'NO';
        $this->si_no_organizacion = 'NO';
        $this->si_no_territorial = 'NO';
        $obj_descripcion_afectacion_impacto = new DescripcionAfectacionImpacto();
        foreach ($afectaciones_al_territorio as $afectaciones)
        {
            //$this->nombre_territorio = $afectaciones->territorio;
            $this->nombre_iniciativa_empresarial = $afectaciones->iniciativa_empresarial;
            $this->iniciativa_empresarial_id = $afectaciones->iniciativa_empresarial_id;
            
            if($afectaciones->tipo_impacto_id == '1')
            {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_ambiental = $afectaciones->id;
                $this->si_no_ambiental = 'SI';
                $this->descripcion_ambiental = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_ambiental[] = $value->impacto_id;
                }                
            }
            
            if($afectaciones->tipo_impacto_id == '2')
            {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_cultural = $afectaciones->id;
                $this->si_no_cultural = 'SI';
                $this->descripcion_cultural = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_cultural[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '3')
            {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_economico = $afectaciones->id;
                $this->si_no_economico = 'SI';
                $this->descripcion_economico = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_economico[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '4')
            {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_social = $afectaciones->id;
                $this->si_no_social = 'SI';
                $this->descripcion_social = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_social[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '5')
            {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_organizacion = $afectaciones->id;
                $this->si_no_organizacion = 'SI';
                $this->descripcion_organizacion = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_organizacion[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '6')
            {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_territorial = $afectaciones->id;
                $this->si_no_territorial = 'SI';
                $this->descripcion_territorial = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_territorial[] = $value->impacto_id;
                }                
            }
            
        }       
        
        
        
        if(Input::hasPost('iniciativa_empresarial')) 
        {        
            $obj_ie = IniciativaEmpresarial::setIniciativaEmpresarial('update', Input::post('iniciativa_empresarial'), $obj_iniciativa_empresarial->territorio, array('estado'=> IniciativaEmpresarial::ACTIVO));
            
            if($obj_ie)
            {
            $iniciativa_empresarial_id = $obj_ie->id;
            $iniciativa_empresarial_nombre = $obj_ie->nombre;
            $data_impacto_ambiental    = Input::post('impacto_ambiental');
            $data_impacto_cultural     = Input::post('impacto_cultural');
            $data_impacto_economico    = Input::post('impacto_economico');
            $data_impacto_social       = Input::post('impacto_social');
            $data_impacto_organizacion = Input::post('impacto_organizacion');
            $data_impacto_territorial  = Input::post('impacto_territorial');
            
            $obj_dai = new DescripcionAfectacionImpacto();
            if($data_impacto_ambiental['si_no'] == 'SI')
            { 
                DescripcionAfectacion::deleteDescripcionAfectacion($iniciativa_empresarial_id, '1');
                $obj_descripcion_afectacion = 
                DescripcionAfectacion::setDescripcionAfectacion('create',                          
                        array(
                            'iniciativa_empresarial_id'=>$iniciativa_empresarial_id,
                            'tipo_impacto_id'=>'1',
                            'descripcion'=>$data_impacto_ambiental['descripcion'],
                              'estado'=>  DescripcionAfectacion::ACTIVO), 
                        $iniciativa_empresarial_nombre, null, 'Impacto Ambiental'); 
                
                $descripcion_afectacion_id = $obj_descripcion_afectacion->id;                
                
                if($obj_descripcion_afectacion)
                {
                    
                    $obj_dai->guardar($obj_descripcion_afectacion->id, Input::post('checks_impacto_ambiental'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            else{ DescripcionAfectacion::deleteDescripcionAfectacion($iniciativa_empresarial_id, '1'); }
            
            
            if($data_impacto_cultural['si_no'] == 'SI')
            { 
                DescripcionAfectacion::deleteDescripcionAfectacion($iniciativa_empresarial_id, '2');
                $obj_descripcion_afectacion = 
                DescripcionAfectacion::setDescripcionAfectacion('create',                          
                        array(
                            'iniciativa_empresarial_id'=>$iniciativa_empresarial_id,
                            'tipo_impacto_id'=>'2',
                            'descripcion'=>$data_impacto_cultural['descripcion'],
                              'estado'=>  DescripcionAfectacion::ACTIVO), 
                        $iniciativa_empresarial_nombre, 
                        null,
                        'Impacto Cultural'); 
                
                $descripcion_afectacion_id = $obj_descripcion_afectacion->id;                
                
                if($obj_descripcion_afectacion)
                {
                    
                    $obj_dai->guardar($obj_descripcion_afectacion->id, Input::post('checks_impacto_cultural'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            else{ DescripcionAfectacion::deleteDescripcionAfectacion($iniciativa_empresarial_id, '2'); }
            
            
            if($data_impacto_economico['si_no'] == 'SI')
            { 
                DescripcionAfectacion::deleteDescripcionAfectacion($iniciativa_empresarial_id, '3');
                $obj_descripcion_afectacion = 
                DescripcionAfectacion::setDescripcionAfectacion('create',                          
                        array(
                            'iniciativa_empresarial_id'=>$iniciativa_empresarial_id,
                            'tipo_impacto_id'=>'3',
                            'descripcion'=>$data_impacto_economico['descripcion'],
                              'estado'=>  DescripcionAfectacion::ACTIVO), 
                        $iniciativa_empresarial_nombre, 
                        null,
                        'Impacto Económico'); 
                
                $descripcion_afectacion_id = $obj_descripcion_afectacion->id;                
                
                if($obj_descripcion_afectacion)
                {
                    
                    $obj_dai->guardar($obj_descripcion_afectacion->id, Input::post('checks_impacto_economico'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            else{ DescripcionAfectacion::deleteDescripcionAfectacion($iniciativa_empresarial_id, '3'); }
            
            
            if($data_impacto_social['si_no'] == 'SI')
            { 
                DescripcionAfectacion::deleteDescripcionAfectacion($iniciativa_empresarial_id, '4');
                $obj_descripcion_afectacion = 
                DescripcionAfectacion::setDescripcionAfectacion('create',                          
                        array(
                            'iniciativa_empresarial_id'=>$iniciativa_empresarial_id,
                             'tipo_impacto_id'=>'4',
                            'descripcion'=>$data_impacto_social['descripcion'],
                              'estado'=>  DescripcionAfectacion::ACTIVO), 
                        $iniciativa_empresarial_nombre, 
                        null,
                        'Impacto Social'); 
                
                $descripcion_afectacion_id = $obj_descripcion_afectacion->id;                
                
                if($obj_descripcion_afectacion)
                {
                    
                    $obj_dai->guardar($obj_descripcion_afectacion->id, Input::post('checks_impacto_social'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            else{ DescripcionAfectacion::deleteDescripcionAfectacion($iniciativa_empresarial_id, '4'); }
            
            
            if($data_impacto_organizacion['si_no'] == 'SI')
            { 
                DescripcionAfectacion::deleteDescripcionAfectacion($iniciativa_empresarial_id, '5');
                $obj_descripcion_afectacion = 
                DescripcionAfectacion::setDescripcionAfectacion('create',                          
                        array(
                            'iniciativa_empresarial_id'=>$iniciativa_empresarial_id,
                            'tipo_impacto_id'=>'5',
                            'descripcion'=>$data_impacto_organizacion['descripcion'],
                              'estado'=>  DescripcionAfectacion::ACTIVO), 
                        $iniciativa_empresarial_nombre, 
                        null,
                        'Impacto Organizacional'); 
                
                $descripcion_afectacion_id = $obj_descripcion_afectacion->id;                
                
                if($obj_descripcion_afectacion)
                {
                    
                    $obj_dai->guardar($obj_descripcion_afectacion->id, Input::post('checks_impacto_organizacion'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            else{ DescripcionAfectacion::deleteDescripcionAfectacion($iniciativa_empresarial_id, '5'); }
            
            
            if($data_impacto_territorial['si_no'] == 'SI')
            { 
                DescripcionAfectacion::deleteDescripcionAfectacion($iniciativa_empresarial_id, '6');
                $obj_descripcion_afectacion = 
                DescripcionAfectacion::setDescripcionAfectacion('create',                          
                        array(
                            'iniciativa_empresarial_id'=>$iniciativa_empresarial_id,
                            'tipo_impacto_id'=>'6',
                            'descripcion'=>$data_impacto_territorial['descripcion'],
                              'estado'=>  DescripcionAfectacion::ACTIVO), 
                        $iniciativa_empresarial_nombre, 
                        null,
                        'Impacto Territorial'); 
                
                $descripcion_afectacion_id = $obj_descripcion_afectacion->id;                
                
                if($obj_descripcion_afectacion)
                {
                    
                    $obj_dai->guardar($obj_descripcion_afectacion->id, Input::post('checks_impacto_territorial'));
                    
                    //Flash::valid('La afectación al territorio se ha registrado correctamente!');                
                }
                
            }
            else{ DescripcionAfectacion::deleteDescripcionAfectacion($iniciativa_empresarial_id, '6'); }
            
              
                
                
                Fuente::setFuente('update', Input::post('fuente'), 'iniciativa_empresarial', $id);
                
                Flash::valid('La Iniciativa Empresarial se ha actualizado correctamente!');
            }
            return Redirect::toAction('editar/'.$key_back.'/3/'.$order.'/'.$page.'/');
           
        }
         $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('iniciativa_empresarial', $id);
        
        $this->iniciativa_empresarial = $obj_iniciativa_empresarial;   
        $this->page_title = 'Actualizar Iniciativa Empresarial del territorio: '.$obj_iniciativa_empresarial->territorio;        
        $this->territorio_nombre = $obj_iniciativa_empresarial->territorio;      
        $this->page_module = 'Gestión Territorial';        
        $this->url_redir_back = 'gestion_territorial/gestion_tcci/editar/'.$key_back.'/3/'.$order.'/'.$page.'/';                           
        $this->key_back = $key_back;    
        $this->key = $key;           
    }
    
     public function ver_iniciativa_empresarial($key, $key_back, $order, $page) { 
       
        if(!$id = Security::getKey($key, 'show_iniciativa_empresarial', 'int')) {
            return Redirect::toAction('listar');
        }   
        
        $obj_iniciativa_empresarial = new IniciativaEmpresarial();        
        if(!$obj_iniciativa_empresarial->getIniciativaEmpresarialById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información de la iniciativa empresarial');
            return Redirect::toAction('ver/'.$key_back.'/3/');
        }   
        
        $iniciativa_empresarial_id = $obj_iniciativa_empresarial->id;
        $obj_descripcion_afectacion = new DescripcionAfectacion();
        $afectaciones_al_territorio = $obj_descripcion_afectacion->getDescripcionAfectacionByIniciativaEmpresarialId($iniciativa_empresarial_id);
        
        
        $this->si_no_ambiental = 'NO';
        $this->si_no_cultural = 'NO';
        $this->si_no_economico = 'NO';
        $this->si_no_social = 'NO';
        $this->si_no_organizacion = 'NO';
        $this->si_no_territorial = 'NO';
        $obj_descripcion_afectacion_impacto = new DescripcionAfectacionImpacto();
        foreach ($afectaciones_al_territorio as $afectaciones)
        {
            //$this->nombre_territorio = $afectaciones->territorio;
            $this->nombre_iniciativa_empresarial = $afectaciones->iniciativa_empresarial;
            $this->iniciativa_empresarial_id = $afectaciones->iniciativa_empresarial_id;
            
            if($afectaciones->tipo_impacto_id == '1')
            {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_ambiental = $afectaciones->id;
                $this->si_no_ambiental = 'SI';
                $this->descripcion_ambiental = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_ambiental[] = $value->impacto_id;
                }                
            }
            
            if($afectaciones->tipo_impacto_id == '2')
            {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_cultural = $afectaciones->id;
                $this->si_no_cultural = 'SI';
                $this->descripcion_cultural = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_cultural[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '3')
            {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_economico = $afectaciones->id;
                $this->si_no_economico = 'SI';
                $this->descripcion_economico = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_economico[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '4')
            {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_social = $afectaciones->id;
                $this->si_no_social = 'SI';
                $this->descripcion_social = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_social[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '5')
            {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_organizacion = $afectaciones->id;
                $this->si_no_organizacion = 'SI';
                $this->descripcion_organizacion = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_organizacion[] = $value->impacto_id;
                }                
            }
            if($afectaciones->tipo_impacto_id == '6')
            {
                $impactos = $obj_descripcion_afectacion_impacto->getDescripcionAfectacionImpacto($afectaciones->id);
                $this->id_territorial = $afectaciones->id;
                $this->si_no_territorial = 'SI';
                $this->descripcion_territorial = $afectaciones->descripcion;                
                foreach ($impactos as $value)
                {
                    $this->array_impacto_territorial[] = $value->impacto_id;
                }                
            }
            
        }       
        
        
        $fuente = new Fuente();
        $this->fuentes = $fuente->getListadoFuente('iniciativa_empresarial', $obj_iniciativa_empresarial->id);
                
        $this->iniciativa_empresarial = $obj_iniciativa_empresarial;   
        $this->page_title = 'Información Iniciativa Empresarial del territorio: '.$obj_iniciativa_empresarial->territorio;        
        $this->territorio_nombre = $obj_iniciativa_empresarial->territorio;        
        $this->page_module = 'Gestión Territorial';        
        $this->url_redir_back = 'gestion_territorial/gestion_tcci/ver/'.$key_back.'/3/'.$order.'/'.$page.'/';  
        
        $this->key_back = $key_back;    
        $this->key = $key;                   
    }
    
    
     /**
     * Método para eliminar
     */
    public function eliminar_iniciativa_empresarial($nombre_iniciativa_empresarial, $nombre_territorio, $key, $key_back, $order, $page) {      
                
        if(!$id = Security::getKey($key, 'del_iniciativa_empresarial', 'int')) {
        return Redirect::to(/*$url_redir_back*/);
        }        
        
        $iniciativa_empresarial = new IniciativaEmpresarial();
                    
        try {
            if($iniciativa_empresarial->delete($id)) {
                Flash::valid("La iniciativa empresarial $nombre_iniciativa_empresarial se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO la iniciativa empresarial $nombre_iniciativa_empresarial, pertenecia al territorio $nombre_territorio.");
            } else {
                Flash::warning('Lo sentimos, pero esta iniciativa empresarial no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Esta iniciativa empresarial no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction('editar/'.$key_back.'/2/'.$order.'/'.$page.'/');
    }
    
    
     
     
     /*     
     ********************************************************************************************
     * **********************************************
     * **********************************************
     */
    
    
    
    /**
     * Método para eliminar
     */
    public function eliminar($key, $redireccionar, $order, $page) {         
        if(!$id = Security::getKey($key, 'eliminar_territorio', 'int')) {
            return Redirect::toAction($redireccionar.'/'.$order.'/'.$page.'/');
        }        
        
        $territorio = new Territorio();
        if(!$territorio->find_first($id)) {
            Flash::error('Lo sentimos, no se ha podido establecer la información del territorio');    
            return Redirect::toAction($redireccionar.'/'.$order.'/'.$page.'/');
        }              
        try {
            $titulado_si = new TituladoSi();
            $titulado_no = new TituladoNo();
            $poblacion = new Poblacion();
            $territorio_municipio = new TerritorioMunicipio();
            
            $titulado_si->delete_all("territorio_id = $id");
            $titulado_no->delete_all("territorio_id = $id");
            $poblacion->delete_all("territorio_id = $id");
            $territorio_municipio->delete_all("territorio_id = $id");
            
            if($territorio->delete()) {
                Flash::valid('El territorio se ha eliminado correctamente!');
                DwAudit::warning("Se ha ELIMINADO el territorio $territorio->nombre.");
            } else {
                Flash::warning('Lo sentimos, pero este territorio no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            Flash::error('Este territorio no se puede eliminar porque se encuentra relacionado con otro registro.');
        }
        
        return Redirect::toAction($redireccionar.'/'.$order.'/'.$page.'/');
    }
            
    
}