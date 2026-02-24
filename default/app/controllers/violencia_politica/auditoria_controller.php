<?php

Load::models('violencia_politica/caso', 'observatorio/municipio', 'observatorio/departamento',
'observatorio/territorio', 'violencia_politica/victima', 
'violencia_politica/victima_hechovictimizante_presunto_responsable',
'violencia_politica/victima_caracterizacion2', 'violencia_politica/victima_etnia2');

class AuditoriaController extends BackendController {
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

    public function listar(){
        $this->page_title = 'Auditoría Información Faltante';
        $sqlQuery = "SELECT caso.*, departamento.nombre AS departamento 
        FROM caso INNER JOIN departamento ON caso.departamento_id = departamento.id ORDER BY caso.id ASC";
        $Caso = new Caso();
        $this->Casos = $Caso->find_all_by_sql($sqlQuery);
    }
}

?>