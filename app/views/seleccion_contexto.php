<?php
/**
 * Vista para selección de contexto (sede/departamento) según el rol del usuario
 * Esta vista se muestra cuando el usuario necesita seleccionar su contexto de trabajo
 */

// Incluir sistema de rutas dinámicas
require_once __DIR__ . '/../config/paths.php';

// Incluir sistema de autenticación
safe_require_once(includes_path('auth_helpers.php'));

// Verificar autenticación
usuarioAutenticado();

// Obtener información del usuario y configuración
$usuario = obtenerUsuarioActual();
$config = obtenerConfiguracionRol();
$rol = $usuario['rol'];

// Incluir modelos necesarios
safe_require_once(model_path('Sede'));

// Incluir modelo departamento con manejo de errores
try {
    safe_require_once(model_path('departamento'));
} catch (Exception $e) {
    // Si falla, intentar con ruta directa
    $departamentoPath = __DIR__ . '/../models/departamento.php';
    if (file_exists($departamentoPath)) {
        require_once $departamentoPath;
    } else {
        die("Error: No se pudo cargar el modelo departamento. " . $e->getMessage());
    }
}

// Procesar selección si se envía formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sede_id = $_POST['sede_id'] ?? null;
    $departamento_id = $_POST['departamento_id'] ?? null;
    
    if ($sede_id) {
        $sede_model = new Sede();
        $sede = $sede_model->obtenerPorId($sede_id);
        if ($sede) {
            establecerSedeSeleccionada($sede_id, $sede['nombre']);
        }
    }
    
    if ($departamento_id) {
        $departamento_model = new Departamento();
        $departamento = $departamento_model->obtenerPorId($departamento_id);
        if ($departamento) {
            establecerDepartamentoSeleccionado($departamento_id, $departamento['nombre']);
        }
    }
    
    // Redirigir al dashboard correspondiente
    irDashboard();
}

// Obtener datos para los selectores
$sede_model = new Sede();
$departamento_model = new Departamento();

$sedes = $sede_model->obtenerTodas();
$departamentos = [];

// Si ya hay sede seleccionada, obtener departamentos de esa sede
if (isset($_SESSION['sede_seleccionada'])) {
    $departamentos = $departamento_model->obtenerTodosConSede();
    $departamentos = array_filter($departamentos, function($dept) {
        return $dept['sede_id'] == $_SESSION['sede_seleccionada'];
    });
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selección de Contexto - Nexus RH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="<?= base_url('public/css/estilo.css') ?>" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
    <div class="row w-100">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        Selección de Contexto de Trabajo
                    </h3>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Información del usuario -->
                    <div class="alert alert-info">
                        <h5><i class="fas fa-user me-2"></i><?= $usuario['nombre_rol'] ?></h5>
                        <p class="mb-0"><strong>Usuario:</strong> <?= $usuario['usuario'] ?></p>
                        <p class="mb-0"><strong>Descripción:</strong> <?= $config['descripcion'] ?></p>
                    </div>

                    <!-- Formulario de selección -->
                    <form method="POST" action="">
                        
                        <!-- Selector de Sede -->
                        <?php if ($config['requiere_sede']): ?>
                            <div class="mb-4">
                                <label for="sede_id" class="form-label">
                                    <i class="fas fa-building me-2"></i>
                                    <strong>Sede de Trabajo</strong>
                                    <?php if ($config['requiere_departamento']): ?>
                                        <span class="text-muted">(Selecciona primero la sede)</span>
                                    <?php endif; ?>
                                </label>
                                <select name="sede_id" id="sede_id" class="form-select" required 
                                        <?= $config['requiere_departamento'] ? 'onchange="cargarDepartamentos(this.value)"' : '' ?>>
                                    <option value="">-- Selecciona una sede --</option>
                                    <?php foreach ($sedes as $sede): ?>
                                        <option value="<?= $sede['id'] ?>" 
                                                <?= (isset($_SESSION['sede_seleccionada']) && $_SESSION['sede_seleccionada'] == $sede['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sede['nombre']) ?> - <?= htmlspecialchars($sede['municipio']) ?>, <?= htmlspecialchars($sede['estado']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Esta será tu sede de trabajo principal
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Selector de Departamento -->
                        <?php if ($config['requiere_departamento']): ?>
                            <div class="mb-4">
                                <label for="departamento_id" class="form-label">
                                    <i class="fas fa-sitemap me-2"></i>
                                    <strong>Departamento</strong>
                                </label>
                                <select name="departamento_id" id="departamento_id" class="form-select" required>
                                    <option value="">-- Selecciona un departamento --</option>
                                    <?php if (isset($_SESSION['sede_seleccionada'])): ?>
                                        <?php foreach ($departamentos as $departamento): ?>
                                            <option value="<?= $departamento['id'] ?>"
                                                    <?= (isset($_SESSION['departamento_seleccionado']) && $_SESSION['departamento_seleccionado'] == $departamento['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($departamento['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Este será tu departamento de trabajo
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Información específica por rol -->
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Información Importante</h6>
                            <?php if ($rol === 'admin'): ?>
                                <p class="mb-0">Como <strong>Administrador</strong>, tienes acceso total al sistema sin restricciones de sede o departamento.</p>
                            <?php elseif ($rol === 'gerente'): ?>
                                <p class="mb-0">Como <strong>Gerente</strong>, supervisarás toda la operación de la sede seleccionada.</p>
                            <?php elseif ($rol === 'jefe_area'): ?>
                                <p class="mb-0">Como <strong>Jefe de Área</strong>, trabajarás específicamente en el departamento seleccionado dentro de la sede.</p>
                            <?php elseif ($rol === 'rh'): ?>
                                <p class="mb-0">Como <strong>Recursos Humanos</strong>, gestionarás el personal de la sede y departamento seleccionados.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Botones de acción -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check me-2"></i>
                                Confirmar Selección
                            </button>
                            
                            <a href="<?= base_url('public/logout.php') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Cerrar Sesión
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para cargar departamentos dinámicamente -->
<script>
function cargarDepartamentos(sedeId) {
    if (!sedeId) {
        document.getElementById('departamento_id').innerHTML = '<option value="">-- Selecciona un departamento --</option>';
        return;
    }

    // Simular carga de departamentos (en producción usar AJAX)
    fetch('<?= base_url("app/controllers/getDepartamentos.php") ?>?sede_id=' + sedeId)
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('departamento_id');
            select.innerHTML = '<option value="">-- Selecciona un departamento --</option>';
            
            data.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept.id;
                option.textContent = dept.nombre;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error cargando departamentos:', error);
        });
}

// Si es admin, no requiere selección
<?php if ($rol === 'admin'): ?>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-redirigir al dashboard para admin
    window.location.href = '<?= base_url("app/views/admin/index.php") ?>';
});
<?php endif; ?>
</script>

</body>
</html> 