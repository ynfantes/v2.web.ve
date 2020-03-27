<?php
require_once 'includes/constants.php';
$rif = array();
$accion = isset($_GET['accion']) ? $_GET['accion'] : "index";
if ($accion=='test') {
    $cliente = new cliente();
    $cliente->enviarEmailPreRegistro(23);
//    $r = $cliente->recuperarContraSena(3,'40666184-8','ynfantes@gmail.com');
//    var_dump($r);
//    die();
}
echo $twig->render('index.html.twig');