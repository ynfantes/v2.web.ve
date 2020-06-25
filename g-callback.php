<?php
require_once './g-config.php';
require_once './includes/constants.php';
if (isset($_SESSION['access_token'])) {
    $gClient->setAccessToken($_SESSION['access_token']);
} else if (isset($_GET['code'])) {
    $token = $gClient->fetchAccessTokenWithAuthCode($_GET['code']);
    $_SESSION['access_token'] = $token;
} else {
    header('Location:'.ROOT);
    exit(0);
}
//if (!isset($token['access_token'])) {
//    die($token['error_description']);
//    exit(0);
//}
$oAuth = new Google_Service_Oauth2($gClient);
$userData = $oAuth->userinfo_v2_me->get();
$email = $userData['email'];
$id    = $userData['id'];
$propietarios = new propietario();
$r = $propietarios->emailRegistrado($email);
if ($r['suceed'] && count($r['data'])>0) {
    $password = $r['data'][0]['clave'];
    //$mensaje = $r['error'];
} else {
    $mensaje = 'El correo electrónico asociado a su cuenta de Gmail no '
            . 'coincide con el correo principal de su cuenta de condominio.';
}
$r = $propietarios->login($email, $password);

if($r) {
    if ($_SESSION['usuario']['id_google']==NULL || $_SESSION['usuario']['id_google']=='') {
        $propietarios->actualizar($_SESSION['usuario']['id'], array('id_google'=>$id));
        $_SESSION['usuario']['id_google'] = $id;
    }
    $_SESSION['picture'] = $userData['picture'];
    header("location:".URL_SISTEMA.'#inmueble/?accion=cartelera');
    exit(0);
} else {
    if(session_status()  == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['mensaje'] = $mensaje;
    header('Location:'.ROOT);
//    $_SESSION['state'] = md5(uniqid(rand(), TRUE));
//    $url = urlencode(ROOT.'faceauth.php');
//    echo $twig->render('index.html.twig',Array(
//        'mensaje'   => $mensaje,
//        'url'       => $url,
//        'state'     => $_SESSION['state']));
}
    
