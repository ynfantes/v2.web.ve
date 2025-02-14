<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

header ('Content-type: text/html; charset=utf-8');

include_once '../includes/constants.php';
include_once '../includes/file.php';

usuario::esUsuarioLogueado();

$session = $_SESSION;

$id_inmueble = isset($_GET['inmueble']) ? $_GET['inmueble']:"";
$total = 0;
$total_usd = 0;
$recibos=0;

$propiedad = new propiedades();

$propiedades = $propiedad->propiedadesPropietario(
        $session['usuario']['cedula'], 
        $session['usuario']['cod_admin']);

$aut=false;
if ($propiedades['suceed']) {
     foreach ($propiedades['data'] as $p) {
         
         if ($id_inmueble == $p['id_inmueble']) {
             $aut = true;
             break;
         }   
     }
}
if (!$aut) {
    die("Está tratando de ver una información que no está asociada a su cuenta de condominio.");
}

if ($id_inmueble!= "") {
    $archivo = '../'.ACTUALIZ.$session['usuario']['cod_admin'].'_'.ARCHIVO_ACTUALIZACION;
    $fecha_actualizacion = JFile::read($archivo);
    $inmuebles = new inmueble();
    $cuentas = Array();
//    $bitacora->insertar(Array(
//        "id_sesion"=>$session['id_sesion'],
//        "id_accion"=>6,
//        "descripcion"=>$propiedad['id_inmueble']." - ".$propiedad['apto'],
//    ));
    $inm = $inmuebles->verDatosInmueble(
            $id_inmueble,
            $session['usuario']['cod_admin']);
    $cuenta = $inmuebles->estadoDeCuenta($session['usuario']['cod_admin'],$id_inmueble);
} else {
    die("No se puede generar la información solicitada.");
}
?>
<page backtop="10mm" backbottom="10mm" backleft="10mm" backright="10mm" style="font-size: 14px">
    <span>TEST</span>
</page>