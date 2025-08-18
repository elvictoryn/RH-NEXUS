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

// Verificar que la solicitud esté en estado "aceptada gerencia"
if ($solicitud['estado'] !== 'aceptada gerencia') {
    $_SESSION['error_decision'] = "Solo se pueden procesar solicitudes aceptadas por gerencia";
    header('Location: lista.php');
    exit;
}

try {
    // Cambiar estado a "en proceso rh"
    $resultado = $solicitud_model->cambiarEstado($solicitud_id, 'en proceso rh');
    
    if ($resultado) {
        // El trigger automáticamente:
        // 1. Registrará el cambio en el historial
        // 2. Notificará al jefe de área del departamento
        
        $_SESSION['solicitud_procesada'] = "Proceso de RH iniciado exitosamente. La solicitud está ahora en gestión de Recursos Humanos.";
        header('Location: lista.php');
        exit;
    } else {
        $_SESSION['error_decision'] = "Error al iniciar el proceso de RH";
        header('Location: lista.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error al iniciar proceso RH: " . $e->getMessage());
    $_SESSION['error_decision'] = "Error interno del servidor al iniciar el proceso";
    header('Location: lista.php');
    exit;
}
?>
