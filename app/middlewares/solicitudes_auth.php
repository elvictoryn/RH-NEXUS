<?php
// Middleware de acceso al módulo de Solicitudes para TODOS los roles válidos
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
if (session_status() === PHP_SESSION_NONE) session_start();

$uid = (int)($_SESSION['id'] ?? 0);
$rol = strtolower($_SESSION['rol'] ?? '');

$roles_permitidos = ['admin','rh','gerente','jefe_area'];

if (!$uid || !in_array($rol, $roles_permitidos, true)) {
  header('Location: ' . BASE_PATH . '/public/login.php');
  exit;
}