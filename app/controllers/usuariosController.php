<?php
// controllers/usuariosController.php
require_once(__DIR__ . '/../models/Usuario.php');
require_once(__DIR__ . '/../models/Departamento.php');

$usuarioModel = new Usuario();
$departamentoModel = new Departamento();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'verificar_usuario') {
    header('Content-Type: application/json; charset=utf-8');
    $usuario = $_GET['usuario'] ?? '';
    echo json_encode(['existe' => $usuarioModel->existeUsuario($usuario)]);
    exit;
}
elseif ($action === 'verificar_num_empleado') {
    header('Content-Type: application/json; charset=utf-8');
    $num = $_GET['numero'] ?? '';
    echo json_encode(['existe' => $usuarioModel->existeNumeroEmpleado($num)]);
    exit;
}
elseif ($action === 'verificar_jefe') {
    header('Content-Type: application/json; charset=utf-8');
    $sede = $_GET['sede_id'] ?? 0;
    $dep = $_GET['departamento_id'] ?? 0;
    echo json_encode(['existe' => $usuarioModel->existeJefeEnDepartamento($sede, $dep)]);
    exit;
}
elseif ($action === 'verificar_gerente') {
    header('Content-Type: application/json; charset=utf-8');
    $sede = $_GET['sede_id'] ?? 0;
    echo json_encode(['existe' => $usuarioModel->existeGerenteEnSede($sede)]);
    exit;
}
elseif ($action === 'departamentos_por_sede') {
    header('Content-Type: application/json; charset=utf-8');
    $id = $_GET['id'] ?? 0;
    $departamentos = $departamentoModel->obtenerPorSede($id);
    echo json_encode($departamentos);
    exit;
}
elseif ($action === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION)) session_start();

    $data = $_POST;
    $data['fotografia'] = null;

    if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['fotografia']['name'], PATHINFO_EXTENSION);
        $fotoNombre = uniqid('foto_') . '.' . $ext;
        move_uploaded_file($_FILES['fotografia']['tmp_name'], __DIR__ . '/../public/img/usuarios/' . $fotoNombre);
        $data['fotografia'] = $fotoNombre;
    }

    $data['nombre_completo'] = mb_strtoupper(trim($data['nombre_completo']), 'UTF-8');
    $data['usuario'] = mb_strtoupper(trim($data['usuario']), 'UTF-8');
    $data['numero_empleado'] = mb_strtoupper(trim($data['numero_empleado']), 'UTF-8');
    $data['contrasena'] = password_hash($data['contrasena'], PASSWORD_DEFAULT);

    if ($usuarioModel->existeUsuario($data['usuario'])) {
        $_SESSION['error_guardado'] = "El nombre de usuario ya existe.";
        header("Location: ../views/admin/usuarios/crear_usuario.php");
        exit();
    }

    if ($usuarioModel->existeNumeroEmpleado($data['numero_empleado'])) {
        $_SESSION['error_guardado'] = "El número de empleado ya está registrado.";
        header("Location: ../views/admin/usuarios/crear_usuario.php");
        exit();
    }

    if ($data['rol'] === 'jefe_area' && $usuarioModel->existeJefeDepartamento($data['sede_id'], $data['departamento_id'])) {
        $_SESSION['error_guardado'] = "Ya existe un jefe de área para este departamento y sede.";
        header("Location: ../views/admin/usuarios/crear_usuario.php");
        exit();
    }

    if ($data['rol'] === 'gerente' && $usuarioModel->existeGerenteEnSede($data['sede_id'])) {
        $_SESSION['error_guardado'] = "Ya existe un gerente asignado a esta sede.";
        header("Location: ../views/admin/usuarios/crear_usuario.php");
        exit();
    }

    $exito = $usuarioModel->crear($data);

    if ($exito) {
        $_SESSION['usuario_guardado'] = "✅ Usuario creado exitosamente.";
        header("Location: ../views/admin/usuarios/lista_usuario.php");
    } else {
        $_SESSION['error_guardado'] = "❌ Error al guardar el usuario.";
        header("Location: ../views/admin/usuarios/crear_usuario.php");
    }
    exit;
}
