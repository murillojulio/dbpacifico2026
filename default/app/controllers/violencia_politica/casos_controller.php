<?php
/**
 * Descripcion: Controlador que se encarga de los casos de violencia politica
 *
 * @category    
 * @package     Controllers  
 */
header("Set-Cookie: sameSite=secure");
Load::models('violencia_politica/victima', 'violencia_politica/caso',
        'violencia_politica/tipo_caso', 'violencia_politica/victima_antecedente_violencia',
        'violencia_politica/caso_dano_territorio', 'violencia_politica/victima_caracterizacion2',
        'violencia_politica/victima_hechovictimizante_presunto_responsable', 'observatorio/comunidad',
        'opcion/genero', 'opcion/etnia', 'opcion/caracterizacion', 'opcion/caracterizacion2', 
        'opcion/antecedente_violencia', 'opcion/hechovictimizante', 'opcion/etnia2',
        'opcion/presunto_responsable', 'observatorio/departamento', 'violencia_politica/victima_etnia2',
        'observatorio/municipio', 'observatorio/territorio', 'observatorio/localidad', 'global/fuente');
class CasosController extends BackendController {
    
    
    /**
     * Método que se ejecuta antes de cualquier acción
     */
    protected function before_filter() {
        //Se cambia el nombre del módulo actual
        $this->page_module = 'Violencia Política';
    }
    
     /**
     * Método principal
     */
    public function index() {
        Redirect::toAction('listar');
    }
    
    public function agregar_caso()
    { 
        Session::set('query_busqueda_caso', '');
         $this->page_title = 'Agregar Caso de Violencia Política';
         $obj_caso = new Caso();
         
         if(Input::hasPost('caso'))
         {
             $obj_caso = Caso::setCaso('create', Input::post('caso'));
             if($obj_caso)
             {
                 Fuente::setFuente('create', Input::post('fuente'), 'caso', $obj_caso->id);
                 Flash::valid('El caso de se ha registrado correctamente!');
                 return Redirect::toAction('editar_caso/'.$obj_caso->id.'/2/');
             }
         }               
     }
     
    public function editar_caso($caso_id, $tab)
    { 
        //Para saber que pestaña estara activa cuando visualice un territorio
        $this->tab_1_active = '';
        $this->tab_2_active = '';
        $this->tab_3_active = '';
        $this->tab_4_active = '';
        $this->tab_5_active = '';
        
        if($tab == 1){ $this->tab_1_active = 'in active'; }
        if($tab == 2){ $this->tab_2_active = 'in active'; }
        if($tab == 3){ $this->tab_3_active = 'in active'; }
        if($tab == 4){ $this->tab_4_active = 'in active'; }
        if($tab == 5){ $this->tab_5_active = 'in active'; }
                
         $obj_caso = new Caso();
         $obj_caso->getCasoById($caso_id);
         $this->caso = $obj_caso;         
         $this->departamento_id = $obj_caso->getMunicipio()->departamento_id;
         $this->page_title = 'Caso: '.$obj_caso->titulo;

        $localidad_id =  $obj_caso->localidad_id;
        $tipo_localidad = $obj_caso->tipo_localidad;
        $this->corregimiento_id = '';
        $this->vereda_id = '';
        $this->inspeccion_id = '';
        if($tipo_localidad == 'corregimiento'){ $this->corregimiento_id = $localidad_id;}
        if($tipo_localidad == 'vereda'){ $this->vereda_id = $localidad_id;}
        if($tipo_localidad == 'inspeccion'){ $this->inspeccion_id = $localidad_id;}
         
         //try {
             $this->victimas = $obj_caso->getVictima();
         //} catch (Exception $exc) {
             //echo $exc->getTraceAsString();
         //}

         $CasoDanoTerritorio = new CasoDanoTerritorio();
         $this->CasoDanoTerritorio = $CasoDanoTerritorio->getDanoTerritorioByCasoId($caso_id);
         
         
          $fuente = new Fuente();
          $this->fuentes = $fuente->getListadoFuente('caso', $caso_id);
                           
         if(Input::hasPost('caso'))
         {
             $obj_caso = Caso::setCaso('update', Input::post('caso'));
             if($obj_caso)
             {
                 Fuente::setFuente('update', Input::post('fuente'), 'caso', $obj_caso->id);
                 Flash::valid('El caso se ha actualizado correctamente!');
                 return Redirect::toAction('editar_caso/'.$obj_caso->id.'/1/');
             }
         }               
     }
     
     
     
      public function agregar_victima($caso_id)
    { 
         
         $obj_caso = new Caso();
         $obj_caso->getCasoById($caso_id);
         
         $this->caso = $obj_caso;
         $this->page_title = 'Agregar Víctima al caso: '.$obj_caso->titulo;
         $this->url_redir_back = 'violencia_politica/casos/editar_caso/'.$obj_caso->id.'/2/';
         
         if(Input::hasPost('victima'))
         {
             $obj_victima = Victima::setVictima('create', Input::post('victima'));
             if($obj_victima)
             {
                 $victima_antecedente_violencia = new VictimaAntecedenteViolencia();
                 $victima_antecedente_violencia->guardar(Input::post('antecedente_violencia'), $obj_victima->id);
                 
                 Flash::valid('La víctima se ha registrado correctamente!');
                 return Redirect::toAction('editar_caso/'.$obj_caso->id.'/2/');
             }
         }               
     }
     
    public function gestion_victima()
    {
        $accion = Input::post('accion');              
        if($accion === 'crear')
        {
            $obj_victima = Victima::setVictima('create', Input::post('victima'));
            if($obj_victima && Input::hasPost('antecedente_violencia'))
            {
                $victima_antecedente_violencia = new VictimaAntecedenteViolencia();
                $victima_antecedente_violencia->guardar(Input::post('antecedente_violencia'), $obj_victima->id);
            }
            if($obj_victima && Input::hasPost('victima_caracterizacion'))
            {
                $victima_caracterizacion = new VictimaCaracterizacion2();
                $victima_caracterizacion->guardar(Input::post('victima_caracterizacion'), $obj_victima->id);
            }
            if($obj_victima && Input::hasPost('victima_etnia'))
            {
                $victima_etnia = new VictimaEtnia2();
                $victima_etnia->guardar(Input::post('victima_etnia'), $obj_victima->id);
            }
            if($obj_victima){                
                $Victima = new Victima();
                $this->victimas = $Victima->getVictimasByCasoId($obj_victima->caso_id);
                Flash::valid("La víctima $obj_victima->nombre se ha registrado correctamente!");
            }
        }elseif($accion === 'editar'){
           $obj_victima = Victima::setVictima('update', Input::post('victima'));
           if($obj_victima)
           {
               $victima_antecedente_violencia = new VictimaAntecedenteViolencia();
               $victima_antecedente_violencia->guardar(Input::post('antecedente_violencia'), $obj_victima->id);
               $victima_caracterizacion = new VictimaCaracterizacion2();
               $victima_caracterizacion->guardar(Input::post('victima_caracterizacion'), $obj_victima->id);                                 
               $victima_etnia = new VictimaEtnia2();
               $victima_etnia->guardar(Input::post('victima_etnia'), $obj_victima->id);                                 
              $Victima = new Victima();
               $this->victimas = $Victima->getVictimasByCasoId($obj_victima->caso_id);
               Flash::valid("La víctima $obj_victima->nombre se ha actualizado correctamente!");
           }
        }elseif($accion === 'eliminar'){
            $victima_id = Input::post('victima_id');
            $victima_nombre = Input::post('nombre_victima');
            $nombre_caso = Input::post('nombre_caso');
            $caso_id = Input::post('caso_id');
            $Victima = new Victima();
            try {
            if($Victima->delete($victima_id)) {
                Flash::valid("La víctima $victima_nombre se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO la víctima $victima_nombre, pertenecia al caso de violencia política $nombre_caso.");
            } else {
                Flash::warning('Lo sentimos, pero esta víctima no se puede eliminar.');
            }
            } catch(KumbiaException $e) {
                if(stripos($e, 'fk_victima_hechovictimizante_presunto_responsable_victima1')){
                    Flash::error('Esta víctima no se puede eliminar porque se encuentra '
                            . 'relacionada con hechos victimizantes');
                }
                //Flash::error('Esta víctima no se puede eliminar porque se encuentra relacionado con otro registro. '.$e);
            }
            $Victima = new Victima();
            $this->victimas = $Victima->getVictimasByCasoId($caso_id);
        }
    }
     
    public function cargar_editar_victima()
    {
        $Victima = new Victima();
        $victima_id = Input::post('victima_id');
        $Victima->find_first($victima_id);
        $this->Victima = $Victima;
        $this->antecedente_violencia = array();   
        $this->victima_caracterizacion =  array();    
        $this->victima_etnia =  array();     
        foreach ($Victima->getVictimaAntecedenteViolencia() as $value) {
            $this->antecedente_violencia[] = $value->antecedente_violencia_id;
        }  
        foreach ($Victima->getVictimaCaracterizacion2() as $value) {
            $this->victima_caracterizacion[] = $value->caracterizacion2_id;
        } 
        foreach ($Victima->getVictimaEtnia2() as $value) {
            $this->victima_etnia[] = $value->etnia2_id;
        }        
        $this->victima_id = $victima_id;
    }
    

     
      public function editar_victima($key) {        
        if(!$id = Security::getKey($key, 'upd_victima', 'int')) 
        {
            return Redirect::toAction('listar');
        }        
         
            $obj_victima = new Victima();
            $obj_victima->find($id);  
                        
            $this->page_title = 'Editar Víctima del caso: '.$obj_victima->getCaso()->titulo;
            $this->victima = $obj_victima;
            $this->url_redir_back = 'violencia_politica/casos/editar_caso/'.$obj_victima->caso_id.'/2/';
            $this->antecedente_violencia = array();
            
             foreach ($obj_victima->getVictimaAntecedenteViolencia() as $value) {
                        $this->antecedente_violencia[] = $value->antecedente_violencia_id;
            }
         
         if(Input::hasPost('victima'))
         {
             $obj_victima = Victima::setVictima('update', Input::post('victima'));
             if($obj_victima)
             {
                 $victima_antecedente_violencia = new VictimaAntecedenteViolencia();
                 $victima_antecedente_violencia->guardar(Input::post('antecedente_violencia'), $obj_victima->id);
                 
                 Flash::valid('La víctima se ha actualizado correctamente!');
                 return Redirect::toAction('editar_caso/'.$obj_victima->caso_id.'/2/');
             }
         }               
     }
     
      /**
     * Método para eliminar
     
    public function eliminar_victima($nombre_victima, $nombre_caso, $key, $key_back) {      
                
        if(!$id = Security::getKey($key, 'del_victima', 'int')) {
            return Redirect::to($url_redir_back);
        }        
        
        $victima = new Victima();
                    
        try {
            if($victima->delete($id)) {
                Flash::valid("La victima $nombre_victima se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO la víctima $nombre_victima, pertenecia al caso de violencia política $nombre_caso.");
            } else {
                Flash::warning('Lo sentimos, pero esta víctima no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            if(stripos($e, 'fk_victima_hechovictimizante_presunto_responsable_victima1')){
                Flash::error('Esta víctima no se puede eliminar porque se encuentra '
                        . 'relacionada con hechos victimizantes');
            }
            //Flash::error('Esta víctima no se puede eliminar porque se encuentra relacionado con otro registro. '.$e);
        }
        
        return Redirect::toAction("editar_caso/$key_back/2/");
    }
    */
     
    
    public function gestion_vhpr()
    {
        $accion = Input::post('accion');
        if($accion === 'crear'){
            $Victima = new Victima();
            $caso_id = Input::post('caso_id');
            $obj_vhpr = new VictimaHechovictimizantePresuntoResponsable();
            $victimas = Input::post('victimas');
            $hechos_victimizantes = Input::post('hechos_victimizantes');
            $presunto_responsable_id = Input::post('presunto_responsable_id');
            $descripcion_presunto_responsable = Input::post('descripcion_presunto_responsable');
         
            if($obj_vhpr->guardar($hechos_victimizantes, $victimas, $presunto_responsable_id, $descripcion_presunto_responsable)){ 
                $this->victimas = $Victima->getVictimasByCasoId($caso_id);
            }
            $this->caso_id = $caso_id;
            
            
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
     
     
      public function agregar_vhpr($caso_id, $presunto_responsable_id, $descripcion_presunto_responsable, $hechovictimizante_id, $victima_id)
    {          
         $obj_vhpr = new VictimaHechovictimizantePresuntoResponsable();
         $obj_vhpr->victima_id = $victima_id;
         $obj_vhpr->hechovictimizante_id = $hechovictimizante_id;
         $obj_vhpr->presunto_responsable_id = $presunto_responsable_id;
         $obj_vhpr->descripcion_presunto_responsable = $descripcion_presunto_responsable;
         
         $obj_vhpr->save();
                  
         if($obj_vhpr)
         {            
                 Flash::valid('El hecho victimizante se ha registrado correctamente!');
                 return Redirect::toAction('editar_caso/'.$caso_id.'/3/');            
         }               
     }
     
      public function eliminar_vhpr($caso_id, $vhpr_id)
    {          
         $obj_vhpr = new VictimaHechovictimizantePresuntoResponsable();
         $obj_vhpr->delete($vhpr_id);
                  
         if($obj_vhpr)
         {            
                 Flash::valid('La victimización se ha eliminado correctamente!');
                 return Redirect::toAction('editar_caso/'.$caso_id.'/3/');            
         }               
     }
     
       public function agregar_fuente($caso_id, $fecha, $nombre)
    {          
         $obj_fuente = new Fuente();
         $obj_fuente->nombre = $nombre;
         $obj_fuente->fecha = $fecha;
         $obj_fuente->tabla = 'caso';
         $obj_fuente->tabla_identi = $caso_id;         
         $obj_fuente->save();
                  
         if($obj_fuente)
         {            
                 Flash::valid('La fuente de información se ha registrado correctamente!');
                 return Redirect::toAction('editar_caso/'.$caso_id.'/4/');            
         }               
     }
     
     /*
     public function consulta($retorna_busqueda = 'no')
    { 
         $this->modo = 'consulta';
         $this->page_title = 'Busqueda Casos de Violencia Política';
         $this->departamento_id = '';
         $this->municipio_id = '';
         $this->territorio_id = '';
         
         $obj_caso = null;
         $casos = null;
         
         if(Input::hasPost('caso'))
         {
             $obj_caso = Input::post('caso');
             if($obj_caso)
             {
                 $titulo = $obj_caso['titulo'];
                 $sql_titulo = '';
                 if($titulo != null)
                 {
                     $sql_titulo = " AND caso.titulo LIKE '%$titulo%'";
                 }
                 
                 $departamento_id = $obj_caso['departamento_id'];
                 $municipio_id = $obj_caso['municipio_id'];
                 $territorio_id = $obj_caso['territorio_id'];
                 
                 $sql_lugar = '';
                 if($departamento_id != null)
                 {
                     $sql_lugar = " AND caso.departamento_id = $departamento_id";
                     if($municipio_id != null)
                     {
                         $sql_lugar = " AND caso.municipio_id = $municipio_id";
                         if($territorio_id != null)
                         {
                            $sql_lugar = " AND caso.territorio_id = $territorio_id";
                         }
                     }
                 }
                 
                 $fecha_desde = $obj_caso['fecha_desde'];
                 $fecha_hasta = $obj_caso['fecha_hasta'];   
                 $sql_fecha_desde = '';
                 if($fecha_desde != null && $fecha_hasta != null)
                 {
                     $sql_fecha_desde = " AND caso.fecha_desde BETWEEN '$fecha_desde' AND '$fecha_hasta'";
                 }  
                 
                 //$sqlQuery = "SELECT caso.* FROM caso WHERE caso.id IS NOT NULL$sql_titulo$sql_fecha_desde$sql_lugar";
                 
                 
                 $sql_inner_victima = '';
                 $sql_inner_pre_resp_hec_vict = '';
                 $sql_inner_hec_vict = '';
                 
                 $sql_victima_nombre = '';
                 $sql_genero_id = '';
                 $sql_caracterizacion_id = '';
                 $sql_presunto_responsable_id = '';
                 $sql_hechovictimizante_id = '';
                 
                 $victima_nombre = $obj_caso['victima_nombre'];
                 //$victima_apellido = $obj_caso['victima_apellido'];
                 $victima_genero_id = $obj_caso['genero_id'];
                 $victima_caracterizacion_id = $obj_caso['caracterizacion_id'];
                 $victima_presunto_responsable_id = NULL; //$obj_caso['presunto_responsable_id'];
                 $victima_hechovictimizante_id = NULL; //$obj_caso['hechovictimizante_id'];          
                 
                 
                 if($victima_nombre != null)
                {                     
                     $sql_victima_nombre = " AND victima.nombre LIKE '%$victima_nombre%'";  
                     $sql_inner_victima = " INNER JOIN victima ON caso.id = victima.caso_id";
                }                
                if($victima_genero_id != null)
                {                    
                     $sql_genero_id = " AND victima.genero_id = $victima_genero_id";   
                     $sql_inner_victima = " INNER JOIN victima ON caso.id = victima.caso_id";
                }
                if($victima_caracterizacion_id != null)
                {                    
                    $sql_caracterizacion_id = " AND victima.caracterizacion_id = $victima_caracterizacion_id"; 
                    $sql_inner_victima = " INNER JOIN victima ON caso.id = victima.caso_id";
                }
                if($victima_presunto_responsable_id != null)
                {
                    $sql_inner_pre_resp_hec_vict = " INNER JOIN victima_hechovictimizante_presunto_responsable ON victima.id = victima_hechovictimizante_presunto_responsable.victima_id";
                    $sql_presunto_responsable_id = " AND victima_hechovictimizante_presunto_responsable.presunto_responsable_id = $victima_presunto_responsable_id";                     
                }
                if($victima_hechovictimizante_id != null)
                {
                    $sql_inner_pre_resp_hec_vict = " INNER JOIN victima_hechovictimizante_presunto_responsable ON victima.id = victima_hechovictimizante_presunto_responsable.victima_id";
                    $sql_hechovictimizante_id = " AND victima_hechovictimizante_presunto_responsable.hechovictimizante_id = $victima_hechovictimizante_id";                     
                }                
                                
                $sql_campos_ubicacion = " departamento.nombre AS departamento, municipio.nombre AS municipio, territorio.nombre AS territorio";
                $sql_inner_campos_ubicacion = " INNER JOIN departamento ON caso.departamento_id = departamento.id";
                $sql_inner_campos_ubicacion .= " INNER JOIN municipio ON caso.municipio_id = municipio.id";
                $sql_inner_campos_ubicacion .= " LEFT JOIN territorio ON caso.territorio_id = territorio.id";
                
                 $sqlQuery = "SELECT DISTINCT caso.*,$sql_campos_ubicacion
                     FROM caso$sql_inner_campos_ubicacion$sql_inner_victima$sql_inner_pre_resp_hec_vict WHERE caso.id IS NOT 
                     NULL$sql_titulo$sql_fecha_desde$sql_lugar$sql_victima_nombre$sql_genero_id$sql_caracterizacion_id$sql_presunto_responsable_id$sql_hechovictimizante_id";
                 
                 
                 Session::set('query_busqueda_caso', $sqlQuery);
                 $this->query = $sqlQuery;
                 
                 $caso = new Caso();
                 $casos = $caso->find_all_by_sql($sqlQuery);
                 $this->casos = $casos;
                 if (sizeof($casos) > 0)
                 {
                     $this->modo = 'resultado';
                     $this->page_title = 'Resultado de la busqueda';
                 }
                 else {
                     $this->modo = 'consulta';
                     Flash::error('No se encontraron registros');
                 }
                    $this->departamento_id = $obj_caso['departamento_id'];
                    $this->municipio_id = $obj_caso['municipio_id'];
                    $this->territorio_id = $obj_caso['territorio_id'];
                 
                 //return Redirect::toAction('resultado_consulta/'.$obj_caso->id.'/2/');
             }
         }  
         if ($retorna_busqueda == 'si')
         {
             $sqlQuery = Session::get('query_busqueda_caso');
             $caso = new Caso();
             $casos = $caso->find_all_by_sql($sqlQuery);
             $this->casos = $casos;
             if (sizeof($casos) > 0)
             {
                 $this->modo = 'resultado';
                 $this->page_title = 'Resultado de la busqueda';
             }
             else {
                 $this->modo = 'consulta';
                 Flash::error('No se encontraron registros');
             }
                $this->departamento_id = $obj_caso['departamento_id'];
                $this->municipio_id = $obj_caso['municipio_id'];
                $this->territorio_id = $obj_caso['territorio_id'];
         }
     }
     */
     
     public function consulta($retorna_busqueda = 'no')
    {
        $this->modo = 'consulta';
        $this->page_title = 'Busqueda Casos de Violencia Política';
        $this->departamento_id = '';
        $this->municipio_id = '';
        $this->territorio_id = '';
    
        if ($retorna_busqueda === 'si') {
            return $this->repetirBusqueda();
        }
    
        if (!Input::hasPost('caso')) {
            return;
        }
    
        $data = Input::post('caso');
    
        $conditions = ['caso.id IS NOT NULL'];
        $joins = [];
        $fields = [
            'caso.*',
            'departamento.nombre AS departamento',
            'municipio.nombre AS municipio',
            'territorio.nombre AS territorio'
        ];
    
        /* =====================
           UBICACIÓN
        ====================== */
        $joins[] = 'INNER JOIN departamento ON caso.departamento_id = departamento.id';
        $joins[] = 'INNER JOIN municipio ON caso.municipio_id = municipio.id';
        $joins[] = 'LEFT JOIN territorio ON caso.territorio_id = territorio.id';
    
        if (!empty($data['territorio_id'])) {
            $conditions[] = 'caso.territorio_id = ' . (int)$data['territorio_id'];
        } elseif (!empty($data['municipio_id'])) {
            $conditions[] = 'caso.municipio_id = ' . (int)$data['municipio_id'];
        } elseif (!empty($data['departamento_id'])) {
            $conditions[] = 'caso.departamento_id = ' . (int)$data['departamento_id'];
        }
    
        /* =====================
           TÍTULO
        ====================== */
        if (!empty($data['titulo'])) {
            $conditions[] = "caso.titulo LIKE '%" . addslashes($data['titulo']) . "%'";
        }
    
        /* =====================
           FECHAS
        ====================== */
        if (!empty($data['fecha_desde']) && !empty($data['fecha_hasta'])) {
            $conditions[] = "caso.fecha_desde BETWEEN '{$data['fecha_desde']}' AND '{$data['fecha_hasta']}'";
        }
    
        /* =====================
           VÍCTIMA
        ====================== */
        $usaVictima = false;
    
        if (!empty($data['victima_nombre'])) {
            $conditions[] = "victima.nombre LIKE '%" . addslashes($data['victima_nombre']) . "%'";
            $usaVictima = true;
        }
    
        if (!empty($data['genero_id'])) {
            $conditions[] = 'victima.genero_id = ' . (int)$data['genero_id'];
            $usaVictima = true;
        }
    
        if (!empty($data['caracterizacion_id'])) {
            $conditions[] = 'victima.caracterizacion_id = ' . (int)$data['caracterizacion_id'];
            $usaVictima = true;
        }
    
        if ($usaVictima) {
            $joins[] = 'INNER JOIN victima ON caso.id = victima.caso_id';
        }
    
        /* =====================
           SQL FINAL
        ====================== */
        $sql = "
            SELECT DISTINCT " . implode(', ', $fields) . "
            FROM caso
            " . implode("\n", $joins) . "
            WHERE " . implode(' AND ', $conditions);
    
        Session::set('query_busqueda_caso', $sql);
        $this->query = $sql;
    
        $caso = new Caso();
        $this->casos = $caso->find_all_by_sql($sql);
    
        if (count($this->casos) > 0) {
            $this->modo = 'resultado';
            $this->page_title = 'Resultado de la busqueda';
        } else {
            Flash::error('No se encontraron registros');
        }
    
        $this->departamento_id = $data['departamento_id'];
        $this->municipio_id = $data['municipio_id'];
        $this->territorio_id = $data['territorio_id'];
    }
    
    private function repetirBusqueda()
    {
        $sql = Session::get('query_busqueda_caso');
        if (!$sql) {
            return;
        }
    
        $caso = new Caso();
        $this->casos = $caso->find_all_by_sql($sql);
    
        if (count($this->casos) > 0) {
            $this->modo = 'resultado';
            $this->page_title = 'Resultado de la busqueda';
        } else {
            Flash::error('No se encontraron registros');
        }
    }


     
     /**
     * Método para eliminar
     */
    public function eliminar_caso( $nombre_caso, $key) {      
        
        $url_redir_back = Session::get('url_back');
        if(!$id = Security::getKey($key, 'del_caso', 'int')) {
            return Redirect::to($url_redir_back);
        }        
        
        $caso = new Caso();
                    
        try {
            if($caso->delete($id)) {
                $fuente = new Fuente();
                $fuente->delete_all("tabla='caso' AND tabla_identi=$id");
                Flash::valid("El caso $nombre_caso se ha eliminado correctamente");
                DwAudit::warning("Se ha ELIMINADO el caso $nombre_caso.");
            } else {
                Flash::warning('Lo sentimos, pero este caso no se puede eliminar.');
            }
        } catch(KumbiaException $e) {
            if(stripos($e, 'fk_caso_dano_territorio_caso1')){
                Flash::error('Este caso no se puede eliminar porque se encuentra '
                        . 'relacionado con daños en un territorio');
            }else {
            Flash::error('Este caso no se puede eliminar porque se encuentra relacionado con otro registro.');
            }
        }
        
        
        
        if(Session::get('query_busqueda_caso') != '')
        {
            return Redirect::to('violencia_politica/casos/consulta/si/'); 
        }
    }
    
    public function select_multiple_victima(){
        View::template(null);
        $Victima = new Victima();
        $this->victimas = $Victima->getVictimasByCasoId(Input::post('caso_id'));
    }

    public function db_select_municipios(){
        View::template(null);
        $this->departamento_id = Input::post('departamento_id');
    }

    public function db_select_territorios(){
        View::template(null);
        $this->municipio_id = Input::post('municipio_id');
    }

    public function db_select_barrios(){
        View::template(null);
        $this->territorio_id = Input::post('territorio_id');
    }

    public function db_select_localidades(){
        View::template(null);
        $this->municipio_id = Input::post('municipio_id');
    }

    public function agregar_localidad()
    {
        $Localidad = new Localidad();
        $municipio_id = Input::post('municipio_id');
        $tipo = Input::post('tipo');
        $nombre = Input::post('nombre');

        $Localidad->municipio_id = $municipio_id;
        $Localidad->tipo = $tipo;
        $Localidad->nombre = $nombre;
        $Localidad->save();
        //$this->Localidad = $Localidad->getLocalidadByMunicipioIdSelect($municipio_id, $tipo);
        $this->municipio_id = $municipio_id;
        $this->tipo = $tipo;
        $this->localidad_id = $Localidad->id;
        if($tipo === 'corregimiento'){           
            View::select('db_select_corregimientos');
        }elseif($tipo === 'vereda'){
            View::select('db_select_veredas');
        }elseif($tipo === 'inspeccion'){
            View::select('db_select_inspecciones');
        }   
            
    }

    public function db_select_corregimientos(){}
    public function db_select_veredas(){}
    public function db_select_inspecciones(){}     
}
?>
