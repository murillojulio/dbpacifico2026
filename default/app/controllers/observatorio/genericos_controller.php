<?php
/**
 * Descripcion: Controlador que se encarga de las vistas genericas que se cargaran con AJAX *
 * @category    
 * @package     Controllers  
 */
Load::models('observatorio/territorio', 'observatorio/territorio_municipio', 'observatorio/departamento',
        'observatorio/municipio', 'observatorio/fuente', 'afectacion/ubicacion',
        'afectacion/conflicto_uso', 'util/estado', 'util/correo_recover_password',
        'sistema/usuario', 'observatorio/subregion');
class GenericosController extends BackendController {
    
   /**
     * Método que carga los municipios de un departamento en una lista de check box
     */
    public function db_check_municipios($departamento_id, $departamento_nombre, $titulo_panel) {       
        $this->departamento_id = $departamento_id;   
        $this->departamento_nombre = $departamento_nombre;
        $this->titulo_panel = $titulo_panel;
    } 
    
    /**
     * Método que carga los territorios que pertenecen a un departamento en una lista select
     */
    public function db_select_territorios($departamento_id, $departamento_nombre) {       
        $this->departamento_id = $departamento_id;   
        $this->departamento_nombre = $departamento_nombre;
    } 
    
    /**
     * Método que carga los territorios que pertenecen a un departamento en una lista select
     */
    public function db_check_impactos($tipo_impacto_id) {       
        $this->tipo_impacto_id = $tipo_impacto_id;   
    } 
    
    /**
     * Método que carga los campos de si un territorio es titulado o no
     */
    public function titulado_si() {
        
         $departamentos = new Departamento();
        $this->departamentos = $departamentos->getListadoDepartamentoDBS();
              
    }
    
    /**
     * Método que carga los campos de si un territorio es titulado o no
     */
    public function titulado_no() {
        
         $departamentos = new Departamento();
        $this->departamentos = $departamentos->getListadoDepartamentoDBS();
              
    }
    
    /**
     * Método que carga los territorios que pertenecen a un departamento en una lista select
     */
    public function db_select_municipios($departamento_id, $field, $label, $required='si', $cargar_territorio='si') {       
        View::template(null);
        $this->departamento_id = $departamento_id;   
        $this->field = $field;
        $this->label = $label;
        $this->required = $required;
        $this->input_required = '';
        $this->cargar_territorio = $cargar_territorio;
        
        if($required == 'si'){ $this->input_required = 'input-required';}
    } 
    
    /**
     * Método que carga los territorios que pertenecen a un departamento en una lista select
     */
    public function db_select_territorios_municipio($municipio_id, $field, $label, $required='si') {       
        $this->municipio_id = $municipio_id;   
        $this->field = $field;
        $this->label = $label;
        $this->required = $required;
        $this->input_required = '';
        
        if ($required == 'si'){ $this->input_required = 'input-required';}
    } 
    
            
     /*
     * Método que carga los territorios que pertenecen a un departamento en una lista select
     */
    public function gestionar_fuente()
    {
        // Obtener datos desde el POST de forma segura con Input::post()
        $id = Input::post('id', 'trim');
        $fecha = Input::post('fecha', 'trim');
        $nombre = Input::post('nombre', 'trim');
        $tabla = Input::post('tabla', 'trim');
        $tabla_identi = Input::post('tabla_identi', 'trim');
    
        $obj_fuente = new Fuente();
    
        if ($id === 'vacio') {
            if (!empty($fecha) && !empty($nombre)) {
                $obj_fuente->fecha = date('Y-m-d', strtotime($fecha));
                $obj_fuente->nombre = $nombre;
                $obj_fuente->tabla = $tabla;
                $obj_fuente->tabla_identi = $tabla_identi;
    
                $obj_fuente->create();
            } else {
                // Error si falta fecha o nombre
                View::select(null); // No renderizar vista
                return json_encode(['error' => 'Fecha o nombre vacíos']);
            }
        } else {
            $obj_fuente->id = $id;
            $obj_fuente->delete();
        }
    
        // Recargar lista para renderizar div_fuente
        $this->fuentes = $obj_fuente->getListadoFuente($tabla, $tabla_identi);
    } 
    
    
     /**
     * Método que carga los subtipos de megaproyectos
     */
    public function db_select_subtipo_megaproyecto($tipo_megaproyecto_id) {       
        $this->tipo_megaproyecto_id = $tipo_megaproyecto_id;           
    } 
    
     /**
     * Método que carga los micro subtipos de megaproyectos
     */
    public function db_select_micro_subtipo_megaproyecto($subtipo_megaproyecto_id) {       
        $this->subtipo_megaproyecto_id = $subtipo_megaproyecto_id;           
    } 
    
    
     /*
     * Método que carga los territorios que pertenecen a un departamento en una lista select
     */
    public function gestion_ubicacion($metodo='', $id='', $afectacion_id='',$departamento_id='', $municipio_id='', $territorio_id='') 
    {       
        $obj_ubicacion = new Ubicacion();
        
        if($metodo == 'create')
        {
            $obj_ubicacion->afectacion_id = $afectacion_id;
            $obj_ubicacion->departamento_id = $departamento_id;
            $obj_ubicacion->municipio_id = $municipio_id;
            $obj_ubicacion->territorio_id = $territorio_id;
            $obj_ubicacion->create();
        }
        else if($metodo == 'delete')
        {            
            $obj_ubicacion->id = $id;
            $obj_ubicacion->delete();
        }
            
        //*$this->ubicaciones = $obj_ubicacion->find_all_by('ubicacion.afectacion_id', $afectacion_id);*/
        $this->ubicaciones = $obj_ubicacion->getUbicaciones($afectacion_id);
    } 
    
    
    /*
     * Método que carga los territorios que pertenecen a un departamento en una lista select
     */
    public function gestion_ubicacion_territorio($metodo='', $id='', $territorio_id='', $municipio_id='') 
    {       
        $obj_territorio_municipio = new TerritorioMunicipio();
        
        if($metodo == 'create')
        {            
            $obj_territorio_municipio->municipio_id = $municipio_id;
            $obj_territorio_municipio->territorio_id = $territorio_id;
            $obj_territorio_municipio->create();
        }
        else if($metodo == 'delete')
        {            
            $obj_territorio_municipio->id = $id;
            $obj_territorio_municipio->delete();
        }
            
        $this->ubicaciones = $obj_territorio_municipio->getDepartamentoMunicipioByTerritorioId($territorio_id);
    } 
    
    /**
     * Método que carga los subtipos de megaproyectos
     */
    public function db_select_subtipo_megaproyecto_politica($tipo_megaproyecto_id) {       
        $this->tipo_megaproyecto_id = $tipo_megaproyecto_id;           
    } 
    
     /**
     * Método que carga los subtipos de megaproyectos
     */
    public function db_select_subtipo_megaproyecto_desarrollo($tipo_megaproyecto_id) {       
        $this->tipo_megaproyecto_id = $tipo_megaproyecto_id;           
    } 
    
    /*
     * Método que carga los territorios que pertenecen a un departamento en una lista select
     */
    public function gestion_conflicto_uso_cn($metodo='', $id='', $area_natural_protegida_id='', $territorio_id='') 
    {       
        $obj_conflicto_uso = new ConflictoUso();
        
        if($metodo == 'create')
        {
            $obj_conflicto_uso->area_natural_protegida_id = $area_natural_protegida_id;
            $obj_conflicto_uso->territorio_id = $territorio_id;
            $obj_conflicto_uso->create();
        }
        else if($metodo == 'delete')
        {            
            $obj_conflicto_uso->id = $id;
            $obj_conflicto_uso->delete();
        }
            
         $this->conflicto_usos_cn = $obj_conflicto_uso->getConflicto($area_natural_protegida_id, 'comunidad_negra');
         
    } 
    
     /*
     * Método que carga los territorios que pertenecen a un departamento en una lista select
     */
    public function gestion_conflicto_uso_ci($metodo='', $id='', $area_natural_protegida_id='', $territorio_id='') 
    {       
        $obj_conflicto_uso = new ConflictoUso();
        
        if($metodo == 'create')
        {
            $obj_conflicto_uso->area_natural_protegida_id = $area_natural_protegida_id;
            $obj_conflicto_uso->territorio_id = $territorio_id;
            $obj_conflicto_uso->create();
        }
        else if($metodo == 'delete')
        {            
            $obj_conflicto_uso->id = $id;
            $obj_conflicto_uso->delete();
        }
            
        $this->conflicto_usos_ci = $obj_conflicto_uso->getConflicto($area_natural_protegida_id, 'indigena');
    }
    
    /*
     * Método que carga los territorios que pertenecen a un departamento en una lista select
     */
    public function gestion_nivel_estado($nivel_estado='', $tabla='', $id='', $modulo='', $nombre='') 
    {       
        $obj_nivel_estado = new ConflictoUso();
        
        if($nivel_estado == 'privado')
        {
            $obj_nivel_estado->sql('UPDATE '.$tabla.' SET nivel="2" WHERE '.$tabla.'.id='.$id);
        }
        else if($nivel_estado == 'publico')
        {            
            $obj_nivel_estado->sql('UPDATE '.$tabla.' SET nivel="1" WHERE '.$tabla.'.id='.$id);
        }
        else if($nivel_estado == 'borrador')
        {
            $obj_nivel_estado->sql('UPDATE '.$tabla.' SET estado="1" WHERE '.$tabla.'.id='.$id);
        }
        else if($nivel_estado == 'completo')
        {     
            if($obj_nivel_estado->sql('UPDATE '.$tabla.' SET estado="2" WHERE '.$tabla.'.id='.$id))
            {
                $correo = CorreoRecoverPassword::send_alert(Usuario::usuariosAlertar(), 
                        "Información completada - $modulo", 
                        "El usuario ".Session::get('login').", ha completado la informacion de $modulo - $nombre.");              
            }
           
        }
            
        $this->objeto = $obj_nivel_estado->find_by_sql('SELECT '.$tabla.'.nivel, '.$tabla.'.estado, '.$tabla.'.id FROM '.$tabla.' WHERE '.$tabla.'.id='.$id);
        $this->tabla = $tabla;
        $this->modulo = $modulo;
        $this->nombre = $nombre;
    } 

    /*
     * Método que carga los territorios que pertenecen a un departamento en una lista select
     */
    public function gestion_nivel_estado_single($nivel_estado='', $tabla='', $id='', $modulo='', $nombre='') 
    {       
        $obj_nivel_estado = new ConflictoUso();
        
        if($nivel_estado == 'privado')
        {
            $obj_nivel_estado->sql('UPDATE '.$tabla.' SET nivel="2" WHERE '.$tabla.'.id='.$id);
        }
        else if($nivel_estado == 'publico')
        {            
            $obj_nivel_estado->sql('UPDATE '.$tabla.' SET nivel="1" WHERE '.$tabla.'.id='.$id);
        }
        else if($nivel_estado == 'borrador')
        {
            $obj_nivel_estado->sql('UPDATE '.$tabla.' SET estado="1" WHERE '.$tabla.'.id='.$id);
        }
        else if($nivel_estado == 'completo')
        {     
            if($obj_nivel_estado->sql('UPDATE '.$tabla.' SET estado="2" WHERE '.$tabla.'.id='.$id))
            {
                $correo = CorreoRecoverPassword::send_alert(Usuario::usuariosAlertar(), 
                        "Información completada - $modulo", 
                        "El usuario ".Session::get('login').", ha completado la informacion de $modulo - $nombre.");              
            }
           
        }
            
        $this->objeto = $obj_nivel_estado->find_by_sql('SELECT '.$tabla.'.nivel, '.$tabla.'.estado, '.$tabla.'.id FROM '.$tabla.' WHERE '.$tabla.'.id='.$id);
        $this->tabla = $tabla;
        $this->modulo = $modulo;
        $this->nombre = $nombre;
    } 
    
    /**
     * Método que carga los daños de un tipo de daño
     */
    public function db_select_danos($tipo_dano_id=NULL) {       
        $this->tipo_dano_id = $tipo_dano_id;           
    } 
    
    /**
     * Método que carga los daños de un tipo de daño
     */
    public function db_select_danos_editar() {   
        $tipo_dano_id = NULL;
        $tipo_dano_id = Input::post('tipo_dano_id');
        $this->tipo_dano_id = $tipo_dano_id;           
    } 

    public function db_select_recarga_territorios(){
        $afectacion_id = NULL;
        $afectacion_id = Input::post('afectacion_id');
        $this->afectacion_id = $afectacion_id;   
        View::template(null);
    }
}