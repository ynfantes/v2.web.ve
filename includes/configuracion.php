<?php
date_default_timezone_set("America/La_Paz");
define("mailPHP",0);
define("sendMail",1);
define("SMTP",2);

$debug = false;
$sistema = "/v2.web.ve/";
$email_error = false;
$mostrar_error = true;
$programa_correo = mailPHP;
$protocolo = 'http';
if ($_SERVER['SERVER_NAME'] == "www.v2.web.ve" | $_SERVER['SERVER_NAME'] == "www.va2.com.ve" | $_SERVER['SERVER_NAME'] == "www.v2.co.ve" | $_SERVER['SERVER_NAME'] == "v2.web.ve" | $_SERVER['SERVER_NAME'] == "va2.com.ve" | $_SERVER['SERVER_NAME'] == "v2.co.ve") {
    $user = "v2";
    $password = "valoriza25231";
    $db = "valoriza2_app";
    //$db = "octagon";
    $email_error = true;
    $mostrar_error = false;
    $debug = false;
    $sistema = "/";
    $programa_correo = SMTP;
    $protocolo = 'https';
} else {
    $user = "root";
    $password = "";
    $db = "valoriza2_app";
    //$db="adminish_sac";
}
define("HOST", "localhost");
define("USER", $user);
define("PASSWORD", $password);
define("DB", $db);
define("SISTEMA", $sistema);
define("EMAIL_ERROR", $email_error);
define("EMAIL_CONTACTO", "ynfantes@gmail.com");
define("EMAIL_TITULO", "error");
define("MOSTRAR_ERROR", $mostrar_error);
define("DEBUG", $debug);

define("TITULO", "Condominio en Línea v2");
/**
 * para las urls
 */
define("ROOT", $protocolo.'://'.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].SISTEMA);
define("URL_SISTEMA", ROOT . "enlinea");
define("URL_INTRANET", ROOT . "intranet");
/**
 * para los includes
 */
define("SERVER_ROOT", $_SERVER['DOCUMENT_ROOT'] . SISTEMA);

/*set_include_path(SERVER_ROOT . "/site/");*/
define("TEMPLATE", SERVER_ROOT . "/template/");
define("PROGRAMA_CORREO",$programa_correo);
define("NOMBRE_APLICACION","Condominio en Línea");
define("ACTUALIZ","data/");
define("ARCHIVO_INMUEBLE","INMUEBLE.txt");
define("ARCHIVO_CUENTAS","CUENTAS.txt");
define("ARCHIVO_FACTURA","FACTURA.txt");
define("ARCHIVO_FACTURA_DETALLE","FACTURA_DETALLE.txt");
define("ARCHIVO_JUNTA_CONDOMINIO","JUNTA_CONDOMINIO.txt");
define("ARCHIVO_PROPIEDADES","PROPIEDADES.txt");
define("ARCHIVO_PROPIETARIOS","PROPIETARIOS.txt");
define("ARCHIVO_EDO_CTA_INM","EDO_CUENTA_INMUEBLE.txt");
define("ARCHIVO_CUENTAS_DE_FONDO","CUENTAS_FONDO.txt");
define("ARCHIVO_MOVIMIENTOS_DE_FONDO","MOVIMIENTO_FONDO.txt");
define("ARCHIVO_ACTUALIZACION","ACTUALIZACION.txt");
define("ARCHIVO_MOVIMIENTO_CAJA","MOVIMIENTO_CAJA.txt");
define("SMTP_SERVER","mail.v2.web.ve");
define("PORT",587);
define("USER_MAIL","no-responder@v2.web.ve");
define("MAIL_CAJERO_WEB","intranet@grupoveneto.com");
define("PASS_MAIL","valoriza25231");
define("MESES_COBRANZA",1000);
define("GRAFICO_FACTURACION",1);
define("GRAFICO_COBRANZA",1);
define("DEMO",0);
define("MULTI_CUENTAS",0);
define("MOVIMIENTO_FONDO",1);
define("RECIBO_GENERAL",0);
define("GRUPOS",1);
