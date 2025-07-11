<?php
if (!isset($_SESSION)) session_start();
$titulo_pagina = "Lista de Departamentos";
include_once('../../shared/header.php');
require_once('../../../models/Departamento.php');

$departamento = new Departamento();
$listaDepartamentos = $departamento->obtenerTodosConSede();
?>

<div class="container mt-4">
  <div class="card shadow p-4 bg-light">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="text-primary">📋 Lista de Departamentos</h2>
      <a href="crear_dep.php" class="btn btn-success">+ Registrar Nuevo Departamento</a>
      <a href="menu.php" class="btn btn-success">← Regresar</a>
    </div>

    <div class="mb-3">
      <input type="text" class="form-control" id="buscador" placeholder="🔍 Buscar por nombre o sede...">
    </div>

    <div class="table-responsive">
      <table class="table table-dark table-striped table-hover align-middle" id="tablaDepartamentos">
        <thead class="text-center">
          <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Sede</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody class="text-center">
          <?php foreach ($listaDepartamentos as $dep): ?>
            <tr>
              <td><?= htmlspecialchars($dep['nombre']) ?></td>
              <td><?= htmlspecialchars($dep['descripcion']) ?></td>
              <td><?= htmlspecialchars($dep['sede_nombre']) ?></td>
              <td>
                <!-- Editar y eliminar futuro -->
                <a href="#" class="text-warning me-2" data-bs-toggle="tooltip" title="Editar">
                  <i class="fas fa-pen"></i>
                </a>
                <a href="#" class="text-danger" data-bs-toggle="tooltip" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este departamento?')">
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
  const tabla = document.getElementById('tablaDepartamentos').getElementsByTagName('tbody')[0];

  buscador.addEventListener('input', () => {
    const texto = buscador.value.toLowerCase();
    const filas = tabla.getElementsByTagName('tr');

    Array.from(filas).forEach(fila => {
      const contenidoFila = fila.textContent.toLowerCase();
      fila.style.display = contenidoFila.includes(texto) ? '' : 'none';
    });
  });
</script>

