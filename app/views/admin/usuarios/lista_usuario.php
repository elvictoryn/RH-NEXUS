<?php
if (!isset($_SESSION)) session_start();
require_once dirname(__DIR__, 4) . '/config/conexion.php';
$db = Conexion::getConexion();

/* ============================================================
   1) ENDPOINTS EN EL MISMO ARCHIVO (AJAX)
   ============================================================ */

/* A) Detalles (GET ?detalles_id=ID) -> JSON {ok, html} */
if (isset($_GET['detalles_id'])) {
  header('Content-Type: application/json; charset=utf-8');
  try{
    $id = (int)$_GET['detalles_id'];
    if ($id <= 0) throw new Exception('ID inválido');

    $sql = "SELECT u.id, u.usuario, u.nombre_completo, u.numero_empleado, u.rol,
                   LOWER(u.estado) AS estado, u.correo, u.telefono, u.fotografia,
                   s.nombre AS sede, d.nombre AS departamento
            FROM usuarios u
            LEFT JOIN sedes s ON s.id=u.sede_id
            LEFT JOIN departamentos d ON d.id=u.departamento_id
            WHERE u.id=:id LIMIT 1";
    $st = $db->prepare($sql);
    $st->execute([':id'=>$id]);
    $u = $st->fetch(PDO::FETCH_ASSOC);
    if (!$u) throw new Exception('Usuario no encontrado');

    $h = fn($v)=> htmlspecialchars((string)$v ?? '', ENT_QUOTES, 'UTF-8');
    $v = function($x){ $x = trim((string)$x); return $x!=='' ? $x : '—'; };

    // Ruta web de fotos
    $imgBase = (defined('BASE_PATH') ? BASE_PATH : '/sistema_rh') . '/public/img/usuarios/';
    $foto = !empty($u['fotografia'])
      ? $imgBase . $h($u['fotografia'])
      : 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2296%22 height=%2296%22><rect width=%22100%25%22 height=%22100%25%22 fill=%22%23f1f5f9%22/></svg>';

    $rolesTxt = [
      'admin' => 'Administrador',
      'rh' => 'Recursos Humanos',
      'jefe_area' => 'Jefe de área',
      'gerente' => 'Gerente'
    ];
    $rolTxt = $rolesTxt[$u['rol']] ?? $u['rol'];

    // Contadores por sección
    $orgTotal = 3;
    $orgFilled = 0
      + (trim($rolTxt) !== '' ? 1 : 0)
      + (trim((string)$u['sede']) !== '' ? 1 : 0)
      + (trim((string)$u['departamento']) !== '' ? 1 : 0);

    $contTotal = 2;
    $contFilled = 0
      + (trim((string)$u['correo']) !== '' ? 1 : 0)
      + (trim((string)$u['telefono']) !== '' ? 1 : 0);

    $accTotal = 3;
    $accFilled = 0
      + (trim((string)$u['usuario']) !== '' ? 1 : 0)
      + (trim((string)$u['numero_empleado']) !== '' ? 1 : 0)
      + (trim((string)$u['estado']) !== '' ? 1 : 0);

    ob_start(); ?>
    <!-- HEADER -->
    <div class="insp-head">
      <div class="d-flex align-items-center gap-3">
        <img src="<?= $foto ?>" alt="Foto" class="insp-avatar">
        <div>
          <div class="insp-name"><?= $h($v($u['nombre_completo'])) ?></div>
          <div class="insp-username">@<?= $h(strtolower($v($u['usuario']))) ?> · #<?= $h($v($u['numero_empleado'])) ?></div>
          <div class="insp-badges">
            <span class="insp-pill"><?= $h($rolTxt) ?></span>
            <span class="insp-status <?= ($u['estado']==='activo'?'ok':'off') ?>">
              <?= ($u['estado']==='activo'?'🟢 Activo':'⚪ Inactivo') ?>
            </span>
          </div>
        </div>
      </div>
      <div class="insp-actions">
        <a href="editar_usuario.php?id=<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline-secondary">✏️ Editar</a>
        <?php if ($u['estado']==='activo'): ?>
          <button type="button" class="btn btn-sm btn-outline-danger js-insp-desactivar" data-id="<?= (int)$u['id'] ?>">🚫 Desactivar</button>
        <?php else: ?>
          <a href="editar_usuario.php?id=<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline-success">✔️ Reactivar</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- BODY -->
    <div class="insp-body">
      <div class="insp-sections">
        <section class="insp-section">
          <div class="sec-title">
            <div class="sec-left"><span class="sec-ico">🏢</span><span>Organización</span></div>
            <span class="sec-count"><?= $orgFilled ?>/<?= $orgTotal ?></span>
          </div>
          <div class="kv-grid">
            <div class="kv"><div class="k">Rol</div><div class="v"><?= $h($v($rolTxt)) ?></div></div>
            <div class="kv"><div class="k">Sede</div><div class="v"><?= $h($v($u['sede'])) ?></div></div>
            <div class="kv"><div class="k">Departamento</div><div class="v"><?= $h($v($u['departamento'])) ?></div></div>
          </div>
        </section>

        <section class="insp-section">
          <div class="sec-title">
            <div class="sec-left"><span class="sec-ico">✉️</span><span>Contacto</span></div>
            <span class="sec-count"><?= $contFilled ?>/<?= $contTotal ?></span>
          </div>
          <div class="kv-grid">
            <div class="kv">
              <div class="k">Correo</div>
              <div class="v"><?= !empty($u['correo']) ? '<a href="mailto:'.$h($u['correo']).'">'.$h($u['correo']).'</a>' : '—' ?></div>
            </div>
            <div class="kv">
              <div class="k">Teléfono</div>
              <div class="v"><?= !empty($u['telefono']) ? '<a href="tel:'.$h($u['telefono']).'">'.$h($u['telefono']).'</a>' : '—' ?></div>
            </div>
          </div>
        </section>

        <section class="insp-section">
          <div class="sec-title">
            <div class="sec-left"><span class="sec-ico">🔐</span><span>Acceso</span></div>
            <span class="sec-count"><?= $accFilled ?>/<?= $accTotal ?></span>
          </div>
          <div class="kv-grid">
            <div class="kv"><div class="k">Usuario</div><div class="v"><?= $h($v($u['usuario'])) ?></div></div>
            <div class="kv"><div class="k">No. Empleado</div><div class="v"><?= $h($v($u['numero_empleado'])) ?></div></div>
            <div class="kv"><div class="k">Estado</div><div class="v"><?= ($u['estado']==='activo'?'🟢 Activo':'⚪ Inactivo') ?></div></div>
          </div>
        </section>
      </div>
    </div>
    <?php
    echo json_encode(['ok'=>true,'html'=>ob_get_clean()], JSON_UNESCAPED_UNICODE);
  } catch(Throwable $e){
    echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
  }
  exit;
}

/* B) Desactivar (POST action=desactivar) -> JSON {ok, msg} */
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '') === 'desactivar') {
  header('Content-Type: application/json; charset=utf-8');
  try{
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) throw new Exception('ID inválido');

    $st = $db->prepare("SELECT id, rol, sede_id, departamento_id FROM usuarios WHERE id=:id LIMIT 1");
    $st->execute([':id'=>$id]);
    $u = $st->fetch();
    if (!$u) throw new Exception('Usuario no encontrado');

    $db->beginTransaction();
    if ($u['rol'] === 'gerente' && !empty($u['sede_id'])) {
      $db->prepare("UPDATE sedes SET gerente_id=NULL WHERE id=:s AND gerente_id=:u")
         ->execute([':s'=>$u['sede_id'], ':u'=>$u['id']]);
    }
    if ($u['rol'] === 'jefe_area' && !empty($u['departamento_id'])) {
      $db->prepare("UPDATE departamentos SET responsable_id=NULL WHERE id=:d AND responsable_id=:u")
         ->execute([':d'=>$u['departamento_id'], ':u'=>$u['id']]);
    }
    $db->prepare("UPDATE usuarios SET estado='inactivo' WHERE id=:id")->execute([':id'=>$id]);
    $db->commit();

    echo json_encode(['ok'=>true, 'msg'=>'Usuario desactivado']);
  } catch(Throwable $e){
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['ok'=>false, 'msg'=>$e->getMessage()]);
  }
  exit;
}

/* ============================================================
   2) LISTA NORMAL (HTML)
   ============================================================ */

/* Catálogos para filtros */
$sedes = $db->query("SELECT id, nombre FROM sedes ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$roles = [
  '' => '— Todos —',
  'admin' => 'Administrador',
  'rh' => 'Recursos Humanos',
  'jefe_area' => 'Jefe de área',
  'gerente' => 'Gerente'
];
$estados = [
  '' => '— Todos —',
  'activo' => 'Activo',
  'inactivo' => 'Inactivo'
];

/* Filtros */
$q        = trim($_GET['q'] ?? '');
$sede_id  = $_GET['sede_id'] ?? '';
$rol      = $_GET['rol'] ?? '';
$estado   = $_GET['estado'] ?? 'activo'; // por defecto activos

$where = [];
$p = [];

if ($q !== '') {
  $where[] = "(UPPER(u.usuario) LIKE :q OR UPPER(u.nombre_completo) LIKE :q OR u.numero_empleado LIKE :qnum OR UPPER(u.correo) LIKE :q)";
  $p[':q'] = '%' . mb_strtoupper($q, 'UTF-8') . '%';
  $p[':qnum'] = '%' . $q . '%';
}
if ($sede_id !== '')  { $where[] = "u.sede_id = :sede";   $p[':sede'] = (int)$sede_id; }
if ($rol !== '')      { $where[] = "u.rol = :rol";        $p[':rol']  = $rol; }
if ($estado !== '')   { $where[] = "LOWER(u.estado) = :est"; $p[':est']  = strtolower($estado); }

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

/* Paginación */
$pag   = max(1, (int)($_GET['pag'] ?? 1));
$limit = 12;
$off   = ($pag - 1) * $limit;

$countSql = "SELECT COUNT(*) FROM usuarios u
             LEFT JOIN sedes s ON s.id=u.sede_id
             LEFT JOIN departamentos d ON d.id=u.departamento_id
             $whereSql";
$stc = $db->prepare($countSql);
$stc->execute($p);
$total = (int)$stc->fetchColumn();
$pages = max(1, (int)ceil($total / $limit));

$sql = "SELECT u.id, u.usuario, u.nombre_completo, u.numero_empleado, u.rol,
               LOWER(u.estado) AS estado, u.correo, u.fotografia, u.telefono,
               s.nombre AS sede, d.nombre AS departamento
        FROM usuarios u
        LEFT JOIN sedes s ON s.id=u.sede_id
        LEFT JOIN departamentos d ON d.id=u.departamento_id
        $whereSql
        ORDER BY u.nombre_completo ASC
        LIMIT :lim OFFSET :off";
$st = $db->prepare($sql);
foreach ($p as $k=>$v) $st->bindValue($k, $v);
$st->bindValue(':lim', $limit, PDO::PARAM_INT);
$st->bindValue(':off', $off, PDO::PARAM_INT);
$st->execute();
$usuarios = $st->fetchAll(PDO::FETCH_ASSOC);

/* Mensajes flash */
$flash_ok    = $_SESSION['usuario_ok']    ?? null;
$flash_error = $_SESSION['usuario_error'] ?? null;
unset($_SESSION['usuario_ok'], $_SESSION['usuario_error']);

/* Header global (USAR camelCase para el título) */
$tituloPagina = "Usuarios";
include_once('../../shared/header.php');

/* Carpeta de fotos (ruta web estable) */
$dirWeb = BASE_PATH . '/public/img/usuarios/';
?>
<style>
:root{ --nav-h: 64px; }

/* ===== Navbar SIEMPRE arriba ===== */
.navbar-nexus{
  position: sticky;
  top: 0;
  z-index: 1100 !important;
}

/* ===== Estilos locales + Inspector ===== */
.card{border-radius:1rem}
.page-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;padding:.85rem 1rem;border-radius:16px;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);backdrop-filter:blur(8px);box-shadow:0 6px 16px rgba(0,0,0,.12);position:relative;z-index:3}
.hero{display:flex;align-items:center;gap:.8rem}
.hero .hero-icon{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;background:linear-gradient(135deg,#0D6EFD,#6ea8fe);color:#fff;font-size:1.25rem;box-shadow:0 6px 14px rgba(13,110,253,.35)}
.hero .title{margin:0;line-height:1.1;font-weight:900;letter-spacing:.2px;font-size:clamp(1.9rem, 2.6vw + .6rem, 2.6rem);background:linear-gradient(90deg,#ffffff 0%, #e6f0ff 60%, #fff);-webkit-background-clip:text;background-clip:text;color:transparent;text-shadow:0 1px 0 rgba(0,0,0,.12)}
.hero .subtitle{margin:0;color:#e8eef7;font-size:.95rem;font-weight:500;opacity:.95}
.page-meta{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}
.badge-soft{background:#e9f2ff;color:#1f2937;border:1px solid #cfe0ff;font-weight:600}
.toolbar{background:rgba(255,255,255,.86);border:1px solid #e5e7eb;border-radius:16px;padding:.9rem;color:#04181e;box-shadow:0 4px 12px rgba(0,0,0,.08)}
.filter-chip{font-size:.9rem}
.view-switch .btn{min-width:130px}
.btn-primary{background:#0D6EFD;border-color:#0D6EFD}
.btn-primary:hover{background:#0b5ed7;border-color:#0b5ed7}

/* GRID tarjetas */
.grid{display:grid;grid-template-columns:repeat(1,1fr);gap:1rem;position:relative;z-index:1}
@media (min-width:576px){.grid{grid-template-columns:repeat(2,1fr)}}
@media (min-width:992px){.grid{grid-template-columns:repeat(3,1fr)}}
@media (min-width:1200px){.grid{grid-template-columns:repeat(4,1fr)}}

.user-card{position:relative;overflow:hidden;border:1px solid #e5e7eb;border-radius:1rem;background:#fff}
.user-card .banner{height:70px;background:linear-gradient(135deg,#0D6EFD,#6ea8fe 60%, #F6BD60)}
.user-card .avatar-wrap{position:relative;display:flex;justify-content:center;margin-top:-36px}
.user-card .avatar{width:72px;height:72px;border-radius:50%;object-fit:cover;border:3px solid #fff;background:#fff}
.user-card .body{padding:.75rem .75rem 1rem;color:#04181e}
.user-card .name{font-weight:700;margin:.25rem 0 0}
.user-card .username{color:#64748b;font-size:.9rem}
.pill{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .5rem;border-radius:999px;font-size:.78rem;border:1px solid #e5e7eb;background:#f8fafc}
.pill .dot{width:.5rem;height:.5rem;border-radius:50%}
.pill-role-admin{border-color:#fee2e2;background:#fff1f2}
.pill-role-rh{border-color:#dcfce7;background:#f0fdf4}
.pill-role-jefe{border-color:#e0e7ff;background:#eef2ff}
.pill-role-gerente{border-color:#ffedd5;background:#fff7ed}
.role-dot-admin{background:#ef4444}
.role-dot-rh{background:#09BC8A}
.role-dot-jefe{background:#6366f1}
.role-dot-gerente{background:#f59e0b}
.tag{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .5rem;border-radius:.5rem;background:#f1f5f9;font-size:.78rem}
.tag i{font-style:normal;opacity:.7}
.uc-actions{position:absolute;top:.5rem;right:.5rem;display:flex;gap:.35rem}
.uc-actions .btn{padding:.25rem .5rem;border-radius:.5rem}
.uc-footer{display:flex;align-items:center;justify-content:space-between;gap:.5rem;padding:.5rem .75rem .9rem}
.uc-footer .meta{font-size:.8rem;color:#64748b}
.empty{background:#fff;border:1px dashed #cbd5e1;border-radius:1rem;padding:2.5rem;text-align:center;color:#64748b}

/* TABLA */
.table > :not(caption) > * > *{vertical-align:middle}
.thumb{width:48px;height:48px;border-radius:.5rem;object-fit:cover;border:1px solid #e5e7eb;background:#fff}
.actions-col{white-space:nowrap}

/* ===== Inspector debajo del navbar ===== */
.insp-backdrop{
  position:fixed;
  left:0; right:0;
  top: var(--nav-h);               /* inicia debajo del navbar */
  bottom:0;
  background:rgba(0,0,0,.45);
  backdrop-filter: blur(2px);
  opacity:0; pointer-events:none; transition:opacity .3s ease;
  z-index: 1090;                   /* < navbar (1100) */
}
.insp-backdrop.show{ opacity:1; pointer-events:auto; }

.inspector{
  position:fixed;
  top: var(--nav-h);               /* debajo del navbar */
  right:-720px;
  height: calc(100% - var(--nav-h));
  width:min(720px,96vw);
  background:#fff; color:#04181e;
  box-shadow:-10px 0 30px rgba(0,0,0,.25);
  transition:right .35s ease;
  z-index: 1095;                   /* < navbar (1100), > contenido */
  display:flex; flex-direction:column;
}
.inspector.open{ right:0; }

/* Modo oscuro del inspector */
.inspector.dark{ background:#0f172a; color:#e5e7eb; }
.inspector.dark .insp-head{ background: linear-gradient(180deg, #0b1220, #0f172a); border-bottom-color:#1f2937;}
.inspector.dark .insp-avatar{ border-color:#1f2937; }
.inspector.dark .insp-username{ color:#9ca3af; }
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

.insp-head{ padding:1rem 1.25rem; border-bottom:1px solid #e5e7eb; background: linear-gradient(180deg, #f8fbff, #ffffff); display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
.insp-avatar{ width:64px; height:64px; border-radius:12px; object-fit:cover; border:2px solid #e5e7eb; background:#fff; }
.insp-name{ font-weight:800; font-size:1.1rem; line-height:1.2; }
.insp-username{ color:#64748b; font-size:.9rem; }
.insp-badges{ display:flex; gap:.5rem; margin-top:.35rem; flex-wrap:wrap; }
.insp-pill{ display:inline-flex; align-items:center; gap:.35rem; padding:.2rem .5rem; font-size:.78rem; border-radius:999px; background:#eef2ff; border:1px solid #e0e7ff; color:#334155}
.insp-status{ padding:.2rem .5rem; border-radius:999px; font-size:.78rem; border:1px solid #e5e7eb; }
.insp-status.ok{ background:#f0fdf4; border-color:#dcfce7; }
.insp-status.off{ background:#f8fafc; }

.insp-actions{ display:flex; gap:.5rem; align-items:center; margin-left:auto; }
.insp-close{ position:absolute; right:.5rem; top:.5rem; background:transparent; border:none; font-size:1.4rem; line-height:1; color:#64748b; }

.insp-body{ padding:1rem 1.25rem; overflow:auto; flex:1; }

.insp-sections{ display:grid; gap:1rem; grid-template-columns: 1fr; }
@media (min-width: 992px){ .insp-sections{ grid-template-columns: 1fr 1fr; } }

.insp-section{ background:#ffffff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,.04); padding:1rem; }

.sec-title{ font-weight:700; font-size:.95rem; color:#0D6EFD; margin-bottom:.6rem; letter-spacing:.2px; display:flex; align-items:center; justify-content:space-between; }
.sec-left{ display:flex; align-items:center; gap:.4rem; }
.sec-ico{ font-size:1.05rem; line-height:1; }
.sec-count{ font-weight:700; background:#eef2ff; padding:.1rem .5rem; border-radius:999px; border:1px solid #e0e7ff; color:#334155; font-size:.78rem; }

.kv-grid{ display:grid; grid-template-columns:1fr; gap:.45rem .75rem; }
.kv{ display:flex; gap:.5rem; align-items:baseline; }
.kv .k{ color:#64748b; min-width:160px; font-weight:600; }
.kv .v{ color:#04181e; }
</style>

<div class="container py-4" style="max-width:1300px">
  <div class="page-head">
    <div class="hero">
      <div class="hero-icon">👥</div>
      <div>
        <h1 class="title">Usuarios</h1>
        <p class="subtitle">Gestión de personal y accesos</p>
      </div>
    </div>
    <div class="page-meta">
      <span class="badge badge-soft">Total: <?= number_format($total) ?></span>
      <a href="crear_usuario.php" class="btn btn-primary">➕ Nuevo usuario</a>
      <a href="menu.php" class="btn btn-outline-secondary">← Regresar</a>
    </div>
  </div>

  <!-- Switch de vista -->
  <div class="d-flex justify-content-end mt-3">
    <div class="btn-group view-switch" role="group" aria-label="Cambiar vista">
      <button type="button" class="btn btn-outline-primary" id="btnViewCards">▦ Tarjetas</button>
      <button type="button" class="btn btn-outline-primary" id="btnViewTable">☰ Tabla</button>
    </div>
  </div>

  <!-- Filtros -->
  <form class="toolbar mt-3" method="get" id="filtros">
    <div class="row g-2">
      <div class="col-12 col-md-5">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Buscar: nombre, usuario, número, correo">
      </div>
      <div class="col-6 col-md-3">
        <select name="sede_id" class="form-select">
          <option value="">— Todas las sedes —</option>
          <?php foreach($sedes as $s): ?>
            <option value="<?= (int)$s['id'] ?>" <?= ($sede_id!=='' && (int)$sede_id===(int)$s['id'])?'selected':''; ?>>
              <?= htmlspecialchars($s['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="rol" class="form-select">
          <?php foreach($roles as $k=>$v): ?>
            <option value="<?= $k ?>" <?= ($rol===$k)?'selected':''; ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <select name="estado" class="form-select">
          <?php foreach($estados as $k=>$v): ?>
            <option value="<?= $k ?>" <?= ($estado===$k)?'selected':''; ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 d-flex justify-content-between">
        <div class="d-flex flex-wrap gap-2 mt-1">
          <?php if ($q!==''): ?>
            <span class="badge rounded-pill text-bg-light filter-chip">🔎 <?= htmlspecialchars($q) ?></span>
          <?php endif; ?>
          <?php if ($sede_id!==''): ?>
            <?php $sn = array_values(array_filter($sedes, fn($x)=> (int)$x['id']===(int)$sede_id)); ?>
            <span class="badge rounded-pill text-bg-light filter-chip">📍 <?= htmlspecialchars($sn[0]['nombre'] ?? 'Sede') ?></span>
          <?php endif; ?>
          <?php if ($rol!==''): ?>
            <span class="badge rounded-pill text-bg-light filter-chip">🧩 <?= htmlspecialchars($roles[$rol] ?? $rol) ?></span>
          <?php endif; ?>
          <?php if ($estado!==''): ?>
            <span class="badge rounded-pill text-bg-light filter-chip">⚙️ <?= htmlspecialchars(ucfirst($estado)) ?></span>
          <?php endif; ?>
        </div>
        <div class="d-flex gap-2">
          <a class="btn btn-outline-secondary" href="lista_usuario.php">Limpiar</a>
          <button class="btn btn-outline-primary">Aplicar filtros</button>
        </div>
      </div>
    </div>
  </form>

  <!-- VISTA TARJETAS -->
  <div id="viewCards" class="mt-3">
    <div class="grid">
      <?php if (!$usuarios): ?>
        <div class="empty col-12">
          <div style="font-size:2rem;line-height:1">🗂️</div>
          <div class="mt-2">No hay resultados con los filtros actuales.</div>
        </div>
      <?php else: foreach($usuarios as $u): ?>
        <?php
          $src = !empty($u['fotografia'])
            ? $dirWeb . htmlspecialchars($u['fotografia'])
            : 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2272%22 height=%2272%22><rect width=%22100%25%22 height=%22100%25%22 fill=%22%23f1f5f9%22/></svg>';
          $rolePillClass = [
            'admin'     => ['pill-role-admin','role-dot-admin'],
            'rh'        => ['pill-role-rh','role-dot-rh'],
            'jefe_area' => ['pill-role-jefe','role-dot-jefe'],
            'gerente'   => ['pill-role-gerente','role-dot-gerente'],
          ];
          [$pillClass, $dotClass] = $rolePillClass[$u['rol']] ?? ['pill-role-jefe','role-dot-jefe'];
          $rolTxt = $roles[$u['rol']] ?? $u['rol'];
        ?>
        <div class="user-card">
          <div class="banner"></div>

          <div class="uc-actions">
            <a href="editar_usuario.php?id=<?= (int)$u['id'] ?>" class="btn btn-light btn-sm" title="Editar">✏️</a>
            <button type="button" class="btn btn-outline-danger btn-sm btn-del" data-id="<?= (int)$u['id'] ?>" title="Desactivar">🚫</button>
          </div>

          <div class="avatar-wrap">
            <img src="<?= $src ?>" class="avatar" alt="Foto">
          </div>

          <div class="body">
            <div class="name"><?= htmlspecialchars($u['nombre_completo']) ?></div>
            <div class="username">@<?= htmlspecialchars(strtolower($u['usuario'])) ?> · #<?= htmlspecialchars($u['numero_empleado']) ?></div>

            <div class="d-flex flex-wrap gap-2 mt-2">
              <span class="pill <?= $pillClass ?>"><span class="dot <?= $dotClass ?>"></span><?= htmlspecialchars($rolTxt) ?></span>
              <?php if(!empty($u['sede'])): ?>
                <span class="tag"><i>📍</i><?= htmlspecialchars($u['sede']) ?></span>
              <?php endif; ?>
              <?php if(!empty($u['departamento'])): ?>
                <span class="tag"><i>🏷️</i><?= htmlspecialchars($u['departamento']) ?></span>
              <?php endif; ?>
            </div>

            <div class="d-flex flex-column gap-1 mt-2" style="font-size:.9rem">
              <?php if(!empty($u['correo'])): ?>
                <a href="mailto:<?= htmlspecialchars($u['correo']) ?>" class="link-secondary text-decoration-none">✉️ <?= htmlspecialchars($u['correo']) ?></a>
              <?php endif; ?>
              <?php if(!empty($u['telefono'])): ?>
                <a href="tel:<?= htmlspecialchars($u['telefono']) ?>" class="link-secondary text-decoration-none">📞 <?= htmlspecialchars($u['telefono']) ?></a>
              <?php endif; ?>
            </div>
          </div>

          <div class="uc-footer">
            <div class="meta"><?= ($u['estado']==='activo') ? '🟢 Activo' : '⚪ Inactivo' ?></div>
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-sm btn-outline-primary btn-open-inspector" data-id="<?= (int)$u['id'] ?>">👁️ Detalles</button>
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
              <th>Foto</th>
              <th>Usuario</th>
              <th>Nombre</th>
              <th>No. Emp.</th>
              <th>Rol</th>
              <th>Sede</th>
              <th>Departamento</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$usuarios): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No hay resultados con los filtros actuales.</td></tr>
          <?php else: foreach($usuarios as $u): ?>
            <?php
              $src = !empty($u['fotografia'])
                ? $dirWeb . htmlspecialchars($u['fotografia'])
                : 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2248%22 height=%2248%22><rect width=%22100%25%22 height=%22100%25%22 fill=%22%23f1f5f9%22/></svg>';
            ?>
            <tr>
              <td><img class="thumb" src="<?= $src ?>" alt="Foto"></td>
              <td><strong><?= htmlspecialchars($u['usuario']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($u['correo'] ?? '') ?></small></td>
              <td><?= htmlspecialchars($u['nombre_completo']) ?></td>
              <td><?= htmlspecialchars($u['numero_empleado']) ?></td>
              <td><?= htmlspecialchars($roles[$u['rol']] ?? $u['rol']) ?></td>
              <td><?= htmlspecialchars($u['sede'] ?? '—') ?></td>
              <td><?= htmlspecialchars($u['departamento'] ?? '—') ?></td>
              <td class="text-end actions-col">
                <button type="button" class="btn btn-sm btn-outline-primary btn-open-inspector" data-id="<?= (int)$u['id'] ?>">👁️ Detalles</button>
                <a href="editar_usuario.php?id=<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">✏️</a>
                <button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="<?= (int)$u['id'] ?>" title="Desactivar">🚫</button>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Paginación -->
  <?php if ($pages > 1): ?>
  <nav class="mt-3">
    <ul class="pagination justify-content-center">
      <?php
        $qs = $_GET;
        $start = max(1, $pag-2);
        $end   = min($pages, $pag+2);
        if ($start > 1){
          $qs['pag']=1; echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($qs) . '">«</a></li>';
        }
        for ($i=$start; $i<=$end; $i++):
          $qs['pag']=$i; $link='?'.http_build_query($qs);
      ?>
        <li class="page-item <?= ($i===$pag)?'active':'' ?>"><a class="page-link" href="<?= $link ?>"><?= $i ?></a></li>
      <?php endfor;
        if ($end < $pages){
          $qs['pag']=$pages; echo '<li class="page-item"><a class="page-link" href="?' . http_build_query($qs) . '">»</a></li>';
        }
      ?>
    </ul>
    <div class="text-center text-muted" style="font-size:.9rem">Página <?= $pag ?> de <?= $pages ?> · Mostrando <?= min($limit, $total - $off) ?> de <?= number_format($total) ?></div>
  </nav>
  <?php endif; ?>
</div>

<!-- PANEL LATERAL + BACKDROP -->
<div class="insp-backdrop" id="inspBackdrop" aria-hidden="true"></div>
<aside class="inspector" id="inspector" aria-hidden="true" aria-label="Detalles de usuario" role="dialog">
  <button class="insp-close" id="inspClose" aria-label="Cerrar">×</button>
  <div id="inspContent">
    <div class="p-3 text-muted">Selecciona un usuario…</div>
  </div>
</aside>

<?php if ($flash_ok || $flash_error): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
  icon: '<?= $flash_ok ? 'success' : 'error' ?>',
  title: '<?= addslashes($flash_ok ?: $flash_error) ?>',
  timer: 1900,
  showConfirmButton: false
});
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ---- Switch de vista con localStorage ----
const KEY = 'usersView';
const btnCards = document.getElementById('btnViewCards');
const btnTable = document.getElementById('btnViewTable');
const viewCards = document.getElementById('viewCards');
const viewTable = document.getElementById('viewTable');
function applyView(v){
  if (v === 'table'){
    viewCards.style.display='none';
    viewTable.style.display='';
    btnTable?.classList.add('btn-primary'); btnTable?.classList.remove('btn-outline-primary');
    btnCards?.classList.add('btn-outline-primary'); btnCards?.classList.remove('btn-primary');
  } else {
    viewCards.style.display='';
    viewTable.style.display='none';
    btnCards?.classList.add('btn-primary'); btnCards?.classList.remove('btn-outline-primary');
    btnTable?.classList.add('btn-outline-primary'); btnTable?.classList.remove('btn-primary');
  }
  try{ localStorage.setItem(KEY, v); }catch(e){}
}
try { applyView(localStorage.getItem(KEY) || 'cards'); } catch(e){ applyView('cards'); }
btnCards?.addEventListener('click', ()=>applyView('cards'));
btnTable?.addEventListener('click', ()=>applyView('table'));

// ---- Desactivar (soft delete) desde el mismo archivo ----
document.addEventListener('click', async (ev)=>{
  const btn = ev.target.closest('.btn-del');
  if (!btn) return;
  const id = btn.dataset.id;
  const conf = await Swal.fire({
    icon:'warning',
    title:'¿Desactivar usuario?',
    text:'Podrás reactivarlo más tarde desde la edición.',
    showCancelButton:true,
    confirmButtonText:'Sí, desactivar',
    cancelButtonText:'Cancelar'
  });
  if (!conf.isConfirmed) return;

  const fd = new FormData();
  fd.append('action','desactivar');
  fd.append('id', id);

  btn.disabled = true;
  try{
    const r = await fetch(location.pathname, { method:'POST', body: fd, credentials: 'same-origin' });
    const ct = r.headers.get('content-type') || '';
    const data = ct.includes('application/json') ? await r.json() : {ok:false, msg: await r.text()};

    if (data.ok){
      await Swal.fire({icon:'success', title: data.msg || 'Usuario desactivado', timer:1300, showConfirmButton:false});
      location.reload();
    } else {
      throw new Error(data.msg || 'No se pudo desactivar');
    }
  }catch(e){
    Swal.fire({icon:'error', title:'Error', text: e.message.slice(0,300)});
  }finally{
    btn.disabled = false;
  }
});

// ---- Panel lateral (inspector) ----
const insp = document.getElementById('inspector');
const inspBackdrop = document.getElementById('inspBackdrop');
const inspClose = document.getElementById('inspClose');
const inspContent = document.getElementById('inspContent');

function openInspector(){
  updateInspectorTheme();
  insp.classList.add('open');
  inspBackdrop.classList.add('show');
  insp.setAttribute('aria-hidden','false');
  inspBackdrop.setAttribute('aria-hidden','false');
  document.body.style.overflow = 'hidden';
}
function closeInspector(){
  insp.classList.remove('open');
  inspBackdrop.classList.remove('show');
  insp.setAttribute('aria-hidden','true');
  inspBackdrop.setAttribute('aria-hidden','true');
  document.body.style.overflow = '';
}
inspBackdrop.addEventListener('click', closeInspector);
inspClose.addEventListener('click', closeInspector);
document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeInspector(); });

async function fetchDetalles(id){
  const url = `?detalles_id=${encodeURIComponent(id)}`;
  const r = await fetch(url, { credentials:'same-origin' });
  const ct = r.headers.get('content-type') || '';
  if (!ct.includes('application/json')) {
    const txt = await r.text(); throw new Error(txt || 'Respuesta inesperada');
  }
  const data = await r.json();
  if (!data.ok) throw new Error(data.msg || 'No se pudieron cargar los detalles');
  return data.html;
}

document.addEventListener('click', async (e)=>{
  const btn = e.target.closest('.btn-open-inspector');
  if (!btn) return;
  const id = btn.dataset.id;

  inspContent.innerHTML = `<div class="p-3 text-muted">Cargando…</div>`;
  openInspector();

  try{
    const html = await fetchDetalles(id);
    inspContent.innerHTML = html;
  }catch(err){
    inspContent.innerHTML = `<div class="p-3 text-danger">⚠️ ${ (err?.message || 'Error al cargar') }</div>`;
  }
});

// Desactivar desde dentro del inspector
document.addEventListener('click', async (e)=>{
  const b = e.target.closest('.js-insp-desactivar');
  if (!b) return;
  const id = b.dataset.id;

  const conf = await Swal.fire({
    icon:'warning',
    title:'¿Desactivar usuario?',
    text:'Podrás reactivarlo más tarde desde la edición.',
    showCancelButton:true,
    confirmButtonText:'Sí, desactivar',
    cancelButtonText:'Cancelar'
  });
  if (!conf.isConfirmed) return;

  const fd = new FormData();
  fd.append('action','desactivar');
  fd.append('id', id);

  b.disabled = true;
  try{
    const r = await fetch(location.pathname, { method:'POST', body: fd, credentials:'same-origin' });
    const data = await r.json();
    if (data.ok){
      await Swal.fire({icon:'success', title: data.msg || 'Usuario desactivado', timer:1200, showConfirmButton:false});
      closeInspector(); location.reload();
    } else {
      throw new Error(data.msg || 'No se pudo desactivar');
    }
  }catch(e2){
    Swal.fire({icon:'error', title:'Error', text: e2.message.slice(0,300)});
  }finally{
    b.disabled = false;
  }
});

/* ====== Tema automático del panel según fondo del body ====== */
function parseRGB(str){
  const m = (str || '').match(/\d+/g);
  if (!m) return [255,255,255];
  return m.slice(0,3).map(Number);
}
function relLuma([r,g,b]){
  r/=255; g/=255; b/=255;
  const lin = v => v <= .03928 ? v/12.92 : Math.pow((v+0.055)/1.055, 2.4);
  r=lin(r); g=lin(g); b=lin(b);
  return 0.2126*r + 0.7152*g + 0.0722*b;
}
function updateInspectorTheme(){
  const bg = getComputedStyle(document.body).backgroundColor;
  const L = relLuma(parseRGB(bg));
  if (L < 0.40){
    insp.classList.add('dark');
    inspBackdrop.classList.add('dark');
  } else {
    insp.classList.remove('dark');
    inspBackdrop.classList.remove('dark');
  }
}
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
