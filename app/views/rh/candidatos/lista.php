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

$titulo_pagina = "Lista de Candidatos - Nexus RH";

// Obtener candidatos según el contexto del usuario
$candidato_model = new Candidato();
$candidatos = $candidato_model->obtenerPorSedeDepartamento(
    $_SESSION['sede_seleccionada'], 
    $_SESSION['departamento_seleccionado']
);

$mensaje_exito = $_SESSION['candidato_creado'] ?? $_SESSION['candidato_editado'] ?? $_SESSION['candidato_eliminado'] ?? null;
$mensaje_error = $_SESSION['error_creacion'] ?? $_SESSION['error_edicion'] ?? $_SESSION['error_eliminacion'] ?? null;
unset($_SESSION['candidato_creado'], $_SESSION['candidato_editado'], $_SESSION['candidato_eliminado'], 
       $_SESSION['error_creacion'], $_SESSION['error_edicion'], $_SESSION['error_eliminacion']);
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
            <h2 class="text-primary">📋 Lista de Candidatos</h2>
            <div>
                <a href="crear.php" class="btn btn-success me-2">+ Registrar Candidato</a>
                <a href="menu.php" class="btn btn-outline-secondary">← Regresar</a>
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
        <?php elseif ($mensaje_error): ?>
            <div class="alert alert-danger text-center fw-bold"><?= htmlspecialchars($mensaje_error) ?></div>
        <?php endif; ?>

        <?php if (empty($candidatos)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>
                No hay candidatos registrados en esta sede y departamento.
            </div>
        <?php else: ?>
            <div class="mb-3">
                <input type="text" id="busqueda" class="form-control" placeholder="🔍 Buscar por nombre, CURP, área de experiencia...">
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" id="tablaCandidatos">
                    <thead class="table-secondary text-center">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Edad</th>
                            <th>Área de Experiencia</th>
                            <th>Carrera</th>
                            <th>Años Exp.</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php foreach ($candidatos as $index => $candidato): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td class="text-start">
                                    <strong><?= htmlspecialchars($candidato['nombre']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($candidato['curp']) ?></small>
                                </td>
                                <td><?= $candidato['edad'] ?> años</td>
                                <td class="text-start">
                                    <?= htmlspecialchars($candidato['area_experiencia']) ?>
                                </td>
                                <td class="text-start">
                                    <?php if ($candidato['carrera']): ?>
                                        <span class="badge bg-info text-dark"><?= htmlspecialchars($candidato['carrera']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">No especificada</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $candidato['anos_experiencia'] ?> años</td>
                                <td>
                                    <?php
                                    $estado_class = '';
                                    $estado_text = '';
                                    switch ($candidato['estado']) {
                                        case 'activo':
                                            $estado_class = 'badge bg-success';
                                            $estado_text = 'Activo';
                                            break;
                                        case 'contratado':
                                            $estado_class = 'badge bg-primary';
                                            $estado_text = 'Contratado';
                                            break;
                                        case 'rechazado':
                                            $estado_class = 'badge bg-danger';
                                            $estado_text = 'Rechazado';
                                            break;
                                        default:
                                            $estado_class = 'badge bg-secondary';
                                            $estado_text = ucfirst($candidato['estado']);
                                    }
                                    ?>
                                    <span class="<?= $estado_class ?>"><?= $estado_text ?></span>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($candidato['fecha_registro'])) ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="mostrarDetalles(<?= $candidato['id'] ?>)" 
                                                title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="editar.php?id=<?= $candidato['id'] ?>" 
                                           class="btn btn-sm btn-outline-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="cambiarEstado(<?= $candidato['id'] ?>, '<?= $candidato['estado'] ?>')" 
                                                title="Cambiar estado">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="confirmarEliminacion(<?= $candidato['id'] ?>, '<?= htmlspecialchars($candidato['nombre']) ?>')" 
                                                title="Eliminar candidato">
                                            <i class="fas fa-trash"></i>
                                        </button>
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

<!-- Modal para detalles del candidato -->
<div class="modal fade" id="modalDetalles" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles del Candidato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetallesBody">
                <!-- Contenido cargado dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para cambiar estado -->
<div class="modal fade" id="modalEstado" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Estado del Candidato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEstado" method="POST" action="cambiar_estado.php">
                    <input type="hidden" name="candidato_id" id="candidato_id_estado">
                    <div class="mb-3">
                        <label class="form-label">Nuevo Estado:</label>
                        <select name="nuevo_estado" class="form-select" required>
                            <option value="activo">Activo</option>
                            <option value="contratado">Contratado</option>
                            <option value="rechazado">Rechazado</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" form="formEstado" class="btn btn-primary">Guardar Cambio</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Función para mostrar detalles del candidato
function mostrarDetalles(candidatoId) {
    fetch(`obtener_detalles.php?id=${candidatoId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('modalDetallesBody').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetalles')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los detalles del candidato');
        });
}

// Función para cambiar estado
function cambiarEstado(candidatoId, estadoActual) {
    document.getElementById('candidato_id_estado').value = candidatoId;
    
    // Seleccionar el estado actual en el select
    const selectEstado = document.querySelector('select[name="nuevo_estado"]');
    selectEstado.value = estadoActual;
    
    new bootstrap.Modal(document.getElementById('modalEstado')).show();
}

// Función para confirmar eliminación
function confirmarEliminacion(candidatoId, nombreCandidato) {
    if (confirm(`¿Estás seguro de que deseas eliminar al candidato "${nombreCandidato}"?\n\nEsta acción cambiará su estado a 'inactivo' y no se podrá deshacer.`)) {
        window.location.href = `eliminar.php?id=${candidatoId}`;
    }
}

// Búsqueda en tiempo real
document.getElementById('busqueda')?.addEventListener('keyup', function() {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll("#tablaCandidatos tbody tr");

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