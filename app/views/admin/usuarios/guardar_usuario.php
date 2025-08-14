<?php
if (!isset($_SESSION)) session_start();

require_once('../../../models/Usuario.php');

$usuarioModel = new Usuario();

// Validación de campos obligatorios
if (
    empty($_POST['nombre_completo']) || empty($_POST['usuario']) ||
    empty($_POST['contrasena']) || empty($_POST['rol']) ||
    empty($_POST['numero_empleado']) || empty($_POST['correo'])
) {
    $_SESSION['error_guardado'] = "Todos los campos obligatorios deben estar completos.";
    header("Location: crear_usuario.php");
    exit();
}

// Recibir y limpiar datos
$nombre_completo = mb_strtoupper(trim($_POST['nombre_completo']), 'UTF-8');
$usuario = mb_strtoupper(trim($_POST['usuario']), 'UTF-8');
$contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
$rol = $_POST['rol'];
$numero_empleado = mb_strtoupper(trim($_POST['numero_empleado']), 'UTF-8');
$correo = trim($_POST['correo']);
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : null;

$sede_id = $_POST['sede_id'] ?? null;
$departamento_id = $_POST['departamento_id'] ?? null;



// Procesar fotografía
$foto_nombre = null;
if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
    $foto_tmp = $_FILES['fotografia']['tmp_name'];
    $ext = pathinfo($_FILES['fotografia']['name'], PATHINFO_EXTENSION);
    $foto_nombre = uniqid('foto_') . '.' . $ext;
    $ruta_destino = '../../../public/img/usuarios/' . $foto_nombre;
    move_uploaded_file($foto_tmp, $ruta_destino);
}

// Guardar usuario
$exito = $usuarioModel->crear([
    'nombre_completo'   => $nombre_completo,
    'usuario'           => $usuario,
    'contrasena'        => $contrasena,
    'rol'               => $rol,
    'numero_empleado'   => $numero_empleado,
    'correo'            => $correo,
    'sede_id'           => $sede_id ?: null,
    'departamento_id'   => $departamento_id ?: null,
    'telefono'          => $telefono,
    'fotografia'        => $foto_nombre
]);

if ($exito) {
    $_SESSION['usuario_guardado'] = "✅ Usuario creado exitosamente.";
    header("Location: crear_usuario.php"); // Si quieres mandar a lista_usuario.php, cámbialo aquí
    exit();
} else {
    $_SESSION['error_guardado'] = "❌ Error al guardar el usuario. Intenta nuevamente.";
    header("Location: crear_usuario.php");
    exit();
}
