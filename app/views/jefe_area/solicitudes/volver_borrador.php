<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario jefe de área
verificarRol('jefe_area');

// Verificar contexto de trabajo (sede y departamento)
verificarContextoRol();

// Incluir modelo de solicitud
safe_require_once(model_path('Solicitud'));

// Verificar que se proporcione un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_edicion'] = "ID de solicitud no proporcionado";
    header('Location: lista.php');
    exit;
}

$solicitud_id = (int)$_GET['id'];

// Obtener información del contexto
$sede_id = $_SESSION['sede_seleccionada'];
$departamento_id = $_SESSION['departamento_seleccionado'];
$usuario_id = $_SESSION['usuario_id'];

// Obtener la solicitud
$solicitud_model = new Solicitud();
$solicitud = $solicitud_model->obtenerPorId($solicitud_id);

// Verificar que la solicitud existe y pertenece al departamento del usuario
if (!$solicitud || $solicitud['departamento_id'] != $departamento_id || $solicitud['sede_id'] != $sede_id) {
    $_SESSION['error_edicion'] = "Solicitud no encontrada o no tienes permisos para modificarla";
    header('Location: lista.php');
    exit;
}

// Verificar que la solicitud esté en estado "enviada a gerencia"
if ($solicitud['estado'] !== 'enviada a gerencia') {
    $_SESSION['error_edicion'] = "Solo se pueden volver a borrador solicitudes enviadas a gerencia";
    header('Location: lista.php');
    exit;
}

try {
    // Cambiar estado a "borrador"
    $resultado = $solicitud_model->cambiarEstado($solicitud_id, 'borrador');
    
    if ($resultado) {
        // El trigger automáticamente registrará el cambio en el historial
        
        $_SESSION['solicitud_editada'] = "Solicitud regresada a estado borrador exitosamente";
        header('Location: lista.php');
        exit;
    } else {
        $_SESSION['error_edicion'] = "Error al regresar la solicitud a borrador";
        header('Location: lista.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error al volver solicitud a borrador: " . $e->getMessage());
    $_SESSION['error_edicion'] = "Error interno del servidor al modificar la solicitud";
    header('Location: lista.php');
    exit;
}
?> 