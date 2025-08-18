<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario de RH
verificarRol('rh');

// Verificar contexto de trabajo (sede y departamento)
verificarContextoRol();

// Incluir modelos necesarios
safe_require_once(model_path('Sede'));
safe_require_once(model_path('departamento'));

$titulo_pagina = "Crear Solicitud - Nexus RH";

// Obtener sedes y departamentos
$sede_model = new Sede();
$departamento_model = new Departamento();

$sedes = $sede_model->obtenerTodas();
$departamentos = $departamento_model->obtenerTodosConSede();

// Filtrar departamentos por la sede del usuario
$departamentos_filtrados = array_filter($departamentos, function($dept) {
    return $dept['sede_id'] == $_SESSION['sede_seleccionada'];
});

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
            <h2 class="text-primary mb-0">📋 Crear Nueva Solicitud</h2>
            <div>
                <a href="menu.php" class="btn btn-outline-secondary me-2">← Regresar</a>
                <a href="lista.php" class="btn btn-outline-info">📋 Ver Solicitudes</a>
            </div>
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
                    <h5 class="text-primary border-bottom pb-2">📝 Información Básica</h5>
                </div>

                <div class="col-md-8">
                    <label class="form-label">Título de la Solicitud *</label>
                    <input type="text" name="titulo" class="form-control text-uppercase" required 
                           placeholder="Ej: Solicitud de Desarrollador Frontend">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Cantidad de Vacantes *</label>
                    <input type="number" name="cantidad" class="form-control" min="1" max="50" value="1" required>
                </div>

                <div class="col-12">
                    <label class="form-label">Descripción Detallada *</label>
                    <textarea name="descripcion" rows="4" class="form-control" required 
                              placeholder="Describe detalladamente el puesto, responsabilidades y contexto del trabajo"></textarea>
                </div>

                <!-- Información del Puesto -->
                <div class="col-12">
                    <h5 class="text-primary border-bottom pb-2 mt-4">💼 Información del Puesto</h5>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Perfil del Puesto *</label>
                    <input type="text" name="perfil_puesto" class="form-control text-uppercase" required 
                           placeholder="Ej: Desarrollador Frontend, Analista de RH, etc.">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Prioridad *</label>
                    <select name="prioridad" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <option value="alta">Alta</option>
                        <option value="media" selected>Media</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Modalidad *</label>
                    <select name="modalidad" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <option value="presencial" selected>Presencial</option>
                        <option value="remoto">Remoto</option>
                        <option value="hibrido">Híbrido</option>
                    </select>
                </div>

                <!-- Ubicación -->
                <div class="col-12">
                    <h5 class="text-primary border-bottom pb-2 mt-4">📍 Ubicación</h5>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Sede *</label>
                    <select name="sede_id" class="form-select" id="sedeSelect" required>
                        <option value="">Seleccione una sede</option>
                        <?php foreach ($sedes as $sede): ?>
                            <option value="<?= $sede['id'] ?>" <?= $sede['id'] == $_SESSION['sede_seleccionada'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sede['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Departamento *</label>
                    <select name="departamento_id" class="form-select" id="departamentoSelect" required>
                        <option value="">Primero seleccione una sede</option>
                    </select>
                    <div id="error-departamentos" class="text-danger mt-1" style="display: none; font-size: 0.9rem;"></div>
                </div>

                <!-- Información Salarial -->
                <div class="col-12">
                    <h5 class="text-primary border-bottom pb-2 mt-4">💰 Información Salarial</h5>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Salario Mínimo</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="salario_min" class="form-control" min="0" step="0.01" 
                               placeholder="0.00">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Salario Máximo</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="salario_max" class="form-control" min="0" step="0.01" 
                               placeholder="0.00">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Fecha Límite de Cobertura</label>
                    <input type="date" name="fecha_limite_cobertura" class="form-control" 
                           min="<?= date('Y-m-d') ?>">
                </div>

                <!-- Requisitos -->
                <div class="col-12">
                    <h5 class="text-primary border-bottom pb-2 mt-4">📋 Requisitos del Puesto</h5>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Carrera Requerida</label>
                    <input type="text" name="requisitos[carrera]" class="form-control text-uppercase" 
                           placeholder="Ej: Ingeniería en Sistemas">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Área de Experiencia</label>
                    <input type="text" name="requisitos[area_exp]" class="form-control text-uppercase" 
                           placeholder="Ej: Desarrollo Web, Recursos Humanos">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Nivel de Educación</label>
                    <select name="requisitos[nivel_educacion]" class="form-select">
                        <option value="">Seleccionar</option>
                        <option value="PRIMARIA">Primaria</option>
                        <option value="SECUNDARIA">Secundaria</option>
                        <option value="PREPARATORIA">Preparatoria</option>
                        <option value="TECNICO">Técnico</option>
                        <option value="LICENCIATURA" selected>Licenciatura</option>
                        <option value="INGENIERIA">Ingeniería</option>
                        <option value="MAESTRIA">Maestría</option>
                        <option value="DOCTORADO">Doctorado</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Experiencia Mínima (años)</label>
                    <input type="number" name="requisitos[experiencia_minima]" class="form-control" min="0" max="50" 
                           placeholder="0">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Habilidades Requeridas</label>
                    <input type="text" name="requisitos[habilidades]" class="form-control" 
                           placeholder="Ej: React, JavaScript, Excel (separar con comas)">
                </div>

                <div class="col-12">
                    <label class="form-label">Observaciones Adicionales</label>
                    <textarea name="requisitos[observaciones]" rows="3" class="form-control" 
                              placeholder="Cualquier requisito adicional o información importante"></textarea>
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

// Cargar departamentos según la sede seleccionada
document.getElementById('sedeSelect').addEventListener('change', function() {
    const sedeId = this.value;
    const departamentoSelect = document.getElementById('departamentoSelect');
    const errorDiv = document.getElementById('error-departamentos');
    
    // Limpiar departamentos y errores
    departamentoSelect.innerHTML = '<option value="">Cargando departamentos...</option>';
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';
    
    if (sedeId) {
        fetch(`../../../controllers/getDepartamentosCandidatos.php?sede_id=${sedeId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Verificar si hay error en la respuesta
                if (data.error) {
                    throw new Error(data.error);
                }
                
                // Verificar que sea un array
                if (!Array.isArray(data)) {
                    throw new Error('Formato de respuesta inválido');
                }
                
                departamentoSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
                data.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.id;
                    option.textContent = dept.nombre;
                    departamentoSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                departamentoSelect.innerHTML = '<option value="">Error al cargar departamentos</option>';
                
                // Mostrar error visual al usuario
                errorDiv.textContent = 'Error al cargar departamentos. Intente nuevamente.';
                errorDiv.style.display = 'block';
            });
    } else {
        departamentoSelect.innerHTML = '<option value="">Primero seleccione una sede</option>';
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';
    }
});

// Validación de salarios
document.querySelectorAll('input[name="salario_min"], input[name="salario_max"]').forEach(input => {
    input.addEventListener('blur', function() {
        const salarioMin = parseFloat(document.querySelector('input[name="salario_min"]').value) || 0;
        const salarioMax = parseFloat(document.querySelector('input[name="salario_max"]').value) || 0;
        
        if (salarioMax > 0 && salarioMin > salarioMax) {
            alert('El salario máximo no puede ser menor que el salario mínimo');
            this.value = '';
        }
    });
});

// Auto-completar departamentos si ya hay sede seleccionada
document.addEventListener('DOMContentLoaded', function() {
    const sedeSelect = document.getElementById('sedeSelect');
    if (sedeSelect.value) {
        sedeSelect.dispatchEvent(new Event('change'));
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