<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario RH
verificarRol('rh');

// Verificar contexto de trabajo (sede y departamento)
verificarContextoRol();

// Incluir modelo de solicitud
safe_require_once(model_path('Solicitud'));

// Verificar que se proporcione un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_decision'] = "ID de solicitud no proporcionado";
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

// Verificar que la solicitud existe
if (!$solicitud) {
    $_SESSION['error_decision'] = "Solicitud no encontrada";
    header('Location: lista.php');
    exit;
}

// Verificar que la solicitud esté en un estado que permita cerrarla
$estados_permitidos = ['aceptada gerencia', 'en proceso rh'];
if (!in_array($solicitud['estado'], $estados_permitidos)) {
    $_SESSION['error_decision'] = "Solo se pueden cerrar solicitudes aceptadas por gerencia o en proceso RH";
    header('Location: lista.php');
    exit;
}

// Verificar que se proporcione el motivo del cierre
if (!isset($_GET['motivo']) || empty(trim($_GET['motivo']))) {
    $_SESSION['error_decision'] = "Debes especificar el motivo del cierre";
    header('Location: lista.php');
    exit;
}

$motivo_cierre = trim($_GET['motivo']);

// Verificar longitud mínima
if (strlen($motivo_cierre) < 10) {
    $_SESSION['error_decision'] = "El motivo del cierre debe tener al menos 10 caracteres";
    header('Location: lista.php');
    exit;
}

try {
    // Cambiar estado a "cerrada"
    $resultado = $solicitud_model->cambiarEstado($solicitud_id, 'cerrada', $motivo_cierre);
    
    if ($resultado) {
        // El trigger automáticamente:
        // 1. Registrará el cambio en el historial
        // 2. Notificará al jefe de área del departamento
        
        $_SESSION['solicitud_cerrada'] = "Solicitud cerrada exitosamente. El jefe de área ha sido notificado.";
        header('Location: lista.php');
        exit;
    } else {
        $_SESSION['error_decision'] = "Error al cerrar la solicitud";
        header('Location: lista.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error al cerrar solicitud: " . $e->getMessage());
    $_SESSION['error_decision'] = "Error interno del servidor al cerrar la solicitud";
    header('Location: lista.php');
    exit;
}
?>
