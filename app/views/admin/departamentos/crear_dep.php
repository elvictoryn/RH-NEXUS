<?php
// ============================================================
// Crear Departamento - Nexus RH
// Ruta: /app/views/admin/departamentos/crear_dep.php
// ============================================================
define('BASE_PATH','/sistema_rh'); // <-- ajusta si tu carpeta cambia
if (session_status() === PHP_SESSION_NONE) session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/models/departamento.php';

// ✅ Endpoint AJAX de verificación
if (isset($_GET['verificar_nombre']) && isset($_GET['sede_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    $nombre  = strtoupper(trim($_GET['verificar_nombre']));
    $sede_id = intval($_GET['sede_id']);
    $departamento = new Departamento();
    echo json_encode(['existe' => $departamento->existeNombreEnSede($nombre, $sede_id)]);
    exit;
}

$tituloPagina = 'Registrar Departamento';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/header.php';
// ↑ header.php ya inyecta: navbar

$departamento = new Departamento();
$sedes = $departamento->listarSedesActivas();

// ===== Flash (SweetAlert) =====
// Soporta varios nombres por compatibilidad con tu backend actual
$flash_ok  = $_SESSION['dep_ok']
          ?? $_SESSION['departamento_ok']
          ?? $_SESSION['departamento_guardado']
          ?? $_SESSION['ok']
          ?? null;

$flash_err = $_SESSION['dep_err']
          ?? $_SESSION['departamento_error']
          ?? $_SESSION['error_guardado']
          ?? $_SESSION['error']
          ?? null;

// Flag para resetear el form después de guardar
$form_reset = !empty($_SESSION['form_reset']);

// Limpia flashes
unset($_SESSION['dep_ok'], $_SESSION['departamento_ok'], $_SESSION['departamento_guardado'], $_SESSION['ok'],
      $_SESSION['dep_err'], $_SESSION['departamento_error'], $_SESSION['error_guardado'], $_SESSION['error'],
      $_SESSION['form_reset']);
?>

<div class="layout-overlay">
  <div class="container py-4" style="max-width:1100px">

    <!-- Encabezado -->
    <div class="glass-card p-3 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-center gap-3">
        <div class="d-grid place-items-center rounded-3 px-3 py-2 text-white" style="background:linear-gradient(135deg,#0D6EFD,#6ea8fe);">
          🧩
        </div>
        <div>
          <h1 class="h3 mb-0 fw-bold text-dark">Departamentos</h1>
          <p class="text-muted mb-0">➕ Registrar nuevo departamento</p>
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="<?php echo BASE_PATH; ?>/app/views/admin/departamentos/menu.php" class="btn btn-outline-secondary btn-sm">← Menú de Deps &amp; Sedes</a>
        <a href="<?php echo BASE_PATH; ?>/app/views/admin/departamentos/lista_dep.php" class="btn btn-outline-info btn-sm">📋 Lista de departamentos</a>
      </div>
    </div>

    <!-- Formulario -->
    <div class="glass-card p-4">
      <h2 class="h5 fw-bold text-primary mb-3">Datos del departamento</h2>

      <form id="formDep" method="POST" action="<?php echo BASE_PATH; ?>/app/views/admin/departamentos/guardar_dep.php" autocomplete="off">
        <div class="row g-3">
          <!-- Nombre -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Nombre del Departamento</label>
            <div class="input-group">
              <span class="input-group-text">🏷️</span>
              <input type="text" name="nombre" class="form-control text-uppercase" id="nombreDep" required placeholder="EJ. SISTEMAS">
            </div>
            <div id="mensajeNombre" class="mt-1" style="min-height:1.2rem;font-size:.9rem;"></div>
            <div class="text-muted small">Debe ser único dentro de la misma sede.</div>
          </div>

          <!-- Sede -->
          <div class="col-md-6">
            <label class="form-label fw-semibold">Sede</label>
            <div class="input-group">
              <span class="input-group-text">📍</span>
              <select name="sede_id" class="form-select" id="sedeDep" required>
                <option value="">Seleccione una sede</option>
                <?php foreach ($sedes as $sede): ?>
                  <option value="<?= (int)$sede['id'] ?>"><?= htmlspecialchars($sede['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Descripción -->
          <div class="col-12">
            <label class="form-label fw-semibold">Descripción</label>
            <textarea name="descripcion" rows="3" class="form-control text-uppercase" required placeholder="Describe funciones principales..."></textarea>
          </div>
        </div>

        <div class="row g-2 mt-4 justify-content-center">
          <div class="col-12 col-md-4 d-grid">
            <a href="<?php echo BASE_PATH; ?>/app/views/admin/departamentos/menu.php" class="btn btn-outline-secondary">← Cancelar</a>
          </div>
          <div class="col-12 col-md-4 d-grid">
            <a href="<?php echo BASE_PATH; ?>/app/views/admin/departamentos/lista_dep.php" class="btn btn-outline-info">📋 Lista Departamentos</a>
          </div>
          <div class="col-12 col-md-4 d-grid">
            <button class="btn btn-primary" id="btnGuardar">Crear</button>
          </div>
        </div>
      </form>
    </div>

  </div>
</div>

<!-- SweetAlert (único tipo de alerta visual) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// MAYÚSCULAS (incluye Ñ/tildes)
document.querySelectorAll('.text-uppercase').forEach(el=>{
  el.addEventListener('input',()=>{ el.value = el.value.toLocaleUpperCase('es-MX'); });
});

const nombreInput   = document.getElementById('nombreDep');
const sedeSelect    = document.getElementById('sedeDep');
const mensajeNombre = document.getElementById('mensajeNombre');
const botonGuardar  = document.getElementById('btnGuardar');

function setMsg(el, text, type){
  el.textContent = text || "";
  el.className = "";
  // Colores consistentes con el resto: success/warn/error
  if (type==='ok')   el.classList.add('text-success');
  else if (type==='warn') el.classList.add('text-warning');
  else if (type==='error') el.classList.add('text-danger');
}

// ✅ Validación de duplicados
function validarNombreDepartamento(){
  const nombre = (nombreInput.value||'').trim().toUpperCase();
  const sedeId = (sedeSelect.value||'').trim();

  if(!nombre || !sedeId){
    setMsg(mensajeNombre, "", "ok");
    nombreInput.classList.remove('is-invalid','is-valid');
    botonGuardar.disabled = false;
    return;
  }
  const url = new URL(window.location.href);
  url.searchParams.set('verificar_nombre', nombre);
  url.searchParams.set('sede_id', sedeId);

  fetch(url.toString())
    .then(r=>r.json())
    .then(data=>{
      if (data.existe){
        setMsg(mensajeNombre, '❌ Ya existe un departamento con ese nombre en esta sede.', 'error');
        nombreInput.classList.add('is-invalid'); nombreInput.classList.remove('is-valid');
        botonGuardar.disabled = true;
      } else {
        setMsg(mensajeNombre, '✓ Disponible', 'ok');
        nombreInput.classList.remove('is-invalid'); nombreInput.classList.add('is-valid');
        botonGuardar.disabled = false;
      }
    })
    .catch(()=>{
      setMsg(mensajeNombre, '⚠️ Error al verificar el nombre.', 'warn');
      nombreInput.classList.add('is-invalid');
      botonGuardar.disabled = false;
    });
}
nombreInput.addEventListener('blur', validarNombreDepartamento);
sedeSelect.addEventListener('change', validarNombreDepartamento);

// 🔄 Si venimos de un guardado exitoso, limpia el formulario
<?php if ($form_reset): ?>
  document.getElementById('formDep').reset();
  nombreInput.classList.remove('is-valid','is-invalid');
  setMsg(mensajeNombre, "", "ok");
<?php endif; ?>

// ===== SweetAlerts de servidor (flash) =====
const flashOK  = <?= $flash_ok  ? json_encode($flash_ok,  JSON_UNESCAPED_UNICODE) : 'null' ?>;
const flashERR = <?= $flash_err ? json_encode($flash_err, JSON_UNESCAPED_UNICODE) : 'null' ?>;

if (flashOK || flashERR){
  Swal.fire({
    icon: flashOK ? 'success' : 'error',
    title: flashOK || flashERR,
    timer: 1900,
    showConfirmButton: false
  });
}
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/footer.php'; ?>
