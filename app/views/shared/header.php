<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= isset($titulo_pagina) ? $titulo_pagina . ' - Nexus RH' : 'Nexus RH' ?></title>
  <meta name="description" content="<?= isset($descripcion_pagina) ? $descripcion_pagina : 'Sistema de gestión de personal y selección para empresas - Nexus RH' ?>">
  <link rel="icon" href="/sistema_rh/public/img/favicon.ico" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="/sistema_rh/public/css/estilo.css" rel="stylesheet">
</head>
<!-- Navbar superior con rutas directas -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
  <!-- Botón/logo que lleva al panel principal -->
  <a class="navbar-brand fw-bold" href="/sistema_rh/app/views/admin/index.php">Nexus RH</a>

  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarNav">
    <!-- Menú de navegación -->
    <ul class="navbar-nav me-auto mb-2 mb-lg-0">

      <li class="nav-item">
        <a class="nav-link" href="/sistema_rh/app/views/admin/usuarios/menu.php">
          <i class="fas fa-users me-1"></i>Usuarios
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="/sistema_rh/app/views/admin/departamentos/menu.php">
          <i class="fas fa-sitemap me-1"></i>administracion de departamentos
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="/sistema_rh/app/views/admin/solicitudes/index.php">
          <i class="fas fa-envelope me-1"></i>Solicitudes
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="/sistema_rh/app/views/admin/evaluaciones/index.php">
          <i class="fas fa-check-circle me-1"></i>Evaluaciones
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="/sistema_rh/app/views/admin/resultados/index.php">
          <i class="fas fa-chart-line me-1"></i>Resultados
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="/sistema_rh/app/views/admin/ia/index.php">
          <i class="fas fa-brain me-1"></i>Módulo de IA
        </a>
      </li>
    </ul>

    <!-- Usuario logueado y botón de salir -->
    <div class="d-flex text-white">
      <span class="me-3"><i class="fas fa-user-circle me-1"></i><?= $_SESSION['usuario'] ?></span>
      <a href="/sistema_rh/public/logout.php" class="btn btn-light btn-sm">Cerrar sesión</a>
    </div>
  </div>
</nav>


