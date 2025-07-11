<?php
if (!isset($_SESSION)) session_start();
require_once('../../../models/Departamento.php');

// ✅ Validación AJAX en tiempo real
if (isset($_GET['verificar']) && isset($_GET['nombre']) && isset($_GET['sede_id'])) {
    $dep = new Departamento();
    $nombre = strtoupper(trim($_GET['nombre']));
    $sede_id = intval($_GET['sede_id']);
    $existe = $dep->existeNombreEnSede($nombre, $sede_id);
    echo json_encode(['existe' => $existe]);
    exit;
}

// ✅ Guardado normal al enviar formulario
$nombre = strtoupper(trim($_POST['nombre'] ?? ''));
$descripcion = strtoupper(trim($_POST['descripcion'] ?? ''));
$sede_id = intval($_POST['sede_id'] ?? 0);

if ($nombre && $descripcion && $sede_id) {
    $departamento = new Departamento();

    // Verificamos si ya existe ese nombre en esa sede
    if ($departamento->existeNombreEnSede($nombre, $sede_id)) {
        $_SESSION['error_departamento'] = "❌ Ya existe un departamento con ese nombre en esa sede.";
    } else {
        $exito = $departamento->crear([
            'nombre'      => $nombre,
            'descripcion' => $descripcion,
            'sede_id'     => $sede_id
        ]);

        if ($exito) {
            $_SESSION['departamento_guardado'] = "✅ Departamento registrado correctamente.";
        } else {
            $_SESSION['error_departamento'] = "❌ Error al guardar el departamento.";
        }
    }
} else {
    $_SESSION['error_departamento'] = "⚠️ Todos los campos son obligatorios.";
}

header("Location: crear_dep.php");
exit;
