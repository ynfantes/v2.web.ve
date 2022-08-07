<?php
include_once 'includes/constants.php';
$mantenimiento = FALSE;
$avance        = 0;
$propietarios  = new propietario();
$propiedades   = new propiedades();
$facturas      = new factura();

$result = $propietarios->listar();

if (!$result['suceed'] || empty($result['data'])) {
    $mantenimiento = TRUE;
} else {
    
    $result = $propiedades->listar();

    if (!$result['suceed'] || empty($result['data'])) {
        $mantenimiento = TRUE;
        
    } else {
        $result = $facturas->listar();
        if (!$result['suceed'] || empty($result['data'])) {
            $mantenimiento = TRUE;
        }
    }    
}

if ($mantenimiento) {
    $mail = new mailto(SMTP);
    $min = date("i");
    $avance = $min * 100 / 60;
    //enviamos una comunicacion al administrador del sistema
    $mail->enviar_email(TITULO.' [Mantenimiento]','Sincronice la página web','','ticket.soporte21@gmail.com','Valoriza2');
}
echo $twig->render( 'login.html.twig', [ "mantenimiento" => $mantenimiento,"avance" => $avance ] );
//echo $twig->render('login.html.twig');