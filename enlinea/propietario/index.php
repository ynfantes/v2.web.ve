<?php
include_once '../../includes/constants.php';
if (isset($_GET['logout'])) {
    die();
}
$propietario = new propietario();
$bitacora = new bitacora();

$accion = isset($_GET['accion']) ? $_GET['accion'] : "ver";
$id = isset($_GET['id']) ? $_GET['id'] : "perfil";
if ($accion=='recuperar-password') {
   $id = base64_decode($id); 
}
if ($id!= 'sac' && !is_numeric($id) && $accion!='login' && $accion!='"actualizados"') {
    $propietario->esPropietarioLogueado();
    $session = $_SESSION;
}

switch ($accion) {
    
    case "ver":
    case "actualizar":

        /* @var $datos_personales callable */
        $datos_personales = $propietario->ver($session['usuario']['id']);
        
        $bitacora->insertar(Array(
                'id_sesion'     => $session['id_sesion'],
                'id_accion'     => 3,
                'descripcion'   => $session['usuario']['nombre'],
            )
        );
        
        echo $twig->render('enlinea/propietario/formulario.html.twig', array(
            'session'       => $session,
            'propietario'   => $datos_personales['data'][0],
            'accion'        => $accion,
            'id'            => $id)
        );
        break; 

    case "modificar":
        $data = $_POST;
        unset($data['actualizar']);
        
        if ($_GET['id'] == 'perfil') {
            $exito = $propietario->actualizar($session['usuario']['id'], $data);
            $mensaje = "Datos actualizados con éxito!";
            $bitacora->insertar(Array(
                "id_sesion"     => $session['id_sesion'],
                "id_accion"     => 14,
                "descripcion"   => '',
            ));
        } else {
            $exito = $propietario->ver($session['usuario']['id']);
            
            if ($exito['suceed'] && count($exito['data']) > 0) {
                
                if ($exito['data'][0]['clave'] == $data['clave_actual']) {
                    
                    unset($data['clave_actual']);
                    $exito = $propietario->actualizar($session['usuario']['id'], $data);
                    $mensaje = "Cambio de clave efectuado con éxito!.";
                    
                    $bitacora->insertar(
                        Array(
                            'id_sesion'     => $session['id_sesion'],
                            'id_accion'     => 7,
                            'descripcion'   => $mensaje,
                        )
                    );

                } else {
                    
                    $mensaje = "La clave actual que ha ingresado es incorrecta.";
                    $exito['suceed'] = false;
                }
            
            } else {
                $mensaje = "El cambio de clave no se pudo procesar.";
            }
        
        }
        if ($exito['suceed']) {
            
            $exito['mensaje'] = $mensaje;

        } else {

            if ($mensaje == "") {

                $mensaje = "Los cambios no puedieron guardarse.";
            }
            $exito['mensaje'] = $mensaje;
        }
        $datos_personales = $propietario->ver($session['usuario']['id']);
        echo $twig->render('enlinea/propietario/formulario.html.twig', array(
            "session" => $session,
            "propietario" => $datos_personales['data'][0],
            "accion" => "actualizar",
            "resultado" => $exito,
            "id" => $_GET['id']));
        break;
    
    case "clavesActualizadas":
        $listado = $propietario->listarPropietariosClavesActualizadas();


        if ($listado['suceed'] && count($listado['data']) > 0) {
            foreach ($listado['data'] as $clave) {


                $propietario->actualizar($clave["id"], Array("cambio_clave" => 0));


                echo $clave["id_inmueble"] . "|";
                echo $clave["apto"] . "|";
                echo $clave["clave"] . "<br>";
            
                
            }
        }
        break; // </editor-fold>
       
    case "actualizados":
        $resultado = $propietario->obtenerPropietariosActualizados($id);
        if ($resultado['suceed'] && count($resultado['data']) > 0) {
            foreach ($resultado['data'] as $actualizado) {
                echo "|" . $actualizado['cedula'] . "|" . $actualizado['id_inmueble'];
                echo "|" . $actualizado['apto'] . "|" . $actualizado['clave'] . "|" . substr(utf8_decode($actualizado['direccion']),0,254);
                echo "|" . $actualizado['telefono1'] . "|" . $actualizado['telefono2'];
                echo "|" . $actualizado['telefono3'] . "|" . $actualizado['email'] . "|" . $actualizado['email_alternativo'] . "<br>";
            }
        }
        break;
    

    case "clave-servicio":
        $propietario = new propietario();
        
        if (!is_numeric($id))
            $id = null;
            
        
        $propietario->envioMasivoEmail('Nuevo servicio web', '../plantillas/clave-servicio.html', $id);
        break; // </editor-fold>
    
        
    case 'sesion':
        $result = $propietario->ver($id);
        $persona = array();
        if($result['suceed'] && count($result['data'])>0) {
            $persona = $result['data'][0];
        }
        echo json_encode($persona);
        break;
    
    case 'login':
        
        $result = [];
        
        if (isset($_POST['email'])) {
            $data = $_POST;
            
            if ($data['password'] != '') {
                
                $result  = $propietario->login($data['email'], $data['password']);
                $result['existe']   = TRUE;            
                
            } else {
                
                $r = $propietario->emailRegistrado($data['email']);
                
                if ($r['suceed'] && count($r['data']) > 0 ) {

                    $result['existe']   = TRUE;
                    $result['id']       = base64_encode($r['data'][0]['id']);
                    if(session_status()  == PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['id']     = $result['id'];
                    $result['email']    = $data['email'];

                } else {

                    $result['existe'] = FALSE;
                    $result['mensaje'] = '<strong>Ups!</strong> Correo electrónico no registrado.';
                }
            }
        } else {
            
            $result['existe'] = FALSE;
        }
        echo json_encode($result);
        break;
    
    case 'recuperar-password':
        if (isset($id)) {
            $result = $propietario->recuperarContraSena($id);
            echo $twig->render('index.html.twig', array('resultado' => $result));
        }
        break; 

}
