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

  $dompdf->stream("cierre_de_caja_".$_GET["fecha"].".pdf", array("Attachment" => false));

  exit(0);
}

if (isset($_GET['fecha'])) {
    $sql = $conexion->prepare("SELECT 
                                DISTINCT 
                                prod.prod_descripcion as descripcion,
                                SUM(depe.prod_cantidad) as cantidad,
                                depe.prod_precio as precio,
                                SUM(depe.prod_precio * depe.prod_cantidad) as total, 
                                pedi.* 
                                FROM pinchetas_restaurante.producto prod
                                left join pinchetas_restaurante.detallepedido depe on (depe.prod_id = prod.prod_id)
                                INNER join pinchetas_restaurante.pedido pedi on (pedi.pedi_id = depe.pedi_id)
                                inner join pinchetas_restaurante.estadopedido espe on (espe.espe_id = pedi.espe_id)
                                WHERE espe.espe_descripcion = 'FACTURADO'
                                and pedi.pedi_fecha = ?
                                GROUP BY prod.prod_id
                                ORDER BY prod.prod_descripcion;");
    $sql->bindValue(1, $_GET['fecha']);
    $sql->execute();
    $sql->setFetchMode(PDO::FETCH_ASSOC);
    $productos = $sql->fetchAll();
    
    $sql = $conexion->prepare("SELECT distinct
                                gast.gast_id as id,
                                gast.gast_descripcion as descripcion,
                                gast.gast_valor as valor,
                                gast.gast_fecha as fecha,
                                CONCAT(pena.pena_primernombre,' ', pena.pena_primerapellido) as nombrepersona
                                FROM pinchetas_restaurante.gasto gast
                                inner join pinchetas_general.personageneral pege on (pege.pege_id = gast.pege_idregistrador)
                                inner join pinchetas_general.personanatural pena on (pena.pege_id = pege.pege_id)
                                where gast.gast_fecha = ?
                                order by gast.gast_fecha; ");
    $sql->bindValue(1, $_GET['fecha']);
    $sql->execute();
    $sql->setFetchMode(PDO::FETCH_ASSOC);
    $egresos = $sql->fetchAll();
}

?>
<?php // include("head.inc"); ?>
<!DOCTYPE html>
<html lang="es-CO">
    <title>Cierre de caja</title>
<body style="max-width: 900px;text-align:center;margin:auto;">
<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post" target="_blank">

<?php
$html="<h2 style='text-align:center'>Cierre de caja del dia ".$fecha." </h2>";

$html.="<table style='width:100%;'  cellspacing='0' cellpadding='0'>";
$html.="  <tr style='border: solid 1px black;'>";
$html.="    <th style='width:40%; border: solid 1px black; padding: 10px;'>PRODUCTO</th>";
$html.="    <th style='width:20%; border: solid 1px black; padding: 10px;'>PRECIO</th>";
$html.="    <th style='width:20%; border: solid 1px black; padding: 10px;'>CANTIDAD</th>";
$html.="    <th style='width:20%; border: solid 1px black; padding: 10px;'>TOTAL</th>";
$html.="  </tr>";

$totalVentas = 0;
    
foreach ($productos as $clave => $producto) {
    $totalVentas = $totalVentas + $producto["total"];
    $html.="  <tr style='border: solid 1px black;'>";
    $html.="    <td style='border: solid 1px black;padding: 5px;text-align:center;'>".$producto["descripcion"]."</td>";
    $html.="    <td style='border: solid 1px black;padding: 5px;text-align:center;'>$".number_format($producto["precio"], 0, ',', '.')."</td>";
    $html.="    <td style='border: solid 1px black;padding: 5px;text-align:center;'>".$producto["cantidad"]."</td>";
    $html.="    <td style='border: solid 1px black;padding: 5px;text-align:center;'>$".number_format($producto["total"], 0, ',', '.')."</td>";
    $html.="  </tr>";
}
$html.="  <tr style='border: solid 1px black;'>";
$html.="    <td style='width:40%; padding: 10px;'></td>";
$html.="    <td style='width:20%; padding: 10px;'></td>";
$html.="    <td style='width:20%; border: solid 1px black; padding: 10px;text-align:center;font-weight:bold;'>SUB. TOTAL</td>";
$html.="    <td style='width:20%; border: solid 1px black; padding: 10px;text-align:center;font-weight:bold;'>$".number_format($totalVentas, 0, ',', '.')."</td>";
$html.="  </tr>";

$html.="</table>";
    
$html.="<br>";

$html.="<table style='width:100%;'  cellspacing='0' cellpadding='0'>";
$html.="  <tr style='border: solid 1px black;'>";
$html.="    <th style='width:70%; border: solid 1px black; padding: 10px;'>EGRESO</th>";
$html.="    <th style='width:30%; border: solid 1px black; padding: 10px;'>COSTO</th>";
$html.="  </tr>";

$totalEgresos = 0;
    
foreach ($egresos as $clave => $egreso) {
    $totalEgresos = $totalEgresos + $egreso["valor"];
    $html.="  <tr style='border: solid 1px black;'>";
    $html.="    <td style='border: solid 1px black;padding: 5px;text-align:center;'>".$egreso["descripcion"]."</td>";
    $html.="    <td style='border: solid 1px black;padding: 5px;text-align:center;'>$".number_format($egreso["valor"], 0, ',', '.')."</td>";
    $html.="  </tr>";
}
$html.="  <tr style='border: solid 1px black;'>";
$html.="    <td style='width:20%; border: solid 1px black; padding: 10px;text-align:center;font-weight:bold;'>SUB. TOTAL</td>";
$html.="    <td style='width:20%; border: solid 1px black; padding: 10px;text-align:center;font-weight:bold;'>$".number_format($totalEgresos, 0, ',', '.')."</td>";
$html.="  </tr>";

$html.="</table>";

$html.="<br>";

$html.="<table style='width:100%;'  cellspacing='0' cellpadding='0'>";
$html.="  <tr style='border: solid 1px black;'>";
$html.="    <th style='width:33%; border: solid 1px black; padding: 10px;'>INGRESO</th>";
$html.="    <th style='width:33%; border: solid 1px black; padding: 10px;'>EGRESO</th>";
$html.="    <th style='width:33%; border: solid 1px black; padding: 10px;'>TOTAL EN CAJA</th>";
$html.="  </tr>";
$html.="  <tr style='border: solid 1px black;'>";
$html.="    <th style='width:33%; border: solid 1px black; padding: 5px;'>$".number_format($totalVentas, 0, ',', '.')."</th>";
$html.="    <th style='width:33%; border: solid 1px black; padding: 5px;'>$".number_format($totalEgresos, 0, ',', '.')."</th>";
$html.="    <th style='width:33%; border: solid 1px black; padding: 5px;'>$".number_format(($totalVentas - $totalEgresos), 0, ',', '.')."</th>";
$html.="  </tr>";
$html.="</table>";   
?>

<?php echo $html; ?>
    <p>Tamaño de hoja y orientación:
        <select name="paper">

        <?php
        foreach ( array_keys(CPDF_Adapter::$PAPER_SIZES) as $size )
          echo "<option ". ($size == "letter" ? "selected " : "" ) . "value=\"$size\">$size</option>\n";
        ?>
        </select>
        <select name="orientation">
          <option value="portrait">vertical</option>
          <option value="landscape">horizontal</option>
        </select>
    </p>
    
    <input type="hidden" name="html" value="<?=$html;?>" />
    <div style="text-align: center; margin-top: 1em;">
      <button type="submit" style="height: 30px;">Generar PDF</button><br><br><br>
    </div>

</form>

</body>
</html>