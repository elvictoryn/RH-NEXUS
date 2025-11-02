<?php
if (!isset($_SESSION)) session_start();
header('Content-Type: application/json; charset=utf-8');

try{
  require_once dirname(__DIR__, 4) . '/config/conexion.php';
  $db = Conexion::getConexion();

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Método no permitido');

  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) throw new Exception('ID inválido');

  // Datos del usuario
  $st = $db->prepare("SELECT id, rol, sede_id, departamento_id FROM usuarios WHERE id=:id LIMIT 1");
  $st->execute([':id'=>$id]);
  $u = $st->fetch();
  if (!$u) throw new Exception('Usuario no encontrado');

  $db->beginTransaction();

  // Liberar vínculos para permitir nombrar nuevo responsable
  if ($u['rol'] === 'gerente' && !empty($u['sede_id'])) {
    $db->prepare("UPDATE sedes SET gerente_id=NULL WHERE id=:s AND gerente_id=:u")
       ->execute([':s'=>$u['sede_id'], ':u'=>$u['id']]);
  }
  if ($u['rol'] === 'jefe_area' && !empty($u['departamento_id'])) {
    $db->prepare("UPDATE departamentos SET responsable_id=NULL WHERE id=:d AND responsable_id=:u")
       ->execute([':d'=>$u['departamento_id'], ':u'=>$u['id']]);
  }

  // Inactivar (soft delete)
  $db->prepare("UPDATE usuarios SET estado='inactivo' WHERE id=:id")->execute([':id'=>$id]);

  $db->commit();
  echo json_encode(['ok'=>true, 'msg'=>'Usuario desactivado']);
}catch(Throwable $e){
  if (isset($db) && $db->inTransaction()) $db->rollBack();
  echo json_encode(['ok'=>false, 'msg'=>$e->getMessage()]);
}
