<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

switch ($_SESSION['rol']) {
    case 'admin':
        header('Location: ../app/views/admin/index.php');
        break;
    case 'rh':
        header('Location: ../app/views/rh/index.php');
        break;
    case 'gerente':
        header('Location: ../app/views/gerente/index.php');
        break;
    case 'jefe_area':
        header('Location: ../app/views/jefe_area/index.php');
        break;
    default:
        echo "Rol desconocido.";
}
?>
