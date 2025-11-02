<?php
// ============================================================
// Guardar Departamento - Nexus RH
// Ruta: /app/views/admin/departamentos/guardar_dep.php
// ============================================================

define('BASE_PATH', '/sistema_rh');  // <-- ajústalo si cambia tu carpeta
if (session_status() === PHP_SESSION_NONE) session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/models/departamento.php';

function redirect_abs(string $pathRelativeToBase) {
    header('Location: ' . BASE_PATH . '/' . ltrim($pathRelativeToBase, '/'));
    exit;
}

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // ⚠️ NO usamos flash_* ni *error_guardado* para evitar alertas del header
    $_SESSION['dep_err'] = 'Método no permitido.';
    redirect_abs('app/views/admin/departamentos/crear_dep.php');
}

// Sanitizar / normalizar
$nombre      = strtoupper(trim($_POST['nombre'] ?? ''));
$descripcion = strtoupper(trim($_POST['descripcion'] ?? ''));
$sede_id     = intval($_POST['sede_id'] ?? 0);

// Validación de requeridos
if ($nombre === '' || $descripcion === '' || $sede_id <= 0) {
    $_SESSION['dep_err'] = '❌ Todos los campos son obligatorios.';
    redirect_abs('app/views/admin/departamentos/crear_dep.php');
}

try {
    $departamento = new Departamento();

    // Duplicado por sede
    if ($departamento->existeNombreEnSede($nombre, $sede_id)) {
        $_SESSION['dep_err'] = '❌ Ya existe un departamento con ese nombre en la sede seleccionada.';
        redirect_abs('app/views/admin/departamentos/crear_dep.php');
    }

    // Datos a guardar
    $data = [
        'nombre'      => $nombre,
        'descripcion' => $descripcion,
        'sede_id'     => $sede_id,
    ];

    $ok = $departamento->crear($data);

    if ($ok) {
        // ✅ Solo llaves “locales” (las usa crear_dep.php para SweetAlert)
        $_SESSION['dep_ok']    = "✅ El departamento '{$data['nombre']}' fue registrado correctamente.";
        $_SESSION['form_reset'] = 1; // limpiar formulario al volver
        redirect_abs('app/views/admin/departamentos/crear_dep.php');
    } else {
        $_SESSION['dep_err'] = '❌ Ocurrió un error al guardar. Intenta nuevamente.';
        redirect_abs('app/views/admin/departamentos/crear_dep.php');
    }

} catch (Throwable $e) {
    $_SESSION['dep_err'] = '❌ Error inesperado al guardar. Intenta más tarde.';
    redirect_abs('app/views/admin/departamentos/crear_dep.php');
}
