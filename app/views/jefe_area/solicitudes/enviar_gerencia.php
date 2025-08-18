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
    $_SESSION['error_envio'] = "ID de solicitud no proporcionado";
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
    $_SESSION['error_envio'] = "Solicitud no encontrada o no tienes permisos para enviarla";
    header('Location: lista.php');
    exit;
}

// Verificar que la solicitud esté en estado borrador
if ($solicitud['estado'] !== 'borrador') {
    $_SESSION['error_envio'] = "Solo se pueden enviar solicitudes en estado borrador";
    header('Location: lista.php');
    exit;
}

try {
    // Cambiar estado a "enviada a gerencia"
    $resultado = $solicitud_model->cambiarEstado($solicitud_id, 'enviada a gerencia');
    
    if ($resultado) {
        // El trigger automáticamente:
        // 1. Registrará el cambio en el historial
        // 2. Notificará a RH y gerentes del departamento
        
        $_SESSION['solicitud_enviada'] = "Solicitud enviada exitosamente a gerencia para revisión";
        header('Location: lista.php');
        exit;
    } else {
        $_SESSION['error_envio'] = "Error al enviar la solicitud a gerencia";
        header('Location: lista.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error al enviar solicitud a gerencia: " . $e->getMessage());
    $_SESSION['error_envio'] = "Error interno del servidor al enviar la solicitud";
    header('Location: lista.php');
    exit;
}
?> 