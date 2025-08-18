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

// Verificar que la solicitud esté en un estado que permita solicitar cambios
$estados_permitidos = ['enviada a gerencia', 'aceptada gerencia', 'en proceso rh'];
if (!in_array($solicitud['estado'], $estados_permitidos)) {
    $_SESSION['error_decision'] = "Solo se pueden solicitar cambios en solicitudes enviadas a gerencia, aceptadas por gerencia o en proceso RH";
    header('Location: lista.php');
    exit;
}

// Verificar que se proporcione el texto de los cambios
if (!isset($_GET['cambios']) || empty(trim($_GET['cambios']))) {
    $_SESSION['error_decision'] = "Debes especificar los cambios solicitados";
    header('Location: lista.php');
    exit;
}

$cambios_solicitados = trim($_GET['cambios']);

// Verificar longitud mínima
if (strlen($cambios_solicitados) < 10) {
    $_SESSION['error_decision'] = "Los cambios solicitados deben tener al menos 10 caracteres";
    header('Location: lista.php');
    exit;
}

try {
    // Cambiar estado a "solicita cambios"
    $resultado = $solicitud_model->cambiarEstado($solicitud_id, 'solicita cambios', $cambios_solicitados);
    
    if ($resultado) {
        // El trigger automáticamente:
        // 1. Registrará el cambio en el historial
        // 2. Notificará al jefe de área del departamento
        
        $_SESSION['cambios_solicitados'] = "Cambios solicitados exitosamente al jefe de área. La solicitud ha sido marcada para revisión.";
        header('Location: lista.php');
        exit;
    } else {
        $_SESSION['error_decision'] = "Error al solicitar cambios en la solicitud";
        header('Location: lista.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error al solicitar cambios en solicitud: " . $e->getMessage());
    $_SESSION['error_decision'] = "Error interno del servidor al solicitar cambios";
    header('Location: lista.php');
    exit;
}
?>
