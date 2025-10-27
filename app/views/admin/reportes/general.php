<?php
// ============================================================
// Reportes (General) — Vista + Export CSV en un solo archivo
// Ruta: /app/views/admin/reportes/general.php
// ============================================================
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
if (session_status() === PHP_SESSION_NONE) session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
$db = Conexion::getConexion();

/* ---- Sesión / roles ---- */
$UID   = (int)($_SESSION['id'] ?? 0);
$ROL   = strtolower($_SESSION['rol'] ?? '');
$SEDE  = (int)($_SESSION['sede_id'] ?? 0);
$DEPTO = (int)($_SESSION['departamento_id'] ?? 0);
if (!$UID || !in_array($ROL, ['admin','rh','gerente','jefe_area'], true)) {
  header('Location: '.BASE_PATH.'/public/login.php'); exit;
}

/* ---- Helpers ---- */
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function estado_color($e){
  $e=strtoupper((string)$e);
  return match($e){
    'ENVIADA'       => '#60a5fa',
    'EN_REV_GER'    => '#f59e0b',
    'APROBADA'      => '#34d399',
    'BUSCANDO'      => '#22c55e',
    'EN_ENTREVISTA' => '#06b6d4',
    'EN_DECISION'   => '#a78bfa',
    'ABIERTA'       => '#10b981',
    'RECHAZADA'     => '#ef4444',
    'CERRADA'       => '#9ca3af',
    default         => '#93c5fd',
  };
}
function initials($name){
  $n=trim((string)$name); if($n==='') return 'U';
  $p=preg_split('/\s+/u',$n);
  $ini=mb_substr($p[0],0,1,'UTF-8');
  if(count($p)>1) $ini.=mb_substr(end($p),0,1,'UTF-8');
  return mb_strtoupper($ini,'UTF-8');
}
function median(array $a): float {
  if (!$a) return 0.0; sort($a); $n=count($a);
  $m=intdiv($n,2);
  return $n%2 ? (float)$a[$m] : (($a[$m-1]+$a[$m])/2);
}

/* ---- Catálogos filtros ---- */
$sedes=$departamentos=[];
try{
  $sedes=$db->query("SELECT id, nombre FROM sedes ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
  $departamentos=$db->query("SELECT id, nombre FROM departamentos ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
}catch(\Throwable $e){}

/* ---- Filtros GET ---- */
$f_sede = isset($_GET['sede']) ? (int)$_GET['sede'] : 0;
$f_dep  = isset($_GET['dep'])  ? (int)$_GET['dep']  : 0;

/* ---- Visibilidad por rol ---- */
$where='1=0'; $params=[];
if ($ROL==='admin' || $ROL==='rh'){
  $where='1=1';
} elseif ($ROL==='gerente'){
  $where='s.sede_id=:vsede'; $params[':vsede']=$SEDE ?: 0;
} elseif ($ROL==='jefe_area'){
  $where='(s.departamento_id=:vdep OR s.autor_id=:vuid)';
  $params[':vdep']=$DEPTO ?: 0; $params[':vuid']=$UID;
}

/* ---- Filtros aplicados ---- */
if ($f_sede>0){ $where.=" AND s.sede_id=:fsede"; $params[':fsede']=$f_sede; }
if ($f_dep>0){  $where.=" AND s.departamento_id=:fdep"; $params[':fdep']=$f_dep; }

/* =======================
   KPIs y datasets base
   ======================= */

/* Total solicitudes */
$total=0;
try{
  $st=$db->prepare("SELECT COUNT(*) FROM solicitudes s WHERE $where");
  $st->execute($params); $total=(int)$st->fetchColumn();
}catch(\Throwable $e){}

/* Por estado */
$por_estado=[];
try{
  $st=$db->prepare("SELECT UPPER(COALESCE(s.estado_actual,'SIN_ESTADO')) estado, COUNT(*) n
                    FROM solicitudes s
                    WHERE $where
                    GROUP BY UPPER(COALESCE(s.estado_actual,'SIN_ESTADO'))
                    ORDER BY n DESC");
  $st->execute($params); $por_estado=$st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}catch(\Throwable $e){}

/* Por sede */
$por_sede=[];
try{
  $st=$db->prepare("SELECT COALESCE(se.nombre,'— Sin sede') sede, COUNT(*) n
                    FROM solicitudes s
                    LEFT JOIN sedes se ON se.id=s.sede_id
                    WHERE $where
                    GROUP BY COALESCE(se.nombre,'— Sin sede')
                    ORDER BY n DESC, sede ASC");
  $st->execute($params); $por_sede=$st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}catch(\Throwable $e){}

/* Por departamento */
$por_dep=[];
try{
  $st=$db->prepare("SELECT COALESCE(d.nombre,'— Sin depto') dep, COUNT(*) n
                    FROM solicitudes s
                    LEFT JOIN departamentos d ON d.id=s.departamento_id
                    WHERE $where
                    GROUP BY COALESCE(d.nombre,'— Sin depto')
                    ORDER BY n DESC, dep ASC");
  $st->execute($params); $por_dep=$st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}catch(\Throwable $e){}

/* Actividad últimos 14 días (para línea) */
$serie_14=[];
try{
  $st=$db->prepare("SELECT DATE(sc.creado_en) d, COUNT(*) n
                    FROM solicitudes_comentarios sc
                    JOIN solicitudes s ON s.id=sc.solicitud_id
                    WHERE $where AND sc.creado_en >= (CURDATE() - INTERVAL 14 DAY)
                    GROUP BY DATE(sc.creado_en)
                    ORDER BY d ASC");
  $st->execute($params); $serie_14=$st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}catch(\Throwable $e){}

$mov_14 = array_sum(array_map(fn($r)=>(int)$r['n'],$serie_14));
$sols_14=0;
try{
  $st=$db->prepare("SELECT COUNT(DISTINCT sc.solicitud_id)
                    FROM solicitudes_comentarios sc
                    JOIN solicitudes s ON s.id=sc.solicitud_id
                    WHERE $where AND sc.creado_en >= (CURDATE() - INTERVAL 14 DAY)");
  $st->execute($params); $sols_14=(int)$st->fetchColumn();
}catch(\Throwable $e){}
$prom_mov_x_sol = $sols_14 ? round($mov_14 / $sols_14, 2) : 0;

/* =======================
   Export CSV
   ======================= */
if (isset($_GET['export']) && $_GET['export']==='csv') {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="reporte_solicitudes.csv"');
  $out=fopen('php://output','w');
  fputcsv($out, ['ID','Título','Puesto','Estado','Sede','Departamento','Prioridad','Vacantes','Fecha deseada']);
  try{
    $st=$db->prepare("SELECT s.id, COALESCE(s.titulo,'') titulo, COALESCE(s.puesto,'') puesto,
                             COALESCE(s.estado_actual,'') estado,
                             COALESCE(se.nombre,'—') sede, COALESCE(d.nombre,'—') depto,
                             COALESCE(s.prioridad,'') prioridad,
                             COALESCE(s.vacantes,0) vacantes,
                             COALESCE(s.fecha_ingreso_deseada,'') fecha_deseada
                      FROM solicitudes s
                      LEFT JOIN sedes se ON se.id=s.sede_id
                      LEFT JOIN departamentos d ON d.id=s.departamento_id
                      WHERE $where
                      ORDER BY s.id DESC");
    $st->execute($params);
    while($r=$st->fetch(PDO::FETCH_ASSOC)){
      fputcsv($out, [$r['id'],$r['titulo'],$r['puesto'],$r['estado'],$r['sede'],$r['depto'],$r['prioridad'],$r['vacantes'],$r['fecha_deseada']]);
    }
  }catch(\Throwable $e){}
  fclose($out); exit;
}

/* =======================
   REPORTE DE TIEMPOS
   =======================
   Heurística: tomamos los comentarios como “eventos”.
   Detectamos el primer timestamp en el que aparece cada estado por solicitud:
   ENVIADA (fallback = primer comentario), APROBADA, ABIERTA, CERRADA.
   Si CERRADA no aparece en el texto pero la solicitud está en CERRADA, usamos el último comentario.
   Palabras clave tolerantes a acentos y mayúsculas.
*/
$comentarios=[];
try{
  $st=$db->prepare("SELECT sc.solicitud_id, sc.comentario, sc.creado_en,
                           s.titulo, s.estado_actual
                    FROM solicitudes_comentarios sc
                    JOIN solicitudes s ON s.id=sc.solicitud_id
                    WHERE $where
                    ORDER BY sc.solicitud_id ASC, sc.id ASC");
  $st->execute($params);
  while($r=$st->fetch(PDO::FETCH_ASSOC)) $comentarios[] = $r;
}catch(\Throwable $e){}

$timeline = []; // sid => ['ENVIADA'=>time, 'APROBADA'=>time, 'ABIERTA'=>time, 'CERRADA'=>time, 'titulo'=>'', 'estado'=>'']
$kw = [
  'ENVIADA'       => ['enviada','solicitud enviada','creada'],
  'APROBADA'      => ['aprobada','aprobó','aprobado','aprobacion','aprobación'],
  'ABIERTA'       => ['abierta','apertura','publicada'],
  'CERRADA'       => ['cerrada','cerró','cerrado','finalizada','finalizó'],
];
$norm = function($s){
  $s = mb_strtolower((string)$s,'UTF-8');
  $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u']);
  return $s;
};

$lastOf = []; // último comentario por solicitud (para fallback CERRADA)
foreach($comentarios as $c){
  $sid = (int)$c['solicitud_id'];
  $t   = strtotime($c['creado_en'] ?? 'now');
  if(!isset($timeline[$sid])) $timeline[$sid] = ['titulo'=>$c['titulo']??'Solicitud','estado'=>$c['estado_actual']??'','ENVIADA'=>null,'APROBADA'=>null,'ABIERTA'=>null,'CERRADA'=>null];
  $lastOf[$sid] = $t;
  $txt = $norm($c['comentario'] ?? '');
  // ENVIADA: si no hay trigger textual, la primera interacción marca "start"
  if ($timeline[$sid]['ENVIADA']===null) $timeline[$sid]['ENVIADA'] = $t;
  foreach($kw as $estado=>$list){
    foreach($list as $needle){
      if ($needle!=='' && str_contains($txt, $needle)){
        if ($timeline[$sid][$estado]===null) $timeline[$sid][$estado] = $t;
        break;
      }
    }
  }
}
// Fallback CERRADA si estado_actual es CERRADA
foreach($timeline as $sid => &$row){
  if (!$row['CERRADA'] && strtoupper($row['estado'])==='CERRADA'){
    $row['CERRADA'] = $lastOf[$sid] ?? $row['ENVIADA'];
  }
}
unset($row);

/* Duraciones (días) */
$days = fn($a,$b)=> ($a && $b) ? round( (max($b,$a) - $a) / 86400, 2) : null;
$dur_env_apr = []; // ENVIADA → APROBADA
$dur_apr_abi = []; // APROBADA → ABIERTA
$dur_env_cer = []; // ENVIADA → CERRADA
$slow_cases  = []; // para top lentos

foreach($timeline as $sid=>$t){
  $d1 = $days($t['ENVIADA'], $t['APROBADA']);
  $d2 = $days($t['APROBADA'], $t['ABIERTA']);
  $d3 = $days($t['ENVIADA'], $t['CERRADA']);
  if ($d1!==null) $dur_env_apr[] = $d1;
  if ($d2!==null) $dur_apr_abi[] = $d2;
  if ($d3!==null) { 
    $dur_env_cer[] = $d3;
    $slow_cases[] = ['id'=>$sid,'titulo'=>$t['titulo'],'dias'=>$d3];
  }
}
usort($slow_cases, fn($a,$b)=> $b['dias'] <=> $a['dias']);
$top_lentos = array_slice($slow_cases, 0, 8);

/* Histograma de ENVIADA→CERRADA */
$hist = ['0-3'=>0,'4-7'=>0,'8-14'=>0,'15-30'=>0,'30+'=>0];
foreach($dur_env_cer as $v){
  if ($v<=3) $hist['0-3']++;
  elseif ($v<=7) $hist['4-7']++;
  elseif ($v<=14) $hist['8-14']++;
  elseif ($v<=30) $hist['15-30']++;
  else $hist['30+']++;
}
$hist_max = max($hist ?: [1]);

/* ---- Vista ---- */
$titulo_pagina="Reportes";
require $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/header.php';
?>
<style>
:root{
  --ink:#eaf2ff; --muted:#c8d4f0; --ok:#22c55e; --brand:#0D6EFD;
  --glass-bg: linear-gradient(180deg, rgba(255,255,255,.12), rgba(255,255,255,.08));
  --glass-brd: rgba(255,255,255,.26);
}
body{ background:#0a0f1d; }
.rep-wrap{ width:min(1280px,96vw); margin: calc(var(--nav-h,64px) + 16px) auto 28px; color:#eaf2ff; }

/* Hero / filtros */
.rep-hero{
  border:1px solid var(--glass-brd); background:var(--glass-bg); border-radius:20px; padding:14px;
  backdrop-filter: blur(12px); box-shadow:0 28px 80px rgba(0,0,0,.36), inset 0 1px 0 rgba(255,255,255,.18);
}
.rep-title{ margin:0; font-size:clamp(1.5rem,2.2vw,2rem); font-weight:1000 }
.rep-sub{ color:#c8d4f0; margin:4px 0 10px }
.rep-filters{ display:grid; grid-template-columns: 1fr 1fr auto; gap:10px }
@media (max-width: 900px){ .rep-filters{ grid-template-columns: 1fr } }
.form-select, .rep-btn{
  background:#0e1c36; color:#dbe8ff; border:1px solid rgba(255,255,255,.18); border-radius:12px;
}
.rep-btn{ padding:.6rem 1rem }

/* Banner con imágenes */
.banner{
  display:grid; grid-template-columns: repeat(4,1fr); gap:8px; margin-top:10px
}
@media (max-width: 900px){ .banner{ grid-template-columns: repeat(2,1fr) } }
.banner img{
  width:100%; height:110px; object-fit:cover; border-radius:14px; border:1px solid rgba(255,255,255,.18);
  box-shadow:0 10px 30px rgba(0,0,0,.25);
}

/* KPIs */
.kpis{ display:grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap:10px; margin-top:12px }
@media (max-width: 1100px){ .kpis{ grid-template-columns: repeat(2,1fr) } }
@media (max-width: 560px){ .kpis{ grid-template-columns: 1fr } }
.kpi{
  border:1px solid rgba(255,255,255,.18); border-radius:16px; background:linear-gradient(180deg,#0c1428,#0b1120);
  padding:12px; box-shadow:0 14px 44px rgba(0,0,0,.32);
}
.kpi h4{ margin:0 0 6px; font-weight:900 }
.kpi .big{ font-size:1.8rem; font-weight:1000 }
.meta{ color:#9fb3ff; font-size:.88rem }

/* Grid principal */
.grid-2-2{ display:grid; grid-template-columns: 2fr 1fr; gap:10px; margin-top:10px }
@media (max-width: 1100px){ .grid-2-2{ grid-template-columns:1fr } }

.card-g{
  border:1px solid rgba(255,255,255,.18); border-radius:16px; background:linear-gradient(180deg,#0c1428,#0b1120);
  padding:12px; box-shadow:0 14px 44px rgba(0,0,0,.32);
}
.card-title{ margin:0 0 8px; font-weight:900 }

/* Line chart (SVG) */
.chart{
  width:100%; height:220px; border-radius:12px; background:#0b162d; border:1px solid rgba(255,255,255,.12);
  display:grid; place-items:center; overflow:hidden;
}
.chart svg{ width:100%; height:100% }

/* Donut */
.donut{ display:grid; grid-template-columns: 120px 1fr; gap:10px; align-items:center }
.donut-pie{ width:120px; height:120px; border-radius:50%; background: conic-gradient(#60a5fa 0deg, #60a5fa 0deg); position:relative }
.donut-hole{ position:absolute; inset:12px; background:#0c1428; border-radius:50%;
  display:grid; place-items:center; font-weight:1000; color:#eaf2ff; border:1px solid rgba(255,255,255,.12) }
.legend{ display:grid; gap:6px }
.legend .rowx{ display:flex; align-items:center; gap:8px; font-size:.92rem }
.legend .sw{ width:10px; height:10px; border-radius:3px }

/* Barras simples */
.bar{ height:10px; border-radius:999px; background:#0f203d; overflow:hidden; margin-top:6px }
.bar > i{ display:block; height:100% }

/* --------- Reporte de tiempos --------- */
.grid-times{ display:grid; grid-template-columns: 1.6fr 1fr; gap:10px; margin-top:10px }
@media (max-width: 1100px){ .grid-times{ grid-template-columns:1fr } }
.histo{ background:#0b162d; border:1px solid rgba(255,255,255,.12); border-radius:12px; padding:10px }
.histo-row{ display:grid; grid-template-columns: 110px 1fr 56px; gap:10px; align-items:center; margin:8px 0 }
.histo .bar{ background:#0f203d }
.histo .bar > i{ background:#0D6EFD }
.table-times{ width:100%; border-collapse:separate; border-spacing:0 8px }
.table-times tr{ background:#0b162d; border:1px solid rgba(255,255,255,.12) }
.table-times td{ padding:10px 12px }
.badge-soft{ display:inline-block; padding:.18rem .55rem; border-radius:999px; border:1px solid rgba(255,255,255,.18);
  background:#0e1c36; color:#cfe0ff; font-size:.78rem }
</style>

<div class="rep-wrap">
  <div class="rep-hero">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <h1 class="rep-title">Reportes</h1>
        <div class="rep-sub">Resumen general de solicitudes — filtros por sede y departamento.</div>
      </div>
      <div>
        <a class="btn btn-primary" href="?<?= http_build_query(array_filter(['sede'=>$f_sede?:null,'dep'=>$f_dep?:null,'export'=>'csv'])) ?>">⬇ Exportar CSV</a>
      </div>
    </div>

    <form method="get" class="rep-filters mt-2" action="">
      <select name="sede" class="form-select">
        <option value="0">Todas las sedes</option>
        <?php foreach($sedes as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= $f_sede===(int)$s['id']?'selected':'' ?>><?= esc($s['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="dep" class="form-select">
        <option value="0">Todos los departamentos</option>
        <?php foreach($departamentos as $d): ?>
          <option value="<?= (int)$d['id'] ?>" <?= $f_dep===(int)$d['id']?'selected':'' ?>><?= esc($d['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="rep-btn">Aplicar</button>
    </form>

    <!-- Banner -->
    <div class="banner">
      <img src="<?= BASE_PATH ?>/public/img/hero/hero1.jpg" alt="" onerror="this.style.display='none'">
      <img src="<?= BASE_PATH ?>/public/img/hero/hero2.jpg" alt="" onerror="this.style.display='none'">
      <img src="<?= BASE_PATH ?>/public/img/hero/hero3.jpg" alt="" onerror="this.style.display='none'">
      <img src="<?= BASE_PATH ?>/public/img/hero/hero4.jpg" alt="" onerror="this.style.display='none'">
    </div>
  </div>

  <!-- KPIs -->
  <section class="kpis">
    <div class="kpi">
      <h4>Total de solicitudes</h4>
      <div class="big"><?= number_format($total) ?></div>
      <div class="meta">con filtros actuales</div>
    </div>
    <div class="kpi">
      <h4>En proceso</h4>
      <?php
        $enProceso=0;
        foreach($por_estado as $e){
          if (in_array($e['estado'], ['ENVIADA','EN_REV_GER','APROBADA','BUSCANDO','EN_ENTREVISTA','EN_DECISION','ABIERTA'], true)) $enProceso+=(int)$e['n'];
        }
      ?>
      <div class="big"><?= number_format($enProceso) ?></div>
      <div class="meta">enviadas, revisión y abiertas</div>
    </div>
    <div class="kpi">
      <h4>Movimientos (14 días)</h4>
      <div class="big"><?= number_format($mov_14) ?></div>
      <div class="meta">comentarios/cambios recientes</div>
    </div>
    <div class="kpi">
      <h4>Prom. movs/sol (14d)</h4>
      <div class="big"><?= number_format($prom_mov_x_sol,2) ?></div>
      <div class="meta">actividad por solicitud</div>
    </div>
  </section>

  <section class="grid-2-2">
    <!-- Gráfico línea 14 días -->
    <div class="card-g">
      <h5 class="card-title">Actividad (últimos 14 días)</h5>
      <div class="chart">
        <?php
          $map=[]; foreach($serie_14 as $r){ $map[$r['d']] = (int)$r['n']; }
          $pts=[]; $labels=[]; $days=14; $maxV=1;
          for($i=$days-1; $i>=0; $i--){
            $d = date('Y-m-d', strtotime("-$i day"));
            $v = $map[$d] ?? 0; $pts[]=$v; $labels[]=$d; if($v>$maxV) $maxV=$v;
          }
          $W=760; $H=200; $PL=30; $PR=10; $PT=10; $PB=28;
          $chartW=$W-$PL-$PR; $chartH=$H-$PT-$PB; $n=count($pts); $path='';
          if($n>0){
            for($i=0;$i<$n;$i++){
              $x=$PL + ($n==1?0:($chartW*($i/($n-1))));
              $y=$PT + ($chartH * (1 - ($pts[$i]/$maxV)));
              $path .= ($i===0?'M':'L') . round($x,1) . ' ' . round($y,1) . ' ';
            }
          }
        ?>
        <svg viewBox="0 0 <?= $W ?> <?= $H ?>" preserveAspectRatio="none">
          <?php for($g=0;$g<=4;$g++): $gy = $PT + ($chartH * ($g/4)); ?>
            <line x1="<?= $PL ?>" y1="<?= $gy ?>" x2="<?= $W-$PR ?>" y2="<?= $gy ?>" stroke="rgba(255,255,255,.08)" stroke-width="1"/>
          <?php endfor; ?>
          <?php if($path): ?>
            <path d="<?= $path ?> L <?= $PL+$chartW ?> <?= $PT+$chartH ?> L <?= $PL ?> <?= $PT+$chartH ?> Z" fill="rgba(13,110,253,.28)"></path>
            <path d="<?= $path ?>" fill="none" stroke="#0D6EFD" stroke-width="2.5" />
          <?php endif; ?>
          <?php for($i=0; $i<$n; $i+=3):
            $x=$PL + ($n==1?0:($chartW*($i/($n-1))));
            $d=DateTime::createFromFormat('Y-m-d',$labels[$i]); $txt=$d?$d->format('d/m'):$labels[$i];
          ?>
            <text x="<?= $x ?>" y="<?= $H-8 ?>" fill="#9fb3ff" font-size="11" text-anchor="middle"><?= $txt ?></text>
          <?php endfor; ?>
          <text x="<?= $PL ?>" y="<?= $PT+10 ?>" fill="#cfe0ff" font-size="11">max: <?= (int)$maxV ?></text>
        </svg>
      </div>
    </div>

    <!-- Donut por estado + leyenda -->
    <div class="card-g">
      <h5 class="card-title">Distribución por estado</h5>
      <?php
        $sumEstados = array_sum(array_map(fn($r)=>(int)$r['n'],$por_estado)) ?: 1;
        $acc=0; $segments=[];
        foreach($por_estado as $r){
          $col=estado_color($r['estado']);
          $ang = (int)round(($r['n']/$sumEstados)*360);
          $segments[]=['c'=>$col,'from'=>$acc,'to'=>$acc+$ang,'n'=>$r['n'],'e'=>$r['estado']];
          $acc += $ang;
        }
      ?>
      <div class="donut">
        <div class="donut-pie" id="donutPie"></div>
        <div class="legend">
          <?php foreach($por_estado as $r): $col=estado_color($r['estado']); ?>
            <div class="rowx">
              <span class="sw" style="background:<?= $col ?>;"></span>
              <span><?= esc($r['estado']) ?></span>
              <span class="meta ms-auto"><?= (int)$r['n'] ?></span>
            </div>
          <?php endforeach; if(!$por_estado): ?>
            <div class="meta">Sin datos</div>
          <?php endif; ?>
        </div>
      </div>
      <script>
        (function(){
          const el=document.getElementById('donutPie');
          if(!el) return;
          const segs = <?= json_encode($segments) ?>;
          if(!segs.length){ el.style.background='conic-gradient(#1f2937 0deg 360deg)'; }
          else{
            const stops = segs.map(s => `${s.c} ${s.from}deg ${s.to}deg`).join(', ');
            el.style.background = `conic-gradient(${stops})`;
          }
          const hole=document.createElement('div');
          hole.className='donut-hole';
          hole.innerHTML='<div><?= number_format($sumEstados) ?><br><small class="meta">total</small></div>';
          el.appendChild(hole);
        })();
      </script>
    </div>
  </section>

  <!-- ====== REPORTE DE TIEMPOS (reemplaza Actividad reciente) ====== -->
  <section class="grid-times">
    <div class="card-g">
      <h5 class="card-title">Tiempos del proceso</h5>
      <div class="row g-3">
        <div class="col-md-4">
          <div class="kpi">
            <h4>ENVIADA → APROBADA</h4>
            <div class="big"><?= number_format(array_sum($dur_env_apr) / (count($dur_env_apr) ?: 1), 2) ?> d</div>
            <div class="meta">mediana: <?= number_format(median($dur_env_apr),2) ?> d</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="kpi">
            <h4>APROBADA → ABIERTA</h4>
            <div class="big"><?= number_format(array_sum($dur_apr_abi) / (count($dur_apr_abi) ?: 1), 2) ?> d</div>
            <div class="meta">mediana: <?= number_format(median($dur_apr_abi),2) ?> d</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="kpi">
            <h4>ENVIADA → CERRADA</h4>
            <div class="big"><?= number_format(array_sum($dur_env_cer) / (count($dur_env_cer) ?: 1), 2) ?> d</div>
            <div class="meta">mediana: <?= number_format(median($dur_env_cer),2) ?> d</div>
          </div>
        </div>
      </div>

      <div class="histo mt-3">
        <h6 class="mb-2">Tiempo de ciclo (ENVIADA → CERRADA) · distribución</h6>
        <?php foreach($hist as $lbl=>$cnt):
          $pct = $hist_max ? round(($cnt/$hist_max)*100) : 0;
        ?>
          <div class="histo-row">
            <div class="badge-soft"><?= $lbl ?> días</div>
            <div class="bar"><i style="width:<?= $pct ?>%"></i></div>
            <div class="meta text-end"><?= (int)$cnt ?></div>
          </div>
        <?php endforeach; ?>
        <?php if(!array_sum($hist)): ?>
          <div class="meta">Sin datos suficientes para cerrar ciclos.</div>
        <?php endif; ?>
      </div>
      <div class="meta mt-2">Nota: estos tiempos se infieren de los textos de comentarios (palabras clave como “aprobada”, “abierta”, “cerrada”). Si un estado no aparece explícito, usamos el primer/último comentario como referencia.</div>
    </div>

    <div class="card-g">
      <h5 class="card-title">Top casos lentos (ENVIADA → CERRADA)</h5>
      <?php if(!$top_lentos): ?>
        <div class="meta">Sin casos cerrados bajo los filtros actuales.</div>
      <?php else: ?>
        <table class="table-times">
          <tbody>
          <?php foreach($top_lentos as $c): 
            $link = BASE_PATH . '/app/views/admin/solicitudes/detalle.php?id=' . (int)$c['id'];
          ?>
            <tr>
              <td style="width:68px"><span class="badge-soft">ID #<?= (int)$c['id'] ?></span></td>
              <td style="max-width:420px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= esc($c['titulo']) ?></td>
              <td style="width:120px" class="text-end"><strong><?= number_format($c['dias'],2) ?></strong> d</td>
              <td style="width:92px" class="text-end"><a class="badge-soft" href="<?= $link ?>">Ver detalle</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </section>

  <!-- Distribuciones por sede y departamento -->
  <section class="grid-2-2" style="grid-template-columns: 1fr 1fr;">
    <div class="card-g">
      <h5 class="card-title">Por sede</h5>
      <?php if(!$por_sede): ?>
        <div class="meta">Sin datos</div>
      <?php else:
        $max = max(array_map(fn($r)=>(int)$r['n'],$por_sede)) ?: 1;
        foreach($por_sede as $r):
          $pct = round(((int)$r['n']/$max)*100);
      ?>
        <div class="d-flex align-items-center justify-content-between">
          <span class="badge-soft"><?= esc($r['sede']) ?></span>
          <span class="meta"><?= (int)$r['n'] ?></span>
        </div>
        <div class="bar"><i style="width:<?= $pct ?>%; background:#60a5fa;"></i></div>
      <?php endforeach; endif; ?>
    </div>

    <div class="card-g">
      <h5 class="card-title">Por departamento</h5>
      <?php if(!$por_dep): ?>
        <div class="meta">Sin datos</div>
      <?php else:
        $max = max(array_map(fn($r)=>(int)$r['n'],$por_dep)) ?: 1;
        foreach($por_dep as $r):
          $pct = round(((int)$r['n']/$max)*100);
      ?>
        <div class="d-flex align-items-center justify-content-between">
          <span class="badge-soft"><?= esc($r['dep']) ?></span>
          <span class="meta"><?= (int)$r['n'] ?></span>
        </div>
        <div class="bar"><i style="width:<?= $pct ?>%; background:#a78bfa;"></i></div>
      <?php endforeach; endif; ?>
    </div>
  </section>
</div>

<?php require $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/footer.php'; ?>
