<<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../../../public/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Administrador - Nexus RH</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link href="/css/estilo.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <div class="bg-dark text-white p-3" style="min-width: 220px; min-height: 100vh;">
        <h4 class="fw-bold">Nexus RH</h4>
        <hr>
        <ul class="nav flex-column">
            <li class="nav-item mb-2"><a class="nav-link text-white" href="index.php"><i class="fas fa-home me-2"></i>Inicio</a></li>
            <li class="nav-item mb-2"><a class="nav-link text-white" href="usuarios/index.php"><i class="fas fa-users me-2"></i>Usuarios</a></li>
            <li class="nav-item mb-2"><a class="nav-link text-white" href="usuarios/menu.php"><i class="fas fa-sitemap me-2"></i>Requerimientos</a></li>
            <li class="nav-item mb-2"><a class="nav-link text-white disabled" href="#"><i class="fas fa-envelope me-2"></i>Solicitudes</a></li>
            <li class="nav-item mb-2"><a class="nav-link text-white disabled" href="#"><i class="fas fa-check-circle me-2"></i>Evaluaciones</a></li>
            <li class="nav-item mb-2"><a class="nav-link text-white disabled" href="#"><i class="fas fa-chart-line me-2"></i>Resultados</a></li>
        </ul>
        <hr>
        <div class="text-white-50 small">Usuario: <?= $_SESSION['usuario'] ?></div>
        <a href="../../../public/logout.php" class="btn btn-sm btn-outline-light mt-2">Cerrar sesión</a>
    </div>

    <!-- Contenido principal -->
    <div class="flex-grow-1 p-4">
        <h2 class="mb-4">Bienvenido Administrador</h2>
        <div class="row g-4">

            <!-- Gestión de usuarios -->
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-users me-2"></i>Usuarios</h5>
                        <p class="card-text">Alta, edición y control de usuarios del sistema.</p>
                        <a href="usuarios/index.php" class="btn btn-primary w-100">Ir al módulo</a>
                    </div>
                </div>
            </div>

            <!-- Requerimientos de puesto -->
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-sitemap me-2"></i>Requerimientos</h5>
                        <p class="card-text">Establecer perfiles de puesto y necesidades por área.</p>
                        <a href="usuarios/menu.php" class="btn btn-primary w-100">Ir al módulo</a>
                    </div>
                </div>
            </div>

            <!-- Solicitudes de candidatos -->
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-envelope me-2"></i>Solicitudes</h5>
                        <p class="card-text">Capturar solicitudes para nuevos candidatos.</p>
                        <a href="#" class="btn btn-secondary w-100 disabled">En desarrollo</a>
                    </div>
                </div>
            </div>

            <!-- Evaluaciones -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-check-circle me-2"></i>Evaluaciones</h5>
                        <p class="card-text">Evaluar y registrar el desempeño de los candidatos entrevistados.</p>
                        <a href="#" class="btn btn-secondary w-100 disabled">En desarrollo</a>
                    </div>
                </div>
            </div>

            <!-- Resultados -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
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
