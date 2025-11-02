<?php
if (!isset($_SESSION)) session_start();
require_once('../../../models/Sede.php');

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
