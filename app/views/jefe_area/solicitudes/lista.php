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

$titulo_pagina = "Mis Solicitudes - Jefe de Área";

// Obtener información del contexto
$sede_id = $_SESSION['sede_seleccionada'];
$departamento_id = $_SESSION['departamento_seleccionado'];
$sede_nombre = $_SESSION['sede_nombre'];
$departamento_nombre = $_SESSION['departamento_nombre'];

// Obtener solicitudes del departamento del usuario
$solicitud_model = new Solicitud();
$solicitudes = $solicitud_model->obtenerPorSedeDepartamento($sede_id, $departamento_id);

// Filtrar por estado si se especifica
$filtro_estado = $_GET['estado'] ?? 'todas';
if ($filtro_estado !== 'todas') {
    $solicitudes = array_filter($solicitudes, function($s) use ($filtro_estado) {
        return $s['estado'] === $filtro_estado;
    });
}

// Mensajes de sesión
$mensaje_exito = $_SESSION['solicitud_creada'] ?? $_SESSION['solicitud_editada'] ?? $_SESSION['solicitud_enviada'] ?? null;
$mensaje_error = $_SESSION['error_creacion'] ?? $_SESSION['error_edicion'] ?? $_SESSION['error_envio'] ?? null;
unset($_SESSION['solicitud_creada'], $_SESSION['solicitud_editada'], $_SESSION['solicitud_enviada'], 
      $_SESSION['error_creacion'], $_SESSION['error_edicion'], $_SESSION['error_envio']);
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
            <h2 class="text-primary mb-0">📋 Mis Solicitudes</h2>
            <div>
                <a href="crear.php" class="btn btn-success me-2">+ Nueva Solicitud</a>
                <a href="menu.php" class="btn btn-outline-secondary">← Regresar</a>
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

        <!-- Filtros -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="btn-group" role="group">
                    <a href="?estado=todas" class="btn btn-outline-primary <?= $filtro_estado === 'todas' ? 'active' : '' ?>">
                        Todas (<?= count($solicitudes) ?>)
                    </a>
                    <a href="?estado=borrador" class="btn btn-outline-secondary <?= $filtro_estado === 'borrador' ? 'active' : '' ?>">
                        Borrador
                    </a>
                    <a href="?estado=enviada a gerencia" class="btn btn-outline-info <?= $filtro_estado === 'enviada a gerencia' ? 'active' : '' ?>">
                        Enviada
                    </a>
                    <a href="?estado=aceptada gerencia" class="btn btn-outline-success <?= $filtro_estado === 'aceptada gerencia' ? 'active' : '' ?>">
                        Aceptada
                    </a>
                    <a href="?estado=rechazada" class="btn btn-outline-danger <?= $filtro_estado === 'rechazada' ? 'active' : '' ?>">
                        Rechazada
                    </a>
                    <a href="?estado=solicita cambios" class="btn btn-outline-warning <?= $filtro_estado === 'solicita cambios' ? 'active' : '' ?>">
                        Solicita Cambios
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
                No hay solicitudes en este departamento.
                <a href="crear.php" class="btn btn-success btn-sm ms-2">Crear Primera Solicitud</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="tablaSolicitudes">
                    <thead class="table-secondary text-center">
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Puesto</th>
                            <th>Prioridad</th>
                            <th>Modalidad</th>
                            <th>Estado</th>
                            <th>Cambios Solicitados</th>
                            <th>Fecha Límite</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php foreach ($solicitudes as $index => $solicitud): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-dark me-2"><?= htmlspecialchars($solicitud['codigo']) ?></span>
                                        <?php if ($solicitud['estado'] === 'solicita cambios'): ?>
                                            <span class="badge bg-warning text-dark" title="Requiere cambios">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Cambios
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-start">
                                    <strong><?= htmlspecialchars($solicitud['perfil_puesto']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($solicitud['descripcion']) ?></small>
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
                                    $modalidad_class = '';
                                    switch($solicitud['modalidad']) {
                                        case 'presencial': $modalidad_class = 'bg-primary'; break;
                                        case 'remoto': $modalidad_class = 'bg-info'; break;
                                        case 'hibrido': $modalidad_class = 'bg-secondary'; break;
                                    }
                                    ?>
                                    <span class="badge <?= $modalidad_class ?>">
                                        <?= ucfirst($solicitud['modalidad']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $estado_class = '';
                                    switch($solicitud['estado']) {
                                        case 'borrador': $estado_class = 'bg-secondary'; break;
                                        case 'enviada a gerencia': $estado_class = 'bg-info'; break;
                                        case 'aceptada gerencia': $estado_class = 'bg-success'; break;
                                        case 'rechazada': $estado_class = 'bg-danger'; break;
                                        case 'pospuesta': $estado_class = 'bg-warning'; break;
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
                                    <?php if ($solicitud['estado'] === 'solicita cambios' && !empty($solicitud['cambios_solicitados'])): ?>
                                        
                                        <div class="text-start">
                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalCambiosSolicitados"
                                                    data-cambios="<?= htmlspecialchars($solicitud['cambios_solicitados']) ?>"
                                                    data-codigo="<?= htmlspecialchars($solicitud['codigo']) ?>"
                                                    data-titulo="<?= htmlspecialchars($solicitud['titulo']) ?>">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Ver Cambios
                                            </button>
                                            <!-- Debug: Mostrar cambios directamente -->
                                            <div class="mt-1 small text-muted">
                                                <strong>Cambios:</strong> <?= htmlspecialchars(substr($solicitud['cambios_solicitados'], 0, 50)) ?>...
                                            </div>
                                        </div>
                                    <?php elseif ($solicitud['estado'] === 'rechazada' && !empty($solicitud['motivo_rechazo'])): ?>
                                        <div class="text-start">
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-placement="top" 
                                                    data-bs-target="#modalMotivoRechazo"
                                                    data-motivo="<?= htmlspecialchars($solicitud['motivo_rechazo']) ?>"
                                                    data-codigo="<?= htmlspecialchars($solicitud['codigo']) ?>"
                                                    data-titulo="<?= htmlspecialchars($solicitud['titulo']) ?>">
                                                <i class="fas fa-times me-1"></i>Ver Motivo
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
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
                                        
                                        <?php if ($solicitud['estado'] === 'borrador'): ?>
                                            <a href="editar.php?id=<?= $solicitud['id'] ?>" class="btn btn-outline-warning btn-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                    onclick="enviarAGerencia(<?= $solicitud['id'] ?>)" title="Enviar a gerencia">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($solicitud['estado'] === 'enviada a gerencia'): ?>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                    onclick="volverABorrador(<?= $solicitud['id'] ?>)" title="Volver a borrador">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <?php if ($solicitud['estado'] === 'solicita cambios'): ?>
                                            <a href="editar.php?id=<?= $solicitud['id'] ?>" class="btn btn-outline-warning btn-sm" title="Editar con cambios solicitados">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-success btn-sm" 
                                                    onclick="enviarAGerencia(<?= $solicitud['id'] ?>)" title="Reenviar a gerencia">
                                                <i class="fas fa-paper-plane"></i>
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

<!-- Modal para mostrar cambios solicitados por gerencia -->
<div class="modal fade" id="modalCambiosSolicitados" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Cambios Solicitados por Gerencia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Código:</strong> <span id="codigoCambios" class="badge bg-dark"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Título:</strong> <span id="tituloCambios"></span>
                    </div>
                </div>
                <div class="alert alert-warning">
                    <h6 class="alert-heading mb-2">⚠️ Cambios Requeridos:</h6>
                    <div id="cambiosDetalle" class="bg-light p-3 rounded"></div>
                </div>
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Revisa cuidadosamente los cambios solicitados antes de proceder a editar la solicitud.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-warning" id="btnEditarConCambios">
                    <i class="fas fa-edit me-1"></i>Editar Solicitud
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar motivo de rechazo -->
<div class="modal fade" id="modalMotivoRechazo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle me-2"></i>Motivo del Rechazo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Código:</strong> <span id="codigoRechazo" class="badge bg-dark"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Título:</strong> <span id="tituloRechazo"></span>
                    </div>
                </div>
                <div class="alert alert-danger">
                    <h6 class="alert-heading mb-2">❌ Motivo del Rechazo:</h6>
                    <div id="motivoDetalle" class="bg-light p-3 rounded"></div>
                </div>
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Esta solicitud fue rechazada por gerencia. Considera los comentarios para futuras solicitudes.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let solicitudIdParaEnviar = null;
let solicitudIdParaEditar = null;

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

// Configurar modales para cambios solicitados y motivos de rechazo
document.addEventListener('DOMContentLoaded', function() {
    // Modal de cambios solicitados
    const modalCambios = document.getElementById('modalCambiosSolicitados');
    if (modalCambios) {
        modalCambios.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const cambios = button.getAttribute('data-cambios');
            const codigo = button.getAttribute('data-codigo');
            const titulo = button.getAttribute('data-titulo');
            
            // Obtener el ID de la solicitud del botón más cercano
            const row = button.closest('tr');
            const editButton = row.querySelector('a[href*="editar.php"]');
            if (editButton) {
                const href = editButton.getAttribute('href');
                const match = href.match(/id=(\d+)/);
                if (match) {
                    solicitudIdParaEditar = match[1];
                }
            }
            
            document.getElementById('codigoCambios').textContent = codigo;
            document.getElementById('tituloCambios').textContent = titulo;
            document.getElementById('cambiosDetalle').innerHTML = cambios.replace(/\n/g, '<br>');
        });
    }
    
    // Modal de motivo de rechazo
    const modalRechazo = document.getElementById('modalMotivoRechazo');
    if (modalRechazo) {
        modalRechazo.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const motivo = button.getAttribute('data-motivo');
            const codigo = button.getAttribute('data-codigo');
            const titulo = button.getAttribute('data-titulo');
            
            document.getElementById('codigoRechazo').textContent = codigo;
            document.getElementById('tituloRechazo').textContent = titulo;
            document.getElementById('motivoDetalle').innerHTML = motivo.replace(/\n/g, '<br>');
        });
    }
    
    // Botón para editar con cambios solicitados
    const btnEditarConCambios = document.getElementById('btnEditarConCambios');
    if (btnEditarConCambios) {
        btnEditarConCambios.addEventListener('click', function() {
            if (solicitudIdParaEditar) {
                window.location.href = `editar.php?id=${solicitudIdParaEditar}`;
            }
        });
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