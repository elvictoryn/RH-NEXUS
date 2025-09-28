<?php
// ===== Menú Departamentos & Sedes (con navbar) =====
define('BASE_PATH','/sistema_rh'); // ajusta si tu carpeta cambia
$tituloPagina = 'Gestión de Departamentos y Sedes - Nexus RH';

if (session_status() === PHP_SESSION_NONE) session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/navbar.php';

// (Opcional) Restringir por rol:
if (strtolower($_SESSION['rol'] ?? '') !== 'admin') {
  header('Location: '.BASE_PATH.'/public/dashboard.php'); exit;
}
?>
<style>
/* ===== Base y paleta claras ===== */
:root{
  --bg:#f2f4f6;
  --panel:#ffffff;
  --line:#e3e7ee;
  --ink:#143047;
  --muted:#6a7a8b;
  --blue:#0D6EFD;     /* Departamentos */
  --green:#0a9f7f;    /* Sedes */
  --neutral:#cbd5e1;  /* Neutro */
}
/* Fondo liso para esta vista (opcional) */
/* body{ background:var(--bg) !important; } */
.wrapper{ max-width:1200px; margin-inline:auto; padding:24px 16px }

/* ===== Banner superior ===== */
.welcome{
  max-width:1060px; margin:12px auto 28px; background:var(--panel);
  border:1px solid var(--line); border-radius:18px; padding:16px 18px;
  text-align:center; box-shadow:0 8px 22px rgba(0,0,0,.08);
}
.welcome h1{
  margin:0; font-weight:900; color:var(--ink);
  font-size:clamp(1.4rem, 2.2vw + .6rem, 2.2rem);
}
.welcome p{ margin:.35rem 0 0; color:#8b6b2c; font-weight:800 }

/* ===== Tarjetas “pastilla” con semicírculo izquierdo ===== */
.pill{
  position:relative; display:flex; align-items:center; gap:14px;
  background:var(--panel); border:1px solid var(--line); border-radius:18px;
  padding:16px 16px 16px calc(16px + clamp(68px, 14vw, 110px));
  box-shadow:0 10px 24px rgba(0,0,0,.10); min-height:110px; overflow:hidden;
}
.pill::before{
  content:""; position:absolute; left:0; top:0; bottom:0; width:clamp(68px, 14vw, 110px);
  background:var(--blue); border-top-left-radius:18px; border-bottom-left-radius:18px;
  border-top-right-radius:56px; border-bottom-right-radius:56px;
}
.pill.is-green::before{ background:var(--green) }
.pill.is-neutral::before{ background:var(--neutral) }

.pill .icon{
  position:absolute; left:12px; top:50%; transform:translateY(-50%);
  width:56px; height:56px; border-radius:14px; display:grid; place-items:center;
  color:#fff; font-size:1.35rem; background:rgba(255,255,255,.18);
  border:2px solid rgba(255,255,255,.35);
}
.pill .content{ flex:1; display:flex; flex-direction:column; gap:4px }
.pill .title{
  margin:0; font-weight:900; letter-spacing:.2px; color:var(--ink);
  text-transform:uppercase;
}
.pill .subtitle{ margin:0; color:var(--muted); font-weight:600 }

/* CTAs */
.actions{ display:grid; grid-template-columns:1fr; gap:8px; max-width:420px; margin-top:10px }
.btn{ height:44px; font-weight:800; border-radius:12px }
.btn-outline-primary{ color:var(--blue); border-color:var(--blue); background:#fff }
.btn-outline-primary:hover{ color:#fff; background:var(--blue); border-color:var(--blue) }
.btn-primary{ background:var(--blue); border-color:var(--blue) }

.btn-outline-success{ color:var(--green); border-color:var(--green); background:#fff }
.btn-outline-success:hover{ color:#fff; background:var(--green); border-color:var(--green) }
.btn-success{ background:var(--green); border-color:var(--green) }

/* Grid responsive */
.grid{ display:grid; gap:18px; grid-template-columns:1fr }
@media (min-width: 992px){ .grid{ grid-template-columns:1fr 1fr } }

/* Barra inferior “Volver” */
.back-rail{ position:sticky; bottom:0; background:var(--panel);
  border-top:1px solid var(--line); box-shadow:0 -6px 18px rgba(0,0,0,.06); margin-top:30px }
.back-rail .inner{ max-width:980px; margin-inline:auto; padding:12px 16px; display:flex; justify-content:center }
.back-rail .btn{ min-width:280px; border-radius:12px }
</style>

<div class="wrapper">
  <!-- Banner -->
  <div class="welcome">
    <h1>Gestión de Departamentos y Sedes</h1>
    <p><span>Bienvenido</span> · Nexus RH</p>
  </div>

  <!-- Tarjetas -->
  <div class="grid">
    <!-- Departamentos -->
    <article class="pill">
      <div class="icon" aria-hidden="true">📁</div>
      <div class="content">
        <h3 class="title">Departamentos</h3>
        <p class="subtitle">Crea, edita o elimina departamentos. Asigna responsables y controla su información.</p>
        <div class="actions">
          <a href="<?php echo BASE_PATH; ?>/app/views/admin/departamentos/crear_dep.php" class="btn btn-outline-primary">➕ Nuevo Departamento</a>
          <a href="<?php echo BASE_PATH; ?>/app/views/admin/departamentos/lista_dep.php" class="btn btn-primary">📄 Ver Departamentos</a>
        </div>
      </div>
    </article>

    <!-- Sedes (dentro de la carpeta de departamentos) -->
    <article class="pill is-green">
      <div class="icon" aria-hidden="true">🏢</div>
      <div class="content">
        <h3 class="title">Sedes</h3>
        <p class="subtitle">Registra nuevas ubicaciones, verifica duplicados y administra datos de contacto.</p>
        <div class="actions">
          <a href="<?php echo BASE_PATH; ?>/app/views/admin/departamentos/crear_sede.php" class="btn btn-outline-success">➕ Nueva Sede</a>
          <a href="<?php echo BASE_PATH; ?>/app/views/admin/departamentos/lista_sedes.php" class="btn btn-success">📄 Ver Sedes</a>
        </div>
      </div>
    </article>
  </div>
</div>

<!-- Rail inferior -->
<div class="back-rail">
  <div class="inner">
    <a href="<?php echo BASE_PATH; ?>/public/admin.php" class="btn btn-outline-dark">⬅ Volver al panel principal</a>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/footer.php'; ?>
