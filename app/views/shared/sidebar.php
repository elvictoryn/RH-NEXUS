<?php
// ======= Sidebar compartido (Nexus RH) =======
if (session_status() === PHP_SESSION_NONE) session_start();

$rol = strtolower($_SESSION['rol'] ?? '');
$uri = $_SERVER['REQUEST_URI'] ?? '';

function item($texto, $ruta, $activo = false, $icon = 'bi-dot') {
  $cls = 'list-group-item d-flex align-items-center gap-2';
  if ($activo) $cls .= ' active';
  return '<a class="'.$cls.'" href="'.$ruta.'"><i class="bi '.$icon.'"></i><span>'.$texto.'</span></a>';
}
?>
<aside class="sidebar">
  <div class="d-flex align-items-center gap-2 mb-2">
    <img src="<?= LOGO_URL ?>" alt="logo" height="28" onerror="this.style.display='none'">
    <strong>Nexus RH</strong>
  </div>
  <div class="list-group">
    <?php if ($rol === 'admin'): ?>
      <?= item('Dashboard', BASE_PATH.'/public/admin.php', str_contains($uri,'/public/admin.php'), 'bi-speedometer2') ?>
      <?= item('Usuarios', BASE_PATH.'/app/views/admin/usuarios/lista_usuario.php', str_contains($uri,'/usuarios/'), 'bi-people') ?>
      
      <!-- 🔑 Ajuste: las sedes se encuentran dentro de departamentos -->
      <?= item('Sedes', BASE_PATH.'/app/views/admin/departamentos/lista_sede.php', str_contains($uri,'lista_sede.php'), 'bi-geo-alt') ?>
      
      <?= item('Departamentos', BASE_PATH.'/app/views/admin/departamentos/lista_dep.php', str_contains($uri,'lista_dep.php'), 'bi-diagram-3') ?>
      <?= item('Solicitudes', BASE_PATH.'/app/views/admin/solicitudes/lista.php', str_contains($uri,'/solicitudes/'), 'bi-inboxes') ?>
      <?= item('Evaluaciones', BASE_PATH.'/app/views/admin/evaluaciones/lista.php', str_contains($uri,'/evaluaciones/'), 'bi-graph-up') ?>
      <?= item('Reportes', BASE_PATH.'/app/views/admin/reportes/general.php', str_contains($uri,'/reportes/'), 'bi-bar-chart') ?>
    <?php elseif ($rol === 'rh'): ?>
      <?= item('Solicitudes', BASE_PATH.'/app/views/rh/solicitudes/index.php', str_contains($uri,'/rh/solicitudes/'), 'bi-inboxes') ?>
      <?= item('Evaluaciones', BASE_PATH.'/app/views/rh/evaluaciones/index.php', str_contains($uri,'/rh/evaluaciones/'), 'bi-graph-up') ?>
      <?= item('Candidatos', BASE_PATH.'/app/views/rh/candidatos/index.php', str_contains($uri,'/rh/candidatos/'), 'bi-person-badge') ?>
    <?php elseif ($rol === 'gerente'): ?>
      <?= item('Solicitudes', BASE_PATH.'/app/views/gerente/solicitudes/index.php', str_contains($uri,'/gerente/solicitudes/'), 'bi-inboxes') ?>
      <?= item('Reportes', BASE_PATH.'/app/views/gerente/reportes/index.php', str_contains($uri,'/gerente/reportes/'), 'bi-bar-chart') ?>
    <?php elseif ($rol === 'jefe_area'): ?>
      <?= item('Mis solicitudes', BASE_PATH.'/app/views/jefe_area/solicitudes/index.php', str_contains($uri,'/jefe_area/solicitudes/'), 'bi-inboxes') ?>
      <?= item('Mi departamento', BASE_PATH.'/app/views/jefe_area/departamento/index.php', str_contains($uri,'/jefe_area/departamento/'), 'bi-diagram-3') ?>
    <?php endif; ?>
  </div>
</aside>
