<?php
// Middleware de acceso al módulo de Solicitudes para TODOS los roles válidos
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
if (session_status() === PHP_SESSION_NONE) session_start();

$uid = (int)($_SESSION['id'] ?? 0);
$rol = strtolower($_SESSION['rol'] ?? '');

$roles_permitidos = ['admin','rh','gerente','jefe_area'];

if (!$uid || !in_array($rol, $roles_permitidos, true)) {
  require_once __DIR__ . '/../../config/environment.php';
  $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://' . $_SERVER['HTTP_HOST'] . BASE_PATH;
  header('Location: ' . $baseUrl . '/public/login.php');
  exit;
}