<?php
/**
 * Configuración de rutas dinámicas para el proyecto RH-NEXUS
 * Este archivo define todas las rutas base del proyecto para evitar problemas
 * con rutas relativas cuando los archivos son incluidos desde diferentes contextos
 */

// ============================================================================
// CONFIGURACIÓN DEL PROYECTO - CARGAR DESDE ARCHIVO DE ENTORNO
// ============================================================================

// Cargar configuración del entorno
if (!defined('PROJECT_FOLDER')) {
    require_once __DIR__ . '/environment.php';
}

// ============================================================================
// DEFINICIÓN DE RUTAS BASE
// ============================================================================

// Definir la ruta raíz del proyecto
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}

// Definir rutas principales del proyecto
if (!defined('APP_PATH')) {
    define('APP_PATH', ROOT_PATH . '/app');
}

if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', ROOT_PATH . '/config');
}

if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', ROOT_PATH . '/public');
}

if (!defined('VIEWS_PATH')) {
    define('VIEWS_PATH', APP_PATH . '/views');
}

if (!defined('MODELS_PATH')) {
    define('MODELS_PATH', APP_PATH . '/models');
}

if (!defined('CONTROLLERS_PATH')) {
    define('CONTROLLERS_PATH', APP_PATH . '/controllers');
}

if (!defined('INCLUDES_PATH')) {
    define('INCLUDES_PATH', APP_PATH . '/includes');
}

if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', APP_PATH . '/public/uploads');
}

/**
 * Clase para manejar rutas dinámicamente
 */
class PathHelper {
    
    /**
     * Obtener ruta absoluta a un modelo
     */
    public static function model($modelName) {
        // Mantener el nombre original sin modificar case
        if (!str_ends_with($modelName, '.php')) {
            $modelName .= '.php';
        }
        
        $path = MODELS_PATH . '/' . $modelName;
        
        // Si el archivo no existe, intentar con diferentes variaciones de case
        if (!file_exists($path)) {
            $baseName = pathinfo($modelName, PATHINFO_FILENAME);
            $extension = pathinfo($modelName, PATHINFO_EXTENSION);
            
            // Intentar con diferentes variaciones
            $variations = [
                strtolower($baseName) . '.' . $extension,  // todo minúscula
                ucfirst(strtolower($baseName)) . '.' . $extension,  // primera mayúscula
                strtoupper($baseName) . '.' . $extension,  // todo mayúscula
                ucwords(strtolower($baseName)) . '.' . $extension,  // cada palabra mayúscula
            ];
            
            foreach ($variations as $variation) {
                $testPath = MODELS_PATH . '/' . $variation;
                if (file_exists($testPath)) {
                    return $testPath;
                }
            }
        }
        
        return $path;
    }
    
    /**
     * Obtener ruta absoluta a una vista
     */
    public static function view($viewPath) {
        if (!str_ends_with($viewPath, '.php')) {
            $viewPath .= '.php';
        }
        return VIEWS_PATH . '/' . $viewPath;
    }
    
    /**
     * Obtener ruta absoluta a un controlador
     */
    public static function controller($controllerName) {
        if (!str_ends_with($controllerName, '.php')) {
            $controllerName .= '.php';
        }
        return CONTROLLERS_PATH . '/' . $controllerName;
    }
    
    /**
     * Obtener ruta absoluta al archivo de configuración
     */
    public static function config($configFile = 'conexion.php') {
        return CONFIG_PATH . '/' . $configFile;
    }
    
    /**
     * Obtener ruta absoluta a archivos de includes
     */
    public static function includes($fileName) {
        if (!str_ends_with($fileName, '.php')) {
            $fileName .= '.php';
        }
        return INCLUDES_PATH . '/' . $fileName;
    }
    
    /**
     * Obtener ruta absoluta a archivos públicos
     */
    public static function public_file($fileName) {
        return PUBLIC_PATH . '/' . $fileName;
    }
    
    /**
     * Obtener ruta absoluta al directorio de uploads
     */
    public static function uploads($fileName = '') {
        if ($fileName) {
            return UPLOADS_PATH . '/' . $fileName;
        }
        return UPLOADS_PATH;
    }
    
    /**
     * Obtener ruta absoluta al header compartido
     */
    public static function shared_header() {
        return VIEWS_PATH . '/shared/header.php';
    }
    
    /**
     * Obtener URL base del proyecto
     */
    public static function base_url($path = '') {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $project_folder = '/' . PROJECT_FOLDER; // Usar la constante definida
        
        return $protocol . $host . $project_folder . ($path ? '/' . ltrim($path, '/') : '');
    }
    
    /**
     * Verificar si un archivo existe en la ruta especificada
     */
    public static function file_exists($path) {
        return file_exists($path);
    }
    
    /**
     * Incluir un archivo de forma segura
     */
    public static function safe_include($path) {
        if (self::file_exists($path)) {
            return include $path;
        } else {
            throw new Exception("Archivo no encontrado: " . $path);
        }
    }
    
    /**
     * Incluir un archivo de forma segura (solo una vez)
     */
    public static function safe_include_once($path) {
        if (self::file_exists($path)) {
            return include_once $path;
        } else {
            throw new Exception("Archivo no encontrado: " . $path);
        }
    }
    
    /**
     * Requerir un archivo de forma segura
     */
    public static function safe_require($path) {
        if (self::file_exists($path)) {
            return require $path;
        } else {
            throw new Exception("Archivo requerido no encontrado: " . $path);
        }
    }
    
    /**
     * Requerir un archivo de forma segura (solo una vez)
     */
    public static function safe_require_once($path) {
        if (self::file_exists($path)) {
            return require_once $path;
        } else {
            // Buscar archivos similares en el directorio
            $directory = dirname($path);
            $filename = basename($path);
            $baseName = pathinfo($filename, PATHINFO_FILENAME);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            
            $similarFiles = [];
            if (is_dir($directory)) {
                $files = scandir($directory);
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..' && 
                        strtolower(pathinfo($file, PATHINFO_FILENAME)) === strtolower($baseName)) {
                        $similarFiles[] = $file;
                    }
                }
            }
            
            $errorMsg = "Archivo requerido no encontrado: " . $path;
            if (!empty($similarFiles)) {
                $errorMsg .= "\nArchivos similares encontrados: " . implode(', ', $similarFiles);
            }
            
            throw new Exception($errorMsg);
        }
    }
}

/**
 * Funciones auxiliares globales para rutas (shortcuts)
 */

function model_path($modelName) {
    $path = PathHelper::model($modelName);
    
    // Si el archivo no existe, intentar con diferentes variaciones
    if (!file_exists($path)) {
        $baseName = pathinfo($modelName, PATHINFO_FILENAME);
        $extension = pathinfo($modelName, PATHINFO_EXTENSION);
        
        // Intentar con diferentes variaciones de case
        $variations = [
            strtolower($baseName) . '.' . $extension,  // todo minúscula
            ucfirst(strtolower($baseName)) . '.' . $extension,  // primera mayúscula
            strtoupper($baseName) . '.' . $extension,  // todo mayúscula
            ucwords(strtolower($baseName)) . '.' . $extension,  // cada palabra mayúscula
        ];
        
        foreach ($variations as $variation) {
            $testPath = MODELS_PATH . '/' . $variation;
            if (file_exists($testPath)) {
                return $testPath;
            }
        }
    }
    
    return $path;
}

function view_path($viewPath) {
    return PathHelper::view($viewPath);
}

function controller_path($controllerName) {
    return PathHelper::controller($controllerName);
}

function config_path($configFile = 'conexion.php') {
    return PathHelper::config($configFile);
}

function includes_path($fileName) {
    return PathHelper::includes($fileName);
}

function public_path($fileName) {
    return PathHelper::public_file($fileName);
}

function uploads_path($fileName = '') {
    return PathHelper::uploads($fileName);
}

function shared_header_path() {
    return PathHelper::shared_header();
}

function base_url($path = '') {
    return PathHelper::base_url($path);
}

/**
 * Funciones para incluir archivos de forma segura
 */

function safe_include($path) {
    return PathHelper::safe_include($path);
}

function safe_include_once($path) {
    return PathHelper::safe_include_once($path);
}

function safe_require($path) {
    return PathHelper::safe_require($path);
}

function safe_require_once($path) {
    return PathHelper::safe_require_once($path);
}

/**
 * Auto-incluir archivos comunes
 */
function autoload_common_files() {
    // Incluir la conexión a la base de datos
    safe_require_once(config_path('conexion.php'));
    
    // Incluir helpers de autenticación si existen
    $auth_helpers = includes_path('auth_helpers.php');
    if (PathHelper::file_exists($auth_helpers)) {
        safe_require_once($auth_helpers);
    }
}

// Inicialización automática opcional
if (!defined('SKIP_AUTOLOAD')) {
    // autoload_common_files(); // Descomenta si quieres carga automática
}