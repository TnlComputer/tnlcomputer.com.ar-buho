<?php
require_once "connections/conexion.php";
error_reporting(E_ALL ^ E_NOTICE);

$resultado = json_decode(file_get_contents("php://input"), true);
$codProds = explode(",", $resultado);
$cont_res=count($codProds);

for($i=0;$i<$cont_res;$i++){ 
  $consP=$conexionApi->query("SELECT * FROM productos WHERE id='".$codProds[$i]."' ");
    $filaP=mysqli_fetch_array($consP); 
    $id_prod=$filaP["id"];
    $codigo=$filaP["codigo"];
    $familia=$filaP["familia"];
    $detalle=utf8_decode($filaP["detalle"]);
    // $descripcion=utf8_decode($filaR["descripcion"]);
    $stock=$filaP["stock"];
    // $preciog=$filaR["precioG"];
         $preciogp=$filaP["precio"];

    //  $preciogp=number_format($filaP["precio"],2,".","");
    //  $preciogp=number_format($filaP["precio"],2);
    // $preciogp=$filaR["columna1"];
    $imagenP=$filaP["codigo"].".webp";

    $prodApi[$i] = array("id_prod"=>"$id_prod", "codigo"=>"$codigo", "familia"=>"$familia", "detalle"=>"$detalle", "stock"=>"$stock", "preciogp"=>"$preciogp", "imagen"=>"$imagenP");
} 

if ($cont_res===0){ 
  echo json_encode("error");
}else{
  echo json_encode($prodApi);
}
  
?>