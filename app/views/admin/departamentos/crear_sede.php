<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../models/Sede.php';

/* ====== Endpoint AJAX: verificar nombre único ====== */
if (isset($_GET['verificar_nombre'])) {
    $nombre = strtoupper(trim($_GET['verificar_nombre']));
    $sede = new Sede();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['existe' => $sede->existeNombre($nombre)]);
    exit;
}

/* ====== Título + Header compartido ====== */
$tituloPagina = "Registrar Sede"; // camelCase (coincide con header.php)
require_once __DIR__ . '/../../shared/header.php';

/* ====== Mensajes de sesión ====== */
$mensaje_exito = $_SESSION['sede_guardada'] ?? null;
$mensaje_error = $_SESSION['error_guardado'] ?? null;
unset($_SESSION['sede_guardada'], $_SESSION['error_guardado']);
?>
<style>
:root{ --nav-h: 64px; }

/* Hotfix mínimo: navbar siempre arriba e interactuable */
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
  position: relative; z-index: 3;
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
  background: rgba(255,255,255,.9);
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
      <div class="hero-icon">🏢</div>
      <div>
        <h1 class="title">Registrar Sede</h1>
        <p class="subtitle">Captura de datos y validaciones</p>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a href="lista_sedes.php" class="btn btn-outline-info">📋 Lista de Sedes</a>
      <a href="menu.php" class="btn btn-outline-secondary">← Regresar</a>
    </div>
  </div>

  <div class="card form-card shadow p-4 mt-3">
    <!-- Fallback (oculto) por si falla SweetAlert -->
    <?php if ($mensaje_exito): ?>
      <div class="alert alert-success text-center fw-bold bootstrap-flash">✅ <?= htmlspecialchars($mensaje_exito) ?></div>
    <?php elseif ($mensaje_error): ?>
      <div class="alert alert-danger text-center fw-bold bootstrap-flash">❌ <?= htmlspecialchars($mensaje_error) ?></div>
    <?php endif; ?>

    <h2 class="section-title"><span class="dot"></span>Datos generales</h2>

    <form id="formSede" method="POST" action="guardar_sede.php" autocomplete="off" class="mt-3">
      <div class="row g-3">

        <div class="col-md-6">
          <label class="form-label">Nombre de la sede</label>
          <div class="input-group input-icon">
            <span class="input-group-text">🏷️</span>
            <input type="text" name="nombre" class="form-control text-uppercase" id="nombreSede" required placeholder="EJ. MATRIZ CENTRO">
          </div>
          <div id="mensajeNombre" class="mt-1"></div>
          <div class="help">Debe ser único en el sistema.</div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Domicilio (calle)</label>
          <div class="input-group input-icon">
            <span class="input-group-text">📫</span>
            <input type="text" name="domicilio" class="form-control text-uppercase" required>
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Número exterior</label>
          <div class="input-group input-icon">
            <span class="input-group-text">#</span>
            <input type="text" name="numero" class="form-control text-uppercase" required>
          </div>
        </div>

        <div class="col-md-3">
          <label class="form-label">Número interior (opcional)</label>
          <div class="input-group input-icon">
            <span class="input-group-text">#</span>
            <input type="text" name="interior" class="form-control text-uppercase">
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Colonia</label>
          <div class="input-group input-icon">
            <span class="input-group-text">🏘️</span>
            <input type="text" name="colonia" class="form-control text-uppercase" required>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Municipio</label>
          <div class="input-group input-icon">
            <span class="input-group-text">🧭</span>
            <input type="text" name="municipio" class="form-control text-uppercase" required>
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Estado</label>
          <div class="input-group input-icon">
            <span class="input-group-text">🗺️</span>
            <input type="text" name="estado" class="form-control text-uppercase" required>
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Código Postal</label>
          <div class="input-group input-icon">
            <span class="input-group-text">🏷️</span>
            <input type="text" name="cp" class="form-control" required pattern="\d{5}" title="Debe contener 5 dígitos" placeholder="#####">
          </div>
          <div class="help">5 dígitos (ej. 01234)</div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Teléfono (10 dígitos)</label>
          <div class="input-group input-icon">
            <span class="input-group-text">📞</span>
            <input type="tel" name="telefono" class="form-control" required pattern="[0-9]{10}" title="Debe contener 10 dígitos" placeholder="5512345678">
          </div>
          <div class="help">Solo números, sin espacios.</div>
        </div>
      </div>

      <div class="row g-2 mt-4 justify-content-center actions-3">
        <div class="col-12 col-md-4 d-grid">
          <a href="menu.php" class="btn btn-outline-secondary btn-eq">← Cancelar</a>
        </div>
        <div class="col-12 col-md-4 d-grid">
          <a href="lista_sedes.php" class="btn btn-outline-info btn-eq">📋 Lista de Sedes</a>
        </div>
        <div class="col-12 col-md-4 d-grid">
          <button class="btn btn-primary btn-eq" id="btnGuardar">Crear</button>
        </div>
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

  // Verificación de nombre (AJAX)
  const nombreInput = document.getElementById("nombreSede");
  const btnGuardar  = document.getElementById("btnGuardar");
  const msgNombre   = document.getElementById("mensajeNombre");

  function setMsg(el, text, type){
    el.textContent = text || "";
    el.className = "";
    el.classList.add(type === 'ok' ? 'text-success' : (type === 'warn' ? 'text-warning' : 'text-danger'));
    el.style.fontSize = ".9rem";
  }

  nombreInput.addEventListener("blur", verificarNombre);
  nombreInput.addEventListener("keyup", (e)=>{
    if (e.target.value.trim()==="") { setMsg(msgNombre, "", "ok"); btnGuardar.disabled = false; }
  });

  function verificarNombre(){
    const nombre = nombreInput.value.trim().toUpperCase();
    if (nombre.length === 0) {
      setMsg(msgNombre, "", "ok");
      btnGuardar.disabled = false;
      return;
    }
    btnGuardar.disabled = true;
    fetch(window.location.pathname + "?verificar_nombre=" + encodeURIComponent(nombre))
      .then(r => r.json())
      .then(data => {
        if (data.existe) {
          setMsg(msgNombre, "❌ Ya existe una sede registrada con ese nombre.", "error");
          btnGuardar.disabled = true;
        } else {
          setMsg(msgNombre, "✓ Disponible", "ok");
          btnGuardar.disabled = false;
        }
      })
      .catch(() => {
        setMsg(msgNombre, "⚠️ Error al verificar el nombre.", "warn");
        btnGuardar.disabled = false;
      });
  }

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
  document.getElementById('formSede').addEventListener('submit', function(){
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = 'Guardando…';
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
