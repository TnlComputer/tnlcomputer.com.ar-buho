<?php
require_once "connections/conexion.php";
error_reporting(E_ALL ^ E_NOTICE);

$rubro1=$_GET["rubro"];

//  $consR=$conexionApi->query("SELECT * FROM rubros WHERE rubro='".$rubro1."' ");
//     $filaR=mysqli_fetch_array($consR); 
//     $imagenR=$filaR["imagen"];


 $consP=$conexionApi->query("SELECT * FROM productos WHERE familia LIKE '%".$rubro1."%' AND stock<>'SIN STOCK' ORDER BY Familia ASC");
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
     $preciogp=$filaP["precio"];
        // $preciogp=number_format($filaP["precio"],2,",",".");
    // $preciogp=$filaR["columna1"];
    $imagenP=$filaP["codigo"].".webp";
    $prodApi[$i] = array("id_prod"=>"$id_prod", "codigo"=>"$codigo", "familia"=>"$familia", "detalle"=>"$detalle", "stock"=>"$stock", "preciogp"=>"$preciogp", "imagen"=>"$imagenP");

  } 

  if ($cant_regP===0){ 
        $prodApi[0] = array("id_prod"=>"$id_prod", "codigo"=>"$codigo", "familia"=>"No hay productos para mostrar", "detalle"=>"$detalle", "stock"=>"$stock", "preciogp"=>"$preciogp");
  } 
    echo json_encode($prodApi);
  
  
  // header("Access-Control-Allow-Origin:*");
  // header('Content-type: application/text');

?>