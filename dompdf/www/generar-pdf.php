<?php
require_once("../conexion.php");
require_once("../dompdf_config.inc.php");
$conexion = new Conexion();

// We check wether the user is accessing the demo locally
$local = array("::1", "127.0.0.1");
$is_local = in_array($_SERVER["REMOTE_ADDR"], $local);

$fecha = $_GET["fecha"];
$productos = null;
$egresos = null;

if ( isset( $_POST["html"] )) {

  if ( get_magic_quotes_gpc() ) {
    $_POST["html"] = stripslashes($_POST["html"]);   
  }
  
  $dompdf = new DOMPDF();
  $dompdf->load_html($_POST["html"]);
  $dompdf->set_paper($_POST["paper"], $_POST["orientation"]);
  $dompdf->render();

  $dompdf->stream($_POST["nombre"], array("Attachment" => false));

  exit(0);
}
?>
