<?php
// Incluir sistema de autenticación
require_once __DIR__ . '/../../includes/auth_helpers.php';

// Verificar que sea gerente
verificarRol('gerente');

// Verificar contexto de trabajo (solo sede)
verificarContextoRol();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Gerente - Nexus RH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../../../public/css/estilo.css" rel="stylesheet">
</head>
<body>

<?php include_once __DIR__ . '/../shared/header.php'; ?>

<!-- Contenido principal -->
<div class="container py-5">
    <h2 class="text-center mb-5">Bienvenido Gerente</h2>
    
    <!-- Información del contexto de trabajo -->
    <div class="alert alert-info mb-4">
        <h6><i class="fas fa-building me-2"></i>Contexto de Trabajo</h6>
        <p class="mb-1"><strong>Sede:</strong> <?= $_SESSION['sede_nombre'] ?? 'No seleccionada' ?></p>
        <p class="mb-0"><small>Como gerente, supervisas toda la operación de esta sede.</small></p>
    </div>
    
    <div class="row g-4">
        <!-- Tarjeta Solicitudes -->
        <div class="col-md-4">
            <div class="custom-card h-100">
                <h5><i class="fas fa-envelope me-2"></i>Solicitudes</h5>
                <p>🔎 Aprobar solicitudes de personal desde los jefes de área.</p>
                <a href="#" class="btn btn-secondary w-100 disabled">En desarrollo</a>
            </div>
        </div>

        <!-- Tarjeta Usuarios -->
        <div class="col-md-4">
            <div class="custom-card h-100">
                <h5><i class="fas fa-users me-2"></i>Usuarios</h5>
                <p>Consultar información de usuarios de la sede.</p>
                <a href="#" class="btn btn-secondary w-100 disabled">En desarrollo</a>
            </div>
        </div>

        <!-- Tarjeta Departamentos -->
        <div class="col-md-4">
            <div class="custom-card h-100">
                <h5><i class="fas fa-sitemap me-2"></i>Departamentos</h5>
                <p>Supervisar departamentos de la sede.</p>
                <a href="#" class="btn btn-secondary w-100 disabled">En desarrollo</a>
            </div>
        </div>

        <!-- Evaluaciones -->
        <div class="col-md-6">
            <div class="custom-card h-100">
                <h5><i class="fas fa-check-circle me-2"></i>Evaluaciones</h5>
                <p>Revisar evaluaciones de candidatos.</p>
                <a href="#" class="btn btn-secondary w-100 disabled">En desarrollo</a>
            </div>
        </div>

        <!-- Resultados -->
        <div class="col-md-6">
            <div class="custom-card h-100">
                <h5><i class="fas fa-chart-line me-2"></i>Resultados</h5>
                <p>Estadísticas y desempeño del proceso.</p>
                <a href="#" class="btn btn-secondary w-100 disabled">En desarrollo</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
