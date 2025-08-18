<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario jefe de área
verificarRol('jefe_area');

// Verificar contexto de trabajo (sede y departamento)
verificarContextoRol();

// Incluir modelo de solicitud
safe_require_once(model_path('Solicitud'));

$titulo_pagina = "Ver Solicitud - Jefe de Área";

// Obtener información del contexto
$sede_id = $_SESSION['sede_seleccionada'];
$departamento_id = $_SESSION['departamento_seleccionado'];
$sede_nombre = $_SESSION['sede_nombre'];
$departamento_nombre = $_SESSION['departamento_nombre'];

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

// Verificar que la solicitud existe y pertenece al departamento del usuario
if (!$solicitud || $solicitud['departamento_id'] != $departamento_id || $solicitud['sede_id'] != $sede_id) {
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
        case 'enviada a gerencia': return 'bg-info';
        case 'aceptada gerencia': return 'bg-success';
        case 'rechazada': return 'bg-danger';
        case 'pospuesta': return 'bg-warning';
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
                <?php if ($solicitud['estado'] === 'borrador'): ?>
                    <a href="editar.php?id=<?= $solicitud_id ?>" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-2"></i>Editar
                    </a>
                <?php endif; ?>
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
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Cambios Solicitados por Gerencia</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h6 class="alert-heading mb-2">⚠️ Se requieren modificaciones:</h6>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($solicitud['cambios_solicitados'])) ?></p>
                </div>
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Una vez realizados los cambios, puedes volver a enviar la solicitud a gerencia.
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Motivo de Rechazo (si aplica) -->
        <?php if ($solicitud['estado'] === 'rechazada' && !empty($solicitud['motivo_rechazo'])): ?>
        <div class="card mb-3 border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-times-circle me-2"></i>Motivo del Rechazo</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <h6 class="alert-heading mb-2">❌ Solicitud rechazada:</h6>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($solicitud['motivo_rechazo'])) ?></p>
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
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Última Actualización:</label>
                        <p class="mb-0"><?= date('d/m/Y H:i', strtotime($solicitud['actualizado_en'])) ?></p>
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
                <?php if ($solicitud['estado'] === 'borrador'): ?>
                    <a href="editar.php?id=<?= $solicitud_id ?>" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-2"></i>Editar
                    </a>
                    <button type="button" class="btn btn-success" onclick="enviarAGerencia(<?= $solicitud_id ?>)">
                        <i class="fas fa-paper-plane me-2"></i>Enviar a Gerencia
                    </button>
                <?php elseif ($solicitud['estado'] === 'enviada a gerencia'): ?>
                    <button type="button" class="btn btn-secondary" onclick="volverABorrador(<?= $solicitud_id ?>)">
                        <i class="fas fa-undo me-2"></i>Volver a Borrador
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar envío a gerencia -->
<div class="modal fade" id="modalEnviarGerencia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enviar Solicitud a Gerencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas enviar esta solicitud a gerencia para su revisión?</p>
                <p class="text-muted small">Una vez enviada, no podrás editarla hasta que sea revisada.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarEnvio">Enviar a Gerencia</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let solicitudIdParaEnviar = null;

// Función para enviar a gerencia
function enviarAGerencia(solicitudId) {
    solicitudIdParaEnviar = solicitudId;
    const modal = new bootstrap.Modal(document.getElementById('modalEnviarGerencia'));
    modal.show();
}

// Confirmar envío
document.getElementById('btnConfirmarEnvio').addEventListener('click', function() {
    if (solicitudIdParaEnviar) {
        window.location.href = `enviar_gerencia.php?id=${solicitudIdParaEnviar}`;
    }
});

// Función para volver a borrador
function volverABorrador(solicitudId) {
    if (confirm('¿Estás seguro de que deseas volver esta solicitud a estado borrador?')) {
        window.location.href = `volver_borrador.php?id=${solicitudId}`;
    }
}
</script>

</body>
</html> 