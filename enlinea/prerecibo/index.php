<?php
header ('Content-type: text/html; charset=utf-8');

include_once '../../includes/constants.php';
$propietario = new propietario();

if (!isset($_GET['id']) || $_GET['accion']=='autorizar') {
    $propietario->esPropietarioLogueado();
    $session = $_SESSION;
    
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : "listar";

$prerecibo = new prerecibo();
$bitacora = new bitacora();
$mensaje='';
$confirma='';
switch ($accion) {
    
    
    case "autorizar":

        $actualizar = $prerecibo->actualizar($_GET['id'], Array("aprobado" => "-1",
            "aprobado_por" => $session['usuario']['nombre'],
            "fecha_aprobado" => date("Y-m-d")));
        if ($actualizar['suceed']) {
            $admin = new administradora();
            $confirma = '<div class="alert alert-block alert-success"><h4 class="alert-heading">';
            $confirma.= '<i class="fa fa-check-square-o"></i> Prerecibo autorizado!</h4>';
            $documento = $prerecibo->ver($_GET['id']);
            if ($documento['suceed'] && count($documento['data']) > 0) {
                // enviamos un email de notificación a la administradora
                $ini = parse_ini_file('../../includes/emails.ini');
                
                $mail = new mailto(SMTP);
                
                $destinatario = $admin->obtenerEmailAdministradora($session['usuario']['cod_admin']);
                $subject = sprintf($ini['ASUNTO_MENSAJE_CONFIRMACION_AUTORIZACION_PRERECIBO'], 
                        $session['junta'],date('m-Y', strtotime($documento['data'][0]['periodo']))
                );
                $mensaje = sprintf($ini['CUERPO_MENSAJE_CONFIRMACION_AUTORIZACION_PRERECIBO'], 
                        $session['junta'],date('m-Y', strtotime($documento['data'][0]['periodo'])),$session['usuario']['nombre']);
            }
            $confirma.= $mensaje;
            
            $r = $mail->enviar_email($subject, $mensaje, "", $destinatario);
            
            if ($r == "") {
                $prerecibo->actualizar($_GET['id'], Array("notificacion" => '-1'));
                $confirma.= '<p>Se ha enviado un correo electrónico de confirmación a la administradora!</p>';
            }
            $confirma.= '</div>';
        } else {
            $confirma = '<div class="alert alert-block alert-danger"><h4 class="alert-heading"><i class="fa fa-times-circle"></i> Autorización fallida!</h4>
            <p>Ocurrió un error durante el proceso. Inténtelo nuevamente. Si el problema persiste comuníquese con el administrador</p></div>';
        }
        $actualizar['mensaje']=$confirma;
        $bitacora->insertar(Array(
            "id_sesion"     => $session['id'],
            "id_accion"     => 18,
            "descripcion"   => date('m-Y', strtotime($documento['data'][0]['periodo']))
        ));
        echo json_encode($actualizar);
        break;

    case "soportes":
        $propiedad = new propiedades();
        $inmuebles = new inmueble();
        $resultado = Array();
        $listado = Array();
        $propiedades = $propiedad->propiedadesPropietario(
                $_SESSION['usuario']['cedula'],
                $_SESSION['usuario']['cod_admin']);
        
        if ($propiedades['suceed']) {
            foreach ($propiedades['data'] as $propiedad) {
                $prerecibos = $prerecibo->listarPorInmueble(
                        $propiedad['id_inmueble'],
                        $_SESSION['usuario']['cod_admin'],
                        12);
                
                
                if ($prerecibos['suceed']) {
                    $inm = $inmuebles->verDatosInmueble(
                            $propiedad['id_inmueble'],
                            $_SESSION['usuario']['cod_admin']);
                    
                    if (!count($prerecibos['data']) > 0) {
                        $resultado['suceed'] = false;
                        $resultado['mensaje'] = "No se ha publicado ningún pre-recibo hasta ahora.";
                    } else {

                        for ($index = 0; $index < count($prerecibos['data']); $index++) {
                            $filename = $prerecibos['data'][$index]['documento'];
                            $prerecibos['data'][$index]['publicado'] = file_exists($filename);
                            $pos = strpos($filename,'_');  
                            $filename = str_replace(substr($filename, 0,$pos), "Soporte", $filename);
                            $prerecibos['data'][$index]['soporte'] = file_exists($filename);
                                                }
                        if (!$mensaje == "") {
                            $resultado['suceed'] = $actualizar['suceed'];
                            $resultado['mensaje'] = $mensaje;
                        }
                    }
                    $listado[] = [  "inmueble"  => $inm['data'][0], "prerecibos"=> $prerecibos ];
                } else {
                    $resultado['suceed'] = False;
                    $resultado['mensaje'] = "Ha ocurrido un error, no se puede recuperar la información.";
                }
            }
        } else {
            $resultado['suceed'] = False;
            $resultado['mensaje'] = "No se puede recuperar la información.";
        }
        $bitacora->insertar(Array(
            "id_sesion"     =>  $_SESSION['id'],
            "id_accion"     => 15
        ));
//        echo '<pre>';
//        echo print_r($listado);
//        echo '</pre>';
//        die();
        echo $twig->render('enlinea/prerecibo/soporte.html.twig', array(
            "session"       => $session,
            "resultado"     => $resultado,
            "prerecibos"    => $listado));
        break; 
    
    case "listar":
        $propiedad = new propiedades();
        $inmuebles = new inmueble();
        $listado = Array();
        $resultado = Array();
        
        $propiedades = $propiedad->propiedadesPropietario(
                        $_SESSION['usuario']['cedula'],
                        $_SESSION['usuario']['cod_admin']);
        if ($propiedades['suceed'] == true) {
            
            foreach ($propiedades['data'] as $p) {
                // mostramos los últimos 5 periodos facturados, lo pagamos como parámetro a esta funcion
                $prerecibos = $prerecibo->listarPorInmueble(
                                $p['id_inmueble'],
                                $_SESSION['usuario']['cod_admin'],
                                12
                            );
                
                if ($prerecibos['suceed']) {
                    $inm = $inmuebles->verDatosInmueble(
                            $p['id_inmueble'],
                            $_SESSION['usuario']['cod_admin']);
                    
                    if (!count($prerecibos['data']) > 0) {
                        $resultado['suceed'] = false;
                        $resultado['mensaje'] = "No se ha publicado ningún pre-recibo hasta ahora.";
                    } else {
                        for ($index = 0; $index < count($prerecibos['data']); $index++) {
                            $filename = $prerecibos['data'][$index]['documento'];
                            $prerecibos['data'][$index]['publicado'] = file_exists($filename);
                            $pos = strpos($filename,'_');  
                            $filename = str_replace(substr($filename, 0,$pos), "Soporte", $filename);
                            $prerecibos['data'][$index]['soporte'] = file_exists($filename)? $filename: "";


                        }
                        if (!$mensaje=="") {
                            $resultado['suceed'] = $actualizar['suceed'];
                            $resultado['mensaje'] = $mensaje;
                        }
                    }
                    $listado[] = Array(
                        "inmueble"  => $inm['data'][0],
                        "prerecibos"=> $prerecibos);
                } else {
                    $resultado['suceed'] = False;
                    $resultado['mensaje'] = "Ha ocurrido un error, no se puede recuperar la información.";
                }
            }
        }
        $bitacora->insertar(Array(
            "id_sesion"     =>  $_SESSION['id'],
            "id_accion"     => 16
        ));
        
        echo $twig->render('enlinea/prerecibo/formulario.html.twig', array(
            "session"       => $session,
            "resultado"     => $resultado,
            "prerecibos"    => $listado));
        break; 
    
    case "publicar":
        $prerecibos = new prerecibo();
        $data = Array(
            'id_inmueble'   => $_GET['id_inmueble'],
            'documento'     => $_GET['documento'],
            'periodo'       => Misc::format_mysql_date($_GET['periodo']));
        if (isset($_GET['cod_admin']) && !$_GET['cod_admin']=='') {
            $data['cod_admin'] = $_GET['cod_admin'];
        }
        //if ($prerecibos->prereciboYaRegistrado($_GET['id_inmueble'], $_GET['periodo'])) {
        
        //} else {
        $resultado = $prerecibos->insertar($data);
        //}
        if ($resultado['suceed']) {
            // enviamos un email de notificación a los miembros de la junta
            echo "Prerecibo registrado con éxito.";
        } else {
            if ($resultado['stats']['errno'] == 1062) {
                echo $resultado['stats']['errno'];
            } else {
                echo $resultado['stats']['error'];
            }
        }
        break; 

    case "ver":
        
        $titulo = $_GET['id'];
        $content = 'Content-type: application/pdf';
        $url = URL_SISTEMA . "/prerecibo/" . $_GET['id'];
        
        header('Content-Disposition: inline; filename="' . $titulo . '"');
        header($content);
        readfile($url);

        $bitacora->insertar(Array(
            "id_sesion"     => $_SESSION['id'],
            "id_accion"     => 17,
            "descripcion"   => $titulo
        ));
        break; 
        
    default:
        break;
}