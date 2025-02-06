<?php
include_once '../../includes/configuracion.php';
include_once '../../includes/propietario.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {

    $name = fopen($_POST['name'].'.pdf','w');
    $base64 = base64_decode($_POST['base64']);

    $succed = fwrite($name,$base64);
    
    return json_encode($succed);

} else {

    propietario::esPropietarioLogueado();
    
    //$factura = new factura();
    //$r = $factura->facturaPerteneceACliente($_GET['id'], $_SESSION['usuario']['cedula']);
    
    //if ($r==true) {
        $titulo = $_GET['id'].".pdf";
        $content='Content-type: application/pdf';
        $url = ROOT."/cancelacion.gastos/".$_GET['id'].".pdf";
        die($url);
        header('Content-Disposition: attachment; filename="'.$titulo.'"');
        header($content);
        readfile($url);
        
    //} else {
    //    echo "El recibo de condominio no se puede mostrar en estos momentos.";
    //}
}