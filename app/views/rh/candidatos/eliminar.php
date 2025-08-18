<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario de RH
verificarRol('rh');

// Verificar contexto de trabajo (sede y departamento)
verificarContextoRol();

// Incluir modelo de candidato
safe_require_once(model_path('Candidato'));

// Verificar que se envió el ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_eliminacion'] = "❌ ID de candidato inválido.";
    header("Location: lista.php");
    exit;
}

$id = intval($_GET['id']);

try {
    $candidato_model = new Candidato();
    
    // Obtener información del candidato antes de eliminar
    $candidato = $candidato_model->obtenerPorId($id);
    
    if (!$candidato) {
        $_SESSION['error_eliminacion'] = "❌ Candidato no encontrado.";
        header("Location: lista.php");
        exit;
    }
    
    // Verificar que el candidato pertenezca al contexto del usuario
    if ($candidato['sede_id'] != $_SESSION['sede_seleccionada'] || 
        $candidato['departamento_id'] != $_SESSION['departamento_seleccionado']) {
        $_SESSION['error_eliminacion'] = "❌ No tienes permisos para eliminar este candidato.";
        header("Location: lista.php");
        exit;
    }
    
    // Realizar soft delete (cambiar estado a 'inactivo')
    $resultado = $candidato_model->eliminarLogico($id);
    
    if ($resultado) {
        $_SESSION['candidato_eliminado'] = "✅ El candidato '{$candidato['nombre']}' fue eliminado correctamente.";
    } else {
        $_SESSION['error_eliminacion'] = "❌ No se pudo eliminar el candidato. Intenta nuevamente.";
    }
    
} catch (Exception $e) {
    error_log("Error al eliminar candidato: " . $e->getMessage());
    $_SESSION['error_eliminacion'] = "❌ Error interno del sistema. Contacta al administrador.";
}

header("Location: lista.php");
exit;
?> 