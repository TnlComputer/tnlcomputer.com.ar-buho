<?php

if (isset($_POST)){
  $name = $_POST["name"];
  $email = $_POST["email"];
  $subject = $_POST["subject"];
  $comments = $_POST["comments"];
  
  $domain = $_SERVER["HTTP_HOST"];
  $to = "jorgemartinez@tnlcomputer.com.ar";
  $subject_mail="Contacto desde el formulario del sitio $domain.";

  $message="
  <br>
  <p>
  Datos enviados desde el formulario del sitio <b>$domain</b><br>
  </p>
  <ul><br>
  <il>Nombre: <b>$name</b></il><br>
  <il>Email: <b>$email</b></il><br>
    <il>Subject: $subject</il><br>
  <il>Comentarios: $comments</il><br>
  </ul><br>
  ";

//   $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
// $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

  $headers = "MIME-Version: 1.0\r\n"."Content-Type:text/html; charset=utf-8\r\n"."From: Envio Automatico No Responder <no-replay@tnlcomputer.com.ar>";

  $send_mail = mail($to, $subject_mail, $message, $headers);

  if(send_mail){
    $res = [
      "err" => false,
      "message" => "Tus datos han sido enviados"
    ];
  }else{
    $res = [
      "err" => true,
      "message" => "Error an enviar tus datos. Intenta nuevamente"
    ];
  }

  header("Access-Control-Allow-Origin: https://tnlcomputer.com.ar");
  header(`Content-type: application/json`);
  echo json_encode($res);
  exit;
}