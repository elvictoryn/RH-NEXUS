<?php
/**
 * Archivo principal para InfinityFree
 * Redirige automáticamente al sistema RH que está en /sistema_rh/
 */

// Detectar si estamos en InfinityFree
$isInfinityFree = isset($_SERVER['HTTP_HOST']) && 
                  (strpos($_SERVER['HTTP_HOST'], 'epizy.com') !== false || 
                   strpos($_SERVER['HTTP_HOST'], 'infinityfree.net') !== false);

if ($isInfinityFree) {
    // Cargar configuración de entorno
    require_once __DIR__ . '/config/environment.php';
    
    // En InfinityFree, redirigir al sistema manteniendo la estructura original
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://' . $_SERVER['HTTP_HOST'] . '/sistema_rh';
    
    // Si es la raíz, redirigir a sistema_rh/public/
    if ($requestUri === '/' || $requestUri === '') {
        header('Location: ' . $baseUrl . '/public/');
        exit;
    }
    
    // Si no es la raíz, redirigir manteniendo la ruta
    header('Location: ' . $baseUrl . '/public' . $requestUri);
    exit;
} else {
    // En desarrollo local, mostrar mensaje
    echo '<h1>Sistema RH Nexus</h1>';
    echo '<p>Para acceder al sistema, ve a: <a href="/sistema_rh/public/">/sistema_rh/public/</a></p>';
    echo '<p>O configura tu servidor local para apuntar directamente a la carpeta public/</p>';
}
?>
