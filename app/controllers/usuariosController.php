<?php
require_once("../models/Usuario.php");

// Verificación duplicados
if (isset($_POST['validar_usuario'])) {
    $usuario = $_POST['usuario'] ?? '';
    $numero = $_POST['numero_empleado'] ?? '';

    $modelo = new Usuario();
    $existe = $modelo->existeUsuario($usuario, $numero);
    echo $existe ? "existe" : "ok";
    exit;
}

// Crear nuevo usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $contrasena = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];
    $nombre_completo = strtoupper($_POST['nombre_completo']);
    $departamento = $_POST['departamento'];
    $sede = $_POST['sede'];
    $numero_empleado = $_POST['numero_empleado'];
    $correo = $_POST['correo'];
    $estado = 'activo';

    $foto_nombre = '';
    if (!empty($_FILES['fotografia']['name'])) {
        $foto_nombre = uniqid() . '_' . $_FILES['fotografia']['name'];
        $destino = "../public/uploads/" . $foto_nombre;
        if (!is_dir("../public/uploads")) mkdir($destino, 0777, true);
        if (!move_uploaded_file($_FILES['fotografia']['tmp_name'], $destino)) {
            echo "error_foto";
            exit;
        }
    }

    $modelo = new Usuario();
    $resultado = $modelo->crear(
        $usuario, $contrasena, $rol, $nombre_completo,
        $departamento, $sede, $numero_empleado, $correo,
        $estado, $foto_nombre
    );

    echo $resultado ? "exito" : "error";
}
