<?php
if (session_status() === PHP_SESSION_NONE) session_start();
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// Datos de sesión
$fotoBase = '/sistema_rh/public/img/usuarios/';
$nombre   = $_SESSION['nombre_completo'] ?? $_SESSION['usuario'] ?? 'Invitad@';
$foto     = $_SESSION['foto'] ?? null;

// (Opcional) marca el link activo desde cada vista, ej. $menu_activo='usuarios'
$menu_activo = $menu_activo ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title><?= isset($titulo_pagina) ? $titulo_pagina . ' - Nexus RH' : 'Nexus RH' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="description" content="<?= isset($descripcion_pagina) ? $descripcion_pagina : 'Sistema de gestión de personal y selección - Nexus RH' ?>">
  <link rel="icon" href="/sistema_rh/public/img/favicon.ico" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="/sistema_rh/public/css/estilo.css" rel="stylesheet">
  <style>
    /* === App Bar moderno con gradiente y sticky === */
    .appbar {
      position: sticky; top: 0; z-index: 1030;
      background: linear-gradient(90deg, #0ea5e9 0%, #6366f1 50%, #a855f7 100%);
      box-shadow: 0 8px 24px rgba(0,0,0,.15);
    }
    .appbar .navbar-brand { letter-spacing:.3px }
    .nav-pill {
      color: #fff !important; border-radius: 999px; padding: .5rem .85rem;
      transition: transform .15s ease, background-color .15s ease, opacity .15s ease;
      opacity:.95;
    }
    .nav-pill:hover { transform: translateY(-1px); background: rgba(255,255,255,.12); opacity:1 }
    .nav-pill.active { background: rgba(255,255,255,.22); }

    /* Chip de usuario */
    .user-chip {
      background: rgba(255,255,255,.18);
      border: 1px solid rgba(255,255,255,.28);
      border-radius: 999px; padding: .25rem .5rem;
      display:flex; align-items:center; gap:.5rem; color:#fff;
    }
    .avatar {
      width: 36px; height: 36px; border-radius: 50%; object-fit: cover;
      border: 2px solid rgba(255,255,255,.75);
    }

    /* Solo íconos en móvil, texto aparece desde lg */
    .label-lg { display:none; }
    @media (min-width: 992px){ .label-lg{ display:inline; } }

    /* Hover sutil en links */
    .navbar-dark .nav-link:hover { text-shadow:0 1px 0 rgba(0,0,0,.08); }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg appbar navbar-dark">
  <div class="container-fluid px-3">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="/sistema_rh/app/views/admin/index.php">
      <i class="fa-solid fa-cubes"></i> Nexus RH
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="topNav">
      <ul class="navbar-nav me-auto mt-2 mt-lg-0 d-flex align-items-lg-center gap-lg-2">
        <li class="nav-item">
          <a class="nav-link nav-pill <?= $menu_activo==='usuarios'?'active':'' ?>"
             href="/sistema_rh/app/views/admin/usuarios/menu.php" aria-current="<?= $menu_activo==='usuarios'?'page':'false' ?>">
            <i class="fas fa-users me-lg-1"></i> <span class="label-lg">Usuarios</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-pill <?= $menu_activo==='departamentos'?'active':'' ?>"
             href="/sistema_rh/app/views/admin/departamentos/menu.php" aria-current="<?= $menu_activo==='departamentos'?'page':'false' ?>">
            <i class="fas fa-sitemap me-lg-1"></i> <span class="label-lg">Departamentos y Sedes</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-pill <?= $menu_activo==='solicitudes'?'active':'' ?>"
             href="/sistema_rh/app/views/admin/solicitudes/menu.php" aria-current="<?= $menu_activo==='solicitudes'?'page':'false' ?>">
            <i class="fas fa-envelope me-lg-1"></i> <span class="label-lg">Solicitudes</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link nav-pill <?= $menu_activo==='candidatos'?'active':'' ?>"
             href="/sistema_rh/app/views/admin/candidatos/menu.php" aria-current="<?= $menu_activo==='candidatos'?'page':'false' ?>">
            <i class="fas fa-check-circle me-lg-1"></i> <span class="label-lg">Candidatos</span>
          </a>
        </li>
      </ul>

      <?php if (isset($_SESSION['usuario'])): ?>
        <div class="dropdown">
          <button class="user-chip dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <?php if (!empty($foto)): ?>
              <img src="<?= e($fotoBase.$foto) ?>" alt="avatar" class="avatar">
            <?php else: ?>
              <i class="fas fa-user-circle fa-lg"></i>
            <?php endif; ?>
            <span class="fw-semibold d-none d-sm-inline"><?= e($nombre) ?></span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow">
            <li><a class="dropdown-item" href="/sistema_rh/app/views/admin/usuarios/perfil.php">
              <i class="fa-regular fa-id-card me-2"></i>Perfil</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="/sistema_rh/public/logout.php">
              <i class="fa-solid fa-right-from-bracket me-2"></i>Cerrar sesión</a></li>
          </ul>
        </div>
      <?php else: ?>
        <a class="btn btn-light btn-sm" href="/sistema_rh/public/login.php">Iniciar sesión</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
