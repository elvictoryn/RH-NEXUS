<?php
if (!isset($_SESSION)) session_start();
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__, 4) . '/config/conexion.php';
$db = Conexion::getConexion();

try {
  $id = (int)($_POST['id'] ?? 0);
  if ($id<=0) throw new Exception('ID inválido');

  $st = $db->prepare("UPDATE departamentos SET estado='inactivo', actualizado_en=NOW() WHERE id=:id");
  $st->execute([':id'=>$id]);

  echo json_encode(['ok'=>true, 'msg'=>'Departamento inactivado']);
} catch (Throwable $e) {
  echo json_encode(['ok'=>false, 'msg'=>$e->getMessage()]);
}