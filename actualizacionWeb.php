<?php

ini_set('max_execution_time', 30000);

header('Content-type: text/html; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once 'includes/db.php';
include_once 'includes/file.php';

$db = new db();
$administradoras = new administradora();

function checkPropertiesAdministrator($cod_admin, $maxInmueble, &$mensaje) {
    // aquí comprobamos la cantidad de edificios que tiene registrado
    $inmueble = new inmueble();
    $inmuebles = $inmueble->getPropertyByAdmin($cod_admin);
    if ($inmuebles['suceed']) {
        
        $countInm = count($inmuebles['data']);
        $countInm++;
        if($countInm > $maxInmueble) {
            $mensaje.= "**********<br>";
            $mensaje.= "Este es un servicio compartido con otros administradores. Y le permite registrar un máximo de $maxInmueble condominios.<br>";
            $mensaje.= "Usted ha excedido el máximo permitido.<br>";
            $mensaje.= "**********<br>";

            if($cod_admin == "056") {
                $mensaje.= "El servicio estará disponible hasta el 21/02/2025";

            } else {
                die($mensaje);

            }
        }
        elseif ($countInm == $maxInmueble){
            $mensaje.= "**********<br>";
            $mensaje.= "Este es un servicio compartido con otros administradores. Y le permite registrar un máximo de $maxInmueble condominios.<br>";
            $mensaje.= "Usted está en el límite permitido.<br>";
            $mensaje.= "**********<br>";
        } else {
            $mensaje.= "**********<br>";
            $mensaje.= "Este es un servicio compartido con otros administradores. Y le permite registrar un máximo de $maxInmueble condominios.<br>";
            $mensaje.= "Actualmente tiene registrado $countInm.<br>";
            $mensaje.= "**********<br>";
        }
    }
}

if (isset($_GET['cod_admin'])) {
    $cod_admin = $_GET['cod_admin'];
    
    $r = $administradoras->verPorCodigo($cod_admin);
    
    if ($r['suceed'] && count($r['data']) > 0) {
        $administradora = $r['data'][0];
        $adminName = $administradora['nombre'];
        $maxInmueble = $administradora['maxInmueble'];
//        echo '<pre>';
//        print_r($administradora);
//        echo '-------<br>';
//        if ($administradora['inactivo']) {
//            echo 'Inactiva';
//        }
//        echo '</pre>';
//        die();
        
        if ($administradora['inactivo']) {
            $mail = new mailto(SMTP);
            $mensaje = "<strong>$adminName</strong><br>SERVICIO SUSPENDIDO. <br />Este servicio está incluido con el Soporte Premium.";
            $mensaje = '<div style="background-color:rgb(227,242,253);padding:50px 0 50px;">'
                    . '<div style="background-color:#fff; min-width:516px; max-width:516px;margin:0 auto; padding:40px 30px 50px 30px;">'
                    . '<div style="background: linear-gradient(145deg,#0d47a1,#42a5f5);padding-left:10px">'
                    . '<img alt="Tu Condominio en línea" src="'.ROOT. 'assets/images/_smarty/logo_dark.png" id="logo" width="240" height="96"></div>'
                    . '<hr style="border: 0;border-top: 1px solid #eee;margin-bottom:20px;">'
                    . '<div style="color:rgb(0,0,0);font-family:Helvetica;font-size:15px!important;font-weight:400;line-height:22px!important;color:#000000">' . $mensaje;
            $mensaje .= '</div><div style="display: block;width: 60px;height: 2px;margin:10px 0;background-color:#2A72D4;position: relative;float:left"></div>'
                    . '<div style="display: block;width: 60px;height: 2px;margin:10px 0;background-color:#1F41A3;position: relative;float:left"></div></div></div>';
            $r = $mail->enviar_email(
                "[Suspendido] Sincronización ". $_SERVER['SERVER_NAME'], $mensaje, 
                " [Valoriza2]", $administradora['email'], "", null, 'webmail.pronet21@gmail.com');
            
            die("$adminName<br/>SERVICIO SUSPENDIDO<br/>Este servicio está incluido con el Soporte Premium.");
            
        }
        
    } else {
        die('Acceso Denegado');
    }
//    echo '<pre>';
//    print_r($administradora);
//    echo '</pre>';
//    die();
} else {
    die('Ups! v2.web.ve: Faltan parámetros en el llamado de actualización');
}

if (isset($_GET['codinm'])) {
    
    $codinm = $_GET['codinm'];
    $db->exec_query("delete from factura_detalle where id_factura in (select numero_factura from facturas wher id_inmueble='$codinm') and cod_admin='$cod_admin'");
    $db->exec_query("delete from facturas where id_inmueble='$codinm' and cod_admin='$cod_admin'");
    $db->exec_query("delete from junta_condominio where id_inmueble='$codinm' and cod_admin='$cod_admin'");
    $db->exec_query("delete from propietarios where codinm='$codinm' and cod_admin='$cod_admin'");
    $db->exec_query("delete from propiedades where id_inmueble='$codinm' and cod_admin='$cod_admin'");
    $db->exec_query("delete from inmueble where id='$codinm' and cod_admin='$cod_admin'");
    $db->exec_query("delete from inmueble_deuda_confidencial where id_inmueble='$codinm' and cod_admin='$cod_admin'");
    $db->exec_query("delete from movimiento_caja where id_inmueble='$codinm' and cod_admin='$cod_admin'");
    $db->exec_query("delete from fondos where id_inmueble='$codinm' and cod_admin='$cod_admin'");
    $db->exec_query("delete from fondos_movimiento where id_inmueble='$codinm' and cod_admin='$cod_admin'");
    $db->exec_query("delete from historico_avisos_cobro where id_inmueble='$codinm' and cod_admin='$cod_admin'");
    $db->exec_query("delete from cancelacion_gastos where id_inmueble='$codinm' and cod_admin='$cod_admin'");

    $mensaje = "<strong>$adminName</strong><br>Actualización inmueble $codinm<br>";

} else {

    $tablas = array("factura_detalle", "facturas", "propiedades",
    "junta_condominio", "inmueble", "inmueble_deuda_confidencial", "movimiento_caja",
    "fondos", "fondos_movimiento", "historico_avisos_cobro","cancelacion_gastos");

    $mensaje = "<strong>$adminName</strong><br />Proceso de actualización general<br>";

    foreach ($tablas as $tabla) {
        $r = $db->exec_query("delete from $tabla where cod_admin='$cod_admin'");
        echo "limpiar tabla: $tabla<br />";

    }
    echo "<br>";
}
checkPropertiesAdministrator($cod_admin,$maxInmueble,$mensaje);

$archivo = ACTUALIZ . $cod_admin . '_' . ARCHIVO_INMUEBLE;
$contenidoFichero = JFile::read($archivo);

$lineas = explode("\r\n", $contenidoFichero);
$inmueble = new inmueble();
$mensaje .= "procesar archivo inmueble (" . count($lineas) . ")<br />";
echo $mensaje;

foreach ($lineas as $linea) {

    $registro = explode("\t", $linea);
    
    if ($registro[0] != "") {

        // echo '<code>';
        // print_r($registro);
        // echo '</code>';
        // die("Registros: ($n)");
        $item = ["id"               => $registro[0],
                "nombre_inmueble"   => $registro[1],
                "deuda"             => $registro[2],

                "fondo_reserva"     => $registro[3],
                "beneficiario"      => $registro[4],
                "banco"             => '',
                "numero_cuenta"     => '',
                "supervision"       => '0',
                "RIF"               => $registro[5],
                "meses_mora"        => $registro[6],
                "porc_mora"         => $registro[7],
                "moneda"            => $registro[8],
                "unidad"            => $registro[9],
                "facturacion_usd"   => $registro[10],
                "tasa_cambio"       => $registro[11],
                "redondea_usd"      => $registro[12],
                "cod_admin"         => $cod_admin,
                "direccion"         => mb_convert_encoding($registro[13], 'UTF-8', 'ISO-8859-1')];

        $r = $inmueble->insertar($item);

        if ($r["suceed"] == FALSE) {
            echo ARCHIVO_INMUEBLE . "<br/>" . $r['stats']['error'] . " " . '<br/>' . $r['query'] . '<br/>';
            die();
        }
    }
}

$archivo = ACTUALIZ . $cod_admin . '_' . ARCHIVO_CUENTAS;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$inmueble = new inmueble();

$mensaje .= "actualizando cuentas inmuebles (" . count($lineas) . ")<br />";
echo "actualizando cuentas inmuebles (" . count($lineas) . ")<br />";

foreach ($lineas as $linea) {
    $id = '';
    $registro = explode("\t", $linea);
    if ($registro[0] != "") {
        $id = $registro[0];
        $registro = Array(
            "numero_cuenta" => $registro[1],
            "banco" => $registro[2]);

        $r = $inmueble->actualizarCuentaDeBanco($id, $cod_admin, $registro);
        if ($r["suceed"] == FALSE) {
            //echo ARCHIVO_INMUEBLE."<br />".$r['stats']['errno']."<br />".$r['stats']['error'];
            echo $r['query'];
            die();
        }
    }
}

$archivo = ACTUALIZ . $cod_admin . '_CUENTAS_INMUEBLE.txt';
if (file_exists($archivo)) {
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    $mensaje .= "Procesar Cuentas Inmuebles (" . count($lineas) . ")<br />";
    echo "Procesar archivo Cuentas Inmuebles (" . count($lineas) . ")<br />";
    foreach ($lineas as $linea) {
        $registro = explode("\t", $linea);

        if ($registro[0] != "") {
            $registro = Array(
                "id_inmueble" => $registro[0],
                "numero_cuenta" => $registro[1],
                "banco" => $registro[2],
                "cod_admin" => $cod_admin);


            $r = $inmueble->agregarCuentaInmueble($registro);

            if ($r["suceed"] == FALSE) {
                //echo ARCHIVO_INMUEBLE."<br />".$r['stats']['errno']."<br />".$r['stats']['error'];
                echo $r['query'];
                //die();
            }
        }
    }
}

$archivo = "./data/CUENTAS_INMUEBLE.txt";
if (file_exists($archivo)) {
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    $mensaje .= "Procesar Cuentas Inmuebles (" . count($lineas) . ")<br />";
    echo "Procesar archivo Cuentas Inmuebles (" . count($lineas) . ")<br />";
    foreach ($lineas as $linea) {
        $registro = explode("\t", $linea);

        if ($registro[0] != "") {
            $registro = Array(
                "id_inmueble" => $registro[0],
                "numero_cuenta" => $registro[1],
                "banco" => $registro[2]);


            $r = $inmueble->agregarCuentaInmueble($registro);


            if ($r["suceed"] == FALSE) {
                //echo ARCHIVO_INMUEBLE."<br />".$r['stats']['errno']."<br />".$r['stats']['error'];
                echo $r['query'];
                die();
            }
        }
    }
} 

$archivo = ACTUALIZ . $cod_admin . '_' . ARCHIVO_JUNTA_CONDOMINIO;
if (file_exists($archivo)) {
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    $junta_condominio = new junta_condominio();
    echo "procesar archivo Junta Condominio (" . count($lineas) . ")<br />";
    $mensaje .= "procesar archivo Junta Condominio (" . count($lineas) . ")<br />";
    foreach ($lineas as $linea) {

        $registro = explode("\t", $linea);

        if ($registro[0] != "") {
            $registro = Array("id_cargo" => $registro[1],
                "id_inmueble" => $registro[0],
                "cedula" => $registro[2],
                "cod_admin" => $cod_admin);
            $r = $junta_condominio->insertar($registro);

            if ($r["suceed"] == FALSE) {
                echo ARCHIVO_JUNTA_CONDOMINIO . "<br />" . $r['stats']['errno'] . "<br />" . $r['stats']['error'];
            }
        }
    }
}

$archivo = ACTUALIZ . $cod_admin . '_' . ARCHIVO_PROPIETARIOS;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$propietario = new propietario();
echo "procesar archivo Propietarios (" . count($lineas) . ")<br />";
$mensaje .= "procesar archivo Propietarios (" . count($lineas) . ")<br />";
foreach ($lineas as $linea) {
    $registro = explode("\t", $linea);
    // $n = count($registro);
    // echo '<code>';
    // print_r($registro);
    // echo '</code>';
    // echo("Registros: ($n)");
    // echo 'Set Reg(10) '. isset($registro[10]);
    // echo 'Set Reg(11) '. isset($registro[11]);
    // die();
    if ($registro[0] != "") {

        $item = ['nombre'           => mb_convert_encoding($registro[0],'UTF-8','ISO-8859-1'),
                'clave'             => $registro[1],
                'email'             => $registro[2],
                'cedula'            => $registro[3],
                'telefono1'         => $registro[4],
                'telefono2'         => $registro[5],
                'telefono3'         => $registro[6],
                'direccion'         => mb_convert_encoding($registro[7],'UTF-8','ISO-8859-1'),
                'recibos'           => $registro[8],
                'email_alternativo' => $registro[9],
                'cod_admin'         => $cod_admin,
                'baja'              => 0,
                'codinm'            => $registro[10],
                'apto'              => $registro[11]];
        
        $r = $propietario->registrarPropietario($item);
        
        if ($r["suceed"] == FALSE) {
            echo "<b>Archivo Propietario: " . $archivo . ' - ' . $r['stats']['errno'] . "-" . $r['stats']['error'] . "</b>" . '<br/>' . $r['query'] . '<br/>';
        }
    }
}

$archivo = ACTUALIZ . $cod_admin . '_' . ARCHIVO_PROPIEDADES;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$propiedades = new propiedades();
echo "procesar archivo Propiedades (" . count($lineas) . ")<br />";
$mensaje .= "procesar archivo Propiedades (" . count($lineas) . ")<br />";
foreach ($lineas as $linea) {

    $registro = explode("\t", $linea);

    if ($registro[0] != "") {
        $registro = Array(
            'cedula' => $registro[0],
            'id_inmueble' => $registro[1],
            'apto' => $registro[2],
            'alicuota' => $registro[3],
            'meses_pendiente' => $registro[4],
            'deuda_total' => $registro[5],
            'deuda_usd' => str_replace("\r", "", $registro[6]),
            'cod_admin' => $cod_admin
        );
        $r = $propiedades->insertar($registro);
        if ($r["suceed"] == FALSE) {
            echo "<b>Archivo Propiedades: " . $r['stats']['errno'] . "-" . $r['stats']['error'] . "</b><br />" . '<br/>' . $r['query'] . '<br/>';
        }
    }
}

$archivo = ACTUALIZ . $cod_admin . '_' . ARCHIVO_FACTURA;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
$facturas = new factura();
echo "procesar archivo Facturas (" . count($lineas) . ")<br />";
$mensaje .= "procesar archivo Facturas (" . count($lineas) . ")<br />";
foreach ($lineas as $linea) {

    $registro = explode("\t", $linea);

    if ($registro[0] != "") {

        $registro = Array(
            'id_inmueble' => $registro[0],
            'apto' => $registro[1],
            'numero_factura' => $registro[2],
            'periodo' => $registro[3],
            'facturado' => $registro[4],
            'abonado' => $registro[5],
            'fecha' => $registro[6],
            'facturado_usd' => $registro[7],
            'cod_admin' => $cod_admin
        );

        $r = $facturas->insertar($registro);

        if (!$r["suceed"]) {
            echo($r['stats']['errno'] . "-" . $r['stats']['error'] . '<br/>' . $r['query'] . '<br/>');
        }
    }
}


$archivo = ACTUALIZ . $cod_admin . '_' . ARCHIVO_FACTURA_DETALLE;
if (file_exists($archivo)) {

    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo Detalle Factura (" . count($lineas) . ")<br />";
    $mensaje .= "procesar archivo Detalle Factura (" . count($lineas) . ")<br />";
    foreach ($lineas as $linea) {
    
        $registro = explode("\t", $linea);
    
    
        if ($registro[0] != "") {
    
            $registro = Array(
                "id_factura" => $registro[0],
                "detalle" => mb_convert_encoding($registro[1],'UTF-8','ISO-8859-1'),
                "codigo_gasto" => $registro[2],
                "comun" => $registro[3],
                "monto" => str_replace("\r", "", $registro[4]),
                "cod_admin" => $cod_admin
            );
    
            $r = $facturas->insertar_detalle_factura($registro);
    
            if ($r["suceed"] == FALSE) {
                echo($r['stats']['errno'] . "-" . $r['stats']['error'] . '<br/>' . $r['query'] . '<br/>');
            }
        }
    }
}

$archivo = ACTUALIZ . $cod_admin . '_' . ARCHIVO_MOVIMIENTO_CAJA;
if(file_exists($archivo)) {
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo movimiento caja (" . count($lineas) . ")<br />";
    $mensaje .= "procesar archivo movimiento caja (" . count($lineas) . ")<br />";
    $pago = new pago();
    foreach ($lineas as $linea) {

        $registro = explode("\t", $linea);

        if ($registro[0] != "") {

            $registro = Array(
                "numero_recibo" => $registro[0],
                "fecha_movimiento" => $registro[1],
                "forma_pago" => mb_convert_encoding($registro[2],'UTF-8','ISO-8859-1'),
                "monto" => $registro[3],
                "cuenta" => mb_convert_encoding($registro[4],'UTF-8'),
                "descripcion" => mb_convert_encoding($registro[5],'UTF-8','ISO-8859-1'),
                "id_inmueble" => $registro[6],
                "id_apto" => str_replace("\r", "", $registro[7]),
                "cod_admin" => $cod_admin
            );

            $r = $pago->insertarMovimientoCaja($registro);


            if ($r["suceed"] == FALSE) {
                echo($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }
}


$archivo = ACTUALIZ . $cod_admin . '_' . ARCHIVO_EDO_CTA_INM;
$contenidoFichero = JFile::read($archivo);
$lineas = explode("\r\n", $contenidoFichero);
echo "procesar archivo estado de cuenta inmueble (" . count($lineas) . ")<br />";
$mensaje .= "procesar archivo estado de cuenta inmueble (" . count($lineas) . ")<br />";
foreach ($lineas as $linea) {


    $registro = explode("\t", $linea);


    if ($registro[0] != "") {

        $registro = Array(
            "id_inmueble" => $registro[0],
            "apto" => $registro[1],
            "propietario" => mb_convert_encoding($registro[2],'UTF-8','ISO-8859-1'),
            "recibos" => $registro[3],
            "deuda" => $registro[4],
            "deuda_usd" => str_replace("\r", "", $registro[5]),
            "cod_admin" => $cod_admin
        );


        $r = $inmueble->insertarEstadoDeCuentaInmueble($registro);


        if ($r["suceed"] == FALSE) {
            echo($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
        }
    }
}

if (GRAFICO_FACTURACION == 1) {
    $archivo = ACTUALIZ . $cod_admin . '_' . "FACTURACION_MENSUAL.txt";
    if(file_exists($archivo)) {
        $contenidoFichero = JFile::read($archivo);
        $lineas = explode("\r\n", $contenidoFichero);
        echo "procesar archivo grafico facturacion mensual (" . count($lineas) . ")<br />";
        $mensaje .= "procesar archivo grafico facturación mensual (" . count($lineas) . ")<br />";
        foreach ($lineas as $linea) {
            $registro = explode("\t", $linea);

            if ($registro[0] != "") {

                $registro = Array(
                    "id_inmueble" => $registro[0],
                    "periodo" => $registro[1],
                    "facturado" => $registro[2],
                    "cod_admin" => $cod_admin
                );

                $r = $inmueble->insertarFacturacionMensual($registro);

                if ($r["suceed"] == FALSE) {
                    die($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
                }
            }
        }
    }
}

if (GRAFICO_COBRANZA == 1) {
    $archivo = ACTUALIZ . $cod_admin . '_' . "COBRANZA_MENSUAL.txt";
    if(file_exists($archivo)) {
        $contenidoFichero = JFile::read($archivo);
        $lineas = explode("\r\n", $contenidoFichero);
        echo "procesar archivo grafico cobranza mensual (" . count($lineas) . ")<br />";
        $mensaje .= "procesar archivo grafico cobranza mensual (" . count($lineas) . ")<br />";
        foreach ($lineas as $linea) {
            $registro = explode("\t", $linea);

            if ($registro[0] != "") {

                $registro = Array(
                    "id_inmueble" => $registro[0],
                    "periodo" => $registro[1],
                    "monto" => $registro[2],
                    "cod_admin" => $cod_admin
                );

                $r = $inmueble->insertarCobranzaMensual($registro);

                if ($r["suceed"] == FALSE) {
                    echo($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
                }
            }
        }
    }
}

if (MOVIMIENTO_FONDO == 1) {
    
    $archivo = ACTUALIZ . $cod_admin . '_' . ARCHIVO_CUENTAS_DE_FONDO;
    if(file_exists($archivo)) {
        $fondo = new fondo();
        $contenidoFichero = JFile::read($archivo);
        if ($contenidoFichero != '') {
            $lineas = explode("\r\n", $contenidoFichero);
            echo "procesar archivo cuentas de fondo (" . count($lineas) . ")<br/>";
            $mensaje .= "procesar archivo cuentas de fondo (" . count($lineas) . ")<br/>";
            foreach ($lineas as $linea) {
                $registro = explode("\t", $linea);
                if ($registro[0] != "") {
                    $registro = Array(
                        "id_inmueble" => $registro[0],
                        "codigo_gasto" => $registro[1],
                        "descripcion" => mb_convert_encoding($registro[2],'UTF-8','ISO-8859-1'),
                        "saldo" => $registro[3],
                        "mostrar" => 1,
                        "cod_admin" => $cod_admin
                    );
                    $r = $fondo->insertarRegistroFondo($registro, Array("saldo" => $registro["saldo"]));
                }
            }
            $archivo = ACTUALIZ . $cod_admin . '_' . ARCHIVO_MOVIMIENTOS_DE_FONDO;
            $contenidoFichero = JFile::read($archivo);
            if ($contenidoFichero != '') {
                $lineas = explode("\r\n", $contenidoFichero);
                echo "procesar archivo movimiento de fondo (" . count($lineas) . ")<br/>";
                $mensaje .= "procesar archivo movimiento de fondo (" . count($lineas) . ")<br/>";
                $id_inmueble = "";
                $codigo_gasto = "";
                foreach ($lineas as $l) {
                    $movimiento = explode("\t", $l);
                    if ($movimiento[0] != "") {

                        if ($id_inmueble != $movimiento[0] || $codigo_gasto != $movimiento[1]) {
                            $id_inmueble = $movimiento[0];
                            $codigo_gasto = $movimiento[1];
                            $r = $fondo->obtenerIdCuentaFondo($id_inmueble, $codigo_gasto, $cod_admin);
                            if ($r['suceed'] && count($r['data']) > 0) {
                                $id = $r['data'][0]['id'];
                            } else {
                                $id = 0;
                            }
                        }

                        if ($id > 0) {
                            $registro = Array(
                                "id_fondo" => $id,
                                "fecha" => $movimiento[2],
                                "tipo" => $movimiento[3],
                                "concepto" => mb_convert_encoding($movimiento[4],'UTF-8','ISO-8859-1'),
                                "debe" => $movimiento[5],
                                "haber" => $movimiento[6]
                            );

                            $r = $fondo->insertarMovimiento($registro);
                            if ($r["suceed"] == FALSE) {
                                echo($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
                            }
                        }
                    }
                }
            }
        }
    }
}

$archivo = ACTUALIZ . $cod_admin . '_' . "GESTIONES.txt";
if (file_exists($archivo)) {

    $gestion = new cobranza();
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    $mensaje .= "procesar archivo Gestiones de Cobranza (" . count($lineas) . ")<br />";
    echo "procesar archivo Gestiones de Cobranza (" . count($lineas) . ")<br />";
    foreach ($lineas as $linea) {
        $registro = explode("\t", $linea);
        if ($registro[0] != "") {
            $registro = Array(
                "id_inmueble" => $registro[0],
                "apto" => $registro[1],
                "telefono" => $registro[2],
                "contacto" => $registro[3],
                "resultado" => mb_convert_encoding($registro[4],'UTF-8','ISO-8859-1'),
                "fecha_hora" => $registro[5],
                "usuario" => $registro[6],
                "tipo" => $registro[7],
                "sincronizado" => 1,
                "cod_admin" => $cod_admin
            );
            $r = $gestion->insertar($registro);
            if ($r["suceed"] == FALSE) {
                echo($r['stats']['errno'] . "-" . $r['stats']['error'] . '<br/>' . $r['query'] . '<br/>');
            }
        }
    }
}

$archivo = ACTUALIZ . $cod_admin . '_' . "HISTORICO_AVISOS_COBRO.txt";
if (file_exists($archivo)) {
    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo historico de avisos(" . count($lineas) . ")<br />";
    $mensaje .= "procesar archivo historico de avisos(" . count($lineas) . ")<br />";
    foreach ($lineas as $linea) {
        $registro = explode("\t", $linea);
        if ($registro[0] != "") {
            $r = $db->insert("historico_avisos_cobro", Array(
                "id_inmueble"    => $registro[0],
                "apto"           => $registro[1],
                "numero_factura" => $registro[2],
                "cod_admin"      => $cod_admin,
                "periodo"        => $registro[3]), "IGNORE"
            );
            if ($r["suceed"] == FALSE) {
                echo($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }
}


$archivo = ACTUALIZ.$cod_admin.'_'."CANCELACION_GASTOS.txt";
if (file_exists($archivo)) {

    $contenidoFichero = JFile::read($archivo);
    $lineas = explode("\r\n", $contenidoFichero);
    echo "procesar archivo cancelacion de gastos (".count($lineas).")<br />";
    $mensaje.="procesar archivo cancelacion de gastos (".count($lineas).")<br />";
    $pago = new pago();
    foreach ($lineas as $linea) {

        $registro = explode("\t", $linea);

        if ($registro[0] != "") {

            $registro = [
                "cod_admin"        => $cod_admin,
                'fecha_movimiento' => $registro[0],
                'monto'            => $registro[1],
                'descripcion'      => mb_convert_encoding($registro[2],'UTF-8','ISO-8859-1'),
                'id_inmueble'      => $registro[3],
                'id_apto'          => $registro[4],
                'periodo'          => $registro[5],
                'numero_factura'   => str_replace("\r","",$registro[6]),            
            ];

            $r = $pago->insertarCancelacionDeGastos($registro);


            if ($r["suceed"] == FALSE) {
                echo($r['stats']['errno'] . "<br />" . $r['stats']['error'] . '<br/>' . $r['query']);
            }
        }
    }
}
$fecha = JFILE::read(ACTUALIZ . $cod_admin . "_ACTUALIZACION.txt");
$f_act = trim($fecha, " \t\n\r\0\x0B");
echo $f_act . '<br/>';
$f_act = Date('Y-m-d h:i:s', strtotime(str_replace('/', '-', $f_act)));
//$f_act = date_format($f_act, 'Y-m-d h:i:s');
//$administradoras->actualizar($administradora['id'], array('fecha_actualizacion'=>"'".$fecha."'"));
$s = $db->update("administradoras", array('fecha_actualizacion' => $f_act), array('id' => $administradora['id']));
echo "****FIN DEL PROCESO DE ACTUALIZACION****<br/>";
echo "Información actualizada al: " . $fecha . "<br/>";

$mail = new mailto(SMTP);

$mensaje = '<div style="background-color:rgb(227,242,253);padding:50px 0 50px;">'
        . '<div style="background-color:#fff; min-width:516px; max-width:516px;margin:0 auto; padding:40px 30px 50px 30px;">'
        . '<div style="background: linear-gradient(145deg,#0d47a1,#42a5f5);padding-left:10px">'
        . '<img alt="Tu Condominio en línea" src="' . ROOT . 'assets/images/_smarty/logo_dark.png" id="logo" width="240" height="96"></div>'
        . '<hr style="border: 0;border-top: 1px solid #eee;margin-bottom:20px;">'
        . '<div style="color:rgb(0,0,0);font-family:Helvetica;font-size:15px!important;font-weight:400;line-height:22px!important;color:#000000">' . $mensaje;
$mensaje .= '</div><div style="display: block;width: 60px;height: 2px;margin:10px 0;background-color:#2A72D4;position: relative;float:left"></div>'
        . '<div style="display: block;width: 60px;height: 2px;margin:10px 0;background-color:#1F41A3;position: relative;float:left"></div></div></div>';


$r = $mail->enviar_email(
        "Sincronización " . $_SERVER['SERVER_NAME'] . " " . $fecha, $mensaje, " [Valoriza2]", $administradora['email'], "", null);

if ($r == "") {
    echo "Email de confirmación enviado con éxito<br />";
} else {
    echo "Falló el envio del email de ejecución del proceso<br />";
}
echo "Cierre esta ventana para finalizar.";
