<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario de RH
verificarRol('rh');

// Verificar contexto de trabajo (sede y departamento)
verificarContextoRol();

$titulo_pagina = "Gestión de Candidatos - Nexus RH";
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

<div class="container mt-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold text-primary">Gestión de Candidatos</h1>
        <p class="lead text-muted">Administra los candidatos para las posiciones disponibles en tu sede y departamento</p>
    </div>

    <!-- Información del contexto de trabajo -->
    <div class="alert alert-info mb-4">
        <h6><i class="fas fa-map-marker-alt me-2"></i>Contexto de Trabajo</h6>
        <p class="mb-1"><strong>Sede:</strong> <?= $_SESSION['sede_nombre'] ?? 'No seleccionada' ?></p>
        <p class="mb-0"><strong>Departamento:</strong> <?= $_SESSION['departamento_nombre'] ?? 'No seleccionado' ?></p>
    </div>

    <div class="row g-4 justify-content-center">

        <!-- Crear nuevo candidato -->
        <div class="col-md-5">
            <div class="card border-primary shadow h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h4 class="card-title text-primary fw-bold">👤 Nuevo Candidato</h4>
                        <p class="card-text">Registra un nuevo candidato con toda su información personal, académica y laboral.</p>
                    </div>
                    <div class="mt-3">
                        <a href="crear.php" class="btn btn-outline-primary w-100 mb-2">➕ Registrar Candidato</a>
                        <a href="lista.php" class="btn btn-primary w-100">📄 Ver Candidatos</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de candidatos -->
        <div class="col-md-5">
            <div class="card border-success shadow h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h4 class="card-title text-success fw-bold">📋 Candidatos Registrados</h4>
                        <p class="card-text">Consulta, edita y gestiona todos los candidatos registrados en el sistema.</p>
                    </div>
                    <div class="mt-3">
                        <a href="lista.php" class="btn btn-outline-success w-100 mb-2">📊 Ver Lista</a>
                        <a href="estadisticas.php" class="btn btn-success w-100">📈 Estadísticas</a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="mt-5 text-center">
        <a href="../index.php" class="btn btn-outline-dark">⬅ Volver al panel principal</a>
    </div>
</div>

</body>
</html> 