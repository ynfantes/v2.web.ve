<?php
include_once '../../includes/constants.php';
$propietario = new propietario();

$propietario->esPropietarioLogueado();

$accion = isset($_GET['accion']) ? $_GET['accion'] : "listar";

$session = $_SESSION;

$bitacora = new bitacora();

switch ($accion) {
    case  "listar":
        $resultado = null;
        if (isset($_GET['pagina'])) {
            $page = $_GET['pagina'];
        } else {
            $page = 1;
        }
        $start = ($page - 1) * 10;
        
        $historico = $bitacora->obtenerBitacoraPorPropietario($session['usuario']['cedula'], $start, 10);
        
        $limit = 0;
        $total = $bitacora->totalRegistrosBitacora($session['usuario']['cedula']);
        
        if ($historico['suceed']) {
            $resultado = $historico['data'];
            $limit = 10 * $page;
            $start++;
        }
        //echo $limit.'<br>'.$total;
        if ($page==1) {
            
            $bitacora->insertar(Array(
                "id_sesion"=>$session['id_sesion'],
                "id_accion"=> 13,
                "descripcion"=>'',
            ));
        }
        echo $twig->render('enlinea/bitacora/listar.html.twig', array(
            "session"  => $session,
            "historico"=> $resultado,
            "pagina"   => $page,
            "start"    => $start,
            "limit"    => $limit,
            "total"   => $total
        ));
        break;
}