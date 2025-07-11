<?php
if (!isset($_SESSION)) session_start();
$titulo_pagina = "Lista de Sedes";
include_once('../../shared/header.php');
require_once('../../../models/Sede.php');

$sede = new Sede();
$listaSedes = $sede->obtenerTodas();
?>

<div class="container mt-4">
  <div class="card shadow p-4 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary">📋 Lista de Sedes</h2>
      <a href="crear_sede.php" class="btn btn-success">+ Registrar Nueva Sede</a>
      <a href="menu.php" class="btn btn-success">← Regresar</a>
    </div>

    <div class="mb-3">
      <input type="text" class="form-control" id="buscador" placeholder="🔍 Buscar por nombre o municipio...">
    </div>

    <div class="table-responsive">
      <table class="table table-dark table-striped table-hover align-middle" id="tablaSedes">
        <thead class="text-center">
          <tr>
            <th>Nombre</th>
            <th>Dirección</th>
            <th>Municipio</th>
            <th>Estado</th>
            <th>Teléfono</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody class="text-center">
          <?php foreach ($listaSedes as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['nombre']) ?></td>
              <td><?= htmlspecialchars($s['domicilio']) . ' ' . htmlspecialchars($s['numero']) . ' ' . ($s['interior'] ? 'Int. ' . htmlspecialchars($s['interior']) : '') . ', ' . htmlspecialchars($s['colonia']) . ', CP ' . htmlspecialchars($s['cp']) ?></td>
              <td><?= htmlspecialchars($s['municipio']) ?></td>
              <td><?= htmlspecialchars($s['estado']) ?></td>
              <td><?= htmlspecialchars($s['telefono']) ?></td>
              <td>
                <a href="editar_sede.php?id=<?= $s['id'] ?>" class="text-warning me-2" data-bs-toggle="tooltip" title="Editar">
                  <i class="fas fa-pen"></i>
                </a>
                <a href="eliminar_sede.php?id=<?= $s['id'] ?>" class="text-danger" data-bs-toggle="tooltip" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta sede?')">
                  <i class="fas fa-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  const buscador = document.getElementById('buscador');
  const tabla = document.getElementById('tablaSedes').getElementsByTagName('tbody')[0];

  buscador.addEventListener('input', () => {
    const texto = buscador.value.toLowerCase();
    const filas = tabla.getElementsByTagName('tr');

    Array.from(filas).forEach(fila => {
      const contenidoFila = fila.textContent.toLowerCase();
      fila.style.display = contenidoFila.includes(texto) ? '' : 'none';
    });
  });
</script>
