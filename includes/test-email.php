<?php
include 'constants.php';
include 'mailto.php';
// se envia el email de confirmación
$ini = parse_ini_file('emails.ini');
$mail = new mailto(SMTP);

$propietario = "edgar";
$forma_pago = 'DEPOSITO';

$mensaje = 'Este es una mensaje de prueba';

$r = $mail->enviar_email("Pago de Condominio", $mensaje, "", "ynfantes@gmail.com");
if ($r=="") {
    echo "<br>mensaje enviado con exito";
} else {
    echo "<br>fallo durante el envio";
}