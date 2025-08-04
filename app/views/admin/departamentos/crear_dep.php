<?php
if (!isset($_SESSION)) session_start();
// Incluir sistema de rutas dinámicas
require_once(__DIR__ . '/../../../config/paths.php');

// Incluir modelo usando rutas dinámicas
safe_require_once(model_path('departamento'));

// Validación AJAX en tiempo real
if (isset($_GET['verificar_nombre']) && isset($_GET['sede_id'])) {
    $nombre = strtoupper(trim($_GET['verificar_nombre']));
    $sede_id = intval($_GET['sede_id']);

    $departamento = new Departamento();
    echo json_encode(['existe' => $departamento->existeNombreEnSede($nombre, $sede_id)]);
    exit;
}

$titulo_pagina = "Registrar Departamento";
// Incluir header usando rutas dinámicas
safe_include_once(shared_header_path());

$departamento = new Departamento();
$sedes = $departamento->listarSedesActivas();

$mensaje_exito = $_SESSION['dep_creado'] ?? null;
$mensaje_error = $_SESSION['error_creacion'] ?? null;
unset($_SESSION['dep_creado'], $_SESSION['error_creacion']);
?>

<div class="container mt-4">
  <div class="card shadow p-4 bg-light">
    <h2 class="text-primary mb-4">Registrar Departamento</h2>

    <?php if ($mensaje_exito): ?>
      <div class="alert alert-success text-center fw-bold"><?= htmlspecialchars($mensaje_exito) ?></div>
    <?php elseif ($mensaje_error): ?>
      <div class="alert alert-danger text-center fw-bold"><?= htmlspecialchars($mensaje_error) ?></div>
    <?php endif; ?>

    <form id="formDep" method="POST" action="guardar_dep.php" autocomplete="off">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre del Departamento</label>
          <input type="text" name="nombre" class="form-control text-uppercase" id="nombreDep" required>
          <div id="mensajeNombre" class="text-danger mt-1" style="font-size: 0.9rem;"></div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Sede</label>
          <select name="sede_id" class="form-select" id="sedeDep" required>
            <option value="">Seleccione una sede</option>
            <?php foreach ($sedes as $sede): ?>
              <option value="<?= $sede['id'] ?>"><?= htmlspecialchars($sede['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-12">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" rows="3" class="form-control text-uppercase" required></textarea>
        </div>
      </div>
      <div class="mt-4 d-flex justify-content-between">
        <a href="lista_dep.php" class="btn btn-outline-secondary">← Cancelar</a>
        <a href="lista_dep.php" class="btn btn-outline-info">📋 Lista de departamentos</a>
        <button type="submit" class="btn btn-success" id="btnGuardar">Guardar Departamento</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Convierte a mayúsculas (incluye tildes y ñ)
  document.querySelectorAll('.text-uppercase').forEach(input => {
    input.addEventListener('input', () => {
      input.value = input.value.toLocaleUpperCase('es-MX');
    });
  });

  const nombreInput = document.getElementById("nombreDep");
  const sedeSelect = document.getElementById("sedeDep");
  const mensajeNombre = document.getElementById("mensajeNombre");
  const botonGuardar = document.getElementById("btnGuardar");

  function validarNombreDepartamento() {
    const nombre = nombreInput.value.trim().toUpperCase();
    const sedeId = sedeSelect.value;

    if (nombre === '' || sedeId === '') {
      mensajeNombre.textContent = '';
      botonGuardar.disabled = false;
      return;
    }

    fetch(`crear_dep.php?verificar_nombre=${encodeURIComponent(nombre)}&sede_id=${sedeId}`)
      .then(response => response.json())
      .then(data => {
        if (data.existe) {
          mensajeNombre.textContent = '❌ Ya existe un departamento con ese nombre en esta sede.';
          botonGuardar.disabled = true;
        } else {
          mensajeNombre.textContent = '';
          botonGuardar.disabled = false;
        }
      })
      .catch(() => {
        mensajeNombre.textContent = '⚠ Error al verificar el nombre.';
        botonGuardar.disabled = false;
      });
  }

  nombreInput.addEventListener('blur', validarNombreDepartamento);
  sedeSelect.addEventListener('change', validarNombreDepartamento);

  setTimeout(() => {
    document.querySelector('.alert-success')?.remove();
    document.querySelector('.alert-danger')?.remove();
  }, 5000);
</script>
