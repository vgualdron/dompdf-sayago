<?php

require_once("../dompdf_config.inc.php");

$html = $_POST["html"];

if (!empty($html)) {
  $name = $_POST["nombre"];
  if ( get_magic_quotes_gpc() ) {
    $_POST["html"] = stripslashes($html);   
  }
  
  $dompdf = new Dompdf();
  $dompdf->load_html($html);
  $dompdf->set_paper("A4", "landscape");
  $dompdf->render();

  $dompdf->stream($name, array("Attachment" => false));

  exit(0);
}
?>
