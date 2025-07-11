<?php
if (!isset($_SESSION)) session_start();
$titulo_pagina = "Editar Sede";
include_once('../../shared/header.php');
require_once('../../../models/Sede.php');

$sede = new Sede();

// Obtener ID desde GET
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$datos = $sede->obtenerPorId($id);

// Si no existe, redirecciona
if (!$datos) {
    $_SESSION['error_edicion'] = "Sede no encontrada.";
    header("Location: lista_sedes.php");
    exit;
}

$mensaje_exito = $_SESSION['sede_editada'] ?? null;
$mensaje_error = $_SESSION['error_edicion'] ?? null;
unset($_SESSION['sede_editada'], $_SESSION['error_edicion']);
?>

<div class="container mt-4">
  <div class="card shadow p-4 bg-light">
    <h2 class="text-primary mb-4">✏️ Editar Sede</h2>

    <?php if ($mensaje_exito): ?>
      <div class="alert alert-success text-center fw-bold" id="alerta-exito">
        <?= htmlspecialchars($mensaje_exito) ?>
      </div>
    <?php elseif ($mensaje_error): ?>
      <div class="alert alert-danger text-center fw-bold" id="alerta-error">
        <?= htmlspecialchars($mensaje_error) ?>
      </div>
    <?php endif; ?>

    <form id="formEditarSede" method="POST" action="actualiza_sede.php" autocomplete="off">
      <input type="hidden" name="id" value="<?= $datos['id'] ?>">

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre de la sede</label>
          <input type="text" name="nombre" class="form-control text-uppercase" id="nombreSede"
                 value="<?= htmlspecialchars($datos['nombre']) ?>" required>
          <div id="mensajeNombre" class="text-danger mt-1" style="font-size: 0.9rem;"></div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Domicilio (calle)</label>
          <input type="text" name="domicilio" class="form-control text-uppercase" required
                 value="<?= htmlspecialchars($datos['domicilio']) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Número exterior</label>
          <input type="text" name="numero" class="form-control text-uppercase" required
                 value="<?= htmlspecialchars($datos['numero']) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Número interior (opcional)</label>
          <input type="text" name="interior" class="form-control text-uppercase"
                 value="<?= htmlspecialchars($datos['interior']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Colonia</label>
          <input type="text" name="colonia" class="form-control text-uppercase" required
                 value="<?= htmlspecialchars($datos['colonia']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Municipio</label>
          <input type="text" name="municipio" class="form-control text-uppercase" required
                 value="<?= htmlspecialchars($datos['municipio']) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Estado</label>
          <input type="text" name="estado" class="form-control text-uppercase" required
                 value="<?= htmlspecialchars($datos['estado']) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Código Postal</label>
          <input type="text" name="cp" class="form-control" required pattern="\d{5}"
                 value="<?= htmlspecialchars($datos['cp']) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Teléfono (10 dígitos)</label>
          <input type="tel" name="telefono" class="form-control" required pattern="[0-9]{10}"
                 value="<?= htmlspecialchars($datos['telefono']) ?>">
        </div>
      </div>

      <div class="mt-4 d-flex justify-content-between">
        <a href="lista_sedes.php" class="btn btn-outline-secondary">← Regresar</a>
        <button type="submit" class="btn btn-primary" id="btnGuardar">Actualizar Sede</button>
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

  const nombreInput = document.getElementById("nombreSede");
  const mensajeNombre = document.getElementById("mensajeNombre");
  const boton = document.getElementById("btnGuardar");

  nombreInput.addEventListener("blur", function () {
    const nombre = this.value.trim().toUpperCase();
    const id = <?= $datos['id'] ?>;

    if (nombre.length === 0) {
      mensajeNombre.textContent = "";
      boton.disabled = false;
      return;
    }

    fetch("actualiza_sede.php?verificar_nombre=1&nombre=" + encodeURIComponent(nombre) + "&id=" + id)
      .then(response => response.json())
      .then(data => {
        if (data.existe) {
          mensajeNombre.textContent = "❌ Ya existe otra sede con ese nombre.";
          boton.disabled = true;
        } else {
          mensajeNombre.textContent = "";
          boton.disabled = false;
        }
      })
      .catch(() => {
        mensajeNombre.textContent = "⚠️ No se pudo verificar el nombre.";
        boton.disabled = false;
      });
  });

  setTimeout(() => {
    const alertaExito = document.getElementById('alerta-exito');
    const alertaError = document.getElementById('alerta-error');
    if (alertaExito) alertaExito.remove();
    if (alertaError) alertaError.remove();
  }, 5000);
</script>
