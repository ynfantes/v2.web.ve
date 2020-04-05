<?php
require_once './includes/constants.php';
require_once dirname(dirname(__FILE__)).'/vendor/autoload.php';
$gClient = new Google_Client();
$gClient->setClientId('1001343383317-dvh1fv5dumnod6rnba80mu4kkh7agejr.apps.googleusercontent.com');
$gClient->setClientSecret('7-wSdb5pKmyOGkxnUvuyarH2');
$gClient->setApplicationName('Condominio en Línea v2');
$gClient->setRedirectUri(ROOT.'g-callback.php');
$gClient->addScope('https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email');
