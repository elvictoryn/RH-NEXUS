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

// Obtener información del contexto
$sede_id = $_SESSION['sede_seleccionada'];
$usuario_id = $_SESSION['usuario_id'];

// Obtener la solicitud
$solicitud_model = new Solicitud();
$solicitud = $solicitud_model->obtenerPorId($solicitud_id);

// Verificar que la solicitud existe y pertenece a la sede del gerente
if (!$solicitud || $solicitud['sede_id'] != $sede_id) {
    $_SESSION['error_decision'] = "Solicitud no encontrada o no tienes permisos para posponerla";
    header('Location: lista.php');
    exit;
}

// Verificar que la solicitud esté en estado "enviada a gerencia"
if ($solicitud['estado'] !== 'enviada a gerencia') {
    $_SESSION['error_decision'] = "Solo se pueden posponer solicitudes enviadas a gerencia";
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
    
    // Cambiar estado a "pospuesta"
    $resultado = $solicitud_model->cambiarEstado($solicitud_id, 'pospuesta');
    
    if ($resultado) {
        // El trigger automáticamente:
        // 1. Registrará el cambio en el historial
        // 2. Notificará al jefe de área del departamento
        
        $_SESSION['solicitud_pospuesta'] = "Solicitud pospuesta exitosamente. El jefe de área será notificado.";
        header('Location: lista.php');
        exit;
    } else {
        $_SESSION['error_decision'] = "Error al posponer la solicitud";
        header('Location: lista.php');
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error al posponer solicitud: " . $e->getMessage());
    $_SESSION['error_decision'] = "Error interno del servidor al posponer la solicitud";
    header('Location: lista.php');
    exit;
}
?> 