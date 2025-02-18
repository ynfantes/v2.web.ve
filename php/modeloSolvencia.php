<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);

$propiedad = new propiedades();
$inmueble = new inmueble();

session_start();
$usuario = $_SESSION['usuario'];

$propiedades = $propiedad->propiedadesPropietario($usuario['cedula'], $usuario['cod_admin']);

if($propiedades['suceed'] && count($propiedades['data'])>0) {

    $inmuebles = $inmueble->verDatosInmueble($propiedades['row']['id_inmueble'], $usuario['cod_admin']);
    
    if ($inmuebles['suceed'] && count($inmuebles['data'])>0) {    
        $data = $inmuebles['data'][0];
        $billing = $inmueble->getLastBilledPeriod($data['cod_admin'], $data['id']);

        if($billing['suceed'] && count($billing['data'])>0) {
            $lastBilledPeriod = $billing['data'][0]['periodo'];
        }

    } else {
        die('No se encuentra información del condominio');
    }
    
    $meses = [
        "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
    ];
    $fecha = new DateTime(); // Fecha actual
    $fecha->modify("+30 days"); // Sumar 30 días

    $dia  = date("d");
    $mes  = $meses[date("n") - 1];
    $anio = date("Y");    

    $diaV  = $fecha->format("d"); // Día del mes
    $mesV  = $meses[$fecha->format("n") - 1]; // Obtener nombre del mes
    $anioV = $fecha->format("Y"); // Año

    if ($lastBilledPeriod) {
        $dateF = new DateTime($lastBilledPeriod);
        $mesB  = $meses[$dateF->format("n") - 1];
        $anioB = $dateF->format("Y");

    } else {
        $fechaF = new DateTime();
        $fechaF->modify(("-1 month"));

        $mesB  = $meses[$fechaF->format("n") - 1]; // Obtener nombre del mes
        $anioB = $fechaF->format("Y"); // Año
    }
    $numero = rand(100, 999);

}
?>
<page backtop="20mm" backbottom="20mm" backleft="15mm" backright="15mm">
    <div style="text-align: center; font-size: 16pt; font-weight: bold;">
        <?php echo $data['nombre_inmueble']; ?>
    </div>
    <div style="text-align: center; font-size: 12pt;">
    <?php echo $data['RIF'] ?>
    </div>
    <br><br><br><br>
    <div style="text-align: center; font-size: 16pt; font-weight: bold;">
        CERTIFICADO DE SOLVENCIA N° <?php echo $numero; ?>
    </div>
    <br><br><br><br>
    <table style="width: 100%; font-size: 12pt; line-height: 1.5;">
        <tr>
            <td style="text-align: justify;">
                Por medio de la presente se hace constar que el Inmueble: <strong><?php echo $propiedades['row']['apto']; ?></strong> ubicado en: 
                <?php echo $data['nombre_inmueble']; ?>, propiedad de <strong><?php echo $usuario['nombre']; ?></strong>, titular de la C.I. / RIF N° 
                <strong><?php echo $usuario['cedula'] ?></strong>, a la fecha se encuentra<br><strong>SOLVENTE</strong>
                con todas las cuotas financieras hasta la emisión del Aviso de Cobro correspondiente al mes de <strong><?php echo "$mesB de $anioB" ?></strong>.
                Dicha solvencia tiene vigencia hasta el <strong><?php echo "$diaV de $mesV de $anioV"?>.</strong>
            </td>
        </tr>
    </table>
    <br><br>
    <div style="text-align: justify; font-size: 12pt; line-height: 1.5;">
        Constancia que se expide, a petición de la parte interesada, en la ciudad de <strong>Maracay</strong>, en fecha <strong><?php echo "$dia de $mes de $anio" ?></strong>.
    </div>
    <br><br><br><br>
    <div style="text-align: center; font-size: 12pt;">
        <strong>Por el <?php echo $data['nombre_inmueble'] ?></strong>
    </div>
    <br><br><br><br><br><br><br><br>
    <div style="text-align: center; font-size: 12pt;">
        __________________________
    </div>
    <page_footer>
    
        <hr>
        <div style="text-align: center; font-size: 10pt;">
            <strong>Dirección:</strong> <?php echo $data['direccion']; ?>
        </div>
        <div style="text-align: center; font-size: 10pt;">
            <strong>Teléfono:</strong> 0412-702.99.88
        </div>

    </page_footer>
</page>
