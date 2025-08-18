<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../includes/auth_helpers.php';

// Verificar que sea usuario gerente
verificarRol('gerente');

// Verificar contexto de trabajo (sede)
verificarContextoRol();

$titulo_pagina = "Panel de Gerente - Nexus RH";
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
        <h1 class="fw-bold text-primary">Panel de Gerente</h1>
        <p class="lead text-muted">Gestiona las operaciones de tu sede desde una perspectiva gerencial</p>
        
        <!-- Información del contexto -->
        <div class="alert alert-info d-inline-block">
            <h6 class="alert-heading mb-1">📍 Contexto de Trabajo</h6>
            <p class="mb-0">
                <strong>Sede:</strong> <?= htmlspecialchars($_SESSION['sede_nombre'] ?? 'No seleccionada') ?>
            </p>
        </div>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Tarjeta de Usuarios -->
        <div class="col-md-4">
            <div class="custom-card h-100 text-center">
                <i class="fas fa-users fa-3x mb-3 text-primary"></i>
                <h4>Usuarios</h4>
                <p class="text-muted">Consulta información de usuarios de la sede</p>
                <a href="usuarios/menu.php" class="btn btn-primary">Gestionar</a>
            </div>
        </div>

        <!-- Tarjeta de Solicitudes -->
        <div class="col-md-4">
            <div class="custom-card h-100 text-center">
                <i class="fas fa-envelope fa-3x mb-3 text-success"></i>
                <h4>Solicitudes</h4>
                <p class="text-muted">Revisa y aprueba solicitudes de personal</p>
                <a href="solicitudes/menu.php" class="btn btn-success">Revisar</a>
            </div>
        </div>

        <!-- Tarjeta de Evaluaciones -->
        <div class="col-md-4">
            <div class="custom-card h-100 text-center">
                <i class="fas fa-check-circle fa-3x mb-3 text-info"></i>
                <h4>Evaluaciones</h4>
                <p class="text-muted">Supervisa el proceso de evaluaciones</p>
                <a href="evaluaciones/menu.php" class="btn btn-info">Supervisar</a>
            </div>
        </div>

        <!-- Tarjeta de Resultados -->
        <div class="col-md-4">
            <div class="custom-card h-100 text-center">
                <i class="fas fa-chart-line fa-3x mb-3 text-warning"></i>
                <h4>Resultados</h4>
                <p class="text-muted">Analiza métricas y reportes de la sede</p>
                <a href="resultados/menu.php" class="btn btn-warning">Analizar</a>
            </div>
        </div>

        <!-- Tarjeta de Departamentos -->
        <div class="col-md-4">
            <div class="custom-card h-100 text-center">
                <i class="fas fa-sitemap fa-3x mb-3 text-secondary"></i>
                <h4>Departamentos</h4>
                <p class="text-muted">Consulta estructura organizacional</p>
                <a href="departamentos/menu.php" class="btn btn-secondary">Consultar</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
