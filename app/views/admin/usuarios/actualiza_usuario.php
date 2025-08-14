<?php
require_once(__DIR__ . '/../../../models/Usuario.php');
require_once(__DIR__ . '/../../../models/Departamento.php');
require_once(__DIR__ . '/../../../models/Sede.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $usuario = trim($_POST['usuario'] ?? '');
    $nombre_completo = mb_strtoupper(trim($_POST['nombre_completo'] ?? ''));
    $numero_empleado = trim($_POST['numero_empleado'] ?? '');
    $rol = $_POST['rol'] ?? '';
    $sede_id = $_POST['sede_id'] ?? null;
    $departamento_id = $_POST['departamento_id'] ?? null;
    $correo = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $estado = $_POST['estado'] ?? 1;

    // Instanciar modelos
    $modeloUsuario = new Usuario();
    $modeloDep = new Departamento();
    $modeloSede = new Sede();

    // Verificación básica
    if (!$id || !$usuario || !$nombre_completo || !$numero_empleado || !$rol || !$sede_id || !$departamento_id) {
        $_SESSION['error_edicion'] = "Todos los campos marcados son obligatorios.";
        header("Location: editar_usuario.php?id=$id");
        exit;
    }

    // Verificar duplicado de usuario (excepto el propio usuario)
    if ($modeloUsuario->existeUsuario($usuario, $id)) {
        $_SESSION['error_edicion'] = "El nombre de usuario ya está registrado en otro usuario.";
        header("Location: editar_usuario.php?id=$id");
        exit;
    }

    // Verificar duplicado de número de empleado (excepto el propio usuario)
    if ($modeloUsuario->existeNumeroEmpleado($numero_empleado, $id)) {
        $_SESSION['error_edicion'] = "El número de empleado ya está registrado en otro usuario.";
        header("Location: editar_usuario.php?id=$id");
        exit;
    }

    // Verificar jefe de área duplicado
    if ($rol === 'jefe_area' && $modeloUsuario->existeJefeEnDepartamento($sede_id, $departamento_id, $id)) {
        $_SESSION['error_edicion'] = "Ya existe un jefe de área registrado en ese departamento y sede.";
        header("Location: editar_usuario.php?id=$id");
        exit;
    }

    // Verificar gerente duplicado
    if ($rol === 'gerente' && $modeloUsuario->existeGerenteEnSede($sede_id, $id)) {
        $_SESSION['error_edicion'] = "Ya existe un gerente asignado a esa sede.";
        header("Location: editar_usuario.php?id=$id");
        exit;
    }

    // Preparar datos a actualizar
    $datos = [
        'usuario' => $usuario,
        'nombre_completo' => $nombre_completo,
        'numero_empleado' => $numero_empleado,
        'rol' => $rol,
        'sede_id' => $sede_id,
        'departamento_id' => $departamento_id,
        'correo' => $correo,
        'telefono' => $telefono,
        'estado' => $estado
    ];

    $resultado = $modeloUsuario->actualizar($id, $datos);

    if ($resultado) {
        $_SESSION['usuario_editado'] = true;
        header("Location: lista_usuario.php");
        exit;
    } else {
        $_SESSION['error_edicion'] = "Hubo un error al actualizar el usuario.";
        header("Location: editar_usuario.php?id=$id");
        exit;
    }
} else {
    header("Location: lista_usuario.php");
    exit;
}






