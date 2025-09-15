<?php
if (!isset($_SESSION)) session_start();
$titulo_pagina = "Nexus RH · Panel";
include_once('../../shared/header.php');
?>
<style>
/* ====== PALETA ====== */
:root{
  --ink:#102a43; --muted:#5d6f82;
  --line:#e6e9ef; --panel:#ffffff; --bg:#f5f7fb;
  --brand:#0D6EFD; --brand2:#7c4dff; --mint:#09BC8A; --pink:#ff5ea8;
}

/* ====== RESETEOS BREVES ====== */
body{ background:var(--bg); }
a{ text-decoration:none }

/* ====== TOPBAR (estilo del ejemplo) ====== */
.topbar{
  position:relative; z-index:5;
  background: radial-gradient(1200px 420px at -10% -40%, #7bd3ff 0%, transparent 60%),
              linear-gradient(90deg, #2e7bff, #6f77ff 55%, #8e5bff);
  color:#fff;
  padding:10px 0;
  box-shadow:0 6px 18px rgba(0,0,0,.12);
}
.nav-wrap{ max-width:1200px; margin:auto; padding:0 16px; display:flex; align-items:center; gap:14px }
.brand{ display:flex; align-items:center; gap:10px; font-weight:900; font-size:1.1rem }
.brand .logo{ width:34px; height:34px; border-radius:9px; background:#fff; display:grid; place-items:center; color:#2e7bff; font-weight:900 }
.nav{ display:none; gap:18px; margin-left:14px }
.nav a{ color:#eaf1ff; font-weight:700 }
.nav a:hover{ color:#fff }
@media (min-width: 992px){ .nav{ display:flex } }

.search{ margin-left:auto; display:flex; gap:8px; align-items:center }
.search input{
  height:38px; border:none; border-radius:10px; padding:0 12px; width:180px;
}
.cta-login{ height:38px; padding:0 14px; border-radius:10px; font-weight:800; color:#2e7bff; background:#fff; border:none }

/* ====== HERO ====== */
.hero{
  max-width:1200px; margin:22px auto 24px; padding:0 16px;
  display:grid; gap:18px; grid-template-columns:1fr; align-items:stretch;
}
@media (min-width: 992px){ .hero{ grid-template-columns: 1.15fr .85fr } }

/* “feature card” a la izquierda (tarjeta visual) */
.card-visual{
  background:linear-gradient(135deg,#233852,#314a6a);
  border-radius:18px; padding:18px; color:#fff; box-shadow:0 12px 28px rgba(0,0,0,.18);
  display:flex; flex-direction:column; justify-content:space-between; min-height:220px;
}
.card-visual .fake-card{
  height:120px; border-radius:12px;
  background:linear-gradient(135deg,#4d6b93,#24364d 65%, #5f8de8);
  box-shadow:inset 0 0 0 2px rgba(255,255,255,.12);
  margin-top:10px;
}

/* texto + CTAs (derecha en desktop) */
.card-hero{
  background:var(--panel); border:1px solid var(--line); border-radius:18px;
  padding:18px; box-shadow:0 10px 24px rgba(0,0,0,.08);
}
.card-hero h1{ margin:0; font-weight:900; color:var(--ink); font-size:clamp(1.3rem,2vw + .7rem,2rem) }
.card-hero p{ color:var(--muted); margin:.4rem 0 1rem }
.chips{ display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px }
.chip{ padding:.3rem .6rem; border-radius:999px; background:#eef4ff; color:#2b4f87; font-weight:800; font-size:.82rem; border:1px solid #d6e4ff }
.btn{ height:44px; font-weight:800; border-radius:10px }
.btn-primary{ background:var(--brand); border-color:var(--brand) }
.btn-primary:hover{ filter:brightness(.95) }
.btn-outline-dark{ background:#fff }

/* ====== BLOQUE: Submenús tipo “cards” (como el carrusel inferior del ejemplo) ====== */
.section{
  max-width:1200px; margin:10px auto 12px; padding:0 16px;
}
.section h2{ text-align:center; color:var(--ink); font-weight:900; font-size:clamp(1.2rem,1.6vw + .6rem,1.6rem); margin:16px 0 }
.cards{
  display:grid; gap:16px; grid-template-columns:1fr;
}
@media (min-width: 768px){ .cards{ grid-template-columns:repeat(2,1fr) } }
@media (min-width: 1200px){ .cards{ grid-template-columns:repeat(3,1fr) } }

.card-mini{
  border:1px solid var(--line); border-radius:16px; background:var(--panel);
  padding:14px; box-shadow:0 8px 20px rgba(0,0,0,.06);
  display:flex; gap:12px; align-items:center; justify-content:space-between;
}
.card-mini .left{
  display:flex; gap:12px; align-items:center;
}
.card-mini .ico{
  width:46px; height:46px; border-radius:12px; color:#fff; font-size:1.1rem;
  display:grid; place-items:center;
  background:linear-gradient(135deg,var(--brand),#6ea8fe);
  box-shadow:0 6px 16px rgba(13,110,253,.25);
}
.card-mini.mint .ico{ background:linear-gradient(135deg,var(--mint),#57e2c8) }
.card-mini.violet .ico{ background:linear-gradient(135deg,var(--brand2),#a88bff) }
.card-mini.pink .ico{ background:linear-gradient(135deg,var(--pink),#ff96c8) }

.card-mini .h{ margin:0; color:var(--ink); font-weight:900 }
.card-mini .d{ margin:0; color:var(--muted); font-weight:600; font-size:.92rem }

.card-mini .cta{ display:flex; gap:8px; flex-wrap:wrap }
.cta .btn{ height:40px; border-radius:10px }

/* ====== BLOQUE: Catálogos principales (Departamentos / Sedes) ====== */
.catalog{
  max-width:1200px; margin:18px auto 40px; padding:0 16px;
}
.catalog .grid{ display:grid; gap:18px; grid-template-columns:1fr }
@media (min-width: 992px){ .catalog .grid{ grid-template-columns:1fr 1fr } }

.big-card{
  position:relative; border:1px solid var(--line); background:var(--panel); border-radius:18px; overflow:hidden;
  box-shadow:0 10px 26px rgba(0,0,0,.07);
}
.big-card .accent{ height:5px; background:var(--brand) }
.big-card.mint .accent{ background:var(--mint) }
.big-card .body{ padding:18px }
.big-card .title{ margin:0; display:flex; gap:.6rem; align-items:center; color:var(--ink); font-weight:900; font-size:1.12rem }
.big-card .badge-ico{ width:40px; height:40px; border-radius:10px; color:#fff; display:grid; place-items:center; background:linear-gradient(135deg,var(--brand),#6ea8fe) }
.big-card.mint .badge-ico{ background:linear-gradient(135deg,var(--mint),#57e2c8) }
.big-card .text{ color:var(--muted); margin:.35rem 0 .65rem }
.big-card .actions{ display:grid; grid-template-columns:1fr; gap:8px; max-width:420px }
</style>

<!-- ====== TOPBAR ====== -->
<header class="topbar">
  <div class="nav-wrap">
    <div class="brand">
      <div class="logo">NRH</div>
      Nexus RH
    </div>
    <nav class="nav" aria-label="Navegación principal">
      <a href="#">Inicio</a>
      <a href="#">RRHH</a>
      <a href="#">Catálogos</a>
      <a href="#">Reportes</a>
      <a href="#">Ajustes</a>
    </nav>
    <div class="search">
      <input type="search" placeholder="Buscar…">
      <button class="cta-login">Entrar</button>
    </div>
  </div>
</header>

<!-- ====== HERO ====== -->
<section class="hero">
  <!-- Tarjeta visual (izquierda) -->
  <div class="card-visual">
    <div>
      <strong style="opacity:.85">Resumen rápido</strong>
      <div style="display:flex; gap:8px; margin-top:8px; flex-wrap:wrap">
        <span class="chip">🧩 Deps</span>
        <span class="chip">🏢 Sedes</span>
        <span class="chip">👥 Empleados</span>
      </div>
    </div>
    <div class="fake-card"></div>
  </div>

  <!-- Texto + CTAs (derecha) -->
  <div class="card-hero">
    <h1>Gestiona todo con MyHR Nexus</h1>
    <p>Catálogos claros, procesos rápidos y una interfaz que no estorba. Crea, edita y consulta sin perderte.</p>
    <div class="chips">
      <span class="chip">Catálogos</span>
      <span class="chip">Reportes</span>
      <span class="chip">Accesos rápidos</span>
    </div>
    <div style="display:flex; gap:10px; flex-wrap:wrap">
      <a href="lista_dep.php" class="btn btn-primary">📄 Ver Departamentos</a>
      <a href="lista_sedes.php" class="btn btn-outline-dark">🗂 Ver Sedes</a>
    </div>
  </div>
</section>

<!-- ====== SUBMENÚS (cards pequeñas) ====== -->
<section class="section">
  <h2>Nuestros módulos</h2>
  <div class="cards">

    <article class="card-mini">
      <div class="left">
        <div class="ico">🧩</div>
        <div>
          <p class="h">Departamentos</p>
          <p class="d">Estructura de áreas y responsables.</p>
        </div>
      </div>
      <div class="cta">
        <a href="crear_dep.php" class="btn btn-primary">➕ Crear</a>
        <a href="lista_dep.php" class="btn btn-outline-dark">Ver</a>
      </div>
    </article>

    <article class="card-mini mint">
      <div class="left">
        <div class="ico">🏢</div>
        <div>
          <p class="h">Sedes</p>
          <p class="d">Ubicaciones y datos de contacto.</p>
        </div>
      </div>
      <div class="cta">
        <a href="crear_sede.php" class="btn btn-success" style="background:var(--mint); border-color:var(--mint)">➕ Crear</a>
        <a href="lista_sedes.php" class="btn btn-outline-dark">Ver</a>
      </div>
    </article>

    <article class="card-mini violet">
      <div class="left">
        <div class="ico">👥</div>
        <div>
          <p class="h">Empleados</p>
          <p class="d">Altas, bajas y expedientes.</p>
        </div>
      </div>
      <div class="cta">
        <a href="#" class="btn btn-primary" style="background:var(--brand2); border-color:var(--brand2)">Entrar</a>
      </div>
    </article>

  </div>
</section>

<!-- ====== CATÁLOGOS PRINCIPALES (cards grandes) ====== -->
<section class="catalog">
  <h2 style="text-align:center; color:var(--ink); font-weight:900; margin-bottom:8px">Catálogos clave</h2>
  <div class="grid">

    <article class="big-card">
      <div class="accent"></div>
      <div class="body">
        <h3 class="title"><span class="badge-ico">📁</span> Departamentos</h3>
        <p class="text">Crea, edita o elimina departamentos. Asigna responsables y controla su información.</p>
        <div class="actions">
          <a href="crear_dep.php" class="btn btn-outline-primary">➕ Nuevo Departamento</a>
          <a href="lista_dep.php" class="btn btn-primary">📄 Ver Departamentos</a>
        </div>
      </div>
    </article>

    <article class="big-card mint">
      <div class="accent"></div>
      <div class="body">
        <h3 class="title"><span class="badge-ico">🏢</span> Sedes</h3>
        <p class="text">Registra ubicaciones y datos de contacto con consistencia.</p>
        <div class="actions">
          <a href="crear_sede.php" class="btn btn-outline-success" style="--bs-border-color:var(--mint)">➕ Nueva Sede</a>
          <a href="lista_sedes.php" class="btn btn-success" style="background:var(--mint); border-color:var(--mint)">📄 Ver Sedes</a>
        </div>
      </div>
    </article>

  </div>
</section>
