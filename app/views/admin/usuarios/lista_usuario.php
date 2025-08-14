<?php
if (!isset($_SESSION)) session_start();
$titulo_pagina = "Lista de Usuarios";
include_once('../../shared/header.php');
require_once('../../../models/Usuario.php');

$usuarioModel = new Usuario();
$listaUsuarios = $usuarioModel->obtenerTodosActivos();

$mensaje_exito = $_SESSION['usuario_guardado'] ?? $_SESSION['usuario_editado'] ?? $_SESSION['usuario_eliminado'] ?? null;
$mensaje_error = $_SESSION['error_guardado'] ?? $_SESSION['error_edicion'] ?? $_SESSION['error_eliminacion'] ?? null;
unset($_SESSION['usuario_guardado'], $_SESSION['usuario_editado'], $_SESSION['usuario_eliminado'], $_SESSION['error_guardado'], $_SESSION['error_edicion'], $_SESSION['error_eliminacion']);
?>

<div class="container mt-4">
  <div class="card shadow p-4 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary">📋 Lista de Usuarios</h2>
      <div>
        <a href="crear_usuario.php" class="btn btn-success me-2">+ Registrar Nuevo Usuario</a>
        <a href="menu.php" class="btn btn-outline-secondary">← Regresar</a>
      </div>
    </div>

    <?php if ($mensaje_exito): ?>
      <div class="alert alert-success text-center fw-bold"><?= htmlspecialchars($mensaje_exito) ?></div>
    <?php elseif ($mensaje_error): ?>
      <div class="alert alert-danger text-center fw-bold"><?= htmlspecialchars($mensaje_error) ?></div>
    <?php endif; ?>

    <?php if (empty($listaUsuarios)): ?>
      <div class="alert alert-info text-center">No hay usuarios registrados.</div>
    <?php else: ?>
      <div class="mb-3">
        <input type="text" id="busqueda" class="form-control" placeholder="🔍 Buscar por nombre, número de empleado, rol...">
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle" id="tablaUsuarios">
          <thead class="table-secondary text-center">
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Número de Empleado</th>
              <th>Rol</th>
              <th>Departamento</th>
              <th>Sede</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody class="text-center">
            <?php foreach ($listaUsuarios as $index => $u): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
                <td><?= htmlspecialchars($u['numero_empleado']) ?></td>
                <td><?= strtoupper($u['rol']) ?></td>
                <td><?= $u['nombre_departamento'] ?? 'N/A' ?></td>
                <td><?= $u['nombre_sede'] ?? 'N/A' ?></td>
                <td>
                  <a href="editar_usuario.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar Usuario">✏️</a>
                  <a href="eliminar_usuario.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmarEliminacion()" title="Eliminar Usuario">🗑️</a>
                  <button type="button" class="btn btn-sm btn-outline-info" onclick="mostrarDetalles(this)" title="Ver Detalles">👁️‍🗨️</button>
                </td>
              </tr>
              <tr class="detalles-row" style="display: none;">
                <td colspan="7">
                  <div class="border rounded p-3 bg-white text-start small">
                    <strong>Nombre:</strong> <?= htmlspecialchars($u['nombre_completo']) ?><br>
                    <strong>Usuario:</strong> <?= htmlspecialchars($u['usuario']) ?><br>
                    <strong>Correo:</strong> <?= htmlspecialchars($u['correo']) ?><br>
                    <strong>Teléfono:</strong> <?= htmlspecialchars($u['telefono']) ?><br>
                    <strong>Rol:</strong> <?= strtoupper($u['rol']) ?><br>
                    <strong>Departamento:</strong> <?= $u['nombre_departamento'] ?? 'N/A' ?><br>
                    <strong>Sede:</strong> <?= $u['nombre_sede'] ?? 'N/A' ?><br>
                    <strong>Estado:</strong> <?= isset($u['estado']) ? htmlspecialchars($u['estado']) : 'N/A' ?><br>
                    <strong>Fotografía:</strong>
                    <?php if (isset($u['fotografia']) && !empty($u['fotografia'])): ?>
                   <br><img src="../../../public/img/usuarios/<?= htmlspecialchars($u['fotografia']) ?>" alt="Foto" class="img-thumbnail mt-1" width="100">
                   <?php else: ?> No disponible<?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
 function confirmarEliminacion() {
  return confirm("¿Estás seguro de que deseas eliminar (dar de baja) este usuario?");
}


  function mostrarDetalles(boton) {
    const fila = boton.closest('tr');
    const filaDetalles = fila.nextElementSibling;
    filaDetalles.style.display = filaDetalles.style.display === 'none' ? '' : 'none';
  }

  document.getElementById('busqueda').addEventListener('keyup', function () {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll("#tablaUsuarios tbody tr");

    filas.forEach((fila, index) => {
      if (index % 2 === 0) {
        const texto = fila.textContent.toLowerCase();
        const detalles = fila.nextElementSibling;
        const mostrar = texto.includes(filtro);
        fila.style.display = mostrar ? '' : 'none';
        detalles.style.display = 'none';
      }
    });
  });

  setTimeout(() => {
    document.querySelector('.alert-success')?.remove();
    document.querySelector('.alert-danger')?.remove();
  }, 4000);
</script>
