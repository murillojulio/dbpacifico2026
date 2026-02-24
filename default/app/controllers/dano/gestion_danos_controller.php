<?php
/**
 * Descripcion: Controlador que se encarga del modal que agrega, edita y eliminar los daños.
 *
 * @category    
 * @package     Controllers  
 */
Load::models('violencia_politica/caso_dano_territorio', 'violencia_politica/caso',
        'observatorio/territorio', 'observatorio/territorio_municipio', 'opcion/dano', 'opcion/tipo_dano',
        'afectacion/afectacion_dano_territorio');
class GestionDanosController extends BackendController { 
    
    public function agregar_dano()
    {
        $CasoDanoTerritorio = new CasoDanoTerritorio();
        $caso_id = Input::post('caso_id');
        $dano_id = Input::post('dano_id');
        $territorio_id = Input::post('territorio_id');
        $descripcion = Input::post('descripcion');
        $CasoDanoTerritorio->caso_id = $caso_id;
        $CasoDanoTerritorio->dano_id = $dano_id;
        $CasoDanoTerritorio->territorio_id = $territorio_id;
        $CasoDanoTerritorio->descripcion = $descripcion;
        $CasoDanoTerritorio->save();
        $this->CasoDanoTerritorio = $CasoDanoTerritorio->getDanoTerritorioByCasoId($caso_id);    
    }
        
    public function eliminar_dano()
    {
        $CasoDanoTerritorio = new CasoDanoTerritorio();
        $caso_dano_territorio_id = Input::post('caso_dano_territorio_id');
        $caso_id = Input::post('caso_id');
        $CasoDanoTerritorio->delete($caso_dano_territorio_id);  
        View::select('agregar_dano');
        $this->CasoDanoTerritorio = $CasoDanoTerritorio->getDanoTerritorioByCasoId($caso_id);         
    }
    
    public function cargar_editar_dano()
    {
        $CasoDanoTerritorio = new CasoDanoTerritorio();
        $caso_dano_territorio_id = Input::post('caso_dano_territorio_id');
        $this->caso_municipio_id = Input::post('caso_municipio_id');
        $this->CasoDanoTerritorio = $CasoDanoTerritorio->find_first($caso_dano_territorio_id); 
        $this->caso_dano_territorio_id = $caso_dano_territorio_id;
        
    }
    
    public function guardar_cambios_dano()
    {
        $CasoDanoTerritorio = new CasoDanoTerritorio();
        $caso_dano_territorio_id = Input::post('caso_dano_territorio_id');
        $CasoDanoTerritorio->find_first($caso_dano_territorio_id);
        $caso_id = Input::post('caso_id');
        $dano_id = Input::post('dano_id');
        $territorio_id = Input::post('territorio_id');
        $descripcion = Input::post('descripcion');
        $CasoDanoTerritorio->dano_id = $dano_id;
        $CasoDanoTerritorio->territorio_id = $territorio_id;
        $CasoDanoTerritorio->descripcion = $descripcion;
        $CasoDanoTerritorio->update();
        View::select('agregar_dano');
        $this->CasoDanoTerritorio = $CasoDanoTerritorio->getDanoTerritorioByCasoId($caso_id);    
    }
    
    public function agregar_dano_afectacion() {
        $AfectacionDanoTerritorio = new AfectacionDanoTerritorio();
        $afectacion_id = Input::post('afectacion_id');
        $dano_id = Input::post('dano_id');
        $territorio_id = Input::post('territorio_id');
        $descripcion = Input::post('descripcion');
        $AfectacionDanoTerritorio->afectacion_id = $afectacion_id;
        $AfectacionDanoTerritorio->dano_id = $dano_id;
        $AfectacionDanoTerritorio->territorio_id = $territorio_id;
        $AfectacionDanoTerritorio->descripcion = $descripcion;
        $AfectacionDanoTerritorio->save();
        $this->AfectacionDanoTerritorio = $AfectacionDanoTerritorio->getDanoTerritorioByAfectacionId($afectacion_id);                
    }
    
    public function eliminar_dano_afectacion()
    {
        $AfectacionDanoTerritorio = new AfectacionDanoTerritorio();
        $afectacion_dano_territorio_id = Input::post('afectacion_dano_territorio_id');
        $afectacion_id = Input::post('afectacion_id');
        $AfectacionDanoTerritorio->delete($afectacion_dano_territorio_id);  
        View::select('agregar_dano_afectacion');
        $this->AfectacionDanoTerritorio = $AfectacionDanoTerritorio->getDanoTerritorioByAfectacionId($afectacion_id);         
    }

    public function cargar_editar_dano_afectacion()
    {
        $AfectacionDanoTerritorio = new AfectacionDanoTerritorio();
        $afectacion_dano_territorio_id = Input::post('afectacion_dano_territorio_id');
        $this->AfectacionDanoTerritorio = $AfectacionDanoTerritorio->find_first($afectacion_dano_territorio_id); 
        $this->afectacion_dano_territorio_id = $afectacion_dano_territorio_id;
        $this->afectacion_id = Input::post('afectacion_id');
        $this->territorio_id = Input::post('territorio_id');
    }
    
    public function guardar_cambios_dano_afectacion()
    {
        $AfectacionDanoTerritorio = new AfectacionDanoTerritorio();
        $afectacion_dano_territorio_id = Input::post('afectacion_dano_territorio_id');
        $AfectacionDanoTerritorio->find_first($afectacion_dano_territorio_id);
        $afectacion_id = Input::post('afectacion_id');
        $dano_id = Input::post('dano_id');
        $territorio_id = Input::post('territorio_id');
        $descripcion = Input::post('descripcion');
        $AfectacionDanoTerritorio->dano_id = $dano_id;
        $AfectacionDanoTerritorio->territorio_id = $territorio_id;
        $AfectacionDanoTerritorio->descripcion = $descripcion;
        $AfectacionDanoTerritorio->update();
        View::select('agregar_dano_afectacion');
        $this->AfectacionDanoTerritorio = $AfectacionDanoTerritorio->getDanoTerritorioByAfectacionId($afectacion_id);    
    }
}
?>
