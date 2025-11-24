<?php
// Aumenta límites
ini_set('max_execution_time', 120);
ini_set('memory_limit', '512M');

header('Content-type: text/html; charset=utf-8');
include_once '../includes/constants.php';

session_start();

// Inicializar variables de sesión si es la primera ejecución
if (!isset($_SESSION['batch_initialized'])) {

    $_SESSION['batch_initialized'] = true;
    $_SESSION['found']     = 0;
    $_SESSION['notFound']  = 0;
    $_SESSION['pointer']   = 0;   // puntero del lote actual
    $_SESSION['done']      = false;

    $path = getcwd();
    $exclude = ['.', '..','index.php','mantenimiento.php'];

    // 1. Obtener todos los archivos del directorio
    $files = scandir($path);

    // 2. Filtrar archivos válidos
    $files = array_values(array_filter($files, function($f) use ($exclude) {
        return !in_array($f, $exclude, true);
    }));

    // Guardamos lista completa en sesión
    $_SESSION['all_files'] = $files;
    $_SESSION['total']     = count($files);
}

$limite_lote = 50;      // cantidad de archivos por lote
$pago = new pago();


// Si ya se terminó todo, mostrar resumen y finalizar
if ($_SESSION['done'] === true) {
    echo "Proceso finalizado.<br>";
    echo $_SESSION['notFound'] . " archivos NO estaban en la base de datos y fueron eliminados.<br>";
    echo $_SESSION['found'] . " archivos SI estaban en la base de datos.<br>";
    session_destroy();
    exit;
}


// Lista completa ya está cargada en sesión
$files      = $_SESSION['all_files'];
$total       = $_SESSION['total'];
$pointer     = $_SESSION['pointer'];

// Calcular límite de lote
$lote = array_slice($files, $pointer, $limite_lote);

// Si el lote está vacío, significa que ya terminamos TODOS
if (empty($lote)) {
    $_SESSION['done'] = true;
    echo "<script> setTimeout(function(){ window.location.reload(); }, 1000); </script>";
    exit;
}

// Procesar lote actual
foreach ($lote as $file) {

    // Verificar en BD
    $existe = $pago->cancelacionExisteEnBaseDeDatos($file);

    if ($existe) {
        $_SESSION['found']++;
    } else {
        unlink($file);
        $_SESSION['notFound']++;
    }
}

// Avanzar puntero al siguiente lote
$_SESSION['pointer'] += $limite_lote;


// Mostrar avance
$procesado = min($_SESSION['pointer'], $total);
$porcentaje = round(($procesado / $total) * 100, 2);

echo "Procesando... $procesado de $total archivos ($porcentaje%)<br>";


// Forzar actualización de pantalla con tu lógica Javascript
echo "<script> setTimeout(function(){ window.location.reload(); }, 500); </script>";
exit;

?>
