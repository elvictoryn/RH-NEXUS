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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_creacion'] = "❌ Método no permitido.";
    header("Location: crear.php");
    exit;
}

// Obtener datos del formulario
$perfil_puesto = strtoupper(trim($_POST['perfil_puesto'] ?? ''));
$cantidad = intval($_POST['cantidad'] ?? 0);
$descripcion = trim($_POST['descripcion'] ?? '');
$prioridad = $_POST['prioridad'] ?? '';
$modalidad = $_POST['modalidad'] ?? '';
$salario_min = !empty($_POST['salario_min']) ? floatval($_POST['salario_min']) : null;
$salario_max = !empty($_POST['salario_max']) ? floatval($_POST['salario_max']) : null;
$fecha_limite_cobertura = !empty($_POST['fecha_limite_cobertura']) ? $_POST['fecha_limite_cobertura'] : null;

// Requisitos del puesto
$carrera = !empty($_POST['carrera']) ? strtoupper(trim($_POST['carrera'])) : null;
$area_exp = !empty($_POST['area_exp']) ? strtoupper(trim($_POST['area_exp'])) : null;
$nivel_educacion = !empty($_POST['nivel_educacion']) ? $_POST['nivel_educacion'] : null;
$experiencia_minima = !empty($_POST['experiencia_minima']) ? intval($_POST['experiencia_minima']) : null;
$habilidades = !empty($_POST['habilidades']) ? strtoupper(trim($_POST['habilidades'])) : null;
$observaciones = !empty($_POST['observaciones']) ? trim($_POST['observaciones']) : null;

// Validaciones básicas
if (empty($perfil_puesto) || $cantidad <= 0 || empty($descripcion) || 
    empty($prioridad) || empty($modalidad)) {
    $_SESSION['error_creacion'] = "❌ Todos los campos obligatorios deben completarse.";
    header("Location: crear.php");
    exit;
}

// Validar cantidad de vacantes
if ($cantidad < 1 || $cantidad > 10) {
    $_SESSION['error_creacion'] = "❌ La cantidad de vacantes debe estar entre 1 y 10.";
    header("Location: crear.php");
    exit;
}

// Validar prioridad
if (!in_array($prioridad, ['alta', 'media', 'baja'])) {
    $_SESSION['error_creacion'] = "❌ Prioridad no válida.";
    header("Location: crear.php");
    exit;
}

// Validar modalidad
if (!in_array($modalidad, ['presencial', 'remoto', 'hibrido'])) {
    $_SESSION['error_creacion'] = "❌ Modalidad no válida.";
    header("Location: crear.php");
    exit;
}

// Validar salarios
if ($salario_min && $salario_max && $salario_min > $salario_max) {
    $_SESSION['error_creacion'] = "❌ El salario mínimo no puede ser mayor al máximo.";
    header("Location: crear.php");
    exit;
}

// Validar fecha límite
if ($fecha_limite_cobertura) {
    $fecha_limite = new DateTime($fecha_limite_cobertura);
    $hoy = new DateTime();
    if ($fecha_limite <= $hoy) {
        $_SESSION['error_creacion'] = "❌ La fecha límite debe ser posterior a hoy.";
        header("Location: crear.php");
        exit;
    }
}

try {
    $solicitud_model = new Solicitud();
    
    // Preparar datos para crear la solicitud
    $data = [
        'titulo' => $perfil_puesto, // Usar perfil_puesto como título
        'descripcion' => $descripcion,
        'departamento_id' => $_SESSION['departamento_seleccionado'],
        'sede_id' => $_SESSION['sede_seleccionada'],
        'perfil_puesto' => $perfil_puesto,
        'cantidad' => $cantidad,
        'prioridad' => $prioridad,
        'modalidad' => $modalidad,
        'salario_min' => $salario_min,
        'salario_max' => $salario_max,
        'fecha_limite_cobertura' => $fecha_limite_cobertura,
        'requisitos_json' => [
            'carrera' => $carrera,
            'area_exp' => $area_exp,
            'nivel_educacion' => $nivel_educacion,
            'experiencia_minima' => $experiencia_minima,
            'habilidades' => $habilidades,
            'observaciones' => $observaciones
        ],
        'solicitante_id' => $_SESSION['usuario_id'] ?? 1, // ID del usuario actual
        'estado' => 'borrador' // Estado inicial
    ];
    
    // Crear la solicitud
    $exito = $solicitud_model->crear($data);
    
    if ($exito) {
        $_SESSION['solicitud_creada'] = "✅ La solicitud '$perfil_puesto' fue creada correctamente en estado 'borrador'.";
        header("Location: lista.php");
    } else {
        $_SESSION['error_creacion'] = "❌ Error al crear la solicitud. Intenta nuevamente.";
        header("Location: crear.php");
    }
    
} catch (Exception $e) {
    error_log("Error al crear solicitud: " . $e->getMessage());
    $_SESSION['error_creacion'] = "❌ Error interno del sistema. Contacta al administrador.";
    header("Location: crear.php");
}

exit;
?> 