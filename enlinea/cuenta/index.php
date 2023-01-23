<?php
include_once '../../includes/constants.php';


propietario::esPropietarioLogueado();
        
$accion = isset($_GET['accion']) ? $_GET['accion'] : "listar";
$session = $_SESSION;
$cod_admin = $session['usuario']['cod_admin'];

$inmuebles = new inmueble();
$propiedad = new propiedades();
$bitacora = new bitacora();

$factura = null;
$cobro = null;
$monto = 0;
$monto_c = 0;
$total = 0;
$total_c = 0;
$promedio_facturacion = 0;
$promedio_cobranza = 0;
$direccion_cobranza = "down";
$i = 0;
$n = 0;
$direccion_facturacion = "right";
$propiedades = $propiedad->inmueblePorPropietario(
        $_SESSION['usuario']['cedula'],
        $_SESSION['usuario']['cod_admin']);


if ($propiedades['suceed'] && count($propiedades['data']) > 0) {
    
    $facturacion = $inmuebles->movimientoFacturacionMensual(
            $propiedades['data'][0]['id_inmueble'],
            $_SESSION['usuario']['cod_admin']);
    if ($facturacion['suceed']) {

        foreach ($facturacion['data'] as $r) {
            $i++;
            $direccion_facturacion = $r['facturado'] > $monto ? "up" : "down";

            $monto = $r['facturado'];
            $total += $monto;
            $factura .= $factura != '' ? ',' : '';
            $factura .= (int) $r['facturado'];
                    }
                    if ($i>0) {
                        $promedio_facturacion = (int) ($total / $i);
                    }
    }

    $cobranza = $inmuebles->movimientoCobranzaMensual(
            $propiedades['data'][0]['id_inmueble'],
            $_SESSION['usuario']['cod_admin']);
    if ($cobranza['suceed']) {
        
        foreach ($cobranza['data'] as $c) {
            $n++;
            $direccion_cobranza = $c['monto'] > $monto_c ? "up" : "down";
            $monto_c = $c['monto'];
            $total_c += $monto_c;
            $cobro .= $cobro != '' ? ',' : '';
            $cobro .= (int)$c['monto'];
        }
        if ($n>0) {$promedio_cobranza = (int)($total_c / $n);}
        
    }
    
}

switch ($accion) {
    
    case "listar":
    default :
        
        $facturas = new factura();

        $propiedades = $propiedad->propiedadesPropietario(
                $session['usuario']['cedula'],
                $session['usuario']['cod_admin']);
        //var_dump($propiedades);
        $cuenta = Array();

        if ($propiedades['suceed'] == true) {


            foreach ($propiedades['data'] as $propiedad) {

                $bitacora->insertar(Array(
                    "id_sesion"=>$session['id_sesion'],
                    "id_accion"=>1,
                    "descripcion"=>$propiedad['id_inmueble']." - ".$propiedad['apto'],
                ));
                
                $inmueble = $inmuebles->verDatosInmueble(
                        $propiedad['id_inmueble'],
                        $session['usuario']['cod_admin']);
                
                $f = $facturas->estadoDeCuenta(
                        $session['usuario']['cod_admin'],
                        $propiedad['id_inmueble'], 
                        $propiedad['apto']);
                
                if ($f['suceed'] == true) {

                        for ($index = 0; $index < count($f['data']); $index++) {
                            $filename = "../avisos/".$f['data'][$index]['numero_factura'].
                                    $session['usuario']['cod_admin'].".pdf";
                            $f['data'][$index]['aviso'] = file_exists($filename);
                        }

                    $cuenta[] = Array(
                        "inmueble"      => $inmueble['data'][0],
                        "propiedades"   => $propiedad,
                        "cuentas"       => $f['data']);
                }
            }
        }
        
        echo $twig->render('enlinea/cuenta/formulario.html.twig', array(
            "session"                => $session,
            "cuentas"                => $cuenta,
            "movimiento_facturacion" => $factura,
            "promedio_facturacion"   => $promedio_facturacion,
            "direccion_facturacion"  => $direccion_facturacion,
            "promedio_cobranza"      => $promedio_cobranza,
            "direccion_cobranza"     => $direccion_cobranza
        ));

        break; 
    
    case "avisos":
        $propiedad = new propiedades();
        $inmuebles = new inmueble();
        $avisos = new factura();
        $historico = array();
        $propiedades = $propiedad->propiedadesPropietario(
                $session['usuario']['cedula'],
                $cod_admin);
        if ($propiedades['suceed'] == true) {
            
            foreach ($propiedades['data'] as $propiedad) {
                
                $inmueble = $inmuebles->verDatosInmueble(
                        $propiedad['id_inmueble'],
                        $cod_admin);
                
                $inmueble = $inmueble['data'][0];
                $ano_actual = date('Y');    
                $ano_anterior = date('Y', strtotime('-1 year'));
                
                $r = $avisos->obtenerAñosHistorico(
                        $inmueble['id'],
                        $propiedad['apto'],
                        $cod_admin);
                
                if ($r['suceed'] && count($r['data'])>1) {
                    $ano_actual = $r['data'][0]['ano'];
                    if (count($r['data'])>1) {
                        $ano_anterior = $r['data'][1]['ano'];
                    }
                }
                //$inmueble['id']
                $recibos_ant = Array();
                $recibos_act = Array();
                $result = $avisos->historicoAvisosDeCobro(
                        $inmueble['id'], 
                        $propiedad['apto'],
                        $cod_admin,
                        $ano_anterior);
                if ($result['suceed'] && count($result['data']) > 0) {
                    for ($index = 0; $index < count($result['data']); $index++) {
                        $filename = "../avisos/".$result['data'][$index]['numero_factura'].$cod_admin.".pdf";
                        $result['data'][$index]['aviso'] = file_exists($filename);
                    }
                    $recibos_ant = $result['data'];
                }
                
                if ($ano_actual != $ano_anterior) {
                    
                    $result = $avisos->historicoAvisosDeCobro(
                            $inmueble['id'], 
                            $propiedad['apto'], 
                            $cod_admin,
                            $ano_actual);
                    
                    if ($result['suceed'] && count($result['data']) > 0) {
                        for ($index = 0; $index < count($result['data']); $index++) {
                            $filename = "../avisos/".$result['data'][$index]['numero_factura'].$cod_admin.".pdf";
                            $result['data'][$index]['aviso'] = file_exists($filename);
                        }
                        $recibos_act = $result['data'];
                    }
                }
                
                $historico[] = Array(
                    "inmueble"      => $inmueble,
                    "propiedades"   => $propiedad,
                    "ano_anterior"  => $ano_anterior,
                    "ano_actual"    => $ano_actual,
                    "recibos_ant"   => $recibos_ant,
                    "recibos_act"   => $recibos_act
                );
            }
        }
        
//        $archivos = glob("../avisos/*".substr($ano_anterior,-2).substr($inmueble['id'],-3).sprintf('%03d', $session['usuario']['id']).".pdf");
//        foreach($archivos as $archivo) {
//            $recibos_ant[] = basename($archivo);
//        }
//        $archivos = glob("../avisos/*".substr($ano_actual,-2).substr($inmueble['id'],-3).sprintf('%03d', $session['usuario']['id']).".pdf");
//        foreach($archivos as $archivo) {
//            $recibos_act[] = basename($archivo);
//        }
        echo $twig->render('enlinea/cuenta/avisos-de-cobro.html.twig', Array(
            "historico" => $historico,
            "movimiento_facturacion" => $factura,
            "promedio_facturacion"   => $promedio_facturacion,
            "direccion_facturacion"  => $direccion_facturacion,
            "promedio_cobranza"      => $promedio_cobranza,
            "direccion_cobranza"     => $direccion_cobranza
        ));
        break; 
    // </editor-fold>
        
}
