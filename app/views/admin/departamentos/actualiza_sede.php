<?php
if (!isset($_SESSION)) session_start();
require_once('../../../models/Sede.php');

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

    $sede = new Sede();

    // Validar que no exista otra sede con el mismo nombre
    if ($sede->existeNombreExcepto($nombre, $id)) {
        $_SESSION['error_edicion'] = "El nombre '$nombre' ya está registrado en otra sede.";
        header("Location: editar_sede.php?id=$id");
        exit;
    }

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
?>
