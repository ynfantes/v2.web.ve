<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once '../includes/constants.php';
require_once SERVER_ROOT.'/vendor/autoload.php';

$archivo='';
ob_start();

include(dirname(__FILE__).'/estadoDeCuentaInmueble.php');
$content = ob_get_clean();

use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;

try {
    $html2pdf = new Html2Pdf('P','letter','es',true,'UTF-8',array(10, 10, 10, 8),false);
    $html2pdf->writeHTML($content);
    $html2pdf->output('EstadoDeCuentaInmueble.pdf');
} catch (Html2PdfException $e) {
    // Captura errores específicos de HTML2PDF
    echo 'Error en HTML2PDF: ' . $e->getMessage();
} catch (Exception $e) {
    // Captura errores generales de PHP
    echo 'Error general: ' . $e->getMessage();
}