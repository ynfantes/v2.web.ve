<?php
require_once '../includes/constants.php';
require_once SERVER_ROOT.'/vendor/autoload.php';

use Spipu\Html2Pdf\Html2Pdf;

ob_start();
include(dirname(__FILE__).'/modeloSolvencia.php');
$html = ob_get_clean();

try {
    $html2pdf = new Html2Pdf();
    $html2pdf->writeHTML($html);
    $html2pdf->output('certificado_solvencia.pdf');
} catch (Exception $e) {
    echo 'Error generando el PDF: ' . $e->getMessage();
}
