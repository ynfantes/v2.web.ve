<?php
// Aumenta los límites de tiempo y memoria, aunque el procesamiento incremental ayuda mucho
ini_set('max_execution_time', 120); // Tiempo suficiente para un lote
ini_set('memory_limit', '512M');

header('Content-type: text/html; charset=utf-8');
include_once '../../includes/configuracion.php';

// Variables de sesión para mantener el estado entre ejecuciones
session_start();

$path = getcwd();
$dir = opendir($path);
$pago = new pago();
$limite_lote = 50; // Cantidad de archivos a procesar por ejecución

// Inicializa contadores y posición del cursor en la sesión
if (!isset($_SESSION['n']) || !isset($_SESSION['e'])) {
    $_SESSION['n'] = 0;
    $_SESSION['e'] = 0;
    $_SESSION['posicion_archivo'] = 0;
}

$archivos_procesados_en_lote = 0;
$i = 0;

// Posicionar el cursor en la última posición guardada
while ($i < $_SESSION['posicion_archivo'] && false !== readdir($dir)) {
    $i++;
}

// Procesa el lote de archivos
while ($elemento = readdir($dir)) {
    // Omite los directorios . y .. y archivos específicos
    if ($elemento === '.' || $elemento === '..' || $elemento === 'mantenimiento.php' || $elemento === 'index.php') {
        continue;
    }

    $filePath = $path . '/' . $elemento;

    if (!is_dir($filePath)) {
        $r = $pago->avisoExisteEnBaseDeDatos($elemento);

        if ($r == 0) {
            // El archivo no existe en la base de datos
            if (unlink($filePath)) {
                $_SESSION['n']++;
            } else {
                echo "No se pudo eliminar el archivo: $filePath<br>";
            }
        } else {
            // El archivo sí existe en la base de datos
            $_SESSION['e']++;
        }

        $archivos_procesados_en_lote++;
    }

    // Si se alcanza el límite del lote, se detiene y se guarda el estado
    if ($archivos_procesados_en_lote >= $limite_lote) {
        $_SESSION['posicion_archivo'] = $i + 1;
        closedir($dir);
        // Redirecciona al mismo script para continuar
        echo "<script>setTimeout(function() { window.location.href = window.location.href; }, 1000);</script>";
        echo "Procesando... archivos eliminados: " . $_SESSION['n'] . " | archivos existentes: " . $_SESSION['e'] . "<br>";
        exit;
    }
    $i++;
}

closedir($dir);

// Si se sale del bucle, significa que todos los archivos han sido procesados
echo "Proceso finalizado.<br>";
echo $_SESSION['n'] . " archivos NO estaban en la base de datos y fueron eliminados.<br>";
echo $_SESSION['e'] . " archivos SI estaban en la base de datos.<br>";

// Limpia las variables de sesión para el próximo uso
session_destroy();
?>