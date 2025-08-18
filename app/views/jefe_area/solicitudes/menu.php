<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario jefe de área
verificarRol('jefe_area');

// Verificar contexto de trabajo (sede y departamento)
verificarContextoRol();

$titulo_pagina = "Gestión de Solicitudes - Jefe de Área";

// Obtener información del contexto
$sede_nombre = $_SESSION['sede_nombre'];
$departamento_nombre = $_SESSION['departamento_nombre'];
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
                <h2 class="text-primary mb-0">📋 Gestión de Solicitudes</h2>
                <a href="../index.php" class="btn btn-outline-secondary">← Regresar al Dashboard</a>
            </div>

            <!-- Contexto de trabajo -->
            <div class="alert alert-info">
                <h6 class="alert-heading mb-1">📍 Contexto de Trabajo</h6>
                <p class="mb-0">
                    <strong>Sede:</strong> <?= htmlspecialchars($sede_nombre) ?> | 
                    <strong>Departamento:</strong> <?= htmlspecialchars($departamento_nombre) ?>
                </p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Crear Nueva Solicitud -->
        <div class="col-md-6 col-lg-4">
            <div class="card custom-card h-100 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-plus-circle fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title text-primary">Crear Solicitud</h5>
                    <p class="card-text text-muted">
                        Crea una nueva solicitud de personal para tu departamento
                    </p>
                    <a href="crear.php" class="btn btn-success w-100">
                        <i class="fas fa-plus me-2"></i>Nueva Solicitud
                    </a>
                </div>
            </div>
        </div>

        <!-- Ver Solicitudes -->
        <div class="col-md-6 col-lg-4">
            <div class="card custom-card h-100 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-list-alt fa-3x text-primary"></i>
                    </div>
                    <h5 class="card-title text-primary">Ver Solicitudes</h5>
                    <p class="card-text text-muted">
                        Revisa y gestiona todas las solicitudes de tu departamento
                    </p>
                    <a href="lista.php" class="btn btn-primary w-100">
                        <i class="fas fa-eye me-2"></i>Ver Solicitudes
                    </a>
                </div>
            </div>
        </div>

        <!-- Enviar a Gerencia -->
        <div class="col-md-6 col-lg-4">
            <div class="card custom-card h-100 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-paper-plane fa-3x text-info"></i>
                    </div>
                    <h5 class="card-title text-primary">Enviar a Gerencia</h5>
                    <p class="card-text text-muted">
                        Envía solicitudes aprobadas para revisión gerencial
                    </p>
                    <a href="lista.php" class="btn btn-info w-100">
                        <i class="fas fa-send me-2"></i>Gestionar Envíos
                    </a>
                </div>
            </div>
        </div>

        <!-- Editar Solicitudes -->
        <div class="col-md-6 col-lg-4">
            <div class="card custom-card h-100 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-edit fa-3x text-warning"></i>
                    </div>
                    <h5 class="card-title text-primary">Editar Solicitudes</h5>
                    <p class="card-text text-muted">
                        Modifica solicitudes en estado borrador
                    </p>
                    <a href="lista.php" class="btn btn-warning w-100">
                        <i class="fas fa-edit me-2"></i>Editar Solicitudes
                    </a>
                </div>
            </div>
        </div>

        <!-- Seguimiento -->
        <div class="col-md-6 col-lg-4">
            <div class="card custom-card h-100 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-chart-line fa-3x text-success"></i>
                    </div>
                    <h5 class="card-title text-primary">Seguimiento</h5>
                    <p class="card-text text-muted">
                        Monitorea el estado y progreso de tus solicitudes
                    </p>
                    <a href="lista.php" class="btn btn-success w-100">
                        <i class="fas fa-chart-line me-2"></i>Seguimiento
                    </a>
                </div>
            </div>
        </div>

        <!-- Historial -->
        <div class="col-md-6 col-lg-4">
            <div class="card custom-card h-100 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-history fa-3x text-secondary"></i>
                    </div>
                    <h5 class="card-title text-primary">Historial</h5>
                    <p class="card-text text-muted">
                        Consulta el historial completo de solicitudes
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
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Flujo de Solicitudes</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Flujo de Trabajo:</h6>
                            <ol class="text-muted">
                                <li><strong>Borrador:</strong> Creas la solicitud inicial</li>
                                <li><strong>Enviada a Gerencia:</strong> Envías para revisión</li>
                                <li><strong>Aceptada/Rechazada:</strong> Gerencia decide</li>
                                <li><strong>En Proceso RH:</strong> RH gestiona la contratación</li>
                                <li><strong>Cerrada:</strong> Solicitud finalizada</li>
                            </ol>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Permisos del Jefe de Área:</h6>
                            <ul class="text-muted">
                                <li>✅ Crear solicitudes</li>
                                <li>✅ Editar solicitudes en borrador</li>
                                <li>✅ Enviar a gerencia</li>
                                <li>✅ Ver estado de solicitudes</li>
                                <li>✅ Recibir notificaciones de cambios</li>
                            </ul>
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