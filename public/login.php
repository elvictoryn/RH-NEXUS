<?php
session_start();
require_once '../config/conexion.php';

$pdo = Conexion::getConexion(); // ← Esta línea es clave

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_POST['usuario'];
    $contrasena = $_POST['contrasena'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($contrasena, $user['contrasena'])) {
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['rol'] = $user['rol'];

        // Redirección según el rol
        switch ($user['rol']) {
            case 'admin':
                header("Location: ../app/views/admin/index.php");
                break;
            case 'rh':
                header("Location: ../app/views/admin/rh/index.php");
                break;
            case 'gerente':
                header("Location: ../app/views/admin/gerente/index.php");
                break;
            case 'jefe_area':
                header("Location: ../app/views/admin/jefe_area/index.php");
                break;
            default:
                echo "Rol no válido.";
        }
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - Nexus RH</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100 shadow-lg bg-white rounded overflow-hidden" style="max-width: 1100px;">
        <!-- Columna informativa -->
        <div class="col-md-6 p-5 text-white" style="background-color: #1e3a8a;">
            <h1 class="fw-bold mb-4">Nexus RH</h1>
            <p class="lead">
                Plataforma especializada para la gestión de procesos de contratación, entrevistas, evaluaciones y resultados.
            </p>
            <p class="mt-3">
                Sistema de uso exclusivo para personal autorizado: RH, gerentes, jefes de área y administradores.
            </p>
        </div>

        <!-- Columna del login -->
        <div class="col-md-6 p-5">
            <h2 class="text-center mb-4" style="color: #1e3a8a;">Iniciar Sesión</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="usuario" class="form-label">Usuario</label>
                    <input type="text" class="form-control" id="usuario" name="usuario" required>
                </div>

                <div class="mb-3">
                    <label for="contrasena" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Ingresar</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>