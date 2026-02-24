<?php
/**
 * Descripcion: Controlador que se encarga de los documentos que se suben al sistema
 *
 * @category    
 * @package     Controllers  
 */
class UploadController extends BackendController {
    
    /**
     * Método para subir documentos
     */
    public function upload_document($documento = '', $destino = '') {   
        
        $ruta = '';
        if($destino == 'politica_publica'){$ruta = 'files/upload/politica_publica';}
        if($destino == 'desarrollo_normativo'){$ruta = 'files/upload/desarrollo_normativo';}
        if($destino == 'megaproyecto'){$ruta = 'files/upload/megaproyecto';}
        
        
        
        $upload = new DwUpload($documento, $ruta);
        $upload->setAllowedTypes('pdf|doc|docx');
        //$upload->setAllowedTypes('*');
        $upload->setEncryptNameWithLogin(TRUE);
        //$upload->setSize('50MB', 170, 200, TRUE);
        if(!$data = $upload->save()) { //retorna un array('path'=>'ruta', 'name'=>'nombre.ext');
            $data = array('error'=>TRUE, 'message'=>$upload->getError());
        }
        sleep(1);//Por la velocidad del script no permite que se actualize el archivo
        $this->data = $data;
        View::json();
    }
}
?>
