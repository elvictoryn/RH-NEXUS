<?php 
// Incluir sistema de rutas dinámicas
require_once(__DIR__ . '/../../../config/paths.php');

if (!isset($_SESSION)) session_start();
$titulo_pagina = "Lista de Usuarios"; 

// Incluir header y modelo usando rutas dinámicas
safe_include_once(shared_header_path());
safe_require_once(model_path('Usuario'));
$modelo = new Usuario();
$usuarios = $modelo->obtenerTodos();
?>

<div class="container mt-4">
  <div class="card shadow p-4 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary">📋 Lista de usuarios</h2>
      <a href="crear.php" class="btn btn-success">+ Registrar nuevo usuario </a>
      <a href="menu.php" class="btn btn-success">← Regresar</a>
    </div>

  <input type="text" id="buscar" class="form-control mb-3" placeholder="Buscar por nombre, usuario, departamento o número de empleado...">

  <div class="table-responsive">
   <table class="table table-dark table-striped table-bordered table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Usuario</th>
          <th>Nombre</th>
          <th>Rol</th>
          <th>Departamento</th>
          <th>Sede</th>
          <th>Número de Empleado</th>
          <th class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody id="tablaUsuarios">
        <?php foreach ($usuarios as $u): ?>
          <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['usuario']) ?></td>
            <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
            <td><?= ucfirst($u['rol']) ?></td>
            <td><?= htmlspecialchars($u['departamento']) ?></td>
            <td><?= htmlspecialchars($u['sede']) ?></td>
            <td><?= htmlspecialchars($u['numero_empleado']) ?></td>
            <td class="text-center">
              <a href="ver.php?id=<?= $u['id'] ?>" class="text-info me-2" data-bs-toggle="tooltip" title="Ver detalles">
                <i class="fas fa-eye"></i>
              </a>
              <a href="editar.php?id=<?= $u['id'] ?>" class="text-warning me-2" data-bs-toggle="tooltip" title="Editar">
                <i class="fas fa-pen"></i>
              </a>
              <a href="eliminar.php?id=<?= $u['id'] ?>" class="text-danger" data-bs-toggle="tooltip" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                <i class="fas fa-trash"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  document.getElementById("buscar").addEventListener("input", function () {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll("#tablaUsuarios tr");

    filas.forEach(fila => {
      const texto = fila.textContent.toLowerCase();
      fila.style.display = texto.includes(filtro) ? "" : "none";
    });
  });

  // Activar tooltips de Bootstrap
  document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
      new bootstrap.Tooltip(tooltipTriggerEl);
    });
  });
</script>
