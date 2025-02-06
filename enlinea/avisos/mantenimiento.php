<?php
ini_set('max_execution_time', 300); // 5 minutos
ini_set('memory_limit', '512M'); // 512MB de memoria

header('Content-type: text/html; charset=utf-8');
include_once '../../includes/constants.php';

$path = getcwd();
$dir = opendir($path);
$factura = new factura();
$e = 0;
$n = 0;

// Aumentar tiempo de ejecución y memoria
ini_set('max_execution_time', 300); // 5 minutos
ini_set('memory_limit', '512M'); // 512MB de memoria

// Procesar archivos
while ($elemento = readdir($dir)) {
    // Ignorar elementos especiales y ciertos archivos
    if ($elemento == "." || $elemento == ".." || $elemento == "mantenimiento.php" || $elemento == "index.php") {
        continue;
    }

    // Si es un archivo
    $filePath = $path . '/' . $elemento;
    if (!is_dir($filePath)) {
        $r = $factura->avisoExisteEnBaseDeDatos($elemento);
        
        if ($r == 0) {
            if (unlink($filePath)) {
                $n++;
            } else {
                // Manejo de errores al eliminar
                echo "No se pudo eliminar el archivo: $filePath<br>";
            }
        } else {
            $e++;
        }
    }

    // Liberar memoria
    unset($elemento, $filePath);
}

closedir($dir);

echo "$n archivos NO están en la base de datos.<br>";
echo "$e archivos SI están en la base de datos";