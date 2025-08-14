<?php
if (!isset($_SESSION)) session_start();
require_once('../../../models/Sede.php');


if (isset($_GET['verificar_nombre'])) {
    $nombre = strtoupper(trim($_GET['verificar_nombre']));
    $sede = new Sede();
    echo json_encode(['existe' => $sede->existeNombre($nombre)]);
    exit;
}

$titulo_pagina = "Registrar Sede";
include_once('../../shared/header.php');

$mensaje_exito = $_SESSION['sede_guardada'] ?? null;
$mensaje_error = $_SESSION['error_guardado'] ?? null;
unset($_SESSION['sede_guardada'], $_SESSION['error_guardado']);
?>

<div class="container mt-4">
  <div class="card shadow p-4 bg-light">
    <h2 class="text-primary mb-4"> ➕ Registrar Nueva Sede</h2>

    <?php if ($mensaje_exito): ?>
      <div class="alert alert-success text-center fw-bold">
        ✅ <?= htmlspecialchars($mensaje_exito) ?>
      </div>
    <?php elseif ($mensaje_error): ?>
      <div class="alert alert-danger text-center fw-bold">
        ❌ <?= htmlspecialchars($mensaje_error) ?>
      </div>
    <?php endif; ?>

    <form id="formSede" method="POST" action="guardar_sede.php" autocomplete="off">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre de la sede</label>
          <input type="text" name="nombre" class="form-control text-uppercase" id="nombreSede" required>
          <div id="mensajeNombre" class="text-danger mt-1" style="font-size: 0.9rem;"></div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Domicilio (calle)</label>
          <input type="text" name="domicilio" class="form-control text-uppercase" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Número exterior</label>
          <input type="text" name="numero" class="form-control text-uppercase" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Número interior (opcional)</label>
          <input type="text" name="interior" class="form-control text-uppercase">
        </div>
        <div class="col-md-6">
          <label class="form-label">Colonia</label>
          <input type="text" name="colonia" class="form-control text-uppercase" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Municipio</label>
          <input type="text" name="municipio" class="form-control text-uppercase" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Estado</label>
          <input type="text" name="estado" class="form-control text-uppercase" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Código Postal</label>
          <input type="text" name="cp" class="form-control" required pattern="\d{5}" title="Debe contener 5 dígitos">
        </div>
        <div class="col-md-4">
          <label class="form-label">Teléfono (10 dígitos)</label>
          <input type="tel" name="telefono" class="form-control" required pattern="[0-9]{10}" title="Debe contener 10 dígitos">
        </div>
      </div>

      <div class="mt-4 d-flex justify-content-between">
        <a href="menu.php" class="btn btn-outline-secondary">← Cancelar</a>
        <a href="lista_sedes.php" class="btn btn-outline-info">📋 Lista de Sedes</a>
        <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar Sede</button>
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

  document.getElementById("nombreSede").addEventListener("blur", function () {
    const nombre = this.value.trim().toUpperCase();
    const boton = document.getElementById("btnGuardar");
    const mensaje = document.getElementById("mensajeNombre");

    if (nombre.length === 0) {
      mensaje.textContent = "";
      boton.disabled = false;
      return;
    }

    fetch("?verificar_nombre=" + encodeURIComponent(nombre))
      .then(response => response.json())
      .then(data => {
        if (data.existe) {
          mensaje.textContent = "❌ Ya existe una sede registrada con ese nombre.";
          boton.disabled = true;
        } else {
          mensaje.textContent = "";
          boton.disabled = false;
        }
      })
      .catch(() => {
        mensaje.textContent = "⚠️ Error al verificar el nombre.";
        boton.disabled = false;
      });
  });
</script>
<script>
setTimeout(() => {
  const successAlert = document.querySelector('.alert-success');
  const errorAlert = document.querySelector('.alert-danger');
  if (successAlert) successAlert.style.display = 'none';
  if (errorAlert) errorAlert.style.display = 'none';
}, 4000);
</script>