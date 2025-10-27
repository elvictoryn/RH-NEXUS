<?php
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
$db = Conexion::getConexion();

if (session_status() === PHP_SESSION_NONE) session_start();
$UID   = (int)($_SESSION['id'] ?? 0);
$ROL   = strtolower($_SESSION['rol'] ?? '');
$SEDE  = (int)($_SESSION['sede_id'] ?? 0);
$DEPTO = (int)($_SESSION['departamento_id'] ?? 0);
$NOMBRE= htmlspecialchars($_SESSION['nombre_completo'] ?? $_SESSION['usuario'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');

/* nombres legibles */
$SEDE_NOMBRE = $DEPTO_NOMBRE = null;
try{
  if ($SEDE) { $st=$db->prepare("SELECT nombre FROM sedes WHERE id=?"); $st->execute([$SEDE]); $SEDE_NOMBRE=$st->fetchColumn()?:null; }
  if ($DEPTO){ $st=$db->prepare("SELECT nombre FROM departamentos WHERE id=?"); $st->execute([$DEPTO]); $DEPTO_NOMBRE=$st->fetchColumn()?:null; }
}catch(\Throwable $e){}

/* util */
function time_ago_es(?string $ts): string {
  if(!$ts) return '';
  $t = strtotime($ts); if(!$t) return '';
  $d = time() - $t;
  if ($d < 60) return 'hace unos segundos';
  if ($d < 3600) return 'hace ' . floor($d/60) . ' min';
  if ($d < 86400) return 'hace ' . floor($d/3600) . ' h';
  return date('d/m/Y H:i', $t);
}

/* feed (mismo filtro por rol) */
$where='1=0'; $params=[];
if ($ROL==='jefe_area'){ $where='(s.departamento_id=:d OR s.autor_id=:u)'; $params=[':d'=>$DEPTO,':u'=>$UID]; }
elseif ($ROL==='gerente'){ $where='s.sede_id=:s'; $params=[':s'=>$SEDE]; }
elseif ($ROL==='rh' || $ROL==='admin'){ $where='1=1'; }

$sql = "
SELECT sc.id, sc.solicitud_id, sc.usuario_id, sc.comentario, sc.creado_en,
       s.titulo, s.puesto, s.estado_actual,
       u.nombre_completo AS autor_nombre, u.usuario AS autor_usuario, u.fotografia
FROM solicitudes_comentarios sc
JOIN solicitudes s ON s.id=sc.solicitud_id
LEFT JOIN usuarios u ON u.id=sc.usuario_id
WHERE $where
ORDER BY sc.id DESC
LIMIT 50";
$feed=[];
try{ $st=$db->prepare($sql); $st->execute($params); $feed=$st->fetchAll(PDO::FETCH_ASSOC) ?: []; }catch(\Throwable $e){ $feed=[]; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Nexus RH · Inicio</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
:root{
  --ink:#eaf2ff; --muted:#c8d4f0; --brand:#0D6EFD; --ok:#22c55e;
  --glass: linear-gradient(180deg, rgba(255,255,255,.16), rgba(255,255,255,.08));
  --brd: rgba(255,255,255,.24);
}
*{ box-sizing:border-box }
html,body{ height:100% }
body{ margin:0; background:#060f1f; color:#eaf2ff; font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial }

/* ===== fondo y grano ===== */
.mesh{
  position:fixed; inset:0; z-index:-3;
  background:
    radial-gradient(60% 45% at 8% 0%, rgba(99,102,241,.38), transparent 60%),
    radial-gradient(45% 40% at 92% 100%, rgba(59,130,246,.35), transparent 65%),
    radial-gradient(30% 30% at 60% 40%, rgba(20,184,166,.22), transparent 60%),
    linear-gradient(180deg,#091a32 0%,#081426 58%,#050d1a 100%);
  animation:hue 18s ease-in-out infinite alternate;
  filter:saturate(1.05) contrast(1.02);
}
@keyframes hue{ from{filter:hue-rotate(0deg) saturate(1.05)} to{filter:hue-rotate(24deg) saturate(1.1)} }
.particles{
  position:fixed; inset:0; z-index:-2; pointer-events:none;
  background-image:
    radial-gradient(2px 2px at 25% 20%, #fff, transparent 40%),
    radial-gradient(1.5px 1.5px at 75% 35%, #fff, transparent 40%),
    radial-gradient(2px 2px at 45% 70%, #fff, transparent 40%),
    radial-gradient(1.7px 1.7px at 12% 80%, #fff, transparent 40%),
    radial-gradient(1.7px 1.7px at 88% 12%, #fff, transparent 40%);
  opacity:.28; animation:twinkle 7s ease-in-out infinite alternate;
}
@keyframes twinkle{0%{opacity:.18}100%{opacity:.35}}
.grain{ position:fixed; inset:0; z-index:-1; opacity:.45;
  background-image:url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160"><filter id="n"><feTurbulence type="fractalNoise" baseFrequency="1.1" numOctaves="2" stitchTiles="stitch"/></filter><rect width="100%" height="100%" filter="url(%23n)" opacity=".04"/></svg>');
}
.page{ max-width:100%; margin:0 auto; padding:16px 14px 28px }

/* ===== HERO (igual al que aprobaste) ===== */
.hero{
  width:min(1360px,96vw); margin:18px auto 18px; position:relative; overflow:hidden;
  border-radius:28px; border:1px solid var(--brd); background:var(--glass); backdrop-filter: blur(18px);
  box-shadow:0 50px 140px rgba(0,0,0,.45);
}
.ribbon{ height:8px; width:100%; background:linear-gradient(90deg,#22d3ee,#60a5fa,#a78bfa,#22c55e,#22d3ee) }
.hero-grid{ display:grid; grid-template-columns: 1.1fr .9fr; gap:18px; padding:24px }
@media (max-width:1050px){ .hero-grid{ grid-template-columns:1fr } }
.left{ position:relative; padding-right:8px }
.badge-top{ display:inline-flex; align-items:center; gap:.5rem; font-weight:900; color:#cfe3ff; }
.badge-top .dot{ width:10px; height:10px; border-radius:50%; background:linear-gradient(135deg,#22d3ee,#60a5fa); box-shadow:0 0 0 3px rgba(34,211,238,.18) }
.title{
  margin:.35rem 0 0; font-weight:1000; line-height:1.05;
  font-size:clamp(2.4rem,5vw,3.8rem);
  background:linear-gradient(92deg,#ffffff,#cfe8ff); -webkit-background-clip:text; background-clip:text; color:transparent;
  text-shadow: 0 10px 40px rgba(0,0,0,.35);
}
.wave{ display:inline-block; animation:w 2.2s ease-in-out infinite }
@keyframes w{ 0%,100%{transform:rotate(0)} 35%{transform:rotate(18deg)} 70%{transform:rotate(-10deg)} }
.subtitle{ margin:.5rem 0 1rem; color:#cfe0ff; font-size:clamp(1.02rem,1.4vw,1.12rem) }
.polaroid{
  position:absolute; right:10px; top:10px; transform:rotate(2.5deg); z-index:2;
  width:140px; background:#0b203d; border-radius:16px; padding:10px; border:1px solid rgba(255,255,255,.18);
  box-shadow:0 18px 60px rgba(0,0,0,.35);
}
.polaroid .ph{ width:100%; aspect-ratio:1/1; border-radius:12px; overflow:hidden; display:grid; place-items:center;
  background:conic-gradient(from 110deg,#60a5fa,#22d3ee,#a78bfa,#22c55e,#60a5fa); color:#07202f; font-weight:1000; font-size:2.4rem }
.polaroid img{ width:100%; height:100%; object-fit:cover; display:block }
.polaroid .nm{ margin-top:8px; font-weight:900; font-size:.86rem; color:#eaf6ff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis }
.pills{ display:flex; flex-wrap:wrap; gap:10px; margin-top:12px }
.pill{
  display:inline-flex; align-items:center; gap:.55rem; padding:.52rem .9rem; border-radius:999px;
  border:1px solid rgba(255,255,255,.22); background:rgba(255,255,255,.12); color:#eaf2ff; font-weight:900; font-size:.92rem
}
.ic{ width:20px; height:20px; border-radius:50%; display:inline-grid; place-items:center; background:linear-gradient(135deg,#60a5fa,#22d3ee); color:#061727; font-size:.82rem }
.ticker-wrap{ margin-top:14px; border-top:1px solid rgba(255,255,255,.12); border-bottom:1px solid rgba(255,255,255,.12); overflow:hidden }
.ticker{
  display:flex; gap:28px; padding:10px 0; white-space:nowrap; animation:marq 26s linear infinite;
  color:#cfe3ff; font-weight:850; letter-spacing:.4px
}
@keyframes marq{ from{ transform:translateX(0) } to{ transform:translateX(-50%) } }
.right{ display:grid; grid-template-rows: 1fr auto; gap:12px; position:relative }
.deck{
  position:relative; height:220px; border-radius:18px; overflow:hidden; border:1px solid rgba(255,255,255,.16);
  background:rgba(9,20,38,.65);
}
.slide{
  position:absolute; inset:0; display:grid; align-content:center; gap:8px; text-align:center; padding:18px;
  opacity:0; transform:translateY(8px) scale(.98); transition:opacity .6s, transform .6s;
}
.slide.on{ opacity:1; transform:none }
.slide .tag{ display:inline-block; padding:.18rem .55rem; border-radius:999px; border:1px solid rgba(255,255,255,.24); background:rgba(255,255,255,.14); font-weight:900; font-size:.78rem; color:#d9ebff }
.slide .txt{ font-size:1.08rem; color:#eef6ff }
.dots{ display:flex; gap:8px; justify-content:center; margin-top:10px }
.dots .d{ width:8px; height:8px; border-radius:999px; background:#93b4ff44 }
.dots .d.on{ background:#fff }
.art{ height:80px; border-radius:16px; border:1px solid rgba(255,255,255,.16); background:#0a1a31; overflow:hidden }
.art svg{ width:100%; height:100%; display:block }

/* ===== FEED – NUEVO DISEÑO (timeline) ===== */
.feed{ width:min(1360px,96vw); margin:18px auto 26px }
.feed-glass{
  border-radius:24px; background:linear-gradient(180deg, rgba(255,255,255,.14), rgba(255,255,255,.08));
  border:1px solid rgba(255,255,255,.24); backdrop-filter: blur(10px);
  box-shadow:0 22px 70px rgba(0,0,0,.36), inset 0 1px 0 rgba(255,255,255,.16); padding:18px 18px 8px;
}
.feed-head{ display:flex; align-items:center; justify-content:space-between; margin:0 2px 8px }
.feed-title{ color:#eaf2ff; margin:0; font-size:1.6rem; font-weight:1000; letter-spacing:.2px }
.live{ display:inline-flex; align-items:center; gap:8px; padding:.26rem .7rem; border-radius:999px; border:1px solid rgba(255,255,255,.25); background:rgba(255,255,255,.12); color:#dbe8ff; font-size:.82rem }
.live .dot{ width:8px; height:8px; border-radius:999px; background:var(--ok) }

/* grupos (Hoy / Anteriores) */
.tg{ margin:10px 0 14px; padding-left:14px; position:relative }
.tg-title{
  font-weight:900; color:#cfe0ff; letter-spacing:.4px; margin:0 0 10px 0; display:flex; align-items:center; gap:.5rem;
}
.tg-title .badge{ font-size:.75rem; padding:.18rem .56rem; border-radius:999px; border:1px solid rgba(255,255,255,.22); background:rgba(255,255,255,.12); color:#eaf2ff; font-weight:900 }

/* línea vertical */
.tg::before{
  content:""; position:absolute; left:18px; top:26px; bottom:6px; width:2px;
  background:linear-gradient(180deg,#2b405f,#24344f);
  box-shadow:0 0 0 1px rgba(0,0,0,.25) inset;
}

/* ítem de timeline */
.ti{
  position:relative; margin:0 0 12px 0; padding-left:44px;
}
.ti-marker{
  position:absolute; left:7px; top:8px; width:22px; height:22px; border-radius:50%;
  box-shadow:0 6px 14px rgba(0,0,0,.38), inset 0 1px 0 rgba(255,255,255,.2);
  background:linear-gradient(135deg,#60a5fa,#22d3ee);
  border:2px solid #0a1426;
}
.ti-marker[data-s="EN_REV_GER"]{ background:linear-gradient(135deg,#fde68a,#f59e0b) }
.ti-marker[data-s="EN_REV_RH"]{ background:linear-gradient(135deg,#e9d5ff,#a78bfa) }
.ti-marker[data-s="ABIERTA"]{ background:linear-gradient(135deg,#bbf7d0,#34d399) }
.ti-marker[data-s="CERRADA"]{ background:linear-gradient(135deg,#cbd5e1,#94a3b8) }

/* tarjeta burbuja */
.card{
  border-radius:16px; background:#0c1428; border:1px solid rgba(255,255,255,.14);
  box-shadow:0 14px 44px rgba(3,8,20,.45); overflow:hidden;
}
.card-hd{
  display:flex; align-items:center; gap:10px; padding:10px 12px; border-bottom:1px solid rgba(255,255,255,.08)
}
.av{ width:40px; height:40px; border-radius:50%; overflow:hidden; display:grid; place-items:center }
.av img{ width:100%; height:100%; object-fit:cover; display:block }
.av.i{ background:linear-gradient(135deg,#2563eb,#10b981); color:#fff; font-weight:1000; text-transform:uppercase }
.hd-txt{ min-width:0 }
.hd-tit{ font-weight:900; color:#eaf2ff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis }
.hd-sub{ color:#9fb3ff; font-size:.86rem; display:flex; gap:.5rem; flex-wrap:wrap }
.badge{
  display:inline-flex; align-items:center; gap:.35rem; padding:.16rem .55rem; border-radius:999px; font-size:.72rem; font-weight:900;
  border:1px solid rgba(255,255,255,.18); background:#0e1c36; color:#dbe8ff;
}
.time{ margin-left:auto; color:#9fb3ff; font-size:.85rem }
.card-bd{ padding:10px 12px 12px; color:#dbe4ff; line-height:1.55 }
.state{
  display:inline-block; margin-top:8px; padding:.22rem .6rem; border-radius:999px; font-size:.74rem; font-weight:900; color:#0a1020;
  box-shadow: 0 0 0 1px rgba(0,0,0,.06), 0 4px 10px rgba(0,0,0,.18);
}
.state[data-s="ENVIADA"]    { background:linear-gradient(135deg,#67e8f9,#93c5fd) }
.state[data-s="EN_REV_GER"] { background:linear-gradient(135deg,#fde68a,#f59e0b) }
.state[data-s="EN_REV_RH"]  { background:linear-gradient(135deg,#e9d5ff,#a78bfa) }
.state[data-s="ABIERTA"]    { background:linear-gradient(135deg,#bbf7d0,#34d399) }
.state[data-s="CERRADA"]    { background:linear-gradient(135deg,#e2e8f0,#94a3b8) }
.empty{
  border:1px dashed rgba(255,255,255,.25); padding:14px; border-radius:14px; text-align:center; color:#9fb3ff;
  background:linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.06));
}
</style>
</head>
<body>

<div class="mesh"></div>
<div class="particles"></div>
<div class="grain"></div>

<div class="page">

  <!-- ===== HERO ===== -->
  <section class="hero" aria-label="Bienvenida">
    <div class="ribbon"></div>
    <div class="hero-grid">
      <div class="left">
        <div class="badge-top"><span class="dot"></span> Nexus RH</div>
        <h1 class="title">Hola, <?= $NOMBRE ?> <span class="wave">👋</span></h1>
        <p class="subtitle">Información clara, personas primero y decisiones con impacto.</p>
        <div class="pills">
          <span class="pill"><span class="ic">🏷</span> ROL: <?= strtoupper($ROL) ?></span>
          <?php if ($SEDE_NOMBRE): ?><span class="pill"><span class="ic">📍</span> SEDE: <?= htmlspecialchars($SEDE_NOMBRE) ?></span><?php endif; ?>
          <?php if ($DEPTO_NOMBRE && $ROL==='jefe_area'): ?><span class="pill"><span class="ic">🗂</span> DEPTO: <?= htmlspecialchars($DEPTO_NOMBRE) ?></span><?php endif; ?>
        </div>
        <div class="ticker-wrap" aria-hidden="true">
          <div class="ticker">
            <span>Talento</span> • <span>Transparencia</span> • <span>Innovación</span> • <span>Colaboración</span> • <span>Excelencia</span> •
            <span>Empatía</span> • <span>Agilidad</span> • <span>Confianza</span> • <span>Servicio</span> •
            <span>Talento</span> • <span>Transparencia</span> • <span>Innovación</span> • <span>Colaboración</span> • <span>Excelencia</span> •
            <span>Empatía</span> • <span>Agilidad</span> • <span>Confianza</span> • <span>Servicio</span>
          </div>
        </div>
        <div class="polaroid" title="Tu perfil">
          <div class="ph">
            <?php
              $foto = trim((string)($_SESSION['foto'] ?? ''));
              if ($foto) {
                $src = BASE_PATH . '/public/img/usuarios/' . ltrim($foto,'/');
                echo '<img src="'.htmlspecialchars($src,ENT_QUOTES,'UTF-8').'" alt="avatar" onerror="this.remove();this.parentElement.textContent=\'' .
                     htmlspecialchars(mb_strtoupper(mb_substr($_SESSION['usuario']??'U',0,1,'UTF-8'),'UTF-8'),ENT_QUOTES,'UTF-8') . '\'">';
              } else {
                $ini='U';
                if (!empty($_SESSION['nombre_completo'] ?? '')) {
                  $p=preg_split('/\s+/u', $_SESSION['nombre_completo']);
                  $ini = mb_strtoupper(mb_substr($p[0],0,1,'UTF-8') . (count($p)>1?mb_substr(end($p),0,1,'UTF-8'):''),'UTF-8');
                } else {
                  $ini = mb_strtoupper(mb_substr($_SESSION['usuario'] ?? 'U',0,1,'UTF-8'),'UTF-8');
                }
                echo htmlspecialchars($ini, ENT_QUOTES, 'UTF-8');
              }
            ?>
          </div>
          <div class="nm"><?= $NOMBRE ?></div>
        </div>
      </div>

      <div class="right">
        <div class="deck" id="deck">
          <?php
            $slides = [
              ['tag'=>'MISIÓN','txt'=>'Impulsar el talento y crear equipos excepcionales.'],
              ['tag'=>'VISIÓN','txt'=>'Ser el referente en experiencia humana y eficacia organizacional.'],
              ['tag'=>'VALORES','txt'=>'Integridad · Colaboración · Innovación · Servicio · Empatía.'],
              ['tag'=>'CULTURA','txt'=>'Comunicación clara, foco en personas y mejora continua.'],
              ['tag'=>'PRINCIPIOS','txt'=>'Datos para decidir, respeto para convivir.'],
              ['tag'=>'PROPÓSITO','txt'=>'Hacer que trabajar aquí sea una gran experiencia.'],
            ];
            $i=0; foreach($slides as $s): ?>
              <div class="slide <?= $i===0?'on':'' ?>">
                <span class="tag"><?= htmlspecialchars($s['tag']) ?></span>
                <div class="txt"><?= htmlspecialchars($s['txt']) ?></div>
              </div>
          <?php $i++; endforeach; ?>
        </div>
        <div class="dots" id="dots">
          <?php for($j=0;$j<count($slides);$j++): ?>
            <span class="d <?= $j===0?'on':'' ?>"></span>
          <?php endfor; ?>
        </div>
        <div class="art" aria-hidden="true">
          <svg viewBox="0 0 1440 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <defs><linearGradient id="gA" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#22d3ee"/><stop offset="1" stop-color="#60a5fa"/></linearGradient></defs>
            <path fill="url(#gA)" fill-opacity=".55" d="M0,64L48,58.7C96,53,192,43,288,58.7C384,75,480,117,576,112C672,107,768,53,864,48C960,43,1056,85,1152,96C1248,107,1344,85,1392,74.7L1440,64L1440,0L0,0Z"/>
            <path fill="#0a1a31" fill-opacity=".75" d="M0,96L40,106.7C80,117,160,139,240,112C320,85,400,11,480,5.3C560,0,640,64,720,85.3C800,107,880,85,960,90.7C1040,96,1120,128,1200,133.3C1280,139,1360,117,1400,106.7L1440,96L1440,120L0,120Z"/>
          </svg>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== FEED (timeline nuevo) ===== -->
  <section class="feed">
    <div class="feed-glass">
      <div class="feed-head">
        <h3 class="feed-title">Actividad de solicitudes</h3>
        <span class="live"><span class="dot"></span> en tiempo real</span>
      </div>

      <?php
        // dividir en hoy y anteriores
        $hoy=[]; $antes=[];
        foreach ($feed as $row) {
          $d = substr((string)$row['creado_en'],0,10);
          if ($d === date('Y-m-d')) $hoy[]=$row; else $antes[]=$row;
        }

        // dibujar tarjeta timeline
        function render_timeline_item($row){
          $autor  = $row['autor_nombre'] ?: $row['autor_usuario'] ?: 'Usuario';
          $ini    = mb_strtoupper(mb_substr($autor,0,1,'UTF-8'));
          $foto   = $row['fotografia'] ? (BASE_PATH.'/public/uploads/fotos/'.ltrim($row['fotografia'],'/')) : null;
          $titulo = htmlspecialchars($row['titulo'] ?? 'Solicitud');
          $puesto = htmlspecialchars($row['puesto'] ?? '—');
          $estado = htmlspecialchars($row['estado_actual'] ?? '');
          $coment = nl2br(htmlspecialchars($row['comentario'] ?? ''));
          $ago    = time_ago_es($row['creado_en'] ?? '');
          $link   = BASE_PATH . '/app/views/admin/solicitudes/detalle.php?id=' . (int)$row['solicitud_id'];
          ?>
          <div class="ti">
            <span class="ti-marker" data-s="<?= $estado ?>"></span>
            <article class="card">
              <div class="card-hd">
                <?php if ($foto): ?>
                  <div class="av"><img src="<?= $foto ?>" alt="usr" loading="lazy"
                    onerror="this.closest('.av').classList.add('i');this.remove();this.closest('.av').textContent='<?= $ini ?>'"></div>
                <?php else: ?>
                  <div class="av i"><?= $ini ?></div>
                <?php endif; ?>
                <div class="hd-txt">
                  <div class="hd-tit"><?= $titulo ?></div>
                  <div class="hd-sub">
                    <span class="badge">#<?= (int)$row['solicitud_id'] ?></span>
                    <span class="badge">Puesto: <?= $puesto ?></span>
                    <a class="badge" href="<?= $link ?>">Ver detalle</a>
                  </div>
                </div>
                <div class="time"><?= $ago ?></div>
              </div>
              <div class="card-bd">
                <strong><?= htmlspecialchars($autor) ?></strong> comentó:<br><?= $coment ?>
                <br><span class="state" data-s="<?= $estado ?>"><?= $estado ?: '—' ?></span>
              </div>
            </article>
          </div>
        <?php } ?>

      <!-- HOY -->
      <div class="tg">
        <h4 class="tg-title">Hoy <span class="badge"><?= count($hoy) ?></span></h4>
        <?php if(!$hoy): ?>
          <div class="empty">Sin actividad hoy.</div>
        <?php else: foreach($hoy as $r) render_timeline_item($r); endif; ?>
      </div>

      <!-- ANTERIORES -->
      <div class="tg">
        <h4 class="tg-title">Anteriores <span class="badge"><?= count($antes) ?></span></h4>
        <?php if(!$antes): ?>
          <div class="empty">Sin registros anteriores.</div>
        <?php else: foreach($antes as $r) render_timeline_item($r); endif; ?>
      </div>

    </div>
  </section>
</div>

<script>
/* carrusel de tarjetas en el deck (hero) */
(function(){
  const slides = Array.from(document.querySelectorAll('#deck .slide'));
  const dots   = Array.from(document.querySelectorAll('#dots .d'));
  if(!slides.length) return;
  let i=0;
  function show(n){
    slides[i].classList.remove('on'); dots[i]?.classList.remove('on');
    i=(n+slides.length)%slides.length;
    slides[i].classList.add('on'); dots[i]?.classList.add('on');
  }
  setInterval(()=>show(i+1), 6000);
  dots.forEach((d,idx)=> d.addEventListener('click', ()=>show(idx)));
})();
</script>
</body>
</html>
