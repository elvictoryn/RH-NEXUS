<?php
// ============================================================
// Detalle de Solicitud (punto de entrada público)
// Ruta: /public/solicitud_detalle.php
// ============================================================
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
if (session_status() === PHP_SESSION_NONE) session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
$db = Conexion::getConexion();

/* --- Sesión mínima --- */
$UID   = (int)($_SESSION['id'] ?? 0);
$ROL   = strtolower($_SESSION['rol'] ?? '');
$SEDE  = isset($_SESSION['sede_id']) ? (int)$_SESSION['sede_id'] : null;

if (!$UID || !in_array($ROL, ['admin','rh','gerente','jefe_area'], true)) {
  header('Location: '.BASE_PATH.'/public/login.php'); exit;
}

/* --- Parámetro ID --- */
$ID = (int)($_GET['id'] ?? 0);
if ($ID <= 0) { http_response_code(400); echo 'ID inválido'; exit; }

/* --- Visibilidad por rol --- */
$W = ['s.id = :id']; $P = [':id'=>$ID];
if ($ROL === 'jefe_area') {
  $W[] = 's.autor_id = :u';  $P[':u'] = $UID;
} elseif ($ROL === 'gerente') {
  if ($SEDE) { $W[] = 's.sede_id = :sede'; $P[':sede'] = $SEDE; }
  else { $W[] = '1=0'; } // gerente sin sede en sesión -> nada
} elseif ($ROL === 'rh') {
  // RH solo ve autorizadas/en proceso/cerradas (no pendientes de gerente)
  $W[] = "s.estado_actual IN ('APROBADA','EN_REV_RH','EN_GESTION','ABIERTA','CERRADA')";
}

/* --- Consulta de detalle --- */
$sql = "SELECT s.*,
               se.nombre AS sede_nombre,
               d.nombre  AS dep_nombre,
               u.nombre_completo AS autor_nombre
        FROM solicitudes s
        LEFT JOIN sedes se ON se.id = s.sede_id
        LEFT JOIN departamentos d ON d.id = s.departamento_id
        LEFT JOIN usuarios u ON u.id = s.autor_id
        WHERE ".implode(' AND ',$W)."
        LIMIT 1";
$st = $db->prepare($sql);
foreach($P as $k=>$v){ $st->bindValue($k,$v,is_int($v)?PDO::PARAM_INT:PDO::PARAM_STR); }
$st->execute();
$S = $st->fetch(PDO::FETCH_ASSOC);

if (!$S) {
  http_response_code(404);
  echo 'Solicitud no encontrada o sin permiso.'; exit;
}

/* --- Helpers UI --- */
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$mapEscolar = [1=>'BACHILLERATO',2=>'LICENCIATURA',3=>'MAESTRIA',4=>'DOCTORADO'];
$folio = 'ID-'.$S['id'];

// Botones según rol/estado
$estado = strtoupper($S['estado_actual'] ?? '');
$puedeAutorizar = ($ROL==='gerente' && in_array($estado, ['ENVIADA','EN_REV_GER'], true));
$puedeGestionarRH = ($ROL==='rh' && in_array($estado, ['APROBADA','EN_REV_RH','EN_GESTION','ABIERTA'], true));
$puedeEditarPorRechazo = ($ROL==='jefe_area' && $S['autor_id']===$UID && $estado==='RECHAZADA');

$titulo_pagina = "Detalle ".$folio;
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/header.php';
?>
<style>
:root{
  --line:#dbe4f0; --brand:#3b82f6; --brand-2:#60a5fa; --ink:#0e1a2a;
}
body{
  background:
    radial-gradient(60% 60% at 15% 8%, rgba(59,130,246,.14), transparent 60%),
    radial-gradient(45% 45% at 85% 12%, rgba(96,165,250,.12), transparent 65%),
    radial-gradient(55% 55% at 70% 92%, rgba(147,197,253,.12), transparent 70%),
    linear-gradient(180deg,#f6f9ff,#f7faff);
}
.wrap{max-width:1200px;margin:0 auto;padding:16px}
.page-head{
  display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;
  padding:.9rem 1rem;border:1px solid var(--line);border-radius:16px;
  background:rgba(255,255,255,.86);backdrop-filter:blur(6px);box-shadow:0 10px 24px rgba(20,40,80,.08)
}
.hero{display:flex;align-items:center;gap:.8rem}
.hero .hero-icon{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;
  background:linear-gradient(135deg,var(--brand),var(--brand-2));color:#fff;font-size:1.25rem}
.hero .title{margin:0;line-height:1.1;font-weight:900;letter-spacing:.2px;font-size:clamp(1.4rem,2vw + .6rem,1.8rem)}
.text-subtitle{margin:0;color:#49617f}

.panel{
  border:1px solid var(--line);border-radius:16px;overflow:hidden;
  background:linear-gradient(180deg, rgba(255,255,255,.80), rgba(255,255,255,.85));
  backdrop-filter: blur(6px);
  box-shadow:0 10px 26px rgba(20,40,80,.10);
}

/* layout detalle + comentarios con scroll propio */
.grid{
  display:grid;grid-template-columns:1fr 360px;gap:14px;
  height:calc(100vh - var(--nav-h,64px) - 140px);
}
@media (max-width: 1100px){ .grid{grid-template-columns:1fr;height:auto} }

.detail{display:flex;flex-direction:column;overflow:hidden}
.detail-head{padding:1rem;border-bottom:1px solid var(--line);background:linear-gradient(180deg,#f4f8ff,#fff)}
.detail-body{padding:1rem;overflow:auto}

.kv-grid{display:grid;grid-template-columns:repeat(2, minmax(0,1fr));gap:.8rem}
@media (max-width: 900px){ .kv-grid{grid-template-columns:1fr} }
.kv-card{
  border:1px solid #d7e3ff;border-radius:12px;padding:.7rem .8rem;background:linear-gradient(180deg, #fbfdff, #f5f9ff);
  box-shadow: 0 6px 18px rgba(20,40,80,.06);
}
.kvk{display:flex;align-items:center;gap:.5rem;color:#3a5680;font-weight:800;font-size:.88rem;margin-bottom:.25rem}
.kvv{color:#0b213a;font-weight:700}
.badge-lite{background:#eaf2ff;border:1px solid #d7e3ff;border-radius:999px;padding:.15rem .6rem;font-size:.78rem}

/* Sidebar (acciones + estado) */
.side{display:flex;flex-direction:column;overflow:hidden}
.side-head{padding:1rem;border-bottom:1px solid var(--line);background:linear-gradient(180deg,#f4f8ff,#fff)}
.side-body{padding:1rem;overflow:auto}
.side .btn{margin-bottom:.5rem}

/* Comentarios */
.comments{margin-top:1rem}
.comment{border-bottom:1px solid #edf2fb;padding:.6rem 0}
.comment .meta{color:#5b7291;font-size:.85rem}
.comment .txt{white-space:pre-wrap}
</style>

<div class="wrap">
  <div class="page-head">
    <div class="hero">
      <div class="hero-icon">📄</div>
      <div>
        <h1 class="title mb-1"><?= esc($S['titulo'] ?: $S['puesto'] ?: $folio) ?></h1>
        <p class="text-subtitle"><?= esc($folio) ?> · Estado: <span class="badge-lite"><?= esc($estado ?: '—') ?></span></p>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a class="btn btn-outline-secondary" href="<?= BASE_PATH ?>/public/solicitudes.php">← Volver a Solicitudes</a>
    </div>
  </div>

  <div class="grid mt-3">
    <!-- DETALLE -->
    <section class="panel detail">
      <div class="detail-head">
        <div class="d-flex align-items-center justify-content-between">
          <div class="fw-bold">Información de la solicitud</div>
          <div class="d-flex gap-2">
            <span class="badge-lite"><?= esc($S['prioridad'] ?: 'NORMAL') ?></span>
            <span class="badge-lite"><?= (int)$S['vacantes'] ?> vacante(s)</span>
          </div>
        </div>
      </div>
      <div class="detail-body">
        <div class="kv-grid">
          <div class="kv-card"><div class="kvk">🧩 Puesto</div><div class="kvv"><?= esc($S['puesto']) ?></div></div>
          <div class="kv-card"><div class="kvk">🏢 Sede</div><div class="kvv"><?= esc($S['sede_nombre']) ?></div></div>
          <div class="kv-card"><div class="kvk">🗂️ Departamento</div><div class="kvv"><?= esc($S['dep_nombre']) ?></div></div>
          <div class="kv-card"><div class="kvk">🎓 Escolaridad</div><div class="kvv"><?= esc($mapEscolar[(int)($S['escolaridad_min'] ?? 0)] ?? '—') ?><?= $S['carrera_estudiada']? ' · '.esc($S['carrera_estudiada']) : '' ?></div></div>
          <div class="kv-card"><div class="kvk">💻 Modalidad</div><div class="kvv"><?= esc($S['modalidad']) ?></div></div>
          <div class="kv-card"><div class="kvk">📄 Contrato</div><div class="kvv"><?= esc($S['tipo_contrato']) ?></div></div>
          <div class="kv-card"><div class="kvk">⏰ Horario</div><div class="kvv"><?= esc($S['horario']) ?></div></div>
          <div class="kv-card"><div class="kvk">💸 Rango salarial</div><div class="kvv">
            <?= ($S['salario_min']!==null? number_format((float)$S['salario_min'],2) : '—') ?> – 
            <?= ($S['salario_max']!==null? number_format((float)$S['salario_max'],2) : '—') ?>
          </div></div>
          <div class="kv-card"><div class="kvk">🛠️ Experiencia</div><div class="kvv"><?= (int)$S['experiencia_anios'] ?> año(s)<?= $S['area_experiencia']? ' en '.esc($S['area_experiencia']) : '' ?></div></div>
          <div class="kv-card"><div class="kvk">🌐 Inglés</div><div class="kvv"><?= esc($S['ingles_combo'] && $S['ingles_combo']!=='0' ? 'REQUERIDO · '.$S['ingles_combo'] : 'NO REQUERIDO') ?></div></div>
        </div>

        <?php
          $comps = json_decode((string)($S['competencias_json'] ?? ''), true);
          if (is_array($comps) && $comps): ?>
          <div class="mt-3">
            <div class="fw-bold text-primary">Competencias</div>
            <div class="d-flex flex-wrap gap-2 mt-1">
              <?php foreach($comps as $c): ?><span class="badge-lite"># <?= esc($c) ?></span><?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if(!empty($S['motivo']) || !empty($S['justificacion'])): ?>
          <div class="mt-3">
            <div class="fw-bold text-primary">Motivo y justificación</div>
            <?php if(!empty($S['motivo'])): ?><div class="mt-1"><strong>Motivo:</strong> <?= nl2br(esc($S['motivo'])) ?></div><?php endif; ?>
            <?php if(!empty($S['reemplazo_de'])): ?><div class="text-muted"><small>Reemplazo de: <?= esc($S['reemplazo_de']) ?></small></div><?php endif; ?>
            <?php if(!empty($S['justificacion'])): ?><div class="mt-1"><?= nl2br(esc($S['justificacion'])) ?></div><?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if(!empty($S['responsabilidades'])): ?>
          <div class="mt-3">
            <div class="fw-bold text-primary">Responsabilidades</div>
            <div class="mt-1"><?= nl2br(esc($S['responsabilidades'])) ?></div>
          </div>
        <?php endif; ?>

        <!-- Aquí, más abajo, podrás integrar comentarios/acciones si lo deseas -->
      </div>
    </section>

    <!-- SIDEBAR (acciones + estado) -->
    <aside class="panel side">
      <div class="side-head">
        <div class="fw-bold">Acciones</div>
      </div>
      <div class="side-body">
        <?php if ($puedeAutorizar): ?>
          <a class="btn btn-success w-100" href="#" onclick="alert('Aceptar: aquí irá el flujo para aprobar y mandar a RH');return false;">✔ Autorizar y enviar a RH</a>
          <a class="btn btn-outline-danger w-100" href="#" onclick="alert('Rechazar: aquí pedirás motivo y devolverás al Jefe de área');return false;">✖ Rechazar (con motivo)</a>
        <?php elseif ($puedeGestionarRH): ?>
          <a class="btn btn-primary w-100" href="#" onclick="alert('RH: abrir flujo de gestión (candidatos/proceso)');return false;">⚙ Gestionar en RH</a>
        <?php elseif ($puedeEditarPorRechazo): ?>
          <a class="btn btn-warning w-100" href="<?= BASE_PATH ?>/app/views/admin/solicitudes/crear_solicitud.php?edit=<?= (int)$S['id'] ?>">✎ Editar (rechazada)</a>
          <a class="btn btn-outline-danger w-100" href="#" onclick="alert('Eliminar (solo rechazada): aquí confirmarás y borrarás');return false;">🗑 Eliminar</a>
        <?php else: ?>
          <div class="text-muted">No hay acciones disponibles para tu rol/estado.</div>
        <?php endif; ?>

        <hr>

        <div class="fw-bold mb-2">Estado actual</div>
        <div class="d-flex flex-column gap-2">
          <div><span class="badge-lite">Estado:</span> <strong><?= esc($estado ?: '—') ?></strong></div>
          <div><span class="badge-lite">Prioridad:</span> <strong><?= esc($S['prioridad'] ?: 'NORMAL') ?></strong></div>
          <div><span class="badge-lite">Autor:</span> <?= esc($S['autor_nombre'] ?: '—') ?></div>
          <div><span class="badge-lite">Sede:</span> <?= esc($S['sede_nombre'] ?: '—') ?></div>
          <div><span class="badge-lite">Departamento:</span> <?= esc($S['dep_nombre'] ?: '—') ?></div>
        </div>
      </div>
    </aside>
  </div>
</div>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/footer.php'; ?>
