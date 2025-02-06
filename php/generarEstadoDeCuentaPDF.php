<?php
$archivo = '';
ob_start();

include(dirname(__FILE__).'/estadoDeCuentaInmueble.php');
$content = ob_get_clean();
//die($content);
require_once('../includes/html2pdf/html2pdf.class.php');

try
{
    $html2pdf = new HTML2PDF('P', 'Letter', 'es',true,'UTF-8',[20, 10, 20, 10]);
    //$html2pdf->setModeDebug();
    $html2pdf->pdf->SetDisplayMode('fullpage');
    $html2pdf->setDefaultFont('arial');
    $html2pdf->writeHTML($content);
    
    if (isset($_GET['email'])) {
        $archivo = $html2pdf->Output('','S');
        
        $mail = new mailto(SMTP);
        
        $reporte =["Estado_de_cuenta.pdf"=>$archivo];
        
        $r = $mail->enviar_email(
            "Estado de Cuenta ".$inm['data'][0]['nombre_inmueble'], 
            "Adunto le estoy enviando el estado de cuenta general del inmueble.<br><br>".$session['usuario']['nombre_completo'], 
            '', 
            MAIL_CAJERO_WEB, 
            "",
            null,
            null,
            $reporte
        );
        $archivo='';
        //if ($r=='') {
        echo $r;
        //} else {
        //    echo 'envio fallido';
        //}
    } else {
         $html2pdf->Output('Estado_de_cuenta_inmueble.pdf');
    }
}

catch(HTML2PDF_exception $e) {
    echo $e;
    exit;
}