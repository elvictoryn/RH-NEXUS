<?php
// ======= Navbar compartida (Nexus RH) =======
if (session_status() === PHP_SESSION_NONE) session_start();

if (!function_exists('u')) {
  function u(string $path): string {
    $bp = defined('BASE_PATH') ? BASE_PATH : '';
    $aliases = [
      'dashboard' => $bp . '/public/dashboard.php',
      'admin'     => $bp . '/public/admin.php',
      'logout'    => $bp . '/public/logout.php',
      'perfil'    => $bp . '/app/views/perfil/index.php',
      'assets'    => $bp . '/public',
      'agenda'    => $bp . '/public/agenda.php',
    ];
    if (isset($aliases[$path])) return $aliases[$path];
    if (str_starts_with($path, '/')) return $bp . $path;
    return $bp . '/' . ltrim($path,'/');
  }
}
if (!function_exists('activeNav')) {
  function activeNav(string $path): string {
    $req = $_SERVER['REQUEST_URI'] ?? '';
    return str_contains($req, $path) ? 'active' : '';
  }
}

$rol     = strtolower($_SESSION['rol'] ?? '');
$usuario = $_SESSION['nombre_completo'] ?? ($_SESSION['usuario'] ?? 'Usuario');
$foto    = $_SESSION['foto'] ?? null;
?>
<style>
.ntf-menu{
  width:min(560px,96vw); padding:0; border-radius:16px; overflow:hidden;
  background:#0a1326; color:#dbe8ff; border:1px solid rgba(255,255,255,.12);
  box-shadow:0 28px 90px rgba(0,0,0,.45), inset 0 1px 0 rgba(255,255,255,.06);
}
.ntf-head{ display:flex; align-items:center; justify-content:space-between; gap:10px;
  padding:.7rem .9rem; background:linear-gradient(180deg,#0c1a33,#0b152a); border-bottom:1px solid rgba(255,255,255,.10); }
.ntf-title{ margin:0; font-weight:900; color:#eaf2ff; letter-spacing:.25px }
.ntf-body{ max-height:420px; overflow:auto; }
.ntf-group{ padding:.45rem .9rem; font-weight:900; color:#8ea4d2; font-size:.82rem; background:#0b162d }
.ntf-list a.ntf-item{ display:grid; grid-template-columns:44px 1fr auto; gap:12px; align-items:center;
  padding:.78rem .9rem; color:#dbe8ff; text-decoration:none; border-bottom:1px solid rgba(255,255,255,.06); }
.ntf-list a.ntf-item:hover{ background:#0c1b36 }
.ntf-ava{ width:44px;height:44px;border-radius:50%; display:grid;place-items:center;color:#fff;font-weight:900;
  background:conic-gradient(from 120deg,#0ea5e9,#6366f1,#22c55e,#0ea5e9) }
.ntf-ava img{ width:100%;height:100%;object-fit:cover;border-radius:50% }
.ntf-line{ margin:0; line-height:1.35; font-size:.98rem }
.ntf-line b{ font-weight:900; color:#fff }
.ntf-sub{ color:#9fb0d6; font-size:.86rem; margin-top:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap }
.ntf-right{ color:#8aa0c1; font-size:.82rem; display:flex; align-items:center; gap:10px }
.navbar-nexus .notif-icon{ position:relative }
.ping{ position:absolute; top:0; right:0; width:8px; height:8px; border-radius:999px; background:#22c55e;
  box-shadow:0 0 0 0 rgba(34,197,94,.7); animation:ping 1.8s infinite; }
@keyframes ping { 0%{ box-shadow:0 0 0 0 rgba(34,197,94,.7) } 100%{ box-shadow:0 0 0 12px rgba(34,197,94,0) } }
</style>

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
        <?php endif; ?>

        <!-- Solicitudes -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= activeNav('/solicitudes') ?>" href="#" data-bs-toggle="dropdown">Solicitudes</a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="<?= BASE_PATH ?>/public/solicitudes.php">📥 Bandeja</a></li>
            <li><a class="dropdown-item" href="<?= BASE_PATH ?>/public/solicitudes_crear.php">➕ Crear solicitud</a></li>
          </ul>
        </li>

        <!-- Candidatos -->
        <?php if (in_array($rol, ['admin','rh'], true)): ?>
          <li class="nav-item">
            <a class="nav-link <?= activeNav('/candidatos/') ?>" href="<?= BASE_PATH ?>/app/views/admin/candidatos/index.php">Candidatos</a>
          </li>
        <?php endif; ?>

        <!-- Agenda (TODOS LOS ROLES) -->
        <li class="nav-item">
          <a class="nav-link <?= activeNav('/public/agenda.php') ?>" href="<?= u('agenda') ?>">Agenda</a>
        </li>

        <!-- Reportes -->
        <?php if ($rol==='admin'): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= activeNav('/app/views/admin/reportes/') ?>" href="#" data-bs-toggle="dropdown">Reportes</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= u('app/views/admin/reportes/general.php') ?>">📊 General</a></li>
            </ul>
          </li>
        <?php elseif ($rol==='rh'): ?>
          <li class="nav-item">
            <a class="nav-link <?= activeNav('/app/views/admin/reportes/general.php') ?>" href="<?= u('app/views/admin/reportes/general.php') ?>">Reportes</a>
          </li>
        <?php elseif ($rol==='gerente'): ?>
          <li class="nav-item"><a class="nav-link <?= activeNav('/gerente/reportes/') ?>" href="<?= u('app/views/gerente/reportes/index.php') ?>">Reportes</a></li>
        <?php endif; ?>

      </ul>

      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-2">
        <!-- Campana -->
        <li class="nav-item dropdown">
          <a class="nav-link notif-icon" id="nbBell" href="#" data-bs-toggle="dropdown" aria-expanded="false" title="Notificaciones">
            <i class="bi bi-bell fs-5"></i>
            <span id="nbPing" class="ping" style="display:none"></span>
          </a>
          <div class="dropdown-menu dropdown-menu-end ntf-menu" aria-labelledby="nbBell">
            <div class="ntf-head">
              <h6 class="ntf-title">Notificaciones</h6>
            </div>
            <div id="nbBody" class="ntf-body">
              <div class="p-3 text-secondary">Cargando…</div>
            </div>
          </div>
        </li>

        <!-- Usuario -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
            <?php if ($foto): ?>
              <img class="nav-avatar me-2" src="<?= u('public/img/usuarios/'.rawurlencode($foto)) ?>" alt="avatar" style="width:28px;height:28px;border-radius:50%;object-fit:cover">
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
/* Fija --nav-h */
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

/* Campana: notificaciones desde actividad */
(function(){
  const API='<?= BASE_PATH ?>/app/actions/notificaciones.php';
  const body=document.getElementById('nbBody');
  const ping=document.getElementById('nbPing');
  let loaded=false, state={items:[]};

  const toAgo=(ts)=>{ const t=Date.parse(ts); if(!isFinite(t)) return '';
    const s=(Date.now()-t)/1000;
    if(s<60) return 'ahora'; if(s<3600) return Math.floor(s/60)+' min';
    if(s<86400) return Math.floor(s/3600)+' h';
    const d=new Date(t); return d.toLocaleDateString();
  };
  const kindIcon=(txt)=>{
    const t=(txt||'').toLowerCase();
    if(t.includes('coment')) return '💬';
    if(t.includes('aprob'))  return '✅';
    if(t.includes('rechaz')) return '⛔';
    return '🔔';
  };
  const line=(it)=>{
    const who=it.autor_nombre || it.autor_usuario || 'Alguien';
    return `${kindIcon(it.accion||it.comentario)} <b>${who}</b> ${it.accion||'actualizó'} en <b>Solicitud ID-${it.solicitud_id}</b>${it.comentario?':':''}`;
  };
  const href=(it)=> it.url || '#';
  const itemHTML=(it)=>{
    const ini=(it.autor_nombre||it.autor_usuario||'U').slice(0,1).toUpperCase();
    const foto=it.fotografia_url||'';
    const sub=(it.comentario||'').replace(/\s+/g,' ').trim();
    return `
      <a class="ntf-item" href="${href(it)}">
        <div class="ntf-ava">${foto?`<img src="${foto}" alt="">`:ini}</div>
        <div>
          <p class="ntf-line">${line(it)}</p>
          ${sub?`<div class="ntf-sub">“${sub}”</div>`:''}
        </div>
        <div class="ntf-right"><span>${toAgo(it.creado_en)}</span></div>
      </a>`;
  };
  const group=(arr)=>{
    const today=new Date(); today.setHours(0,0,0,0);
    const g={ 'Hoy':[], 'Anteriores':[] };
    arr.forEach(it=>{
      const d=new Date(it.creado_en||Date.now());
      const k=new Date(d); k.setHours(0,0,0,0);
      if(k.getTime()===today.getTime()) g['Hoy'].push(it); else g['Anteriores'].push(it);
    });
    return g;
  };
  const render=()=>{
    const arr = state.items;
    ping.style.display = arr.length ? 'block' : 'none';
    if(!arr.length){ body.innerHTML='<div class="p-3 text-secondary">Sin notificaciones.</div>'; return; }
    const g=group(arr);
    const sec=(t,a)=> a.length? `<div class="ntf-group">${t}</div><div class="ntf-list">${a.map(itemHTML).join('')}</div>` : '';
    body.innerHTML=sec('Hoy',g['Hoy'])+sec('Anteriores',g['Anteriores']);
  };
  async function load(){
    try{
      const res=await fetch(API,{credentials:'same-origin'});
      const j=await res.json();
      state.items=j.items||[];
      render();
    }catch(e){ body.innerHTML='<div class="p-3 text-danger">Error al cargar notificaciones.</div>'; }
  }
  document.addEventListener('shown.bs.dropdown', ev=>{
    if(ev.target.id==='nbBell'){ ping.style.display='none'; if(!loaded){ loaded=true; load(); } }
  });
  setInterval(()=>{ if(loaded) load(); }, 45000);
})();
</script>
