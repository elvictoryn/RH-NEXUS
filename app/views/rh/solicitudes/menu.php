<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario de RH
verificarRol('rh');

// Verificar contexto de trabajo (sede y departamento)
verificarContextoRol();

$titulo_pagina = "Gestión de Solicitudes - Nexus RH";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $titulo_pagina ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../../../../public/css/estilo.css" rel="stylesheet">
</head>
<body>

<?php include_once __DIR__ . '/../../shared/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">📋 Gestión de Solicitudes</h3>
                        <a href="../index.php" class="btn btn-outline-light">← Regresar al Panel</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Crear Solicitud -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card custom-card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-plus-circle fa-3x text-success"></i>
                                    </div>
                                    <h5 class="card-title">Crear Solicitud</h5>
                                    <p class="card-text">Registra una nueva solicitud de personal para tu departamento.</p>
                                    <a href="crear.php" class="btn btn-primary w-100">Crear Nueva</a>
                                </div>
                            </div>
                        </div>

                        <!-- Listar Solicitudes -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card custom-card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-list fa-3x text-info"></i>
                                    </div>
                                    <h5 class="card-title">Ver Solicitudes</h5>
                                    <p class="card-text">Consulta y gestiona <strong>todas las solicitudes del sistema</strong> desde cualquier sede y departamento.</p>
                                    <a href="lista.php" class="btn btn-primary w-100">Ver Lista</a>
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card custom-card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-chart-bar fa-3x text-warning"></i>
                                    </div>
                                    <h5 class="card-title">Estadísticas</h5>
                                    <p class="card-text">Visualiza estadísticas y reportes de solicitudes.</p>
                                    <a href="estadisticas.php" class="btn btn-primary w-100">Ver Estadísticas</a>
                                </div>
                            </div>
                        </div>

                        <!-- Solicitudes Pendientes -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card custom-card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-clock fa-3x text-warning"></i>
                                    </div>
                                    <h5 class="card-title">Pendientes</h5>
                                    <p class="card-text">Solicitudes en espera de aprobación o en proceso.</p>
                                    <a href="lista.php?estado=enviada a gerencia" class="btn btn-primary w-100">Ver Pendientes</a>
                                </div>
                            </div>
                        </div>

                        <!-- Solicitudes Aprobadas -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card custom-card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-check-circle fa-3x text-success"></i>
                                    </div>
                                    <h5 class="card-title">Aprobadas</h5>
                                    <p class="card-text">Solicitudes aprobadas y en proceso de reclutamiento.</p>
                                    <a href="lista.php?estado=aceptada gerencia" class="btn btn-primary w-100">Ver Aprobadas</a>
                                </div>
                            </div>
                        </div>

                        <!-- Solicitudes Cerradas -->
                        <div class="col-md-6 col-lg-4">
                            <div class="card custom-card h-100">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-archive fa-3x text-secondary"></i>
                                    </div>
                                    <h5 class="card-title">Cerradas</h5>
                                    <p class="card-text">Solicitudes finalizadas o canceladas.</p>
                                    <a href="lista.php?estado=cerrada" class="btn btn-primary w-100">Ver Cerradas</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Contexto -->
                    <div class="mt-4">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">📍 Contexto de Trabajo</h6>
                            <p class="mb-0">
                                <strong>Sede:</strong> <?= htmlspecialchars($_SESSION['sede_nombre'] ?? 'No seleccionada') ?> | 
                                <strong>Departamento:</strong> <?= htmlspecialchars($_SESSION['departamento_nombre'] ?? 'No seleccionado') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html> 