<?php
define('BASE_PATH','/sistema_rh'); // ajusta si tu carpeta raíz cambia
require_once __DIR__ . '/../app/controllers/AuthController.php';

$auth = new AuthController();

// Si se envió formulario, procesa login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->login();
} else {
    // Mostrar vista de login
    $auth->showLogin($_SESSION['login_error'] ?? null);
    unset($_SESSION['login_error']);
}
