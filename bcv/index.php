<?php
include_once '../includes/constants.php';
$action = isset($_GET['action']) ? $_GET['action']:'';
//header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Headers: 'X-Requested-With,content-type'");
header('Content-Type: application/json; charset=UTF-8;');



function file_get_contents_curl($url){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      $data = curl_exec($ch);
      curl_close($ch);
      return $data;
}
function limpiarString($String){ 
     $String = str_replace(array("|","|","[","^","´","`","¨","~","]","'","#","{","}",".","","\t","\n"," ", "Bs/USD","USD"),"",$String);
     return $String;
}

function obtenerTasaDeCambioDelDiaBCV() {
    $tasa = [];
    $url = 'https://www.bcv.org.ve';
    if(($html   = file_get_contents_curl($url)) == false) {
        //Convierte la información de la URL en cadena
        $error = error_get_last();
        $tasa['error'] = "HTTP request failed. Error was: " . $error['message'];
    }
    $doc    = new DOMDocument();
    @$doc->loadHTML($html);
    //$nodes = $doc->querySelector('#dolar');
    $nodes = $doc->getElementById('dolar');
    $lectura = rtrim(ltrim($nodes->nodeValue));
    $lectura = explode(" ", limpiarString($lectura));
    $tasa = [
        'dolar'   => $lectura[0],
        'price'   => number_format( str_replace(",",".",$lectura[0]), 2, ',', '.' ),
        'unit'    => 'Bs/USD',
        'percent' => '0,00%',
        'symbol'  => '▲',
        'change'  => '0,00'
    ];
    //▼ ▶
    return $tasa;
}

switch ($action) {

    case 'rateOfDay':
        $historico = new historicoTasaBDV();
        $result = $historico->getPriceDollar();
        echo json_encode($result);
        break;

    case 'saveHistory':
            
        $tasa = obtenerTasaDeCambioDelDiaBCV();
       
        $historico = new historicoTasaBDV();
        $result = [];
   
        if (!isset($tasa['error'])) {
            $precio = str_replace(",",".",$tasa['price']);
            
            if (is_numeric($precio)) {
               
                $result = $historico->recordPriceDoolar(['precio' => $precio]);
                
            } else {
                $result['error'] = 'No se pudo obtener la tasa de cambio del BCV';
            }
            
        } else {
            $result = $tasa;
        }
        echo json_encode($result);

        break;

    default:
        $tasa = obtenerTasaDeCambioDelDiaBCV();
        echo json_encode($tasa);
}