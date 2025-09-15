<?php
if (!isset($_SESSION)) session_start();
require_once('../../../models/departamento.php');

$departamento = new Departamento();
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    $_SESSION['mensaje_error'] = "ID inválido.";
    header("Location: lista_dep.php");
    exit;
}

$dep   = $departamento->obtenerPorId($id);
$sedes = $departamento->listarSedesActivas();

$titulo_pagina = "Editar Departamento";
include_once('../../shared/header.php');

/* Mensajes (opcional, si tu controlador los setea) */
$flash_ok    = $_SESSION['dep_editado']    ?? null;   // opcional
$flash_error = $_SESSION['error_edicion']  ?? null;   // opcional
unset($_SESSION['dep_editado'], $_SESSION['error_edicion']);
?>

<style>
/* ====== Hero y contenedor, consistente con el resto ====== */
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

/* Input con icono */
.input-icon .input-group-text{background:#fff;border-right:0;color:#94a3b8}
.input-icon .form-control{border-left:0}

/* Mensaje de validación inline */
#mensajeNombre{min-height:1.2rem}

/* Botonera inferior */
.form-actions{display:flex;gap:.5rem;justify-content:space-between;flex-wrap:wrap}
.form-actions .right{display:flex;gap:.5rem}

/* Mayúsculas con UX */
.text-uppercase{ text-transform: uppercase; }
</style>

<div class="container py-4" style="max-width:1100px">
  <!-- Hero -->
  <div class="page-head mb-3">
    <div class="hero">
      <div class="hero-icon">✏️</div>
      <div>
        <h1 class="title">Editar Departamento</h1>
        <p class="subtitle">Actualiza la información del área</p>
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

    <!-- (Si tu backend coloca mensajes, los mostramos como inline ocultos y también vía SweetAlert) -->
    <?php if ($flash_ok): ?>
      <div class="alert alert-success text-center fw-bold d-none" id="alert-inline-ok">
        ✅ <?= htmlspecialchars($flash_ok) ?>
      </div>
    <?php elseif ($flash_error): ?>
      <div class="alert alert-danger text-center fw-bold d-none" id="alert-inline-err">
        ❌ <?= htmlspecialchars($flash_error) ?>
      </div>
    <?php endif; ?>

    <form id="formEditarDep" method="POST" action="actualiza_dep.php" autocomplete="off">
      <input type="hidden" name="id" value="<?= (int)$dep['id'] ?>">

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre del Departamento</label>
          <div class="input-group input-icon">
            <span class="input-group-text">🏷️</span>
            <input
              type="text"
              name="nombre"
              class="form-control text-uppercase"
              id="nombreDep"
              value="<?= htmlspecialchars($dep['nombre']) ?>"
              required
              placeholder="EJ. SISTEMAS">
          </div>
          <div id="mensajeNombre" class="text-danger mt-1" style="font-size: 0.9rem;"></div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Sede</label>
          <div class="input-group input-icon">
            <span class="input-group-text">📍</span>
            <select name="sede_id" class="form-select" id="sedeDep" required>
              <option value="">Seleccione una sede</option>
              <?php foreach ($sedes as $sede): ?>
                <option value="<?= $sede['id'] ?>" <?= ($sede['id'] == $dep['sede_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($sede['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="col-md-12">
          <label class="form-label">Descripción</label>
          <textarea name="descripcion" rows="3" class="form-control text-uppercase" required placeholder="Describe funciones, alcances, etc."><?= htmlspecialchars($dep['descripcion']) ?></textarea>
        </div>
      </div>

      <div class="mt-4 form-actions">
        <a href="lista_dep.php" class="btn btn-outline-secondary">← Cancelar</a>
        <div class="right">
          <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php if ($flash_ok || $flash_error): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
  icon: '<?= $flash_ok ? 'success' : 'error' ?>',
  title: '<?= addslashes($flash_ok ?: $flash_error) ?>',
  timer: 1800, showConfirmButton: false
});
</script>
<?php endif; ?>

<script>
// Mantén tu comportamiento de MAYÚSCULAS con soporte de acentos/ñ
document.querySelectorAll('.text-uppercase').forEach(input => {
  input.addEventListener('input', () => {
    input.value = input.value.toLocaleUpperCase('es-MX');
  });
});

const nombreInput   = document.getElementById("nombreDep");
const sedeSelect    = document.getElementById("sedeDep");
const mensajeNombre = document.getElementById("mensajeNombre");

// ✅ Validación de duplicados (sin cambiar el endpoint: actualiza_dep.php)
function validarNombreEditando() {
  const nombre = (nombreInput.value || '').trim().toUpperCase();
  const sedeId = (sedeSelect.value || '').trim();
  const id     = <?= (int)$dep['id'] ?>;

  if (nombre === '' || sedeId === '') {
    mensajeNombre.textContent = '';
    nombreInput.classList.remove('is-invalid','is-valid');
    return;
  }

  fetch(`actualiza_dep.php?verificar_nombre=${encodeURIComponent(nombre)}&sede_id=${encodeURIComponent(sedeId)}&id=${encodeURIComponent(id)}`)
    .then(res => res.json())
    .then(data => {
      if (data.existe) {
        mensajeNombre.textContent = '❌ Ya existe un departamento con ese nombre en esta sede.';
        nombreInput.classList.add('is-invalid');
        nombreInput.classList.remove('is-valid');
      } else {
        mensajeNombre.textContent = '';
        nombreInput.classList.remove('is-invalid');
        nombreInput.classList.add('is-valid');
      }
    })
    .catch(() => {
      mensajeNombre.textContent = '⚠ Error al verificar el nombre.';
      nombreInput.classList.add('is-invalid');
    });
}

nombreInput.addEventListener('blur', validarNombreEditando);
sedeSelect.addEventListener('change', validarNombreEditando);
</script>
