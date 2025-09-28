<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ¿No hay sesión iniciada?
if (empty($_SESSION['id'])) {
    header('Location: /sistema_rh/public/login.php');
    exit;
}

// Tiempo máximo de inactividad (30 minutos)
$maxInactivity = 30 * 60; 

if (isset($_SESSION['last_act']) && (time() - $_SESSION['last_act']) > $maxInactivity) {
    // Limpiar todo
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: /sistema_rh/public/login.php?expired=1');
    exit;
}

// Actualizar timestamp de actividad
$_SESSION['last_act'] = time();
