<?php
if (!isset($_SESSION)) session_start();
require_once('../../../models/departamento.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = strtoupper(trim($_POST['nombre'] ?? ''));
    $descripcion = strtoupper(trim($_POST['descripcion'] ?? ''));
    $sede_id     = intval($_POST['sede_id'] ?? 0);

    // Validación de campos requeridos
    if (empty($nombre) || empty($descripcion) || $sede_id <= 0) {
        $_SESSION['error_guardado'] = "❌ Todos los campos son obligatorios.";
        header("Location: ../views/admin/departamentos/crear_dep.php");
        exit;
    }

    $departamento = new Departamento();

    // Validar si ya existe el nombre en la misma sede
    if ($departamento->existeNombreEnSede($nombre, $sede_id)) {
        $_SESSION['error_guardado'] = "❌ Ya existe un departamento con ese nombre en la sede seleccionada.";
        header("Location: ../views/admin/departamentos/crear_dep.php");
        exit;
    }

    $data = [
        'nombre'      => $nombre,
        'descripcion' => $descripcion,
        'sede_id'     => $sede_id
    ];

    $exito = $departamento->crear($data);

    if ($exito) {
        $_SESSION['dep_creado'] = "✅ El departamento '{$data['nombre']}' fue registrado correctamente.";
    } else {
        $_SESSION['error_creacion'] = "❌ Ocurrió un error al guardar. Intenta nuevamente.";
    }

   header("Location: crear_dep.php");
    exit;
}
