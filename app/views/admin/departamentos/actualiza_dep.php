<?php
if (!isset($_SESSION)) session_start();
// Incluir sistema de rutas dinámicas
require_once(__DIR__ . '/../../../config/paths.php');

// Incluir modelo usando rutas dinámicas
safe_require_once(model_path('departamento'));

$departamento = new Departamento();

if (isset($_GET['verificar_nombre'], $_GET['sede_id'], $_GET['id'])) {
    $nombre = strtoupper(trim($_GET['verificar_nombre']));
    $sede_id = intval($_GET['sede_id']);
    $id = intval($_GET['id']);

    echo json_encode(['existe' => $departamento->existeNombreEnSedeEditando($nombre, $sede_id, $id)]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = intval($_POST['id'] ?? 0);
    $nombre      = strtoupper(trim($_POST['nombre'] ?? ''));
    $descripcion = strtoupper(trim($_POST['descripcion'] ?? ''));
    $sede_id     = intval($_POST['sede_id'] ?? 0);
    $responsable = !empty($_POST['responsable_id']) ? intval($_POST['responsable_id']) : null;

    if ($id <= 0 || $nombre === '' || $descripcion === '' || $sede_id <= 0) {
        $_SESSION['mensaje_error'] = "❌ Todos los campos obligatorios deben completarse.";
        header("Location: editar_dep.php?id=$id");
        exit;
    }

    if ($departamento->existeNombreEnSedeEditando($nombre, $sede_id, $id)) {
        $_SESSION['mensaje_error'] = "❌ Ya existe un departamento con ese nombre en esa sede.";
        header("Location: editar_dep.php?id=$id");
        exit;
    }

    $data = [
        'nombre'      => $nombre,
        'descripcion' => $descripcion,
        'sede_id'     => $sede_id,
        'responsable_id' => $responsable
    ];

    $exito = $departamento->actualizar($id, $data);

    $_SESSION['mensaje_exito'] = $exito
        ? "✅ Departamento actualizado correctamente."
        : "❌ Error al actualizar el departamento.";

    header("Location: lista_dep.php");
    exit;
}
