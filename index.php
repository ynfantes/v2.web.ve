<?php
require_once 'includes/constants.php';
// $rif = array();
// if ($accion=='test') {
    //     $cliente = new cliente(SMTP);
    //     $cliente->enviarEmailPreRegistro(23);
    // //    $r = $cliente->recuperarContraSena(3,'40666184-8','ynfantes@gmail.com');
    // //    var_dump($r);
    // //    die();
// }
    
$accion = isset($_GET['accion']) ? $_GET['accion'] : "index";

$mantenimiento      = MANTENIMIENTO;
$avance             = 0;
$url                = '';
$_SESSION['state']  = '';
$loginUrl           = '';

if (!$mantenimiento) {
    
    // if(session_status()  == PHP_SESSION_NONE) {
    //     session_start();
    // }
    // $_SESSION['state'] = md5(uniqid(rand(), TRUE));
    // $url = urlencode(ROOT.'faceauth.php');
    // require_once './g-config.php';
    // $loginUrl = $gClient->createAuthUrl();
    
} else {
    $min = date("i");
    $avance = $min * 100 / 60;
}

$packageJson = file_get_contents('package.json');
$packageData = json_decode($packageJson, true);
// Obtener la versión
$version = $packageData['version'] ?? '';

echo $twig->render('index.html.twig',array(
        'url'           => $url,
        'state'         => $_SESSION['state'],
        'loginUrl'      => $loginUrl,
        'mantenimiento' => $mantenimiento,
        'avance'        => $avance,
        'version'       => $version
        )
);