<?php
/** 
 *
 * Clase que gestiona todo lo relacionado con los
 * victimas y con su respectivo antecedente_violencia
 *
 * @category
 * @package     Models 
 */

class VictimaHechovictimizantePresuntoResponsable extends ActiveRecord {
    
    //Se desabilita el logger para no llenar el archivo de "basura"
    public $logger = FALSE;
        
    /**
     * Método para definir las relaciones y validaciones
     */
    protected function initialize() {
        $this->belongs_to('victima');
        $this->belongs_to('hechovictimizante');
        $this->belongs_to('presunto_responsable');
    }

    
    
    
    public function getVictimaHechovictimizantePresuntoResponsable($victima_id) 
    {                   
        $columns = 'victima_hechovictimizante_presunto_responsable.*, antecedente_violencia.nombre AS antecedente_violencia';        
        $join = 'INNER JOIN antecedente_violencia ON antecedente_violencia.id = victima_hechovictimizante_presunto_responsable.antecedente_violencia_id';
        $conditions = 'victima_hechovictimizante_presunto_responsable.id IS NOT NULL AND victima_hechovictimizante_presunto_responsable.victima_id='.$victima_id;  
        return $this->find("columns: $columns", "join: $join", "conditions: $conditions");        
    }
    
      
    
    public function guardar($dataHechos, $dataVictimas, $presunto_responsable_id, $descripcion_presunto_responsable)
    { 
        foreach ($dataHechos as $valueHecho) {
            foreach ($dataVictimas as $valueVictima) {
                $obj_VictimaHechovictimizantePresuntoResponsable = new VictimaHechovictimizantePresuntoResponsable();
                $obj_VictimaHechovictimizantePresuntoResponsable->victima_id = $valueVictima;
                $obj_VictimaHechovictimizantePresuntoResponsable->hechovictimizante_id = $valueHecho;
                $obj_VictimaHechovictimizantePresuntoResponsable->presunto_responsable_id = $presunto_responsable_id;
                $obj_VictimaHechovictimizantePresuntoResponsable->descripcion_presunto_responsable = $descripcion_presunto_responsable;
                $obj_VictimaHechovictimizantePresuntoResponsable->save();
            }
        }    
        return TRUE;
    }
    
     public function getVictimaHechovictimizantePresuntoResponsableByVictimaId($victima_id) 
    {                   
      return $this->find_all_by_sql("SELECT victima_hechovictimizante_presunto_responsable.*, 
          presunto_responsable.nombre AS nombre_presunto_responsable, victima.nombre AS nombre_victima,
          hechovictimizante.nombre AS nombre_hechovictimizante
 FROM victima_hechovictimizante_presunto_responsable INNER JOIN presunto_responsable ON 
 victima_hechovictimizante_presunto_responsable.presunto_responsable_id = presunto_responsable.id 
 INNER JOIN victima ON victima_hechovictimizante_presunto_responsable.victima_id = victima.id 
 INNER JOIN hechovictimizante ON victima_hechovictimizante_presunto_responsable.hechovictimizante_id = hechovictimizante.id
 WHERE victima_id=$victima_id ORDER BY nombre_presunto_responsable ASC");
    }     
}
?>