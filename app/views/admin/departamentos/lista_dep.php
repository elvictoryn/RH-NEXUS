<?php
if (!isset($_SESSION)) session_start();
require_once dirname(__DIR__, 4) . '/config/conexion.php';

$db = Conexion::getConexion();

/* ============================================================
   ENDPOINT AJAX: Detalles (antes de imprimir HTML)
   ============================================================ */
if (isset($_GET['detalles_id'])) {
  header('Content-Type: application/json; charset=utf-8');
  try {
    $id = (int)$_GET['detalles_id'];
    if ($id <= 0) throw new Exception('ID inválido');

    $sql = "SELECT d.id, d.nombre, d.descripcion, d.estado,
                   s.id AS sede_id, s.nombre AS sede_nombre, s.municipio, s.estado AS estado_sede, s.telefono AS tel_sede,
                   u.id AS responsable_id, u.nombre_completo AS responsable_nombre, u.correo AS responsable_correo, u.telefono AS responsable_tel
            FROM departamentos d
            LEFT JOIN sedes s ON s.id = d.sede_id
            LEFT JOIN usuarios u ON u.id = d.responsable_id
            WHERE d.id=:id
            LIMIT 1";
    $st = $db->prepare($sql);
    $st->execute([':id'=>$id]);
    $d = $st->fetch(PDO::FETCH_ASSOC);
    if (!$d) throw new Exception('Departamento no encontrado');

    // Conteo de usuarios asignados (si existe la columna)
    $countUsers = 0;
    try {
      $cst = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE departamento_id=:id");
      $cst->execute([':id'=>$id]);
      $countUsers = (int)$cst->fetchColumn();
    } catch (\Throwable $e) { /* ignorar si no existe columna */ }

    $h = fn($v)=>htmlspecialchars((string)$v ?? '', ENT_QUOTES, 'UTF-8');
    $v = fn($x)=> trim((string)$x) !== '' ? $x : '—';

    ob_start(); ?>
    <div class="insp-head">
      <div class="d-flex align-items-center gap-3">
        <div class="sd-avatar">🏷️</div>
        <div>
          <div class="insp-name"><?= $h($v($d['nombre'])) ?></div>
          <div class="insp-username"><?= $h($v($d['sede_nombre'])) ?></div>
          <div class="insp-badges">
            <span class="insp-status <?= ($d['estado']==='activo'?'ok':'off') ?>"><?= ($d['estado']==='activo'?'🟢 Activo':'⚪ Inactivo') ?></span>
            <span class="insp-pill">👥 <?= $countUsers ?> usuario<?= $countUsers===1?'':'s' ?></span>
          </div>
        </div>
      </div>
      <div class="insp-actions">
        <a href="editar_dep.php?id=<?= (int)$d['id'] ?>" class="btn btn-sm btn-outline-secondary">✏️ Editar</a>
      </div>
    </div>

    <div class="insp-body">
      <div class="insp-sections">
        <section class="insp-section">
          <div class="sec-title"><div class="sec-left"><span class="sec-ico">📄</span><span>Descripción</span></div></div>
          <div><?= nl2br($h($v($d['descripcion']))) ?></div>
        </section>

        <section class="insp-section">
          <div class="sec-title"><div class="sec-left"><span class="sec-ico">🏢</span><span>Ubicación (Sede)</span></div></div>
          <div class="kv-grid">
            <div class="kv"><div class="k">Sede</div><div class="v"><?= $h($v($d['sede_nombre'])) ?></div></div>
            <div class="kv"><div class="k">Municipio</div><div class="v"><?= $h($v($d['municipio'])) ?></div></div>
            <div class="kv"><div class="k">Estado</div><div class="v"><?= $h($v($d['estado_sede'])) ?></div></div>
            <div class="kv"><div class="k">Teléfono sede</div><div class="v"><?= $h($v($d['tel_sede'])) ?></div></div>
          </div>
        </section>

        <section class="insp-section">
          <div class="sec-title"><div class="sec-left"><span class="sec-ico">👤</span><span>Responsable</span></div></div>
          <div class="kv-grid">
            <div class="kv"><div class="k">Nombre</div><div class="v"><?= $h($v($d['responsable_nombre'])) ?></div></div>
            <div class="kv"><div class="k">Correo</div><div class="v"><?= $h($v($d['responsable_correo'])) ?></div></div>
            <div class="kv"><div class="k">Teléfono</div><div class="v"><?= $h($v($d['responsable_tel'])) ?></div></div>
          </div>
        </section>
      </div>
    </div>
    <?php
    echo json_encode(['ok'=>true,'html'=>ob_get_clean()], JSON_UNESCAPED_UNICODE);
  } catch (\Throwable $e) {
    echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
  }
  exit;
}

/* ============================================================
   Filtros + Datos
   ============================================================ */
$q       = trim($_GET['q'] ?? '');
$sede_id = $_GET['sede_id'] ?? '';
$resp_id = $_GET['resp_id'] ?? '';
$estado  = $_GET['estado']  ?? '';

$where=[]; $p=[];
if ($q!==''){
  $where[]="(UPPER(d.nombre) LIKE :q OR UPPER(d.descripcion) LIKE :q OR UPPER(s.nombre) LIKE :q OR UPPER(u.nombre_completo) LIKE :q)";
  $p[':q'] = '%'.mb_strtoupper($q, 'UTF-8').'%';
}
if ($sede_id!==''){ $where[]="d.sede_id=:sede"; $p[':sede']=(int)$sede_id; }
if ($resp_id!==''){ $where[]="d.responsable_id=:resp"; $p[':resp']=(int)$resp_id; }
if ($estado!==''){  $where[]="LOWER(d.estado)=:est"; $p[':est']=strtolower($estado); }

$whereSql = $where?('WHERE '.implode(' AND ', $where)):'';

$pag   = max(1, (int)($_GET['pag'] ?? 1));
$limit = 12;
$off   = ($pag-1)*$limit;

$stc = $db->prepare("SELECT COUNT(*) FROM departamentos d
                     LEFT JOIN sedes s ON s.id=d.sede_id
                     LEFT JOIN usuarios u ON u.id=d.responsable_id
                     $whereSql");
$stc->execute($p);
$total = (int)$stc->fetchColumn();
$pages = max(1, (int)ceil($total/$limit));

$sql = "SELECT d.id, d.nombre, d.descripcion, d.estado,
               s.nombre AS sede_nombre,
               u.nombre_completo AS responsable
        FROM departamentos d
        LEFT JOIN sedes s ON s.id=d.sede_id
        LEFT JOIN usuarios u ON u.id=d.responsable_id
        $whereSql
        ORDER BY d.nombre ASC
        LIMIT :lim OFFSET :off";
$st = $db->prepare($sql);
foreach($p as $k=>$v) $st->bindValue($k,$v);
$st->bindValue(':lim',$limit,PDO::PARAM_INT);
$st->bindValue(':off',$off,PDO::PARAM_INT);
$st->execute();
$deps = $st->fetchAll(PDO::FETCH_ASSOC);

/* Catálogos */
$sedes = $db->query("SELECT id, nombre FROM sedes ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$resps = $db->query("SELECT id, nombre_completo FROM usuarios WHERE rol='jefe_area' ORDER BY nombre_completo")->fetchAll(PDO::FETCH_ASSOC);

/* Mensajes (Sesión) */
$flash_ok    = $_SESSION['mensaje_exito'] ?? null;
$flash_error = $_SESSION['mensaje_error'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['mensaje_error']);

/* Header global */
$titulo_pagina = "Departamentos";
include_once('../../shared/header.php');

/* Ruta para imágenes (si usas alguna más adelante) */
?>
<style>
/* ====== Estilos utilitarios (coherentes con Sedes/Usuarios) ====== */
.page-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;padding:.85rem 1rem;border-radius:16px;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);backdrop-filter:blur(8px);box-shadow:0 6px 16px rgba(0,0,0,.12)}
.hero{display:flex;align-items:center;gap:.8rem}
.hero .hero-icon{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;background:linear-gradient(135deg,#0D6EFD,#6ea8fe);color:#fff;font-size:1.25rem;box-shadow:0 6px 14px rgba(13,110,253,.35)}
.hero .title{margin:0;line-height:1.1;font-weight:900;letter-spacing:.2px;font-size:clamp(1.8rem, 2.6vw + .6rem, 2.6rem);background:linear-gradient(90deg,#ffffff 0%, #e6f0ff 60%, #fff);-webkit-background-clip:text;background-clip:text;color:transparent;text-shadow:0 1px 0 rgba(0,0,0,.12)}
.hero .subtitle{margin:0;color:#e8eef7;font-size:.95rem;font-weight:500;opacity:.95}
.badge-soft{background:#e9f2ff;color:#1f2937;border:1px solid #cfe0ff;font-weight:600}
.toolbar{background:rgba(255,255,255,.86);border:1px solid #e5e7eb;border-radius:16px;padding:.9rem;color:#04181e;box-shadow:0 4px 12px rgba(0,0,0,.08)}
.view-switch .btn{min-width:130px}
.btn-primary{background:#0D6EFD;border-color:#0D6EFD}
.btn-primary:hover{background:#0b5ed7;border-color:#0b5ed7}
.grid{display:grid;grid-template-columns:repeat(1,1fr);gap:1rem}
@media (min-width:576px){.grid{grid-template-columns:repeat(2,1fr)}}
@media (min-width:992px){.grid{grid-template-columns:repeat(3,1fr)}}
@media (min-width:1200px){.grid{grid-template-columns:repeat(4,1fr)}}
.dep-card{position:relative;overflow:hidden;border:1px solid #e5e7eb;border-radius:1rem;background:#fff}
.dep-card .banner{height:64px;background:linear-gradient(135deg,#0D6EFD,#6ea8fe 60%, #F6BD60)}
.dep-card .body{padding:.75rem .75rem 1rem;color:#04181e}
.dep-card .name{font-weight:800;font-size:1.05rem}
.dep-card .meta{color:#64748b;font-size:.9rem}
.dep-actions{position:absolute;top:.5rem;right:.5rem;display:flex;gap:.35rem}
.dep-actions .btn{padding:.25rem .5rem;border-radius:.5rem}
.dep-footer{display:flex;align-items:center;justify-content:space-between;gap:.5rem;padding:.5rem .75rem .9rem}
.tag{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .5rem;border-radius:.5rem;background:#f1f5f9;font-size:.78rem;color:#334155}
.empty{background:#fff;border:1px dashed #cbd5e1;border-radius:1rem;padding:2.5rem;text-align:center;color:#64748b}
.table > :not(caption) > * > *{vertical-align:middle}
.actions-col{white-space:nowrap}

/* Inspector lateral */
.insp-backdrop{position:fixed; inset:0; background:rgba(0,0,0,.45); backdrop-filter:blur(2px); opacity:0; pointer-events:none; transition:opacity .3s ease; z-index:1055}
.insp-backdrop.show{opacity:1; pointer-events:auto}
.inspector{position:fixed; top:0; right:-720px; height:100%; width:min(720px,96vw); background:#fff; color:#04181e; box-shadow:-10px 0 30px rgba(0,0,0,.25); transition:right .35s ease; z-index:1060; display:flex; flex-direction:column}
.inspector.open{right:0}
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
.kv-grid{display:grid;grid-template-columns:1fr;gap:.45rem .75rem}
.kv{display:flex;gap:.5rem;align-items:baseline}
.kv .k{color:#64748b;min-width:160px;font-weight:600}
.kv .v{color:#04181e}
</style>

<div class="container py-4" style="max-width:1300px">
  <!-- Hero -->
  <div class="page-head">
    <div class="hero">
      <div class="hero-icon">🧩</div>
      <div>
        <h1 class="title">Departamentos</h1>
        <p class="subtitle">Catálogo por sede y responsable</p>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <span class="badge-soft">Total: <?= number_format($total) ?></span>
      <a href="crear_dep.php" class="btn btn-primary">➕ Nuevo</a>
      <a href="menu.php" class="btn btn-outline-secondary">← Regresar</a>
    </div>
  </div>

  <!-- Switch centrado (igual que Usuarios) -->
  <div class="d-flex justify-content-center mt-3">
    <div class="btn-group view-switch" role="group" aria-label="Cambiar vista">
      <button type="button" class="btn btn-outline-primary" id="btnViewCards">▦ Tarjetas</button>
      <button type="button" class="btn btn-outline-primary" id="btnViewTable">☰ Tabla</button>
    </div>
  </div>

  <!-- Filtros -->
  <form class="toolbar mt-3" method="get" id="filtros">
    <div class="row g-2 align-items-end">
      <div class="col-12 col-lg-5">
        <div class="input-group input-icon">
          <span class="input-group-text">🔎</span>
          <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Buscar: nombre, descripción, sede o responsable">
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <select name="sede_id" class="form-select">
          <option value="">— Todas las sedes —</option>
          <?php foreach($sedes as $s): ?>
            <option value="<?= (int)$s['id'] ?>" <?= ($sede_id!=='' && (int)$sede_id===(int)$s['id'])?'selected':''; ?>>
              <?= htmlspecialchars($s['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-lg-3">
        <select name="resp_id" class="form-select">
          <option value="">— Todos los responsables —</option>
          <?php foreach($resps as $r): ?>
            <option value="<?= (int)$r['id'] ?>" <?= ($resp_id!=='' && (int)$resp_id===(int)$r['id'])?'selected':''; ?>>
              <?= htmlspecialchars($r['nombre_completo']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-lg-1">
        <select name="estado" class="form-select">
          <option value="">— Todos —</option>
          <option value="activo"   <?= ($estado==='activo'?'selected':'') ?>>Activos</option>
          <option value="inactivo" <?= ($estado==='inactivo'?'selected':'') ?>>Inactivos</option>
        </select>
      </div>
      <div class="col-12 d-flex justify-content-between mt-2">
        <div></div>
        <div class="d-flex gap-2">
          <a class="btn btn-outline-secondary" href="<?= basename($_SERVER['PHP_SELF']) ?>">Limpiar</a>
          <button class="btn btn-outline-primary">Aplicar filtros</button>
        </div>
      </div>
    </div>
  </form>

  <!-- VISTA TARJETAS -->
  <div id="viewCards" class="mt-3">
    <div class="grid">
      <?php if (!$deps): ?>
        <div class="empty col-12">
          <div style="font-size:2rem;line-height:1">🗂️</div>
          <div class="mt-2">No hay resultados con los filtros actuales.</div>
        </div>
      <?php else: foreach ($deps as $d): ?>
        <div class="dep-card">
          <div class="banner"></div>

          <div class="dep-actions">
            <a href="editar_dep.php?id=<?= (int)$d['id'] ?>" class="btn btn-light btn-sm" title="Editar">✏️</a>
            <button type="button" class="btn btn-outline-danger btn-sm btn-del" data-id="<?= (int)$d['id'] ?>" title="Eliminar">🗑️</button>
          </div>

          <div class="body">
            <div class="name"><?= htmlspecialchars($d['nombre']) ?></div>
            <div class="meta">📍 <?= htmlspecialchars($d['sede_nombre'] ?? '—') ?></div>
            <div class="d-flex flex-wrap gap-2 mt-2">
              <span class="tag">👤 <?= htmlspecialchars($d['responsable'] ?: 'No asignado') ?></span>
              <span class="tag"><?= ($d['estado']==='activo'?'🟢 Activo':'⚪ Inactivo') ?></span>
            </div>
          </div>

          <div class="dep-footer">
            <div class="meta"></div>
            <button type="button" class="btn btn-sm btn-outline-primary btn-open-inspector" data-id="<?= (int)$d['id'] ?>">👁️ Detalles</button>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

  <!-- VISTA TABLA -->
  <div id="viewTable" class="mt-3" style="display:none">
    <div class="card shadow-sm p-2">
      <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover" id="tablaDepartamentos">
          <thead>
            <tr class="text-center">
              <th>Nombre</th>
              <th>Sede</th>
              <th>Responsable</th>
              <th>Estatus</th>
              <th class="text-end">Acciones</th>
            </tr>
          </thead>
          <tbody class="text-center">
            <?php if (!$deps): ?>
              <tr><td colspan="5" class="text-muted py-4">No hay resultados.</td></tr>
            <?php else: foreach($deps as $d): ?>
              <tr>
                <td><strong><?= htmlspecialchars($d['nombre']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($d['descripcion'] ?? '') ?></small></td>
                <td><span class="tag">📍 <?= htmlspecialchars($d['sede_nombre'] ?? '—') ?></span></td>
                <td><?= $d['responsable'] ? '<span class="tag">👤 '.htmlspecialchars($d['responsable']).'</span>' : '<span class="text-muted">No asignado</span>' ?></td>
                <td><?= ($d['estado']==='activo'?'🟢 Activo':'⚪ Inactivo') ?></td>
                <td class="text-end actions-col">
                  <button type="button" class="btn btn-sm btn-outline-primary btn-open-inspector" data-id="<?= (int)$d['id'] ?>" title="Detalles">👁️</button>
                  <a href="editar_dep.php?id=<?= (int)$d['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Editar">✏️</a>
                  <button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="<?= (int)$d['id'] ?>" title="Eliminar">🗑️</button>
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
<aside class="inspector" id="inspector" aria-hidden="true" aria-label="Detalles de departamento" role="dialog">
  <button class="insp-close" id="inspClose" aria-label="Cerrar">×</button>
  <div id="inspContent"><div class="p-3 text-muted">Selecciona un departamento…</div></div>
</aside>

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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Preferencia de vista (igual que Usuarios)
const KEY='depsView';
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

// Inspector lateral (Ver detalles)
const insp=document.getElementById('inspector');
const inspBackdrop=document.getElementById('inspBackdrop');
const inspClose=document.getElementById('inspClose');
const inspContent=document.getElementById('inspContent');
function openInspector(){ insp.classList.add('open'); inspBackdrop.classList.add('show'); insp.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
function closeInspector(){ insp.classList.remove('open'); inspBackdrop.classList.remove('show'); insp.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
inspBackdrop.addEventListener('click', closeInspector);
inspClose.addEventListener('click', closeInspector);
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeInspector(); });

async function fetchDetalles(id){
  const r = await fetch(`?detalles_id=${encodeURIComponent(id)}`, {credentials:'same-origin'});
  const data = await r.json(); // asegurado porque no hubo HTML antes del JSON
  if(!data.ok) throw new Error(data.msg||'Error');
  return data.html;
}
async function openDetalles(id){
  inspContent.innerHTML = `<div class="p-3 text-muted">Cargando…</div>`;
  openInspector();
  try{ const html = await fetchDetalles(id); inspContent.innerHTML = html; }
  catch(e){ inspContent.innerHTML = `<div class="p-3 text-danger">⚠️ ${ (e?.message||'No se pudo cargar') }</div>`; }
}
document.addEventListener('click', e=>{
  const b=e.target.closest('.btn-open-inspector'); if(!b) return;
  openDetalles(b.dataset.id);
});

// Eliminar = INACTIVAR (igual que Usuarios)
document.querySelectorAll('.btn-del').forEach(btn=>{
  btn.addEventListener('click', async ()=>{
    const id=btn.dataset.id;
    const conf=await Swal.fire({icon:'warning', title:'¿Inactivar departamento?', text:'Podrás reactivarlo editándolo más tarde.', showCancelButton:true, confirmButtonText:'Sí, inactivar', cancelButtonText:'Cancelar'});
    if(!conf.isConfirmed) return;

    const fd=new FormData(); fd.append('id', id);
    try{
      const r=await fetch('eliminar_dep.php', {method:'POST', body: fd});
      const data=await r.json();
      if(data.ok){
        await Swal.fire({icon:'success', title: data.msg || 'Departamento inactivado', timer:1300, showConfirmButton:false});
        location.reload();
      }else{
        Swal.fire({icon:'error', title: data.msg || 'No se pudo inactivar'});
      }
    }catch(e){
      Swal.fire({icon:'error', title:'Error de red'});
    }
  });
});
</script>
