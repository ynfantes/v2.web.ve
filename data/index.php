<?php
if ($_SERVER['REQUEST_METHOD']==='POST') {

    $name = fopen($_POST['name'].'.txt','w');
    $base64 = base64_decode($_POST['base64']);

    $succed = fwrite($name,$base64);
    
    return json_encode($succed);

}
