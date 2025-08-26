<?php
/**
 * Configuración de Entorno - RH-NEXUS (Multi-Rama)
 * 
 * Este archivo permite configurar fácilmente el proyecto para diferentes entornos
 * y ramas del mismo proyecto usando subcarpetas
 * Solo necesitas cambiar las variables según tu configuración
 */

// ============================================================================
// CONFIGURACIÓN DEL ENTORNO - CAMBIAR SEGÚN TU CONFIGURACIÓN
// ============================================================================

// Nombre de la carpeta del proyecto en el servidor web
// Ejemplos: 'RH-NEXUS/produccion', 'RH-NEXUS/desarrollo', 'sistema_rh', etc.
//define('PROJECT_FOLDER', 'RH-NEXUS');                    // Rama principal
//define('PROJECT_FOLDER', 'RH-NEXUS/produccion');         // Rama de producción
define('PROJECT_FOLDER', 'RH-NEXUS/bryan');          // Rama de desarrollo


// Configuración de la base de datos
define('DB_HOST', 'localhost');
//define('DB_NAME', 'sistema_rh');
define('DB_NAME', 'rh_b');

define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración del servidor (opcional)
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');

// Configuración de desarrollo (opcional)
define('DEBUG_MODE', true);
define('SHOW_ERRORS', true);

// Configuración de rutas de archivos (opcional)
define('UPLOADS_FOLDER', 'app/public/uploads');
define('LOGS_FOLDER', 'logs');

// ============================================================================
// VALIDACIONES DE CONFIGURACIÓN
// ============================================================================

// Validar que las constantes estén definidas
if (!defined('PROJECT_FOLDER') || !defined('DB_HOST') || !defined('DB_NAME')) {
    die('Error: Configuración incompleta en environment.php');
}

// Mostrar errores en desarrollo
if (DEBUG_MODE && SHOW_ERRORS) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// ============================================================================
// FUNCIONES DE UTILIDAD PARA EL ENTORNO
// ============================================================================

/**
 * Obtener la configuración del entorno
 */
function getEnvironmentConfig() {
    return [
        'project_folder' => PROJECT_FOLDER,
        'db_host' => DB_HOST,
        'db_name' => DB_NAME,
        'db_user' => DB_USER,
        'db_pass' => DB_PASS,
        'debug_mode' => DEBUG_MODE,
        'show_errors' => SHOW_ERRORS
    ];
}

/**
 * Verificar si estamos en modo desarrollo
 */
function isDevelopment() {
    return DEBUG_MODE;
}

/**
 * Obtener la URL base del proyecto
 */
function getProjectUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . $host . '/' . PROJECT_FOLDER;
}

/**
 * Verificar la conexión a la base de datos
 */
function testDatabaseConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
            DB_USER,
            DB_PASS
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return ['success' => true, 'message' => 'Conexión exitosa'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()];
    }
}

/**
 * Obtener información del entorno para debugging
 */
function getEnvironmentInfo() {
    return [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        'project_folder' => PROJECT_FOLDER,
        'database' => DB_NAME,
        'debug_mode' => DEBUG_MODE
    ];
}

// ============================================================================
// INFORMACIÓN DE CONFIGURACIÓN
// ============================================================================

if (DEBUG_MODE) {
    // Log de configuración en desarrollo
    error_log("RH-NEXUS Multi-Rama Environment Loaded: " . PROJECT_FOLDER);
}
?> 