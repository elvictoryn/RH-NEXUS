<?php
if (!isset($_SESSION)) session_start();
$titulo_pagina = "Registrar Departamento";
include_once('../../shared/header.php');
require_once('../../../models/Sede.php');
require_once('../../../models/Departamento.php');

$sede = new Sede();
$listaSedes = $sede->obtenerTodas();

$mensaje_exito = $_SESSION['departamento_guardado'] ?? null;
$mensaje_error = $_SESSION['error_departamento'] ?? null;
unset($_SESSION['departamento_guardado'], $_SESSION['error_departamento']);

// Verificación AJAX embebida
if (isset($_GET['verificar_nombre'])) {
  $departamento = new Departamento();
  $existe = $departamento->existeNombreEnSede($_GET['nombre'], $_GET['sede_id']);
  echo json_encode(['existe' => $existe]);
  exit;
}
?>

<div class="container mt-4">
  <div class="card shadow p-4 bg-light">
    <h2 class="text-primary mb-4">Registrar Nuevo Departamento</h2>

    <?php if ($mensaje_exito): ?>
      <div class="alert alert-success text-center fw-bold" id="alerta-exito">
        ✅ <?= htmlspecialchars($mensaje_exito) ?>
      </div>
    <?php elseif ($mensaje_error): ?>
      <div class="alert alert-danger text-center fw-bold" id="alerta-error">
        ❌ <?= htmlspecialchars($mensaje_error) ?>
      </div>
    <?php endif; ?>

    <form id="formDepartamento" method="POST" action="guardar_dep.php" autocomplete="off">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre del departamento</label>
          <input type="text" name="nombre" class="form-control text-uppercase" id="nombreDepartamento" required>
          <div id="mensajeNombre" class="text-danger mt-1" style="font-size: 0.9rem;"></div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Sede asignada</label>
          <select name="sede_id" class="form-select text-uppercase" id="sedeSelect" required>
            <option value="" disabled selected>Seleccione una sede</option>
            <?php foreach ($listaSedes as $sede): ?>
              <option value="<?= $sede['id'] ?>"><?= htmlspecialchars($sede['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-12">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" class="form-control text-uppercase" rows="3" required></textarea>
        </div>
      </div>

      <div class="mt-4 d-flex justify-content-between">
        <a href="menu.php" class="btn btn-outline-secondary">← Cancelar</a>
        <div>
          <a href="lista_dep.php" class="btn btn-outline-info me-2">📋 Lista de Departamentos</a>
          <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar Departamento</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  document.querySelectorAll('.text-uppercase').forEach(input => {
    input.addEventListener('input', () => {
      input.value = input.value.toUpperCase();
    });
  });

  const nombreInput = document.getElementById("nombreDepartamento");
  const sedeSelect = document.getElementById("sedeSelect");
  const mensajeNombre = document.getElementById("mensajeNombre");
  const btnGuardar = document.getElementById("btnGuardar");

  function validarNombre() {
    const nombre = nombreInput.value.trim().toUpperCase();
    const sede_id = sedeSelect.value;

    if (nombre && sede_id) {
      fetch(`crear_dep.php?verificar_nombre=1&nombre=${encodeURIComponent(nombre)}&sede_id=${sede_id}`)
        .then(res => res.json())
        .then(data => {
          if (data.existe) {
            mensajeNombre.textContent = "❌ Ya existe un departamento con ese nombre en esta sede.";
            btnGuardar.disabled = true;
          } else {
            mensajeNombre.textContent = "";
            btnGuardar.disabled = false;
          }
        })
        .catch(() => {
          mensajeNombre.textContent = "⚠️ Error al verificar el nombre.";
          btnGuardar.disabled = false;
        });
    } else {
      mensajeNombre.textContent = "";
      btnGuardar.disabled = false;
    }
  }

  nombreInput.addEventListener("blur", validarNombre);
  sedeSelect.addEventListener("change", validarNombre);

  setTimeout(() => {
    const alertaExito = document.getElementById('alerta-exito');
    const alertaError = document.getElementById('alerta-error');
    if (alertaExito) alertaExito.remove();
    if (alertaError) alertaError.remove();
  }, 5000);
</script>
