<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario de RH
verificarRol('rh');

// Verificar contexto de trabajo (sede y departamento)
verificarContextoRol();

// Incluir modelo de solicitud
safe_require_once(model_path('Solicitud'));

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_creacion'] = "❌ Método de acceso no permitido.";
    header("Location: crear.php");
    exit;
}

// Validar campos requeridos
$campos_requeridos = ['titulo', 'descripcion', 'departamento_id', 'sede_id', 'perfil_puesto', 'cantidad', 'prioridad', 'modalidad'];
$campos_faltantes = [];

foreach ($campos_requeridos as $campo) {
    if (empty($_POST[$campo])) {
        $campos_faltantes[] = $campo;
    }
}

if (!empty($campos_faltantes)) {
    $_SESSION['error_creacion'] = "❌ Faltan campos requeridos: " . implode(', ', $campos_faltantes);
    header("Location: crear.php");
    exit;
}

// Validar formato de datos
$titulo = trim($_POST['titulo']);
$descripcion = trim($_POST['descripcion']);
$departamento_id = (int)$_POST['departamento_id'];
$sede_id = (int)$_POST['sede_id'];
$perfil_puesto = trim($_POST['perfil_puesto']);
$cantidad = (int)$_POST['cantidad'];
$prioridad = $_POST['prioridad'];
$modalidad = $_POST['modalidad'];

// Validar valores específicos
if (strlen($titulo) < 5 || strlen($titulo) > 255) {
    $_SESSION['error_creacion'] = "❌ El título debe tener entre 5 y 255 caracteres.";
    header("Location: crear.php");
    exit;
}

if (strlen($descripcion) < 10) {
    $_SESSION['error_creacion'] = "❌ La descripción debe tener al menos 10 caracteres.";
    header("Location: crear.php");
    exit;
}

if ($cantidad < 1 || $cantidad > 50) {
    $_SESSION['error_creacion'] = "❌ La cantidad de vacantes debe estar entre 1 y 50.";
    header("Location: crear.php");
    exit;
}

$prioridades_validas = ['alta', 'media', 'baja'];
if (!in_array($prioridad, $prioridades_validas)) {
    $_SESSION['error_creacion'] = "❌ Prioridad no válida.";
    header("Location: crear.php");
    exit;
}

$modalidades_validas = ['presencial', 'remoto', 'hibrido'];
if (!in_array($modalidad, $modalidades_validas)) {
    $_SESSION['error_creacion'] = "❌ Modalidad no válida.";
    header("Location: crear.php");
    exit;
}

// Validar salarios si se proporcionan
$salario_min = !empty($_POST['salario_min']) ? (float)$_POST['salario_min'] : null;
$salario_max = !empty($_POST['salario_max']) ? (float)$_POST['salario_max'] : null;

if ($salario_min !== null && $salario_max !== null && $salario_min > $salario_max) {
    $_SESSION['error_creacion'] = "❌ El salario mínimo no puede ser mayor que el salario máximo.";
    header("Location: crear.php");
    exit;
}

// Validar fecha límite si se proporciona
$fecha_limite_cobertura = !empty($_POST['fecha_limite_cobertura']) ? $_POST['fecha_limite_cobertura'] : null;

if ($fecha_limite_cobertura) {
    $fecha_actual = date('Y-m-d');
    if ($fecha_limite_cobertura < $fecha_actual) {
        $_SESSION['error_creacion'] = "❌ La fecha límite no puede ser anterior a hoy.";
        header("Location: crear.php");
        exit;
    }
}

// Procesar requisitos
$requisitos_json = null;
if (!empty($_POST['requisitos'])) {
    $requisitos = $_POST['requisitos'];
    $requisitos_procesados = [];
    
    // Procesar cada campo de requisitos
    if (!empty($requisitos['carrera'])) {
        $requisitos_procesados['carrera'] = trim($requisitos['carrera']);
    }
    
    if (!empty($requisitos['area_exp'])) {
        $requisitos_procesados['area_exp'] = trim($requisitos['area_exp']);
    }
    
    if (!empty($requisitos['nivel_educacion'])) {
        $requisitos_procesados['nivel_educacion'] = $requisitos['nivel_educacion'];
    }
    
    if (!empty($requisitos['experiencia_minima'])) {
        $experiencia = (int)$requisitos['experiencia_minima'];
        if ($experiencia >= 0 && $experiencia <= 50) {
            $requisitos_procesados['experiencia_minima'] = $experiencia;
        }
    }
    
    if (!empty($requisitos['habilidades'])) {
        $habilidades = array_map('trim', explode(',', $requisitos['habilidades']));
        $habilidades = array_filter($habilidades); // Remover elementos vacíos
        if (!empty($habilidades)) {
            $requisitos_procesados['habilidades'] = $habilidades;
        }
    }
    
    if (!empty($requisitos['observaciones'])) {
        $requisitos_procesados['observaciones'] = trim($requisitos['observaciones']);
    }
    
    // Solo crear JSON si hay requisitos válidos
    if (!empty($requisitos_procesados)) {
        $requisitos_json = $requisitos_procesados;
    }
}

// Crear instancia del modelo
$solicitud_model = new Solicitud();

// Preparar datos para la inserción
$datos_solicitud = [
    'titulo' => $titulo,
    'descripcion' => $descripcion,
    'departamento_id' => $departamento_id,
    'sede_id' => $sede_id,
    'perfil_puesto' => $perfil_puesto,
    'cantidad' => $cantidad,
    'prioridad' => $prioridad,
    'modalidad' => $modalidad,
    'salario_min' => $salario_min,
    'salario_max' => $salario_max,
    'fecha_limite_cobertura' => $fecha_limite_cobertura,
    'requisitos_json' => $requisitos_json,
    'solicitante_id' => $_SESSION['usuario_id'],
    'estado' => 'borrador'
];

// Intentar crear la solicitud
$resultado = $solicitud_model->crear($datos_solicitud);

if ($resultado) {
    $_SESSION['solicitud_creada'] = "✅ Solicitud creada exitosamente. Código: " . $solicitud_model->obtenerUltimoId();
    header("Location: lista.php");
    exit;
} else {
    $_SESSION['error_creacion'] = "❌ Error al crear la solicitud. Intente nuevamente.";
    header("Location: crear.php");
    exit;
}
?> 