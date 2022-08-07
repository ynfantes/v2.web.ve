<?php
include_once '../includes/constants.php';
include_once '../includes/file.php';

propietario::esPropietarioLogueado();
$session = $_SESSION;
if (file_exists('../'.ACTUALIZ.$session['usuario']['cod_admin'].'_'.ARCHIVO_ACTUALIZACION)) {
    $archivo = '../'.ACTUALIZ.$session['usuario']['cod_admin'].'_'.ARCHIVO_ACTUALIZACION;
    $fecha_actualizacion = JFile::read($archivo);
} else {
    $fecha_actualizacion = '';
}

// <editor-fold defaultstate="collapsed" desc="cantidad recibos pendientes">
$f = new factura();
$p = new pago();

$cuenta = $f->numeroRecibosPendientesPropietario(
        $session['usuario']['cod_admin'],
        $session['usuario']['cedula']);
$recibos = 0;
$cancela = 0;

if ($cuenta['suceed']) {

    if (count($cuenta['data']) > 0) 
        $recibos = $cuenta['data'][0]['cantidad'];
}
$pagos = $p->numeroRecibosCanceladosPorPropitario($session['usuario']['cod_admin'],$session['usuario']['cedula']);

if ($pagos['suceed']) {
    if (count($pagos['data']) > 0) {
        $cancela = count($pagos['data']);
    }
}
// </editor-fold>

$mensajes = new mensajes();
$m = $mensajes->cantidadMensajesSinLeerPorPropietario(
        $session['usuario']['cod_admin'],
        $session['usuario']['cedula']);

$sesiones = propietario::obtenerInfoUltimasSesiones(
        $session['usuario']['cod_admin'],
        $session['usuario']['cedula'],
        $session['id_sesion']);

$historico = null;

if ($sesiones['suceed']) {
    $historico = $sesiones['data'];
}

echo $twig->render('enlinea/index.html.twig', array(
    "session"               => $session,
    "fecha_actualizacion"   => $fecha_actualizacion,
    "recibos"               => $recibos,
    "cancelacion"           => $cancela,
    "historico"             => $historico,
    "mensajes"              => $m
    ));