<?php
// Incluir sistema de rutas dinámicas si no está incluido
if (!defined('PROJECT_FOLDER')) {
    require_once __DIR__ . '/../../config/paths.php';
}

// Incluir helpers de autenticación
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../includes/auth_helpers.php';
}

// Obtener información del usuario actual
$usuario_actual = $_SESSION['usuario'] ?? 'Usuario';
$rol_actual = $_SESSION['rol'] ?? '';

// Determinar la URL del dashboard según el rol
function getDashboardUrl($rol) {
    switch ($rol) {
        case 'admin':
            return base_url('app/views/admin/index.php');
        case 'rh':
            return base_url('app/views/rh/index.php');
        case 'gerente':
            return base_url('app/views/gerente/index.php');
        case 'jefe_area':
            return base_url('app/views/jefe_area/index.php');
        default:
            return base_url('public/dashboard.php');
    }
}

// Determinar el menú según el rol
function getMenuItems($rol) {
    $items = [];
    
    switch ($rol) {
        case 'admin':
            $items = [
                ['url' => 'usuarios/menu.php', 'icon' => 'fas fa-users', 'text' => 'Usuarios'],
                ['url' => 'departamentos/menu.php', 'icon' => 'fas fa-sitemap', 'text' => 'Administración de Departamentos'],
                ['url' => 'solicitudes/index.php', 'icon' => 'fas fa-envelope', 'text' => 'Solicitudes'],
                ['url' => 'evaluaciones/index.php', 'icon' => 'fas fa-check-circle', 'text' => 'Evaluaciones'],
                ['url' => 'resultados/index.php', 'icon' => 'fas fa-chart-line', 'text' => 'Resultados'],
                ['url' => 'ia/index.php', 'icon' => 'fas fa-brain', 'text' => 'Módulo de IA']
            ];
            break;
            
        case 'rh':
            $items = [
                ['url' => 'usuarios/menu.php', 'icon' => 'fas fa-users', 'text' => 'Usuarios'],
                ['url' => 'candidatos/menu.php', 'icon' => 'fas fa-user-tie', 'text' => 'Candidatos'],
                ['url' => 'solicitudes/menu.php', 'icon' => 'fas fa-envelope', 'text' => 'Solicitudes'],
                ['url' => 'evaluaciones/menu.php', 'icon' => 'fas fa-check-circle', 'text' => 'Evaluaciones'],
                ['url' => 'resultados/menu.php', 'icon' => 'fas fa-chart-line', 'text' => 'Resultados']
            ];
            break;
            
        case 'gerente':
            $items = [
                ['url' => 'usuarios/menu.php', 'icon' => 'fas fa-users', 'text' => 'Usuarios'],
                ['url' => 'solicitudes/menu.php', 'icon' => 'fas fa-envelope', 'text' => 'Solicitudes'],
                ['url' => 'evaluaciones/menu.php', 'icon' => 'fas fa-check-circle', 'text' => 'Evaluaciones'],
                ['url' => 'resultados/menu.php', 'icon' => 'fas fa-chart-line', 'text' => 'Resultados']
            ];
            break;
            
        case 'jefe_area':
            $items = [
                ['url' => 'usuarios/menu.php', 'icon' => 'fas fa-users', 'text' => 'Usuarios'],
                ['url' => 'solicitudes/menu.php', 'icon' => 'fas fa-envelope', 'text' => 'Solicitudes'],
                ['url' => 'evaluaciones/menu.php', 'icon' => 'fas fa-check-circle', 'text' => 'Evaluaciones'],
                ['url' => 'resultados/menu.php', 'icon' => 'fas fa-chart-line', 'text' => 'Resultados']
            ];
            break;
    }
    
    return $items;
}

$menu_items = getMenuItems($rol_actual);
$dashboard_url = getDashboardUrl($rol_actual);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= isset($titulo_pagina) ? $titulo_pagina . ' - Nexus RH' : 'Nexus RH' ?></title>
  <meta name="description" content="<?= isset($descripcion_pagina) ? $descripcion_pagina : 'Sistema de gestión de personal y selección para empresas - Nexus RH' ?>">
  <link rel="icon" href="<?= base_url('public/img/favicon.ico') ?>" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="<?= base_url('public/css/estilo.css') ?>" rel="stylesheet">
</head>
<!-- Navbar superior con rutas dinámicas según el rol -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
  <!-- Botón/logo que lleva al panel principal del rol -->
  <a class="navbar-brand fw-bold" href="<?= $dashboard_url ?>">
    <i class="fas fa-home me-2"></i>Nexus RH
  </a>

  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarNav">
    <!-- Menú de navegación dinámico -->
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
      <?php foreach ($menu_items as $item): ?>
        <li class="nav-item">
          <a class="nav-link" href="<?= base_url('app/views/' . $rol_actual . '/' . $item['url']) ?>">
            <i class="<?= $item['icon'] ?> me-1"></i><?= $item['text'] ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <!-- Usuario logueado y botón de salir -->
    <div class="d-flex text-white">
      <span class="me-3">
        <i class="fas fa-user-circle me-1"></i>
        <?= htmlspecialchars($usuario_actual) ?>
        <small class="d-block text-white-50"><?= ucfirst($rol_actual) ?></small>
      </span>
      <a href="<?= base_url('public/logout.php') ?>" class="btn btn-light btn-sm">
        <i class="fas fa-sign-out-alt me-1"></i>Cerrar sesión
      </a>
    </div>
  </div>
</nav>


