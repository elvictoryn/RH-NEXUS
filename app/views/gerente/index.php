<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'gerente') {
    header('Location: ../../../public/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel NOMBRE_DEL_ROL</title>
</head>
<body>
    <h2>Bienvenido NOMBRE_DEL_ROL: <?= $_SESSION['usuario'] ?></h2>
    <a href="../../../public/logout.php">Cerrar sesión</a>
</body>
</html>
