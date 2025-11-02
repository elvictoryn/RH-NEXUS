<?php
// Punto de entrada universal a CREAR SOLICITUD (para todos los roles)
define('BASE_PATH','/sistema_rh');
require_once __DIR__ . '/../app/middlewares/solicitudes_auth.php';

// Reutilizamos la vista de crear que ya tienes
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/admin/solicitudes/crear_solicitud.php';
