<?php
// Jalamos las librerias de dompdf
// require_once './dompdf/autoload.inc.php';
require_once("dompdf/dompdf_config.inc.php");
// Inicializamos dompdf
$dompdf = new Dompdf();
// Le pasamos el html a dompdf
$dompdf->load_html('hello world');
// Colocamos als propiedades de la hoja
$dompdf->set_paper("A4", "landscape");
// Escribimos el html en el PDF
$dompdf->render();
// Ponemos el PDF en el browser
$dompdf->stream();
?>