<?php
if (!isset($_SESSION)) session_start();
require_once('../../../models/Usuario.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_eliminacion'] = "ID inválido.";
    header("Location: lista_usuario.php");
    exit;
}

$id = intval($_GET['id']);
$usuario = new Usuario();

$exito = $usuario->eliminarLogico($id);

if ($exito) {
    $_SESSION['usuario_eliminado'] = "✅ Usuario dado de baja correctamente.";
} else {
    $_SESSION['error_eliminacion'] = "❌ Error al intentar dar de baja al usuario.";
}

header("Location: lista_usuario.php");
exit;