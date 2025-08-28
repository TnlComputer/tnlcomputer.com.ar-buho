<?php
require_once "connections/conexion.php";
error_reporting(E_ALL ^ E_NOTICE);

$cons=$conexionApi->query("SELECT * FROM rubros ORDER BY rubro ASC");
$cant_reg=mysqli_num_rows($cons);

	for($i=0;$i<$cant_reg;$i++){ 
		$fila=mysqli_fetch_array($cons); 
    $id_rubro=$fila["id_rubro"];
    $rubro=$fila["rubro"];
    // $rubro_nombre=$fila["rubro_nombre"];
    // $foto_rubro=$fila["foto_rub"];

 $rubrosApi[$i] =array("id_rubro"=>"$id_rubro", "rubro"=>"$rubro");
 
}
// $apiRubros=json_encode($rubrosApi);
echo json_encode($rubrosApi);

  // echo "<br>".$apiRubros."<br>";
?>