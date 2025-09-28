<?php
// ============================================================
// Lista de Sedes - Nexus RH (con mapa + inspector)
// ============================================================

/* Base + sesión */
define('BASE_PATH','/sistema_rh'); // <-- AJUSTA si tu carpeta cambia
if (session_status() === PHP_SESSION_NONE) session_start();

/* Conexión */
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
$db = Conexion::getConexion();

/* ============================================================
   HELPERS
   ============================================================ */
function columnExists(PDO $db, string $table, string $col): bool {
  $st = $db->prepare("SHOW COLUMNS FROM `$table` LIKE :c");
  $st->execute([':c'=>$col]);
  return (bool)$st->fetch();
}
$HAS_LAT = columnExists($db, 'sedes', 'latitud');
$HAS_LNG = columnExists($db, 'sedes', 'longitud');

/* ============================================================
   ENDPOINTS AJAX (MISMO ARCHIVO)
   ============================================================ */

/* A) JSON para el mapa (respeta filtros del listado) */
if (isset($_GET['sedes_json'])) {
  header('Content-Type: application/json; charset=utf-8');
  try {
    $q          = trim($_GET['q'] ?? '');
    $estado_nom = trim($_GET['estado_nom'] ?? '');
    $estatus    = $_GET['estatus'] ?? 'activo'; // activo|inactivo|''

    $where = []; $p = [];

    if ($q !== '') {
      $where[] = "(UPPER(s.nombre) LIKE :q OR UPPER(s.municipio) LIKE :q OR UPPER(s.estado) LIKE :q)";
      $p[':q'] = '%'.mb_strtoupper($q,'UTF-8').'%';
    }
    if ($estado_nom !== '') {
      $where[] = "UPPER(s.estado) = :estnom";
      $p[':estnom'] = mb_strtoupper($estado_nom,'UTF-8');
    }
    if ($estatus !== '') {
      $where[] = "s.activo = :act";
      $p[':act'] = ($estatus==='activo') ? 1 : 0;
    }

    $cols = "s.id, s.nombre, s.municipio, s.estado, s.telefono, s.activo";
    if ($HAS_LAT && $HAS_LNG) $cols .= ", s.latitud, s.longitud";

    $sql = "SELECT $cols, u.nombre_completo AS gerente
            FROM sedes s
            LEFT JOIN usuarios u ON u.id = s.gerente_id
            ".($where?('WHERE '.implode(' AND ',$where)):'')."
            ORDER BY s.nombre ASC";
    $st = $db->prepare($sql);
    $st->execute($p);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok'=>true,'data'=>$rows], JSON_UNESCAPED_UNICODE);
  } catch(Throwable $e){
    echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
  }
  exit;
}

/* B) Detalles (panel lateral) */
if (isset($_GET['detalles_id'])) {
  header('Content-Type: application/json; charset=utf-8');
  try {
    $id = (int)$_GET['detalles_id'];
    if ($id<=0) throw new Exception('ID inválido');

    $cols = "s.id, s.nombre, s.domicilio, s.numero, s.interior, s.colonia,
             s.municipio, s.estado, s.cp, s.telefono, s.activo,
             s.gerente_id, u.nombre_completo AS gerente";
    if ($HAS_LAT && $HAS_LNG) $cols .= ", s.latitud, s.longitud";

    $st = $db->prepare("SELECT $cols
                        FROM sedes s
                        LEFT JOIN usuarios u ON u.id = s.gerente_id
                        WHERE s.id=:id LIMIT 1");
    $st->execute([':id'=>$id]);
    $s = $st->fetch(PDO::FETCH_ASSOC);
    if (!$s) throw new Exception('Sede no encontrada');

    $h = fn($v)=> htmlspecialchars((string)$v ?? '', ENT_QUOTES, 'UTF-8');
    $v = function($x){ $x = trim((string)$x); return $x!=='' ? $x : '—'; };

    // Contadores por sección (como lo tenías)
    $ubTotal=5; $ubFilled = 0
      + (trim((string)$s['municipio'])!==''?1:0)
      + (trim((string)$s['estado'])!==''?1:0)
      + (trim((string)$s['domicilio'])!==''?1:0)
      + (trim((string)$s['numero'])!==''?1:0)
      + (trim((string)$s['cp'])!==''?1:0);

    $contTotal=1; $contFilled = 0 + (trim((string)$s['telefono'])!==''?1:0);
    $admTotal=1;  $admFilled = 0 + (trim((string)$s['gerente'])!==''?1:0);

    ob_start(); ?>
    <div class="insp-head">
      <div class="d-flex align-items-center gap-3">
        <div class="sd-avatar">🏢</div>
        <div>
          <div class="insp-name"><?= $h($v($s['nombre'])) ?></div>
          <div class="insp-username"><?= $h($v($s['municipio'])) ?>, <?= $h($v($s['estado'])) ?></div>
          <div class="insp-badges">
            <span class="insp-status <?= ((int)$s['activo']===1?'ok':'off') ?>">
              <?= ((int)$s['activo']===1?'🟢 Activa':'⚪ Inactiva') ?>
            </span>
            <?php if ($HAS_LAT && $HAS_LNG && $s['latitud']!==null && $s['longitud']!==null): ?>
              <span class="insp-pill">📍 <?= $h($s['latitud']) ?>, <?= $h($s['longitud']) ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="insp-actions">
        <a href="editar_sede.php?id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-secondary">✏️ Editar</a>
        <?php if ((int)$s['activo']===1): ?>
          <button type="button" class="btn btn-sm btn-outline-danger js-insp-toggle" data-id="<?= (int)$s['id'] ?>" data-act="0">🚫 Desactivar</button>
        <?php else: ?>
          <button type="button" class="btn btn-sm btn-outline-success js-insp-toggle" data-id="<?= (int)$s['id'] ?>" data-act="1">♻️ Reactivar</button>
        <?php endif; ?>
      </div>
    </div>
    <div class="insp-body">
      <div class="insp-sections">
        <section class="insp-section">
          <div class="sec-title">
            <div class="sec-left"><span class="sec-ico">🗺️</span><span>Ubicación</span></div>
            <span class="sec-count"><?= $ubFilled ?>/<?= $ubTotal ?></span>
          </div>
          <div class="kv-grid">
            <div class="kv"><div class="k">Municipio</div><div class="v"><?= $h($v($s['municipio'])) ?></div></div>
            <div class="kv"><div class="k">Estado</div><div class="v"><?= $h($v($s['estado'])) ?></div></div>
            <div class="kv"><div class="k">Domicilio</div><div class="v"><?= $h($v($s['domicilio'])) ?> <?= $h($v($s['numero'])) ?> <?= ($s['interior']?('Int. '.$h($s['interior'])):'') ?></div></div>
            <div class="kv"><div class="k">Colonia</div><div class="v"><?= $h($v($s['colonia'])) ?></div></div>
            <div class="kv"><div class="k">C.P.</div><div class="v"><?= $h($v($s['cp'])) ?></div></div>
          </div>
        </section>

        <section class="insp-section">
          <div class="sec-title">
            <div class="sec-left"><span class="sec-ico">☎️</span><span>Contacto</span></div>
            <span class="sec-count"><?= $contFilled ?>/<?= $contTotal ?></span>
          </div>
          <div class="kv-grid">
            <div class="kv"><div class="k">Teléfono</div><div class="v"><?= $h($v($s['telefono'])) ?></div></div>
          </div>
        </section>

        <section class="insp-section">
          <div class="sec-title">
            <div class="sec-left"><span class="sec-ico">👤</span><span>Administración</span></div>
            <span class="sec-count"><?= $admFilled ?>/<?= $admTotal ?></span>
          </div>
          <div class="kv-grid">
            <div class="kv"><div class="k">Gerente</div><div class="v"><?= $h($v($s['gerente'])) ?></div></div>
          </div>
        </section>
      </div>
    </div>
    <?php
    echo json_encode(['ok'=>true, 'html'=>ob_get_clean()], JSON_UNESCAPED_UNICODE);
  } catch(Throwable $e){
    echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
  }
  exit;
}

/* C) Toggle activo (soft delete / reactivar) */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '') === 'toggle_activo') {
  header('Content-Type: application/json; charset=utf-8');
  try {
    $id = (int)($_POST['id'] ?? 0);
    $to = ($_POST['to'] ?? '1')==='1' ? 1 : 0;
    if ($id<=0) throw new Exception('ID inválido');
    $st = $db->prepare("UPDATE sedes SET activo=:a WHERE id=:id");
    $st->execute([':a'=>$to, ':id'=>$id]);
    echo json_encode(['ok'=>true, 'msg'=> $to? 'Sede activada':'Sede desactivada' ]);
  } catch(Throwable $e){
    echo json_encode(['ok'=>false, 'msg'=>$e->getMessage()]);
  }
  exit;
}

/* ============================================================
   LISTA NORMAL (HTML)
   ============================================================ */

/* Filtros (por defecto, mostrar Activas) */
$q          = trim($_GET['q'] ?? '');
$estado_nom = trim($_GET['estado_nom'] ?? '');
$estatus    = isset($_GET['estatus']) ? $_GET['estatus'] : 'activo'; // default "activo"

/* Catálogo de estados (para filtro) */
$estadosOpts = $db->query("SELECT DISTINCT estado FROM sedes ORDER BY estado")->fetchAll(PDO::FETCH_COLUMN);

$where = []; $p = [];
if ($q !== '') {
  $where[] = "(UPPER(s.nombre) LIKE :q OR UPPER(s.municipio) LIKE :q OR UPPER(s.estado) LIKE :q)";
  $p[':q'] = '%'.mb_strtoupper($q,'UTF-8').'%';
}
if ($estado_nom !== '') {
  $where[] = "UPPER(s.estado) = :estnom";
  $p[':estnom'] = mb_strtoupper($estado_nom,'UTF-8');
}
if ($estatus !== '') {
  $where[] = "s.activo = :act"; $p[':act'] = ($estatus==='activo') ? 1 : 0;
}

$countSql = "SELECT COUNT(*) FROM sedes s ".($where?('WHERE '.implode(' AND ',$where)):'');
$stc = $db->prepare($countSql); $stc->execute($p); $total = (int)$stc->fetchColumn();

$pag   = max(1, (int)($_GET['pag'] ?? 1));
$limit = 12;
$off   = ($pag - 1) * $limit;
$pages = max(1, (int)ceil($total/$limit));

$colsList = "s.id, s.nombre, s.municipio, s.estado, s.telefono, s.activo, u.nombre_completo AS gerente";
$sql = "SELECT $colsList
        FROM sedes s
        LEFT JOIN usuarios u ON u.id = s.gerente_id
        ".($where?('WHERE '.implode(' AND ',$where)):'')."
        ORDER BY s.nombre ASC
        LIMIT :lim OFFSET :off";
$st = $db->prepare($sql);
foreach($p as $k=>$v) $st->bindValue($k,$v);
$st->bindValue(':lim', $limit, PDO::PARAM_INT);
$st->bindValue(':off', $off, PDO::PARAM_INT);
$st->execute();
$sedes = $st->fetchAll(PDO::FETCH_ASSOC);

/* Flash */
$flash_ok = $_SESSION['sede_ok'] ?? ($_SESSION['sede_guardada'] ?? $_SESSION['sede_editada'] ?? $_SESSION['sede_eliminada'] ?? null);
$flash_error = $_SESSION['sede_error'] ?? ($_SESSION['error_guardado'] ?? $_SESSION['error_edicion'] ?? $_SESSION['error_eliminacion'] ?? null);
unset($_SESSION['sede_ok'], $_SESSION['sede_error'], $_SESSION['sede_guardada'], $_SESSION['sede_editada'], $_SESSION['sede_eliminada'], $_SESSION['error_guardado'], $_SESSION['error_edicion'], $_SESSION['error_eliminacion']);

/* Header global (MISMO HEADER QUE EN DEPARTAMENTOS) */
$tituloPagina = "Sedes"; // <-- usa el mismo nombre de variable que header.php
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/header.php';
?>
<style>
:root{ --nav-h: 64px; } /* altura aprox del navbar */

/* ====== Hero & Toolbar ====== */
.page-head{
  display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;
  padding:.85rem 1rem;border-radius:16px;background:rgba(255,255,255,.18);
  border:1px solid rgba(255,255,255,.35);backdrop-filter:blur(8px);box-shadow:0 6px 16px rgba(0,0,0,.12);
  position: relative; z-index: 2;
}
.hero{display:flex;align-items:center;gap:.8rem}
.hero .hero-icon{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;background:linear-gradient(135deg,#0D6EFD,#6ea8fe);color:#fff;font-size:1.25rem;box-shadow:0 6px 14px rgba(13,110,253,.35)}
.hero .title{margin:0;line-height:1.1;font-weight:900;letter-spacing:.2px;font-size:clamp(1.9rem, 2.6vw + .6rem, 2.6rem);background:linear-gradient(90deg,#ffffff 0%, #e6f0ff 60%, #fff);-webkit-background-clip:text;background-clip:text;color:transparent;text-shadow:0 1px 0 rgba(0,0,0,.12)}
.hero .subtitle{margin:0;color:#e8eef7;font-size:.95rem;font-weight:500;opacity:.95}
.badge-soft{background:#e9f2ff;color:#1f2937;border:1px solid #cfe0ff;font-weight:600}

.toolbar{
  background:rgba(255,255,255,.86);
  border:1px solid #e5e7eb;border-radius:16px;padding:.9rem;color:#04181e;
  box-shadow:0 4px 12px rgba(0,0,0,.08);
  position:relative; z-index:1;
}
.btn-primary{background:#0D6EFD;border-color:#0D6EFD}
.btn-primary:hover{background:#0b5ed7;border-color:#0b5ed7}

/* Switch de vista */
.view-switch .btn{ min-width:130px; }

/* ====== MAPA ====== */
#mapCard{
  border-radius:16px; overflow:hidden; border:1px solid #e5e7eb;
  box-shadow:0 8px 24px rgba(0,0,0,.08);
  position:relative; z-index:1;
  margin-top:.5rem;
}
#mapMX{height:380px;}
.map-legend{
  position:absolute; right:12px; bottom:12px;
  background:#fff; border:1px solid #e5e7eb; border-radius:10px;
  padding:.4rem .6rem; font-size:.85rem; color:#334155; z-index: 2;
}
.map-toolbar{
  position:absolute; left:12px; top:12px; display:flex; gap:.5rem; z-index: 2;
}
.map-toolbar .btn{padding:.35rem .6rem; border-radius:.5rem}

/* Leaflet por debajo del navbar */
.leaflet-container,
.leaflet-pane,
.leaflet-top,
.leaflet-bottom{
  z-index: 400 !important;
}

/* ====== Grid Tarjetas ====== */
.grid{display:grid;grid-template-columns:repeat(1,1fr);gap:1rem; position:relative; z-index:1;}
@media (min-width:576px){.grid{grid-template-columns:repeat(2,1fr)}}
@media (min-width:992px){.grid{grid-template-columns:repeat(3,1fr)}}
@media (min-width:1200px){.grid{grid-template-columns:repeat(4,1fr)}}

.sede-card{position:relative;overflow:hidden;border:1px solid #e5e7eb;border-radius:1rem;background:#fff}
.sede-card .banner{height:64px;background:linear-gradient(135deg,#0D6EFD,#6ea8fe 60%, #F6BD60)}
.sede-card .body{padding:.75rem .75rem 1rem;color:#04181e}
.sede-card .name{font-weight:800; font-size:1.05rem}
.sede-card .meta{color:#64748b; font-size:.9rem}
.sede-actions{position:absolute;top:.5rem;right:.5rem;display:flex;gap:.35rem; z-index:2;}
.sede-actions .btn{padding:.25rem .5rem;border-radius:.5rem}
.sede-footer{display:flex;align-items:center;justify-content:space-between;gap:.5rem;padding:.5rem .75rem .9rem}
.tag{display:inline-flex; align-items:center; gap:.35rem; padding:.25rem .5rem; border-radius:.5rem; background:#f1f5f9; font-size:.78rem; color:#334155}
.empty{background:#fff;border:1px dashed #cbd5e1;border-radius:1rem;padding:2.5rem;text-align:center;color:#64748b}

/* ====== Tabla ====== */
.table > :not(caption) > * > *{vertical-align:middle}
.actions-col{white-space:nowrap}

/* ====== Inspector – debajo del navbar ====== */
.insp-backdrop{
  position:fixed; left:0; right:0; bottom:0;
  top: var(--nav-h);
  background:rgba(0,0,0,.45); backdrop-filter:blur(2px);
  opacity:0; pointer-events:none; transition:opacity .3s ease; z-index:900;
}
.insp-backdrop.show{opacity:1; pointer-events:auto}
.inspector{
  position:fixed; right:-720px;
  top: calc(var(--nav-h) + 8px);
  height: calc(100% - var(--nav-h) - 16px);
  width:min(720px,96vw);
  background:#fff; color:#04181e; box-shadow:-10px 0 30px rgba(0,0,0,.25);
  transition:right .35s ease; z-index:950; display:flex; flex-direction:column; border-top-left-radius:14px;
}
.inspector.open{right:0}

/* tema oscuro dinámico del inspector */
.inspector.dark{ background:#0f172a; color:#e5e7eb; }
.inspector.dark .insp-head{ background: linear-gradient(180deg, #0b1220, #0f172a); border-bottom-color:#1f2937;}
.inspector.dark .insp-pill{ background:#101826; border-color:#1f2937; color:#e5e7eb; }
.inspector.dark .insp-status{ border-color:#1f2937; }
.inspector.dark .insp-status.ok{ background:#052e25; border-color:#064e3b; }
.inspector.dark .insp-status.off{ background:#0b1220; }
.inspector.dark .insp-section{ background:#0b1220; border-color:#1f2937; box-shadow:none; }
.inspector.dark .sec-title{ color:#cfe0ff; }
.inspector.dark .sec-count{ background:#0f1b2e; border-color:#1e3a8a; color:#cfe0ff; }
.inspector.dark .kv .k{ color:#9ca3af; }
.inspector.dark .kv .v{ color:#e5e7eb; }
.inspector.dark a{ color:#93c5fd; }

.insp-head{padding:1rem 1.25rem; border-bottom:1px solid #e5e7eb; background:linear-gradient(180deg,#f8fbff,#fff); display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap}
.sd-avatar{width:56px;height:56px;border-radius:12px;display:grid;place-items:center;background:#0D6EFD; color:#fff; font-size:1.3rem}
.insp-name{font-weight:800;font-size:1.1rem;line-height:1.2}
.insp-username{color:#64748b;font-size:.9rem}
.insp-badges{display:flex;gap:.5rem;margin-top:.35rem;flex-wrap:wrap}
.insp-pill{display:inline-flex;align-items:center;gap:.35rem;padding:.2rem .5rem;font-size:.78rem;border-radius:999px;background:#eef2ff;border:1px solid #e0e7ff;color:#334155}
.insp-status{padding:.2rem .5rem;border-radius:999px;font-size:.78rem;border:1px solid #e5e7eb}
.insp-status.ok{background:#f0fdf4;border-color:#dcfce7}
.insp-status.off{background:#f8fafc}
.insp-actions{display:flex;gap:.5rem;align-items:center;margin-left:auto}
.insp-close{position:absolute;right:.5rem;top:.5rem;background:transparent;border:none;font-size:1.4rem;line-height:1;color:#64748b}
.insp-body{padding:1rem 1.25rem; overflow:auto; flex:1}
.insp-sections{display:grid;gap:1rem;grid-template-columns:1fr}
@media(min-width:992px){.insp-sections{grid-template-columns:1fr 1fr}}
.insp-section{background:#fff;border:1px solid #e5e7eb;border-radius:12px;box-shadow:0 4px 10px rgba(0,0,0,.04);padding:1rem}
.sec-title{font-weight:700;font-size:.95rem;color:#0D6EFD;margin-bottom:.6rem;letter-spacing:.2px;display:flex;align-items:center;justify-content:space-between}
.sec-left{display:flex;align-items:center;gap:.4rem}
.sec-ico{font-size:1.05rem}
.sec-count{font-weight:700;background:#eef2ff;padding:.1rem .5rem;border-radius:999px;border:1px solid #e0e7ff;color:#334155;font-size:.78rem}
.kv-grid{display:grid;grid-template-columns:1fr;gap:.45rem .75rem}
.kv{display:flex;gap:.5rem;align-items:baseline}
.kv .k{color:#64748b;min-width:160px;font-weight:600}
.kv .v{color:#04181e}

/* ===== HOTFIX Navbar vs Leaflet (lista_sedes.php) ===== */

/* 1) El navbar manda SIEMPRE (usa la misma clase del navbar del sitio) */
.navbar-nexus{
  position: sticky;
  top: 0;
  z-index: 9999 !important; /* por encima de cualquier capa local */
}

/* 2) Menús/collapse/offcanvas del navbar por encima de overlays */
.navbar-nexus .dropdown-menu,
.navbar-nexus .navbar-collapse,
.navbar-nexus .collapse,
.navbar-nexus .offcanvas{
  position: relative;
  z-index: 10000 !important;
}

/* 3) El card del mapa crea su propio “mundo” y no invade fuera */
#mapCard{
  position: relative !important;
  overflow: hidden !important;
  isolation: isolate;       /* crea stacking context propio */
  z-index: 1 !important;    /* muy por debajo del navbar */
}

/* 4) El contenedor del mapa NO se sale del card */
#mapMX{
  position: relative !important;
  height: 380px;            /* ya lo tenías, lo reforzamos */
}

/* 5) Inspector/backdrop debajo del navbar (como en departamentos) */
.insp-backdrop{
  top: var(--nav-h) !important;
  z-index: 900 !important;   /* < navbar */
}
.inspector{
  top: calc(var(--nav-h) + 8px) !important;
  height: calc(100% - var(--nav-h) - 16px) !important;
  z-index: 950 !important;   /* < navbar */
}

/* 6) Por si alguna capa absoluta se estira, la recortamos al card */
#mapCard > .leaflet-container,
#mapCard > .leaflet-container .leaflet-pane{
  inset: 0 !important;
}

/* 7) Fondo global JAMÁS tapa nada (refuerzo por si el global no cargó) */
body::before{
  z-index: -1 !important;
  pointer-events: none !important;
}
</style>

<div class="container py-4" style="max-width:1300px">
  <div class="page-head">
    <div class="hero">
      <div class="hero-icon">📍</div>
      <div>
        <h1 class="title">Sedes</h1>
        <p class="subtitle">Mapa y gestión de ubicaciones</p>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <span class="badge-soft">Total: <?= number_format($total) ?></span>
      <a href="crear_sede.php" class="btn btn-primary">➕ Nueva sede</a>
      <a href="menu.php" class="btn btn-outline-secondary">← Regresar</a>
    </div>
  </div>

  <!-- Switch centrado (Tarjetas / Tabla) -->
  <div class="d-flex justify-content-center mt-3">
    <div class="btn-group view-switch" role="group" aria-label="Cambiar vista">
      <button type="button" class="btn btn-outline-primary" id="btnViewCards">▦ Tarjetas</button>
      <button type="button" class="btn btn-outline-primary" id="btnViewTable">☰ Tabla</button>
    </div>
  </div>

  <!-- MAPA -->
  <div class="position-relative mt-3" id="mapCard">
    <div id="mapMX"></div>
    <div class="map-toolbar">
      <button class="btn btn-outline-primary btn-sm" id="btnFit">🔎 Ajustar</button>
      <button class="btn btn-outline-secondary btn-sm" id="btnClearSel">🧹 Limpiar selección</button>
    </div>
    <div class="map-legend">● Activa · ○ Inactiva</div>
  </div>

  <!-- FILTROS -->
  <form class="toolbar mt-3" method="get" id="filtros">
    <div class="row g-2 align-items-end">
      <div class="col-12 col-lg-6">
        <div class="input-group">
          <span class="input-group-text">🔎</span>
          <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Buscar: nombre, municipio o estado">
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <select name="estado_nom" class="form-select">
          <option value="">— Todos los estados —</option>
          <?php foreach ($estadosOpts as $eo): ?>
            <option value="<?= htmlspecialchars($eo) ?>" <?= ($estado_nom===$eo?'selected':'') ?>><?= htmlspecialchars($eo) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-lg-2">
        <select name="estatus" class="form-select">
          <option value="">— Todas —</option>
          <option value="activo"   <?= ($estatus==='activo'?'selected':'') ?>>Activas</option>
          <option value="inactivo" <?= ($estatus==='inactivo'?'selected':'') ?>>Inactivas</option>
        </select>
      </div>
      <div class="col-12 col-lg-1 d-grid">
        <button class="btn btn-outline-primary">Filtrar</button>
      </div>

      <div class="col-12 d-flex justify-content-between mt-2">
        <div></div>
        <div class="d-flex gap-2">
          <?php $qsClear = basename($_SERVER['PHP_SELF']); ?>
          <a class="btn btn-outline-secondary" href="<?= $qsClear ?>">Limpiar</a>
        </div>
      </div>
    </div>
  </form>

  <!-- VISTA TARJETAS -->
  <div id="viewCards" class="mt-3">
    <div class="grid">
      <?php if (!$sedes): ?>
        <div class="empty col-12">
          <div style="font-size:2rem;line-height:1">🗂️</div>
          <div class="mt-2">No hay resultados con los filtros actuales.</div>
        </div>
      <?php else: foreach($sedes as $s): ?>
        <div class="sede-card">
          <div class="banner"></div>

          <div class="sede-actions">
            <a href="editar_sede.php?id=<?= (int)$s['id'] ?>" class="btn btn-light btn-sm" title="Editar">✏️</a>
            <?php if ((int)$s['activo']===1): ?>
              <button type="button" class="btn btn-outline-danger btn-sm btn-toggle" data-id="<?= (int)$s['id'] ?>" data-to="0" title="Desactivar">🚫</button>
            <?php else: ?>
              <button type="button" class="btn btn-outline-success btn-sm btn-toggle" data-id="<?= (int)$s['id'] ?>" data-to="1" title="Reactivar">♻️</button>
            <?php endif; ?>
          </div>

          <div class="body">
            <div class="name"><?= htmlspecialchars($s['nombre']) ?></div>
            <div class="meta"><?= htmlspecialchars($s['municipio']) ?>, <?= htmlspecialchars($s['estado']) ?></div>

            <div class="d-flex flex-wrap gap-2 mt-2">
              <span class="tag">☎️ <?= htmlspecialchars($s['telefono'] ?: '—') ?></span>
              <span class="tag">👤 <?= htmlspecialchars($s['gerente'] ?: 'Sin gerente') ?></span>
            </div>
          </div>

          <div class="sede-footer">
            <div class="meta"><?= ((int)$s['activo']===1)?'🟢 Activa':'⚪ Inactiva' ?></div>
            <div>
              <button type="button" class="btn btn-sm btn-outline-primary btn-open-inspector" data-id="<?= (int)$s['id'] ?>">👁️ Detalles</button>
            </div>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- VISTA TABLA -->
  <div id="viewTable" class="mt-3" style="display:none">
    <div class="card shadow-sm p-2">
      <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Municipio</th>
              <th>Estado</th>
              <th>Teléfono</th>
              <th>Gerente</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$sedes): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No hay resultados.</td></tr>
          <?php else: foreach($sedes as $s): ?>
            <tr>
              <td><strong><?= htmlspecialchars($s['nombre']) ?></strong></td>
              <td><?= htmlspecialchars($s['municipio']) ?></td>
              <td><?= htmlspecialchars($s['estado']) ?></td>
              <td><?= htmlspecialchars($s['telefono'] ?: '—') ?></td>
              <td><?= htmlspecialchars($s['gerente'] ?: '—') ?></td>
              <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-primary btn-open-inspector" data-id="<?= (int)$s['id'] ?>">👁️</button>
                <a href="editar_sede.php?id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-secondary">✏️</a>
                <?php if ((int)$s['activo']===1): ?>
                  <button type="button" class="btn btn-sm btn-outline-danger btn-toggle" data-id="<?= (int)$s['id'] ?>" data-to="0">🚫</button>
                <?php else: ?>
                  <button type="button" class="btn btn-sm btn-outline-success btn-toggle" data-id="<?= (int)$s['id'] ?>" data-to="1">♻️</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Paginación -->
  <?php if ($pages>1): ?>
    <nav class="mt-3">
      <ul class="pagination justify-content-center">
        <?php
          $qs = $_GET; $start=max(1,$pag-2); $end=min($pages,$pag+2);
          if ($start>1){ $qs['pag']=1; echo '<li class="page-item"><a class="page-link" href="?'.http_build_query($qs).'">«</a></li>'; }
          for($i=$start;$i<=$end;$i++): $qs['pag']=$i;
            echo '<li class="page-item '.($i===$pag?'active':'').'"><a class="page-link" href="?'.http_build_query($qs).'">'.$i.'</a></li>';
          endfor;
          if ($end<$pages){ $qs['pag']=$pages; echo '<li class="page-item"><a class="page-link" href="?'.http_build_query($qs).'">»</a></li>'; }
        ?>
      </ul>
      <div class="text-center text-muted" style="font-size:.9rem">
        Página <?= $pag ?> de <?= $pages ?> · Mostrando <?= min($limit,$total-$off) ?> de <?= number_format($total) ?>
      </div>
    </nav>
  <?php endif; ?>
</div>

<!-- Inspector -->
<div class="insp-backdrop" id="inspBackdrop" aria-hidden="true"></div>
<aside class="inspector" id="inspector" aria-hidden="true" aria-label="Detalles de sede" role="dialog">
  <button class="insp-close" id="inspClose" aria-label="Cerrar">×</button>
  <div id="inspContent"><div class="p-3 text-muted">Selecciona una sede…</div></div>
</aside>

<?php if ($flash_ok || $flash_error): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
  icon: '<?= $flash_ok ? 'success' : 'error' ?>',
  title: '<?= addslashes($flash_ok ?: $flash_error) ?>',
  timer: 1900, showConfirmButton: false
});
</script>
<?php endif; ?>

<!-- Leaflet (mapa) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="anonymous"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/* ====== Preferencia de vista (como Departamentos) ====== */
const KEY='sedesView';
const btnCards=document.getElementById('btnViewCards');
const btnTable=document.getElementById('btnViewTable');
const viewCards=document.getElementById('viewCards');
const viewTable=document.getElementById('viewTable');
function applyView(v){
  if(v==='table'){ viewCards.style.display='none'; viewTable.style.display='';
    btnTable.classList.add('btn-primary'); btnTable.classList.remove('btn-outline-primary');
    btnCards.classList.add('btn-outline-primary'); btnCards.classList.remove('btn-primary');
  } else { viewCards.style.display=''; viewTable.style.display='none';
    btnCards.classList.add('btn-primary'); btnCards.classList.remove('btn-outline-primary');
    btnTable.classList.add('btn-outline-primary'); btnTable.classList.remove('btn-primary');
  }
  try{ localStorage.setItem(KEY,v); }catch(e){}
}
let pref='cards'; try{ pref=localStorage.getItem(KEY)||'cards'; }catch(e){}
applyView(pref);
btnCards.addEventListener('click',()=>applyView('cards'));
btnTable.addEventListener('click',()=>applyView('table'));

/* ====== Inspector ====== */
const insp = document.getElementById('inspector');
const inspBackdrop = document.getElementById('inspBackdrop');
const inspClose = document.getElementById('inspClose');
const inspContent = document.getElementById('inspContent');

function parseRGB(str){ const m=(str||'').match(/\d+/g); return m? m.slice(0,3).map(Number):[255,255,255]; }
function relLuma([r,g,b]){ r/=255; g/=255; b/=255; const lin=v=>v<=.03928?v/12.92:Math.pow((v+0.055)/1.055,2.4); r=lin(r); g=lin(g); b=lin(b); return 0.2126*r+0.7152*g+0.0722*b; }
function updateInspectorTheme(){
  const bg = getComputedStyle(document.body).backgroundColor;
  const L = relLuma(parseRGB(bg));
  if (L < 0.40){ insp.classList.add('dark'); inspBackdrop.classList.add('dark'); } else { insp.classList.remove('dark'); inspBackdrop.classList.remove('dark'); }
}
function openInspector(){ updateInspectorTheme(); insp.classList.add('open'); inspBackdrop.classList.add('show'); insp.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
function closeInspector(){ insp.classList.remove('open'); inspBackdrop.classList.remove('show'); insp.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
inspBackdrop.addEventListener('click', closeInspector);
inspClose.addEventListener('click', closeInspector);
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeInspector(); });

async function fetchDetalles(id){
  const r = await fetch(`?detalles_id=${encodeURIComponent(id)}`, {credentials:'same-origin'});
  const data = await r.json(); if(!data.ok) throw new Error(data.msg||'Error');
  return data.html;
}
async function openDetalles(id){
  inspContent.innerHTML = `<div class="p-3 text-muted">Cargando…</div>`;
  openInspector();
  try{ const html = await fetchDetalles(id); inspContent.innerHTML = html; }
  catch(e){ inspContent.innerHTML = `<div class="p-3 text-danger">⚠️ ${ (e?.message||'Error al cargar') }</div>`; }
}
document.addEventListener('click', e=>{
  const b = e.target.closest('.btn-open-inspector'); if(!b) return;
  openDetalles(b.dataset.id);
});

/* Toggle activo */
async function toggleActivo(id, to){
  const fd = new FormData(); fd.append('action','toggle_activo'); fd.append('id', id); fd.append('to', to);
  const r = await fetch(location.pathname, {method:'POST', body: fd, credentials:'same-origin'});
  const data = await r.json(); if(!data.ok) throw new Error(data.msg||'Error');
  return data;
}
document.addEventListener('click', async e=>{
  const b = e.target.closest('.btn-toggle'); if(!b) return;
  const id=b.dataset.id, to=b.dataset.to;
  const conf = await Swal.fire({icon:'warning', title:(to==='1'?'¿Reactivar sede?':'¿Desactivar sede?'), showCancelButton:true, confirmButtonText:'Sí', cancelButtonText:'Cancelar'});
  if(!conf.isConfirmed) return;
  try{
    await toggleActivo(id,to);
    await Swal.fire({icon:'success', title:(to==='1'?'Sede activada':'Sede desactivada'), timer:1100, showConfirmButton:false});
    location.reload();
  }catch(err){ Swal.fire({icon:'error', title:'Error', text:(err?.message||'No se pudo actualizar')}); }
});

// Toggle desde inspector
document.addEventListener('click', async e=>{
  const b = e.target.closest('.js-insp-toggle'); if(!b) return;
  const id=b.dataset.id, to=b.dataset.act;
  const conf = await Swal.fire({icon:'warning', title:(to==='1'?'¿Reactivar sede?':'¿Desactivar sede?'), showCancelButton:true, confirmButtonText:'Sí'});
  if(!conf.isConfirmed) return;
  try{
    await toggleActivo(id,to);
    await Swal.fire({icon:'success', title:(to==='1'?'Sede activada':'Sede desactivada'), timer:1000, showConfirmButton:false});
    closeInspector(); location.reload();
  }catch(err){ Swal.fire({icon:'error', title:'Error', text:(err?.message||'No se pudo actualizar')}); }
});

/* ====== MAPA (Leaflet) ====== */
const map = L.map('mapMX', { zoomControl: true, scrollWheelZoom: true });
const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ attribution:'© OpenStreetMap' }).addTo(map);
// México centro/aprox
map.setView([23.6, -102.55], 4.6);

// Centroides por estado (aprox)
const MX_CENTROIDS = {
  "AGUASCALIENTES":[21.883,-102.296], "BAJA CALIFORNIA":[30.840,-115.283], "BAJA CALIFORNIA SUR":[25.988,-111.671],
  "CAMPECHE":[19.190,-90.525], "COAHUILA":[27.145,-102.002], "COLIMA":[19.122,-103.888], "CHIAPAS":[16.430,-92.820],
  "CHIHUAHUA":[28.636,-106.076], "CIUDAD DE MÉXICO":[19.432,-99.133], "CDMX":[19.432,-99.133],
  "DURANGO":[24.833,-104.928], "GUANAJUATO":[21.019,-101.257], "GUERRERO":[17.430,-99.545],
  "HIDALGO":[20.481,-98.963], "JALISCO":[20.659,-103.349], "MÉXICO":[19.284,-99.655], "ESTADO DE MÉXICO":[19.284,-99.655],
  "MICHOACÁN":[19.566,-101.706], "MORELOS":[18.681,-99.091], "NAYARIT":[21.751,-104.845],
  "NUEVO LEÓN":[25.592,-99.996], "OAXACA":[17.081,-96.735], "PUEBLA":[19.041,-98.208],
  "QUERÉTARO":[20.588,-100.389], "QUINTANA ROO":[19.591,-88.043], "SAN LUIS POTOSÍ":[22.156,-100.985],
  "SINALOA":[24.799,-107.389], "SONORA":[29.089,-110.961], "TABASCO":[17.840,-92.618],
  "TAMAULIPAS":[24.289,-98.949], "TLAXCALA":[19.318,-98.237], "VERACRUZ":[19.173,-96.134],
  "YUCATÁN":[20.709,-89.094], "ZACATECAS":[22.770,-102.583]
};

const markersLayer = L.layerGroup().addTo(map);
let bounds = [];

async function loadMapData(){
  const params = new URLSearchParams({
    sedes_json: '1',
    q: '<?= htmlspecialchars($q, ENT_QUOTES) ?>',
    estado_nom: '<?= htmlspecialchars($estado_nom, ENT_QUOTES) ?>',
    estatus: '<?= htmlspecialchars($estatus, ENT_QUOTES) ?>'
  });
  const r = await fetch(`?${params.toString()}`, {credentials:'same-origin'});
  const data = await r.json();
  if(!data.ok){ console.warn(data.msg||'Error sedes_json'); return; }

  markersLayer.clearLayers();
  bounds = [];

  data.data.forEach(s=>{
    let lat=null, lng=null;

    if ('latitud' in s && 'longitud' in s && s.latitud!==null && s.longitud!==null){
      lat = parseFloat(s.latitud); lng = parseFloat(s.longitud);
    }
    if ((lat===null || isNaN(lat) || isNaN(lng)) && s.estado){
      const key = String(s.estado).trim().toUpperCase();
      if (MX_CENTROIDS[key]) { [lat,lng] = MX_CENTROIDS[key]; }
    }
    if (lat===null || lng===null) return;

    const active = Number(s.activo)===1;
    const mk = L.circleMarker([lat,lng], {
      radius: 7, weight: 1.5,
      color: active ? '#0D6EFD' : '#9aa4b2',
      fillColor: active ? '#0D6EFD' : '#cbd5e1',
      fillOpacity: active ? 0.8 : 0.6
    });

    const safe = v => (v??'').toString().replace(/[<>&"]/g, s => ({'<':'&lt;','>':'&gt;','&':'&amp;','"':'&quot;'}[s]));
    const popupHtml = `
      <div style="min-width:200px">
        <div style="font-weight:800">${safe(s.nombre)}</div>
        <div class="text-muted" style="font-size:.9rem">${safe(s.municipio)}, ${safe(s.estado)}</div>
        <div class="mt-1" style="font-size:.9rem">☎️ ${safe(s.telefono || '—')}</div>
        <div class="mt-2 d-flex gap-2">
          <button class="btn btn-sm btn-outline-primary" onclick="openDetalles(${Number(s.id)})">Detalles</button>
          <a class="btn btn-sm btn-outline-secondary" href="editar_sede.php?id=${Number(s.id)}">Editar</a>
        </div>
      </div>`;
    mk.bindPopup(popupHtml, {autoPan:true});

    mk.addTo(markersLayer);
    bounds.push([lat,lng]);
  });

  fitToMarkers();
}
function fitToMarkers(){
  if (bounds.length){
    const b = L.latLngBounds(bounds);
    map.fitBounds(b, {padding:[30,30]});
  } else {
    map.setView([23.6,-102.55], 4.6);
  }
}
document.getElementById('btnFit').addEventListener('click', fitToMarkers);
document.getElementById('btnClearSel').addEventListener('click', ()=>{ map.closePopup(); });

loadMapData(); // inicial
</script>

<?php
// (opcional) footer unificado
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/footer.php';
