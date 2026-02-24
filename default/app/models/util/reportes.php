<?php

/**
 * Descripcion: Controlador que se encarga de la gestión de reporte en formato pdf
 *
 * @category    
 * @package     Controllers  
 */
Load::models(
    'observatorio/territorio',
    'observatorio/departamento',
    'observatorio/municipio',
    'observatorio/poblacion',
    'observatorio/titulado_si',
    'observatorio/titulado_no',
    'observatorio/territorio_municipio',
    'observatorio/comunidad',
    'observatorio/conflicto',
    'observatorio/fuente',
    'util/currency',
    'util/pdf'
);
class Reportes
{
    public function generarPdf($id)
    {
        $url_reporte = 'http://www.dbpacifico.creando.net';
        $pdf = new PDF();

        $this->territorio_id = $id;
        $obj_territorio = new Territorio();
        if (!$obj_territorio->getTerritorioById($id)) {
            Flash::error('Lo sentimos, no se pudo establecer la información del territorio');
            return Redirect::toAction('listar_cn');
        }
        $this->obj_territorio = $obj_territorio;
        $obj_territorio_municipio = new TerritorioMunicipio();
        $ubicaciones = $obj_territorio_municipio->getDepartamentoMunicipioByTerritorioId($id);

        // Load::lib('fpdf182/fpdf');
        $data2 = array();
        $contador = 0;
        foreach ($ubicaciones as $ubicacion) {
            $array = array("$ubicacion->subregion_nombre", "$ubicacion->departamento_nombre", "$ubicacion->municipio_nombre");
            $data2[$contador] = $array;
            $contador++;
        }
        $data = array();

        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 14);
        if ($obj_territorio->tipo === 'comunidad_negra') {
            $pdf->WriteHTML('<b>Territorio Colectivo Comunidades Negras</b>');
        } elseif ($obj_territorio->tipo === 'indigena') {
            $pdf->WriteHTML('<b>Territorio Colectivo Resguardos Indigenas</b>');
        } elseif ($obj_territorio->tipo === 'urbano') {
            $pdf->WriteHTML('<b>Territorio Urbano</b>');
        }
        $pdf->Ln(7);
        $pdf->SetFont('Arial', '', 12);
        $pdf->WriteHTML('<b>Nombre del territorio:</b> ' . $obj_territorio->nombre);
        $pdf->Ln();
        $pdf->WriteHTML('<b>Ubicación del territorio</b>');
        $pdf->Ln();
        // Títulos de las columnas
        $header = array('Subregión', 'Departamento', 'Municipio');
        $pdf->BasicTable($header, $data2, 60, 15);
        $pdf->Ln();
        $pdf->WriteHTML('<b>¿Territorio titulado?: </b>' . $obj_territorio->titulado);
        $pdf->Ln();
        if ($obj_territorio->titulado == 'SI') {
            $obj_titulado_si = new TituladoSi();
            $obj_titulado_si->getTituladoSiByTerritorioId($id);
            $pdf->WriteHTML('<b>Resolución de constitución #:</b> ' . $obj_titulado_si->resolucion_constitucion);
            $pdf->Ln(5);
            $pdf->WriteHTML('<b>Área titulada: </b>' . $obj_titulado_si->area_titulada . ' ha');
            $pdf->Ln(5);
            $pdf->WriteHTML('<b>Límite / Lindero: </b>' . $obj_titulado_si->limite_lindero);
            $pdf->Ln(7);
            if($obj_titulado_si->documento_constitucion){
                $documento_constitucion = '<b>Documento constitución:</b> <a href="'.$url_reporte.'/files/upload/territorio/resolucion/documento_constitucion/'.$obj_titulado_si->documento_constitucion .'">Ver documento</a>';
                $pdf->WriteHTML($documento_constitucion);
            }else{
                $pdf->WriteHTML("<b>Documento constitución:</b>");
            }
            $pdf->Ln(7);
            $pdf->SetFont('Arial', '', 14);
            $pdf->WriteHTML('<b><u>Solicitud de ampliación</u></b>');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Ln(5);
            $pdf->WriteHTML('<b>¿Tiene solicitud de ampliación?:</b> ' . $obj_titulado_si->solicitud_ampliacion_si_no);
            if ($obj_titulado_si->solicitud_ampliacion_si_no == 'SI') {
                $pdf->Ln(5);
                $pdf->WriteHTML('<b>Fecha de la solicitud: </b>' . $obj_titulado_si->solicitud_ampliacion_fecha);
                $pdf->Ln();
                $pdf->WriteHTML('<b>Resolución: </b>' . $obj_titulado_si->solicitud_ampliacion_resolucion);
                $pdf->Ln();
                $pdf->WriteHTML('<b>Área solicitada: </b>' . $obj_titulado_si->solicitud_ampliacion_area . ' ha');
                $pdf->Ln();
                $pdf->WriteHTML('<b>Límite / Lindero:</b> ' . $obj_titulado_si->solicitud_ampliacion_lindero_limite);
                $pdf->Ln();
                $pdf->WriteHTML('<b>Observaciones:</b> ' . $obj_titulado_si->solicitud_ampliacion_observacion);
                $pdf->Ln();
                $documento_ampliacion = '<b>Documento solicitud ampliación:</b> <a href="'.$url_reporte.'/files/upload/territorio/resolucion/documento_ampliacion/' . $obj_titulado_si->solicitud_ampliacion_documento . '">Ver documento</a>';
                $pdf->WriteHTML($documento_ampliacion);
            }

            $pdf->Ln(7);
            $pdf->SetFont('Arial', '', 14);
            $pdf->WriteHTML('<b><u>Solicitud de saneamiento</u></b>');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Ln(5);
            $pdf->WriteHTML('<b>¿Tiene solicitud de saneamiento?:</b> ' . $obj_titulado_si->solicitud_saneamiento_si_no);
            if ($obj_titulado_si->solicitud_saneamiento_si_no == 'SI') {
                $pdf->Ln(5);
                $pdf->WriteHTML('<b>Fecha de la solicitud: </b>' . $obj_titulado_si->solicitud_saneamiento_fecha);
                $pdf->Ln();
                $pdf->WriteHTML('<b>Resolución: </b>' . $obj_titulado_si->solicitud_saneamiento_resolucion);
                $pdf->Ln();
                $pdf->WriteHTML('<b>Área solicitada: </b>' . $obj_titulado_si->solicitud_saneamiento_area . ' ha');
                $pdf->Ln();
                $pdf->WriteHTML('<b>Límite / Lindero:</b> ' . $obj_titulado_si->solicitud_saneamiento_lindero_limite);
                $pdf->Ln();
                $pdf->WriteHTML('<b>Observaciones:</b> ' . $obj_titulado_si->solicitud_saneamiento_observacion);
                $pdf->Ln();
                $documento_saneamiento = '<b>Documento solicitud saneamiento:</b> <a href="'.$url_reporte.'/files/upload/territorio/resolucion/documento_saneamiento/' . $obj_titulado_si->solicitud_saneamiento_documento . '">Ver documento</a>';
                $pdf->WriteHTML($documento_saneamiento);
            }
        } elseif ($obj_territorio->titulado == 'NO') {
            $obj_titulado_no = new TituladoNo();
            $obj_titulado_no->getTituladoNoByTerritorioId($id);
            $pdf->SetFont('Arial', '', 14);
            $pdf->WriteHTML('<b><u>Solicitud de título</u></b>');
            $pdf->Ln();
            $pdf->SetFont('Arial', '', 12);
            $pdf->WriteHTML('<b>Fecha de la solicitud: </b>' . $obj_titulado_no->solicitud_titulo_fecha);
            $pdf->Ln();
            $pdf->WriteHTML('<b>Área solicitada: </b>' . $obj_titulado_no->solicitud_titulo_area . ' ha');
            $pdf->Ln();
            $pdf->WriteHTML('<b>Límite / Lindero:</b> ' . $obj_titulado_no->solicitud_titulo_limite_lindero);
            $pdf->Ln();
            $pdf->WriteHTML('<b>Observaciones:</b> ' . $obj_titulado_no->solicitud_titulo_observacion);
            $pdf->Ln();
            $documento_solicitud_titulo = '<b>Documento solicitud título:</b> <a href="'.$url_reporte.'/files/upload/territorio/resolucion/documento_solicitud_titulo/' . $obj_titulado_no->documento_solicitud_titulo . '">Ver documento</a>';
            $pdf->WriteHTML($documento_solicitud_titulo);
        }

        $poblacion = new Poblacion();
        $poblacion = Poblacion::getPoblacion('territorio_id', $id);

        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 14);
        $pdf->WriteHTML('<b><u>POBLACIÓN</u></b>');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Ln(7);
        $pdf->WriteHTML('<b> Total: </b>' . $poblacion->total);
        $pdf->Ln();
        $pdf->WriteHTML('<b> Mujeres: </b>' . $poblacion->mujer);
        $pdf->Ln();
        $pdf->WriteHTML('<b> Hombres: </b>' . $poblacion->hombre);
        $pdf->Ln();
        $pdf->WriteHTML('<b> Número de familias: </b>' . $poblacion->numero_familia);
        $pdf->Ln();
        $pdf->WriteHTML('<b> Cantidad de niños con registro civil en edad de primera infancia: </b>' . $obj_territorio->cant_ninos_primera_inf);
        $pdf->Ln();
        $pdf->WriteHTML('<b> Cantidad de niños sin registro civil en edad de primera infancia: </b>' . $obj_territorio->cant_sin_ninos_primera_inf);
        $pdf->Ln(7);


        $header_tabla_poblacion = array('Rango edad (años)', 'Mujeres', 'Hombres', 'Total');
        $array_rango_edad = array();
        $e0_4 = array("0 - 4", "$poblacion->m_e0_4", "$poblacion->h_e0_4", "$poblacion->e0_4");
        $array_rango_edad[0] = $e0_4;
        $e5_9 = array("5 - 9", "$poblacion->m_e5_9", "$poblacion->h_e5_9", "$poblacion->e5_9");
        $array_rango_edad[1] = $e5_9;
        $e10_14 = array("10 - 14", "$poblacion->m_e10_14", "$poblacion->h_e10_14", "$poblacion->e10_14");
        $array_rango_edad[2] = $e10_14;
        $e15_19 = array("15 - 19", "$poblacion->m_e15_19", "$poblacion->h_e15_19", "$poblacion->e15_19");
        $array_rango_edad[3] = $e15_19;
        $e20_24 = array("20 - 24", "$poblacion->m_e20_24", "$poblacion->h_e20_24", "$poblacion->e20_24");
        $array_rango_edad[4] = $e20_24;
        $e25_29 = array("25 - 29", "$poblacion->m_e25_29", "$poblacion->h_e25_29", "$poblacion->e25_29");
        $array_rango_edad[5] = $e25_29;
        $e30_34 = array("30 - 34", "$poblacion->m_e30_34", "$poblacion->h_e30_34", "$poblacion->e30_34");
        $array_rango_edad[6] = $e30_34;
        $e35_39 = array("35 - 39", "$poblacion->m_e35_39", "$poblacion->h_e35_39", "$poblacion->e35_39");
        $array_rango_edad[7] = $e35_39;
        $e40_44 = array("40 - 44", "$poblacion->m_e40_44", "$poblacion->h_e40_44", "$poblacion->e40_44");
        $array_rango_edad[8] = $e40_44;
        $e45_49 = array("45 - 49", "$poblacion->m_e45_49", "$poblacion->h_e45_49", "$poblacion->e45_49");
        $array_rango_edad[9] = $e45_49;
        $e50_54 = array("50 - 54", "$poblacion->m_e50_54", "$poblacion->h_e50_54", "$poblacion->e50_54");
        $array_rango_edad[10] = $e50_54;
        $e55_59 = array("55 - 59", "$poblacion->m_e55_59", "$poblacion->h_e55_59", "$poblacion->e55_59");
        $array_rango_edad[11] = $e55_59;
        $e60_64 = array("60 - 64", "$poblacion->m_e60_64", "$poblacion->h_e60_64", "$poblacion->e60_64");
        $array_rango_edad[12] = $e60_64;
        $e65_69 = array("65 - 69", "$poblacion->m_e65_69", "$poblacion->h_e65_69", "$poblacion->e65_69");
        $array_rango_edad[13] = $e65_69;
        $e70_74 = array("70 - 74", "$poblacion->m_e70_74", "$poblacion->h_e70_74", "$poblacion->e70_74");
        $array_rango_edad[14] = $e70_74;
        $e75_79 = array("75 - 79", "$poblacion->m_e75_79", "$poblacion->h_e75_79", "$poblacion->e75_79");
        $array_rango_edad[15] = $e75_79;
        $e80_mas = array("80 ó mas", "$poblacion->m_e80_mas", "$poblacion->h_e80_mas", "$poblacion->e80_mas");
        $array_rango_edad[16] = $e80_mas;
        $pdf->BasicTable($header_tabla_poblacion, $array_rango_edad, 40, 15);

        /************************* */
        /************************* */

        $obj_comunidades = new Comunidad();
        $comunidades = $obj_comunidades->getComunidadesByTerritorioId($id);

        $cantidad_comunidad = 0;
        foreach ($comunidades as $registros) :
            $cantidad_comunidad++;
        endforeach;

        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 14);
        $pdf->WriteHTML('<b><u>COMUNIDADES</u></b>');
        $pdf->Ln(7);
        $pdf->SetFont('Arial', '', 12);
        $pdf->WriteHTML("<b>Cantidad de comunidades: </b>$cantidad_comunidad");
        $pdf->Ln(7);
        foreach ($comunidades as $comunidad) :
            $pdf->WriteHTML("   <b><u>$comunidad->nombre</u></b>");
            $pdf->Ln();
            $pdf->WriteHTML("       <b>Fecha de creación: </b>$comunidad->fecha_creacion");
            $pdf->Ln();
            $pdf->WriteHTML("       <b>Fecha de disolución: </b>$comunidad->fecha_disolucion");
            $pdf->Ln();
            $pdf->WriteHTML("       <b>Descripción de la ubicación geográfica de la comunidad</b>");
            $pdf->Ln();
            $pdf->SetX(25);
            $pdf->MultiCell(0, 5, utf8_decode($comunidad->descripcion_ubicacion));
            $pdf->Ln();
            $poblacion = Poblacion::getPoblacion('comunidad_id', $comunidad->id);
            $pdf->WriteHTML('       <b><u>Población</u></b>');
            $pdf->Ln(7);
            $pdf->WriteHTML('       <b>Total: </b>' . $poblacion->total);
            $pdf->Ln();
            $pdf->WriteHTML('       <b>Mujeres: </b>' . $poblacion->mujer);
            $pdf->Ln();
            $pdf->WriteHTML('       <b>Hombres: </b>' . $poblacion->hombre);
            $pdf->Ln();
            $pdf->WriteHTML('       <b>Número de familias: </b>' . $poblacion->numero_familia);
            $pdf->Ln();
            $array_rango_edad = array();
            $e0_4 = array("0 - 4", "$poblacion->m_e0_4", "$poblacion->h_e0_4", "$poblacion->e0_4");
            $array_rango_edad[0] = $e0_4;
            $e5_9 = array("5 - 9", "$poblacion->m_e5_9", "$poblacion->h_e5_9", "$poblacion->e5_9");
            $array_rango_edad[1] = $e5_9;
            $e10_14 = array("10 - 14", "$poblacion->m_e10_14", "$poblacion->h_e10_14", "$poblacion->e10_14");
            $array_rango_edad[2] = $e10_14;
            $e15_19 = array("15 - 19", "$poblacion->m_e15_19", "$poblacion->h_e15_19", "$poblacion->e15_19");
            $array_rango_edad[3] = $e15_19;
            $e20_24 = array("20 - 24", "$poblacion->m_e20_24", "$poblacion->h_e20_24", "$poblacion->e20_24");
            $array_rango_edad[4] = $e20_24;
            $e25_29 = array("25 - 29", "$poblacion->m_e25_29", "$poblacion->h_e25_29", "$poblacion->e25_29");
            $array_rango_edad[5] = $e25_29;
            $e30_34 = array("30 - 34", "$poblacion->m_e30_34", "$poblacion->h_e30_34", "$poblacion->e30_34");
            $array_rango_edad[6] = $e30_34;
            $e35_39 = array("35 - 39", "$poblacion->m_e35_39", "$poblacion->h_e35_39", "$poblacion->e35_39");
            $array_rango_edad[7] = $e35_39;
            $e40_44 = array("40 - 44", "$poblacion->m_e40_44", "$poblacion->h_e40_44", "$poblacion->e40_44");
            $array_rango_edad[8] = $e40_44;
            $e45_49 = array("45 - 49", "$poblacion->m_e45_49", "$poblacion->h_e45_49", "$poblacion->e45_49");
            $array_rango_edad[9] = $e45_49;
            $e50_54 = array("50 - 54", "$poblacion->m_e50_54", "$poblacion->h_e50_54", "$poblacion->e50_54");
            $array_rango_edad[10] = $e50_54;
            $e55_59 = array("55 - 59", "$poblacion->m_e55_59", "$poblacion->h_e55_59", "$poblacion->e55_59");
            $array_rango_edad[11] = $e55_59;
            $e60_64 = array("60 - 64", "$poblacion->m_e60_64", "$poblacion->h_e60_64", "$poblacion->e60_64");
            $array_rango_edad[12] = $e60_64;
            $e65_69 = array("65 - 69", "$poblacion->m_e65_69", "$poblacion->h_e65_69", "$poblacion->e65_69");
            $array_rango_edad[13] = $e65_69;
            $e70_74 = array("70 - 74", "$poblacion->m_e70_74", "$poblacion->h_e70_74", "$poblacion->e70_74");
            $array_rango_edad[14] = $e70_74;
            $e75_79 = array("75 - 79", "$poblacion->m_e75_79", "$poblacion->h_e75_79", "$poblacion->e75_79");
            $array_rango_edad[15] = $e75_79;
            $e80_mas = array("80 ó mas", "$poblacion->m_e80_mas", "$poblacion->h_e80_mas", "$poblacion->e80_mas");
            $array_rango_edad[16] = $e80_mas;
            $pdf->BasicTable($header_tabla_poblacion, $array_rango_edad, 40, 20);
            $pdf->Ln(7);
        endforeach;

        /************************* */
        /************************* */

        $pdf->SetFont('Arial', '', 14);
        $pdf->WriteHTML('<b><u>CONFLICTOS</u></b>');
        $pdf->Ln(7);
        $pdf->SetFont('Arial', '', 12);
        $obj_conflictos = new Conflicto();
        $conflictos = $obj_conflictos->getConflictosByTerritorioId($id);
        foreach ($conflictos as $conflicto) :
            $pdf->WriteHTML("   <b><u>$conflicto->nombre</u></b>");
            $pdf->Ln();

            $cadena_tipo_conflicto = '';
            if ($conflicto->inter_etnico == 1) {
                $cadena_tipo_conflicto .= "Inter étnico, ";
            }
            if ($conflicto->intra_etnico == 1) {
                $cadena_tipo_conflicto .= "Intra étnico, ";
            }
            if ($conflicto->cultural == 1) {
                $cadena_tipo_conflicto .= "Cultural, ";
            }
            if ($conflicto->economico == 1) {
                $cadena_tipo_conflicto .= "Económico, ";
            }

            if ($conflicto->recurso_natural == 1) {
                $cadena_tipo_conflicto .= "Recursos naturales, ";
            }
            if ($conflicto->politico_violencia == 1) {
                $cadena_tipo_conflicto .= "Político - Violencia, ";
            }
            if ($conflicto->politico_grupo_armado == 1) {
                $cadena_tipo_conflicto .= "Político - Presencia de grupos armados, ";
            }
            if ($conflicto->politico_electoral == 1) {
                $cadena_tipo_conflicto .= "Político - Electoral, ";
            }

            if ($conflicto->territorial_uso == 1) {
                $cadena_tipo_conflicto .= "Territorial - Uso, ";
            }
            if ($conflicto->territorial_delimitacion == 1) {
                $cadena_tipo_conflicto .= "Territorial - Delimitación, ";
            }
            if ($conflicto->otros == 1) {
                $cadena_tipo_conflicto .= "Otro";
            }
            $pdf->SetX(20);
            $pdf->WriteHTML("<b>Tipo de conflicto:</b> " . $cadena_tipo_conflicto);
            $pdf->Ln(7);

            $pdf->SetX(20);
            $pdf->WriteHTML("<b>Descripción del conflicto</b> ");
            $pdf->Ln();
            $pdf->SetX(25);
            $pdf->WriteHTML("<b>Hechos</b> ");
            $pdf->Ln();
            $pdf->SetX(30);
            $pdf->MultiCell(0, 5, utf8_decode($conflicto->hechos));
            $pdf->Ln(1);
            $pdf->SetX(25);
            $pdf->WriteHTML("<b>Actores</b> ");
            $pdf->Ln();
            $pdf->SetX(30);
            $pdf->MultiCell(0, 5, utf8_decode($conflicto->actores));
            $pdf->Ln(1);
            $pdf->SetX(25);
            $pdf->WriteHTML("<b>Ubicación</b> ");
            $pdf->Ln();
            $pdf->SetX(30);
            $pdf->MultiCell(0, 5, utf8_decode($conflicto->ubicacion));
            $pdf->Ln(1);
            $pdf->SetX(25);
            $pdf->WriteHTML("<b>Observaciones</b> ");
            $pdf->Ln();
            $pdf->SetX(30);
            $pdf->MultiCell(0, 5, utf8_decode($conflicto->observacion));
            $pdf->Ln();

            $pdf->SetX(20);
            $pdf->WriteHTML("<b>Estado del conflicto: </b>$conflicto->estado_conflicto");
            $pdf->Ln();
            $pdf->SetX(20);
            $pdf->WriteHTML("<b>Fecha comienzo del conflicto: </b>$conflicto->fecha_comienzo");
            $pdf->Ln();
            $pdf->SetX(20);
            $pdf->WriteHTML("<b>Fecha terminación del conflicto: </b>$conflicto->fecha_fin");
            $pdf->Ln();
        endforeach;




        $pdf->SetFont('Arial', '', 12);
        $pdf->Ln(7);


        $fuentes = new Fuente();
        $obj_fuente = $fuentes->getListadoFuente('territorio', $obj_territorio->id);
        $pdf->SetFont('Arial', '', 14);
        $pdf->WriteHTML('<b><u>FUENTE(S) DE LA INFORMACIÓN</u></b>');
        $pdf->Ln(7);
        $pdf->SetFont('Arial', '', 12);

        foreach ($obj_fuente as $fuente) :
            $pdf->SetX(15);
            $pdf->MultiCell(0, 5, utf8_decode($fuente->nombre . " / fecha de la fuente: " . $fuente->fecha));
            $pdf->Ln();
        endforeach;




        //$pdf->Cell(40,10, utf8_decode('¡Hola, Mundo!'));
        //$pdf = new PDF();

        /*
$pdf->AddPage();
$pdf->ImprovedTable($header,$data);
$pdf->AddPage();
$pdf->FancyTable($header,$data);
*/
        $pdf->Output();
    }
}
