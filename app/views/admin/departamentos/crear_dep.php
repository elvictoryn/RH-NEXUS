<?php
if (!isset($_SESSION)) session_start();
require_once('../../../models/departamento.php');

// ✅ Validación AJAX (la dejo igual)
if (isset($_GET['verificar_nombre']) && isset($_GET['sede_id'])) {
    $nombre = strtoupper(trim($_GET['verificar_nombre']));
    $sede_id = intval($_GET['sede_id']);
    $departamento = new Departamento();
    echo json_encode(['existe' => $departamento->existeNombreEnSede($nombre, $sede_id)]);
    exit;
}

$titulo_pagina = "Registrar Departamento";
include_once('../../shared/header.php');

$departamento = new Departamento();
$sedes = $departamento->listarSedesActivas();

/* Flash (mensajes de sesión) */
$mensaje_exito = $_SESSION['dep_creado'] ?? null;
$mensaje_error = $_SESSION['error_creacion'] ?? null;
unset($_SESSION['dep_creado'], $_SESSION['error_creacion']);
?>

<style>
/* ====== Hero y contenedor elegante, consistente con el resto ====== */
.page-head{
  display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;
  padding:.85rem 1rem;border-radius:16px;
  background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);backdrop-filter:blur(8px);
  box-shadow:0 6px 16px rgba(0,0,0,.12)
}
.hero{display:flex;align-items:center;gap:.8rem}
.hero .hero-icon{
  width:46px;height:46px;border-radius:12px;display:grid;place-items:center;
  background:linear-gradient(135deg,#0D6EFD,#6ea8fe);color:#fff;font-size:1.25rem;
  box-shadow:0 6px 14px rgba(13,110,253,.35)
}
.hero .title{
  margin:0;line-height:1.1;font-weight:900;letter-spacing:.2px;
  font-size:clamp(1.8rem, 2.6vw + .6rem, 2.6rem);
  background:linear-gradient(90deg,#ffffff 0%, #e6f0ff 60%, #fff);
  -webkit-background-clip:text;background-clip:text;color:transparent;
  text-shadow:0 1px 0 rgba(0,0,0,.12)
}
.hero .subtitle{margin:0;color:#e8eef7;font-size:.95rem;font-weight:500;opacity:.95}

/* Tarjeta formulario */
.form-card{background:rgba(255,255,255,.9);border:1px solid #e5e7eb;border-radius:16px;box-shadow:0 6px 18px rgba(0,0,0,.08)}
.form-title{font-weight:800;color:#0D6EFD}
.help{font-size:.85rem;color:#64748b}

/* Input con icono */
.input-icon .input-group-text{background:#fff;border-right:0;color:#94a3b8}
.input-icon .form-control{border-left:0}

/* Mensaje de validación inline */
#mensajeNombre{min-height:1.2rem}

/* Botonera inferior */
.form-actions{display:flex;gap:.5rem;justify-content:space-between;flex-wrap:wrap}
.form-actions .right{display:flex;gap:.5rem}

/* Pequeños toques */
.text-uppercase{ text-transform: uppercase; }
</style>

<div class="container py-4" style="max-width:1100px">
  <!-- Hero -->
  <div class="page-head mb-3">
    <div class="hero">
      <div class="hero-icon">🧩</div>
      <div>
        <h1 class="title">Departamentos</h1>
        <p class="subtitle">➕ Registrar nuevo departamento</p>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <a href="lista_dep.php" class="btn btn-outline-secondary">📋 Lista</a>
      <a href="lista_dep.php" class="btn btn-outline-dark">← Regresar</a>
    </div>
  </div>

  <!-- Formulario -->
  <div class="form-card p-4">
    <h2 class="form-title mb-3">Datos del departamento</h2>

    <!-- (Mantengo las alertas existentes, pero las oculto si habrá SweetAlert) -->
    <?php if ($mensaje_exito): ?>
      <div class="alert alert-success text-center fw-bold d-none" id="alert-inline-ok">
        ✅ <?= htmlspecialchars($mensaje_exito) ?>
      </div>
    <?php elseif ($mensaje_error): ?>
      <div class="alert alert-danger text-center fw-bold d-none" id="alert-inline-err">
        ❌ <?= htmlspecialchars($mensaje_error) ?>
      </div>
    <?php endif; ?>

    <form id="formDep" method="POST" action="guardar_dep.php" autocomplete="off">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre del Departamento</label>
          <div class="input-group input-icon">
            <span class="input-group-text">🏷️</span>
            <input type="text" name="nombre" class="form-control text-uppercase" id="nombreDep" required placeholder="NOMBRE DEL DEARTAMENTO EJ. SISTEMAS">
          </div>
          <div id="mensajeNombre" class="text-danger mt-1" style="font-size: 0.9rem;"></div>
          <div class="help">Debe ser único dentro de la misma sede.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Sede</label>
          <div class="input-group input-icon">
            <span class="input-group-text">📍</span>
            <select name="sede_id" class="form-select" id="sedeDep" required>
              <option value="">Seleccione una sede</option>
              <?php foreach ($sedes as $sede): ?>
                <option value="<?= $sede['id'] ?>"><?= htmlspecialchars($sede['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="col-md-12">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" rows="3" class="form-control text-uppercase" required placeholder="Describe funciones principales..."></textarea>
        </div>
      </div>

      <div class="mt-4 form-actions">
        <a href="lista_dep.php" class="btn btn-outline-secondary">← Cancelar</a>
        <div class="right">
          <a href="lista_dep.php" class="btn btn-outline-info">📋 Ver lista</a>
          <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar departamento</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php if ($mensaje_exito || $mensaje_error): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Toast bonito (mismo que venimos usando)
  Swal.fire({
    icon: '<?= $mensaje_exito ? 'success' : 'error' ?>',
    title: '<?= addslashes($mensaje_exito ?: $mensaje_error) ?>',
    timer: 1800, showConfirmButton: false
  });
</script>
<?php endif; ?>

<script>
// Convierte a MAYÚSCULAS (incluye Ñ y tildes)
document.querySelectorAll('.text-uppercase').forEach(input => {
  input.addEventListener('input', () => {
    input.value = input.value.toLocaleUpperCase('es-MX');
  });
});

const nombreInput   = document.getElementById("nombreDep");
const sedeSelect    = document.getElementById("sedeDep");
const mensajeNombre = document.getElementById("mensajeNombre");
const botonGuardar  = document.getElementById("btnGuardar");

// ✅ Validación de duplicados (sin cambiar tu endpoint: crear_dep.php)
function validarNombreDepartamento() {
  const nombre = (nombreInput.value || '').trim().toUpperCase();
  const sedeId = (sedeSelect.value || '').trim();

  if (nombre === '' || sedeId === '') {
    mensajeNombre.textContent = '';
    nombreInput.classList.remove('is-invalid','is-valid');
    botonGuardar.disabled = false;
    return;
  }

  fetch(`crear_dep.php?verificar_nombre=${encodeURIComponent(nombre)}&sede_id=${encodeURIComponent(sedeId)}`)
    .then(r => r.json())
    .then(data => {
      if (data.existe) {
        mensajeNombre.textContent = '❌ Ya existe un departamento con ese nombre en esta sede.';
        nombreInput.classList.add('is-invalid');
        nombreInput.classList.remove('is-valid');
        botonGuardar.disabled = true;
      } else {
        mensajeNombre.textContent = '';
        nombreInput.classList.remove('is-invalid');
        nombreInput.classList.add('is-valid');
        botonGuardar.disabled = false;
      }
    })
    .catch(() => {
      mensajeNombre.textContent = '⚠ Error al verificar el nombre.';
      nombreInput.classList.add('is-invalid');
      botonGuardar.disabled = false;
    });
}

nombreInput.addEventListener('blur', validarNombreDepartamento);
sedeSelect.addEventListener('change', validarNombreDepartamento);

// Auto-ocultar alertas inline si llegaran a mostrarse
setTimeout(() => {
  document.getElementById('alert-inline-ok')?.remove();
  document.getElementById('alert-inline-err')?.remove();
}, 5000);
</script>
