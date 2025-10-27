<?php
// ============================================================
// Agenda (todos los roles) · Sedes → Gerente (encabezado) → Departamentos → Contactos
// ============================================================
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
if (session_status() === PHP_SESSION_NONE) session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
$db = Conexion::getConexion();

/* ---- Sesión mínima ---- */
$UID  = (int)($_SESSION['id'] ?? 0);
if (!$UID) { header('Location: '.BASE_PATH.'/public/login.php'); exit; }

/* ---- Utils ---- */
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function initials($name){
  $n = trim((string)$name); if ($n==='') return 'U';
  $p = preg_split('/\s+/u',$n);
  $ini = mb_substr($p[0],0,1,'UTF-8');
  if (count($p)>1) $ini .= mb_substr(end($p),0,1,'UTF-8');
  return mb_strtoupper($ini,'UTF-8');
}

/* ---- SEDES con datos visibles ---- */
$sedesInfo = [];
try{
  $qS = $db->query("
    SELECT
      s.id,
      s.nombre,
      s.gerente_id,
      COALESCE(s.telefono, s.telefono_sede, s.telefono1, s.tel, s.telefono_sucursal) AS sede_telefono,
      COALESCE(s.domicilio, s.direccion, s.`dirección`, s.ubicacion, s.`ubicación`, s.domicilio_sucursal) AS sede_domicilio,
      COALESCE(s.latitud, s.lat) AS latitud,
      COALESCE(s.longitud, s.lng) AS longitud
    FROM sedes s
    ORDER BY s.nombre ASC
  ");
  $tmp = $qS->fetchAll(PDO::FETCH_ASSOC) ?: [];
  foreach($tmp as $s){ $sedesInfo[(int)$s['id']] = $s; }
}catch(Throwable $e){}

/* ---- Catálogo para filtros ---- */
$sedes = [];
foreach($sedesInfo as $s){ $sedes[] = ['id'=>$s['id'], 'nombre'=>$s['nombre']]; }
$departamentos = [];
try{
  $departamentos = $db->query("SELECT id, nombre FROM departamentos ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
}catch(Throwable $e){}

/* ---- Filtros ---- */
$sedeId = isset($_GET['sede']) ? (int)$_GET['sede'] : 0;
$depId  = isset($_GET['dep'])  ? (int)$_GET['dep']  : 0;
$q      = trim((string)($_GET['q'] ?? ''));

/* ---- Contactos activos ---- */
$sql = "
  SELECT
    u.id, u.nombre_completo, u.usuario, u.correo, u.telefono, u.rol, u.fotografia,
    u.sede_id, u.departamento_id,
    s.nombre AS sede_nombre, s.gerente_id,
    d.nombre AS dep_nombre
  FROM usuarios u
  LEFT JOIN sedes s ON s.id = u.sede_id
  LEFT JOIN departamentos d ON d.id = u.departamento_id
  WHERE u.estado='activo' AND (COALESCE(u.telefono,'')<>'' OR COALESCE(u.correo,'')<>'')
";
$params=[];
if ($sedeId>0){ $sql.=" AND u.sede_id=:sede"; $params[':sede']=$sedeId; }
if ($depId>0){  $sql.=" AND u.departamento_id=:dep"; $params[':dep']=$depId; }
if ($q!==''){
  $sql.=" AND (u.nombre_completo LIKE :q OR u.usuario LIKE :q OR u.correo LIKE :q OR u.telefono LIKE :q)";
  $params[':q'] = "%$q%";
}
$sql.=" ORDER BY s.nombre IS NULL, s.nombre ASC, d.nombre IS NULL, d.nombre ASC, u.nombre_completo ASC";

$rows=[];
try{
  $st=$db->prepare($sql);
  $st->execute($params);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}catch(Throwable $e){ $rows=[]; }

/* ---- Agrupar por Sede ---- */
$bySede = [];
foreach($rows as $r){
  $sid = (int)($r['sede_id'] ?? 0);
  $sname = $r['sede_nombre'] ?: '— Sin sede';
  if (!isset($bySede[$sid])) $bySede[$sid] = ['sede_nombre'=>$sname, 'items'=>[]];
  $bySede[$sid]['items'][] = $r;
}

/* ---- Función para traer gerente puntual si hace falta ---- */
function fetchGerenteBySede(PDO $db, int $sedeId): ?array {
  if ($sedeId<=0) return null;
  $st = $db->prepare("
    SELECT id, nombre_completo, usuario, correo, telefono, rol, fotografia, sede_id, departamento_id
    FROM usuarios
    WHERE estado='activo' AND rol='gerente' AND sede_id=? 
    ORDER BY id ASC LIMIT 1
  ");
  $st->execute([$sedeId]);
  return $st->fetch(PDO::FETCH_ASSOC) ?: null;
}

/* ---- Construcción final: sede → gerente (si hay) → deps ---- */
$struct = [];
foreach($bySede as $sid => $bundle){
  $sedeNombre = $bundle['sede_nombre'];
  $items = $bundle['items'];

  $sinfo = $sedesInfo[$sid] ?? [
    'id'=>$sid, 'nombre'=>$sedeNombre, 'gerente_id'=>null,
    'sede_telefono'=>null, 'sede_domicilio'=>null, 'latitud'=>null, 'longitud'=>null
  ];
  $gerente_id = (int)($sinfo['gerente_id'] ?? 0);

  // 1) Si hay gerente_id, intento encontrarlo en items
  $ger = null; $rest = $items;
  if ($gerente_id>0){
    foreach($items as $k=>$r){
      if ((int)$r['id'] === $gerente_id){
        $ger = $r; unset($rest[$k]); $rest = array_values($rest); break;
      }
    }
    // Si no está en el set, lo traigo directo
    if ($ger === null){
      $tmp = $db->prepare("SELECT id, nombre_completo, usuario, correo, telefono, rol, fotografia, sede_id, departamento_id
                           FROM usuarios WHERE id=? AND estado='activo' LIMIT 1");
      $tmp->execute([$gerente_id]);
      $ger = $tmp->fetch(PDO::FETCH_ASSOC) ?: null;
    }
  }

  // 2) Si NO hay gerente_id o no lo encontré, tomo al PRIMER usuario rol=gerente en esa sede
  if ($ger === null){
    // ¿hay algún gerente ya en el set?
    foreach($items as $k=>$r){
      if (strtolower((string)$r['rol'])==='gerente'){
        $ger = $r; unset($rest[$k]); $rest = array_values($rest); break;
      }
    }
    // si tampoco apareció en el set (porque filtros), pruebo consulta puntual
    if ($ger === null){
      $ger = fetchGerenteBySede($db, $sid);
    }
  }

  // Reagrupar el resto por departamento
  $byDep = [];
  foreach($rest as $r){
    $dep = $r['dep_nombre'] ?: '— Sin departamento';
    $byDep[$dep][] = $r;
  }

  $struct[] = [
    'sede_id' => $sid,
    'sede_nombre' => $sedeNombre,
    'sede_telefono' => $sinfo['sede_telefono'] ?? null,
    'sede_domicilio' => $sinfo['sede_domicilio'] ?? null,
    'latitud' => $sinfo['latitud'] ?? null,
    'longitud' => $sinfo['longitud'] ?? null,
    'gerente' => $ger,
    'deps' => $byDep
  ];
}

/* ---- Header compartido ---- */
$titulo_pagina = "Agenda";
require $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/header.php';
?>
<style>
:root{
  --ink:#eaf2ff; --muted:#c8d4f0; --ok:#22c55e; --brand:#0D6EFD;
  --glass-bg: linear-gradient(180deg, rgba(255,255,255,.12), rgba(255,255,255,.08));
  --glass-brd: rgba(255,255,255,.26);
}
body{ background:#0a0f1d; }

/* === HERO === */
.ag-hero{ width:min(1280px,96vw); margin: calc(var(--nav-h,64px) + 18px) auto 12px;
  border-radius:22px; background:var(--glass-bg); border:1px solid var(--glass-brd);
  backdrop-filter: blur(12px); box-shadow:0 36px 90px rgba(0,0,0,.36), inset 0 1px 0 rgba(255,255,255,.16);
  padding:18px 18px 8px; color:#eaf2ff; }
.ag-title{ margin:0 0 2px; font-weight:1000; font-size:clamp(1.6rem,2.4vw,2.2rem) }
.ag-sub{ margin:0 0 10px; color:#c8d4f0; font-size:.98rem }
.ag-filters{ display:grid; grid-template-columns: 1fr 1fr 1.2fr auto; gap:10px; margin-top:8px }
@media (max-width: 900px){ .ag-filters{ grid-template-columns:1fr } }
.ag-filters .form-select,.ag-search{
  background:#0e1c36; color:#dbe8ff; border:1px solid rgba(255,255,255,.15); border-radius:12px;
}
.ag-search{ padding:.7rem .9rem }

/* === CONTENIDO === */
.ag-wrap{ width:min(1280px,96vw); margin: 10px auto 28px }

/* Sede (accordion) */
.sede{ border-radius:18px; overflow:hidden; margin-bottom:10px;
  border:1px solid rgba(255,255,255,.14); background:linear-gradient(180deg,#0c1428,#0b1120); }
.sede-hd{ display:flex; align-items:center; justify-content:space-between; gap:10px;
  padding:.75rem 1rem; color:#eaf2ff; cursor:pointer; }
.sede-title{ font-weight:900; display:flex; flex-direction:column; gap:.2rem }
.sede-sub{ color:#9fb3ff; font-size:.88rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:72vw }
.sede-hd .badge{ background:#0D6EFD; border:1px solid #0D6EFD }
.sede-body{ display:none; padding:.6rem .6rem 1rem }
.sede.open .sede-body{ display:block }

/* Tarjeta sede (Gerente + datos) */
.sede-card{ border:1px solid rgba(255,255,255,.14); background:linear-gradient(180deg,#0c1428,#0b1326);
  border-radius:16px; padding:12px; margin:.5rem; }
.sc-top{ display:flex; align-items:center; gap:12px; flex-wrap:wrap; color:#eaf2ff }
.sc-info{ color:#9fb3ff; font-size:.92rem }

/* Departamentos y contactos */
.dep{ border-radius:16px; overflow:hidden; margin:.5rem; border:1px solid rgba(255,255,255,.10); background:#0b162d; }
.dep-hd{ display:flex; align-items:center; justify-content:space-between; gap:10px;
  padding:.6rem .75rem; color:#eaf2ff; font-weight:900; cursor:pointer; }
.dep-body{ display:none; padding:.6rem .6rem .9rem }
.dep.open .dep-body{ display:block }

.card-grid{ display:grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap:10px }
@media (max-width: 1200px){ .card-grid{ grid-template-columns: repeat(3,1fr) } }
@media (max-width: 840px){ .card-grid{ grid-template-columns: repeat(2,1fr) } }
@media (max-width: 520px){ .card-grid{ grid-template-columns: 1fr } }

.ct{ border:1px solid rgba(255,255,255,.12); border-radius:16px; background:linear-gradient(180deg,#0c1428,#0b1326);
  padding:.8rem; color:#dbe8ff; position:relative; min-height:124px; }
.ct-hd{ display:flex; align-items:center; gap:10px; margin-bottom:.4rem; min-height:48px }
.ava{ width:48px; height:48px; border-radius:50%; overflow:hidden; display:grid; place-items:center;
  background:conic-gradient(from 120deg,#0ea5e9,#6366f1,#22c55e,#0ea5e9); color:#fff; font-weight:1000; font-size:1.1rem; }
.ava img{ width:100%; height:100%; object-fit:cover; border-radius:50% }
.name{ font-weight:900; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis }
.meta{ color:#9fb3ff; font-size:.86rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis }
.puesto{ margin-top:.25rem; font-size:.78rem; color:#c7d8ff; }

/* Mapa */
.map-wrap{ width:min(1280px,96vw); margin: 10px auto 34px;
  border:1px solid rgba(255,255,255,.14); background:linear-gradient(180deg,#0c1428,#0b1326);
  border-radius:18px; padding:10px; }
#sedesMap{ width:100%; height:420px; border-radius:12px; }
.leaflet-container a{ color:#0D6EFD; }
</style>

<div class="ag-hero">
  <h1 class="ag-title">Agenda</h1>
  <p class="ag-sub">Contactos por sede, mostrando primero al gerente y los datos de la sucursal.</p>

  <form class="ag-filters" method="get" action="">
    <select name="sede" class="form-select">
      <option value="0">Todas las sedes</option>
      <?php foreach($sedes as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= $sedeId===(int)$s['id']?'selected':'' ?>><?= esc($s['nombre']) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="dep" class="form-select">
      <option value="0">Todos los departamentos</option>
      <?php foreach($departamentos as $d): ?>
        <option value="<?= (int)$d['id'] ?>" <?= $depId===(int)$d['id']?'selected':'' ?>><?= esc($d['nombre']) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="text" name="q" value="<?= esc($q) ?>" class="ag-search" placeholder="Buscar por nombre, correo o teléfono (⌘/Ctrl + K)">
    <button class="btn btn-primary">Filtrar</button>
  </form>
</div>

<div class="ag-wrap" id="agWrap">
  <?php if (!$struct): ?>
    <div class="sede" style="margin:.5rem;"><div class="sede-hd">Sin contactos con los filtros actuales.</div></div>
  <?php else: ?>
    <?php foreach($struct as $block): ?>
      <?php
        $sede = $block['sede_nombre'] ?: '— Sin sede';
        $ger  = $block['gerente'];
        $telS = trim((string)($block['sede_telefono'] ?? ''));
        $domS = trim((string)($block['sede_domicilio'] ?? ''));
        $deps = $block['deps'];
        $count = ($ger?1:0); foreach($deps as $depArr){ $count += count($depArr); }
      ?>
      <section class="sede open">
        <div class="sede-hd" onclick="this.parentElement.classList.toggle('open')">
          <div class="sede-title">
            <div><i class="bi bi-geo-alt me-2"></i><?= esc($sede) ?></div>
            <div class="sede-sub">
              <?php if ($telS): ?><i class="bi bi-telephone me-1"></i><?= esc($telS) ?> · <?php endif; ?>
              <?php if ($domS): ?><i class="bi bi-geo me-1"></i><?= esc($domS) ?><?php endif; ?>
            </div>
          </div>
          <span class="badge text-bg-primary"><?= (int)$count ?> contacto(s)</span>
        </div>

        <div class="sede-body">
          <!-- Gerente -->
          <div class="sede-card">
            <div class="sc-top">
              <?php if ($ger):
                $iniG = initials($ger['nombre_completo'] ?: ($ger['usuario'] ?? 'U'));
                $fotoG= !empty($ger['fotografia']) ? (BASE_PATH.'/public/img/usuarios/'.rawurlencode($ger['fotografia'])) : '';
                $nmG  = $ger['nombre_completo'] ?: ($ger['usuario'] ?? 'Gerente');
                $mailG= trim((string)($ger['correo'] ?? ''));
                $telG = trim((string)($ger['telefono'] ?? ''));
              ?>
                <div class="ava"><?= $fotoG ? '<img src="'.esc($fotoG).'" alt="">' : esc($iniG) ?></div>
                <div style="min-width:0">
                  <div class="fw-bold">Gerente de sede · <?= esc($sede) ?> <span class="badge-rol ms-1">GERENTE</span></div>
                  <div class="sc-info">
                    <span class="fw-bold"><?= esc($nmG) ?></span>
                    <?php if ($mailG || $telG): ?>
                      · <span><?= esc($mailG ?: '—') ?> <?= ($mailG && $telG)?'·':'' ?> <?= esc($telG ?: '—') ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              <?php else: ?>
                <div class="ava">G</div>
                <div style="min-width:0">
                  <div class="fw-bold">Gerente de sede · <?= esc($sede) ?> <span class="badge-rol ms-1">GERENTE</span></div>
                  <div class="sc-info">No registrado aún.</div>
                </div>
              <?php endif; ?>

              <div class="ms-auto" style="text-align:right">
                <?php if ($telS): ?><div><i class="bi bi-telephone me-1"></i><strong><?= esc($telS) ?></strong></div><?php endif; ?>
                <?php if ($domS): ?><div class="text-truncate" style="max-width:380px"><i class="bi bi-geo me-1"></i><?= esc($domS) ?></div><?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Departamentos -->
          <?php foreach($deps as $dep => $cts): ?>
            <article class="dep open">
              <div class="dep-hd" onclick="this.parentElement.classList.toggle('open')">
                <div><i class="bi bi-diagram-3 me-2"></i><?= esc($dep) ?></div>
                <span class="badge text-bg-secondary"><?= count($cts) ?></span>
              </div>
              <div class="dep-body">
                <div class="card-grid">
                  <?php foreach($cts as $c):
                    $ini  = initials($c['nombre_completo'] ?: ($c['usuario'] ?: 'U'));
                    $foto = $c['fotografia'] ? (BASE_PATH.'/public/img/usuarios/'.rawurlencode($c['fotografia'])) : '';
                    $tel  = trim((string)$c['telefono']); $mail= trim((string)$c['correo']);
                    $rol  = strtoupper((string)($c['rol'] ?? ''));
                    $area = $c['dep_nombre'] ?: '—';
                    $puesto = trim($rol . ($area && $area!=='—' ? ' · '.$area : ''));
                  ?>
                  <div class="ct" data-n="<?= esc(mb_strtolower($c['nombre_completo'].' '.$c['usuario'].' '.$mail.' '.$tel, 'UTF-8')) ?>">
                    <div class="ct-hd">
                      <div class="ava"><?= $foto ? '<img src="'.esc($foto).'" alt="">' : esc($ini) ?></div>
                      <div style="min-width:0">
                        <div class="name" title="<?= esc($c['nombre_completo'] ?: ($c['usuario'] ?: 'Usuario')) ?>">
                          <?= esc($c['nombre_completo'] ?: ($c['usuario'] ?: 'Usuario')) ?>
                        </div>
                        <div class="meta" title="<?= esc(trim($mail.' · '.$tel,' ·')) ?>">
                          <?= esc($mail ?: '—') ?> <?= $mail && $tel ? '·' : '' ?> <?= esc($tel ?: '—') ?>
                        </div>
                        <?php if ($puesto): ?><div class="puesto"><?= esc($puesto) ?></div><?php endif; ?>
                      </div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Mapa -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<div class="map-wrap"><div id="sedesMap" aria-label="Mapa de sedes"></div></div>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
const MAP_SEDES = [
  <?php foreach($sedesInfo as $s):
    $lat = $s['latitud'] ?? null; $lng = $s['longitud'] ?? null;
    if ($lat==='' || $lng===''){ $lat=null; $lng=null; }
    if ($lat!==null && $lng!==null):
      $tel = trim((string)($s['sede_telefono'] ?? ''));
      $dom = trim((string)($s['sede_domicilio'] ?? ''));
      $name = $s['nombre'] ?? ('Sede #'.$s['id']);
  ?>
  { id: <?= (int)$s['id'] ?>, name: <?= json_encode($name) ?>, tel: <?= json_encode($tel) ?>, dom: <?= json_encode($dom) ?>, lat: <?= (float)$lat ?>, lng: <?= (float)$lng ?> },
  <?php endif; endforeach; ?>
];

(function(){
  const has = MAP_SEDES.length>0;
  const map = L.map('sedesMap', {zoomControl:true, attributionControl:true}).setView([23.6,-102.5], has?5:5);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19, attribution:'&copy; OpenStreetMap'}).addTo(map);
  if (!has) return;
  const g = L.featureGroup();
  MAP_SEDES.forEach(s=>{
    const html = `<div style="min-width:200px">
      <strong>${s.name}</strong><br>
      ${s.tel? ('<i class="bi bi-telephone"></i> '+s.tel+'<br>'):''}
      ${s.dom? ('<i class="bi bi-geo"></i> '+s.dom):''}
      ${s.lat && s.lng ? ('<div class="mt-1"><a target="_blank" rel="noopener" href="https://www.google.com/maps?q='+s.lat+','+s.lng+'">Ver en Maps</a></div>') : ''}
    </div>`;
    const m = L.marker([s.lat, s.lng]).bindPopup(html);
    g.addLayer(m);
  });
  g.addTo(map); map.fitBounds(g.getBounds().pad(0.2));
})();
</script>

<script>
// Búsqueda rápida (⌘/Ctrl + K)
(function(){
  const input = document.querySelector('.ag-search');
  function filter(){
    const q = (input.value || '').trim().toLowerCase();
    document.querySelectorAll('.ct').forEach(card=>{
      const hay = card.getAttribute('data-n').indexOf(q) !== -1;
      card.style.display = hay ? '' : 'none';
    });
  }
  input.addEventListener('input', filter);
  window.addEventListener('keydown', (e)=>{
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase()==='k'){ e.preventDefault(); input.focus(); }
  });
})();
</script>

<?php require $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/footer.php'; ?>
