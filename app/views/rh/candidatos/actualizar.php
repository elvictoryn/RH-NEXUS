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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_edicion'] = "Método no permitido.";
    header("Location: lista.php");
    exit;
}

// Obtener datos del formulario
$id = intval($_POST['id'] ?? 0);
$nombre = strtoupper(trim($_POST['nombre'] ?? ''));
$curp = strtoupper(trim($_POST['curp'] ?? ''));
$edad = intval($_POST['edad'] ?? 0);
$genero = $_POST['genero'] ?? '';
$telefono = trim($_POST['telefono'] ?? '');
$correo = strtolower(trim($_POST['correo'] ?? ''));
$distancia_sede = !empty($_POST['distancia_sede']) ? floatval($_POST['distancia_sede']) : null;
$direccion = strtoupper(trim($_POST['direccion'] ?? ''));
$nivel_educacion = strtoupper(trim($_POST['nivel_educacion'] ?? ''));
$carrera = !empty($_POST['carrera']) ? strtoupper(trim($_POST['carrera'])) : null;
$area_experiencia = strtoupper(trim($_POST['area_experiencia'] ?? ''));
$anos_experiencia = intval($_POST['anos_experiencia'] ?? 0);
$companias_previas = !empty($_POST['companias_previas']) ? strtoupper(trim($_POST['companias_previas'])) : null;
$sede_id = intval($_POST['sede_id'] ?? 0);
$departamento_id = intval($_POST['departamento_id'] ?? 0);
$estado = $_POST['estado'] ?? 'activo';

// Validaciones básicas
if ($id <= 0 || empty($nombre) || empty($curp) || $edad < 18 || $edad > 100 || 
    empty($genero) || empty($telefono) || empty($correo) || empty($direccion) || 
    empty($nivel_educacion) || empty($area_experiencia) || $anos_experiencia < 0 || 
    $sede_id <= 0 || $departamento_id <= 0) {
    $_SESSION['error_edicion'] = "❌ Todos los campos obligatorios deben completarse correctamente.";
    header("Location: editar.php?id=$id");
    exit;
}

// Validar formato de teléfono
if (!preg_match('/^[0-9]{10}$/', $telefono)) {
    $_SESSION['error_edicion'] = "❌ El teléfono debe contener exactamente 10 dígitos.";
    header("Location: editar.php?id=$id");
    exit;
}

// Validar formato de email
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_edicion'] = "❌ El formato del correo electrónico no es válido.";
    header("Location: editar.php?id=$id");
    exit;
}

// Validar formato de CURP
if (!preg_match('/^[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z][0-9]$/', $curp)) {
    $_SESSION['error_edicion'] = "❌ El formato de la CURP no es válido.";
    header("Location: editar.php?id=$id");
    exit;
}

try {
    $candidato_model = new Candidato();
    
    // Verificar si ya existe otro candidato con la misma CURP (excluyendo el actual)
    if ($candidato_model->existeCurp($curp, $id)) {
        $_SESSION['error_edicion'] = "❌ Ya existe otro candidato con esa CURP.";
        header("Location: editar.php?id=$id");
        exit;
    }
    
    // Preparar datos para actualizar
    $data = [
        'nombre' => $nombre,
        'curp' => $curp,
        'edad' => $edad,
        'genero' => $genero,
        'telefono' => $telefono,
        'correo' => $correo,
        'distancia_sede' => $distancia_sede,
        'direccion' => $direccion,
        'nivel_educacion' => $nivel_educacion,
        'carrera' => $carrera,
        'area_experiencia' => $area_experiencia,
        'anos_experiencia' => $anos_experiencia,
        'companias_previas' => $companias_previas,
        'sede_id' => $sede_id,
        'departamento_id' => $departamento_id,
        'estado' => $estado
    ];
    
    // Actualizar candidato
    $exito = $candidato_model->actualizar($id, $data);
    
    if ($exito) {
        $_SESSION['candidato_editado'] = "✅ El candidato '$nombre' fue actualizado correctamente.";
        header("Location: lista.php");
    } else {
        $_SESSION['error_edicion'] = "❌ Error al actualizar el candidato. Intenta nuevamente.";
        header("Location: editar.php?id=$id");
    }
    
} catch (Exception $e) {
    error_log("Error al actualizar candidato: " . $e->getMessage());
    $_SESSION['error_edicion'] = "❌ Error interno del sistema. Contacta al administrador.";
    header("Location: editar.php?id=$id");
}

exit;
?> 