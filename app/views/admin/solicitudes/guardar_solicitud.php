<?php
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
if (session_status() === PHP_SESSION_NONE) session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
$db = Conexion::getConexion();

$UID = (int)($_SESSION['id'] ?? 0);
$ROL = strtolower($_SESSION['rol'] ?? '');
$SEDE = (int)($_SESSION['sede_id'] ?? 0);
$DEP  = (int)($_SESSION['departamento_id'] ?? 0);

$isAjax = isset($_POST['ajax']);

// Para respuestas AJAX, evita que cualquier warning rompa el JSON:
if ($isAjax) {
  @ini_set('display_errors', '0');
  @ini_set('html_errors', '0');
  header('X-Content-Type-Options: nosniff');
}

function json_exit($arr){
  if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
  // Limpia posibles espacios previos
  if (ob_get_length()) { @ob_clean(); }
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

if (!$UID || !in_array($ROL, ['admin','rh','gerente','jefe_area'], true)) {
  if ($isAjax) json_exit(['ok'=>false,'msg'=>'Sesión inválida']);
  header('Location: '.BASE_PATH.'/public/login.php'); exit;
}

function U($s){ return mb_strtoupper(trim((string)$s), 'UTF-8'); }

$estadoInicial = 'ENVIADA';

// --------- Datos ---------
$titulo            = U($_POST['titulo'] ?? '');
$puesto            = U($_POST['puesto'] ?? '');
$vacantes          = max(1, (int)($_POST['vacantes'] ?? 1));

$sede_id           = (int)($_POST['sede_id'] ?? 0);
$departamento_id   = (int)($_POST['departamento_id'] ?? 0);

$fecha_ingreso     = $_POST['fecha_ingreso_deseada'] ?? null;
$tipo_contrato     = U($_POST['tipo_contrato'] ?? '');
$modalidad         = U($_POST['modalidad'] ?? '');
$horario           = U($_POST['horario'] ?? '');

$salario_min       = strlen($_POST['salario_min'] ?? '') ? (float)$_POST['salario_min'] : null;
$salario_max       = strlen($_POST['salario_max'] ?? '') ? (float)$_POST['salario_max'] : null;

$escolaridad_min   = $_POST['escolaridad_min'] ?? '';
$escolaridad_min   = ($escolaridad_min === '' ? null : max(1, min(4, (int)$escolaridad_min))); // 1..4 o NULL

$carrera_estudiada = U($_POST['carrera_estudiada'] ?? '');
$experiencia_anios = (int)($_POST['experiencia_anios'] ?? 0);
$area_experiencia  = U($_POST['area_experiencia'] ?? '');
$ingles_combo      = U($_POST['ingles_combo'] ?? '0');

$competencias_json = $_POST['competencias_json'] ?? '';

$motivo            = U($_POST['motivo'] ?? '');
$reemplazo_de      = U($_POST['reemplazo_de'] ?? '');
$prioridad         = U($_POST['prioridad'] ?? 'NORMAL');

$justificacion     = U($_POST['justificacion'] ?? '');
$responsabilidades = U($_POST['responsabilidades'] ?? '');

// Reglas por rol
if ($ROL==='gerente' && $SEDE) { $sede_id = $SEDE; }
if ($ROL==='jefe_area' && $DEP) { $departamento_id = $DEP; }

// Validaciones
$errors = [];
if ($puesto === '') $errors[] = 'El campo PUESTO es obligatorio.';
if ($sede_id <= 0) $errors[] = 'Debes seleccionar una SEDE.';
if ($departamento_id <= 0) $errors[] = 'Debes seleccionar un DEPARTAMENTO.';
if ($justificacion === '') $errors[] = 'La JUSTIFICACIÓN es obligatoria.';
if ($salario_min !== null && $salario_max !== null && $salario_min > $salario_max) $errors[] = 'SALARIO MIN no puede ser mayor que SALARIO MAX.';

if ($errors) {
  if ($isAjax) json_exit(['ok'=>false,'msg'=>implode(' ', $errors)]);
  $_SESSION['flash_error'] = implode("\n", $errors);
  header('Location: '.BASE_PATH.'/app/views/admin/solicitudes/crear_solicitud.php'); exit;
}

// Insertar
try {
  $db->beginTransaction();

  $st = $db->prepare("
    INSERT INTO solicitudes
    (autor_id, sede_id, departamento_id, titulo, puesto, vacantes,
     fecha_ingreso_deseada, tipo_contrato, modalidad, horario,
     salario_min, salario_max,
     escolaridad_min, carrera_estudiada, experiencia_anios, area_experiencia, ingles_combo, competencias_json,
     motivo, reemplazo_de, prioridad, justificacion, responsabilidades,
     estado_actual, creada_en)
    VALUES
    (:autor_id, :sede_id, :departamento_id, :titulo, :puesto, :vacantes,
     :fecha_ingreso_deseada, :tipo_contrato, :modalidad, :horario,
     :salario_min, :salario_max,
     :escolaridad_min, :carrera_estudiada, :experiencia_anios, :area_experiencia, :ingles_combo, :competencias_json,
     :motivo, :reemplazo_de, :prioridad, :justificacion, :responsabilidades,
     :estado_actual, NOW())
  ");
  $st->execute([
    ':autor_id'            => $UID,
    ':sede_id'             => $sede_id,
    ':departamento_id'     => $departamento_id,
    ':titulo'              => ($titulo ?: null),
    ':puesto'              => $puesto,
    ':vacantes'            => $vacantes,

    ':fecha_ingreso_deseada'=> $fecha_ingreso ?: null,
    ':tipo_contrato'        => $tipo_contrato ?: null,
    ':modalidad'            => $modalidad ?: null,
    ':horario'              => $horario ?: null,

    ':salario_min'          => $salario_min,
    ':salario_max'          => $salario_max,

    ':escolaridad_min'      => $escolaridad_min,
    ':carrera_estudiada'    => ($carrera_estudiada ?: null),
    ':experiencia_anios'    => ($experiencia_anios ?: null),
    ':area_experiencia'     => ($area_experiencia ?: null),
    ':ingles_combo'         => ($ingles_combo ?: '0'),
    ':competencias_json'    => ($competencias_json ?: null),

    ':motivo'               => ($motivo ?: null),
    ':reemplazo_de'         => ($reemplazo_de ?: null),
    ':prioridad'            => ($prioridad==='URGENTE' ? 'URGENTE' : 'NORMAL'),
    ':justificacion'        => $justificacion,
    ':responsabilidades'    => ($responsabilidades ?: null),

    ':estado_actual'        => $estadoInicial,
  ]);

  $SID = (int)$db->lastInsertId();

  $st2 = $db->prepare("
    INSERT INTO solicitudes_seguimiento (solicitud_id, estado_anterior, estado_nuevo, usuario_id, nota, creado_en)
    VALUES (?, NULL, 'ENVIADA', ?, 'Solicitud enviada por el autor', NOW())
  ");
  $st2->execute([$SID, $UID]);

  $db->commit();

  $detalleUrl = BASE_PATH . '/app/views/admin/solicitudes/menu.php?id=' . $SID;

  if ($isAjax) json_exit(['ok'=>true, 'id'=>$SID, 'detalle_url'=>$detalleUrl]);

  header('Location: '.$detalleUrl); exit;

} catch (\Throwable $e) {
  if ($db->inTransaction()) $db->rollBack();
  if ($isAjax) json_exit(['ok'=>false,'msg'=>'Ocurrió un error al guardar la solicitud.']);
  $_SESSION['flash_error'] = 'Ocurrió un error al guardar la solicitud.';
  header('Location: '.BASE_PATH.'/app/views/admin/solicitudes/crear_solicitud.php'); exit;
}
