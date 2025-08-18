<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../includes/auth_helpers.php';

// Verificar que sea usuario administrador
verificarRol('admin');

$titulo_pagina = "Panel de Administrador - Nexus RH";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $titulo_pagina ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../../../public/css/estilo.css" rel="stylesheet">
</head>
<body>

<?php include_once __DIR__ . '/../shared/header.php'; ?>

<div class="container mt-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold text-primary">Panel de Administrador</h1>
        <p class="lead text-muted">Gestiona todo el sistema desde una perspectiva administrativa completa</p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Tarjeta de Usuarios -->
        <div class="col-md-4">
            <div class="custom-card h-100 text-center">
                <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                <h4>Usuarios</h4>
                <p class="text-muted">Gestiona usuarios, roles y permisos del sistema</p>
                <a href="usuarios/menu.php" class="btn btn-primary">Gestionar</a>
            </div>
        </div>

        <!-- Tarjeta de Departamentos y Sedes -->
        <div class="col-md-4">
            <div class="custom-card h-100 text-center">
                <i class="fas fa-sitemap fa-3x mb-3 text-success"></i>
                <h4>Departamentos y Sedes</h4>
                <p class="text-muted">Administra la estructura organizacional</p>
                <a href="departamentos/menu.php" class="btn btn-success">Administrar</a>
            </div>
        </div>

        <!-- Tarjeta de Solicitudes -->
        <div class="col-md-4">
            <div class="custom-card h-100 text-center">
                <i class="fas fa-envelope fa-3x mb-3 text-info"></i>
                <h4>Solicitudes</h4>
                <p class="text-muted">Supervisa todas las solicitudes del sistema</p>
                <a href="solicitudes/index.php" class="btn btn-info">Supervisar</a>
            </div>
        </div>

        <!-- Tarjeta de Evaluaciones -->
        <div class="col-md-4">
            <div class="custom-card h-100 text-center">
                <i class="fas fa-check-circle fa-3x mb-3 text-warning"></i>
                <h4>Evaluaciones</h4>
                <p class="text-muted">Configura y supervisa procesos de evaluación</p>
                <a href="evaluaciones/index.php" class="btn btn-warning">Configurar</a>
            </div>
        </div>

        <!-- Tarjeta de Resultados -->
        <div class="col-md-4">
            <div class="custom-card h-100 text-center">
                <i class="fas fa-chart-line fa-3x mb-3 text-secondary"></i>
                <h4>Resultados</h4>
                <p class="text-muted">Analiza métricas y reportes del sistema</p>
                <a href="resultados/index.php" class="btn btn-secondary">Analizar</a>
            </div>
        </div>

        <!-- Tarjeta de Módulo IA -->
        <div class="col-md-4">
            <div class="custom-card h-100 text-center">
                <i class="fas fa-brain fa-3x mb-3 text-danger"></i>
                <h4>Módulo IA</h4>
                <p class="text-muted">Gestiona funcionalidades de inteligencia artificial</p>
                <a href="ia/index.php" class="btn btn-danger">Gestionar</a>
            </div>
        </div>
    </div>

    <!-- Sección de estadísticas rápidas -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Estadísticas del Sistema</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-primary">0</h3>
                                <p class="text-muted mb-0">Usuarios Activos</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-success">0</h3>
                                <p class="text-muted mb-0">Departamentos</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-info">0</h3>
                                <p class="text-muted mb-0">Solicitudes</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning">0</h3>
                            <p class="text-muted mb-0">Evaluaciones</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
