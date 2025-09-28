<?php
// ======= Navbar compartida (Nexus RH) =======
if (session_status() === PHP_SESSION_NONE) session_start();

// Helper URL
if (!function_exists('u')) {
  function u(string $path): string {
    $bp = defined('BASE_PATH') ? BASE_PATH : '';
    $aliases = [
      'dashboard' => $bp . '/public/dashboard.php',
      'admin'     => $bp . '/public/admin.php',
      'logout'    => $bp . '/public/logout.php',
      'perfil'    => $bp . '/app/views/perfil/index.php',
      'assets'    => $bp . '/public',
    ];
    if (isset($aliases[$path])) return $aliases[$path];
    if (str_starts_with($path, '/')) return $bp . $path;
    return $bp . '/' . ltrim($path,'/');
  }
}

// Activo de navegación
if (!function_exists('activeNav')) {
  function activeNav(string $path): string {
    $req = $_SERVER['REQUEST_URI'] ?? '';
    return str_contains($req, $path) ? 'active' : '';
  }
}

$rol        = strtolower($_SESSION['rol'] ?? '');
$usuario    = $_SESSION['nombre_completo'] ?? ($_SESSION['usuario'] ?? 'Usuario');
$foto       = $_SESSION['foto'] ?? null;
$notifCount = (int)($_SESSION['notif_count'] ?? 0);

$roles_validos = ['admin','rh','gerente','jefe_area'];
$canSolicitudes = in_array($rol, $roles_validos, true);
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top navbar-nexus">
  <div class="container-fluid nav-shell">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= u('dashboard') ?>">
      <img src="<?= defined('LOGO_URL') ? LOGO_URL : '' ?>" alt="Logo" height="28" onerror="this.style.display='none'">
      <span>Nexus RH</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNexus" aria-controls="navbarNexus" aria-expanded="false" aria-label="Menú">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNexus">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <?php if ($canSolicitudes): ?>
          <!-- ====== Solicitudes (UNIVERSAL para todos los roles válidos) ====== -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= activeNav('/solicitudes') ?>" href="#" data-bs-toggle="dropdown">Solicitudes</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= BASE_PATH ?>/public/solicitudes.php">📥 Bandeja</a></li>
              <li><a class="dropdown-item" href="<?= BASE_PATH ?>/public/solicitudes_crear.php">➕ Crear solicitud</a></li>

              <?php if ($rol==='admin'): ?>
                <li><hr class="dropdown-divider"></li>
                <!-- Opciones extra SOLO para admin (manteniendo tus rutas) -->
                <li><a class="dropdown-item" href="<?= u('app/views/admin/solicitudes/menu.php') ?>">🏁 Menú de Solicitudes (admin)</a></li>
                <li><a class="dropdown-item" href="<?= u('app/views/admin/solicitudes/tipos.php') ?>">⚙️ Tipos & Políticas</a></li>
                <li><a class="dropdown-item" href="<?= u('app/views/admin/solicitudes/reportes.php') ?>">📊 Reportes</a></li>
              <?php endif; ?>
            </ul>
          </li>
        <?php endif; ?>

        <?php if ($rol==='admin'): ?>
          <!-- Usuarios -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= activeNav('/usuarios/') ?>" href="#" data-bs-toggle="dropdown">Usuarios</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= u('app/views/admin/usuarios/menu.php') ?>">🏁 Menú de Usuarios</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= u('app/views/admin/usuarios/crear_usuario.php') ?>">➕ Crear usuario</a></li>
              <li><a class="dropdown-item" href="<?= u('app/views/admin/usuarios/lista_usuario.php') ?>">📋 Lista de usuarios</a></li>
            </ul>
          </li>

          <!-- Departamentos & Sedes -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= activeNav('/departamentos/') ?>" href="#" data-bs-toggle="dropdown">Departamentos &amp; Sedes</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= u('app/views/admin/departamentos/menu.php') ?>">🏁 Menú de Deps &amp; Sedes</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= u('app/views/admin/departamentos/crear_dep.php') ?>">➕ Crear departamento</a></li>
              <li><a class="dropdown-item" href="<?= u('app/views/admin/departamentos/lista_dep.php') ?>">📋 Lista de departamentos</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= u('app/views/admin/departamentos/crear_sede.php') ?>">➕ Crear sede</a></li>
              <li><a class="dropdown-item" href="<?= u('app/views/admin/departamentos/lista_sedes.php') ?>">📋 Lista de sedes</a></li>
            </ul>
          </li>

          <!-- Candidatos (registro) -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= activeNav('/candidatos/') ?>" href="#" data-bs-toggle="dropdown">Candidatos</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= u('app/views/admin/candidatos/menu.php') ?>">🏁 Menú de Candidatos</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= u('app/views/admin/candidatos/crear_candidato.php') ?>">➕ Registrar candidato</a></li>
              <li><a class="dropdown-item" href="<?= u('app/views/admin/candidatos/lista_candidatos.php') ?>">📋 Lista de candidatos</a></li>
            </ul>
          </li>

          <!-- Evaluaciones de candidatos -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= activeNav('/evaluaciones/') ?>" href="#" data-bs-toggle="dropdown">Evaluaciones</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= u('app/views/admin/evaluaciones/menu.php') ?>">🏁 Menú de Evaluaciones</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= u('app/views/admin/evaluaciones/plantillas.php') ?>">📄 Plantillas</a></li>
              <li><a class="dropdown-item" href="<?= u('app/views/admin/evaluaciones/aplicaciones.php') ?>">🧪 Aplicaciones</a></li>
              <li><a class="dropdown-item" href="<?= u('app/views/admin/evaluaciones/resultados.php') ?>">📈 Resultados</a></li>
            </ul>
          </li>

          <!-- Reportes (globales) -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= activeNav('/reportes/') ?>" href="#" data-bs-toggle="dropdown">Reportes</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= u('app/views/admin/reportes/general.php') ?>">📊 General</a></li>
              <li><a class="dropdown-item" href="<?= u('app/views/admin/reportes/descargas.php') ?>">⬇️ Descargas</a></li>
              <li><a class="dropdown-item" href="<?= u('app/views/admin/reportes/auditoria.php') ?>">🧾 Auditoría</a></li>
            </ul>
          </li>

          <!-- Configuración -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= activeNav('/config/') ?>" href="#" data-bs-toggle="dropdown">Configuración</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= u('app/views/admin/config/parametros.php') ?>">⚙️ Parámetros</a></li>
              <li><a class="dropdown-item" href="<?= u('app/views/admin/config/catálogos.php') ?>">📚 Catálogos</a></li>
              <li><a class="dropdown-item" href="<?= u('app/views/admin/config/seguridad.php') ?>">🛡️ Seguridad</a></li>
            </ul>
          </li>
        <?php endif; ?>

        <?php if ($rol==='rh'): ?>
          <!-- (Quitamos el menú de solicitudes específico RH para evitar duplicados.
               Ahora usan el dropdown universal de arriba) -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= activeNav('/candidatos/') ?>" href="#" data-bs-toggle="dropdown">Candidatos</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= u('app/views/rh/candidatos/crear_candidato.php') ?>">➕ Registrar candidato</a></li>
              <li><a class="dropdown-item" href="<?= u('app/views/rh/candidatos/lista_candidatos.php') ?>">📋 Lista de candidatos</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= activeNav('/evaluaciones/') ?>" href="#" data-bs-toggle="dropdown">Evaluaciones</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= u('app/views/rh/evaluaciones/aplicaciones.php') ?>">🧪 Aplicaciones</a></li>
              <li><a class="dropdown-item" href="<?= u('app/views/rh/evaluaciones/resultados.php') ?>">📈 Resultados</a></li>
            </ul>
          </li>
          <li class="nav-item"><a class="nav-link <?= activeNav('/reportes/') ?>" href="<?= u('app/views/rh/reportes/index.php') ?>">Reportes</a></li>
        <?php endif; ?>

        <?php if ($rol==='gerente'): ?>
          <!-- (Quitamos el menú de solicitudes específico Gerente para evitar duplicados) -->
          <li class="nav-item"><a class="nav-link <?= activeNav('/gerente/reportes/') ?>" href="<?= u('app/views/gerente/reportes/index.php') ?>">Reportes</a></li>
        <?php endif; ?>

        <?php if ($rol==='jefe_area'): ?>
          <!-- (Quitamos el menú de solicitudes específico Jefe de área para evitar duplicados) -->
          <!-- Podrías dejar otros accesos propios si los tienes -->
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-2">
        <!-- Notificaciones -->
        <li class="nav-item dropdown">
          <a class="nav-link notif-icon" href="#" data-bs-toggle="dropdown" title="Notificaciones">
            <i class="bi bi-bell fs-5"></i>
            <?php if ($notifCount>0): ?><span class="notif-badge"><?= $notifCount ?></span><?php endif; ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width:280px">
            <li class="dropdown-header fw-bold">Notificaciones</li>
            <li><a class="dropdown-item small" href="#">Nueva solicitud aprobada</a></li>
            <li><a class="dropdown-item small" href="#">Comentario en Solicitud #124</a></li>
            <li><a class="dropdown-item small" href="#">Backup manual completado</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-center small" href="#">Ver todas</a></li>
          </ul>
        </li>

        <!-- Usuario -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
            <?php if ($foto): ?>
              <img class="nav-avatar me-2" src="<?= u('public/img/usuarios/'.rawurlencode($foto)) ?>" alt="avatar">
            <?php else: ?>
              <span class="me-2">👤</span>
            <?php endif; ?>
            <?= htmlspecialchars($usuario) ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li class="dropdown-header"><?= ucfirst($rol ?: 'Invitado') ?></li>
            <li><a class="dropdown-item" href="<?= u('perfil') ?>"><i class="bi bi-person-badge me-2"></i>Mi perfil</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= u('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Salir</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<script>
// Fija --nav-h con la altura real del navbar (para inspector/secciones que dependen de esa variable)
(function(){
  function setNavH(){
    var nav = document.querySelector('.navbar-nexus');
    if (!nav) return;
    var h = nav.getBoundingClientRect().height || nav.offsetHeight || 64;
    document.documentElement.style.setProperty('--nav-h', h + 'px');
  }
  document.addEventListener('DOMContentLoaded', setNavH);
  window.addEventListener('resize', setNavH);
})();
</script>
