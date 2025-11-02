<?php
// Detectar si estamos en InfinityFree
function isInfinityFree(): bool {
    return isset($_SERVER['HTTP_HOST']) && 
           (strpos($_SERVER['HTTP_HOST'], 'epizy.com') !== false || 
            strpos($_SERVER['HTTP_HOST'], 'infinityfree.net') !== false ||
            strpos($_SERVER['HTTP_HOST'], 'ct.ws') !== false ||
            strpos($_SERVER['HTTP_HOST'], 'rh-nexus.ct.ws') !== false);
}

// Detectar si estamos usando HTTPS
function isHTTPS(): bool {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
           $_SERVER['SERVER_PORT'] == 443 ||
           (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

// Generar URL base con protocolo correcto
function getBaseURL(): string {
    $protocol = isHTTPS() ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = defined('BASE_PATH') ? BASE_PATH : '/sistema_rh';
    return $protocol . '://' . $host . $basePath;
}

// Configuración de entorno
if (isInfinityFree()) {
    // Configuración para InfinityFree
    define('ENVIRONMENT', 'production');
    define('BASE_PATH', '/sistema_rh');
    define('DB_CONFIG', 'production');
    define('IS_HTTPS', isHTTPS());
    define('BASE_URL', getBaseURL());
} else {
    // Configuración local
    define('ENVIRONMENT', 'development');
    define('BASE_PATH', '/sistema_rh');
    define('DB_CONFIG', 'local');
    define('IS_HTTPS', isHTTPS());
    define('BASE_URL', getBaseURL());
}
