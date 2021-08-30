<?php

require_once("dompdf/dompdf_config.inc.php");

$dompdf = new DOMPDF();
$dompdf->load_html(file_get_contents('testing.html'));
$dompdf->render();
$dompdf->stream("ejemplo-basico.pdf", array('Attachment' => 0));