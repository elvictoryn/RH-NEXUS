<?php
if (!isset($_SESSION)) session_start();
// Incluir sistema de rutas dinámicas
require_once(__DIR__ . '/../../../config/paths.php');

$titulo_pagina = "Lista de Sedes";

// Incluir header y modelo usando rutas dinámicas
safe_include_once(shared_header_path());
safe_require_once(model_path('Sede'));

$sede = new Sede();
$listaSedes = $sede->obtenerTodas();

$mensaje_exito = $_SESSION['sede_guardada'] ?? $_SESSION['sede_editada'] ?? $_SESSION['sede_eliminada'] ?? null;
$mensaje_error = $_SESSION['error_guardado'] ?? $_SESSION['error_edicion'] ?? $_SESSION['error_eliminacion'] ?? null;
unset($_SESSION['sede_guardada'], $_SESSION['sede_editada'], $_SESSION['sede_eliminada'], $_SESSION['error_guardado'], $_SESSION['error_edicion'], $_SESSION['error_eliminacion']);
?>

<div class="container mt-4">
  <div class="card shadow p-4 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary">📋 Lista de Sedes</h2>
      <div>
        <a href="crear_sede.php" class="btn btn-success me-2">+ Registrar Nueva Sede</a>
        <a href="menu.php" class="btn btn-outline-secondary">← Regresar</a>
      </div>
    </div>

    <?php if ($mensaje_exito): ?>
      <div class="alert alert-success text-center fw-bold"><?= htmlspecialchars($mensaje_exito) ?></div>
    <?php elseif ($mensaje_error): ?>
      <div class="alert alert-danger text-center fw-bold"><?= htmlspecialchars($mensaje_error) ?></div>
    <?php endif; ?>

    <?php if (empty($listaSedes)): ?>
      <div class="alert alert-info text-center">No hay sedes registradas.</div>
    <?php else: ?>
      <div class="mb-3">
        <input type="text" id="busqueda" class="form-control" placeholder="🔍 Buscar por nombre, municipio o estado...">
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle" id="tablaSedes">
          <thead class="table-secondary text-center">
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Municipio</th>
              <th>Estado</th>
              <th>Teléfono</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody class="text-center">
            <?php foreach ($listaSedes as $index => $s): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($s['nombre']) ?></td>
                <td><?= htmlspecialchars($s['municipio']) ?></td>
                <td><?= htmlspecialchars($s['estado']) ?></td>
                <td><?= htmlspecialchars($s['telefono']) ?></td>
                <td>
                  <a href="editar_sede.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar Sede">✏️</a>
                  <a href="eliminar_sede.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmarEliminacion()" title="Eliminar Sede">🗑️</a>
                  <button type="button" class="btn btn-sm btn-outline-info" onclick="mostrarDetalles(this)" title="Ver Detalles">👁️‍🗨️</button>
                </td>
              </tr>
              <tr class="detalles-row" style="display: none;">
                <td colspan="6">
                  <div class="border rounded p-3 bg-white text-start small">
                    <strong>Nombre:</strong> <?= htmlspecialchars($s['nombre']) ?><br>
                    <strong>Domicilio:</strong> <?= htmlspecialchars($s['domicilio']) ?><br>
                    <strong>Número:</strong> <?= htmlspecialchars($s['numero']) ?><br>
                    <strong>Interior:</strong> <?= $s['interior'] ? htmlspecialchars($s['interior']) : 'N/A' ?><br>
                    <strong>Colonia:</strong> <?= htmlspecialchars($s['colonia']) ?><br>
                    <strong>Municipio:</strong> <?= htmlspecialchars($s['municipio']) ?><br>
                    <strong>Estado:</strong> <?= htmlspecialchars($s['estado']) ?><br>
                    <strong>CP:</strong> <?= htmlspecialchars($s['cp']) ?><br>
                    <strong>Teléfono:</strong> <?= htmlspecialchars($s['telefono']) ?>
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
    return confirm("¿Estás seguro de que deseas eliminar esta sede?");
  }

  function mostrarDetalles(boton) {
    const fila = boton.closest('tr');
    const filaDetalles = fila.nextElementSibling;
    filaDetalles.style.display = filaDetalles.style.display === 'none' ? '' : 'none';
  }

  document.getElementById('busqueda').addEventListener('keyup', function () {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll("#tablaSedes tbody tr");

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