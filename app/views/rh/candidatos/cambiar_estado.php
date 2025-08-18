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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $candidato_id = $_POST['candidato_id'] ?? null;
    $nuevo_estado = $_POST['nuevo_estado'] ?? null;
    
    if (!$candidato_id || !$nuevo_estado) {
        $_SESSION['error_edicion'] = "❌ Datos incompletos para cambiar el estado.";
        header("Location: lista.php");
        exit;
    }
    
    // Validar que el estado sea válido
    $estados_validos = ['activo', 'contratado', 'rechazado'];
    if (!in_array($nuevo_estado, $estados_validos)) {
        $_SESSION['error_edicion'] = "❌ Estado no válido.";
        header("Location: lista.php");
        exit;
    }
    
    // Crear instancia del modelo
    $candidato_model = new Candidato();
    
    // Cambiar el estado del candidato
    $resultado = $candidato_model->cambiarEstado($candidato_id, $nuevo_estado);
    
    if ($resultado) {
        $_SESSION['candidato_editado'] = "✅ El estado del candidato fue actualizado correctamente.";
    } else {
        $_SESSION['error_edicion'] = "❌ Error al cambiar el estado del candidato.";
    }
    
    header("Location: lista.php");
    exit;
} else {
    // Si no es POST, redirigir a la lista
    header("Location: lista.php");
    exit;
}
?> 