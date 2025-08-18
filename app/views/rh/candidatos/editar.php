<?php
if (!isset($_SESSION)) session_start();

// Incluir sistema de autenticación
require_once __DIR__ . '/../../../includes/auth_helpers.php';

// Verificar que sea usuario de RH
verificarRol('rh');

// Verificar contexto de trabajo (sede y departamento)
verificarContextoRol();

// Incluir modelos necesarios
safe_require_once(model_path('Candidato'));
safe_require_once(model_path('Sede'));
safe_require_once(model_path('departamento'));

$titulo_pagina = "Editar Candidato - Nexus RH";

// Obtener ID del candidato
$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    $_SESSION['error_edicion'] = "ID de candidato inválido.";
    header("Location: lista.php");
    exit;
}

// Obtener datos del candidato
$candidato_model = new Candidato();
$candidato = $candidato_model->obtenerPorId($id);

if (!$candidato) {
    $_SESSION['error_edicion'] = "Candidato no encontrado.";
    header("Location: lista.php");
    exit;
}

// Verificar que el candidato pertenezca al contexto del usuario
if ($candidato['sede_id'] != $_SESSION['sede_seleccionada'] || 
    $candidato['departamento_id'] != $_SESSION['departamento_seleccionado']) {
    $_SESSION['error_edicion'] = "No tienes permisos para editar este candidato.";
    header("Location: lista.php");
    exit;
}

// Obtener sedes y departamentos
$sede_model = new Sede();
$departamento_model = new Departamento();

$sedes = $sede_model->obtenerTodas();
$departamentos = $departamento_model->obtenerTodosConSede();

// Filtrar departamentos por la sede del usuario
$departamentos_filtrados = array_filter($departamentos, function($dept) {
    return $dept['sede_id'] == $_SESSION['sede_seleccionada'];
});

$mensaje_exito = $_SESSION['candidato_editado'] ?? null;
$mensaje_error = $_SESSION['error_edicion'] ?? null;
unset($_SESSION['candidato_editado'], $_SESSION['error_edicion']);
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
            <h2 class="text-primary mb-0">✏️ Editar Candidato</h2>
            <div>
                <a href="lista.php" class="btn btn-outline-secondary me-2">← Regresar</a>
                <a href="menu.php" class="btn btn-outline-info">🏠 Menú</a>
            </div>
        </div>

        <?php if ($mensaje_exito): ?>
            <div class="alert alert-success text-center fw-bold"><?= htmlspecialchars($mensaje_exito) ?></div>
        <?php elseif ($mensaje_error): ?>
            <div class="alert alert-danger text-center fw-bold"><?= htmlspecialchars($mensaje_error) ?></div>
        <?php endif; ?>

        <form id="formEditarCandidato" method="POST" action="actualizar.php" autocomplete="off">
            <input type="hidden" name="id" value="<?= $candidato['id'] ?>">
            
            <div class="row g-3">
                <!-- Información Personal -->
                <div class="col-12">
                    <h5 class="text-primary border-bottom pb-2">📋 Información Personal</h5>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nombre Completo *</label>
                    <input type="text" name="nombre" class="form-control text-uppercase" 
                           value="<?= htmlspecialchars($candidato['nombre']) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">CURP *</label>
                    <input type="text" name="curp" class="form-control text-uppercase" maxlength="18" required 
                           pattern="[A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z][0-9]" 
                           title="Formato: AAAA000000HAAAAAA00"
                           value="<?= htmlspecialchars($candidato['curp']) ?>">
                    <div class="form-text">Formato: AAAA000000HAAAAAA00</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Edad *</label>
                    <input type="number" name="edad" class="form-control" min="18" max="100" required
                           value="<?= $candidato['edad'] ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Género *</label>
                    <select name="genero" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <option value="Masculino" <?= $candidato['genero'] == 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                        <option value="Femenino" <?= $candidato['genero'] == 'Femenino' ? 'selected' : '' ?>>Femenino</option>
                        <option value="Otro" <?= $candidato['genero'] == 'Otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Teléfono *</label>
                    <input type="tel" name="telefono" class="form-control" pattern="[0-9]{10}" 
                           title="Debe contener 10 dígitos" required
                           value="<?= htmlspecialchars($candidato['telefono']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Correo Electrónico *</label>
                    <input type="email" name="correo" class="form-control" required
                           value="<?= htmlspecialchars($candidato['correo']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Distancia de la Sede de Interés (km)</label>
                    <input type="number" name="distancia_sede" class="form-control" step="0.01" min="0"
                           value="<?= $candidato['distancia_sede'] ?? '' ?>">
                    <div class="form-text">Distancia desde la dirección del candidato hasta la sede solicitada</div>
                </div>

                <div class="col-12">
                    <label class="form-label">Dirección Completa *</label>
                    <textarea name="direccion" rows="3" class="form-control text-uppercase" required><?= htmlspecialchars($candidato['direccion']) ?></textarea>
                </div>

                <!-- Información Académica -->
                <div class="col-12">
                    <h5 class="text-primary border-bottom pb-2 mt-4">🎓 Información Académica</h5>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nivel de Educación *</label>
                    <select name="nivel_educacion" class="form-select" required>
                        <option value="">Seleccionar</option>
                        <option value="PRIMARIA" <?= $candidato['nivel_educacion'] == 'PRIMARIA' ? 'selected' : '' ?>>Primaria</option>
                        <option value="SECUNDARIA" <?= $candidato['nivel_educacion'] == 'SECUNDARIA' ? 'selected' : '' ?>>Secundaria</option>
                        <option value="PREPARATORIA" <?= $candidato['nivel_educacion'] == 'PREPARATORIA' ? 'selected' : '' ?>>Preparatoria</option>
                        <option value="TECNICO" <?= $candidato['nivel_educacion'] == 'TECNICO' ? 'selected' : '' ?>>Técnico</option>
                        <option value="LICENCIATURA" <?= $candidato['nivel_educacion'] == 'LICENCIATURA' ? 'selected' : '' ?>>Licenciatura</option>
                        <option value="INGENIERIA" <?= $candidato['nivel_educacion'] == 'INGENIERIA' ? 'selected' : '' ?>>Ingeniería</option>
                        <option value="MAESTRIA" <?= $candidato['nivel_educacion'] == 'MAESTRIA' ? 'selected' : '' ?>>Maestría</option>
                        <option value="DOCTORADO" <?= $candidato['nivel_educacion'] == 'DOCTORADO' ? 'selected' : '' ?>>Doctorado</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Carrera</label>
                    <input type="text" name="carrera" class="form-control text-uppercase"
                           value="<?= htmlspecialchars($candidato['carrera'] ?? '') ?>">
                    <div class="form-text">Opcional - Solo si aplica</div>
                </div>

                <!-- Información Laboral -->
                <div class="col-12">
                    <h5 class="text-primary border-bottom pb-2 mt-4">💼 Información Laboral</h5>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Área de Experiencia *</label>
                    <input type="text" name="area_experiencia" class="form-control text-uppercase" required
                           value="<?= htmlspecialchars($candidato['area_experiencia']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Años de Experiencia *</label>
                    <input type="number" name="anos_experiencia" class="form-control" min="0" max="50" required
                           value="<?= $candidato['anos_experiencia'] ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">Compañías Previas</label>
                    <textarea name="companias_previas" rows="2" class="form-control text-uppercase" 
                              placeholder="Lista las empresas donde has trabajado anteriormente"><?= htmlspecialchars($candidato['companias_previas'] ?? '') ?></textarea>
                    <div class="form-text">Opcional - Separa con comas</div>
                </div>

                <!-- Área de Solicitud -->
                <div class="col-12">
                    <h5 class="text-primary border-bottom pb-2 mt-4">🎯 Área de Solicitud</h5>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Sede de Interés *</label>
                    <select name="sede_id" class="form-select" id="sedeSelect" required>
                        <option value="">Seleccione una sede</option>
                        <?php foreach ($sedes as $sede): ?>
                            <option value="<?= $sede['id'] ?>" <?= $sede['id'] == $candidato['sede_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sede['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Sede donde el candidato solicita trabajar</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Departamento de Interés *</label>
                    <select name="departamento_id" class="form-select" id="departamentoSelect" required>
                        <option value="">Cargando departamentos...</option>
                    </select>
                    <div id="error-departamentos" class="text-danger mt-1" style="display: none; font-size: 0.9rem;"></div>
                    <div class="form-text">Departamento donde el candidato solicita trabajar</div>
                </div>

                <!-- Estado del Candidato -->
                <div class="col-12">
                    <h5 class="text-primary border-bottom pb-2 mt-4">📊 Estado del Candidato</h5>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Estado *</label>
                    <select name="estado" class="form-select" required>
                        <option value="activo" <?= $candidato['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                        <option value="evaluando" <?= $candidato['estado'] == 'evaluando' ? 'selected' : '' ?>>En Evaluación</option>
                        <option value="contratado" <?= $candidato['estado'] == 'contratado' ? 'selected' : '' ?>>Contratado</option>
                        <option value="rechazado" <?= $candidato['estado'] == 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="lista.php" class="btn btn-outline-secondary">← Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Candidato</button>
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
                    // Marcar como seleccionado si coincide con el departamento actual
                    if (dept.id == <?= $candidato['departamento_id'] ?>) {
                        option.selected = true;
                    }
                    departamentoSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                departamentoSelect.innerHTML = '<option value="">Error al cargar departamentos</option>';
                
                // Mostrar error visual al usuario
                errorDiv.textContent = 'Error al cargar departamentos. Intente nuevamente.';
                errorDiv.style.display = 'block';
                
                // Mostrar mensaje de error más descriptivo en consola
                if (error.message.includes('HTTP error')) {
                    console.error('Error de conexión con el servidor');
                } else if (error.message.includes('Unexpected token')) {
                    console.error('Error en el formato de respuesta del servidor');
                }
            });
    } else {
        departamentoSelect.innerHTML = '<option value="">Primero seleccione una sede</option>';
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';
    }
});

// Validación de CURP
document.querySelector('input[name="curp"]').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
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