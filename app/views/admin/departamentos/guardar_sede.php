<?php
if (!isset($_SESSION)) session_start();
// Incluir sistema de rutas dinámicas
require_once(__DIR__ . '/../../../config/paths.php');

// Incluir modelo usando rutas dinámicas
safe_require_once(model_path('Sede'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre'    => strtoupper(trim($_POST['nombre'])),
        'domicilio' => strtoupper(trim($_POST['domicilio'])),
        'numero'    => strtoupper(trim($_POST['numero'])),
        'interior'  => strtoupper(trim($_POST['interior'] ?? '')),
        'colonia'   => strtoupper(trim($_POST['colonia'])),
        'municipio' => strtoupper(trim($_POST['municipio'])),
        'estado'    => strtoupper(trim($_POST['estado'])),
        'cp'        => trim($_POST['cp']),
        'telefono'  => trim($_POST['telefono']),
    ];

    $sede = new Sede();

    if ($sede->existeNombre($data['nombre'])) {
        $_SESSION['error_guardado'] = "La sede '{$data['nombre']}' ya está registrada.";
    } else {
        $exito = $sede->crear($data);
        if ($exito) {
            $_SESSION['sede_guardada'] = "✅ La sede '{$data['nombre']}' fue registrada correctamente.";
        } else {
            $_SESSION['error_guardado'] = "❌ Error al guardar la sede. Intenta nuevamente.";
        }
    }

    header("Location: crear_sede.php");
    exit;
}
?>
