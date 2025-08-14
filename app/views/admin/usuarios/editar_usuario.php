<?php
if (!isset($_SESSION)) session_start();
require_once('../../../models/Usuario.php');
require_once('../../../models/Departamento.php');
require_once('../../../models/Sede.php');

$usuarioModel = new Usuario();
$departamentoModel = new Departamento();
$sedeModel = new Sede();

// Validar ID
$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
  $_SESSION['error_edicion'] = 'ID de usuario no válido';
  header('Location: lista_usuario.php');
  exit;
}

$usuario = $usuarioModel->obtenerPorId($id);
if (!$usuario) {
  $_SESSION['error_edicion'] = 'Usuario no encontrado';
  header('Location: lista_usuario.php');
  exit;
}

$sedes = $sedeModel->obtenerTodas();
$departamentos = $departamentoModel->obtenerTodosActivos();

$titulo_pagina = "Editar Usuario";
include_once('../../shared/header.php');
?>

<div class="container mt-4">
  <div class="card shadow p-4">
    <h2 class="text-primary mb-4">✏️ Editar Usuario</h2>

    <form id="formEditarUsuario" action="actualiza_usuario.php" method="POST" enctype="multipart/form-data" class="row g-3">
      <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id']) ?>">

      <div class="col-md-6">
        <label for="nombre_completo" class="form-label">Nombre completo</label>
        <input type="text" name="nombre_completo" id="nombre_completo" class="form-control" required value="<?= htmlspecialchars($usuario['nombre_completo']) ?>">
      </div>

      <div class="col-md-3">
        <label for="numero_empleado" class="form-label">Número de empleado</label>
        <input type="text" name="numero_empleado" id="numero_empleado" class="form-control" required value="<?= htmlspecialchars($usuario['numero_empleado']) ?>">
        <div class="invalid-feedback" id="errorNumeroEmpleado"></div>
      </div>

      <div class="col-md-3">
        <label for="usuario" class="form-label">Usuario</label>
        <input type="text" name="usuario" id="usuario" class="form-control" required value="<?= htmlspecialchars($usuario['usuario']) ?>">
        <div class="invalid-feedback" id="errorUsuario"></div>
      </div>

      <div class="col-md-4">
        <label for="correo" class="form-label">Correo electrónico</label>
        <input type="email" name="correo" id="correo" class="form-control" value="<?= htmlspecialchars($usuario['correo']) ?>">
      </div>

      <div class="col-md-4">
        <label for="rol" class="form-label">Rol</label>
        <select name="rol" id="rol" class="form-select" required>
          <option value="">Seleccione...</option>
          <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
          <option value="rh" <?= $usuario['rol'] === 'rh' ? 'selected' : '' ?>>RH</option>
          <option value="jefe" <?= $usuario['rol'] === 'jefe' ? 'selected' : '' ?>>Jefe de área</option>
          <option value="gerente" <?= $usuario['rol'] === 'gerente' ? 'selected' : '' ?>>Gerente</option>
        </select>
      </div>

      <div class="col-md-4">
        <label for="telefono" class="form-label">Teléfono</label>
        <input type="text" name="telefono" id="telefono" class="form-control" value="<?= htmlspecialchars($usuario['telefono']) ?>">
      </div>

      <div class="col-md-6">
        <label for="sede_id" class="form-label">Sede</label>
        <select name="sede_id" id="sede_id" class="form-select" required>
          <option value="">Seleccione sede</option>
          <?php foreach ($sedes as $sede): ?>
            <option value="<?= $sede['id'] ?>" <?= $usuario['sede_id'] == $sede['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sede['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label for="departamento_id" class="form-label">Departamento</label>
        <select name="departamento_id" id="departamento_id" class="form-select" required>
          <option value="">Seleccione departamento</option>
          <?php foreach ($departamentos as $dep): ?>
            <?php if ($dep['sede_id'] == $usuario['sede_id']): ?>
              <option value="<?= $dep['id'] ?>" <?= $usuario['departamento_id'] == $dep['id'] ? 'selected' : '' ?>><?= htmlspecialchars($dep['nombre']) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label for="fotografia" class="form-label">Fotografía</label>
        <input type="file" name="fotografia" id="fotografia" class="form-control">
        <?php if ($usuario['fotografia']): ?>
          <img src="../../../public/img/usuarios/<?= $usuario['fotografia'] ?>" alt="Foto actual" width="100" class="mt-2">
        <?php endif; ?>
      </div>

      <div class="col-md-6">
        <label for="estado" class="form-label">Estado</label>
        <select name="estado" id="estado" class="form-select" required>
          <option value="activo" <?= $usuario['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option>
          <option value="inactivo" <?= $usuario['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select>
      </div>

      <div class="col-12 d-flex justify-content-between">
        <a href="lista_usuario.php" class="btn btn-secondary">← Cancelar</a>
        <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
      </div>
    </form>
  </div>
</div>

<script src="../../../public/js/validaciones_usuario.js"></script>
