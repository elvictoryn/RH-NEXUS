<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario gerente
verificarRol('gerente');

// Verificar contexto de trabajo (sede)
verificarContextoRol();

$titulo_pagina = "Revisión de Solicitudes - Gerente";

// Obtener información del contexto
$sede_nombre = $_SESSION['sede_nombre'];
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
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-primary mb-0">📋 Revisión de Solicitudes</h2>
                <a href="../index.php" class="btn btn-outline-secondary">← Regresar al Dashboard</a>
            </div>

            <!-- Contexto de trabajo -->
            <div class="alert alert-info">
                <h6 class="alert-heading mb-1">📍 Contexto de Trabajo</h6>
                <p class="mb-0">
                    <strong>Sede:</strong> <?= htmlspecialchars($sede_nombre) ?>
                </p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Revisar Solicitudes -->
        <div class="col-md-6 col-lg-4">
            <div class="card custom-card h-100 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-search fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title text-primary">Revisar Solicitudes</h5>
                    <p class="card-text text-muted">
                        Revisa todas las solicitudes enviadas a gerencia de tu sede
                    </p>
                    <a href="lista.php" class="btn btn-primary w-100">
                        <i class="fas fa-eye me-2"></i>Revisar Solicitudes
                    </a>
                </div>
            </div>
        </div>

        <!-- Aprobar Solicitudes -->
        <div class="col-md-6 col-lg-4">
            <div class="card custom-card h-100 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-check-circle fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title text-primary">Aprobar Solicitudes</h5>
                    <p class="card-text text-muted">
                        Aprueba solicitudes que cumplan con los requisitos
                    </p>
                    <a href="lista.php?estado=enviada a gerencia" class="btn btn-success w-100">
                        <i class="fas fa-check me-2"></i>Aprobar Solicitudes
                    </a>
                </div>
            </div>
        </div>

        <!-- Rechazar Solicitudes -->
        <div class="col-md-6 col-lg-4">
            <div class="card custom-card h-100 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-times-circle fa-3x text-danger"></i>
                    </div>
                    <h5 class="card-title text-primary">Rechazar Solicitudes</h5>
                    <p class="card-text text-muted">
                        Rechaza solicitudes que no cumplan con los criterios
                    </p>
                    <a href="lista.php?estado=enviada a gerencia" class="btn btn-danger w-100">
                        <i class="fas fa-times me-2"></i>Rechazar Solicitudes
                    </a>
                </div>
            </div>
        </div>

        <!-- Posponer Solicitudes -->
        <div class="col-md-6 col-lg-4">
            <div class="card custom-card h-100 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-pause-circle fa-3x text-warning"></i>
                    </div>
                    <h5 class="card-title text-primary">Posponer Solicitudes</h5>
                    <p class="card-text text-muted">
                        Posponer solicitudes para revisión posterior
                    </p>
                    <a href="lista.php?estado=enviada a gerencia" class="btn btn-warning w-100">
                        <i class="fas fa-pause me-2"></i>Posponer Solicitudes
                    </a>
                </div>
            </div>
        </div>

        <!-- Solicitar Cambios -->
        <div class="col-md-6 col-lg-4">
            <div class="card custom-card h-100 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-edit fa-3x text-info"></i>
                    </div>
                    <h5 class="card-title text-primary">Solicitar Cambios</h5>
                    <p class="card-text text-muted">
                        Solicita modificaciones a los jefes de área
                    </p>
                    <a href="lista.php?estado=enviada a gerencia" class="btn btn-info w-100">
                        <i class="fas fa-edit me-2"></i>Solicitar Cambios
                    </a>
                </div>
            </div>
        </div>

        <!-- Historial de Decisiones -->
        <div class="col-md-6 col-lg-4">
            <div class="card custom-card h-100 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-history fa-3x text-secondary"></i>
                    </div>
                    <h5 class="card-title text-primary">Historial</h5>
                    <p class="card-text text-muted">
                        Consulta el historial de decisiones tomadas
                    </p>
                    <a href="lista.php" class="btn btn-secondary w-100">
                        <i class="fas fa-history me-2"></i>Ver Historial
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Proceso de Revisión</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Estados de Solicitudes:</h6>
                            <ul class="text-muted">
                                <li><span class="badge bg-warning">Enviada a Gerencia</span> - Pendiente de revisión</li>
                                <li><span class="badge bg-success">Aceptada por Gerencia</span> - Aprobada para RH</li>
                                <li><span class="badge bg-danger">Rechazada</span> - No aprobada</li>
                                <li><span class="badge bg-secondary">Pospuesta</span> - En espera</li>
                                <li><span class="badge bg-warning">Solicita Cambios</span> - Requiere modificaciones</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Permisos del Gerente:</h6>
                            <ul class="text-muted">
                                <li>✅ Ver todas las solicitudes de la sede</li>
                                <li>✅ Aprobar solicitudes válidas</li>
                                <li>✅ Rechazar solicitudes con motivo</li>
                                <li>✅ Posponer para revisión posterior</li>
                                <li>✅ Solicitar cambios a jefes de área</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Estadísticas Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-primary mb-1">0</h4>
                                <small class="text-muted">Enviadas a Gerencia</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-success mb-1">0</h4>
                                <small class="text-muted">Aprobadas Hoy</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h4 class="text-danger mb-1">0</h4>
                                <small class="text-muted">Rechazadas Hoy</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-warning mb-1">0</h4>
                            <small class="text-muted">Pospuestas</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html> 