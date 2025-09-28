<?php
// ============================================================
// Acciones sobre Solicitud (autorizar, rechazar, comentar, eliminar)
// Respuestas JSON para ser usadas desde detalle.php
// ============================================================
define('BASE_PATH','/sistema_rh');
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
$db = Conexion::getConexion();

$UID      = (int)($_SESSION['id'] ?? 0);
$ROL      = strtolower($_SESSION['rol'] ?? '');
$SEDE_ID  = isset($_SESSION['sede_id']) ? (int)$_SESSION['sede_id'] : null;

function out($ok, $msg='', $extra=[]){
  echo json_encode(array_merge(['ok'=>$ok,'msg'=>$msg], $extra)); exit;
}
function esc_js($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

if (!$UID || !in_array($ROL, ['admin','rh','gerente','jefe_area'], true)) out(false,'No autenticado');

$accion = $_POST['accion'] ?? '';
$SID    = (int)($_POST['id'] ?? 0);
if (!$SID) out(false,'ID inválido');

// Cargar solicitud + permisos base
try{
  $st = $db->prepare("SELECT * FROM solicitudes WHERE id=? LIMIT 1");
  $st->execute([$SID]);
  $S = $st->fetch(PDO::FETCH_ASSOC);
  if (!$S) out(false,'Solicitud no encontrada');

  // Reglas de visibilidad
  if ($ROL==='jefe_area' && (int)$S['autor_id'] !== $UID) out(false,'Sin permiso');
  if ($ROL==='gerente') {
    if (!$SEDE_ID || (int)$S['sede_id'] !== $SEDE_ID) out(false,'Sin permiso (sede)');
  }
  if ($ROL==='rh') {
    $visibles = ['APROBADA_GER','EN_REV_RH','ABIERTA','CERRADA_CUBIERTA','CERRADA_CANCELADA','CERRADA_CADUCADA'];
    if (!in_array($S['estado_actual'], $visibles, true)) out(false,'No disponible para RH');
  }

  // Acciones
  if ($accion === 'autorizar') {
    if ($ROL!=='gerente') out(false,'Solo gerente puede autorizar');
    if ($S['estado_actual']!=='ENVIADA') out(false,'Estado incompatible para autorizar');

    $db->beginTransaction();
    $db->prepare("UPDATE solicitudes SET estado_actual='APROBADA_GER', autorizada_por=?, autorizada_en=NOW(), rechazo_motivo=NULL WHERE id=?")
       ->execute([$UID,$SID]);

    // seguimiento
    $db->prepare("INSERT INTO solicitudes_seguimiento (solicitud_id, actor_id, accion, detalle, creado_en)
                  VALUES (?,?,?,?,NOW())")
       ->execute([$SID,$UID,'AUTORIZAR','Gerente autorizó y envió a RH']);

    // notifica a autor y RH (si tienes tabla notificaciones)
    try{
      // autor
      $db->prepare("INSERT INTO notificaciones (usuario_id, mensaje, link, leida, expira_en)
                    VALUES (?,?,?,?,DATE_ADD(NOW(), INTERVAL 15 DAY))")
         ->execute([$S['autor_id'], "Tu solicitud ID-$SID fue autorizada por Gerencia", BASE_PATH."/app/views/solicitudes/detalle.php?id=$SID", 0]);
      // a RH (todos los usuarios RH)
      $q = $db->query("SELECT id FROM usuarios WHERE rol='rh' AND estado='activo'");
      foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $rh) {
        $db->prepare("INSERT INTO notificaciones (usuario_id, mensaje, link, leida, expira_en)
                      VALUES (?,?,?,?,DATE_ADD(NOW(), INTERVAL 15 DAY))")
           ->execute([$rh['id'], "Nueva solicitud autorizada (ID-$SID) para gestión", BASE_PATH."/app/views/solicitudes/detalle.php?id=$SID", 0]);
      }
    }catch(\Throwable $e){/*silencio*/}

    $db->commit();
    out(true,'Autorizada');

  } elseif ($accion === 'rechazar') {
    if ($ROL!=='gerente') out(false,'Solo gerente puede rechazar');
    if ($S['estado_actual']!=='ENVIADA') out(false,'Estado incompatible para rechazar');
    $motivo = trim($_POST['motivo'] ?? '');
    if ($motivo==='') out(false,'Motivo obligatorio');

    $db->beginTransaction();
    $db->prepare("UPDATE solicitudes SET estado_actual='RECHAZADA_GER', rechazada_por=?, rechazada_en=NOW(), rechazo_motivo=? WHERE id=?")
       ->execute([$UID, $motivo, $SID]);

    $db->prepare("INSERT INTO solicitudes_seguimiento (solicitud_id, actor_id, accion, detalle, creado_en)
                  VALUES (?,?,?,?,NOW())")
       ->execute([$SID,$UID,'RECHAZAR', 'Gerente rechazó: '.$motivo]);

    try{
      $db->prepare("INSERT INTO notificaciones (usuario_id, mensaje, link, leida, expira_en)
                    VALUES (?,?,?,?,DATE_ADD(NOW(), INTERVAL 15 DAY))")
         ->execute([$S['autor_id'], "Tu solicitud ID-$SID fue rechazada por Gerencia", BASE_PATH."/app/views/solicitudes/detalle.php?id=$SID", 0]);
    }catch(\Throwable $e){}

    $db->commit();
    out(true,'Rechazada');

  } elseif ($accion === 'eliminar') {
    // Autor puede eliminar solo si RECHAZADA_GER o BORRADOR
    if (!($ROL==='jefe_area' && (int)$S['autor_id']===$UID && in_array($S['estado_actual'], ['RECHAZADA_GER','BORRADOR'], true))) {
      out(false,'No puedes eliminar esta solicitud');
    }

    $db->beginTransaction();
    // borrar comentarios y seguimiento primero si FK no cascade
    $db->prepare("DELETE FROM solicitudes_comentarios WHERE solicitud_id=?")->execute([$SID]);
    $db->prepare("DELETE FROM solicitudes_seguimiento WHERE solicitud_id=?")->execute([$SID]);
    $db->prepare("DELETE FROM solicitudes WHERE id=?")->execute([$SID]);
    $db->commit();
    out(true,'Eliminada');

  } elseif ($accion === 'comentar') {
    $txt = trim($_POST['comentario'] ?? '');
    if ($txt==='') out(false,'Comentario vacío');

    $db->prepare("INSERT INTO solicitudes_comentarios (solicitud_id, usuario_id, comentario, creado_en) VALUES (?,?,?,NOW())")
       ->execute([$SID,$UID,$txt]);

    $safe = nl2br(esc_js($txt));
    out(true,'Comentado',['comentario_html'=>$safe]);

  } else {
    out(false,'Acción no soportada');
  }

} catch(\Throwable $e){
  if ($db->inTransaction()) $db->rollBack();
  out(false, 'Error: '.$e->getMessage());
}
