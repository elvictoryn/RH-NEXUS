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
    // Validar campos requeridos
    $campos_requeridos = [
        'nombre', 'curp', 'edad', 'genero', 'nivel_educacion',
        'area_experiencia', 'anos_experiencia', 'telefono', 
        'correo', 'direccion', 'sede_id', 'departamento_id'
    ];
    
    $campos_vacios = [];
    foreach ($campos_requeridos as $campo) {
        if (empty($_POST[$campo])) {
            $campos_vacios[] = $campo;
        }
    }
    
    if (!empty($campos_vacios)) {
        $_SESSION['error_creacion'] = "❌ Los siguientes campos son obligatorios: " . implode(', ', $campos_vacios);
        header("Location: crear.php");
        exit;
    }
    
    // Validar CURP
    $curp = strtoupper(trim($_POST['curp']));
    if (!preg_match('/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z][0-9]$/', $curp)) {
        $_SESSION['error_creacion'] = "❌ El formato de CURP no es válido.";
        header("Location: crear.php");
        exit;
    }
    
    // Validar edad
    $edad = (int)$_POST['edad'];
    if ($edad < 18 || $edad > 100) {
        $_SESSION['error_creacion'] = "❌ La edad debe estar entre 18 y 100 años.";
        header("Location: crear.php");
        exit;
    }
    
    // Validar años de experiencia
    $anos_experiencia = (int)$_POST['anos_experiencia'];
    if ($anos_experiencia < 0 || $anos_experiencia > 50) {
        $_SESSION['error_creacion'] = "❌ Los años de experiencia deben estar entre 0 y 50.";
        header("Location: crear.php");
        exit;
    }
    
    // Validar teléfono
    $telefono = trim($_POST['telefono']);
    if (!preg_match('/^[0-9]{10}$/', $telefono)) {
        $_SESSION['error_creacion'] = "❌ El teléfono debe contener exactamente 10 dígitos.";
        header("Location: crear.php");
        exit;
    }
    
    // Validar correo
    $correo = strtolower(trim($_POST['correo']));
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_creacion'] = "❌ El formato del correo electrónico no es válido.";
        header("Location: crear.php");
        exit;
    }
    
    // Crear instancia del modelo
    $candidato_model = new Candidato();
    
    // Verificar si ya existe un candidato con la misma CURP
    if ($candidato_model->existeCurp($curp)) {
        $_SESSION['error_creacion'] = "❌ Ya existe un candidato registrado con esa CURP.";
        header("Location: crear.php");
        exit;
    }
    
    // Preparar datos para guardar
    $data = [
        'nombre' => $_POST['nombre'],
        'curp' => $curp,
        'edad' => $edad,
        'genero' => $_POST['genero'],
        'nivel_educacion' => $_POST['nivel_educacion'],
        'carrera' => $_POST['carrera'] ?? null,
        'area_experiencia' => $_POST['area_experiencia'],
        'anos_experiencia' => $anos_experiencia,
        'companias_previas' => $_POST['companias_previas'] ?? null,
        'distancia_sede' => !empty($_POST['distancia_sede']) ? (float)$_POST['distancia_sede'] : null,
        'telefono' => $telefono,
        'correo' => $correo,
        'direccion' => $_POST['direccion'],
        'sede_id' => (int)$_POST['sede_id'],
        'departamento_id' => (int)$_POST['departamento_id']
    ];
    
    // Intentar guardar el candidato
    $resultado = $candidato_model->crear($data);
    
    if ($resultado) {
        $_SESSION['candidato_creado'] = "✅ El candidato '{$data['nombre']}' fue registrado correctamente.";
        header("Location: lista.php");
    } else {
        $_SESSION['error_creacion'] = "❌ Ocurrió un error al guardar el candidato. Intenta nuevamente.";
        header("Location: crear.php");
    }
    exit;
} else {
    // Si no es POST, redirigir al formulario
    header("Location: crear.php");
    exit;
}
?> 