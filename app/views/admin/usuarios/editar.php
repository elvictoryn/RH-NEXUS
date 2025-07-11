<?php
if (!isset($_SESSION)) session_start();
$titulo_pagina = "Editar Usuario - Nexus RH";
include_once('../../shared/header.php');
require_once('../../../models/Usuario.php');

$modelo = new Usuario();
$id = $_GET['id'] ?? null;
$usuario = $modelo->obtenerPorId($id);

if (!$usuario) {
    echo "<div class='alert alert-danger'>Usuario no encontrado</div>";
    exit;
}
?>

<div class="container mt-4">
  <div class="card shadow-lg p-4 rounded bg-light">
    <h2 class="mb-4 text-primary">Editar Usuario</h2>

    <form action="actualizar.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Usuario</label>
          <input type="text" name="usuario" class="form-control" value="<?= htmlspecialchars($usuario['usuario']) ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Nombre completo</label>
          <input type="text" name="nombre_completo" class="form-control text-uppercase" value="<?= htmlspecialchars($usuario['nombre_completo']) ?>" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Rol</label>
          <select name="rol" class="form-select" required>
            <option value="admin" <?= $usuario['rol'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
            <option value="rh" <?= $usuario['rol'] == 'rh' ? 'selected' : '' ?>>Recursos Humanos</option>
            <option value="gerente" <?= $usuario['rol'] == 'gerente' ? 'selected' : '' ?>>Gerente</option>
            <option value="jefe_area" <?= $usuario['rol'] == 'jefe_area' ? 'selected' : '' ?>>Jefe de Área</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Departamento</label>
          <input type="text" name="departamento" class="form-control text-uppercase" value="<?= htmlspecialchars($usuario['departamento']) ?>" required>
        </div>

        <div class="col-md-4">
          <label class="form-label">Sede</label>
          <input type="text" name="sede" class="form-control text-uppercase" value="<?= htmlspecialchars($usuario['sede']) ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Número de empleado</label>
          <input type="text" name="numero_empleado" class="form-control" value="<?= htmlspecialchars($usuario['numero_empleado']) ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Correo electrónico</label>
          <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Fotografía actual</label><br>
          <?php if ($usuario['fotografia']): ?>
            <img src="../../../public/uploads/<?= $usuario['fotografia'] ?>" alt="Foto" class="img-thumbnail mb-2" style="max-width: 100px;">
          <?php else: ?>
            <p class="text-muted">No hay foto</p>
          <?php endif; ?>
          <input type="file" name="fotografia" class="form-control mt-2">
          <input type="hidden" name="foto_actual" value="<?= $usuario['fotografia'] ?>">
        </div>
      </div>

      <!-- Cambiar contraseña -->
      <div class="mt-4">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="togglePassword()">Modificar contraseña</button>
            <div id="passwordSection" class="mt-3 d-none">
            <label class="form-label">Nueva contraseña</label>
             <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para no cambiar">
  </div>
</div>

      <div class="mt-4 d-flex justify-content-between">
        <a href="lista.php" class="btn btn-outline-dark">← Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<script>
function togglePassword() {
  const section = document.getElementById('passwordSection');
  section.classList.toggle('d-none');
}
</script>
