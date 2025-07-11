<?php
if (!isset($_SESSION)) session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/sistema_rh/config/conexion.php'); 
require_once($_SERVER['DOCUMENT_ROOT'] . '/sistema_rh/app/models/Usuario.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];
    $usuario = $_POST['usuario']; 
    $password = $_POST['password']; 
    $rol = $_POST['rol'];
    $nombre_completo = strtoupper($_POST['nombre_completo']);
    $departamento = strtoupper($_POST['departamento']);
    $sede = strtoupper($_POST['sede']);
    $numero_empleado = strtoupper($_POST['numero_empleado']);
    $correo = $_POST['correo'];

    // Si hay una nueva foto
    if (!empty($_FILES['fotografia']['name'])) {
        $nombre_foto = uniqid() . '_' . $_FILES['fotografia']['name'];
        $ruta_destino = '../../../public/uploads/' . $nombre_foto;
        move_uploaded_file($_FILES['fotografia']['tmp_name'], $ruta_destino);
    } else {
        $nombre_foto = $_POST['foto_actual'];
    }

    $usuarioObj = new Usuario();

    // Si se cambió la contraseña, se actualiza encriptada
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $usuarioObj->actualizarConPassword($id, $usuario, $password_hash, $rol, $nombre_completo, $departamento, $sede, $numero_empleado, $correo, $nombre_foto);
    } else {
        $usuarioObj->actualizarSinPassword($id, $usuario, $rol, $nombre_completo, $departamento, $sede, $numero_empleado, $correo, $nombre_foto);
    }

    header("Location: lista.php");
    exit;
}
?>
