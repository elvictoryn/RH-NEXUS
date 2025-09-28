<?php 
// ============================================================
// Bandeja simple de Solicitudes (lista -> ir a detalle)
// (Incluye autor y departamento en cada ítem)
// ============================================================
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
if (session_status() === PHP_SESSION_NONE) session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
$db = Conexion::getConexion();

/* ===== Sesión ===== */
$uid     = (int)($_SESSION['id'] ?? 0);
$rol     = strtolower($_SESSION['rol'] ?? '');
$sede_id = isset($_SESSION['sede_id']) ? (int)$_SESSION['sede_id'] : null;

if (!$uid || !in_array($rol, ['admin','rh','gerente','jefe_area'], true)) {
  header('Location: '.BASE_PATH.'/public/login.php'); exit;
}

/* ===== Helpers ===== */
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function qcount(PDO $db, string $sql, array $p=[]): int {
  $st=$db->prepare($sql);
  foreach($p as $k=>$v){ $st->bindValue($k,$v,is_int($v)?PDO::PARAM_INT:PDO::PARAM_STR); }
  $st->execute(); return (int)$st->fetchColumn();
}

/**
 * WHERE unificado para lista y contadores
 */
function build_where(string $folder, string $rol, int $uid, ?int $sede_id, string $q=''): array {
  $W=[]; $P=[];
  switch ($folder) {
    case 'urgent':
      $W[]="s.prioridad='URGENTE'";
      break;
    case 'open':
      $W[]="s.estado_actual IN ('ENVIADA','EN_REV_GER','EN_REV_RH','ABIERTA')";
      break;
    case 'closed':
      $W[]="s.estado_actual='CERRADA'";
      break;
    case 'mine':
      $W[]="s.autor_id=:u"; $P[':u']=$uid;
      break;
    case 'inbox':
    default:
      if ($rol==='jefe_area') {
        $W[]="s.autor_id=:u"; $P[':u']=$uid;
      } elseif ($rol==='gerente' && $sede_id) {
        $W[]="s.sede_id=:s"; $P[':s']=$sede_id;
      } elseif ($rol==='rh') {
        // RH: solo solicitudes aprobadas (requisito)
        $W[]="s.estado_actual='APROBADA'";
      } else {
        $W[]="1=1";
      }
      break;
  }

  // Refuerzo de visibilidad por rol
  if ($rol==='jefe_area') {
    if (!in_array("s.autor_id=:u", $W)) { $W[]="s.autor_id=:u"; $P[':u']=$uid; }
  } elseif ($rol==='gerente' && $sede_id) {
    if (!in_array("s.sede_id=:s", $W)) { $W[]="s.sede_id=:s"; $P[':s']=$sede_id; }
  } elseif ($rol==='rh') {
    if ($folder==='urgent') {
      $W[]="s.estado_actual='APROBADA'";
    } elseif ($folder==='open') {
      $W[]="s.estado_actual IN ('APROBADA','EN_GESTION')";
    } elseif ($folder==='closed') {
      $W[]="s.estado_actual='CERRADA'";
    }
  }

  if ($q!==''){
    $W[]="(UPPER(s.titulo) LIKE :q OR UPPER(s.puesto) LIKE :q OR UPPER(CONCAT('ID-', s.id)) LIKE :q)";
    $P[':q']='%'.mb_strtoupper($q,'UTF-8').'%';
  }

  $where = $W ? ('WHERE '.implode(' AND ',$W)) : '';
  return [$where, $P];
}

/* ===== Carpeta & búsqueda ===== */
$folder = $_GET['folder'] ?? 'inbox';
$q      = trim($_GET['q'] ?? '');

/* ===== WHERE unificado ===== */
[$where, $params] = build_where($folder, $rol, $uid, $sede_id, $q);

/* ===== Paginación ===== */
$pag   = max(1, (int)($_GET['pag'] ?? 1));
$limit = 20;
$off   = ($pag-1)*$limit;

/* ===== Contar total ===== */
$total = qcount($db,"SELECT COUNT(*) FROM solicitudes s $where",$params);
$pages = max(1,(int)ceil($total/$limit));

/* ===== Query principal 
   - se agrega JOIN con usuarios (u) para autor
   - seleccionamos u.nombre_completo (autor_nombre) y u.usuario (autor_usuario de respaldo)
   - mantenemos s.creada_en como en tu versión previa
============================================================== */
$sql = "SELECT 
          s.id, s.titulo, s.puesto, s.vacantes, s.estado_actual, s.prioridad,
          s.creada_en,
          se.nombre AS sede, d.nombre AS departamento,
          u.nombre_completo AS autor_nombre, u.usuario AS autor_usuario
        FROM solicitudes s
        LEFT JOIN sedes se         ON se.id = s.sede_id
        LEFT JOIN departamentos d  ON d.id  = s.departamento_id
        LEFT JOIN usuarios u       ON u.id  = s.autor_id
        $where
        ORDER BY s.creada_en DESC
        LIMIT :lim OFFSET :off";
$st=$db->prepare($sql);
foreach($params as $k=>$v){ $st->bindValue($k,$v,is_int($v)?PDO::PARAM_INT:PDO::PARAM_STR); }
$st->bindValue(':lim',$limit,PDO::PARAM_INT);
$st->bindValue(':off',$off,PDO::PARAM_INT);
$st->execute();
$rows=$st->fetchAll(PDO::FETCH_ASSOC);

/* ===== Carpeta UI ===== */
$folders = [
  'inbox'  => 'Bandeja',
  'urgent' => 'Urgentes',
  'open'   => 'Abiertas',
  'closed' => 'Cerradas',
  'mine'   => 'Creadas por mí',
];

/* ===== Contadores (mismo build_where) ===== */
$counts=[];
foreach($folders as $key=>$name){
  [$wC, $pC] = build_where($key, $rol, $uid, $sede_id, $q);
  $counts[$key] = qcount($db,"SELECT COUNT(*) FROM solicitudes s $wC",$pC);
}

$titulo_pagina = "Solicitudes";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/header.php';
?>
<style>
:root{
  --nav-h:64px;
  --ink:#0e1a2a; --line:#dbe4f0;
  --brand:#3b82f6; --brand-2:#60a5fa;
}

/* Fondo general ligeramente azulado */
body{
  background:
    radial-gradient(60% 60% at 15% 8%, rgba(59,130,246,.10), transparent 60%),
    radial-gradient(45% 45% at 85% 12%, rgba(96,165,250,.10), transparent 65%),
    linear-gradient(180deg,#f0f4ff,#f6f9ff);
}

.container-full{width:100%;max-width:100%;padding:0 16px}

/* Header más oscuro para legibilidad */
.page-head{
  display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;
  padding:.9rem 1rem;border-radius:16px;
  background: linear-gradient(90deg, #1e293b, #334155);
  color:#f1f5f9;
  border:1px solid #1f2a37; box-shadow:0 6px 16px rgba(12, 18, 28, .35)
}
.hero{display:flex;align-items:center;gap:.8rem}
.hero .hero-icon{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;
  background:linear-gradient(135deg,var(--brand),var(--brand-2));color:#fff}
.hero .title{margin:0;font-weight:900;font-size:clamp(1.6rem,2.2vw + .6rem,2.2rem)}
.page-head .text-muted{color:#cbd5e1 !important}

/* Layout */
.wrap{display:grid;grid-template-columns:300px 1fr;gap:14px;min-height:calc(100vh - var(--nav-h) - 120px)}
@media (max-width: 992px){ .wrap{grid-template-columns:1fr} }

/* Paneles */
.panel{border:1px solid var(--line);border-radius:16px;background:#fff;overflow:hidden;box-shadow:0 8px 18px rgba(20,40,80,.08)}

/* Menú/Folders oscuro */
.left{
  padding:10px;
  background: linear-gradient(180deg, #1e293b, #334155);
  color:#e5edf7;
  border-color:#263043;
}
.f-title{font-weight:900;color: #93c5fd;margin:.25rem .25rem .5rem}
.fnav{list-style:none;margin:0;padding:0}
.fnav a{
  display:flex;align-items:center;justify-content:space-between;gap:.6rem;
  padding:.7rem .8rem;margin:.22rem 0;border-radius:12px;
  border:1px solid #2b3a55;
  background:rgba(255,255,255,0.04);
  text-decoration:none;color:#f8fafc;font-weight:700
}
.fnav a:hover,.fnav a.active{
  background:rgba(59,130,246,.22); border-color: #365899
}
.count{font-weight:800;font-size:.8rem;color: #c7dbff}
.search-box{
  padding:.7rem;border-top:1px solid #2b3a55;
  background:rgba(255,255,255,0.04)
}
.search-box .input-group-text{background: #0b1626;color: #a5b4fc;border-color: #2b3a55}
.search-box .form-control{background: #0f1b2c;color: #e2e8f0;border-color:#2b3a55}
.search-box .btn{border-color:#3b82f6}

/* Lista */
.list-head{padding:.8rem .95rem;border-bottom:1px solid var(--line);background: #fff}
.list-body{
  max-height:calc(100vh - var(--nav-h) - 220px);
  overflow:auto;
  background:linear-gradient(180deg, #0e59d2ff,#f8fbff);
}
.item{display:grid;grid-template-columns:1.6rem 1fr auto;gap:.7rem;padding:.75rem .95rem;border-bottom:1px solid #1c54ceff}
.item:hover{background: #4d81c5ff;cursor:pointer}
.mi-ico{font-size:1.1rem}
.mi-title{font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color: #0b213a}
.mi-sub{font-size:.9rem;color: #0b213a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.badges{display:flex;gap:.35rem;flex-wrap:wrap;margin-top:.25rem}
.badge{background: #ea3434ff;border:1px solid #d7e3ff;border-radius:999px;padding:.05rem .5rem;font-size:.75rem}
.mi-aux{display:flex;align-items:center;gap:.5rem;color: #01060dff;font-size:.85rem}

/* Paginación */
.pag{padding:.6rem;border-top:1px solid var(--line);background:#fff}
</style>

<div class="container-full py-4">
  <div class="page-head">
    <div class="hero">
      <div class="hero-icon">📥</div>
      <div><h1 class="title mb-1">Solicitudes</h1><div class="text-muted">Bandeja</div></div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a class="btn btn-primary" href="<?= BASE_PATH ?>/app/views/admin/solicitudes/crear_solicitud.php">➕ Nueva</a>
    </div>
  </div>

  <div class="wrap mt-3">
    <!-- Izquierda: carpetas -->
    <aside class="panel left">
      <div class="f-title">Carpetas</div>
      <ul class="fnav">
        <?php foreach($folders as $k=>$label): ?>
          <li><a class="<?= $folder===$k?'active':'' ?>" href="?folder=<?= esc($k) ?>&q=<?= esc($q) ?>">
            <span><?= esc($label) ?></span>
            <span class="count"><?= number_format($counts[$k] ?? 0) ?></span>
          </a></li>
        <?php endforeach; ?>
      </ul>
      <div class="search-box">
        <form method="get" class="d-grid gap-2">
          <input type="hidden" name="folder" value="<?= esc($folder) ?>">
          <div class="input-group input-group-sm">
            <span class="input-group-text">🔎</span>
            <input type="text" class="form-control" name="q" value="<?= esc($q) ?>" placeholder="Buscar: ID-#, título, puesto">
          </div>
          <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary w-50 btn-sm" href="?folder=<?= esc($folder) ?>">Limpiar</a>
            <button class="btn btn-outline-primary w-50 btn-sm">Buscar</button>
          </div>
        </form>
      </div>
    </aside>

    <!-- Derecha: lista -->
    <section class="panel">
      <div class="list-head d-flex align-items-center justify-content-between">
        <div class="fw-semibold"><?= esc($folders[$folder] ?? 'Bandeja') ?></div>
        <div class="text-muted small">Mostrando <?= min($limit, max(0,$total-$off)) ?> de <?= number_format($total) ?></div>
      </div>

      <div class="list-body">
        <?php if(!$rows): ?>
          <div class="p-4 text-center text-muted">No hay solicitudes aquí.</div>
        <?php else: foreach($rows as $r): ?>
          <?php
            $ico     = ($r['prioridad']==='URGENTE') ? '🚨' : '📝';
            $folio   = 'ID-'.$r['id'];
            $href    = BASE_PATH.'/app/views/admin/solicitudes/detalle.php?id='.(int)$r['id'];
            $autor   = trim($r['autor_nombre'] ?? '') !== '' ? $r['autor_nombre'] : ($r['autor_usuario'] ?? '—');
            $depName = $r['departamento'] ?: '—';
          ?>
          <a class="item text-decoration-none" href="<?= esc($href) ?>">
            <div class="mi-ico"><?= $ico ?></div>
            <div>
              <div class="mi-title"><?= esc($r['titulo'] ?: $r['puesto'] ?: $folio) ?></div>
              <div class="mi-sub">
                <!-- Aquí añadimos autor y su departamento -->
                <?= esc("Autor: $autor · Dep: $depName") ?>
                &nbsp;·&nbsp; <?= esc($r['sede'] ?: '—') ?>
                &nbsp;·&nbsp; Folio <?= esc($folio) ?>
              </div>
              <div class="badges">
                <span class="badge"><?= esc(strtoupper($r['estado_actual'])) ?></span>
                <?php if ($r['prioridad']==='URGENTE'): ?><span class="badge">URGENTE</span><?php endif; ?>
                <span class="badge"><?= (int)$r['vacantes'] ?> vacante(s)</span>
              </div>
            </div>
            <div class="mi-aux">
              <span class="small"><?= esc(date('d/M H:i', strtotime($r['creada_en'] ?? 'now'))) ?></span>
              <span class="text-muted">›</span>
            </div>
          </a>
        <?php endforeach; endif; ?>
      </div>

      <?php if ($pages>1): ?>
      <div class="pag">
        <nav>
          <ul class="pagination pagination-sm mb-0 justify-content-center">
            <?php
              $qs=$_GET; $start=max(1,$pag-2); $end=min($pages,$pag+2);
              if ($start>1){ $qs['pag']=1; echo '<li class="page-item"><a class="page-link" href="?'.http_build_query($qs).'">«</a></li>'; }
              for($i=$start;$i<=$end;$i++):
                $qs['pag']=$i;
                echo '<li class="page-item '.($i===$pag?'active':'').'"><a class="page-link" href="?'.http_build_query($qs).'">'.$i.'</a></li>';
              endfor;
              if ($end<$pages){ $qs['pag']=$pages; echo '<li class="page-item"><a class="page-link" href="?'.http_build_query($qs).'">»</a></li>'; }
            ?>
          </ul>
        </nav>
      </div>
      <?php endif; ?>
    </section>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/footer.php'; ?>
