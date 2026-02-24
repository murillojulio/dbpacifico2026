<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * victimas y con su respectivo antecedente_violencia
 *
 * @category
 * @package     Models 
 */

class AfectacionDanoTerritorio extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('afectacion');
        $this->belongs_to('dano');
        $this->belongs_to('territorio');
    } 
    
    public function getDanoTerritorioByAfectacionId($afectacion_id) 
    {                   
        $columns = 'afectacion_dano_territorio.*, territorio.nombre AS territorio, dano.nombre AS dano';        
        $join = 'INNER JOIN territorio ON territorio.id = afectacion_dano_territorio.territorio_id '
                . 'INNER JOIN dano ON dano.id = afectacion_dano_territorio.dano_id';
        $conditions = 'afectacion_dano_territorio.id IS NOT NULL AND afectacion_dano_territorio.afectacion_id='.$afectacion_id;  
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions");        
    }
}
?>