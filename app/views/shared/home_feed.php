<?php
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
$db = Conexion::getConexion();

/* ====== Sesión ====== */
$UID   = (int)($_SESSION['id'] ?? 0);
$ROL   = strtolower($_SESSION['rol'] ?? '');
$SEDE  = (int)($_SESSION['sede_id'] ?? 0);
$DEPTO = (int)($_SESSION['departamento_id'] ?? 0);
$NOMBRE= htmlspecialchars($_SESSION['nombre_completo'] ?? $_SESSION['usuario'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');

/* ====== Nombres legibles ====== */
$SEDE_NOMBRE = null; $DEPTO_NOMBRE = null;
try {
  if ($SEDE) { $st=$db->prepare("SELECT nombre FROM sedes WHERE id=?"); $st->execute([$SEDE]); $SEDE_NOMBRE=$st->fetchColumn()?:null; }
  if ($DEPTO){ $st=$db->prepare("SELECT nombre FROM departamentos WHERE id=?"); $st->execute([$DEPTO]); $DEPTO_NOMBRE=$st->fetchColumn()?:null; }
} catch (\Throwable $e) {}

/* ====== Util ====== */
function time_ago_es(?string $ts): string {
  if(!$ts) return '';
  $t = strtotime($ts); if(!$t) return '';
  $d = time() - $t;
  if ($d < 60) return 'hace unos segundos';
  if ($d < 3600) return 'hace ' . floor($d/60) . ' min';
  if ($d < 86400) return 'hace ' . floor($d/3600) . ' h';
  return date('d/m/Y H:i', $t);
}

/* ====== Feed ====== */
$where='1=0'; $params=[];
if ($ROL==='jefe_area'){ $where='(s.departamento_id=:d OR s.autor_id=:u)'; $params=[':d'=>$DEPTO,':u'=>$UID]; }
elseif ($ROL==='gerente'){ $where='s.sede_id=:s'; $params=[':s'=>$SEDE]; }
elseif ($ROL==='rh'){ $where='1=1'; }
$sql = "
SELECT sc.id, sc.solicitud_id, sc.usuario_id, sc.comentario, sc.creado_en,
       s.titulo, s.puesto, s.estado_actual,
       u.nombre_completo AS autor_nombre, u.usuario AS autor_usuario, u.fotografia
FROM solicitudes_comentarios sc
JOIN solicitudes s ON s.id=sc.solicitud_id
LEFT JOIN usuarios u ON u.id=sc.usuario_id
WHERE $where
ORDER BY sc.id DESC
LIMIT 30";
$feed=[];
try{ $st=$db->prepare($sql); $st->execute($params); $feed=$st->fetchAll(PDO::FETCH_ASSOC) ?: []; }catch(\Throwable $e){ $feed=[]; }

/* ====== HERO: slides ====== */
$heroImages = [
  BASE_PATH.'/public/img/hero/hero1.jpg',
  BASE_PATH.'/public/img/hero/hero2.jpg',
  BASE_PATH.'/public/img/hero/hero3.jpg',
  BASE_PATH.'/public/img/hero/hero4.jpg',
];
shuffle($heroImages);
$slides = [
  ['tag'=>'MISIÓN', 'txt'=>'Impulsar el talento y construir equipos excepcionales.', 'img'=>$heroImages[0] ?? $heroImages[0]],
  ['tag'=>'VISIÓN', 'txt'=>'Ser el referente en experiencia humana y eficacia organizacional.', 'img'=>$heroImages[1] ?? $heroImages[0]],
  ['tag'=>'VALORES','txt'=>'Integridad · Colaboración · Innovación · Servicio', 'img'=>$heroImages[2] ?? $heroImages[0]],
  ['tag'=>'FRASE',  'txt'=>'“La cultura se desayuna a la estrategia.”', 'img'=>$heroImages[3] ?? $heroImages[0]],
];
?>
<style>
:root{
  --ink:#eaf2ff; --muted:#c8d4f0; --ok:#22c55e; --brand:#0D6EFD;
}
*{ box-sizing:border-box }
body{ background:#0a0f1d; }

/* ====== FONDO AURORA ====== */
.aurora-bg{
  position:fixed; inset:0; z-index:-2;
  background:
    radial-gradient(65% 65% at 15% 5%, rgba(13,110,253,.35), transparent 60%),
    radial-gradient(50% 50% at 85% 100%, rgba(139,92,246,.28), transparent 65%),
    radial-gradient(35% 35% at 60% 50%, rgba(6,182,212,.18), transparent 60%),
    linear-gradient(180deg,#0b2240 0%,#081426 58%,#06101f 100%);
  filter:saturate(1.1);
}
.grain{
  position:fixed; inset:0; z-index:-1; opacity:.45;
  background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160"><filter id="n"><feTurbulence type="fractalNoise" baseFrequency="1.1" numOctaves="2" stitchTiles="stitch"/></filter><rect width="100%" height="100%" filter="url(%23n)" opacity=".04"/></svg>');
}

/* ====== CONTENEDOR ====== */
.page{ max-width:100%; margin:0 auto; padding:16px 16px 28px }

/* ====== HERO (altura del carrusel ) ====== */
.hero{ height:45vh; min-height:300px; position:relative; margin-bottom:16px; display:grid; place-items:center; }
.hero-frame{
  width:min(100%, 96vw); height:100%; border-radius:20px; position:relative; overflow:hidden;
  background:linear-gradient(180deg, rgba(255,255,255,.14), rgba(255,255,255,.08));
  border:1px solid rgba(255,255,255,.28);
  backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
  box-shadow: 0 36px 90px rgba(0,0,0,.36), inset 0 1px 0 rgba(255,255,255,.18);
}
.hero-lights{
  position:absolute; inset:-20%;
  background:
    radial-gradient(40% 35% at 12% 18%, rgba(13,110,253,.35), transparent 60%),
    radial-gradient(35% 30% at 80% 18%, rgba(6,182,212,.30), transparent 60%),
    radial-gradient(55% 45% at 70% 82%, rgba(139,92,246,.28), transparent 70%);
  animation:floaty 22s ease-in-out infinite alternate;
  filter:saturate(1.15);
  pointer-events:none;
}
@keyframes floaty{ 0%{ transform:translate3d(0,0,0) scale(1) } 100%{ transform:translate3d(0,-14px,0) scale(1.03) } }

/* ====== CARRUSEL ====== */
.h-carousel{ position:absolute; inset:0; display:grid; place-items:center; padding:22px; outline:none }
.h-track{ position:relative; width:min(1120px,92vw); height:100% }
.hslide{
  position:absolute; inset:0; display:grid; align-content:center; justify-items:center; gap:10px;
  padding:0 24px; text-align:center; color:#fff; opacity:0; transform:translateY(8px);
  transition:opacity .6s ease, transform .6s ease;
}
.hslide.active{ opacity:1; transform:translateY(0) }
.suptitle{
  display:inline-flex; align-items:center; gap:8px; font-weight:900; letter-spacing:.28px; font-size:.9rem; color:#eaf2ff;
  padding:.32rem .9rem; border-radius:999px; border:1px solid rgba(255,255,255,.36); background:rgba(255,255,255,.15);
}
.suptitle i{width:16px;height:16px;display:inline-block;border-radius:50%;background:conic-gradient(from 90deg at 50% 50%, #5eead4, #60a5fa, #c084fc, #5eead4)}
.big{ margin:.25rem 0 .8rem; font-weight:900; letter-spacing:.2px; font-size:clamp(2rem, 3.8vw, 3rem); color:#ffffff; text-shadow:0 2px 18px rgba(13,110,253,.28); }
.lead{ margin:0 auto; max-width:980px; line-height:1.75; font-size:clamp(1.06rem,1.4vw,1.2rem); color:var(--muted); }
.pills{ display:flex; gap:10px; flex-wrap:wrap; justify-content:center; margin-top:8px }
.pill{ padding:.38rem .95rem; border-radius:999px; border:1px solid rgba(255,255,255,.38); background:rgba(255,255,255,.16); color:#f1f6ff; font-size:.9rem; font-weight:700; }
.h-dots{ position:absolute; bottom:16px; left:50%; transform:translateX(-50%); display:flex; gap:8px; z-index:2 }
.h-dots .dot{ width:9px; height:9px; border-radius:999px; background:rgba(255,255,255,.45); cursor:pointer }
.h-dots .dot.on{ background:#fff }

/* ====== FEED ====== */
.feed{ width:min(1360px, 96vw); margin:10px auto 26px; }
.feed-glass{
  border-radius:22px; background:linear-gradient(180deg, rgba(255,255,255,.14), rgba(255,255,255,.08));
  border:1px solid rgba(255,255,255,.24); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
  box-shadow:0 20px 56px rgba(0,0,0,.34), inset 0 1px 0 rgba(255,255,255,.16); padding:16px 14px;
}
.feed-head{ display:flex; align-items:center; justify-content:space-between; margin:6px 4px 12px }
.feed-title{ color:#eaf2ff; margin:0; font-size:1.7rem; font-weight:900; letter-spacing:.2px }
.live{ display:inline-flex; align-items:center; gap:8px; padding:.26rem .7rem; border-radius:999px; border:1px solid rgba(255,255,255,.25); background:rgba(255,255,255,.12); color:#dbe8ff; font-size:.82rem }
.live .dot{ width:8px; height:8px; border-radius:999px; background:var(--ok) }

.post{ background:#0c1428; border:1px solid rgba(255,255,255,.13); border-radius:16px; box-shadow:0 16px 46px rgba(3,8,20,.55); overflow:hidden; margin-bottom:14px; transform:translateY(8px); opacity:0; transition:.35s ease; }
.post.reveal{ transform:none; opacity:1 }
.p-hd{ display:flex; gap:12px; align-items:center; padding:14px 16px; border-bottom:1px solid rgba(255,255,255,.08) }
.ava{ width:44px; height:44px; border-radius:50%; overflow:hidden; background:linear-gradient(135deg,#0ea5e9,#6366f1); color:#fff; display:grid; place-items:center; font-weight:900 }
.ava img{ width:100%; height:100%; object-fit:cover }
.title{ color:#eaf2ff; font-weight:800 }
.meta{ color:#9fb3ff; font-size:.86rem }
.time{ margin-left:auto; color:#9fb3ff; font-size:.86rem }
.p-bd{ padding:10px 16px 16px; color:#dbe4ff; line-height:1.5 }

/* estados */
.state{ display:inline-block; margin-top:8px; padding:.22rem .6rem; border-radius:999px; font-size:.75rem; font-weight:800; color:#0a1020; box-shadow: 0 0 0 1px rgba(0,0,0,.06), 0 4px 10px rgba(0,0,0,.18); }
.state[data-s="ENVIADA"]    { background:linear-gradient(135deg,#67e8f9,#93c5fd) }
.state[data-s="EN_REV_GER"] { background:linear-gradient(135deg,#fde68a,#f59e0b) }
.state[data-s="EN_REV_RH"]  { background:linear-gradient(135deg,#e9d5ff,#a78bfa) }
.state[data-s="ABIERTA"]    { background:linear-gradient(135deg,#bbf7d0,#34d399) }
.state[data-s="CERRADA"]    { background:linear-gradient(135deg,#e2e8f0,#94a3b8) }

@media (max-width: 840px){
  .hero{ height:56vh; min-height:360px }
  .lead{ max-width:95% }
}
@media (prefers-reduced-motion: reduce){
  .hslide,.post,.hero-lights{ transition:none !important; animation:none !important; }
}
</style>

<div class="aurora-bg"></div>
<div class="grain"></div>

<div class="page">
  <!-- ====== HERO ====== -->
  <section class="hero">
    <div class="hero-frame">
      <div class="hero-lights"></div>
      <div class="h-carousel" tabindex="0">
        <div class="h-track">

          <!-- Slide 0 · Bienvenida -->
          <div class="hslide active">
            <span class="suptitle"><i></i> Bienvenido</span>
            <h1 class="big">Hola, <?= $NOMBRE ?> 👋</h1>
            <p class="lead">Tu espacio de Nexus RH: estado de solicitudes, actividad reciente y comunicación clara entre áreas.</p>
            <div class="pills">
              <span class="pill">ROL: <?= strtoupper($ROL) ?></span>
              <?php if ($SEDE_NOMBRE): ?><span class="pill">SEDE: <?= htmlspecialchars($SEDE_NOMBRE) ?></span><?php endif; ?>
              <?php if ($DEPTO_NOMBRE && $ROL==='jefe_area'): ?><span class="pill">DEPTO: <?= htmlspecialchars($DEPTO_NOMBRE) ?></span><?php endif; ?>
            </div>
          </div>

          <!-- Slide 1 · Justificación -->
          <div class="hslide">
            <span class="suptitle"><i></i> Justificación</span>
            <h2 class="big">Nexus RH nace de una investigación con profesionales de RR. HH. y problemas reales.</h2>
            <p class="lead">El sistema surge tras entrevistas con especialistas. Detectamos retos en reclutamiento, comunicación y objetividad; diseñamos Nexus RH para resolverlos con enfoque moderno.</p>
          </div>

          <!-- Slide 2 · Hipótesis -->
          <div class="hslide">
            <span class="suptitle"><i></i> Hipótesis</span>
            <h2 class="big">Un sistema web inteligente y centralizado acelera la selección y eleva la objetividad.</h2>
            <p class="lead">“Si implementamos un sistema inteligente para contratar, reducimos tiempos, mejoramos la comunicación y tomamos decisiones más objetivas frente a procesos manuales”.</p>
          </div>

          <!-- Slide 3 · Objetivo -->
          <div class="hslide">
            <span class="suptitle"><i></i> Objetivo</span>
            <h2 class="big">Optimizar la selección y contratación con información clara para todas las áreas.</h2>
            <p class="lead">Nexus RH busca mejorar integralmente el proceso y proveer datos comprensibles para jefaturas, gerencias y RR. HH., alineando expectativas y acelerando decisiones.</p>
          </div>

          <!-- Dots -->
          <div class="h-dots" id="hDots">
            <span class="dot on" aria-label="Slide 1" role="button" tabindex="0"></span>
            <span class="dot" aria-label="Slide 2" role="button" tabindex="0"></span>
            <span class="dot" aria-label="Slide 3" role="button" tabindex="0"></span>
            <span class="dot" aria-label="Slide 4" role="button" tabindex="0"></span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ====== FEED ====== -->
  <section class="feed">
    <div class="feed-glass">
      <div class="feed-head">
        <h3 class="feed-title">Actividad de solicitudes</h3>
        <span class="live"><span class="dot"></span> en tiempo real</span>
      </div>

      <?php if (!$feed): ?>
        <article class="post reveal">
          <div class="p-hd">
            <div class="ava">🙂</div>
            <div>
              <div class="title">Sin actividad por ahora</div>
              <div class="meta">Cuando haya comentarios o cambios, aparecerán aquí.</div>
            </div>
          </div>
          <div class="p-bd"></div>
        </article>
      <?php else: ?>
        <?php foreach ($feed as $row):
          $autor  = $row['autor_nombre'] ?: $row['autor_usuario'] ?: 'Usuario';
          $ini    = mb_strtoupper(mb_substr($autor,0,1,'UTF-8'));
          $foto   = $row['fotografia'] ? (BASE_PATH.'/public/uploads/fotos/'.ltrim($row['fotografia'],'/')) : null;
          $titulo = htmlspecialchars($row['titulo'] ?? 'Solicitud');
          $puesto = htmlspecialchars($row['puesto'] ?? '—');
          $estado = htmlspecialchars($row['estado_actual'] ?? '');
          $coment = nl2br(htmlspecialchars($row['comentario'] ?? ''));
          $ago    = time_ago_es($row['creado_en'] ?? '');
          $link   = BASE_PATH . '/app/views/admin/solicitudes/seguimiento.php?id=' . (int)$row['solicitud_id'];
        ?>
        <article class="post">
          <div class="p-hd">
            <div class="ava">
              <?php if ($foto): ?><img src="<?= $foto ?>" alt="usr" loading="lazy"><?php else: ?><?= $ini ?><?php endif; ?>
            </div>
            <div>
              <div class="title"><?= $titulo ?> · <span class="meta"><?= $puesto ?></span></div>
              <div class="meta"><a class="link" href="<?= $link ?>">Ver solicitud #<?= (int)$row['solicitud_id'] ?></a></div>
            </div>
            <div class="time"><?= $ago ?></div>
          </div>
          <div class="p-bd">
            <?= $coment ?>
            <br><span class="state" data-s="<?= $estado ?>"><?= $estado ?: '—' ?></span>
          </div>
        </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
</div>

<script>
/* Carrusel auto (15 s) + dots + pausa en hover + teclado + swipe */
(function(){
  const slider = document.querySelector('.h-carousel');
  const slides = Array.from(document.querySelectorAll('.hslide'));
  const dots   = Array.from(document.querySelectorAll('#hDots .dot'));
  if (!slides.length) return;

  let i = 0, timer = null, paused = false, touchStartX = null;

  function show(idx){
    slides[i].classList.remove('active'); dots[i]?.classList.remove('on');
    i = (idx + slides.length) % slides.length;
    slides[i].classList.add('active'); dots[i]?.classList.add('on');
  }

  function play(){ if (timer) clearInterval(timer); timer = setInterval(()=>{ if(!paused) show(i+1); }, 15000); }
  function pause(){ paused = true; }
  function resume(){ paused = false; }

  dots.forEach((d,idx)=>{
    d.addEventListener('click', ()=>{ show(idx); });
    d.addEventListener('keydown', (e)=>{ if(e.key==='Enter' || e.key===' ') { e.preventDefault(); show(idx); }});
  });

  slider.addEventListener('mouseenter', pause);
  slider.addEventListener('mouseleave', resume);

  slider.addEventListener('keydown', (e)=>{
    if (e.key === 'ArrowRight') { show(i+1); }
    if (e.key === 'ArrowLeft')  { show(i-1); }
  });

  slider.addEventListener('touchstart', (e)=>{ touchStartX = e.touches[0].clientX; }, {passive:true});
  slider.addEventListener('touchend',   (e)=>{
    if (touchStartX === null) return;
    const dx = e.changedTouches[0].clientX - touchStartX;
    if (Math.abs(dx) > 40) show(i + (dx < 0 ? 1 : -1));
    touchStartX = null;
  });

  play();
})();

/* Reveal del feed */
(function(){
  const posts = Array.from(document.querySelectorAll('.post'));
  if (!('IntersectionObserver' in window)) { posts.forEach(p=>p.classList.add('reveal')); return; }
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{ if (e.isIntersecting){ e.target.classList.add('reveal'); io.unobserve(e.target); } });
  }, {threshold:.12});
  posts.forEach(p=>io.observe(p));
})();
</script>
