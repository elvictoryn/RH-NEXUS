<?php
// Punto de entrada público a la BANDEJA de solicitudes (para todos los roles)
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
if (session_status() === PHP_SESSION_NONE) session_start();

// (Opcional) Guarda el middleware si lo tienes estable y no saca salida.
// Si te estaba rompiendo, COMÉNTALO y usa el guard sencillo de abajo.
// require_once __DIR__ . '/../app/middlewares/solicitudes_auth.php';

// Guard sencillo por si no usas middleware:
$rol = strtolower($_SESSION['rol'] ?? '');
$uid = (int)($_SESSION['id'] ?? 0);
if (!$uid || !in_array($rol, ['admin','rh','gerente','jefe_area'], true)) {
  header('Location: ' . BASE_PATH . '/public/login.php');
  exit;
}

// Carga la vista de la bandeja (menu.php) desde app/views (servida a través de /public)
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/admin/solicitudes/menu.php';
