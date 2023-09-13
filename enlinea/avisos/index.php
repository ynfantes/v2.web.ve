<?php
include_once '../../includes/constants.php';
include_once '../../includes/propietario.php';

if ($_SERVER['REQUEST_METHOD']==='POST') {

    $name = fopen($_POST['name'].'pdf','w');
    $base64 = base64_decode($_POST['base64']);

    $succed = fwrite($name,$base64);
    
    return json_encode($succed);

} else {
    propietario::esPropietarioLogueado();

    $factura = new factura();

    $r = $factura->facturaPerteneceACliente(
            $_GET['id'], 
            $_SESSION['usuario']['cedula'],
            $_SESSION['usuario']['cod_admin']);

    if ($r == true ) {
        $titulo = "AC".$_GET['id'].".pdf";
        $content="Content-type: application/pdf";
        $url = URL_SISTEMA."/avisos/".$_GET['id'].$_SESSION['usuario']['cod_admin'].".pdf";
        header('Content-Disposition: inline; filename="'.$titulo.'"');
        header($content);
        readfile($url);
        $bitacora = new bitacora();
        $bitacora->insertar(Array(
            "id_sesion"     => $_SESSION['id_sesion'],
            "id_accion"     => 2,
            "descripcion"   => $_GET['id'],
        ));
        
    //    $url = URL_SISTEMA."/avisos/".$_GET['id'].".pdf";
    //    header("location:$url");
        
    } else {
        echo "El aviso de cobro no se puede mostrar en estos momentos.";
    }
}