<?php
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');

try {
  require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
  $db = Conexion::getConexion();

  $UID = (int)($_SESSION['id'] ?? 0);
  $ROL = strtolower($_SESSION['rol'] ?? '');
  if (!$UID || !in_array($ROL, ['admin','rh','gerente','jefe_area'], true)) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit;
  }

  $sede_id = isset($_GET['sede_id']) ? (int)$_GET['sede_id'] : null;
  $dep_id  = isset($_GET['dep_id'])  ? (int)$_GET['dep_id']  : null;
  $q       = trim((string)($_GET['q'] ?? ''));

  // Campos habituales en tu BD:
  // usuarios: id, nombre_completo, usuario, correo, email, telefono, telefono_movil, celular, extension, puesto, departamento_id, sede_id, fotografia
  // departamentos: id, nombre
  // sedes: id, nombre

  $sql = "
    SELECT u.id,
           COALESCE(u.nombre_completo, u.usuario) AS nombre_completo,
           u.usuario,
           COALESCE(u.email, u.correo) AS email,
           u.telefono, u.telefono_movil, u.celular, u.extension,
           u.puesto,
           d.nombre AS dep_nombre,
           s.nombre AS sede_nombre,
           u.fotografia
    FROM usuarios u
    LEFT JOIN departamentos d ON d.id = u.departamento_id
    LEFT JOIN sedes s        ON s.id = u.sede_id
    WHERE 1=1
  ";

  $params = [];
  if ($sede_id) { $sql .= " AND u.sede_id = :sede";  $params[':sede'] = $sede_id; }
  if ($dep_id)  { $sql .= " AND u.departamento_id = :dep"; $params[':dep']  = $dep_id; }
  if ($q !== '') {
    $sql .= " AND (u.nombre_completo LIKE :q OR u.usuario LIKE :q OR u.email LIKE :q OR u.correo LIKE :q OR u.telefono LIKE :q OR u.telefono_movil LIKE :q OR u.celular LIKE :q OR u.extension LIKE :q)";
    $params[':q'] = '%'.$q.'%';
  }
  $sql .= " ORDER BY s.nombre, d.nombre, u.nombre_completo, u.usuario LIMIT 600";

  $st = $db->prepare($sql);
  $st->execute($params);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

  // Prepara URLs de foto si existen
  $baseFoto = BASE_PATH . '/public/img/usuarios/';
  foreach($rows as &$r){
    $f = (string)($r['fotografia'] ?? '');
    $r['fotografia_url'] = $f ? ($baseFoto . rawurlencode($f)) : null;
  }

  echo json_encode(['ok'=>true,'items'=>$rows], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>'EXC: '.$e->getMessage()]);
}
