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

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $candidato_id = (int)$_GET['id'];
    
    // Crear instancia del modelo
    $candidato_model = new Candidato();
    
    // Obtener detalles del candidato
    $candidato = $candidato_model->obtenerPorId($candidato_id);
    
    if ($candidato) {
        // Verificar que el candidato pertenezca al contexto del usuario
        if ($candidato['sede_id'] == $_SESSION['sede_seleccionada'] && 
            $candidato['departamento_id'] == $_SESSION['departamento_seleccionado']) {
            
            // Mostrar detalles del candidato
            ?>
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">📋 Información Personal</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Nombre:</strong></td><td><?= htmlspecialchars($candidato['nombre']) ?></td></tr>
                        <tr><td><strong>CURP:</strong></td><td><?= htmlspecialchars($candidato['curp']) ?></td></tr>
                        <tr><td><strong>Edad:</strong></td><td><?= $candidato['edad'] ?> años</td></tr>
                        <tr><td><strong>Género:</strong></td><td><?= htmlspecialchars($candidato['genero']) ?></td></tr>
                        <tr><td><strong>Teléfono:</strong></td><td><?= htmlspecialchars($candidato['telefono']) ?></td></tr>
                        <tr><td><strong>Correo:</strong></td><td><?= htmlspecialchars($candidato['correo']) ?></td></tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h6 class="text-primary">🎓 Información Académica</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Nivel:</strong></td><td><?= htmlspecialchars($candidato['nivel_educacion']) ?></td></tr>
                        <?php if ($candidato['carrera']): ?>
                            <tr><td><strong>Carrera:</strong></td><td><?= htmlspecialchars($candidato['carrera']) ?></td></tr>
                        <?php endif; ?>
                    </table>
                    
                    <h6 class="text-primary mt-3">💼 Información Laboral</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Área:</strong></td><td><?= htmlspecialchars($candidato['area_experiencia']) ?></td></tr>
                        <tr><td><strong>Experiencia:</strong></td><td><?= $candidato['anos_experiencia'] ?> años</td></tr>
                        <?php if ($candidato['companias_previas']): ?>
                            <tr><td><strong>Empresas:</strong></td><td><?= htmlspecialchars($candidato['companias_previas']) ?></td></tr>
                        <?php endif; ?>
                    </table>
                </div>
                
                <div class="col-12">
                    <h6 class="text-primary">📍 Información de Ubicación</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Dirección:</strong></td><td><?= htmlspecialchars($candidato['direccion']) ?></td></tr>
                        <?php if ($candidato['distancia_sede']): ?>
                            <tr><td><strong>Distancia:</strong></td><td><?= $candidato['distancia_sede'] ?> km</td></tr>
                        <?php endif; ?>
                    </table>
                </div>
                
                <div class="col-12">
                    <h6 class="text-primary">🎯 Área de Solicitud</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Sede:</strong></td><td><?= htmlspecialchars($candidato['sede_nombre']) ?></td></tr>
                        <tr><td><strong>Departamento:</strong></td><td><?= htmlspecialchars($candidato['departamento_nombre']) ?></td></tr>
                    </table>
                </div>
                
                <div class="col-12">
                    <h6 class="text-primary">📊 Información del Sistema</h6>
                    <table class="table table-sm">
                        <tr><td><strong>Estado:</strong></td>
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
                        </tr>
                        <tr><td><strong>Fecha Registro:</strong></td><td><?= date('d/m/Y H:i', strtotime($candidato['fecha_registro'])) ?></td></tr>
                        <?php if ($candidato['actualizado_en'] && $candidato['actualizado_en'] != $candidato['fecha_registro']): ?>
                            <tr><td><strong>Última Actualización:</strong></td><td><?= date('d/m/Y H:i', strtotime($candidato['actualizado_en'])) ?></td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
            <?php
        } else {
            echo '<div class="alert alert-danger">No tienes permisos para ver este candidato.</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Candidato no encontrado.</div>';
    }
} else {
    echo '<div class="alert alert-danger">ID de candidato no válido.</div>';
}
?> 