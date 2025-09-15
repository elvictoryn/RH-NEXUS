<?php
if (!isset($_SESSION)) session_start();
include_once __DIR__ . '/../../shared/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Usuarios - Nexus RH</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="/sistema_rh/public/css/estilo.css" rel="stylesheet">
</head>
<body>

<div class="container py-5">
  <h2 class="text-center mb-4">Gestión de Solicitudes </h2>
  <p class="text-center text-muted mb-5">Crear y gestionar solicitudes de personal </p>

  <div class="row justify-content-center g-4">

    <!-- Crear nueva solicitud -->
    <div class="col-md-4">
      <div class="custom-card h-100 text-center">
        <i class="fas fa-user-plus fa-2x mb-3 text-primary"></i>
        <h5>Crear solicitud</h5>
        <p>Registra una nueva solicitud de personal para tu departamento</p>
        <a href="crear_solicitud.php" class="btn btn-primary w-100">Crear Nueva</a>
      </div>
    </div>

    <!-- Ver solicitudes  -->
    <div class="col-md-4">
      <div class="custom-card h-100 text-center">
        <i class="fas fa-users-cog fa-2x mb-3 text-primary"></i>
        <h5>Ver solicitudes</h5>
        <p>Consula y gestiona todas las solicitudes del sistema </p>
        <a href="lista_solicitudes.php" class="btn btn-primary w-100">Ver Lista de solicitudes</a>
      </div>
    </div>

<!-- Estadisticas pendiente de desarrollar   -->
    <div class="col-md-4">
      <div class="custom-card h-100 text-center">
        <i class="fas fa-users-cog fa-2x mb-3 text-primary"></i>
        <h5>Estadisticas</h5>
        <p>Generador de estadsticas </p>
        <a href="lista_usuario.php" class="btn btn-primary w-100">Ver Lista de solicitudes</a>
      </div>
    </div>





      <div class="mt-5 text-center">
    <a href="../../admin/index.php" class="btn btn-outline-dark">⬅ Volver al panel principal</a>
  </div>
  </div>
</div>

</body>
</html>
