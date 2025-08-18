<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario gerente
verificarRol('gerente');

// Verificar contexto de trabajo (sede)
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

// Verificar que se proporcione la descripción de cambios
if (!isset($_GET['cambios']) || empty(trim($_GET['cambios']))) {
    $_SESSION['error_decision'] = "Descripción de cambios no proporcionada";
    header('Location: lista.php');
    exit;
}

$cambios_solicitados = trim($_GET['cambios']);

// Validar longitud de los cambios
if (strlen($cambios_solicitados) < 10) {
    $_SESSION['error_decision'] = "La descripción de cambios debe tener al menos 10 caracteres";
    header('Location: lista.php');
    exit;
}

// Obtener información del contexto
$sede_id = $_SESSION['sede_seleccionada'];
$usuario_id = $_SESSION['usuario_id'];

// Obtener la solicitud
$solicitud_model = new Solicitud();
$solicitud = $solicitud_model->obtenerPorId($solicitud_id);

// Verificar que la solicitud existe y pertenece a la sede del gerente
if (!$solicitud || $solicitud['sede_id'] != $sede_id) {
    $_SESSION['error_decision'] = "Solicitud no encontrada o no tienes permisos para solicitar cambios";
    header('Location: lista.php');
    exit;
}

// Verificar que la solicitud esté en estado "enviada a gerencia"
if ($solicitud['estado'] !== 'enviada a gerencia') {
    $_SESSION['error_decision'] = "Solo se pueden solicitar cambios en solicitudes enviadas a gerencia";
    header('Location: lista.php');
    exit;
}

try {
    // Asignar el gerente actual a la solicitud
    $asignacion_gerente = $solicitud_model->asignarGerente($solicitud_id, $usuario_id);
    
    if (!$asignacion_gerente) {
        $_SESSION['error_decision'] = "Error al asignar gerente a la solicitud";
        header('Location: lista.php');
        exit;
    }
    
    // Cambiar estado a "solicita cambios" y guardar los cambios solicitados
    $resultado = $solicitud_model->cambiarEstado($solicitud_id, 'solicita cambios', $cambios_solicitados);
    
    if ($resultado) {
        // El trigger automáticamente:
        // 1. Registrará el cambio en el historial
        // 2. Notificará al jefe de área del departamento
        
        $_SESSION['solicitud_cambios'] = "Cambios solicitados exitosamente. El jefe de área será notificado.";
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