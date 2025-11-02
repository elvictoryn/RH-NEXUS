<?php
// /app/actions/notificaciones.php
// Devuelve JSON para la campana de notificaciones usando la actividad de solicitudes (comentarios)
// Respeta visibilidad por rol y linkea a /app/views/admin/solicitudes/detalle.php?id=###

if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

try {
  require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
  $db = Conexion::getConexion();

  // ====== Sesión mínima ======
  $UID   = (int)($_SESSION['id'] ?? 0);
  $ROL   = strtolower($_SESSION['rol'] ?? '');
  $SEDE  = (int)($_SESSION['sede_id'] ?? 0);
  $DEPTO = (int)($_SESSION['departamento_id'] ?? 0);

  if (!$UID || !in_array($ROL, ['admin','rh','gerente','jefe_area'], true)) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit;
  }

  // ====== Filtro por visibilidad (igual que el Home) ======
  $where='1=0'; $params=[];
  if ($ROL==='jefe_area'){
    $where='(s.departamento_id=:d OR s.autor_id=:u)'; $params=[':d'=>$DEPTO,':u'=>$UID];
  } elseif ($ROL==='gerente'){
    $where='s.sede_id=:s'; $params=[':s'=>$SEDE];
  } elseif ($ROL==='rh' || $ROL==='admin'){
    $where='1=1';
  }

  // ====== Traer actividad reciente ======
  // Usamos comentarios como eventos de notificación
  $sql = "
    SELECT
      sc.id,
      sc.solicitud_id,
      sc.usuario_id,
      sc.comentario,
      sc.creado_en,

      s.titulo,
      s.puesto,
      s.estado_actual,

      u.nombre_completo   AS autor_nombre,
      u.usuario           AS autor_usuario,
      u.fotografia        AS autor_foto
    FROM solicitudes_comentarios sc
    JOIN solicitudes s ON s.id = sc.solicitud_id
    LEFT JOIN usuarios u ON u.id = sc.usuario_id
    WHERE $where
    ORDER BY sc.id DESC
    LIMIT 50
  ";

  $st = $db->prepare($sql);
  $st->execute($params);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

  // ====== Mapear a formato esperado por la campana ======
  $items = [];
  foreach ($rows as $r) {
    $autorNombre  = trim((string)($r['autor_nombre'] ?? ''));
    $autorUsuario = trim((string)($r['autor_usuario'] ?? ''));
    $foto         = trim((string)($r['autor_foto'] ?? ''));

    $items[] = [
      'id'             => (int)$r['id'],
      'solicitud_id'   => (int)$r['solicitud_id'],
      // Frase corta; el JS ya añade el verbo/contexto con icono
      'accion'         => 'comentó',
      // Texto (se mostrará como sublínea entre comillas)
      'comentario'     => (string)($r['comentario'] ?? ''),
      'creado_en'      => (string)($r['creado_en'] ?? ''),

      // Info del autor (para “quién hizo qué”)
      'autor_nombre'   => $autorNombre,
      'autor_usuario'  => $autorUsuario,

      // Foto (si no carga, el JS muestra la inicial)
      'fotografia_url' => ($foto ? (BASE_PATH.'/public/uploads/fotos/'.ltrim($foto,'/')) : ''),

      // Chip opcional con el estado de la solicitud
      'etiqueta'       => (string)($r['estado_actual'] ?? ''),

      // Leída/no leída: si no llevas control, van todas como no leídas
      'leida'          => false,

      // Link directo a detalle (requisito)
      'url'            => BASE_PATH . '/app/views/admin/solicitudes/detalle.php?id=' . (int)$r['solicitud_id'],
    ];
  }

  // Si no manejas “leídas”, el contador puede ser el total (o 0 si prefieres)
  $unread = count(array_filter($items, fn($i)=>!$i['leida']));

  echo json_encode([
    'ok' => true,
    'unread_count' => $unread,
    'items' => $items
  ], JSON_UNESCAPED_UNICODE);

} catch (\Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'Error interno','detail'=>$e->getMessage()]);
}
