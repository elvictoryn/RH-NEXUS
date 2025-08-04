<?php
if (!isset($_SESSION)) session_start();
// Incluir sistema de rutas dinámicas
require_once(__DIR__ . '/../../../config/paths.php');

// Incluir modelo usando rutas dinámicas
safe_require_once(model_path('Sede'));

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_eliminacion'] = "ID inválido.";
    header("Location: lista_sedes.php");
    exit;
}

$sede = new Sede();
$id = intval($_GET['id']);

if ($sede->desactivar($id)) {
    $_SESSION['sede_eliminada'] = "La sede ha sido eliminada correctamente.";
} else {
    $_SESSION['error_eliminacion'] = "Ocurrió un error al eliminar la sede.";
}

header("Location: lista_sedes.php");
exit;
?>
