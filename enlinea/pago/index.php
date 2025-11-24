<?php
include_once '../../includes/constants.php';
$propietario = new propietario();
if (!isset($_GET['id'])) {    
    $propietario->esPropietarioLogueado();
    $session = $_SESSION;
}
$bitacora = new bitacora();

$accion = isset($_GET['accion']) ? $_GET['accion'] : "listar";

function file_get_contents_curl($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
function limpiarString($String){ 
   $String = str_replace(array("|","|","[","^","´","`","¨","~","]","'","#","{","}",".","","\t","\n"," ", "Bs/USD","USD"),"",$String);
   return $String;
}

switch ($accion) {
    
    case "listaRecibosCanceladosNoPublicados":
        $pagos = new pago();
        $pago = $pagos->listarPagosProcesadosWeb();
        if ($pago['suceed']) {
            $n = 0;
            foreach ($pago['data'] as $r) {
                $filename = "../../cancelacion.gastos/" . $r['id_factura'] . ".pdf";
                if (!file_exists($filename)) {
                    echo $r['id_factura'] . "|" . $r['id_inmueble'] . "|" . $r['id_apto'] . "<br>";
                    $n++;
                }
            }
            echo "Total Recibos: " . $n;
        }
        break; 
    
    case "cancelacion":
        // Asegurarse de que la sesión esté iniciada para obtener cod_admin
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $cod_admin = isset($_SESSION['usuario']['cod_admin']) ? $_SESSION['usuario']['cod_admin'] : '';

        // Usar ruta de fichero local en lugar de URL para conservar la sesión del cliente
        $filename = __DIR__ . '/../../cancelacion.gastos/' . $_GET['id'] . $cod_admin . '.pdf';

        if (!is_file($filename)) {
            header("HTTP/1.1 404 Not Found");
            echo "Archivo no encontrado";
            break;
        }

        $titulo = basename($filename);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $titulo . '"');
        header('Content-Length: ' . filesize($filename));

        // Liberar lock de sesión antes de enviar el fichero
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        readfile($filename);
        exit;
        break;
    
    case "ver":
        $propiedad = new propiedades();
        $inmuebles = new inmueble();
        $pagos = new pago();

        $propiedades = $propiedad->propiedadesPropietario(
                $session['usuario']['cedula'],
                $session['usuario']['cod_admin']);
        $cuenta = [];
        
        if ($propiedades['suceed'] == true) {
            
            foreach ($propiedades['data'] as $propiedad) {
                
                $legal = $propiedad['meses_pendiente'] > MESES_COBRANZA;
                
                $inmueble = $inmuebles->verDatosInmueble(
                       $propiedad['id_inmueble'],
                       $session['usuario']['cod_admin']);
                
                $pago = $pagos->listarPagosProcesados($propiedad['id_inmueble'], $propiedad['apto'], $session['usuario']['cod_admin'], 5);
                
                if ($pago['suceed'] == true) {
                    $bitacora->insertar(Array(
                        "id_sesion"=>$session['id_sesion'],
                        "id_accion"=> 12,
                        "descripcion"=>count($pago['data'])." recibos(s) registrado(s).",
                    ));
                    for ($index = 0; $index < count($pago['data']); $index++) {
                        
                        if (RECIBO_GENERAL === 1) {
                            $filename = "../../cancelacion.gastos/" . $pago['data'][$index]['n_recibo'] . ".pdf";
                            $pago['data'][$index]['recibo'] = file_exists($filename);

                        } else {
                            
                            $filename = "../../cancelacion.gastos/".$pago['data'][$index]['numero_factura'].".pdf";
                            $pago['data'][$index]['recibo'] = file_exists($filename);
                        }
                    }
                    $cuenta[] = [
                                    "cuentas"     => $pago['data'],
                                    "inmueble"    => $inmueble['data'][0],
                                    "legal"       => $legal,
                                    "propiedades" => $propiedad,
                                ];
                }
            }
        }
        $params = [
            "session" => $session,
            "cuentas" => $cuenta,
        ];

        echo $twig->render('enlinea/pago/cancelacion.html.twig', $params);
        break; 
    
    case "procesar":
        $pago = new pago();
        $data = $_POST;


        $ExpirationDate = $data['mes'] . "/" . $data['year'];
        $ip = Misc::getRealIP();
        
        $fields = array(
            "KeyId" => 0,
            "PublicKeyId" => 0,
            "Amount" => Misc::format_mysql_number($data['monto']),
            "Description" => "Pago de Condominio",
            "CardHolder" => $data['CardHolder'],
            "CardHolderID" => $data['CardHolderID'],
            "CardNumber" => str_replace("-", "", $data['CardNumber']),
            "CVC" => $data['CVC'],
            "ExpirationDate" => $ExpirationDate,
            "StatusId" => "2",
            "IP" => $ip,
            "OrderNumber" => "100",
            "Address" => "",
            "City" => "",
            "ZipCode" => "1031",
            "State" => "DF",
            "TypUsr" => $data['TypUsr'],
            "Correo" => $data['email'],
            "TelTitular" => str_replace("-", "", $data['telefono']));
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.pagoxpress.com.ve/Api/api/Commerce/Payment",
            //CURLOPT_URL => "https://www.pagoxpress.com.ve/Apitest/api/Commerce/Payment",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($fields),
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/x-www-form-urlencoded",
                "publickeyid: 42F9F956EBB148B4A574F85E7E66E83D"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl); 

        if ($err) {
            echo "cURL Error #:" . $err;
            break;
        } else {
            $respuesta = json_decode($response);

            $data['Code'] = $respuesta->{'code'};
            
            if ($data['Code'] != 201) { 
                // si el pago es rechazado, registramos el pago como rechazado
                $data['estatus'] = 'R';
            }
            $data['numero_documento']   = date("YmdHi", time());//$respuesta->{'reference'};
            $data['banco_origen']       = $respuesta->{'lote'};
            $data['Message']            = $respuesta->{'message'};
            $data['ip']                 = $ip;
            $data['voucher']            = $respuesta->{'voucher'};
            $data['fecha']              = date("Y-m-d H:i:00 ", time());

            $exito = $pago->procesarPagoEnLinea($data);

            $bitacora->insertar(Array(
                "id_sesion" => $session['id_sesion'],
                "id_accion" => 18,
                "descripcion" => $data['numero_documento']." > ".$data['Message']." > ".$exito['mensaje'],
            ));
            echo $response;
        
        }


        break; 
    
    case "guardar":
        $pago = new pago();
        $data = $_POST;
        
        // if (isset($session['usuario']['directorio'])) {
        //     $p = $propietario->obtenerPropietario( $data['id_inmueble'][0],  $data['id_apto'][0]);
        //     foreach ($p as $i) {
        //         $celular = str_replace("(", "", $data['telefono']);
        //         $celular = str_replace(")", "", $celular);
        //         $celular = str_replace("-", "", $celular);
        //         $celular = str_replace(" ", "", $celular);
        //         $propietario->actualizar($i['id'], Array('email'=>$data['email'],'telefono3'=>$celular,'modificado'=>1)); 
        //     }
        // } else {
        //     $celular = str_replace("(", "", $data['telefono']);
        //     $celular = str_replace(")", "", $celular);
        //     $celular = str_replace("-", "", $celular);
        //     $celular = str_replace(" ", "", $celular);
        //     $datos_contacto = Array('email'=>$data['email'],'telefono3'=>$celular,'modificado'=>1);
        //     $propietario->actualizar($session['usuario']['id'], $datos_contacto);
        //     $_SESSION['usuario']['email']=$data['email'];
        //     $_SESSION['usuario']['telefono3']=$celular;
        // }
        if (isset($_FILES['soporte'])) {
            $file = explode(".",$_FILES['soporte']['name']);
            $extension = strtolower(end($file));
            $data['soporte']=$data['tipo_pago'].$data['numero_documento'].'_'.$data['id_inmueble'][0].'_'.$data['id_apto'][0].'.'.$extension;
            $tempFile = $_FILES['soporte']['tmp_name'];
            $mainFile = $data['soporte'];
            move_uploaded_file($tempFile,"soportes/".$mainFile);
        }
        if (count($data) > 0) {
            unset($data['registrar']);
            $data['fecha']=date("Y-m-d H:i:00 ", time());
            $exito = $pago->registrarPago($data);
            
            $bitacora->insertar(Array(
                "id_sesion"     => $session['id_sesion'],
                "id_accion"     => 9,
                "descripcion"   => $data['numero_documento']." >".$exito['mensaje'],
            ));
        } else {
            header("location:".URL_SISTEMA."/pago/registrar");
            return;
        }
        
        echo json_encode($exito);
        
        break;
    
    case "registrar":
    case "listar":
    default :
        $propiedad = new propiedades();
        $facturas = new factura();
        $inmuebles = new inmueble();
        $bcvRates = new historicoTasaBDV();

        $resultado = [];
        $cuenta = [];
        $bancos = [];

        if ($accion == 'guardar') {
            $resultado = $exito;
        }
        
        $propiedades = $propiedad->propiedadesPropietario(
                $session['usuario']['cedula'],
                $session['usuario']['cod_admin']);

        
        $result = $inmuebles->listarBancosActivos();
        
        if ($result['suceed']) {
            $bancos = $result['data'];
        }
        
        $bitacora->insertar(Array(
            "id_sesion"=>$session['id_sesion'],
            "id_accion"=> 8,
            "descripcion"=>'Inicio del proceso',
        ));
        
        if ($propiedades['suceed'] == true) {

            foreach ($propiedades['data'] as $propiedad) {

                $inmueble = $inmuebles->verDatosInmueble(
                        $propiedad['id_inmueble'],
                        $session['usuario']['cod_admin']);
                
                if ($session['usuario']['cod_admin'] == '0012') {
                    $factura = $facturas->estadoDeCuentaPagos(
                        $session['usuario']['cod_admin'],
                        $propiedad['id_inmueble'],
                        $propiedad['apto']);

                } else {

                    $factura = $facturas->estadoDeCuenta(
                            $session['usuario']['cod_admin'],
                            $propiedad['id_inmueble'],
                            $propiedad['apto']);
                            
                }
                
                if ($factura['suceed'] == true) {
                    
                    if ($propiedad['meses_pendiente'] < MESES_COBRANZA) {
                    
                        for ($index = 0; $index < count($factura['data']); $index++) {
                            $filename = "../avisos/" . $factura['data'][$index]['numero_factura'].
                                    $session['usuario']['cod_admin'].".pdf";
                            
                            $factura['data'][$index]['aviso'] = file_exists($filename);
                            
                            $r = pago::facturaPendientePorProcesar($factura['data'][$index]['periodo'], $factura['data'][$index]['id_inmueble'], $factura['data'][$index]['apto']);
                            
                            if ($r['suceed'] && count($r['data'])>0) {
                                $factura['data'][$index]['pagado'] = 1;
                                $factura['data'][$index]['pagado_detalle']= "<i class='fa fa-calendar-o'></i> ".
                                        Misc::date_format($r['data'][0]['fecha'])."<br>".
                                        strtoupper($r['data'][0]['tipo_pago']." - Ref: ".
                                                $r['data'][0]['numero_documento']."<br>Monto: ".
                                                number_format($r['data'][0]['monto'],2,",","."));
                            } else {
                                $factura['data'][$index]['pagado'] = 0;
                                $factura['data'][$index]['pagado_detalle']='';
                            }
                        }
                    }
                    $banco = $inmuebles->obtenerCuentasBancariasPorInmueble(
                            $session['usuario']['cod_admin'],
                            $propiedad['id_inmueble']);
                    //var_dump($banco);
                    $inmueble['data'][0]['cuentas_bancarias'] = $banco['data'];
                    
                    $cuenta[] = [
                        'cuentas'     => $factura['data'],
                        'inmueble'    => $inmueble['data'][0],
                        'propiedades' => $propiedad,
                        'resultado'   => $resultado,
                    ];
                }
                
            }
        }
        
        $tasa   = [
            'dolar' => '0,00',
            'usd'   => '-,--',
        ];
        $exchangeRate = $bcvRates->getPriceDollar();
       
        if ($exchangeRate!= null) {
            
            $update = $exchangeRate['updated']; 

            // Obtener la fecha y hora actual del servidor
            $dtServidor = new DateTime(); 
            $fechaServidor = $dtServidor->format("Y-m-d");
            $horaServidor = $dtServidor->format("H:i:s");
            
            // Verificar si es sábado o domingo
            $diaSemana = $dtServidor->format("N"); // 1 (Lunes) - 7 (Domingo)
            
            // Crear objeto DateTime para la fecha de actualización
            $dtUpdate = new DateTime($update);
            $fechaUpdate = $dtUpdate->format("Y-m-d");
            $horaUpdate = $dtUpdate->format("H:i:s");

            // Definir la hora de referencia (3:00 PM)
            $horaReferencia = "15:00:00";
            // Comprobación
            
            if ($diaSemana == 6 || $diaSemana == 7) {
                $tasa['usd'] = $exchangeRate['prev'];
            }
            elseif ($fechaServidor === $fechaUpdate && $horaUpdate > $horaReferencia) {
                $tasa['usd'] = $exchangeRate['prev'];
            } else {
                $tasa['usd'] = $exchangeRate['price'];
            }
        }

        $params = [
            'accion'      => $accion,
            'bancos'      => $bancos,
            'cuentas'     => $cuenta,
            'propiedades' => $propiedades['data'],
            'session'     => $session,
            'tasa'        => $tasa,
            'usuario'     => $session['usuario'],
        ];
        if ($session['administra']['codigo']=='0012') {
            $url = 'enlinea/pago/formulario.html.0012.twig';
        } else {

            $url = 'enlinea/pago/formulario.html.twig';
        }
        echo $twig->render($url, $params);
        break; 
        
    case "pago-tdc":
        $propiedad = new propiedades();
        $facturas = new factura();
        $inmuebles = new inmueble();
        $resultado = Array();
        if ($accion == 'guardar') {
            $resultado = $exito;
        }

        
        $propiedades = $propiedad->propiedadesPropietario(
                $session['usuario']['cedula'],
                $session['usuario']['cod_admin']);
        
        $cuenta = Array();


        $bitacora->insertar(Array(
            "id_sesion" => $session['id_sesion'],
            "id_accion" => 17,
            "descripcion" => 'Inicio del proceso Pago en línea',
        ));
        if ($propiedades['suceed'] == true) {

            foreach ($propiedades['data'] as $propiedad) {

                $inmueble = $inmuebles->verDatosInmueble(
                        $propiedad['id_inmueble'],
                        $session['usuario']['cod_admin']);
                $factura = $facturas->estadoDeCuenta(
                        $session['usuario']['cod_admin'],
                        $propiedad['id_inmueble'],
                        $propiedad['apto']);

                if ($factura['suceed'] == true) {

                    if ($propiedad['meses_pendiente'] < MESES_COBRANZA) {

                        for ($index = 0; $index < count($factura['data']); $index++) {
                            $filename = "../avisos/" . $factura['data'][$index]['numero_factura'] . ".pdf";
                            $factura['data'][$index]['aviso'] = file_exists($filename);
                            $r = pago::facturaPendientePorProcesar($factura['data'][$index]['periodo'], $factura['data'][$index]['id_inmueble'], $factura['data'][$index]['apto']);
                            if ($r['suceed'] && count($r['data']) > 0) {
                                $factura['data'][$index]['pagado'] = 1;
                                $factura['data'][$index]['pagado_detalle'] = "<i class='fa fa-calendar-o'></i> " .
                                        Misc::date_format($r['data'][0]['fecha']) . "<br>" .
                                        strtoupper($r['data'][0]['tipo_pago'] . " - Ref: " .
                                                $r['data'][0]['numero_documento'] . "<br>Monto: " .
                                                number_format($r['data'][0]['monto'], 2, ",", "."));
                            } else {
                                $factura['data'][$index]['pagado'] = 0;
                                $factura['data'][$index]['pagado_detalle'] = '';
                            }
                        }
                    }
                    $banco = $inmuebles->obtenerCuentasBancariasPorInmueble($session['usuario']['cod_admin'], $propiedad['id_inmueble']);
                    $inmueble['data'][0]['cuentas_bancarias'] = $banco['data'];
                    $cuenta[] = Array(
                        "inmueble"      => $inmueble['data'][0],
                        "propiedades"   => $propiedad,
                        "cuentas"       => $factura['data'],
                        "resultado"     => $resultado
                    );
                }
            }
        }
        
        echo $twig->render('enlinea/pago/pagoTDC.html.twig', array(
            "session"   => $session,
            "cuentas"   => $cuenta,
            "accion"    => $accion,
            "usuario"   => $session['usuario'],
            "propiedades" => $propiedades['data']
        ));
        break; 
            
    case "listaPagosDetalle":
        $pagos = new pago();
        $pago_detalle = $pagos->detalleTodosPagosPendientes();
        if ($pago_detalle['suceed'] && count($pago_detalle['data']) > 0) {
            echo "id_pago,id_inmueble,id_apto,monto,id_factura<br>";
            foreach ($pago_detalle['data'] as $value) {
                echo $value['id_pago'] . ",";
                echo "\"".$value['id_inmueble']."\",";
                echo $value['id_apto'] . ",";
                echo $value['monto'] * 100 . ",";
                echo "\"".$value['id_factura']."\"";
                echo "<br>";
            }
        }
        break; 

    case "listaPagosMaestros":
        $pagos = new pago();
        $pagos_maestro = $pagos->listarPagosPendientes($_GET['id']);

        if ($pagos_maestro['suceed'] && count($pagos_maestro['data']) > 0) {
            echo "id,fecha,tipo_pago,numero_documento,fecha_documento,monto,banco_origen,";
            echo "banco_destino,numero_cuenta,estatus,email,enviado,telefono<br>";
            foreach ($pagos_maestro['data'] as $pago) {
                echo $pago['id'] . ",";
                echo Misc::date_format($pago['fecha']) . ",";
                echo strtoupper($pago['tipo_pago']) . ","; 
                echo $pago["numero_documento"].",";
                echo Misc::date_format($pago["fecha_documento"]) . ",";
                echo $pago["monto"] * 100 . ",";
                echo $pago["banco_origen"] . ",";
                echo $pago["banco_destino"] . ",";
                echo str_replace("-", "", "#".$pago["numero_cuenta"]) . ",";
                echo strtoupper($pago["estatus"]) . ",";
                echo $pago["email"] . ",";
                echo $pago["enviado"] . ",";
                echo $pago["telefono"];
                echo "<br>";
            }
        }
        break; 

    case "listarPagosPendientesOld":
        $pagos = new pago();
        $pagos_maestro = $pagos->listarPagosPendientes($session['usuario']['cod_admin']);
        
        if ($pagos_maestro['suceed'] && count($pagos_maestro['data']) > 0) {
            
            foreach ($pagos_maestro['data'] as $pago) {

                $pago_detalle = $pagos->detallePagoPendiente($pago['id']);
                
                if ($pago_detalle['suceed'] && count($pago_detalle['data']) > 0) {
                    $enviado = $pago["enviado"] == 0 ? "False" : "True";
                    echo "|" . $pago['id'] . "|";
                    echo Misc::date_format($pago['fecha']) . "|";
                    echo strtoupper($pago['tipo_pago']) . "|";
                    echo $pago["numero_documento"] . "|";
                    echo Misc::date_format($pago["fecha_documento"]) . "|";
                    echo Misc::number_format($pago["monto"]) . "|";
                    echo $pago["banco_origen"] . "|";
                    echo $pago["banco_destino"] . "|";
                    echo $pago["numero_cuenta"] . "|";
                    echo strtoupper($pago["estatus"]) . "|";
                    echo $pago["email"] . "|";
                    echo $enviado . "|";
                    echo $pago["telefono"] . "|";
                    // --
                    foreach ($pago_detalle['data'] as $value) {
                        echo $value['id_inmueble'] . "|";
                        echo $value['id_apto'] . "|";
                        echo Misc::number_format($value['monto']) . "|";
                        echo $value['id_factura'] . "|";
                        echo $value['periodo'] . "|";
                    }
                    echo "<br>";
                
                }
            
            }
        

        } else {
            echo "0";
        }


        break; // </editor-fold>

    case "listarPagosPendientes":
    case "listarPagosPendientesTDC":
        $pagos = new pago();
        $cod_admin = $_GET['id'];
        if ($accion=='listarPagosPendientes') {
            $pagos_maestro = $pagos->listarPagosPendientes_new($cod_admin);
        } else {
            $pagos_maestro = $pagos->listarPagosPendientes_enlinea();
        }
        
        if ($pagos_maestro['suceed']) {
            
            if (count($pagos_maestro['data']) > 0) {
                foreach ($pagos_maestro['data'] as $pago) {
                    $pago_detalle = $pagos->detallePagoPendiente($pago['id']);
                    if ($pago_detalle['suceed'] && count($pago_detalle['data']) > 0) {
                        $enviado = $pago["enviado"] == 0 ? "False" : "True";
                        echo "|" . $pago['id'] . "|";
                        echo Misc::date_format($pago['fecha']) . "|";
                        echo strtoupper($pago['tipo_pago']) . "|";
                        echo $pago["numero_documento"] . "|";
                        echo Misc::date_format($pago["fecha_documento"]) . "|";
                        echo Misc::number_format($pago["monto"]) . "|";
                        echo $pago["banco_origen"] . "|";
                        echo $pago["banco_destino"] . "|";
                        echo $pago["numero_cuenta"] . "|";
                        echo strtoupper($pago["estatus"]) . "|";
                        echo $pago["email"] . "|";
                        echo $enviado . "|";
                        echo $pago["telefono"] . "|";
                        echo $pago["usuario_intranet"] . "|";
                        // --
                        foreach ($pago_detalle['data'] as $value) {
                            echo $value['id_inmueble'] . "|";
                            echo $value['id_apto'] . "|";
                            echo Misc::number_format($value['monto']) . "|";
                            echo $value['id_factura'] . "|";
                            echo $value['periodo'] . "|";
                        }
                        echo "<br>";

                    }
                }
            } else {
                echo "0";
            }

        } else {
            echo var_dump($pagos_maestro);
        }
        break; 
            
    case "confirmar":

        $pago = new pago();
        $id = $_GET['id'];
        $estatus = $_GET['estatus'];
        $recibo = isset($_GET['recibo'])? $_GET['recibo']:null;
        
        $r = $pago->procesarPago($id, $estatus,$recibo);
        echo $r;
        break;
    
   
    case "reenviarEmailRegistroPago":
        $pago = new pago();
       $id = $_GET['id'];
        $pago->reenviarEmailPagoRegistrado($id);
        break; 
    
    case "reenviarEmailPagoEnLinea":
        $pago = new pago();
        $id = $_GET['id'];
        $resultado = $pago->enviarEmailPagoEnLinea($id);
        break; 
        
    case "reenviarEmailPagoEnLinea_":
        $pagos = new pago();
        $id = $_GET['id'];

        $data = $pagos->ver($id);


        if ($data['suceed'] === TRUE && count($data['data']) > 0) {
            $propietario = 'Propietario(a)';


            $archivo = '';
            $archivo1 = '';
            // get the HTML
            //$id_pago = 26106;
            $total = 0;


            $monto = $data['data'][0]['monto'];

            $pago_detalle = $pagos->detallePagoPendiente($id);

            if ($pago_detalle['suceed'] && count($pago_detalle['data']) > 0) {
                $inmueble = new inmueble();
                $codigo_apto = $pago_detalle['data'][0]['id_apto'];
                $codigo_inmueble = $pago_detalle['data'][0]['id_inmueble'];
                $datos_inmueble = $inmueble->ver($codigo_inmueble);

                $nombre_inmueble = "";
                if ($datos_inmueble['suceed'] && count($datos_inmueble['data']) > 0) {
                    $nombre_inmueble = $datos_inmueble['data'][0]['nombre_inmueble'];
                }
            } else {
                die('No se pudo generar el comprobante. No se encuentra el detalle del pago');
            }



            ob_start();
            
            ?>
            <page>
            <div style="rotate: 90; position: absolute; width: 100mm; height: 4mm; left: 212mm; top: 0; font-style: italic; font-weight: normal; text-align: center; font-size: 2.5mm;">
            Recibo electrónico del servicio de pago en línea de grupoveneto.com
            </div>
            <div style="width: 90%; margin-left:50px">
            <table style="width: 100%;" cellspacing="1mm" cellpadding="0" >
            <tr>
            <td style="width: 100%;">
            <div class="zone" style="height: 26mm;position: relative;font-size: 5mm;">
            <img src="../../assets/images/logo-app.png" alt="logo" style="margin-top: 3mm;">
            <div style="position: absolute; right: 3mm; bottom: 3mm; text-align: right; font-size: 3mm; ">
            Fecha: <?php echo date('d/m/Y H:i:s'); ?><br><br>
            <span style="font-size: 4mm;"><b>Comprobante de pago en línea Nº <?php echo $id_pago ?></b></span>
            </div>
            </div>
            </td>
            </tr>
            <tr>
            <td>
            <div class="zone" style="height: 60mm;text-align: justify; font-size: 3.5mm; padding: 14px;">
            <p style="line-height: 6mm">
            Hemos recibido de <b><?php echo $session['usuario']['nombre'] ?></b>, propietario del inmueble
            <b><?php echo $codigo_apto ?></b> del condominio <b><?php echo $nombre_inmueble ?></b>, la cantidad de 
            <b><?php echo number_format($monto, 2, ",", ".") ?> Bolívares</b>, correspondientes al pago de:<br><br>
            </p>
            <?php if ($pago_detalle['suceed'] && count($pago_detalle['data']) > 0) { ?>
                            <table style="width: 500px">
                <?php
                foreach ($pago_detalle['data'] as $detalle) {

                    $total += $detalle['monto'];
                ?>
                                <tr>
                                <td style="width: 75%">Pago de Condominio (<?php echo Misc::date_periodo_format($detalle['periodo']) ?>)</td>
                                <td style="width:25%; text-align: right"><?php echo number_format($detalle['monto'], 2, ",", ".") ?></td>
                                </tr>
                <?php } ?>
                            <tr>
                            <td style="text-align: right"><b>Total:</b></td>
                            <td style="text-align: right"><b><?php echo number_format($total, 2, ",", ".") ?></b></td>
                            </tr>
                            </table>
            <?php } ?>
                        <br>
                        <br>
                        </div>
                        </td>
                        </tr>
                        <tr>
                        <td>
                        <div style="border:1px solid #000; width: 160px">
            <?php echo $html ?>
                        </div>
                        </td>
                        </tr>
                        </table>
                        </div>
                        </page>
            <?php

            
            // $content = ob_get_clean();
            // // convert to PDF
            // require_once('../../includes/html2pdf/html2pdf.class.php');


            // try             {
            //     $html2pdf = new HTML2PDF('P', 'Letter', 'fr', true, 'UTF-8', array(0, 10, 0, 0));
            //     $html2pdf->setDefaultFont("Helvetica");
            //     // recibo
            //     $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
            //     //$archivo = $html2pdf->Output('','S');
            //     $html2pdf->Output('ticket.pdf');

            //     //$mail = new mailto(SMTP);
            //     //$voucher = Array("Recibo de pago"=>$archivo);
            //     //$r = $mail->enviar_email("Pago electrónico web", "Adjunto", '', $_POST['email'], "",null,null,$voucher);
            //                     //$archivo='';
            //     //if ($r=='') {
            //     //    echo 'Email enviado con éxito';
            //     //} else {
            //     //    echo 'envio fallido';
            //     //}
            // }             catch (HTML2PDF_exception $e) {
            //     echo $e;
            //     exit;
            // }
        }
        break; 
            
    case "historico":
        $propiedad = new propiedades();
        $pagos = new pago();
        $inmuebles = new inmueble();

        $propiedades = $propiedad->propiedadesPropietario(
                $_SESSION['usuario']['cedula'],
                $_SESSION['usuario']['cod_admin']);


        $historico = Array();
        if ($propiedades['suceed'] == true) {


            foreach ($propiedades['data'] as $propiedad) {
                $bitacora->insertar(Array(
                    "id_sesion"   => $session['id_sesion'],
                    "id_accion"   => 16,
                    "descripcion" => $propiedad['id_inmueble']." - ".$propiedad['apto'],
                ));

                $inmueble = $inmuebles->verDatosInmueble(
                        $propiedad['id_inmueble'],
                        $session['usuario']['cod_admin']);

                $p = $pagos->estadoDeCuenta(
                        $session['usuario']['cod_admin'],
                        $propiedad['id_inmueble'],
                        $propiedad['apto']);

                if ($p['suceed'] == true) {
                    for ($index = 0; $index < count($p['data']); $index++) {
                        $filename = "../../cancelacion.gastos/" . $p['data'][$index]['numero_recibo'].$session['usuario']['cod_admin']. ".pdf";
                        $p['data'][$index]['recibo'] = file_exists($filename);
                    }
                    $historico[] = Array(

                        'inmueble'  => $inmueble['data'][0],
                        'propiedad' => $propiedad,
                        'pagos'     => $p['data']
                        
                    );
                }
            }
        }
        echo $twig->render('enlinea/pago/historico.html.twig', array(
            'session'      => $session,
            'propiedades'  => $propiedad,
            'historicos'   => $historico
        ));
        break; 
        
    case "actualizar_factura":
        
        if (isset($_GET['inmueble']) && isset($_GET['apto']) && isset($_GET['id']) && isset($_GET['monto'])) {
            $pagos = new pago();
            $r = $pagos->actualizarFactura($_GET['inmueble'], $_GET['apto'], $_GET['id'], $_GET['monto']);
            if (!$r['stats']['error'] == '') {
                echo $r['stats']['error'];
            }

        
        }
        break; 
        
    case "listarPagosProcesadosGeneral":
        $pagos = new pago();
        $desde = null;
        $hasta = null;
        $codigo_inmueble = null;
        if (isset($_GET['desde'])) {
            $desde = $_GET['desde'];
        }
        if (isset($_GET['hasta'])) {
            $hasta = $_GET['hasta'];
        }
        if (isset($_GET['inmueble'])) {
            $codigo_inmueble = $_GET['inmueble'];
        }


        $pagos_maestro = $pagos->listarPagosProcesadosRangoFechas($desde, $hasta, $codigo_inmueble);

        if ($pagos_maestro['suceed'] && count($pagos_maestro['data']) > 0) {


            foreach ($pagos_maestro['data'] as $pago) {

                $pago_detalle = $pagos->detallePagoPendiente($pago['id']);


                if ($pago_detalle['suceed'] && count($pago_detalle['data']) > 0) {
                    $enviado = $pago["enviado"] == 0 ? "False" : "True";
                    echo "|" . $pago['id'] . "|";
                    echo Misc::date_format($pago['fecha']) . "|";
                    echo strtoupper($pago['tipo_pago']) . "|";
                    echo $pago["numero_documento"] . "|";
                    echo Misc::date_format($pago["fecha_documento"]) . "|";
                    echo Misc::number_format($pago["monto"]) . "|";
                    echo $pago["banco_origen"] . "|";
                    echo $pago["banco_destino"] . "|";
                    echo $pago["numero_cuenta"] . "|";
                    echo strtoupper($pago["estatus"]) . "|";
                    echo $pago["email"] . "|";
                    echo $enviado . "|";
                    echo $pago["telefono"] . "|";
                    // --
                    foreach ($pago_detalle['data'] as $value) {
                        echo $value['id_inmueble'] . "|";
                        echo $value['id_apto'] . "|";
                        echo Misc::number_format($value['monto']) . "|";
                        echo $value['id_factura'] . "|";
                        echo $value['periodo'] . "|";
                    }
                    echo "<br>";
                }
            }
        } else {
            echo "0";
        }
        break; 

    case "reenviarNotificacionRegistroPago":
        $pago = new pago();
        $lista = $pago->listarPagosEmailRegisroNoEnviado();

        if ($lista['suceed'] && count($lista['data']) > 0) {
            foreach ($lista['data'] as $registro) {
                $pago->reenviarEmailPagoRegistrado($registro['id']);
                echo '<br />';
            }
        } else {
            echo 'No hay notificaciones pendientes por reenviar';
        }
        break;
}