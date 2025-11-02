<?php
// ============================================================
// Candidatos (RH/Admin) — Index con tabla ordenable + drawer
// Ruta: /app/views/admin/candidatos/index.php?sol=###
// ============================================================
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
if (session_status() === PHP_SESSION_NONE) session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
$db = Conexion::getConexion();

/* ---- Sesión ---- */
$UID = (int)($_SESSION['id'] ?? 0);
$ROL = strtolower($_SESSION['rol'] ?? '');
$SEDE= isset($_SESSION['sede_id']) ? (int)$_SESSION['sede_id'] : null;
if (!$UID || !in_array($ROL, ['admin','rh','gerente','jefe_area'], true)) {
  header('Location: '.BASE_PATH.'/public/login.php'); exit;
}

/* ---- Helpers ---- */
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function mayus($s){ return mb_strtoupper((string)$s,'UTF-8'); }
function list_norm($txt){
  $t = mayus((string)$txt);
  $t = str_replace(['|',';'], ',', $t);
  $arr = array_filter(array_map('trim', explode(',', $t)));
  return array_values(array_unique($arr));
}
function ingles_rank($nivel){
  $map=['NO'=>0,'BASICO'=>1,'INTERMEDIO'=>2,'AVANZADO'=>3,'NATIVO'=>4];
  $n = mayus($nivel);
  return $map[$n] ?? 0;
}
// ======= NUEVO: helpers para normalizar y evitar fallos por acentos/espacios =======
function sin_acentos($s){
  $repl = [
    'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ü'=>'U','Ñ'=>'N',
    'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n'
  ];
  return strtr((string)$s, $repl);
}
function norm_val($s){
  $s = sin_acentos($s);
  $s = mb_strtoupper($s,'UTF-8');
  $s = preg_replace('/\s+/',' ', trim($s));
  return $s;
}

/* ---- Param solicitud ---- */
$sol = (int)($_GET['sol'] ?? 0);
$alert=null; $atype='success';

/* ---- Solicitudes visibles para selector ---- */
$solicitudes=[];
try{
  $st = $db->prepare("
    SELECT s.id, s.titulo, s.puesto, s.estado_actual, s.ingles_combo,
           se.nombre sede, d.nombre dep, u.nombre_completo autor, s.sede_id, s.autor_id,
           s.experiencia_anios, s.escolaridad_min, s.carrera_estudiada, s.area_experiencia, s.competencias_json
    FROM solicitudes s
    LEFT JOIN sedes se ON se.id=s.sede_id
    LEFT JOIN departamentos d ON d.id=s.departamento_id
    LEFT JOIN usuarios u ON u.id=s.autor_id
    WHERE
      (
        :rol IN ('admin','rh')
        OR (:rol='gerente' AND s.sede_id=:sede)
        OR (:rol='jefe_area' AND s.autor_id=:uid)
      )
      AND s.estado_actual IN ('APROBADA','BUSCANDO','EN_ENTREVISTA','EN_DECISION','ABIERTA','CERRADA')
    ORDER BY s.id DESC
  ");
  $st->execute([':rol'=>$ROL, ':sede'=>$SEDE, ':uid'=>$UID]);
  $solicitudes = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}catch(Throwable $e){
  $alert='No se pudieron cargar solicitudes: '.$e->getMessage(); $atype='danger';
}

/* ---- Cargar solicitud activa ---- */
$S=null; $estaEnEntrevista=false;
if ($sol>0){
  try{
    $st=$db->prepare("
      SELECT s.*, se.nombre AS sede, d.nombre AS dep, u.nombre_completo AS autor
      FROM solicitudes s
      LEFT JOIN sedes se ON se.id=s.sede_id
      LEFT JOIN departamentos d ON d.id=s.departamento_id
      LEFT JOIN usuarios u ON u.id=s.autor_id
      WHERE s.id=? LIMIT 1
    ");
    $st->execute([$sol]); $S=$st->fetch(PDO::FETCH_ASSOC);
    if(!$S){ $alert='Solicitud no encontrada.'; $atype='danger'; $sol=0; }
    else{
      $visible=false;
      if ($ROL==='admin' || $ROL==='rh') $visible=true;
      elseif($ROL==='gerente') $visible=($SEDE && (int)$S['sede_id']===$SEDE);
      elseif($ROL==='jefe_area') $visible=((int)$S['autor_id']===$UID);
      if(!$visible){ $alert='Sin permiso para esta solicitud.'; $atype='danger'; $sol=0; $S=null; }
      else { $estaEnEntrevista = in_array($S['estado_actual'], ['EN_ENTREVISTA','EN_DECISION','CERRADA'], true); }
    }
  }catch(Throwable $e){
    $alert='Error al cargar solicitud: '.$e->getMessage(); $atype='danger'; $sol=0;
  }
}

/* ========= ALGORITMO COMPATIBILIDAD ========= */
function escolar_to_ord($t){
  $t=mayus($t);
  if (str_contains($t,'DOCTOR')) return 4;
  if (str_contains($t,'MAESTR')) return 3;
  if (str_contains($t,'LICEN')) return 2;
  if (str_contains($t,'PREPA')||str_contains($t,'BACH')) return 1;
  return 0;
}
function carrera_afín($req, $cand){
  $req = mayus((string)$req);
  $cand= mayus((string)$cand);
  if($req===''||$cand==='') return false;
  $norm = fn($s)=>preg_replace('/[^A-Z0-9 ]/u','',str_replace(['INGENIERIA','ING.','LIC.','LICENCIATURA EN','LICENCIATURA','MAESTRIA','DOCTORADO'],'',$s));
  $r = $norm($req); $c=$norm($cand);
  $rTokens = array_filter(explode(' ', $r));
  foreach($rTokens as $tk){ if($tk!=='' && str_contains($c,$tk)) return true; }
  $map=[ 'SISTEMAS'=>'INFORMATICA', 'COMPUTACION'=>'INFORMATICA', 'ADMINISTRACION'=>'ADMINISTRACION', 'LOGISTICA'=>'LOGISTICA' ];
  foreach($map as $a=>$b){ if((str_contains($r,$a)&&str_contains($c,$b))||(str_contains($r,$b)&&str_contains($c,$a))) return true; }
  return str_contains($c,$r);
}
function compat_calc(array $c, array $S): array {
  $score=0; $det=[];
  $minReq = (int)($S['escolaridad_min'] ?? 0);
  $cand   = escolar_to_ord($c['EducationLevel'] ?? '');
  if ($minReq>0){
    if ($cand >= $minReq){ $score+=10; $det[]='Escolaridad: cumple'; } else { $det[]='Escolaridad: no cumple'; }
  }
  $expReq = (int)($S['experiencia_anios'] ?? 0);
  $expC   = (int)($c['ExperienceYears'] ?? 0);
  if ($expC >= $expReq){ $score+=20; $det[]='Experiencia: cumple'; } else { $det[]='Experiencia: no cumple'; }
  $carReq = (string)($S['carrera_estudiada'] ?? '');
  $carC   = (string)($c['Carrera'] ?? '');
  if (carrera_afín($carReq,$carC)){ $score+=25; $det[]='Carrera: afín'; } else { $det[]='Carrera: no afín'; }
  $areasReq = list_norm($S['area_experiencia'] ?? '');
  $areasC   = list_norm($c['Área de experiencia'] ?? '');
  $inter = array_intersect($areasReq, $areasC);
  if (count($inter)>=1){ $score+=20; $det[]='Áreas: coinciden'; } else { $det[]='Áreas: sin coincidencia'; }
  $compReq = json_decode((string)($S['competencias_json'] ?? '[]'), true);
  $compReq = is_array($compReq) ? array_map('mayus',$compReq) : [];
  $compC   = list_norm($c['Competencias técnicas'] ?? '');
  $match   = 0;
  if ($compReq){
    foreach($compReq as $req){ if (in_array(mayus($req), $compC, true)) $match++; }
    $ratio = (count($compReq)>0) ? ($match / count($compReq)) : 0;
    $score += (int)round(15 * $ratio);
  }
  $ireqTxt = mayus((string)($S['ingles_combo'] ?? '0'));
  $ireq = ($ireqTxt==='0'||$ireqTxt==='NO') ? 0 : ingles_rank($ireqTxt);
  $ipost= ingles_rank($c['Inglés_postulante'] ?? 'NO');
  if ($ireq === 0){ $score += 10; $det[]='Inglés: no requerido'; }
  else {
    if ($ipost >= $ireq){ $score += 10; $det[]='Inglés: cumple'; } else { $det[]='Inglés: no cumple'; }
  }
  $pct = max(0,min(100,$score));
  return [$pct,$det];
}

/* ---- Acciones POST ---- */
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/helpers/IAIntegration.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($ROL, ['admin','rh'], true)) {
  $act = $_POST['act'] ?? '';
  try {
    if ($act === 'alta' && $sol > 0) {
      $nombre = mayus($_POST['Nombre_completo'] ?? '');
      $tel    = trim((string)($_POST['Telefono'] ?? ''));
      $correo = trim((string)($_POST['Correo'] ?? ''));
      $fuente = mayus((string)($_POST['RecruitmentStrategy'] ?? ''));
      $carrera= mayus($_POST['Carrera'] ?? '');
      $esc    = mayus((string)($_POST['EducationLevel'] ?? ''));
      $exp    = (int)($_POST['ExperienceYears'] ?? 0);
      $areas  = mayus($_POST['Área_de_experiencia'] ?? '');
      $comps  = mayus($_POST['Competencias_técnicas'] ?? '');
      $ingReq = mayus((string)($_POST['Inglés_requerido'] ?? 'NO'));
      $ingPos = mayus((string)($_POST['Inglés_postulante'] ?? 'NO'));

      if ($nombre === '' || $correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL))
        throw new Exception('Nombre y correo válidos son obligatorios.');

      $ins = $db->prepare("
        INSERT INTO `postulantes_por_vacante`
          (`solicitud_id`,`Nombre completo`,`Telefono`,`Correo`,`RecruitmentStrategy`,`Carrera`,
           `EducationLevel`,`ExperienceYears`,`Área de experiencia`,`Competencias técnicas`,
           `Inglés_requerido`,`Inglés_postulante`)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
      ");
      $ins->execute([$sol,$nombre,$tel,$correo,$fuente,$carrera,$esc,$exp,$areas,$comps,$ingReq,$ingPos]);
      header('Location: ?sol='.$sol.'&ok=1'); exit;
    }

    if ($act === 'eliminar' && $sol > 0) {
      $id = (int)($_POST['id'] ?? 0);
      $db->prepare("DELETE FROM `postulantes_por_vacante` WHERE id=? AND solicitud_id=?")->execute([$id, $sol]);
      header('Location: ?sol='.$sol.'&del=1'); exit;
    }

    if ($act === 'editar' && $sol > 0) {
      $id     = (int)($_POST['id'] ?? 0);
      $nombre = mayus($_POST['Nombre_completo'] ?? '');
      $tel    = trim((string)($_POST['Telefono'] ?? ''));
      $fuente = mayus((string)($_POST['RecruitmentStrategy'] ?? ''));
      $carrera= mayus($_POST['Carrera'] ?? '');
      $esc    = mayus((string)($_POST['EducationLevel'] ?? ''));
      $exp    = (int)($_POST['ExperienceYears'] ?? 0);
      $areas  = mayus($_POST['Área_de_experiencia'] ?? '');
      $comps  = mayus($_POST['Competencias_técnicas'] ?? '');
      $ingPos = mayus((string)($_POST['Inglés_postulante'] ?? 'NO'));
      $up = $db->prepare("
        UPDATE `postulantes_por_vacante`
           SET `Nombre completo`=?,`Telefono`=?,`RecruitmentStrategy`=?,`Carrera`=?,
               `EducationLevel`=?,`ExperienceYears`=?,`Área de experiencia`=?,`Competencias técnicas`=?,
               `Inglés_postulante`=?,`updated_at`=NOW()
         WHERE id=? AND solicitud_id=?
      ");
      $up->execute([$nombre,$tel,$fuente,$carrera,$esc,$exp,$areas,$comps,$ingPos,$id,$sol]);
      header('Location: ?sol='.$sol.'&upd=1'); exit;
    }

    if ($act === 'a_entrevista' && $sol > 0) {
      $id = (int)($_POST['id'] ?? 0);
      $cur = $db->prepare("SELECT estado_actual FROM solicitudes WHERE id=?");
      $cur->execute([$sol]);
      $est = (string)$cur->fetchColumn();
      if (!in_array($est, ['EN_ENTREVISTA','EN_DECISION','CERRADA'], true)) {
        $db->prepare("UPDATE solicitudes SET estado_actual='EN_ENTREVISTA' WHERE id=?")->execute([$sol]);
      }
      header('Location: ?sol='.$sol.'&toint=1'); exit;
    }

    if ($act === 'guardar_puntajes' && $sol > 0) {
      $id  = (int)($_POST['id'] ?? 0);
      $ps  = max(0, min(100, (int)($_POST['PersonalityScore'] ?? 0)));
      $ss  = max(0, min(100, (int)($_POST['SkillScore'] ?? 0)));
      $is  = max(0, min(100, (int)($_POST['InterviewScore'] ?? 0)));

      $db->prepare("
        UPDATE `postulantes_por_vacante`
           SET `PersonalityScore`=?,`SkillScore`=?,`InterviewScore`=?,`updated_at`=NOW()
         WHERE id=? AND solicitud_id=?
      ")->execute([$ps,$ss,$is,$id,$sol]);

      // IA SOLO AQUÍ (y en ranking_demo)
      $res = ia_predict_for_candidate($id);
      $eval = (int)$res['evaluation_score'];
      $via  = $res['viability'];

      $db->prepare("
        UPDATE `postulantes_por_vacante`
           SET `Puntaje de evaluación`=?, `Viabilidad`=?, `updated_at`=NOW()
         WHERE id=? AND solicitud_id=?
      ")->execute([$eval,$via,$id,$sol]);

      header('Location: ?sol='.$sol.'&scores=1'); exit;
    }

    if ($act === 'ranking_demo' && $sol > 0) {
      $count = ia_rank_for_solicitud($sol);
      header('Location: ?sol='.$sol.'&rank=1&count='.$count); exit;
    }

    if ($act === 'contratar' && $sol > 0) {
      $id = (int)($_POST['id'] ?? 0);
      $db->prepare("UPDATE `postulantes_por_vacante` SET `Decisión_Final`='CONTRATADO', `updated_at`=NOW() WHERE id=? AND solicitud_id=?")->execute([$id,$sol]);
      $db->prepare("UPDATE solicitudes SET estado_actual='CERRADA' WHERE id=?")->execute([$sol]);
      header('Location: ?sol='.$sol.'&done=1'); exit;
    }

  } catch (Throwable $e) {
    $alert = 'Acción no completada: ' . $e->getMessage();
    $atype = 'danger';
  }
}

/* ---- Cargar candidatos + calcular compatibilidad y ordenar default por compatibilidad ---- */
$cands=[];
if ($sol>0 && $S){
  try{
    $q=$db->prepare("SELECT * FROM `postulantes_por_vacante` WHERE solicitud_id=?");
    $q->execute([$sol]); $cands=$q->fetchAll(PDO::FETCH_ASSOC) ?: [];
    foreach($cands as &$c){
      [$pct,$det] = compat_calc($c,$S);
      $c['_compat_pct']=$pct; $c['_compat_det']=$det;
    }
    unset($c);
    usort($cands,function($a,$b){
      $cmp = ($b['_compat_pct'] <=> $a['_compat_pct']);
      if ($cmp!==0) return $cmp;
      $ea = (int)($a['Puntaje de evaluación'] ?? 0);
      $eb = (int)($b['Puntaje de evaluación'] ?? 0);
      $cmp = ($eb <=> $ea);
      if ($cmp!==0) return $cmp;
      return ((int)$b['id'] <=> (int)$a['id']);
    });
  }catch(Throwable $e){
    $alert='Error al cargar candidatos: '.$e->getMessage(); $atype='danger';
  }
}

/* ---- UI ---- */
$titulo_pagina="Candidatos (RH)";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/header.php';
?>
<style>
/* ====== NAVBAR GAP FIX ====== */
:root{
  --ink:#0e2239; --muted:#5a6a85; --line:#dbe4f0; --soft:#f7faff;
  --brand:#2563eb; --ok:#16a34a; --warn:#eab308; --bad:#dc2626;
  --chip:#ecf2ff; --shadow:0 10px 30px rgba(15,30,60,.10);
  --navh: var(--nav-h, 64px);
}
body{ background:linear-gradient(180deg,#eaf3ff,#ffffff 30%); padding-top:0!important; }
.navbar{ margin-bottom:0!important; }
.mt-nav{ margin-top:0!important; }
.container-xxl{max-width:1500px!important}
.wrap{ padding: 12px 16px 28px; }

/* TOASTS */
.toaster{position:fixed; top:14px; right:14px; z-index:1055; display:grid; gap:10px}
.toastish{display:flex;align-items:center;gap:.6rem;border-radius:12px;padding:.6rem .9rem;border:1px solid;font-weight:700; box-shadow:var(--shadow); backdrop-filter: blur(4px)}
.toastish.ok{background:#ecfdf5;border-color:#bbf7d0;color:#065f46}
.toastish.warn{background:#fffbeb;border-color:#fde68a;color:#92400e}
.toastish.bad{background:#fef2f2;border-color:#fecaca;color:#991b1b}

/* CARDS */
.cardish{border:1px solid var(--line); border-radius:14px; background:#fff; box-shadow:var(--shadow)}
.card-head{display:flex; gap:8px; align-items:center; justify-content:space-between; padding:10px 12px; border-bottom:1px solid var(--line); background:#0f1b2e; color:#fff; border-radius:14px 14px 0 0}
.card-body{padding:12px}

/* SELECTOR */
.selector{display:grid; grid-template-columns:1fr auto; gap:10px; align-items:center}
.selector .form-select{height:46px}
.btn-primary{background:#0b72ff; border-color:#0b72ff}
.btn-success{background:#16a34a; border-color:#16a34a}
.btn-outline-primary{border-color:#0b72ff; color:#0b72ff}
.btn-outline-primary:hover{background:#0b72ff;color:#fff}

/* ACCORDION */
.accord{border:1px dashed #cfe0ff; border-radius:12px; background:#f8fbff;}
.accord .acc-head{display:flex; align-items:center; justify-content:space-between; padding:10px 12px; cursor:pointer}
.acc-body{padding:10px 12px; display:none}
.acc-open .acc-body{display:block}

/* ACTION BAR */
.actionbar{display:grid; grid-template-columns: 1fr auto auto auto; gap:10px; align-items:center; margin-top:10px}
.searcher input{height:44px}

/* TABLE */
.table-wrap{overflow:auto; border:1px solid var(--line); border-radius:14px; background:#fff; box-shadow:var(--shadow)}
table.datagrid{width:100%; border-collapse:separate; border-spacing:0}
.datagrid thead th{position:sticky; top:0; background:#0f1b2e; color:#fff; z-index:2; padding:12px 10px; font-weight:900; border-bottom:1px solid var(--line)}
.datagrid tbody td{padding:12px 10px; border-bottom:1px solid #eef2f8; vertical-align:top}
.datagrid tbody tr:nth-child(odd){background:#fbfdff}
.datagrid tbody tr:hover{background:#f0f6ff; cursor:pointer}
th.sortable{user-select:none}
th.sortable .hint{opacity:.8; font-size:.88rem; margin-left:6px}
th.sortable.active{color:#93c5fd}
th.sortable.active:after{content: attr(data-dir); font-weight:900; margin-left:6px}
.small{font-size:.92rem; color:var(--muted)}
.tag{display:inline-flex;align-items:center;gap:.4rem; border:1px dashed #dbe4f0; background:#fff; border-radius:999px; padding:.16rem .55rem; font-weight:700}
.meter{height:7px; background:#e8eefc; border-radius:999px; overflow:hidden}
.meter>i{display:block; height:100%; background:linear-gradient(90deg,#22c55e,#3b82f6)}
.medal{display:inline-flex; align-items:center; gap:6px; font-weight:800; padding:.14rem .5rem; border-radius:999px; border:1px solid #e6efff; background:#fff}
.medal.top{background:#fff7e6; border-color:#fde68a}

/* DRAWER */
.drawer{position:fixed; top:0; right:-560px; width:560px; height:100vh; background:#ffffff; border-left:1px solid var(--line); box-shadow:-10px 0 30px rgba(15,30,60,.2); z-index:1050; transition:right .28s ease}
.drawer.open{right:0}
.drawer-head{padding:12px 16px; background:#0f1b2e; color:#fff}
.drawer-title{display:flex; align-items:center; justify-content:space-between; gap:8px}
.drawer-sub{opacity:.9; font-size:.92rem; margin-top:2px}
.drawer-body{padding:14px 16px; overflow:auto; height:calc(100vh - 56px)}
.dw-section{margin-bottom:14px}
.dw-section h6{font-weight:900; font-size:.96rem; margin-bottom:8px; color:#0e2239}
.kv{display:flex; gap:.4rem; flex-wrap:wrap}
.kv .chip{background:#eff6ff; border:1px solid #dbeafe; color:#1d4ed8; border-radius:999px; padding:.2rem .6rem; font-weight:800}
.dw-actions{display:grid; grid-template-columns:repeat(2,minmax(140px,1fr)); gap:8px}
.dw-actions .btn{width:100%}

/* Modals offset */
.modal-dialog{margin-top: calc(var(--navh) + 10px);}
.modal-header{background:#0f1b2e;color:#fff}
.modal-title{font-weight:900}
</style>

<div class="toaster" id="toaster">
  <?php if ($alert): ?><div class="toastish <?= $atype==='danger'?'bad':($atype==='warning'?'warn':'ok') ?>"><?= esc($alert) ?></div><?php endif; ?>
  <?php foreach([
    'ok'=>'Candidato registrado con éxito.',
    'del'=>'Candidato eliminado.',
    'upd'=>'Candidato actualizado.',
    'toint'=>'Candidato enviado a entrevista.',
    'scores'=>'Puntajes de entrevista guardados.',
    'rank'=>'Evaluación IA calculada.',
    'done'=>'Contratación registrada y solicitud cerrada.'
  ] as $k=>$msg): if (isset($_GET[$k])): ?>
    <div class="toastish ok"><?= esc($msg) ?></div>
  <?php endif; endforeach; ?>
</div>

<div class="container-xxl wrap">
  <!-- Selector de solicitud -->
  <div class="cardish">
    <div class="card-head">
      <b>Candidatos por solicitud</b>
      <div class="d-flex gap-2">
        <?php if (in_array($ROL,['admin','rh'])): ?>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAlta">+ Registrar candidato</button>
        <?php endif; ?>
      </div>
    </div>
    <div class="card-body">
      <form method="get" class="selector">
        <select name="sol" class="form-select" required onchange="this.form.submit()">
          <option value="">— Selecciona una solicitud —</option>
          <?php foreach($solicitudes as $row): $sel=($sol>0 && $sol==(int)$row['id'])?'selected':''; ?>
            <option value="<?= (int)$row['id'] ?>" <?= $sel ?>>
              ID-<?= (int)$row['id'] ?> · <?= esc($row['titulo'] ?: $row['puesto'] ?: '—') ?> · <?= esc($row['sede'] ?: '—') ?>/<?= esc($row['dep'] ?: '—') ?> · Estado: <?= esc($row['estado_actual']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>

      <?php if($S): ?>
      <!-- Accordion Detalles -->
      <div id="accSol" class="accord mt-2">
        <div class="acc-head" onclick="toggleAcc()">
          <div><b>Detalles de la solicitud · ID-<?= (int)$S['id'] ?></b></div>
          <div id="accIcon">Mostrar</div>
        </div>
        <div class="acc-body">
          <div class="row g-3">
            <div class="col-md-4"><small class="text-muted">Título / Puesto</small><div class="fw-bold"><?= esc($S['titulo'] ?: $S['puesto'] ?: '—') ?></div></div>
            <div class="col-md-2"><small class="text-muted">Estado actual</small><div class="fw-bold"><?= esc($S['estado_actual']) ?></div></div>
            <div class="col-md-3"><small class="text-muted">Departamento</small><div class="fw-bold"><?= esc($S['dep'] ?: '—') ?></div></div>
            <div class="col-md-3"><small class="text-muted">Sede</small><div class="fw-bold"><?= esc($S['sede'] ?: '—') ?></div></div>
            <div class="col-md-2"><small class="text-muted">Exp. requerida</small><div class="fw-bold"><?= (int)($S['experiencia_anios'] ?? 0) ?> AÑOS</div></div>
            <div class="col-md-2"><small class="text-muted">Escolaridad mínima</small><div class="fw-bold"><?= (int)($S['escolaridad_min'] ?? 0) ?></div></div>
            <div class="col-md-4"><small class="text-muted">Inglés requerido</small><div class="fw-bold"><?= esc($S['ingles_combo'] ?? 'NO') ?></div></div>
            <div class="col-md-4"><small class="text-muted">Autor</small><div class="fw-bold"><?= esc($S['autor'] ?: '—') ?></div></div>
            <div class="col-md-6"><small class="text-muted">Carrera(s) requerida(s)</small><div class="fw-bold"><?= esc($S['carrera_estudiada'] ?: '—') ?></div></div>
            <div class="col-md-6"><small class="text-muted">Áreas requeridas</small><div class="fw-bold"><?= esc($S['area_experiencia'] ?: '—') ?></div></div>
            <div class="col-12"><small class="text-muted">Competencias requeridas</small><div class="fw-bold"><?= esc($S['competencias_json'] ?: '—') ?></div></div>
          </div>
        </div>
      </div>

      <!-- Acciones -->
      <div class="actionbar">
        <div class="searcher">
          <input type="text" id="q" class="form-control" placeholder="Buscar por nombre, correo, fuente…" oninput="filterRows()">
        </div>
        <form method="post" class="d-inline">
          <input type="hidden" name="act" value="ranking_demo">
          <button class="btn btn-outline-primary">Asignar ranking IA</button>
        </form>
        <button class="btn btn-success" onclick="sortBy('compat')">Ordenar por compatibilidad</button>
        <button class="btn btn-outline-primary" onclick="sortBy('ia')">Ordenar por IA</button>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if($S): ?>
  <!-- Tabla -->
  <div class="table-wrap mt-3">
    <table class="datagrid" id="grid">
      <thead>
        <tr>
          <th class="sortable" data-key="id">ID <span class="hint">↕</span></th>
          <th>#</th>
          <th>Nombre completo</th>
          <th>Contacto</th>
          <th>Perfil</th>
          <th class="sortable" data-key="compat">Compatibilidad (%) <span class="hint">↕</span></th>
          <th class="sortable" data-key="ia">Puntaje IA <span class="hint">↕</span></th>
        </tr>
      </thead>
      <tbody>
        <?php $n=1; foreach($cands as $c):
          $pct=(int)$c['_compat_pct'];
          $ia =(int)($c['Puntaje de evaluación'] ?? 0);
          $rank = (int)($c['PosiciónRanking'] ?? 0);
          $cls = $pct>=80?'text-success':($pct>=60?'text-warning':'text-danger');
        ?>
        <tr data-id="<?= (int)$c['id'] ?>" onclick="openDrawer(<?= (int)$c['id'] ?>)">
          <td data-val="<?= (int)$c['id'] ?>"><b><?= (int)$c['id'] ?></b></td>
          <td><?= $n++ ?></td>
          <td>
            <div class="fw-bold"><?= esc($c['Nombre completo']) ?></div>
            <div class="small"><?= esc($c['RecruitmentStrategy']) ?></div>
          </td>
          <td class="small">
            <?= esc($c['Correo']) ?> · <?= esc($c['Telefono'] ?: '—') ?>
          </td>
          <td class="small">
            <div class="kv">
              <span class="tag"><?= esc($c['Carrera'] ?: '—') ?></span>
              <span class="tag"><?= esc($c['EducationLevel'] ?: '—') ?></span>
              <span class="tag"><?= (int)$c['ExperienceYears'] ?> AÑOS</span>
            </div>
          </td>
          <td data-val="<?= $pct ?>">
            <div class="d-flex align-items-center gap-2">
              <span class="fw-bold <?= $cls ?>"><?= $pct ?>%</span>
              <div class="meter" style="width:160px"><i style="width:<?= $pct ?>%"></i></div>
            </div>
            <div class="small">Inglés req: <?= esc($S['ingles_combo'] ?? 'NO') ?> · Post: <?= esc($c['Inglés_postulante'] ?: 'NO') ?></div>
          </td>
          <td data-val="<?= $ia ?>">
            <span class="medal <?= $rank>0 && $rank<=3 ? 'top':'' ?>">
              IA <?= $ia ?>
              <?php if($rank>0): ?>
                <span title="Ranking IA">🏅 #<?= $rank ?></span>
              <?php endif; ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(!$cands): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">Aún no hay candidatos registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- ===== DRAWER Detalles del candidato ===== -->
<div class="drawer" id="drawer">
  <div class="drawer-head">
    <div class="drawer-title">
      <div>
        <div id="dw-title" class="fw-bold">Candidato</div>
        <div id="dw-sub" class="drawer-sub">—</div>
      </div>
    </div>
  </div>
  <div class="drawer-body" id="dw-body"></div>
</div>

<!-- ===== Modal Registrar candidato ===== -->
<?php if (in_array($ROL,['admin','rh']) && $S && in_array($S['estado_actual'], ['APROBADA','BUSCANDO','EN_ENTREVISTA','EN_DECISION','ABIERTA'], true)): ?>
<div class="modal fade" id="modalAlta" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Registrar candidato</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <form method="post" class="to-upper">
        <input type="hidden" name="act" value="alta">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-8"><label class="form-label">Nombre completo *</label><input name="Nombre_completo" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Correo *</label><input type="email" name="Correo" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Teléfono</label><input name="Telefono" class="form-control"></div>
            <div class="col-md-4">
              <label class="form-label">Fuente de reclutamiento *</label>
              <select name="RecruitmentStrategy" class="form-select">
                <option>RECOMENDADO</option><option>PORTALES DE EMPLEO</option><option>HEADHUNTING</option>
              </select>
            </div>
            <div class="col-md-4"><label class="form-label">Años de experiencia *</label><input type="number" min="0" max="40" name="ExperienceYears" class="form-control" value="0"></div>
            <div class="col-md-6"><label class="form-label">Carrera *</label><input name="Carrera" class="form-control"></div>
            <div class="col-md-6">
              <label class="form-label">Escolaridad *</label>
              <select name="EducationLevel" class="form-select">
                <option>PREPARATORIA</option><option>LICENCIATURA</option><option>MAESTRÍA</option><option>DOCTORADO</option>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label">Áreas de experiencia (separadas por coma)</label><input name="Área_de_experiencia" class="form-control" placeholder="LOGISTICA, CALIDAD, ..."></div>
            <div class="col-md-6"><label class="form-label">Competencias técnicas (separadas por coma)</label><input name="Competencias_técnicas" class="form-control" placeholder="EXCEL, LIDERAZGO, ..."></div>
            <div class="col-md-6"><label class="form-label">Inglés requerido (desde solicitud)</label><input name="Inglés_requerido" class="form-control" value="<?= esc($S['ingles_combo'] ?? 'NO') ?>" readonly></div>
            <div class="col-md-6">
              <label class="form-label">Inglés del postulante</label>
              <select name="Inglés_postulante" class="form-select">
                <option>NO</option><option>BASICO</option><option>INTERMEDIO</option><option>AVANZADO</option><option>NATIVO</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer"><button class="btn btn-success">Guardar candidato</button></div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
// Uppercase & sin acentos (inputs en .to-upper)
(function(){
  const rmAcc = s => s.normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  document.addEventListener('input', e=>{
    const el=e.target;
    if(!el.closest('.to-upper')) return;
    if(el.type==='email' || el.type==='number') return;
    const p1=el.selectionStart,p2=el.selectionEnd;
    el.value = rmAcc(el.value.toUpperCase());
    if(p1!=null&&p2!=null) el.setSelectionRange(p1,p2);
  });
})();

// Accordion persist
function toggleAcc(){
  const acc = document.getElementById('accSol');
  acc.classList.toggle('acc-open');
  localStorage.setItem('accSolOpen', acc.classList.contains('acc-open')?'1':'0');
  document.getElementById('accIcon').textContent = acc.classList.contains('acc-open') ? 'Ocultar' : 'Mostrar';
}
(function(){
  const acc = document.getElementById('accSol'); if(!acc) return;
  const state = localStorage.getItem('accSolOpen');
  if(state==='1'){ acc.classList.add('acc-open'); document.getElementById('accIcon').textContent='Ocultar'; }
})();

// Filtro
function filterRows(){
  const q = (document.getElementById('q').value || '').toLowerCase();
  document.querySelectorAll('#grid tbody tr').forEach(tr=>{
    tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

// Sorting
let sortState = {key:null, dir:1};
function sortBy(key){
  const ths = document.querySelectorAll('th.sortable');
  ths.forEach(th=>{ th.classList.remove('active'); th.removeAttribute('data-dir'); });
  const th = Array.from(ths).find(t=>t.dataset.key===key) || null;

  const rows = Array.from(document.querySelectorAll('#grid tbody tr')).filter(tr=>tr.style.display!=='none');
  const idx = key==='id'?0:(key==='compat'?5:6);
  const dir = (sortState.key===key) ? -sortState.dir : 1;
  sortState = {key, dir};

  rows.sort((a,b)=>{
    const va = parseInt(a.children[idx].getAttribute('data-val') || '0', 10);
    const vb = parseInt(b.children[idx].getAttribute('data-val') || '0', 10);
    return dir*(vb-va);
  });
  const tb = document.querySelector('#grid tbody');
  rows.forEach(r=>tb.appendChild(r));
  if(th){ th.classList.add('active'); th.setAttribute('data-dir', dir>0?'↑':'↓'); }
}

// ===== Drawer details =====
const data = <?php
  $out=[];
  foreach($cands as $c){
    $out[$c['id']] = [
      'id'=>(int)$c['id'],
      'Nombre'=> $c['Nombre completo'],
      'Correo'=> $c['Correo'],
      'Telefono'=> $c['Telefono'],
      'RecruitmentStrategy'=> $c['RecruitmentStrategy'],
      'Carrera'=> $c['Carrera'],
      'EducationLevel'=> $c['EducationLevel'],
      'ExperienceYears'=> (int)$c['ExperienceYears'],
      'Areas'=> $c['Área de experiencia'],
      'Comps'=> $c['Competencias técnicas'],
      'InglesPost'=> $c['Inglés_postulante'],
      '_compat_pct'=> (int)$c['_compat_pct'],
      'Eval'=> (int)($c['Puntaje de evaluación'] ?? 0),
      'Viabilidad'=> ($c['Viabilidad'] ?? '—'),
      'PosRank'=> (int)($c['PosiciónRanking'] ?? 0),
    ];
  }
  echo json_encode($out, JSON_UNESCAPED_UNICODE);
?>;

function openDrawer(id){
  const d = data[id]; if(!d) return;
  document.getElementById('dw-title').textContent = `${d.Nombre} · ID-${d.id}`;
  document.getElementById('dw-sub').textContent = `${d.Correo} · Tel: ${d.Telefono||'—'} · Fuente: ${d.RecruitmentStrategy||'—'}`;
  const body = document.getElementById('dw-body');
  body.innerHTML = `
    <div class="dw-section">
      <div class="d-flex align-items-center justify-content-between">
        <h6 class="m-0">Resumen</h6>
        <button class="btn btn-light btn-sm" onclick="closeDrawer()">← Regresar</button>
      </div>
      <div class="kv mt-2">
        <span class="chip">${d.Carrera||'—'}</span>
        <span class="chip">${d.EducationLevel||'—'}</span>
        <span class="chip">${d.ExperienceYears} AÑOS</span>
        <span class="chip">Compatibilidad: ${d._compat_pct}%</span>
        <span class="chip">Puntaje IA: ${d.Eval}</span>
        <span class="chip">Viabilidad: ${d.Viabilidad}</span>
        ${d.PosRank?`<span class="chip">🏅 Ranking IA #${d.PosRank}</span>`:''}
      </div>
    </div>
    <div class="dw-section">
      <h6>Detalles</h6>
      <div class="mb-2"><b>Áreas de experiencia:</b> ${d.Areas||'—'}</div>
      <div class="mb-2"><b>Competencias técnicas:</b> ${d.Comps||'—'}</div>
      <div class="mb-2"><b>Inglés:</b> Postulante ${d.InglesPost||'NO'} · Requerido <?= esc($S['ingles_combo'] ?? 'NO') ?></div>
    </div>
    <div class="dw-section">
      <h6>Acciones</h6>
      <div class="dw-actions">
        <?php if (in_array($ROL,['admin','rh'])): ?>
        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editModal" onclick="prefillEdit(${id})">Editar</button>
        <form method="post">
          <input type="hidden" name="act" value="a_entrevista"><input type="hidden" name="id" value="${id}">
          <?php if ($estaEnEntrevista): ?>
            <button class="btn btn-outline-primary" disabled title="La solicitud ya está en entrevista">En entrevista</button>
          <?php else: ?>
            <button class="btn btn-outline-primary">Enviar a entrevista</button>
          <?php endif; ?>
        </form>
        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#scoreModal" onclick="prefillScore(${id})">Puntajes de entrevista</button>
        <form method="post" onsubmit="return confirm('¿Confirmas que deseas CONTRATAR a este candidato y CERRAR la solicitud?');">
          <input type="hidden" name="act" value="contratar"><input type="hidden" name="id" value="${id}">
          <button class="btn btn-dark">Contratar</button>
        </form>
        <form method="post" onsubmit="return confirm('¿Eliminar este candidato? Esta acción no se puede deshacer.');">
          <input type="hidden" name="act" value="eliminar"><input type="hidden" name="id" value="${id}">
          <button class="btn btn-outline-danger">Eliminar</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  `;
  document.getElementById('drawer').classList.add('open');
}
function closeDrawer(){ document.getElementById('drawer').classList.remove('open'); }
document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeDrawer(); });

// ===== Helpers de normalización en JS (para selects del modal de edición) =====
function jsSinAcentos(s){
  return (s||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'');
}
function jsNormVal(s){
  return jsSinAcentos(String(s||'')).toUpperCase().replace(/\s+/g,' ').trim();
}

// Prefill modal EDITAR — usando comparación normalizada para preseleccionar opciones
function prefillEdit(id){
  const c = <?php
    $by = [];
    foreach($cands as $c){
      $by[$c['id']] = $c;
    }
    echo json_encode($by, JSON_UNESCAPED_UNICODE);
  ?>[id];
  if(!c) return;

  const optsRecruit = ['RECOMENDADO','PORTALES DE EMPLEO','HEADHUNTING'];
  const optsEdu = ['PREPARATORIA','LICENCIATURA','MAESTRÍA','DOCTORADO'];
  const optsIng = ['NO','BASICO','INTERMEDIO','AVANZADO','NATIVO'];

  const rSel = (opt)=> jsNormVal(c['RecruitmentStrategy'])===jsNormVal(opt) ? 'selected' : '';
  const eSel = (opt)=> jsNormVal(c['EducationLevel'])===jsNormVal(opt) ? 'selected' : '';
  const iSel = (opt)=> jsNormVal(c['Inglés_postulante'])===jsNormVal(opt) ? 'selected' : '';

  const html = `
  <div class="modal fade" id="edit${id}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Editar candidato · ID-${id}</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <form method="post" class="to-upper">
          <input type="hidden" name="act" value="editar"><input type="hidden" name="id" value="${id}">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-8"><label class="form-label">Nombre completo</label><input name="Nombre_completo" class="form-control" value="${c['Nombre completo']??''}"></div>
              <div class="col-md-4"><label class="form-label">Teléfono</label><input name="Telefono" class="form-control" value="${c['Telefono']??''}"></div>
              <div class="col-md-6">
                <label class="form-label">Fuente de reclutamiento</label>
                <select name="RecruitmentStrategy" class="form-select">
                  ${optsRecruit.map(opt=>`<option value="${opt}" ${rSel(opt)}>${opt}</option>`).join('')}
                </select>
              </div>
              <div class="col-md-6"><label class="form-label">Años de experiencia</label><input type="number" min="0" max="40" name="ExperienceYears" class="form-control" value="${parseInt(c['ExperienceYears']||0)}"></div>
              <div class="col-md-6"><label class="form-label">Carrera</label><input name="Carrera" class="form-control" value="${c['Carrera']??''}"></div>
              <div class="col-md-6">
                <label class="form-label">Escolaridad</label>
                <select name="EducationLevel" class="form-select">
                  ${optsEdu.map(opt=>`<option value="${opt}" ${eSel(opt)}>${opt}</option>`).join('')}
                </select>
              </div>
              <div class="col-md-12"><label class="form-label">Áreas de experiencia (coma)</label><input name="Área_de_experiencia" class="form-control" value="${c['Área de experiencia']??''}"></div>
              <div class="col-md-12"><label class="form-label">Competencias técnicas (coma)</label><input name="Competencias_técnicas" class="form-control" value="${c['Competencias técnicas']??''}"></div>
              <div class="col-md-6">
                <label class="form-label">Inglés del postulante</label>
                <select name="Inglés_postulante" class="form-select">
                  ${optsIng.map(opt=>`<option value="${opt}" ${iSel(opt)}>${opt}</option>`).join('')}
                </select>
              </div>
              <div class="col-md-6"><div class="form-text">Inglés requerido por la solicitud: <b><?= esc($S['ingles_combo'] ?? 'NO') ?></b></div></div>
            </div>
          </div>
          <div class="modal-footer"><button class="btn btn-primary">Guardar cambios</button></div>
        </form>
      </div>
    </div>
  </div>`;
  const tmp = document.createElement('div'); tmp.innerHTML = html; document.body.appendChild(tmp);
  const m = new bootstrap.Modal(tmp.querySelector('.modal')); m.show();
  tmp.addEventListener('hidden.bs.modal', ()=> tmp.remove());
}

// Prefill modal PUNTAJES (IA solo se recalcula aquí al guardar, no al editar)
function prefillScore(id){
  const c = data[id]; if(!c) return;
  const html = `
  <div class="modal fade" id="scores${id}" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Puntajes de entrevista · ${c.Nombre}</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <form method="post">
          <input type="hidden" name="act" value="guardar_puntajes"><input type="hidden" name="id" value="${id}">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-4"><label class="form-label">Puntaje de personalidad</label><input type="number" min="0" max="100" name="PersonalityScore" class="form-control" value="${parseInt((<?php echo json_encode(array_column($cands,'PersonalityScore','id')); ?>)[id]||0)}"></div>
              <div class="col-4"><label class="form-label">Puntaje técnico</label><input type="number" min="0" max="100" name="SkillScore" class="form-control" value="${parseInt((<?php echo json_encode(array_column($cands,'SkillScore','id')); ?>)[id]||0)}"></div>
              <div class="col-4"><label class="form-label">Puntaje de la entrevista</label><input type="number" min="0" max="100" name="InterviewScore" class="form-control" value="${parseInt((<?php echo json_encode(array_column($cands,'InterviewScore','id')); ?>)[id]||0)}"></div>
            </div>
            <div class="form-text mt-2">Al Guardar se le asignara un pontaje conforme la evaluacion del algoritmo para su viavilidad.</div>
          </div>
          <div class="modal-footer"><button class="btn btn-success">Guardar puntajes de entrevista</button></div>
        </form>
      </div>
    </div>
  </div>`;
  const tmp = document.createElement('div'); tmp.innerHTML = html; document.body.appendChild(tmp);
  const m = new bootstrap.Modal(tmp.querySelector('.modal')); m.show();
  tmp.addEventListener('hidden.bs.modal', ()=> tmp.remove());
}
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/footer.php'; ?>
