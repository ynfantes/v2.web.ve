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
<page  style="font-size: 10pt;">
    <img src="../assets/images/_smarty/logo_app.png" alt="Logo" width=150 /><br><br>
<div data-widget-editbutton="false" id="wid-id-3" class="jarviswidget  jarviswidget-sortable jarviswidget-color-white" role="widget" data-widget-attstyle="jarviswidget-color-white">
<div role="heading">
    <span style="font-size: 20px; font-weight: bold">Estado de Cuenta Inmueble</span><br>
    <span style="font-size: 14px; font-weight: bold"><?php echo $inm['data'][0]['nombre_inmueble'] ?></span><br>
    <span style="font-size: 10px;color:#333">Información actualizada al: <?php echo $fecha_actualizacion ?></span><br>
    <br>
    
</div>
<div role="content" style="display: block;">

<div class="widget-body no-padding">
    <table  style="width: 60%;border: solid 1px #5544DD; border-collapse: collapse" align="center">
        <thead>
            <tr>
                <th style="width: 30%; text-align: center; border: solid 1px #337AB7; background: #337AB7;padding: 2mm; color: #fff">
                    <?php echo $inm['data'][0]['unidad'] ?>
                </th>
                <th style="width: 30%; text-align: center; border: solid 1px #337AB7; background: #337AB7;padding: 2mm; color: #fff">RECIBOS PENDIENTES</th>
                <th style="width: 30%; text-align: center; border: solid 1px #337AB7; background: #337AB7;padding: 2mm; color: #fff">
                    DEUDA<br><?php echo 'EN '.$inm['data'][0]['moneda'] ?>
                </th>
                <?php if ($inm['data'][0]['facturacion_usd']) {?>
                <th style="width: 30%; text-align: center; border: solid 1px #337AB7; background: #337AB7;padding: 2mm; color: #fff">DEUDA<br>EN $</th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cuenta['data'] as $r) {
                $total += $r['deuda'];
                $total_usd += $r['deuda_usd'];
                $recibos += $r['recibos'];
                ?>
            <tr>
                <td style="text-align: center;border: solid 1px #cfcfcf;"><?php echo $r['apto']; ?></td>
                <td style="text-align: center;border: solid 1px #cfcfcf;"><?php echo $r['recibos']; ?></td>
                <td style="text-align: right;border: solid 1px #cfcfcf;"><?php echo number_format($r['deuda'], 2, ",","."); ?>&nbsp;&nbsp;</td>
                <?php if ($inm['data'][0]['facturacion_usd']) {?>
                <td style="text-align: right;border: solid 1px #cfcfcf;"><?php echo number_format($r['deuda_usd'], 2, ",","."); ?>&nbsp;&nbsp;</td>
                <?php } ?>
            </tr>
            <?php } ?>
            <tr>
                <td style="text-align: right;border: solid 1px #cfcfcf;"><strong>Total</strong>&nbsp;&nbsp;</td>
                <td style="text-align: center;border: solid 1px #cfcfcf;"><strong><?php echo $recibos; ?></strong>&nbsp;&nbsp;</td>
                <td style="text-align: right;border: solid 1px #cfcfcf;"><strong><?php echo number_format($total, 2, ",","."); ?></strong>&nbsp;&nbsp;</td>
                <?php if ($inm['data'][0]['facturacion_usd']) {?>
                <td style="text-align: right;border: solid 1px #cfcfcf;"><strong><?php echo number_format($total_usd, 2, ",","."); ?></strong>&nbsp;&nbsp;</td>
                <?php } ?>
            </tr>
        </tbody>
</table>
</div>  
</div>
</div>
</page>

