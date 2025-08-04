<?php
// Incluir sistema de autenticación
require_once __DIR__ . '/../../includes/auth_helpers.php';

// Verificar que sea usuario de RH
verificarRol('rh');

// Verificar contexto de trabajo (sede y departamento)
verificarContextoRol();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Recursos Humanos - Nexus RH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../../../public/css/estilo.css" rel="stylesheet">
</head>
<body>

<?php include_once __DIR__ . '/../shared/header.php'; ?>

<!-- Contenido principal -->
<div class="container py-5">
    <h2 class="text-center mb-5">Bienvenido Recursos Humanos</h2>
    
    <!-- Información del contexto de trabajo -->
    <div class="alert alert-info mb-4">
        <h6><i class="fas fa-map-marker-alt me-2"></i>Contexto de Trabajo</h6>
        <p class="mb-1"><strong>Sede:</strong> <?= $_SESSION['sede_nombre'] ?? 'No seleccionada' ?></p>
        <p class="mb-0"><strong>Departamento:</strong> <?= $_SESSION['departamento_nombre'] ?? 'No seleccionado' ?></p>
    </div>
    
    <div class="row g-4">
        <!-- Tarjeta Usuarios -->
        <div class="col-md-4">
            <div class="custom-card h-100">
                <h5><i class="fas fa-users me-2"></i>Usuarios</h5>
                <p>Alta, edición y control de usuarios del sistema.</p>
                <a href="usuarios/menu.php" class="btn btn-primary w-100">Ir al módulo</a>
            </div>
        </div>

        <!-- Tarjeta Solicitudes -->
        <div class="col-md-4">
            <div class="custom-card h-100">
                <h5><i class="fas fa-envelope me-2"></i>Solicitudes</h5>
                <p>Procesar solicitudes de nuevos candidatos.</p>
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
    </div>
</div>

</body>
</html>
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chart-line me-2"></i>Resultados</h5>
                        <p class="card-text">Consultar estadísticas y desempeño del proceso de selección.</p>
                        <a href="#" class="btn btn-secondary w-100 disabled">En desarrollo</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
