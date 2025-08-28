<?php
require_once "connections/conexion.php";
error_reporting(E_ALL ^ E_NOTICE);


// $rubro3=$_POST["producto-secundary"];
// $rubro4=$_POST["id"];
// $rubro5=$_POST["x"];
// $rubro6=$_GET["datosp"];


// $rubro2=$_POST["id_rubro"];
// $rubro2=1;

// if ($rubro!=NULL) {
//   $rubroCD=$rubro1;
// } else {
//   $rubroCD=$rubro2;
// }


// if (isset($rubro1)){$rubroCD=$rubro1;}
// if (isset($rubro2)){$rubroCD=$rubro2;}

// if ((!isset($rubro1)) and  (!isset($rubro2))){$rubroCD=1; }

// echo "Rub1  - ".$rubro1."<br>";

// echo "Rub1  - ".$rubro1."<br> Rub2 - ".$rubro2."<br> Rub3 - ".$rubro3."<br> Rub4 - ".$rubro4."<br> Rub5 - ".$rubro5."<br> Rub6 - ".$rubro6;

 $consO=$conexionApi->query("SELECT * FROM ofertas WHERE oferta='SI' ");
$cant_regO=mysqli_num_rows($consO);
//  echo "<br>Reg-g  - ".$cant_regRg;

// $consR=$conexionApi->query("SELECT * FROM productos WHERE id_rubro='".$rubro2."' ORDER BY Familia ASC");
// $consR=$conexionApi->query("SELECT * FROM productos ORDER BY Familia ASC");
// $cant_regR=mysqli_num_rows($consR);

// echo "<br>Reg -> ".$cant_regR;

// if ($cant_regR>0){

  for($i=0;$i<$cant_regO;$i++){ 
    $filaO=mysqli_fetch_array($consO); 
    // $id_prod=$filaO["id"];
        $codigo=$filaO["codigo"];
    // $familia=$filaO["familia"];
    // $detalle=utf8_decode($filaO["detalle"]);
    // $descripcion=utf8_decode($filaO["descripcion"]);
    // $stock=$filaO["stock"];
    // $preciogp=$filaO["precio"];
    // $foto=$filaO["foto"];

     $consP=$conexionApi->query("SELECT * FROM productos WHERE codigo='".$codigo."' AND stock<>'SIN STOCK' ORDER BY Familia ASC");
      // $cant_regP=mysqli_num_rows($consP);

      // for($i=0;$i<$cant_regP;$i++){ 
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
        $oferApi[$i] = array("id_prod"=>"$id_prod", "codigo"=>"$codigo", "familia"=>"$familia", "detalle"=>"$detalle", "stock"=>"$stock", "preciogp"=>"$preciogp", "imagen"=>"$imagenP");

    // } 
    
  //   $oferApi[$i] = array("id_prod"=>"$id_prod", "codigo"=>"$codigo", "familia"=>"$familia", "detalle"=>"$detalle", "stock"=>"$stock", "preciogp"=>"$preciogp", "foto"=>"$foto");
  } 

  if ($cant_regO===0){ 
      $imagenP=$filaP["codigo"].".webp";
      $oferApi[$i] = array("id_prod"=>"$id_prod", "codigo"=>"$codigo", "familia"=>"$familia", "detalle"=>"$detalle", "stock"=>"$stock", "preciogp"=>"$preciogp", "imagen"=>"$imagenP");
  } 
    echo json_encode($oferApi);
  
  
  // header("Access-Control-Allow-Origin:*");
  // header('Content-type: application/text');

?>