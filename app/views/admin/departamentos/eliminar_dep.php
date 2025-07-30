<?php
if (!isset($_SESSION)) session_start();
require_once('../../../models/departamento.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_departamento'] = "ID inválido.";
    header("Location: lista_dep.php");
    exit;
}

$id = intval($_GET['id']);
$departamento = new Departamento();
$resultado = $departamento->eliminarLogico($id);

if ($resultado) {
    $_SESSION['departamento_guardado'] = "Departamento eliminado correctamente.";
} else {
    $_SESSION['error_departamento'] = "No se pudo eliminar el departamento.";
}

header("Location: lista_dep.php");
exit;