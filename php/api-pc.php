<?php
require_once "connections/conexion.php";
error_reporting(E_ALL ^ E_NOTICE);

$rubro1="PC ARMADA";

//  $consR=$conexionApi->query("SELECT * FROM rubros WHERE rubro='".$rubro1."' ");
//     $filaR=mysqli_fetch_array($consR); 
//     $imagenR=$filaR["imagen"];


 $consP=$conexionApi->query("SELECT * FROM productos WHERE familia='".$rubro1."' AND stock<>'SIN STOCK' ORDER BY Familia ASC");
$cant_regP=mysqli_num_rows($consP);

  for($i=0;$i<$cant_regP;$i++){ 
    $filaP=mysqli_fetch_array($consP); 
    $id_prod=$filaP["id"];
    $codigo=$filaP["codigo"];
    $familia=$filaP["familia"];
    $detalle=utf8_decode($filaP["detalle"]);
    // $descripcion=utf8_decode($filaR["descripcion"]);
    $stock=$filaP["stock"];
    // $preciog=$filaR["precioG"];
    $preciogp=number_format($filaP["precio"]);
    // $preciogp=$filaR["columna1"];
    $imagenP=$filaP["codigo"].".webp";
    $pcApi[$i] = array("id_prod"=>"$id_prod", "codigo"=>"$codigo", "familia"=>"$familia", "detalle"=>"$detalle", "stock"=>"$stock", "preciogp"=>"$preciogp", "imagen"=>"$imagenP");

  } 

  if ($cant_regP===0){ 
        $pcApi[0] = array("id_prod"=>"$id_prod", "codigo"=>"$codigo", "familia"=>"No hay productos para mostrar", "detalle"=>"$detalle", "stock"=>"$stock", "preciogp"=>"$preciogp");
  } 
    echo json_encode($pcApi);
  
  
  // header("Access-Control-Allow-Origin:*");
  // header('Content-type: application/text');

?>