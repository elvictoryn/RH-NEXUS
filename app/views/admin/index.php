<?php
// ================================
// Panel de Administración - Nexus RH
// Archivo: /app/views/admin/index.php
// ================================
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/sistema_rh');
}
$tituloPagina = 'Administrador | Nexus RH';

// Carga header (inicia sesión si falta + conexión BD + <head>)
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/header.php';

// Seguridad: si no hay sesión, manda a login y corta (aquí ya hay session_start en header)
if (!isset($_SESSION['usuario'])) {
    header('Location: ' . BASE_PATH . '/public/login.php');
    exit();
}

// Ya tenemos $pdo disponible desde header.php (Conexion::getConexion())

try { $pdo = Conexion::getConexion(); } catch (Throwable $e) { $pdo = null; }

// ---------- Helpers locales ----------
function contarActivos(PDO $pdo, string $tabla): int {
    try {
        $tieneActivo = false;
        $stmtCols = $pdo->query("DESCRIBE {$tabla}");
        foreach ($stmtCols as $col) {
            if (strcasecmp($col['Field'], 'activo') === 0) { $tieneActivo = true; break; }
        }
        $sql = $tieneActivo ? "SELECT COUNT(*) c FROM {$tabla} WHERE activo=1"
                            : "SELECT COUNT(*) c FROM {$tabla}";
        $row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        return (int)($row['c'] ?? 0);
    } catch (Throwable $e) { return 0; }
}

function ultimosRegistros(PDO $pdo, string $tabla, $prefFechas=['creado_en','created_at','fecha_creacion'], $limit=5): array {
    try {
        $cols = $pdo->query("DESCRIBE {$tabla}")->fetchAll(PDO::FETCH_COLUMN);
        $fechaCol = null;
        foreach ($prefFechas as $c) {
            foreach ($cols as $real) { if (strcasecmp($real,$c)===0) { $fechaCol=$real; break 2; } }
        }
        $nombreCol = null;
        foreach (['usuario','nombre_completo','nombre','departamento','sede','titulo'] as $c) {
            foreach ($cols as $real) { if (strcasecmp($real,$c)===0) { $nombreCol=$real; break 2; } }
        }
        $idCol = in_array('id',$cols,true) ? 'id' : $cols[0];
        $select = "SELECT {$idCol} AS id" . ($nombreCol ? ", {$nombreCol} AS nombre" : "");
        if ($fechaCol) {
            $sql = "$select, {$fechaCol} AS fecha FROM {$tabla} ORDER BY {$fechaCol} DESC LIMIT {$limit}";
        } else {
            $sql = "$select FROM {$tabla} ORDER BY {$idCol} DESC LIMIT {$limit}";
        }
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) { return []; }
}

function linkSiExiste(string $rutaRel, string $texto, string $clase='btn btn-sm btn-primary'): string {
    $fsFull = $_SERVER['DOCUMENT_ROOT'] . $rutaRel;
    if (file_exists($fsFull)) return '<a href="'. $rutaRel .'" class="'. $clase .'">'. $texto .'</a>';
    return '<button class="'. $clase .'" disabled title="Archivo no encontrado">'. $texto .'</button>';
}

// ---------- Datos ----------
$conteoUsuarios      = $pdo ? contarActivos($pdo, 'usuarios')      : 0;
$conteoSedes         = $pdo ? contarActivos($pdo, 'sedes')         : 0;
$conteoDepartamentos = $pdo ? contarActivos($pdo, 'departamentos') : 0;

$ultUsuarios      = $pdo ? ultimosRegistros($pdo, 'usuarios')      : [];
$ultSedes         = $pdo ? ultimosRegistros($pdo, 'sedes')         : [];
$ultDepartamentos = $pdo ? ultimosRegistros($pdo, 'departamentos') : [];

// Rutas modulares reales
$rCrearUsuario  = BASE_PATH . "/app/views/admin/usuarios/crear_usuario.php";
$rListaUsuarios = BASE_PATH . "/app/views/admin/usuarios/lista_usuario.php";
$rCrearSede     = BASE_PATH . "/app/views/admin/departamentos/crear_sede.php";
$rListaSede     = BASE_PATH . "/app/views/admin/departamentos/lista_sedes.php";
$rCrearDep      = BASE_PATH . "/app/views/admin/departamentos/crear_dep.php";
$rListaDep      = BASE_PATH . "/app/views/admin/departamentos/lista_dep.php";
?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/navbar.php'; ?>

<main class="container my-4">
  <h1 class="h3 mb-4">Panel de Administración</h1>

  <!-- Tarjetas -->
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card card-counter h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h2 class="display-5 mb-0"><?php echo $conteoUsuarios; ?></h2>
              <p class="text-muted mb-2">Usuarios activos</p>
            </div>
            <span class="badge bg-primary">Usuarios</span>
          </div>
          <div class="d-flex gap-2">
            <?php
              echo linkSiExiste($rCrearUsuario, 'Crear', 'btn btn-sm btn-success');
              echo linkSiExiste($rListaUsuarios, 'Ver lista', 'btn btn-sm btn-outline-primary');
            ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card card-counter h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h2 class="display-5 mb-0"><?php echo $conteoSedes; ?></h2>
              <p class="text-muted mb-2">Sedes activas</p>
            </div>
            <span class="badge bg-success">Sedes</span>
          </div>
          <div class="d-flex gap-2">
            <?php
              echo linkSiExiste($rCrearSede, 'Crear', 'btn btn-sm btn-success');
              echo linkSiExiste($rListaSede, 'Ver lista', 'btn btn-sm btn-outline-success');
            ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card card-counter h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <h2 class="display-5 mb-0"><?php echo $conteoDepartamentos; ?></h2>
              <p class="text-muted mb-2">Departamentos activos</p>
            </div>
            <span class="badge bg-info text-dark">Departamentos</span>
          </div>
          <div class="d-flex gap-2">
            <?php
              echo linkSiExiste($rCrearDep, 'Crear', 'btn btn-sm btn-success');
              echo linkSiExiste($rListaDep, 'Ver lista', 'btn btn-sm btn-outline-info');
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Actividad reciente -->
  <div class="row g-3">
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-header bg-white fw-semibold">Usuarios recientes</div>
        <div class="card-body">
          <?php if ($ultUsuarios): ?>
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead><tr><th>ID</th><th>Usuario/Nombre</th><th>Fecha</th></tr></thead>
                <tbody>
                  <?php foreach ($ultUsuarios as $u): ?>
                    <tr>
                      <td><?php echo htmlspecialchars((string)$u['id']); ?></td>
                      <td><?php echo htmlspecialchars($u['nombre'] ?? '—'); ?></td>
                      <td><?php echo htmlspecialchars($u['fecha'] ?? '—'); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?><p class="text-muted mb-0">Sin datos para mostrar.</p><?php endif; ?>
        </div>
        <div class="card-footer">
          <?php echo linkSiExiste($rListaUsuarios, 'Ir a usuarios', 'btn btn-sm btn-primary'); ?>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-header bg-white fw-semibold">Sedes recientes</div>
        <div class="card-body">
          <?php if ($ultSedes): ?>
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead><tr><th>ID</th><th>Nombre</th><th>Fecha</th></tr></thead>
                <tbody>
                  <?php foreach ($ultSedes as $s): ?>
                    <tr>
                      <td><?php echo htmlspecialchars((string)$s['id']); ?></td>
                      <td><?php echo htmlspecialchars($s['nombre'] ?? '—'); ?></td>
                      <td><?php echo htmlspecialchars($s['fecha'] ?? '—'); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?><p class="text-muted mb-0">Sin datos para mostrar.</p><?php endif; ?>
        </div>
        <div class="card-footer">
          <?php echo linkSiExiste($rListaSede, 'Ir a sedes', 'btn btn-sm btn-success'); ?>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-header bg-white fw-semibold">Departamentos recientes</div>
        <div class="card-body">
          <?php if ($ultDepartamentos): ?>
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead><tr><th>ID</th><th>Nombre</th><th>Fecha</th></tr></thead>
                <tbody>
                  <?php foreach ($ultDepartamentos as $d): ?>
                    <tr>
                      <td><?php echo htmlspecialchars((string)$d['id']); ?></td>
                      <td><?php echo htmlspecialchars($d['nombre'] ?? '—'); ?></td>
                      <td><?php echo htmlspecialchars($d['fecha'] ?? '—'); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?><p class="text-muted mb-0">Sin datos para mostrar.</p><?php endif; ?>
        </div>
        <div class="card-footer">
          <?php echo linkSiExiste($rListaDep, 'Ir a departamentos', 'btn btn-sm btn-info'); ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/footer.php'; ?>
