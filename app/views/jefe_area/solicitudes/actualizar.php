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

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_edicion'] = "Método no permitido";
    header('Location: lista.php');
    exit;
}

// Obtener información del contexto
$sede_id = $_SESSION['sede_seleccionada'];
$departamento_id = $_SESSION['departamento_seleccionado'];
$usuario_id = $_SESSION['usuario_id'];

// Verificar que se proporcione el ID de la solicitud
if (!isset($_POST['solicitud_id']) || empty($_POST['solicitud_id'])) {
    $_SESSION['error_edicion'] = "ID de solicitud no proporcionado";
    header('Location: lista.php');
    exit;
}

$solicitud_id = (int)$_POST['solicitud_id'];

// Obtener la solicitud para verificar permisos
$solicitud_model = new Solicitud();
$solicitud_actual = $solicitud_model->obtenerPorId($solicitud_id);

// Verificar que la solicitud existe y pertenece al departamento del usuario
if (!$solicitud_actual || $solicitud_actual['departamento_id'] != $departamento_id || $solicitud_actual['sede_id'] != $sede_id) {
    $_SESSION['error_edicion'] = "Solicitud no encontrada o no tienes permisos para editarla";
    header('Location: lista.php');
    exit;
}

// Verificar que la solicitud esté en estado borrador o solicita cambios
if ($solicitud_actual['estado'] !== 'borrador' && $solicitud_actual['estado'] !== 'solicita cambios') {
    $_SESSION['error_edicion'] = "Solo se pueden editar solicitudes en estado borrador o cuando se solicitan cambios";
    header('Location: lista.php');
    exit;
}

// Validar campos requeridos
$campos_requeridos = ['titulo', 'descripcion', 'perfil_puesto', 'cantidad', 'prioridad', 'modalidad'];
foreach ($campos_requeridos as $campo) {
    if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
        $_SESSION['error_edicion'] = "El campo '$campo' es requerido";
        header('Location: editar.php?id=' . $solicitud_id);
        exit;
    }
}

// Validar longitud de campos
if (strlen(trim($_POST['titulo'])) < 10) {
    $_SESSION['error_edicion'] = "El título debe tener al menos 10 caracteres";
    header('Location: editar.php?id=' . $solicitud_id);
    exit;
}

if (strlen(trim($_POST['descripcion'])) < 20) {
    $_SESSION['error_edicion'] = "La descripción debe tener al menos 20 caracteres";
    header('Location: editar.php?id=' . $solicitud_id);
    exit;
}

if (strlen(trim($_POST['perfil_puesto'])) < 5) {
    $_SESSION['error_edicion'] = "El perfil del puesto debe tener al menos 5 caracteres";
    header('Location: editar.php?id=' . $solicitud_id);
    exit;
}

// Validar cantidad
$cantidad = (int)$_POST['cantidad'];
if ($cantidad < 1 || $cantidad > 10) {
    $_SESSION['error_edicion'] = "La cantidad debe estar entre 1 y 10";
    header('Location: editar.php?id=' . $solicitud_id);
    exit;
}

// Validar prioridad
$prioridades_validas = ['alta', 'media', 'baja'];
if (!in_array($_POST['prioridad'], $prioridades_validas)) {
    $_SESSION['error_edicion'] = "Prioridad no válida";
    header('Location: editar.php?id=' . $solicitud_id);
    exit;
}

// Validar modalidad
$modalidades_validas = ['presencial', 'remoto', 'hibrido'];
if (!in_array($_POST['modalidad'], $modalidades_validas)) {
    $_SESSION['error_edicion'] = "Modalidad no válida";
    header('Location: editar.php?id=' . $solicitud_id);
    exit;
}

// Validar salarios
$salario_min = !empty($_POST['salario_min']) ? (float)$_POST['salario_min'] : null;
$salario_max = !empty($_POST['salario_max']) ? (float)$_POST['salario_max'] : null;

if ($salario_min !== null && $salario_max !== null && $salario_max < $salario_min) {
    $_SESSION['error_edicion'] = "El salario máximo debe ser mayor al mínimo";
    header('Location: editar.php?id=' . $solicitud_id);
    exit;
}

// Validar fecha límite
$fecha_limite = !empty($_POST['fecha_limite_cobertura']) ? $_POST['fecha_limite_cobertura'] : null;
if ($fecha_limite) {
    $fecha_limite_obj = DateTime::createFromFormat('Y-m-d', $fecha_limite);
    if (!$fecha_limite_obj || $fecha_limite_obj->format('Y-m-d') !== $fecha_limite) {
        $_SESSION['error_edicion'] = "Formato de fecha límite no válido";
        header('Location: editar.php?id=' . $solicitud_id);
        exit;
    }
}

// Procesar requisitos
$requisitos = [];
if (isset($_POST['requisitos']) && is_array($_POST['requisitos'])) {
    $requisitos = $_POST['requisitos'];
    
    // Procesar habilidades (convertir de string a array)
    if (isset($requisitos['habilidades']) && !empty($requisitos['habilidades'])) {
        $habilidades_array = array_map('trim', explode(',', $requisitos['habilidades']));
        $habilidades_array = array_filter($habilidades_array); // Remover elementos vacíos
        $requisitos['habilidades'] = $habilidades_array;
    }
    
    // Limpiar campos vacíos
    $requisitos = array_filter($requisitos, function($valor) {
        return !empty($valor) || $valor === '0';
    });
}

// Preparar datos para actualizar
$datos_actualizacion = [
    'titulo' => trim($_POST['titulo']),
    'descripcion' => trim($_POST['descripcion']),
    'departamento_id' => $departamento_id,
    'sede_id' => $sede_id,
    'perfil_puesto' => trim($_POST['perfil_puesto']),
    'cantidad' => $cantidad,
    'prioridad' => $_POST['prioridad'],
    'modalidad' => $_POST['modalidad'],
    'salario_min' => $salario_min,
    'salario_max' => $salario_max,
    'fecha_limite_cobertura' => $fecha_limite,
    'requisitos_json' => !empty($requisitos) ? $requisitos : null,
    'estado' => 'borrador' // Mantener en borrador
];

try {
    // Actualizar la solicitud
    $resultado = $solicitud_model->actualizar($solicitud_id, $datos_actualizacion);
    
    if ($resultado) {
        $_SESSION['solicitud_editada'] = "Solicitud actualizada exitosamente";
        header('Location: lista.php');
        exit;
    } else {
        $_SESSION['error_edicion'] = "Error al actualizar la solicitud";
        header('Location: editar.php?id=' . $solicitud_id);
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error al actualizar solicitud: " . $e->getMessage());
    $_SESSION['error_edicion'] = "Error interno del servidor al actualizar la solicitud";
    header('Location: editar.php?id=' . $solicitud_id);
    exit;
}
?> 