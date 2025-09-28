<?php
// ============================================================
// Detalle de Solicitud (página dedicada)
// Ruta sugerida: /app/views/solicitudes/detalle.php?id=###
// ============================================================
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
if (session_status() === PHP_SESSION_NONE) session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
$db = Conexion::getConexion();

/* ---- Sesión mínima ---- */
$UID     = (int)($_SESSION['id'] ?? 0);
$ROL     = strtolower($_SESSION['rol'] ?? '');
$SEDE    = isset($_SESSION['sede_id']) ? (int)$_SESSION['sede_id'] : null;
$NOMBRE  = $_SESSION['nombre_completo'] ?? ($_SESSION['usuario'] ?? 'Usuario');

if (!$UID || !in_array($ROL, ['admin','rh','gerente','jefe_area'], true)) {
  header('Location: '.BASE_PATH.'/public/login.php'); exit;
}

/* ---- Helpers ---- */
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function post($k,$d=null){ return $_POST[$k] ?? $d; }

/* ---- Cargar solicitud + validar visibilidad ---- */
$id = (int)($_GET['id'] ?? 0);
if ($id<=0){ header('Location: '.BASE_PATH.'/public/solicitudes.php'); exit; }

try {
  $st = $db->prepare("
    SELECT s.*,
           se.nombre AS sede_nombre,
           d.nombre  AS dep_nombre,
           u.nombre_completo AS autor_nombre, u.id AS autor_id
    FROM solicitudes s
    LEFT JOIN sedes se ON se.id=s.sede_id
    LEFT JOIN departamentos d ON d.id=s.departamento_id
    LEFT JOIN usuarios u ON u.id=s.autor_id
    WHERE s.id = :id
    LIMIT 1
  ");
  $st->execute([':id'=>$id]);
  $S = $st->fetch(PDO::FETCH_ASSOC);
  if (!$S) throw new Exception('Solicitud no encontrada');

  // Reglas de visibilidad por rol
  $visible = false;
  if ($ROL==='admin') {
    $visible = true;
  } elseif ($ROL==='jefe_area') {
    $visible = ((int)$S['autor_id'] === $UID);
  } elseif ($ROL==='gerente') {
    $visible = ($SEDE && (int)$S['sede_id'] === $SEDE);
  } elseif ($ROL==='rh') {
    $visible = in_array($S['estado_actual'], ['APROBADA','EN_REV_RH','EN_GESTION','ABIERTA','CERRADA'], true);
  }
  if (!$visible) throw new Exception('No tienes permiso para ver esta solicitud');

} catch (Throwable $e) {
  $error = $e->getMessage();
  // Pintamos un error simple y salimos
  require $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/header.php';
  echo '<div class="container py-4"><div class="alert alert-danger">'.$error.'</div></div>';
  require $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/footer.php';
  exit;
}

/* ---- Manejo de acciones POST (cambiar estado, comentar, eliminar) ---- */
$alert = null; $alert_type = 'success';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $action = post('action','');
  try {
    // Seguridad básica: volver a cargar visibilidad si hiciera falta (ya cargado arriba)
    if ($action === 'comentar') {
      $coment = trim((string)post('comentario',''));
      if ($coment==='') throw new Exception('Escribe un comentario.');
      $ins = $db->prepare("INSERT INTO solicitudes_comentarios (solicitud_id, usuario_id, comentario, creado_en)
                           VALUES (?,?,?,NOW())");
      $ins->execute([$id, $UID, $coment]);
      $alert = 'Comentario agregado.';
      $alert_type = 'success';

    } elseif ($action === 'aprobar' && ($ROL==='gerente' || $ROL==='admin')) {
      // Gerente sólo si misma sede
      if ($ROL==='gerente' && (! $SEDE || (int)$S['sede_id'] !== $SEDE)) throw new Exception('No puedes autorizar fuera de tu sede.');
      if (!in_array($S['estado_actual'], ['ENVIADA','EN_REV_GER'], true)) throw new Exception('Estado no válido para aprobar.');
      $up = $db->prepare("UPDATE solicitudes SET estado_actual='APROBADA' WHERE id=?");
      $up->execute([$id]);
      $alert = 'Solicitud aprobada y enviada a RH.';
      $alert_type = 'success';
      $S['estado_actual']='APROBADA';

      // Log como comentario del gerente (opcional)
      $c = $db->prepare("INSERT INTO solicitudes_comentarios (solicitud_id, usuario_id, comentario, creado_en)
                         VALUES (?,?,?,NOW())");
      $c->execute([$id, $UID, 'APROBADA por Gerente.']);

    } elseif ($action === 'rechazar' && ($ROL==='gerente' || $ROL==='admin')) {
      if ($ROL==='gerente' && (! $SEDE || (int)$S['sede_id'] !== $SEDE)) throw new Exception('No puedes rechazar fuera de tu sede.');
      if (!in_array($S['estado_actual'], ['ENVIADA','EN_REV_GER'], true)) throw new Exception('Estado no válido para rechazar.');

      $motivo = trim((string)post('motivo',''));
      if ($motivo==='') throw new Exception('Debes indicar el motivo de rechazo.');

      $up = $db->prepare("UPDATE solicitudes SET estado_actual='RECHAZADA' WHERE id=?");
      $up->execute([$id]);
      $alert = 'Solicitud rechazada (devuelta a Jefe de Área).';
      $alert_type = 'warning';
      $S['estado_actual']='RECHAZADA';

      // Registrar motivo como comentario
      $c = $db->prepare("INSERT INTO solicitudes_comentarios (solicitud_id, usuario_id, comentario, creado_en)
                         VALUES (?,?,?,NOW())");
      $c->execute([$id, $UID, 'RECHAZADA: '.$motivo]);

    } elseif ($action === 'gestionar' && ($ROL==='rh' || $ROL==='admin')) {
      if (!in_array($S['estado_actual'], ['APROBADA','EN_REV_RH'], true)) throw new Exception('Estado no válido para tomar en gestión.');
      $up = $db->prepare("UPDATE solicitudes SET estado_actual='EN_GESTION' WHERE id=?");
      $up->execute([$id]);
      $alert = 'Solicitud tomada en gestión.';
      $S['estado_actual']='EN_GESTION';

    } elseif ($action === 'abrir' && ($ROL==='rh' || $ROL==='admin')) {
      if ($S['estado_actual'] !== 'EN_GESTION') throw new Exception('Solo desde EN_GESTION se puede pasar a ABIERTA.');
      $up = $db->prepare("UPDATE solicitudes SET estado_actual='ABIERTA' WHERE id=?");
      $up->execute([$id]);
      $alert = 'Solicitud abierta (publicada).';
      $S['estado_actual']='ABIERTA';

    } elseif ($action === 'cerrar' && ($ROL==='rh' || $ROL==='admin')) {
      if (!in_array($S['estado_actual'], ['ABIERTA','EN_GESTION'], true)) throw new Exception('Solo ABIERTA/EN_GESTION puede cerrarse.');
      $up = $db->prepare("UPDATE solicitudes SET estado_actual='CERRADA' WHERE id=?");
      $up->execute([$id]);
      $alert = 'Solicitud cerrada.';
      $S['estado_actual']='CERRADA';

    } elseif ($action === 'eliminar' && ($ROL==='admin' || ($ROL==='jefe_area' && (int)$S['autor_id']===$UID))) {
      if ($ROL==='jefe_area' && $S['estado_actual']!=='RECHAZADA') {
        throw new Exception('Solo puedes eliminar si está RECHAZADA.');
      }
      // Eliminación dura o lógica (aquí dura; si prefieres lógica, agrega una columna).
      $db->beginTransaction();
      $db->prepare("DELETE FROM solicitudes_comentarios WHERE solicitud_id=?")->execute([$id]);
      $db->prepare("DELETE FROM solicitudes WHERE id=?")->execute([$id]);
      $db->commit();
      // Redirigir a la bandeja tras eliminar
      header('Location: '.BASE_PATH.'/public/solicitudes.php?ok=1&msg='.rawurlencode('Solicitud eliminada.')); exit;

    } elseif ($action === 'cambiar_estado_admin' && $ROL==='admin') {
      $nuevo = strtoupper(trim((string)post('nuevo_estado','')));
      $valid = ['ENVIADA','EN_REV_GER','APROBADA','EN_REV_RH','EN_GESTION','ABIERTA','RECHAZADA','CERRADA'];
      if (!in_array($nuevo, $valid, true)) throw new Exception('Estado inválido.');
      $up = $db->prepare("UPDATE solicitudes SET estado_actual=? WHERE id=?");
      $up->execute([$nuevo, $id]);
      $S['estado_actual']=$nuevo;
      $alert = 'Estado actualizado por Admin.';
    }

  } catch (Throwable $e) {
    $alert = $e->getMessage();
    $alert_type = 'danger';
  }
}

/* ---- Cargar comentarios ---- */
$comentarios = [];
try {
  $stc = $db->prepare("
    SELECT sc.id, sc.comentario, sc.creado_en,
           u.nombre_completo AS autor, u.usuario
    FROM solicitudes_comentarios sc
    LEFT JOIN usuarios u ON u.id=sc.usuario_id
    WHERE sc.solicitud_id = ?
    ORDER BY sc.id DESC
    LIMIT 100
  ");
  $stc->execute([$id]);
  $comentarios = $stc->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (\Throwable $e) {
  $comentarios = [];
}

/* ---- Mapeos de textos ---- */
$mapEscolar = [1=>'BACHILLERATO',2=>'LICENCIATURA',3=>'MAESTRIA',4=>'DOCTORADO'];
$escolaridadTxt = $mapEscolar[(int)($S['escolaridad_min'] ?? 0)] ?? '—';

$ingles_label = 'NO REQUERIDO';
if (!empty($S['ingles_combo']) && strtoupper($S['ingles_combo'])!=='0') {
  $ingles_label = 'REQUERIDO · '.esc($S['ingles_combo']);
}

/* ---- UI/estilos ---- */
$titulo_pagina = "Solicitud ID-".$S['id'];
require $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/header.php';
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
.head{
  display:flex;align-items:center;justify-content:space-between;gap:.8rem;flex-wrap:wrap;
  padding: .9rem 1rem; border:1px solid var(--line); border-radius:14px;
  background:linear-gradient(180deg, rgba(255,255,255,.88), rgba(255,255,255,.82));
  box-shadow:0 10px 20px rgba(20,40,80,.08)
}
.head .title{margin:0;font-weight:900;letter-spacing:.3px;color:#0b213a}
.pills{display:flex;gap:.4rem;flex-wrap:wrap}
.pill{display:inline-flex;align-items:center;gap:.45rem;padding:.28rem .65rem;border:1px solid #cfe0ff;border-radius:999px;font-size:.78rem;background:#eaf2ff;color:#0b213a}
.pill.pr-urgente{background:#fff3f0;border-color:#ffd7ce}
.card{
  border:1px solid var(--line); border-radius:14px; background:#ffffffcc; backdrop-filter:blur(4px);
  box-shadow:0 10px 30px rgba(20,40,80,.08)
}
.main{display:grid;grid-template-columns: 1.3fr .9fr; gap:16px; margin-top:14px}
@media (max-width: 1024px){ .main{grid-template-columns:1fr} }
.card .card-body{padding:1rem}
.kv-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.8rem}
@media (max-width: 800px){ .kv-grid{grid-template-columns:1fr} }
.kv{border:1px solid #d7e3ff;border-radius:12px;padding:.65rem .8rem;background:linear-gradient(180deg,#fbfdff,#f5f9ff)}
.kvk{font-weight:800;color:#3a5680}
.kvv{color:#0b213a;font-weight:700}

.section-title{font-weight:900;color:#17467a;margin:.2rem 0 .6rem}
.resp{border:1px dashed #cfe0ff;border-radius:10px;padding:.7rem .8rem;background:linear-gradient(180deg,#ffffff,.82,#f5f9ff,.9)}
.btn-bar{display:flex;flex-wrap:wrap;gap:.5rem}
.comment-box{display:flex;gap:.6rem;margin-top:.7rem}
.comment-box textarea{flex:1}
.comments{max-height:380px;overflow:auto;padding-right:.3rem}
.comment{border-bottom:1px solid #eef2fb;padding:.6rem 0}
.comment .who{font-weight:700;color:#102a4d}
.comment .when{color:#5b7291;font-size:.86rem}
.badge-soft{background:#eaf2ff;border:1px solid #d7e3ff;border-radius:999px;padding:.15rem .6rem;font-size:.76rem;color:#15385e}
</style>

<div class="wrap">
  <div class="head">
    <div>
      <h1 class="title">Solicitud <?= 'ID-'.$S['id'] ?> · <?= esc($S['titulo'] ?: $S['puesto'] ?: '—') ?></h1>
      <div class="pills">
        <span class="pill">Estado: <strong><?= esc($S['estado_actual'] ?: '—') ?></strong></span>
        <span class="pill <?= ($S['prioridad']==='URGENTE'?'pr-urgente':'') ?>">Prioridad: <strong><?= esc($S['prioridad'] ?: 'NORMAL') ?></strong></span>
        <span class="pill">Sede: <?= esc($S['sede_nombre'] ?: '—') ?></span>
        <span class="pill">Depto: <?= esc($S['dep_nombre'] ?: '—') ?></span>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a class="btn btn-outline-secondary" href="<?= BASE_PATH; ?>/public/solicitudes.php">← Volver a la bandeja</a>
    </div>
  </div>

  <?php if ($alert): ?>
    <div class="alert alert-<?= esc($alert_type) ?> mt-3"><?= esc($alert) ?></div>
  <?php endif; ?>

  <div class="main">
    <!-- Columna izquierda: info -->
    <div class="card">
      <div class="card-body">
        <div class="section-title">Información de la vacante</div>
        <div class="kv-grid">
          <div class="kv"><div class="kvk">Puesto</div><div class="kvv"><?= esc($S['puesto']) ?></div></div>
          <div class="kv"><div class="kvk">Vacantes</div><div class="kvv"><?= (int)$S['vacantes'] ?></div></div>
          <div class="kv"><div class="kvk">Fecha deseada</div><div class="kvv"><?= esc($S['fecha_ingreso_deseada'] ?: '—') ?></div></div>
          <div class="kv"><div class="kvk">Contrato</div><div class="kvv"><?= esc($S['tipo_contrato'] ?: '—') ?></div></div>
          <div class="kv"><div class="kvk">Modalidad</div><div class="kvv"><?= esc($S['modalidad'] ?: '—') ?></div></div>
          <div class="kv"><div class="kvk">Horario</div><div class="kvv"><?= esc($S['horario'] ?: '—') ?></div></div>
          <div class="kv">
            <div class="kvk">Rango salarial</div>
            <div class="kvv">
              <?= ($S['salario_min']!==null? number_format((float)$S['salario_min'],2):'—') ?>
              –
              <?= ($S['salario_max']!==null? number_format((float)$S['salario_max'],2):'—') ?>
            </div>
          </div>
          <div class="kv"><div class="kvk">Escolaridad</div><div class="kvv"><?= esc($escolaridadTxt) ?></div></div>
          <div class="kv"><div class="kvk">Carrera(s)</div><div class="kvv"><?= esc($S['carrera_estudiada'] ?: '—') ?></div></div>
          <div class="kv"><div class="kvk">Experiencia</div><div class="kvv"><?= (int)$S['experiencia_anios'] ?> año(s)</div></div>
          <div class="kv"><div class="kvk">Áreas de experiencia</div><div class="kvv"><?= esc($S['area_experiencia'] ?: '—') ?></div></div>
          <div class="kv"><div class="kvk">Inglés</div><div class="kvv"><?= $ingles_label ?></div></div>
        </div>

        <?php
          $comps = json_decode((string)($S['competencias_json'] ?? ''), true);
          if (is_array($comps) && $comps):
        ?>
        <div class="section-title mt-3">Competencias</div>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach($comps as $c): ?>
            <span class="badge-soft"># <?= esc($c) ?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($S['motivo'] || $S['reemplazo_de'] || $S['justificacion']): ?>
          <div class="section-title mt-3">Motivo y justificación</div>
          <?php if ($S['motivo']): ?><div><strong>Motivo:</strong> <?= nl2br(esc($S['motivo'])) ?></div><?php endif; ?>
          <?php if ($S['reemplazo_de']): ?><div><small>Reemplazo de: <?= esc($S['reemplazo_de']) ?></small></div><?php endif; ?>
          <?php if ($S['justificacion']): ?><div class="resp mt-2"><?= nl2br(esc($S['justificacion'])) ?></div><?php endif; ?>
        <?php endif; ?>

        <?php if ($S['responsabilidades']): ?>
          <div class="section-title mt-3">Responsabilidades</div>
          <div class="resp"><?= nl2br(esc($S['responsabilidades'])) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Columna derecha: acciones + comentarios -->
    <div class="card">
      <div class="card-body">
        <!-- Acciones por rol -->
        <div class="section-title">Acciones</div>
        <div class="btn-bar mb-3">
          <?php if ($ROL==='gerente' || $ROL==='admin'): ?>
            <?php if (in_array($S['estado_actual'], ['ENVIADA','EN_REV_GER'], true)): ?>
              <form method="post" onsubmit="return confirm('¿Aprobar esta solicitud y enviarla a RH?');">
                <input type="hidden" name="action" value="aprobar">
                <button class="btn btn-success">✔ Aprobar</button>
              </form>

              <form method="post" class="d-flex align-items-center gap-2">
                <input type="hidden" name="action" value="rechazar">
                <input type="text" name="motivo" class="form-control" placeholder="Motivo de rechazo (obligatorio)" required>
                <button class="btn btn-outline-danger" onclick="return confirm('¿Rechazar esta solicitud?');">✖ Rechazar</button>
              </form>
            <?php endif; ?>
          <?php endif; ?>

          <?php if ($ROL==='rh' || $ROL==='admin'): ?>
            <?php if (in_array($S['estado_actual'], ['APROBADA','EN_REV_RH'], true)): ?>
              <form method="post" onsubmit="return confirm('¿Tomar en gestión esta solicitud?');">
                <input type="hidden" name="action" value="gestionar">
                <button class="btn btn-primary">🛠 Tomar en gestión</button>
              </form>
            <?php endif; ?>

            <?php if ($S['estado_actual'] === 'EN_GESTION'): ?>
              <form method="post" onsubmit="return confirm('¿Marcar como ABIERTA (publicada)?');">
                <input type="hidden" name="action" value="abrir">
                <button class="btn btn-outline-primary">📣 Abrir</button>
              </form>
            <?php endif; ?>

            <?php if (in_array($S['estado_actual'], ['EN_GESTION','ABIERTA'], true)): ?>
              <form method="post" onsubmit="return confirm('¿Cerrar esta solicitud?');">
                <input type="hidden" name="action" value="cerrar">
                <button class="btn btn-outline-secondary">🔒 Cerrar</button>
              </form>
            <?php endif; ?>
          <?php endif; ?>

          <?php if ($ROL==='jefe_area' && (int)$S['autor_id']===$UID && $S['estado_actual']==='RECHAZADA'): ?>
            <!-- Sustituye href de editar según tu ruta -->
            <a class="btn btn-warning" href="<?= BASE_PATH; ?>/app/views/admin/solicitudes/crear_solicitud.php?editar=<?= (int)$S['id'] ?>">✏️ Editar</a>
            <form method="post" onsubmit="return confirm('¿Eliminar esta solicitud RECHAZADA? Esta acción no se puede deshacer.');">
              <input type="hidden" name="action" value="eliminar">
              <button class="btn btn-danger">🗑 Eliminar</button>
            </form>
          <?php endif; ?>

          <?php if ($ROL==='admin'): ?>
            <form method="post" class="d-flex align-items-center gap-2">
              <input type="hidden" name="action" value="cambiar_estado_admin">
              <select name="nuevo_estado" class="form-select">
                <?php
                  $valid = ['ENVIADA','EN_REV_GER','APROBADA','EN_REV_RH','EN_GESTION','ABIERTA','RECHAZADA','CERRADA'];
                  foreach($valid as $v){
                    $sel = ($v===$S['estado_actual'])?'selected':'';
                    echo '<option '.$sel.'>'.$v.'</option>';
                  }
                ?>
              </select>
              <button class="btn btn-dark" onclick="return confirm('¿Cambiar estado manualmente?');">Actualizar</button>
            </form>
          <?php endif; ?>
        </div>

        <!-- Comentarios -->
        <div class="section-title">Comentarios</div>
        <form method="post" class="comment-box">
          <input type="hidden" name="action" value="comentar">
          <textarea name="comentario" rows="2" class="form-control" placeholder="Escribe un comentario..." required></textarea>
          <button class="btn btn-outline-primary">Enviar</button>
        </form>

        <div class="comments mt-3">
          <?php if(!$comentarios): ?>
            <div class="text-muted">Aún no hay comentarios.</div>
          <?php else: foreach($comentarios as $c): ?>
            <div class="comment">
              <div class="who"><?= esc($c['autor'] ?: $c['usuario'] ?: 'Usuario') ?></div>
              <div class="when"><?= esc($c['creado_en'] ?: '') ?></div>
              <div class="mt-1"><?= nl2br(esc($c['comentario'])) ?></div>
            </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/footer.php'; ?>
