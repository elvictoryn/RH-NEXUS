<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario jefe de área
verificarRol('jefe_area');

// Verificar contexto de trabajo (sede y departamento)
verificarContextoRol();

// Incluir modelos necesarios
safe_require_once(model_path('Sede'));
safe_require_once(model_path('departamento'));

$titulo_pagina = "Crear Solicitud - Jefe de Área";

// Obtener información del contexto
$sede_id = $_SESSION['sede_seleccionada'];
$departamento_id = $_SESSION['departamento_seleccionado'];
$sede_nombre = $_SESSION['sede_nombre'];
$departamento_nombre = $_SESSION['departamento_nombre'];

$mensaje_exito = $_SESSION['solicitud_creada'] ?? null;
$mensaje_error = $_SESSION['error_creacion'] ?? null;
unset($_SESSION['solicitud_creada'], $_SESSION['error_creacion']);
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
    <div class="card shadow p-4 bg-light">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary mb-0">📝 Crear Nueva Solicitud</h2>
            <div>
                <a href="menu.php" class="btn btn-outline-secondary me-2">← Regresar</a>
                <a href="lista.php" class="btn btn-outline-info">📋 Ver Solicitudes</a>
            </div>
        </div>

        <!-- Contexto de trabajo -->
        <div class="alert alert-info">
            <h6 class="alert-heading mb-1">📍 Contexto de Trabajo</h6>
            <p class="mb-0">
                <strong>Sede:</strong> <?= htmlspecialchars($sede_nombre) ?> | 
                <strong>Departamento:</strong> <?= htmlspecialchars($departamento_nombre) ?>
            </p>
        </div>

        <?php if ($mensaje_exito): ?>
            <div class="alert alert-success text-center fw-bold"><?= htmlspecialchars($mensaje_exito) ?></div>
        <?php elseif ($mensaje_error): ?>
            <div class="alert alert-danger text-center fw-bold"><?= htmlspecialchars($mensaje_error) ?></div>
        <?php endif; ?>

        <form id="formSolicitud" method="POST" action="guardar.php" autocomplete="off">
            <div class="row g-3">
                <!-- Información Básica -->
                <div class="col-12">
                    <h5 class="text-primary border-bottom pb-2">📋 Información de la Solicitud</h5>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Título del Puesto *</label>
                    <input type="text" name="perfil_puesto" class="form-control text-uppercase" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Cantidad de Vacantes *</label>
                    <input type="number" name="cantidad" class="form-control" min="1" max="10" required>
                </div>

                <div class="col-12">
                    <label class="form-label">Descripción del Puesto *</label>
                    <textarea name="descripcion" rows="4" class="form-control" required 
                              placeholder="Describe las responsabilidades, funciones y características del puesto..."></textarea>
                </div>

                <!-- Prioridad y Modalidad -->
                <div class="col-md-6">
                    <label class="form-label">Prioridad *</label>
                    <select name="prioridad" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <option value="alta">Alta</option>
                        <option value="media">Media</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Modalidad de Trabajo *</label>
                    <select name="modalidad" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <option value="presencial">Presencial</option>
                        <option value="remoto">Remoto</option>
                        <option value="hibrido">Híbrido</option>
                    </select>
                </div>

                <!-- Salarios -->
                <div class="col-md-6">
                    <label class="form-label">Salario Mínimo (MXN)</label>
                    <input type="number" name="salario_min" class="form-control" min="0" step="1000">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Salario Máximo (MXN)</label>
                    <input type="number" name="salario_max" class="form-control" min="0" step="1000">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Fecha Límite de Cobertura</label>
                    <input type="date" name="fecha_limite_cobertura" class="form-control" 
                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                </div>

                <!-- Requisitos -->
                <div class="col-12">
                    <h5 class="text-primary border-bottom pb-2 mt-4">🎯 Requisitos del Puesto</h5>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Carrera Requerida</label>
                    <input type="text" name="carrera" class="form-control text-uppercase" 
                           placeholder="Ej: Administración de Empresas">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Área de Experiencia</label>
                    <input type="text" name="area_exp" class="form-control text-uppercase" 
                           placeholder="Ej: Recursos Humanos">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nivel de Educación Mínimo</label>
                    <select name="nivel_educacion" class="form-select">
                        <option value="">Seleccionar</option>
                        <option value="PREPARATORIA">Preparatoria</option>
                        <option value="TECNICO">Técnico</option>
                        <option value="LICENCIATURA">Licenciatura</option>
                        <option value="INGENIERIA">Ingeniería</option>
                        <option value="MAESTRIA">Maestría</option>
                        <option value="DOCTORADO">Doctorado</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Experiencia Mínima (años)</label>
                    <input type="number" name="experiencia_minima" class="form-control" min="0" max="20">
                </div>

                <div class="col-12">
                    <label class="form-label">Habilidades Requeridas</label>
                    <textarea name="habilidades" rows="3" class="form-control text-uppercase" 
                              placeholder="Lista las habilidades técnicas y blandas requeridas..."></textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Observaciones Adicionales</label>
                    <textarea name="observaciones" rows="2" class="form-control" 
                              placeholder="Información adicional relevante..."></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="menu.php" class="btn btn-outline-secondary">← Cancelar</a>
                <button type="submit" class="btn btn-success" id="btnGuardar">Crear Solicitud</button>
            </div>
        </form>
    </div>
</div>

<script>
// Convierte a mayúsculas
document.querySelectorAll('.text-uppercase').forEach(input => {
    input.addEventListener('input', () => {
        input.value = input.value.toLocaleUpperCase('es-MX');
    });
});

// Validación de salarios
document.querySelector('input[name="salario_max"]').addEventListener('input', function() {
    const salarioMin = document.querySelector('input[name="salario_min"]').value;
    const salarioMax = this.value;
    
    if (salarioMin && salarioMax && parseFloat(salarioMin) > parseFloat(salarioMax)) {
        this.setCustomValidity('El salario máximo debe ser mayor al mínimo');
    } else {
        this.setCustomValidity('');
    }
});

// Ocultar mensajes después de 5 segundos
setTimeout(() => {
    document.querySelector('.alert-success')?.remove();
    document.querySelector('.alert-danger')?.remove();
}, 5000);
</script>

</body>
</html> 