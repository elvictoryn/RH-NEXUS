<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario gerente
verificarRol('gerente');

// Verificar contexto de trabajo (sede)
verificarContextoRol();

// Incluir modelo de solicitud
safe_require_once(model_path('Solicitud'));

$titulo_pagina = "Ver Solicitud - Gerente";

// Obtener información del contexto
$sede_id = $_SESSION['sede_seleccionada'];
$sede_nombre = $_SESSION['sede_nombre'];

// Verificar que se proporcione un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_visualizacion'] = "ID de solicitud no proporcionado";
    header('Location: lista.php');
    exit;
}

$solicitud_id = (int)$_GET['id'];

// Obtener la solicitud
$solicitud_model = new Solicitud();
$solicitud = $solicitud_model->obtenerPorId($solicitud_id);

// Verificar que la solicitud existe y pertenece a la sede del gerente
if (!$solicitud || $solicitud['sede_id'] != $sede_id) {
    $_SESSION['error_visualizacion'] = "Solicitud no encontrada o no tienes permisos para verla";
    header('Location: lista.php');
    exit;
}

// Decodificar requisitos JSON si existe
$requisitos = [];
if (!empty($solicitud['requisitos_json'])) {
    $requisitos = json_decode($solicitud['requisitos_json'], true);
}

// Función para obtener clase de badge según estado
function getEstadoClass($estado) {
    switch($estado) {
        case 'borrador': return 'bg-secondary';
        case 'enviada a gerencia': return 'bg-warning';
        case 'aceptada gerencia': return 'bg-success';
        case 'rechazada': return 'bg-danger';
        case 'pospuesta': return 'bg-secondary';
        case 'en proceso rh': return 'bg-primary';
        case 'solicita cambios': return 'bg-warning';
        case 'cerrada': return 'bg-dark';
        default: return 'bg-secondary';
    }
}

// Función para obtener clase de badge según prioridad
function getPrioridadClass($prioridad) {
    switch($prioridad) {
        case 'alta': return 'bg-danger';
        case 'media': return 'bg-warning';
        case 'baja': return 'bg-success';
        default: return 'bg-secondary';
    }
}

// Función para obtener clase de badge según modalidad
function getModalidadClass($modalidad) {
    switch($modalidad) {
        case 'presencial': return 'bg-primary';
        case 'remoto': return 'bg-info';
        case 'hibrido': return 'bg-secondary';
        default: return 'bg-secondary';
    }
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
            <h2 class="text-primary mb-0">👁️ Detalles de la Solicitud</h2>
            <div>
                <a href="lista.php" class="btn btn-outline-secondary">← Regresar</a>
            </div>
        </div>

        <!-- Contexto de trabajo -->
        <div class="alert alert-info">
            <h6 class="alert-heading mb-1">📍 Contexto de Trabajo</h6>
            <p class="mb-0">
                <strong>Sede:</strong> <?= htmlspecialchars($sede_nombre) ?>
            </p>
        </div>

        <!-- Información básica -->
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información Básica</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Código:</label>
                                <p class="mb-0"><?= htmlspecialchars($solicitud['codigo']) ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Estado:</label>
                                <p class="mb-0">
                                    <span class="badge <?= getEstadoClass($solicitud['estado']) ?>">
                                        <?= ucfirst(str_replace('_', ' ', $solicitud['estado'])) ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Título:</label>
                                <p class="mb-0"><?= htmlspecialchars($solicitud['titulo']) ?></p>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Descripción:</label>
                                <p class="mb-0"><?= nl2br(htmlspecialchars($solicitud['descripcion'])) ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Perfil del Puesto:</label>
                                <p class="mb-0"><?= htmlspecialchars($solicitud['perfil_puesto']) ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Cantidad de Vacantes:</label>
                                <p class="mb-0"><?= htmlspecialchars($solicitud['cantidad']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Configuración</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Prioridad:</label>
                            <p class="mb-0">
                                <span class="badge <?= getPrioridadClass($solicitud['prioridad']) ?>">
                                    <?= ucfirst($solicitud['prioridad']) ?>
                                </span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Modalidad:</label>
                            <p class="mb-0">
                                <span class="badge <?= getModalidadClass($solicitud['modalidad']) ?>">
                                    <?= ucfirst($solicitud['modalidad']) ?>
                                </span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Fecha Límite:</label>
                            <p class="mb-0">
                                <?php if ($solicitud['fecha_limite_cobertura']): ?>
                                    <?php
                                    $fecha_limite = new DateTime($solicitud['fecha_limite_cobertura']);
                                    $hoy = new DateTime();
                                    $dias_restantes = $hoy->diff($fecha_limite)->days;
                                    $clase_fecha = $dias_restantes <= 7 ? 'text-danger fw-bold' : ($dias_restantes <= 15 ? 'text-warning' : 'text-success');
                                    ?>
                                    <span class="<?= $clase_fecha ?>">
                                        <?= $fecha_limite->format('d/m/Y') ?>
                                        <br><small>(<?= $dias_restantes ?> días restantes)</small>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">No definida</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información salarial -->
        <?php if ($solicitud['salario_min'] || $solicitud['salario_max']): ?>
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Información Salarial</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Salario Mínimo:</label>
                        <p class="mb-0">
                            <?php if ($solicitud['salario_min']): ?>
                                $<?= number_format($solicitud['salario_min'], 2) ?>
                            <?php else: ?>
                                <span class="text-muted">No especificado</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Salario Máximo:</label>
                        <p class="mb-0">
                            <?php if ($solicitud['salario_max']): ?>
                                $<?= number_format($solicitud['salario_max'], 2) ?>
                            <?php else: ?>
                                <span class="text-muted">No especificado</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Requisitos del puesto -->
        <?php if (!empty($requisitos)): ?>
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Requisitos del Puesto</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if (!empty($requisitos['carrera'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Carrera:</label>
                        <p class="mb-0"><?= htmlspecialchars($requisitos['carrera']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($requisitos['area_exp'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Área de Experiencia:</label>
                        <p class="mb-0"><?= htmlspecialchars($requisitos['area_exp']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($requisitos['nivel_educacion'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nivel de Educación:</label>
                        <p class="mb-0"><?= htmlspecialchars($requisitos['nivel_educacion']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($requisitos['experiencia_minima'])): ?>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Experiencia Mínima:</label>
                        <p class="mb-0"><?= htmlspecialchars($requisitos['experiencia_minima']) ?> años</p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($requisitos['habilidades'])): ?>
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Habilidades:</label>
                        <p class="mb-0">
                            <?php if (is_array($requisitos['habilidades'])): ?>
                                <?php foreach ($requisitos['habilidades'] as $habilidad): ?>
                                    <span class="badge bg-light text-dark me-1"><?= htmlspecialchars($habilidad) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?= htmlspecialchars($requisitos['habilidades']) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($requisitos['observaciones'])): ?>
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Observaciones:</label>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($requisitos['observaciones'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cambios Solicitados (si aplica) -->
        <?php if ($solicitud['estado'] === 'solicita cambios' && !empty($solicitud['cambios_solicitados'])): ?>
        <div class="card mb-3 border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Cambios Solicitados</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h6 class="alert-heading mb-2">⚠️ Cambios solicitados:</h6>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($solicitud['cambios_solicitados'])) ?></p>
                </div>
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Estos cambios fueron solicitados al jefe de área para su revisión.
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Información del sistema -->
        <div class="card mb-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-database me-2"></i>Información del Sistema</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Departamento:</label>
                        <p class="mb-0"><?= htmlspecialchars($solicitud['departamento_nombre']) ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Solicitante:</label>
                        <p class="mb-0"><?= htmlspecialchars($solicitud['solicitante_nombre']) ?></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Gerente Asignado:</label>
                        <p class="mb-0">
                            <?php if ($solicitud['gerente_nombre']): ?>
                                <?= htmlspecialchars($solicitud['gerente_nombre']) ?>
                            <?php else: ?>
                                <span class="text-muted">No asignado</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Fecha de Creación:</label>
                        <p class="mb-0"><?= date('d/m/Y H:i', strtotime($solicitud['creado_en'])) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="d-flex justify-content-between">
            <a href="lista.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Regresar a Lista
            </a>
            <div>
                <?php if ($solicitud['estado'] === 'enviada a gerencia'): ?>
                    <button type="button" class="btn btn-success me-2" onclick="aprobarSolicitud(<?= $solicitud_id ?>)">
                        <i class="fas fa-check me-2"></i>Aprobar
                    </button>
                    <button type="button" class="btn btn-danger me-2" onclick="rechazarSolicitud(<?= $solicitud_id ?>)">
                        <i class="fas fa-times me-2"></i>Rechazar
                    </button>
                    <button type="button" class="btn btn-secondary me-2" onclick="posponerSolicitud(<?= $solicitud_id ?>)">
                        <i class="fas fa-pause me-2"></i>Posponer
                    </button>
                    <button type="button" class="btn btn-warning" onclick="solicitarCambios(<?= $solicitud_id ?>)">
                        <i class="fas fa-edit me-2"></i>Solicitar Cambios
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para aprobar solicitud -->
<div class="modal fade" id="modalAprobar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aprobar Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas aprobar esta solicitud?</p>
                <p class="text-muted small">La solicitud pasará al proceso de RH para su gestión.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarAprobar">Aprobar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para rechazar solicitud -->
<div class="modal fade" id="modalRechazar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rechazar Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="motivoRechazo" class="form-label">Motivo del rechazo *</label>
                    <textarea class="form-control" id="motivoRechazo" rows="3" 
                              placeholder="Explica brevemente por qué se rechaza la solicitud..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarRechazar">Rechazar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para posponer solicitud -->
<div class="modal fade" id="modalPosponer" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Posponer Solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas posponer esta solicitud?</p>
                <p class="text-muted small">La solicitud quedará en espera para revisión posterior.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-secondary" id="btnConfirmarPosponer">Posponer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para solicitar cambios -->
<div class="modal fade" id="modalSolicitarCambios" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Solicitar Cambios</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="motivoCambios" class="form-label">Cambios solicitados *</label>
                    <textarea class="form-control" id="motivoCambios" rows="3" 
                              placeholder="Describe los cambios que se requieren..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmarCambios">Solicitar Cambios</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let solicitudIdActual = null;

// Funciones para mostrar modales
function aprobarSolicitud(solicitudId) {
    solicitudIdActual = solicitudId;
    const modal = new bootstrap.Modal(document.getElementById('modalAprobar'));
    modal.show();
}

function rechazarSolicitud(solicitudId) {
    solicitudIdActual = solicitudId;
    const modal = new bootstrap.Modal(document.getElementById('modalRechazar'));
    modal.show();
}

function posponerSolicitud(solicitudId) {
    solicitudIdActual = solicitudId;
    const modal = new bootstrap.Modal(document.getElementById('modalPosponer'));
    modal.show();
}

function solicitarCambios(solicitudId) {
    solicitudIdActual = solicitudId;
    const modal = new bootstrap.Modal(document.getElementById('modalSolicitarCambios'));
    modal.show();
}

// Confirmar acciones
document.getElementById('btnConfirmarAprobar').addEventListener('click', function() {
    if (solicitudIdActual) {
        window.location.href = `aprobar.php?id=${solicitudIdActual}`;
    }
});

document.getElementById('btnConfirmarRechazar').addEventListener('click', function() {
    const motivo = document.getElementById('motivoRechazo').value.trim();
    if (!motivo) {
        alert('Debes especificar un motivo para el rechazo.');
        return;
    }
    if (solicitudIdActual) {
        window.location.href = `rechazar.php?id=${solicitudIdActual}&motivo=${encodeURIComponent(motivo)}`;
    }
});

document.getElementById('btnConfirmarPosponer').addEventListener('click', function() {
    if (solicitudIdActual) {
        window.location.href = `posponer.php?id=${solicitudIdActual}`;
    }
});

document.getElementById('btnConfirmarCambios').addEventListener('click', function() {
    const cambios = document.getElementById('motivoCambios').value.trim();
    if (!cambios) {
        alert('Debes especificar los cambios solicitados.');
        return;
    }
    if (solicitudIdActual) {
        window.location.href = `solicitar_cambios.php?id=${solicitudIdActual}&cambios=${encodeURIComponent(cambios)}`;
    }
});
</script>

</body>
</html> 