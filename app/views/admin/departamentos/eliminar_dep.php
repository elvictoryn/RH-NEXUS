<?php
// ============================================================
// Desactivar / Reactivar Departamento (soft delete)
// Entrada: POST id, opcional action=activar|inactivar (default inactivar)
// Salida: JSON { ok: bool, msg: string }
// ============================================================

define('BASE_PATH','/sistema_rh'); // <-- AJUSTA si tu carpeta cambia
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');

try {
    // Seguridad básica
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    if (!isset($_SESSION['usuario']) || !isset($_SESSION['rol'])) {
        throw new Exception('Sesión no válida');
    }
    // Permite solo admin (ajusta si quieres más roles)
    if (strtolower($_SESSION['rol']) !== 'admin') {
        throw new Exception('Acceso denegado');
    }

    // Validar entrada
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new Exception('ID inválido');
    }
    $action = strtolower(trim($_POST['action'] ?? 'inactivar'));
    if (!in_array($action, ['inactivar','activar'], true)) {
        throw new Exception('Acción inválida');
    }

    // Conexión
    require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
    $db = Conexion::getConexion();

    // Verificar que exista
    $chk = $db->prepare("SELECT id, estado FROM departamentos WHERE id=:id LIMIT 1");
    $chk->execute([':id'=>$id]);
    $row = $chk->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new Exception('Departamento no encontrado');
    }

    // Si ya está en el estado solicitado, no hacer nada
    $nuevoEstado = ($action === 'activar') ? 'activo' : 'inactivo';
    if (strtolower($row['estado']) === $nuevoEstado) {
        echo json_encode(['ok'=>true, 'msg'=>"Departamento ya está {$nuevoEstado}"]);
        exit;
    }

    // Actualizar (soft delete / toggle)
    $upd = $db->prepare("
        UPDATE departamentos
        SET estado = :estado,
            actualizado_en = NOW()
        WHERE id = :id
        LIMIT 1
    ");
    $upd->execute([':estado'=>$nuevoEstado, ':id'=>$id]);

    if ($upd->rowCount() < 1) {
        throw new Exception('No se pudo actualizar el estado');
    }

    // (Opcional) Auditoría básica
    // $aud = $db->prepare("INSERT INTO auditoria (usuario, accion, entidad, entidad_id, creado_en)
    //                      VALUES (:u, :a, 'departamentos', :id, NOW())");
    // $aud->execute([
    //     ':u' => $_SESSION['usuario'],
    //     ':a' => ($action==='activar'?'reactivar':'inactivar'),
    //     ':id'=> $id
    // ]);

    echo json_encode(['ok'=>true, 'msg'=>"Departamento {$nuevoEstado}"]);
} catch (Throwable $e) {
    http_response_code(200); // seguimos devolviendo JSON controlado
    echo json_encode(['ok'=>false, 'msg'=>$e->getMessage()]);
}
