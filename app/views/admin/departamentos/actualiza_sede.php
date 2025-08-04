<?php
if (!isset($_SESSION)) session_start();
// Incluir sistema de rutas dinámicas
require_once(__DIR__ . '/../../../config/paths.php');

// Incluir modelo usando rutas dinámicas
safe_require_once(model_path('Sede'));

$sede = new Sede();

// Verificación AJAX de nombre (evitar duplicados al editar)
if (isset($_GET['verificar_nombre'], $_GET['nombre'], $_GET['id'])) {
    $nombre = strtoupper(trim($_GET['nombre']));
    $id = intval($_GET['id']);

    $existe = $sede->existeNombreExcepto($nombre, $id);
    echo json_encode(['existe' => $existe]);
    exit;
}

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = intval($_POST['id']);
    $nombre     = strtoupper(trim($_POST['nombre']));
    $domicilio  = strtoupper(trim($_POST['domicilio']));
    $numero     = strtoupper(trim($_POST['numero']));
    $interior   = strtoupper(trim($_POST['interior'] ?? ''));
    $colonia    = strtoupper(trim($_POST['colonia']));
    $municipio  = strtoupper(trim($_POST['municipio']));
    $estado     = strtoupper(trim($_POST['estado']));
    $cp         = trim($_POST['cp']);
    $telefono   = trim($_POST['telefono']);

    // Validación de campos obligatorios
    if ($id <= 0 || $nombre === '' || $domicilio === '' || $numero === '' || $colonia === '' || $municipio === '' || $estado === '' || $cp === '' || $telefono === '') {
        $_SESSION['error_edicion'] = "❌ Todos los campos obligatorios deben completarse.";
        header("Location: editar_sede.php?id=$id");
        exit;
    }

    // Verificar duplicado
    if ($sede->existeNombreExcepto($nombre, $id)) {
        $_SESSION['error_edicion'] = "❌ Ya existe otra sede con ese nombre.";
        header("Location: editar_sede.php?id=$id");
        exit;
    }

    // Preparar datos para actualizar
    $data = [
        'nombre'     => $nombre,
        'domicilio'  => $domicilio,
        'numero'     => $numero,
        'interior'   => $interior,
        'colonia'    => $colonia,
        'municipio'  => $municipio,
        'estado'     => $estado,
        'cp'         => $cp,
        'telefono'   => $telefono
    ];

    $resultado = $sede->actualizar($id, $data);

    if ($resultado) {
        $_SESSION['sede_editada'] = "✅ La sede '$nombre' fue actualizada correctamente.";
    } else {
        $_SESSION['error_edicion'] = "❌ Error al actualizar la sede. Intenta nuevamente.";
    }

    header("Location: editar_sede.php?id=$id");
    exit;
}
