<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once('../../../models/Sede.php');

/* ====== Título + Header compartido ====== */
$tituloPagina = "Editar Sede"; // usar camelCase igual que en header.php
include_once('../../shared/header.php');

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

/* ====== Mensajes de sesión ====== */
$mensaje_exito = $_SESSION['sede_editada'] ?? null;
$mensaje_error = $_SESSION['error_edicion'] ?? null;
unset($_SESSION['sede_editada'], $_SESSION['error_edicion']);
?>

<style>
:root{ --nav-h: 64px; }

/* Hotfix navbar siempre arriba e interactuable */
.navbar-nexus{
  position: sticky;
  top: 0;
  z-index: 1100 !important;
}

/* ====== Hero/Encabezado interno ====== */
.page-head{
  display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;
  padding:.85rem 1rem;border-radius:16px;
  background: rgba(255,255,255,.18);
  border: 1px solid rgba(255,255,255,.35);
  backdrop-filter: blur(8px);
  box-shadow: 0 6px 16px rgba(0,0,0,.12);
  position: relative; z-index: 3; /* sobre fondo */
}
.hero{display:flex;align-items:center;gap:.8rem}
.hero .hero-icon{
  width:46px;height:46px;border-radius:12px;display:grid;place-items:center;
  background: linear-gradient(135deg, #0D6EFD, #6ea8fe);
  color:#fff;font-size:1.25rem;box-shadow:0 6px 14px rgba(13,110,253,.35)
}
.hero .title{
  margin:0;line-height:1.1;font-weight:900;letter-spacing:.2px;
  font-size: clamp(1.6rem, 2.2vw + .6rem, 2.2rem);
  background: linear-gradient(90deg,#ffffff 0%, #e6f0ff 60%, #fff);
  -webkit-background-clip:text;background-clip:text;color:transparent;
  text-shadow:0 1px 0 rgba(0,0,0,.12)
}
.hero .subtitle{margin:0;color:#e8eef7;font-size:.95rem;font-weight:500;opacity:.95}

/* ====== Card/Form ====== */
.form-card{
  border-radius:16px;border:1px solid #e5e7eb;
  box-shadow:0 8px 24px rgba(0,0,0,.08);
  background: rgba(255,255,255,.88);
  position: relative; z-index: 2;
}
.section-title{
  font-weight:800;color:#0D6EFD;letter-spacing:.2px;margin-bottom:.75rem;
  display:flex;align-items:center;gap:.5rem
}
.section-title .dot{width:.6rem;height:.6rem;border-radius:50%;background:#0D6EFD;display:inline-block}

/* Inputs con icono */
.input-icon .input-group-text{
  background:#fff;border-right:0;color:#94a3b8;
}
.input-icon .form-control{
  border-left:0;
}

/* Botones */
.btn-primary{ background:#0D6EFD; border-color:#0D6EFD; }
.btn-primary:hover{ background:#0b5ed7; border-color:#0b5ed7; }
.btn-outline-info{ border-color:#0D6EFD; color:#0D6EFD; }
.btn-outline-info:hover{ background:#0D6EFD; color:#fff; }

/* Ayudas / validación ligera */
.help{ font-size:.88rem; color:#64748b; }
#mensajeNombre{ min-height: 1.2rem; }

/* Ocultamos alertas Bootstrap (usaremos SweetAlert) pero quedan como fallback */
.bootstrap-flash { display:none; }
</style>

<div class="container py-4" style="max-width:1100px; position:relative; z-index:2;">
  <!-- Encabezado interno -->
  <div class="page-head">
    <div class="hero">
      <div class="hero-icon">✏️</div>
      <div>
        <h1 class="title">Editar Sede</h1>
        <p class="subtitle">Actualiza la información de la ubicación</p>
      </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a href="lista_sedes.php" class="btn btn-outline-info">📋 Lista de Sedes</a>
      <a href="lista_sedes.php" class="btn btn-outline-secondary">← Regresar</a>
    </div>
  </div>

  <div class="card form-card shadow p-4 mt-3">
    <!-- Fallback (oculto) por si falla SweetAlert -->
    <?php if ($mensaje_exito): ?>
      <div class="alert alert-success text-center fw-bold bootstrap-flash" id="alerta-exito">
        <?= htmlspecialchars($mensaje_exito) ?>
      </div>
    <?php elseif ($mensaje_error): ?>
      <div class="alert alert-danger text-center fw-bold bootstrap-flash" id="alerta-error">
        <?= htmlspecialchars($mensaje_error) ?>
      </div>
    <?php endif; ?>

    <h2 class="section-title"><span class="dot"></span>Datos de la sede</h2>

    <form id="formEditarSede" method="POST" action="actualiza_sede.php" autocomplete="off" class="mt-3">
      <input type="hidden" name="id" value="<?= (int)$datos['id'] ?>">

      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre de la sede</label>
          <div class="input-group input-icon">
            <span class="input-group-text">🏷️</span>
            <input type="text" name="nombre" class="form-control text-uppercase" id="nombreSede"
                   value="<?= htmlspecialchars($datos['nombre']) ?>" required>
          </div>
          <div id="mensajeNombre" class="mt-1"></div>
          <div class="help">Debe ser único en el sistema.</div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Domicilio (calle)</label>
          <div class="input-group input-icon">
            <span class="input-group-text">📫</span>
            <input type="text" name="domicilio" class="form-control text-uppercase" required
                   value="<?= htmlspecialchars($datos['domicilio']) ?>">
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Número exterior</label>
          <div class="input-group input-icon">
            <span class="input-group-text">#</span>
            <input type="text" name="numero" class="form-control text-uppercase" required
                   value="<?= htmlspecialchars($datos['numero']) ?>">
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Número interior (opcional)</label>
          <div class="input-group input-icon">
            <span class="input-group-text">#</span>
            <input type="text" name="interior" class="form-control text-uppercase"
                   value="<?= htmlspecialchars($datos['interior']) ?>">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Colonia</label>
          <div class="input-group input-icon">
            <span class="input-group-text">🏘️</span>
            <input type="text" name="colonia" class="form-control text-uppercase" required
                   value="<?= htmlspecialchars($datos['colonia']) ?>">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Municipio</label>
          <div class="input-group input-icon">
            <span class="input-group-text">🧭</span>
            <input type="text" name="municipio" class="form-control text-uppercase" required
                   value="<?= htmlspecialchars($datos['municipio']) ?>">
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Estado</label>
          <div class="input-group input-icon">
            <span class="input-group-text">🗺️</span>
            <input type="text" name="estado" class="form-control text-uppercase" required
                   value="<?= htmlspecialchars($datos['estado']) ?>">
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Código Postal</label>
          <div class="input-group input-icon">
            <span class="input-group-text">🏷️</span>
            <input type="text" name="cp" class="form-control" required pattern="\d{5}"
                   value="<?= htmlspecialchars($datos['cp']) ?>" placeholder="#####">
          </div>
          <div class="help">5 dígitos (ej. 01234)</div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Teléfono (10 dígitos)</label>
          <div class="input-group input-icon">
            <span class="input-group-text">📞</span>
            <input type="tel" name="telefono" class="form-control" required pattern="[0-9]{10}"
                   value="<?= htmlspecialchars($datos['telefono']) ?>" placeholder="5512345678">
          </div>
          <div class="help">Solo números, sin espacios.</div>
        </div>
      </div>

      <div class="mt-4 d-flex justify-content-between flex-wrap gap-2">
        <a href="lista_sedes.php" class="btn btn-outline-secondary">← Regresar</a>
        <button type="submit" class="btn btn-primary" id="btnGuardar">Actualizar Sede</button>
      </div>
    </form>
  </div>
</div>

<!-- SweetAlert para mensajes y validaciones visuales -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Uppercase con soporte ES-MX (incluye ñ/tildes)
  document.querySelectorAll('.text-uppercase').forEach(input => {
    input.addEventListener('input', () => {
      input.value = input.value.toLocaleUpperCase('es-MX');
    });
  });

  // Helpers de mensajes inline
  function setMsg(el, text, type){
    el.textContent = text || "";
    el.className = "";
    el.classList.add(type === 'ok' ? 'text-success' : (type === 'warn' ? 'text-warning' : 'text-danger'));
    el.style.fontSize = ".9rem";
  }

  // Verificación de nombre (usa tu endpoint actual)
  const nombreInput = document.getElementById("nombreSede");
  const mensajeNombre = document.getElementById("mensajeNombre");
  const boton = document.getElementById("btnGuardar");
  const sedeId = <?= (int)$datos['id'] ?>;

  nombreInput.addEventListener("blur", function () {
    const nombre = this.value.trim().toUpperCase();

    if (nombre.length === 0) {
      setMsg(mensajeNombre, "", "ok");
      boton.disabled = false;
      return;
    }

    boton.disabled = true;
    // Si tu actualiza_sede.php soporta la verificación, mantenemos este endpoint:
    fetch("actualiza_sede.php?verificar_nombre=1&nombre=" + encodeURIComponent(nombre) + "&id=" + sedeId)
      .then(response => response.json())
      .then(data => {
        if (data.existe) {
          setMsg(mensajeNombre, "❌ Ya existe otra sede con ese nombre.", "error");
          boton.disabled = true;
        } else {
          setMsg(mensajeNombre, "✓ Disponible", "ok");
          boton.disabled = false;
        }
      })
      .catch(() => {
        setMsg(mensajeNombre, "⚠️ No se pudo verificar el nombre.", "warn");
        boton.disabled = false;
      });
  });

  // Toasts de éxito/error desde PHP
  const flashOK  = <?= $mensaje_exito ? json_encode($mensaje_exito, JSON_UNESCAPED_UNICODE) : 'null' ?>;
  const flashERR = <?= $mensaje_error ? json_encode($mensaje_error, JSON_UNESCAPED_UNICODE) : 'null' ?>;

  if (flashOK || flashERR){
    Swal.fire({
      icon: flashOK ? 'success' : 'error',
      title: flashOK || flashERR,
      timer: 1900,
      showConfirmButton: false
    });
  }

  // UX de envío
  document.getElementById('formEditarSede').addEventListener('submit', function(){
    boton.disabled = true;
    boton.innerText = 'Actualizando…';
  });
</script>

<?php
// ===== Footer compartido (carga Bootstrap Bundle JS para navbar/dropdowns) =====
$footer = __DIR__ . '/../../shared/footer.php';
if (is_file($footer)) {
  require_once $footer;
} else {
  // Fallback: incluir Bootstrap Bundle desde CDN si no tienes footer
  echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>';
}
?>
