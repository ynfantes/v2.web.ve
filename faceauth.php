<?php 
require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';
//require_once dirname(dirname(__FILE__)).'\vendor\facebook\graph-sdk\src\Facebook\autoload.php';
require_once './includes/constants.php';

$app_id = "814631822351325"; //change this
$app_secret = "f740e1e921b29f910b40a0bd4bc0b75c"; //change this
$redirect_url = ROOT."faceauth.php"; //change this

$code = $_REQUEST["code"];
if(session_status()  == PHP_SESSION_NONE) {
    session_start();
}

if(empty($code))
{
    header( 'Location: '.ROOT ) ; //change this
    exit(0);
}

$access_token_details = getAccessTokenDetails($app_id,$app_secret,$redirect_url,$code);

if($access_token_details == null)
{
    echo "No se obtiene el token de acceso";
    exit(0);
}   

if($_SESSION['state'] == null || ($_SESSION['state'] != $_REQUEST['state'])) 
{
    die("May be CSRF attack");
}

$_SESSION['access_token'] = $access_token_details['access_token']; //save token is session 

$fb = new \Facebook\Facebook([
  'app_id'              => $app_id,           //Replace {your-app-id} with your app ID
  'app_secret'          => $app_secret,   //Replace {your-app-secret} with your app secret
  'graph_api_version'   => 'v6.0',
]);

try {
   
    // Get your UserNode object, replace {access-token} with your token
    $response = $fb->get('/me?fields=name,email,first_name,last_name,picture',
    $_SESSION['access_token']);

} catch(\Facebook\Exceptions\FacebookResponseException $e) {
        // Returns Graph API errors when they occur
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch(\Facebook\Exceptions\FacebookSDKException $e) {
    // Returns SDK errors when validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}
$me = $response->getGraphUser();
//All that is returned in the response
//echo 'All the data returned from the Facebook server:'.$me;
//echo '<br>';
////Print out my name
//echo 'Mi nombre es '.$me->getName();
//echo '<br>';
$picture    = $me->getPicture();
//var_dump($picture);
//echo $picture['url'];
//die();
$email      = $me->getEmail();
$id         = $me->getId();
$propietarios = new propietario();
$password = '';
$r = $propietarios->emailRegistrado($email);
if ($r['suceed'] && count($r['data'])>0) {
    $password = $r['data'][0]['clave'];
    $mensaje = $r['error'];
} else {
    $mensaje = 'El correo electrónico asociado a su cuenta de Facebook no '
            . 'coincide con el correo principal de su cuenta de condominio.';
}
$r = $propietarios->login($email, $password);
if($r) {
    if ($_SESSION['usuario']['id_facebook']==NULL || $_SESSION['usuario']['id_facebook']=='') {
        $propietarios->actualizar($_SESSION['usuario']['id'], array('id_facebook'=>$id));
        $_SESSION['usuario']['id_facebook'] = $id;
    }
    $_SESSION['picture'] = $picture['url'];
    header("location:".URL_SISTEMA.'#inmueble/?accion=cartelera');
    exit(0);
} else {
    if(session_status()  == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['mensaje']=$mensaje;
    header("location:".ROOT);
    //$_SESSION['state'] = md5(uniqid(rand(), TRUE));
    //$url = urlencode(ROOT.'faceauth.php');
    //echo $twig->render('index.html.twig',Array(
    //    'mensaje'=>$mensaje,
    //    'url'   => $url,
    //    'state' => $_SESSION['state']));
}

function getAccessTokenDetails($app_id,$app_secret,$redirect_url,$code)
{

	$token_url = "https://graph.facebook.com/oauth/access_token?client_id="
                .$app_id."&redirect_uri=".urlencode($redirect_url)."&client_secret="
                .$app_secret."&code=".$code;
        $response = file_get_contents($token_url);
        $params = null;
        $params = json_decode($response,true);
        return $params;

}
