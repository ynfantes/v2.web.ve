<?php
include_once 'configuracion.php';


require_once SERVER_ROOT.'/vendor/autoload.php';
include_once SERVER_ROOT.'includes/extensiones.php';

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;

$loader = new FilesystemLoader(SERVER_ROOT.'template');

$options = [
    'debug'         => true,
    'cache'         => false,
    "auto_reload"   => true
];
$twig = new Environment($loader, $options);
$twig->addExtension(new DebugExtension());
$twig->addExtension(new Extensiones());

if (isset($_SESSION)){
    $twig->addGlobal("session", $_SESSION);
}

spl_autoload_register( function($class) {
    include_once SERVER_ROOT.'/includes/'.$class.'.php';
});

if (isset($_GET['logout']) && $_GET['logout'] == true) {
    $user_logout = new propietario();
    $user_logout->logout();
}

//https://www.google.com/settings/u/1/security/lesssecureapps
//https://accounts.google.com/DisplayUnlockCaptcha
//https://security.google.com/settings/security/activity?hl=en&pli=1