<?php
// Archivo de prueba para verificar que PHP funciona
echo "PHP está funcionando correctamente<br>";
echo "Versión de PHP: " . phpversion() . "<br>";
echo "Fecha actual: " . date('Y-m-d H:i:s') . "<br>";

// Verificar configuración de entorno
echo "<br>=== CONFIGURACIÓN DE ENTORNO ===<br>";
require_once __DIR__ . '/../config/environment.php';
echo "ENVIRONMENT: " . (defined('ENVIRONMENT') ? ENVIRONMENT : 'NO DEFINIDO') . "<br>";
echo "BASE_PATH: " . (defined('BASE_PATH') ? BASE_PATH : 'NO DEFINIDO') . "<br>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NO DEFINIDO') . "<br>";

// Probar conexión a base de datos con más detalles
echo "<br>=== PRUEBA DE CONEXIÓN A BASE DE DATOS ===<br>";
try {
    require_once __DIR__ . '/../config/conexion.php';
    
    // Mostrar credenciales que se están usando
    if (ENVIRONMENT === 'production') {
        echo "Usando credenciales de PRODUCCIÓN:<br>";
        echo "- Host: sql306.infinityfree.com<br>";
        echo "- DB: if0_40170523_sistema_rh<br>";
        echo "- User: if0_40170523<br>";
    } else {
        echo "Usando credenciales de DESARROLLO:<br>";
        echo "- Host: 127.0.0.1<br>";
        echo "- DB: sistema_rh<br>";
        echo "- User: root<br>";
    }
    
    $db = Conexion::getConexion();
    echo "✅ Conexión a base de datos: EXITOSA<br>";
    
    // Probar una consulta simple
    $stmt = $db->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "✅ Consulta de prueba: " . $result['test'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ Error de conexión a base de datos: " . $e->getMessage() . "<br>";
    echo "Detalles del error: " . $e->getTraceAsString() . "<br>";
}

// Mostrar información del servidor
echo "<br>=== INFORMACIÓN DEL SERVIDOR ===<br>";
echo "Servidor: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NO DEFINIDO') . "<br>";
?>
