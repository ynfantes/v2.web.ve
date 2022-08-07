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
$userData   = $oAuth->userinfo_v2_me->get();
$email      = $userData['email'];
$id         = $userData['id'];
$hash       = '';
$propietarios = new propietario();
$r = $propietarios->emailRegistrado($email);

if ($r['suceed'] && count($r['data'])>0) {
    
    $password = $r['data'][0]['clave'];
    $result['existe']   = TRUE;
    $result['id']       = base64_encode($r['data'][0]['id']);
    if(session_status()  == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['id']     = $result['id'];
    $r = $propietarios->login($email, $password);

    if($r['suceed'] && $hash=='') {
        if ($_SESSION['usuario']['id_google']==NULL || $_SESSION['usuario']['id_google']=='') {
            $propietarios->actualizar($_SESSION['usuario']['id'], array('id_google'=>$id));
            $_SESSION['usuario']['id_google'] = $id;
        }
        $_SESSION['picture'] = $userData['picture'];
        header("location:".URL_SISTEMA.'#inmueble/?accion=cartelera');
        exit(0);
    }
    
} else {
    
    $hash = '#alert_gmail';
    header('Location:'.ROOT.$hash);
    
}