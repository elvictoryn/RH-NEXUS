<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario de RH
verificarRol('rh');

// Verificar contexto de trabajo (sede y departamento)
verificarContextoRol();

// Incluir modelo de candidato
safe_require_once(model_path('Candidato'));

$titulo_pagina = "Estadísticas de Candidatos - Nexus RH";

// Obtener estadísticas
$candidato_model = new Candidato();
$estadisticas = $candidato_model->obtenerEstadisticas();

// Obtener candidatos del contexto actual
$candidatos = $candidato_model->obtenerPorSedeDepartamento(
    $_SESSION['sede_seleccionada'], 
    $_SESSION['departamento_seleccionado']
);

// Calcular estadísticas adicionales
$total_candidatos = count($candidatos);
$por_genero = [
    'Masculino' => 0,
    'Femenino' => 0,
    'Otro' => 0
];

$por_educacion = [];
$por_experiencia = [];

foreach ($candidatos as $candidato) {
    // Contar por género
    $genero = $candidato['genero'] ?? 'Otro';
    if (isset($por_genero[$genero])) {
        $por_genero[$genero]++;
    }
    
    // Contar por nivel de educación
    $educacion = $candidato['nivel_educacion'] ?? 'No especificado';
    $por_educacion[$educacion] = ($por_educacion[$educacion] ?? 0) + 1;
    
    // Contar por años de experiencia
    $exp = $candidato['anos_experiencia'] ?? 0;
    if ($exp <= 2) {
        $rango = '0-2 años';
    } elseif ($exp <= 5) {
        $rango = '3-5 años';
    } elseif ($exp <= 10) {
        $rango = '6-10 años';
    } else {
        $rango = 'Más de 10 años';
    }
    $por_experiencia[$rango] = ($por_experiencia[$rango] ?? 0) + 1;
}

$mensaje_exito = $_SESSION['estadisticas_actualizadas'] ?? null;
unset($_SESSION['estadisticas_actualizadas']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $titulo_pagina ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../../../../public/css/estilo.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include_once __DIR__ . '/../../shared/header.php'; ?>

<div class="container mt-4">
    <div class="card shadow p-4 bg-light">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary mb-0">📊 Estadísticas de Candidatos</h2>
            <div>
                <a href="menu.php" class="btn btn-outline-secondary me-2">← Regresar</a>
                <a href="lista.php" class="btn btn-outline-info">📋 Ver Candidatos</a>
            </div>
        </div>

        <!-- Información del contexto de trabajo -->
        <div class="alert alert-info mb-4">
            <h6><i class="fas fa-map-marker-alt me-2"></i>Contexto de Trabajo</h6>
            <p class="mb-1"><strong>Sede:</strong> <?= $_SESSION['sede_nombre'] ?? 'No seleccionada' ?></p>
            <p class="mb-0"><strong>Departamento:</strong> <?= $_SESSION['departamento_nombre'] ?? 'No seleccionado' ?></p>
        </div>

        <?php if ($mensaje_exito): ?>
            <div class="alert alert-success text-center fw-bold"><?= htmlspecialchars($mensaje_exito) ?></div>
        <?php endif; ?>

        <!-- Resumen general -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white text-center">
                    <div class="card-body">
                        <h3 class="card-title"><?= $total_candidatos ?></h3>
                        <p class="card-text">Total Candidatos</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-success text-white text-center">
                    <div class="card-body">
                        <h3 class="card-title"><?= $por_genero['Masculino'] ?></h3>
                        <p class="card-text">Hombres</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-info text-white text-center">
                    <div class="card-body">
                        <h3 class="card-title"><?= $por_genero['Femenino'] ?></h3>
                        <p class="card-text">Mujeres</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-warning text-white text-center">
                    <div class="card-body">
                        <h3 class="card-title"><?= $por_genero['Otro'] ?></h3>
                        <p class="card-text">Otros</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row g-4">
            <!-- Gráfico de distribución por género -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Distribución por Género</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="generoChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfico de distribución por educación -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Distribución por Nivel de Educación</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="educacionChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfico de distribución por experiencia -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Distribución por Años de Experiencia</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="experienciaChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tabla de estadísticas detalladas -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Estadísticas Detalladas</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Criterio</th>
                                        <th>Cantidad</th>
                                        <th>Porcentaje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($por_educacion as $educacion => $cantidad): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($educacion) ?></td>
                                            <td><?= $cantidad ?></td>
                                            <td><?= $total_candidatos > 0 ? round(($cantidad / $total_candidatos) * 100, 1) : 0 ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón para actualizar estadísticas -->
        <div class="mt-4 text-center">
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt me-2"></i>Actualizar Estadísticas
            </button>
        </div>
    </div>
</div>

<script>
// Gráfico de distribución por género
const generoCtx = document.getElementById('generoChart').getContext('2d');
new Chart(generoCtx, {
    type: 'doughnut',
    data: {
        labels: ['Masculino', 'Femenino', 'Otro'],
        datasets: [{
            data: [<?= $por_genero['Masculino'] ?>, <?= $por_genero['Femenino'] ?>, <?= $por_genero['Otro'] ?>],
            backgroundColor: ['#007bff', '#17a2b8', '#ffc107'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Gráfico de distribución por educación
const educacionCtx = document.getElementById('educacionChart').getContext('2d');
new Chart(educacionCtx, {
    type: 'bar',
    data: {
        labels: [<?= implode(',', array_map(function($key) { return "'" . addslashes($key) . "'"; }, array_keys($por_educacion))) ?>],
        datasets: [{
            label: 'Candidatos',
            data: [<?= implode(',', array_values($por_educacion)) ?>],
            backgroundColor: '#28a745',
            borderColor: '#20c997',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Gráfico de distribución por experiencia
const experienciaCtx = document.getElementById('experienciaChart').getContext('2d');
new Chart(experienciaCtx, {
    type: 'bar',
    data: {
        labels: [<?= implode(',', array_map(function($key) { return "'" . addslashes($key) . "'"; }, array_keys($por_experiencia))) ?>],
        datasets: [{
            label: 'Candidatos',
            data: [<?= implode(',', array_values($por_experiencia)) ?>],
            backgroundColor: '#fd7e14',
            borderColor: '#e83e8c',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Ocultar mensajes después de 5 segundos
setTimeout(() => {
    document.querySelector('.alert-success')?.remove();
}, 5000);
</script>

</body>
</html> 