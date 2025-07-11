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
  <h2 class="text-center mb-4">Gestión de Usuarios</h2>
  <p class="text-center text-muted mb-5">Desde aquí puedes crear, editar y eliminar usuarios del sistema.</p>

  <div class="row justify-content-center g-4">

    <!-- Crear nuevo usuario -->
    <div class="col-md-4">
      <div class="custom-card h-100 text-center">
        <i class="fas fa-user-plus fa-2x mb-3 text-primary"></i>
        <h5>Nuevo Usuario</h5>
        <p>Registrar un nuevo usuario con su rol correspondiente.</p>
        <a href="crear.php" class="btn btn-primary w-100">Crear Usuario</a>
      </div>
    </div>

    <!-- Ver todos los usuarios -->
    <div class="col-md-4">
      <div class="custom-card h-100 text-center">
        <i class="fas fa-users-cog fa-2x mb-3 text-primary"></i>
        <h5>Administrar Usuarios</h5>
        <p>Consultar y gestionar los usuarios ya registrados.</p>
        <a href="lista.php" class="btn btn-primary w-100">Ver Usuarios</a>
      </div>
    </div>

  </div>
</div>

</body>
</html>
