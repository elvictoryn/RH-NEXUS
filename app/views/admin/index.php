
<?php
// Incluir sistema de autenticación
require_once __DIR__ . '/../../includes/auth_helpers.php';

// Verificar que sea administrador
soloAdmin();

// Para admin no se requiere verificación de contexto (acceso total)
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Administrador - Nexus RH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../../../public/css/estilo.css" rel="stylesheet">
</head>
<body>

<?php include_once __DIR__ . '/../shared/header.php'; ?>

<!-- Contenido principal -->
<div class="container py-5">
    <h2 class="text-center mb-5">Bienvenido Administrador</h2>
    <div class="row g-4">

        <!-- Tarjeta Usuarios -->
        <div class="col-md-4">
            <div class="custom-card h-100">
                <h5><i class="fas fa-users me-2"></i>Usuarios</h5>
                <p>Alta, edición y control de usuarios del sistema.</p>
                <a href="usuarios/menu.php" class="btn btn-primary w-100">Ir al módulo</a>
            </div>
        </div>

        <!-- Tarjeta Requerimientos -->
        <div class="col-md-4">
            <div class="custom-card h-100">
                <h5><i class="fas fa-sitemap me-2"></i>Departamentos y Sedes</h5>
                <p>Administracion de sedes y departamentos </p>
                <a href="departamentos/menu.php" class="btn btn-primary w-100">Ir al módulo</a>
            </div>
        </div>

        <!-- Tarjeta Solicitudes -->
        <div class="col-md-4">
            <div class="custom-card h-100">
                <h5><i class="fas fa-envelope me-2"></i>Solicitudes</h5>
                <p>Solicitudes de nuevos candidatos.</p>
                <a href="#" class="btn btn-secondary w-100 disabled">En desarrollo</a>
            </div>
        </div>

        <!-- Evaluaciones -->
        <div class="col-md-4">
            <div class="custom-card h-100">
                <h5><i class="fas fa-check-circle me-2"></i>Evaluaciones</h5>
                <p>Evaluar desempeño de candidatos.</p>
                <a href="#" class="btn btn-secondary w-100 disabled">En desarrollo</a>
            </div>
        </div>

        <!-- Resultados -->
        <div class="col-md-4">
            <div class="custom-card h-100">
                <h5><i class="fas fa-chart-line me-2"></i>Resultados</h5>
                <p>Estadísticas y desempeño del proceso.</p>
                <a href="#" class="btn btn-secondary w-100 disabled">En desarrollo</a>
            </div>
        </div>

         <!-- Resultados -->
        <div class="col-md-4">
            <div class="custom-card h-100">
                <h5><i class="fas fa-chart-line me-2"></i>Resultados</h5>
                <p>Estadísticas y desempeño del proceso.</p>
                <a href="#" class="btn btn-secondary w-100 disabled">En desarrollo</a>
            </div>
        </div>

        <!-- Módulo IA -->
        <div class="col-md-4">
            <div class="custom-card h-100">
                <h5><i class="fas fa-robot me-2"></i>Módulo IA</h5>
                <p>Análisis y resultados de inteligencia artificial.</p>
                <a href="#" class="btn btn-secondary w-100 disabled">En desarrollo</a>
            </div>
        </div>

    </div>
</div>

</body>
</html>
