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

$titulo_pagina = "Revisar Solicitudes - Gerente";

// Obtener información del contexto
$sede_id = $_SESSION['sede_seleccionada'];
$sede_nombre = $_SESSION['sede_nombre'];

// Obtener solicitudes de la sede del gerente
$solicitud_model = new Solicitud();
$solicitudes = $solicitud_model->obtenerPorSede($sede_id);

// Filtrar por estado si se especifica
$filtro_estado = $_GET['estado'] ?? 'todas';
if ($filtro_estado !== 'todas') {
    $solicitudes = array_filter($solicitudes, function($s) use ($filtro_estado) {
        return $s['estado'] === $filtro_estado;
    });
}

// Mensajes de sesión
$mensaje_exito = $_SESSION['solicitud_aprobada'] ?? $_SESSION['solicitud_rechazada'] ?? $_SESSION['solicitud_pospuesta'] ?? null;
$mensaje_error = $_SESSION['error_decision'] ?? null;
unset($_SESSION['solicitud_aprobada'], $_SESSION['solicitud_rechazada'], $_SESSION['solicitud_pospuesta'], 
      $_SESSION['error_decision']);
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
            <h2 class="text-primary mb-0">📋 Revisar Solicitudes</h2>
            <div>
                <a href="menu.php" class="btn btn-outline-secondary">← Regresar</a>
            </div>
        </div>

        <!-- Contexto de trabajo -->
        <div class="alert alert-info">
            <h6 class="alert-heading mb-1">📍 Contexto de Trabajo</h6>
            <p class="mb-0">
                <strong>Sede:</strong> <?= htmlspecialchars($sede_nombre) ?>
            </p>
        </div>

        <?php if ($mensaje_exito): ?>
            <div class="alert alert-success text-center fw-bold"><?= htmlspecialchars($mensaje_exito) ?></div>
        <?php elseif ($mensaje_error): ?>
            <div class="alert alert-danger text-center fw-bold"><?= htmlspecialchars($mensaje_error) ?></div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="btn-group" role="group">
                    <a href="?estado=todas" class="btn btn-outline-primary <?= $filtro_estado === 'todas' ? 'active' : '' ?>">
                        Todas (<?= count($solicitudes) ?>)
                    </a>
                    <a href="?estado=enviada a gerencia" class="btn btn-outline-warning <?= $filtro_estado === 'enviada a gerencia' ? 'active' : '' ?>">
                        Enviadas a Gerencia
                    </a>
                    <a href="?estado=aceptada gerencia" class="btn btn-outline-success <?= $filtro_estado === 'aceptada gerencia' ? 'active' : '' ?>">
                        Aprobadas
                    </a>
                    <a href="?estado=rechazada" class="btn btn-outline-danger <?= $filtro_estado === 'rechazada' ? 'active' : '' ?>">
                        Rechazadas
                    </a>
                    <a href="?estado=pospuesta" class="btn btn-outline-secondary <?= $filtro_estado === 'pospuesta' ? 'active' : '' ?>">
                        Pospuestas
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <input type="text" id="busqueda" class="form-control" placeholder="🔍 Buscar solicitudes...">
            </div>
        </div>

        <?php if (empty($solicitudes)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>
                No hay solicitudes para revisar en esta sede.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="tablaSolicitudes">
                    <thead class="table-secondary text-center">
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Departamento</th>
                            <th>Puesto</th>
                            <th>Solicitante</th>
                            <th>Prioridad</th>
                            <th>Estado</th>
                            <th>Fecha Límite</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php foreach ($solicitudes as $index => $solicitud): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <span class="badge bg-dark"><?= htmlspecialchars($solicitud['codigo']) ?></span>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($solicitud['departamento_nombre']) ?></strong>
                                </td>
                                <td class="text-start">
                                    <strong><?= htmlspecialchars($solicitud['perfil_puesto']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($solicitud['descripcion']) ?></small>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($solicitud['solicitante_nombre']) ?></small>
                                </td>
                                <td>
                                    <?php
                                    $prioridad_class = '';
                                    switch($solicitud['prioridad']) {
                                        case 'alta': $prioridad_class = 'bg-danger'; break;
                                        case 'media': $prioridad_class = 'bg-warning'; break;
                                        case 'baja': $prioridad_class = 'bg-success'; break;
                                    }
                                    ?>
                                    <span class="badge <?= $prioridad_class ?>">
                                        <?= ucfirst($solicitud['prioridad']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $estado_class = '';
                                    switch($solicitud['estado']) {
                                        case 'borrador': $estado_class = 'bg-secondary'; break;
                                        case 'enviada a gerencia': $estado_class = 'bg-warning'; break;
                                        case 'aceptada gerencia': $estado_class = 'bg-success'; break;
                                        case 'rechazada': $estado_class = 'bg-danger'; break;
                                        case 'pospuesta': $estado_class = 'bg-secondary'; break;
                                        case 'en proceso rh': $estado_class = 'bg-primary'; break;
                                        case 'solicita cambios': $estado_class = 'bg-warning'; break;
                                        case 'cerrada': $estado_class = 'bg-dark'; break;
                                    }
                                    ?>
                                    <span class="badge <?= $estado_class ?>">
                                        <?= ucfirst(str_replace('_', ' ', $solicitud['estado'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($solicitud['fecha_limite_cobertura']): ?>
                                        <?php
                                        $fecha_limite = new DateTime($solicitud['fecha_limite_cobertura']);
                                        $hoy = new DateTime();
                                        $dias_restantes = $hoy->diff($fecha_limite)->days;
                                        $clase_fecha = $dias_restantes <= 7 ? 'text-danger fw-bold' : ($dias_restantes <= 15 ? 'text-warning' : 'text-success');
                                        ?>
                                        <span class="<?= $clase_fecha ?>">
                                            <?= $fecha_limite->format('d/m/Y') ?>
                                            <br><small>(<?= $dias_restantes ?> días)</small>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">No definida</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group-vertical btn-group-sm">
                                        <a href="ver.php?id=<?= $solicitud['id'] ?>" class="btn btn-outline-info btn-sm" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if ($solicitud['estado'] === 'enviada a gerencia'): ?>
                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                    onclick="aprobarSolicitud(<?= $solicitud['id'] ?>)" title="Aprobar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="rechazarSolicitud(<?= $solicitud['id'] ?>)" title="Rechazar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                    onclick="posponerSolicitud(<?= $solicitud['id'] ?>)" title="Posponer">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning btn-sm" 
                                                    onclick="solicitarCambios(<?= $solicitud['id'] ?>)" title="Solicitar cambios">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
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

// Búsqueda en tiempo real
document.getElementById('busqueda').addEventListener('keyup', function() {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll("#tablaSolicitudes tbody tr");

    filas.forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(filtro) ? '' : 'none';
    });
});

// Ocultar mensajes después de 5 segundos
setTimeout(() => {
    document.querySelector('.alert-success')?.remove();
    document.querySelector('.alert-danger')?.remove();
}, 5000);
</script>

</body>
</html> 