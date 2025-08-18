<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario jefe de área
verificarRol('jefe_area');

// Verificar contexto de trabajo (sede y departamento)
verificarContextoRol();

// Incluir modelos necesarios
safe_require_once(model_path('Solicitud'));
safe_require_once(model_path('Sede'));
safe_require_once(model_path('departamento'));

$titulo_pagina = "Editar Solicitud - Jefe de Área";

// Obtener información del contexto
$sede_id = $_SESSION['sede_seleccionada'];
$departamento_id = $_SESSION['departamento_seleccionado'];
$sede_nombre = $_SESSION['sede_nombre'];
$departamento_nombre = $_SESSION['departamento_nombre'];

// Verificar que se proporcione un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_edicion'] = "ID de solicitud no proporcionado";
    header('Location: lista.php');
    exit;
}

$solicitud_id = (int)$_GET['id'];

// Obtener la solicitud
$solicitud_model = new Solicitud();
$solicitud = $solicitud_model->obtenerPorId($solicitud_id);

// Verificar que la solicitud existe y pertenece al departamento del usuario
if (!$solicitud || $solicitud['departamento_id'] != $departamento_id || $solicitud['sede_id'] != $sede_id) {
    $_SESSION['error_edicion'] = "Solicitud no encontrada o no tienes permisos para editarla";
    header('Location: lista.php');
    exit;
}

// Verificar que la solicitud esté en estado borrador o solicita cambios
if ($solicitud['estado'] !== 'borrador' && $solicitud['estado'] !== 'solicita cambios') {
    $_SESSION['error_edicion'] = "Solo se pueden editar solicitudes en estado borrador o cuando se solicitan cambios";
    header('Location: lista.php');
    exit;
}

// Obtener sedes y departamentos para el formulario
$sede_model = new Sede();
$departamento_model = new Departamento();
$sedes = $sede_model->obtenerTodas();
$departamentos = $departamento_model->obtenerTodosConSede();

// Filtrar departamentos por la sede actual
$departamentos_sede = array_filter($departamentos, function($dept) use ($sede_id) {
    return $dept['sede_id'] == $sede_id;
});

// Decodificar requisitos JSON si existe
$requisitos = [];
if (!empty($solicitud['requisitos_json'])) {
    $requisitos = json_decode($solicitud['requisitos_json'], true);
}
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
            <h2 class="text-primary mb-0">✏️ Editar Solicitud</h2>
            <div>
                <a href="lista.php" class="btn btn-outline-secondary">← Regresar</a>
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

        <!-- Información de la solicitud -->
        <div class="alert alert-warning">
            <h6 class="alert-heading mb-1">📋 Información de la Solicitud</h6>
            <p class="mb-0">
                <strong>Código:</strong> <?= htmlspecialchars($solicitud['codigo']) ?> | 
                <strong>Estado:</strong> <span class="badge bg-secondary"><?= ucfirst($solicitud['estado']) ?></span>
            </p>
        </div>

        <?php if ($solicitud['estado'] === 'solicita cambios' && !empty($solicitud['cambios_solicitados'])): ?>
        <!-- Cambios solicitados por gerencia -->
        <div class="alert alert-warning border-warning border-3">
            <div class="d-flex align-items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h5 class="alert-heading mb-3">⚠️ Cambios Solicitados por Gerencia</h5>
                    <div class="bg-light p-4 rounded border">
                        <h6 class="text-dark mb-2">📝 Cambios Requeridos:</h6>
                        <div class="bg-white p-3 rounded border-start border-warning border-4">
                            <p class="mb-0 fw-bold text-dark"><?= nl2br(htmlspecialchars($solicitud['cambios_solicitados'])) ?></p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="alert alert-info border-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Instrucciones:</strong> Revisa cuidadosamente los cambios solicitados, modifica la solicitud según corresponda, y luego reenvía a gerencia para su aprobación.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <form action="actualizar.php" method="POST" id="formSolicitud">
            <input type="hidden" name="solicitud_id" value="<?= $solicitud_id ?>">
            
            <div class="row">
                <!-- Información básica -->
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información Básica</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="titulo" class="form-label">Título de la Solicitud *</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" 
                                           value="<?= htmlspecialchars($solicitud['titulo']) ?>" required maxlength="255">
                                </div>
                                
                                <div class="col-md-12 mb-3">
                                    <label for="descripcion" class="form-label">Descripción *</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required><?= htmlspecialchars($solicitud['descripcion']) ?></textarea>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="perfil_puesto" class="form-label">Perfil del Puesto *</label>
                                    <input type="text" class="form-control" id="perfil_puesto" name="perfil_puesto" 
                                           value="<?= htmlspecialchars($solicitud['perfil_puesto']) ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="cantidad" class="form-label">Cantidad de Vacantes *</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                           value="<?= htmlspecialchars($solicitud['cantidad']) ?>" min="1" max="10" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Configuración -->
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Configuración</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="prioridad" class="form-label">Prioridad *</label>
                                <select class="form-select" id="prioridad" name="prioridad" required>
                                    <option value="alta" <?= $solicitud['prioridad'] === 'alta' ? 'selected' : '' ?>>Alta</option>
                                    <option value="media" <?= $solicitud['prioridad'] === 'media' ? 'selected' : '' ?>>Media</option>
                                    <option value="baja" <?= $solicitud['prioridad'] === 'baja' ? 'selected' : '' ?>>Baja</option>
                                </select>
                            </div>
                
                            <div class="mb-3">
                                <label for="modalidad" class="form-label">Modalidad *</label>
                                <select class="form-select" id="modalidad" name="modalidad" required>
                                    <option value="presencial" <?= $solicitud['modalidad'] === 'presencial' ? 'selected' : '' ?>>Presencial</option>
                                    <option value="remoto" <?= $solicitud['modalidad'] === 'remoto' ? 'selected' : '' ?>>Remoto</option>
                                    <option value="hibrido" <?= $solicitud['modalidad'] === 'hibrido' ? 'selected' : '' ?>>Híbrido</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="fecha_limite_cobertura" class="form-label">Fecha Límite de Cobertura</label>
                                <input type="date" class="form-control" id="fecha_limite_cobertura" name="fecha_limite_cobertura" 
                                       value="<?= $solicitud['fecha_limite_cobertura'] ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información salarial -->
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Información Salarial</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="salario_min" class="form-label">Salario Mínimo</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="salario_min" name="salario_min" 
                                       value="<?= $solicitud['salario_min'] ?>" min="0" step="0.01">
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="salario_max" class="form-label">Salario Máximo</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="salario_max" name="salario_max" 
                                       value="<?= $solicitud['salario_max'] ?>" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Requisitos del puesto -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Requisitos del Puesto</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="carrera" class="form-label">Carrera</label>
                            <input type="text" class="form-control" id="carrera" name="requisitos[carrera]" 
                                   value="<?= htmlspecialchars($requisitos['carrera'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="area_exp" class="form-label">Área de Experiencia</label>
                            <input type="text" class="form-control" id="area_exp" name="requisitos[area_exp]" 
                                   value="<?= htmlspecialchars($requisitos['area_exp'] ?? '') ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="nivel_educacion" class="form-label">Nivel de Educación</label>
                            <select class="form-select" id="nivel_educacion" name="requisitos[nivel_educacion]">
                                <option value="">Seleccionar...</option>
                                <option value="PRIMARIA" <?= ($requisitos['nivel_educacion'] ?? '') === 'PRIMARIA' ? 'selected' : '' ?>>Primaria</option>
                                <option value="SECUNDARIA" <?= ($requisitos['nivel_educacion'] ?? '') === 'SECUNDARIA' ? 'selected' : '' ?>>Secundaria</option>
                                <option value="PREPARATORIA" <?= ($requisitos['nivel_educacion'] ?? '') === 'PREPARATORIA' ? 'selected' : '' ?>>Preparatoria</option>
                                <option value="TECNICO" <?= ($requisitos['nivel_educacion'] ?? '') === 'TECNICO' ? 'selected' : '' ?>>Técnico</option>
                                <option value="LICENCIATURA" <?= ($requisitos['nivel_educacion'] ?? '') === 'LICENCIATURA' ? 'selected' : '' ?>>Licenciatura</option>
                                <option value="INGENIERIA" <?= ($requisitos['nivel_educacion'] ?? '') === 'INGENIERIA' ? 'selected' : '' ?>>Ingeniería</option>
                                <option value="MAESTRIA" <?= ($requisitos['nivel_educacion'] ?? '') === 'MAESTRIA' ? 'selected' : '' ?>>Maestría</option>
                                <option value="DOCTORADO" <?= ($requisitos['nivel_educacion'] ?? '') === 'DOCTORADO' ? 'selected' : '' ?>>Doctorado</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="experiencia_minima" class="form-label">Experiencia Mínima (años)</label>
                            <input type="number" class="form-control" id="experiencia_minima" name="requisitos[experiencia_minima]" 
                                   value="<?= htmlspecialchars($requisitos['experiencia_minima'] ?? '') ?>" min="0">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="habilidades" class="form-label">Habilidades (separadas por comas)</label>
                            <textarea class="form-control" id="habilidades" name="requisitos[habilidades]" rows="3" 
                                      placeholder="Ej: Excel, Word, PowerPoint, Comunicación, Liderazgo"><?= htmlspecialchars(implode(', ', $requisitos['habilidades'] ?? [])) ?></textarea>
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="observaciones" class="form-label">Observaciones Adicionales</label>
                            <textarea class="form-control" id="observaciones" name="requisitos[observaciones]" rows="3"><?= htmlspecialchars($requisitos['observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="d-flex justify-content-between">
                <a href="lista.php" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
                <div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Convertir a mayúsculas
document.getElementById('titulo').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

document.getElementById('perfil_puesto').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

// Validación de salarios
document.getElementById('salario_max').addEventListener('input', function() {
    const salarioMin = parseFloat(document.getElementById('salario_min').value) || 0;
    const salarioMax = parseFloat(this.value) || 0;
    
    if (salarioMax > 0 && salarioMin > 0 && salarioMax < salarioMin) {
        this.setCustomValidity('El salario máximo debe ser mayor al mínimo');
    } else {
        this.setCustomValidity('');
    }
});

// Validación del formulario
document.getElementById('formSolicitud').addEventListener('submit', function(e) {
    const titulo = document.getElementById('titulo').value.trim();
    const descripcion = document.getElementById('descripcion').value.trim();
    const perfilPuesto = document.getElementById('perfil_puesto').value.trim();
    
    if (titulo.length < 10) {
        e.preventDefault();
        alert('El título debe tener al menos 10 caracteres');
        return false;
    }
    
    if (descripcion.length < 20) {
        e.preventDefault();
        alert('La descripción debe tener al menos 20 caracteres');
        return false;
    }
    
    if (perfilPuesto.length < 5) {
        e.preventDefault();
        alert('El perfil del puesto debe tener al menos 5 caracteres');
        return false;
    }
});
</script>

</body>
</html> 