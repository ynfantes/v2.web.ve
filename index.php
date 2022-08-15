<?php
require_once 'includes/constants.php';
$rif = array();
$accion = isset($_GET['accion']) ? $_GET['accion'] : "index";
if ($accion=='test') {
    $cliente = new cliente(SMTP);
    $cliente->enviarEmailPreRegistro(23);
//    $r = $cliente->recuperarContraSena(3,'40666184-8','ynfantes@gmail.com');
//    var_dump($r);
//    die();
}

$mantenimiento      = MANTENIMIENTO;
$avance             = 0;
$url                = '';
$_SESSION['state']  = '';
$loginUrl           = '';

if (!$mantenimiento) {
    
    if(session_status()  == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['state'] = md5(uniqid(rand(), TRUE));
    $url = urlencode(ROOT.'faceauth.php');
    require_once './g-config.php';
    $loginUrl = $gClient->createAuthUrl();
    
} else {
    $min = date("i");
    $avance = $min * 100 / 60;
}



echo $twig->render('index.html.twig',array(
        'url'           => $url,
        'state'         => $_SESSION['state'],
        'loginUrl'      => $loginUrl,
        'mantenimiento' => $mantenimiento,
        'avance'        => $avance
        )
);