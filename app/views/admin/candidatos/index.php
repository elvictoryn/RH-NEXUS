<?php
// ============================================================
// Candidatos (RH/Admin) — Vista en barras, ranking compatibilidad
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

/* ---- Param solicitud ---- */
$sol = (int)($_GET['sol'] ?? 0);
$alert=null; $atype='success';

/* ---- Solicitudes visibles para selector ---- */
$solicitudes=[];
try{
  $st = $db->prepare("
    SELECT s.id, s.titulo, s.puesto, s.estado_actual, s.ingles_combo,
           se.nombre sede, d.nombre dep, u.nombre_completo autor, s.sede_id, s.autor_id
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
      else {
        $estaEnEntrevista = in_array($S['estado_actual'], ['EN_ENTREVISTA','EN_DECISION','CERRADA'], true);
      }
    }
  }catch(Throwable $e){
    $alert='Error al cargar solicitud: '.$e->getMessage(); $atype='danger'; $sol=0;
  }
}

/* ========= ALGORITMO COMPATIBILIDAD (sistema,) Esa función calcula un puntaje de compatibilidad (0–100) con base en criterios y pesos:

Carrera (25%)
Si la carrera del candidato coincide con alguna carrera requerida → +25 pts.

Escolaridad (10%)
Si cumple o supera la escolaridad mínima → +10 pts.

Experiencia (20%)
Si tiene igual o más años de experiencia que lo requerido → +20 pts.
Si le falta 1 año → +12 pts.
Menos de eso → 0 pts.

Áreas de experiencia (20%)
Cuenta cuántas áreas solicitadas están también en las áreas del candidato.
Si todas coinciden → +20 pts.
Si solo algunas, se da un porcentaje.

Competencias técnicas (15%)
Igual que áreas: se calcula cuántas competencias solicitadas coinciden con las del candidato.

Inglés (10%)
Si no se requiere inglés → +10 pts automático.
Si se requiere:

Nivel INTERMEDIO o más → +10 pts.

Nivel BÁSICO → +5 pts.

🔹 ¿Qué se guarda en la base?

Al final, calcular_compat() devuelve:

compat_porcentaje → número de 0 a 100.

semaforo → texto (verde, amarillo, rojo) según el porcentaje:

Verde: ≥80

Amarillo: 60–79

Rojo: <60

Estos dos campos se actualizan en la tabla postulantes_por_vacante.========= */
function escolar_to_ord($t){
  $t=mayus($t);
  if (str_contains($t,'DOCTOR')) return 4;
  if (str_contains($t,'MAESTR')) return 3;
  if (str_contains($t,'LICEN')) return 2;
  if (str_contains($t,'PREPA')||str_contains($t,'BACH')) return 1;
  return 0;
}
function compat_calc(array $c, array $S): array {
  $score=0; $det=[];
  // Escolaridad (10)
  $minReq = (int)($S['escolaridad_min'] ?? 0);
  $cand   = escolar_to_ord($c['EducationLevel'] ?? '');
  if ($minReq>0){
    if ($cand >= $minReq){ $score+=10; $det[]='Escolaridad: cumple'; } else { $det[]='Escolaridad: no cumple'; }
  }
  // Experiencia (20)
  $expReq = (int)($S['experiencia_anios'] ?? 0);
  $expC   = (int)($c['ExperienceYears'] ?? 0);
  if ($expC >= $expReq){ $score+=20; $det[]='Experiencia: cumple'; } else { $det[]='Experiencia: no cumple'; }
  // Carrera (25)
  $carReq = mayus((string)($S['carrera_estudiada'] ?? ''));
  $carC   = mayus((string)($c['Carrera'] ?? ''));
  if ($carReq!=='' && $carC!=='' && str_contains($carC, $carReq)){ $score+=25; $det[]='Carrera: afín'; }
  else { $det[]='Carrera: no afín'; }
  // Áreas (20)
  $areasReq = list_norm($S['area_experiencia'] ?? '');
  $areasC   = list_norm($c['Área de experiencia'] ?? '');
  $inter = array_intersect($areasReq, $areasC);
  if (count($inter)>=1){ $score+=20; $det[]='Áreas: coinciden'; } else { $det[]='Áreas: sin coincidencia'; }
  // Competencias (15)
  $compReq = json_decode((string)($S['competencias_json'] ?? '[]'), true);
  $compReq = is_array($compReq) ? array_map('mayus',$compReq) : [];
  $compC   = list_norm($c['Competencias técnicas'] ?? '');
  $match   = 0;
  if ($compReq){
    foreach($compReq as $req){ if (in_array(mayus($req), $compC, true)) $match++; }
    $ratio = (count($compReq)>0) ? ($match / count($compReq)) : 0;
    $score += (int)round(15 * $ratio);
  }
  // Inglés (10)
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
if ($_SERVER['REQUEST_METHOD']==='POST' && in_array($ROL,['admin','rh'],true)) {
  $act = $_POST['act'] ?? '';
  try{
    if ($act==='alta' && $sol>0){
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
      if ($nombre==='' || $correo==='' || !filter_var($correo,FILTER_VALIDATE_EMAIL))
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

    if ($act==='eliminar' && $sol>0){
      $id=(int)($_POST['id'] ?? 0);
      $db->prepare("DELETE FROM `postulantes_por_vacante` WHERE id=? AND solicitud_id=?")->execute([$id,$sol]);
      header('Location: ?sol='.$sol.'&del=1'); exit;
    }

    if ($act==='editar' && $sol>0){
      $id     =(int)($_POST['id'] ?? 0);
      $nombre = mayus($_POST['Nombre_completo'] ?? '');
      $tel    = trim((string)($_POST['Telefono'] ?? ''));
      $fuente = mayus((string)($_POST['RecruitmentStrategy'] ?? ''));
      $carrera= mayus($_POST['Carrera'] ?? '');
      $esc    = mayus((string)($_POST['EducationLevel'] ?? ''));
      $exp    = (int)($_POST['ExperienceYears'] ?? 0);
      $areas  = mayus($_POST['Área_de_experiencia'] ?? '');
      $comps  = mayus($_POST['Competencias_técnicas'] ?? '');
      $ingPos = mayus((string)($_POST['Inglés_postulante'] ?? 'NO'));
      $up=$db->prepare("
        UPDATE `postulantes_por_vacante`
           SET `Nombre completo`=?,`Telefono`=?,`RecruitmentStrategy`=?,`Carrera`=?,
               `EducationLevel`=?,`ExperienceYears`=?,`Área de experiencia`=?,`Competencias técnicas`=?,
               `Inglés_postulante`=?,`updated_at`=NOW()
         WHERE id=? AND solicitud_id=?
      ");
      $up->execute([$nombre,$tel,$fuente,$carrera,$esc,$exp,$areas,$comps,$ingPos,$id,$sol]);
      header('Location: ?sol='.$sol.'&upd=1'); exit;
    }

    if ($act==='a_entrevista' && $sol>0){
      $id=(int)($_POST['id'] ?? 0);
      // No guardamos etapa; solo movemos el estado de la solicitud si corresponde
      $cur=$db->prepare("SELECT estado_actual FROM solicitudes WHERE id=?"); $cur->execute([$sol]);
      $est=(string)$cur->fetchColumn();
      if (!in_array($est,['EN_ENTREVISTA','EN_DECISION','CERRADA'],true)){
        $db->prepare("UPDATE solicitudes SET estado_actual='EN_ENTREVISTA' WHERE id=?")->execute([$sol]);
      }
      header('Location: ?sol='.$sol.'&toint=1'); exit;
    }

    if ($act==='guardar_puntajes' && $sol>0){
      $id=(int)($_POST['id'] ?? 0);
      $ps=max(0,min(100,(int)($_POST['PersonalityScore'] ?? 0)));
      $ss=max(0,min(100,(int)($_POST['SkillScore'] ?? 0)));
      $is=max(0,min(100,(int)($_POST['InterviewScore'] ?? 0)));
      $eval=round(($ps+$ss+$is)/3);
      $via =($eval>=80?'ALTA':($eval>=60?'MEDIA':'BAJA'));
      $db->prepare("
        UPDATE `postulantes_por_vacante`
           SET `PersonalityScore`=?,`SkillScore`=?,`InterviewScore`=?,
               `Puntaje de evaluación`=?,`Viabilidad`=?,`updated_at`=NOW()
         WHERE id=? AND solicitud_id=?
      ")->execute([$ps,$ss,$is,$eval,$via,$id,$sol]);
      header('Location: ?sol='.$sol.'&scores=1'); exit;
    }

    if ($act==='ranking_demo' && $sol>0){
      $rs = $db->prepare("SELECT id FROM `postulantes_por_vacante` WHERE solicitud_id=? ORDER BY `Puntaje de evaluación` DESC, id ASC");
      $rs->execute([$sol]); $r=1;
      while($row=$rs->fetch(PDO::FETCH_ASSOC)){
        $db->prepare("UPDATE `postulantes_por_vacante` SET `PosiciónRanking`=? WHERE id=?")->execute([$r,(int)$row['id']]); $r++;
      }
      header('Location: ?sol='.$sol.'&rank=1'); exit;
    }

    if ($act==='contratar' && $sol>0){
      $id=(int)($_POST['id'] ?? 0);
      $db->prepare("UPDATE `postulantes_por_vacante` SET `Decisión_Final`='CONTRATADO', `updated_at`=NOW() WHERE id=? AND solicitud_id=?")->execute([$id,$sol]);
      $db->prepare("UPDATE solicitudes SET estado_actual='CERRADA' WHERE id=?")->execute([$sol]);
      header('Location: ?sol='.$sol.'&done=1'); exit;
    }

  }catch(Throwable $e){ $alert='Acción no completada: '.$e->getMessage(); $atype='danger'; }
}

/* ---- Cargar candidatos + calcular compatibilidad y ordenar ---- */
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
:root{
  --ink:#0c1f3a; --muted:#5a6a85; --line:#dbe4f0; --soft:#f7faff;
  --brand:#2563eb; --ok:#16a34a; --warn:#eab308; --bad:#dc2626;
  --navh: var(--nav-h, 64px);
}
body{background:linear-gradient(180deg,#f2f6ff,#ffffff); padding-top: calc(var(--navh) + 6px);}

.container-xxl{max-width:1500px!important}
.wrap{padding:16px}
.mt-nav{margin-top: calc(var(--navh) + 6px)!important}

.toastish{display:flex;align-items:center;gap:.6rem;border-radius:12px;padding:.55rem .8rem;border:1px solid;font-weight:700}
.toastish.ok{background:#ecfdf5;border-color:#bbf7d0;color:#065f46}
.toastish.warn{background:#fffbeb;border-color:#fde68a;color:#92400e}
.toastish.bad{background:#fef2f2;border-color:#fecaca;color:#991b1b}

.card{border:1px solid var(--line);border-radius:16px;background:#fff;box-shadow:0 12px 28px rgba(15,30,60,.06)}
.card-bleed{padding:16px}
.section-title{font-weight:900;color:var(--ink)}

.form-lg .form-control,.form-lg .form-select{height:46px;font-size:1rem}
.to-upper input[type="text"], .to-upper input:not([type="email"]):not([type="number"]), .to-upper textarea{letter-spacing:.3px}

/* ===== Barra por candidato ===== */
.rowbar{display:grid;grid-template-columns: 1.3fr 1.5fr .9fr .9fr auto;gap:14px;align-items:center;padding:16px;border-bottom:1px solid var(--line)}
.rowbar:nth-child(odd){background:var(--soft)}
.name{font-weight:900;color:var(--ink);font-size:1.12rem}
.sub{color:var(--muted);font-size:.95rem}
.badge2{border:1px solid #e6efff;background:#fff;border-radius:999px;padding:.18rem .6rem;font-weight:800}
.comp{display:inline-flex;align-items:center;gap:.35rem;border-radius:999px;padding:.32rem .7rem;font-weight:900;border:1px solid #dbeafe;background:#eff6ff;color:#1d4ed8}
.comp.mid{background:#fffbeb;border-color:#fde68a;color:#92400e}
.comp.low{background:#fef2f2;border-color:#fecaca;color:#991b1b}
.kv{display:flex;gap:.4rem;flex-wrap:wrap}
.kv span{border:1px dashed #dbe4f0;background:#fff;border-radius:999px;padding:.16rem .55rem;font-weight:700}

.actions{display:grid;grid-template-columns:repeat(2,minmax(140px,1fr));gap:8px;justify-content:end}
.actions .btn{width:100%}

/* Modal offset para no tapar navbar */
.modal-dialog{margin-top: calc(var(--navh) + 10px);}
.modal-header{background:#0f1b2e;color:#fff}
.modal-title{font-weight:900}
</style>

<div class="container-xxl wrap">
  <?php if ($alert): ?><div class="toastish <?= $atype==='danger'?'bad':($atype==='warning'?'warn':'ok') ?> mb-2"><?= esc($alert) ?></div><?php endif; ?>
  <?php foreach(['ok'=>'Candidato registrado.','del'=>'Candidato eliminado.','upd'=>'Candidato actualizado.',
                 'toint'=>'Candidato enviado a entrevista.','scores'=>'Puntajes de entrevista guardados.',
                 'rank'=>'Ranking (demo) asignado.','done'=>'Contratación registrada y solicitud cerrada.'] as $k=>$msg): ?>
    <?php if (isset($_GET[$k])): ?><div class="toastish ok mb-2"><?= esc($msg) ?></div><?php endif; ?>
  <?php endforeach; ?>

  <!-- 1) Candidatos por solicitud -->
  <div class="card card-bleed">
    <div class="section-title mb-2">Candidatos por solicitud</div>
    <form method="get" class="row g-2">
      <div class="col-lg-10">
        <select name="sol" class="form-select form-lg" required onchange="this.form.submit()">
          <option value="">— Selecciona una solicitud —</option>
          <?php foreach($solicitudes as $row): $sel=($sol>0 && $sol==(int)$row['id'])?'selected':''; ?>
            <option value="<?= (int)$row['id'] ?>" <?= $sel ?>>
              ID-<?= (int)$row['id'] ?> · <?= esc($row['titulo'] ?: $row['puesto'] ?: '—') ?> · <?= esc($row['sede'] ?: '—') ?>/<?= esc($row['dep'] ?: '—') ?> · Estado: <?= esc($row['estado_actual']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-2 d-grid"><button class="btn btn-primary">Abrir</button></div>
    </form>
  </div>

  <?php if($S): ?>
  <!-- 2) Registrar candidato -->
  <?php if (in_array($ROL,['admin','rh']) && in_array($S['estado_actual'], ['APROBADA','BUSCANDO','EN_ENTREVISTA','EN_DECISION','ABIERTA'], true)): ?>
  <div class="card card-bleed mt-3">
    <h5 class="section-title mb-2">Registrar candidato</h5>
    <form method="post" class="row g-3 form-lg to-upper">
      <input type="hidden" name="act" value="alta">
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
      <div class="col-12 d-flex justify-content-end"><button class="btn btn-success px-4">Guardar candidato</button></div>
    </form>
  </div>
  <?php endif; ?>

  <!-- 3) Candidatos — ordenados por compatibilidad -->
  <div class="card card-bleed mt-3">
    <div class="d-flex align-items-center justify-content-between">
      <h5 class="section-title mb-0">Candidatos — ordenados por compatibilidad</h5>
      <?php if (in_array($ROL,['admin','rh'])): ?>
        <form method="post" class="d-inline">
          <input type="hidden" name="act" value="ranking_demo">
          <button class="btn btn-outline-primary btn-sm">Calcular evaluación IA</button>
        </form>
      <?php endif; ?>
    </div>

    <?php if(!$cands): ?>
      <div class="text-muted text-center py-4">Aún no hay candidatos registrados.</div>
    <?php else: foreach($cands as $c):
      $pct=(int)$c['_compat_pct']; $cls=$pct>=80?'':($pct>=60?'mid':'low');
      $eval=(int)($c['Puntaje de evaluación'] ?? 0);
    ?>
      <div class="rowbar">
        <div>
          <div class="name"><?= esc($c['Nombre completo']) ?></div>
          <div class="sub"><?= esc($c['Correo']) ?> · Teléfono: <?= esc($c['Telefono'] ?: '—') ?> · Fuente: <?= esc($c['RecruitmentStrategy']) ?></div>
        </div>
        <div>
          <div class="kv">
            <span><?= esc($c['Carrera']) ?></span>
            <span><?= esc($c['EducationLevel']) ?></span>
            <span><?= (int)$c['ExperienceYears'] ?> AÑOS</span>
            <span class="badge2">Áreas: <?= esc($c['Área de experiencia'] ?: '—') ?></span>
            <span class="badge2">Competencias: <?= esc($c['Competencias técnicas'] ?: '—') ?></span>
          </div>
        </div>
        <div>
          <div class="kv">
            <span>INGLÉS REQUERIDO: <?= esc($S['ingles_combo'] ?? 'NO') ?></span>
            <span>INGLÉS DEL POSTULANTE: <?= esc($c['Inglés_postulante'] ?: 'NO') ?></span>
          </div>
        </div>
        <div>
          <div class="kv">
            <span class="comp <?= $cls ?>">Compatibilidad <?= $pct ?>%</span>
            <span class="badge2">Puntaje de evaluación: <?= $eval ?></span>
            <span class="badge2">Viabilidad: <?= esc($c['Viabilidad'] ?: '—') ?></span>
            <span class="badge2">Posición Ranking #<?= (int)($c['PosiciónRanking'] ?? 0) ?></span>
          </div>
        </div>
        <div class="actions">
          <?php if (in_array($ROL,['admin','rh'])): ?>
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#edit<?= (int)$c['id'] ?>">Editar</button>
            <form method="post" <?= $estaEnEntrevista ? '' : '' ?>>
              <input type="hidden" name="act" value="a_entrevista"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <?php if ($estaEnEntrevista): ?>
                <button class="btn btn-outline-primary btn-sm" disabled title="La solicitud ya está en entrevista">En entrevista</button>
              <?php else: ?>
                <button class="btn btn-outline-primary btn-sm">Enviar a entrevista</button>
              <?php endif; ?>
            </form>
            <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#scores<?= (int)$c['id'] ?>">Puntajes de entrevista</button>
            <form method="post" onsubmit="return confirm('¿Marcar como contratado y cerrar la solicitud?');">
              <input type="hidden" name="act" value="contratar"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <button class="btn btn-dark btn-sm">Contratar</button>
            </form>
            <form method="post" onsubmit="return confirm('¿Eliminar este candidato? Esta acción no se puede deshacer.');">
              <input type="hidden" name="act" value="eliminar"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <button class="btn btn-outline-danger btn-sm">Eliminar</button>
            </form>
          <?php else: ?><em class="text-muted">Solo lectura</em><?php endif; ?>
        </div>
      </div>

      <!-- Modal Editar -->
      <div class="modal fade" id="edit<?= (int)$c['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Editar candidato · ID-<?= (int)$c['id'] ?></h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <form method="post" class="to-upper">
              <input type="hidden" name="act" value="editar"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <div class="modal-body">
                <div class="row g-3">
                  <div class="col-md-8"><label class="form-label">Nombre completo</label><input name="Nombre_completo" class="form-control" value="<?= esc($c['Nombre completo']) ?>"></div>
                  <div class="col-md-4"><label class="form-label">Teléfono</label><input name="Telefono" class="form-control" value="<?= esc($c['Telefono']) ?>"></div>
                  <div class="col-md-6">
                    <label class="form-label">Fuente de reclutamiento</label>
                    <select name="RecruitmentStrategy" class="form-select">
                      <?php foreach(['RECOMENDADO','PORTALES DE EMPLEO','HEADHUNTING'] as $opt): ?>
                        <option <?= ($c['RecruitmentStrategy']===$opt?'selected':'') ?>><?= $opt ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-6"><label class="form-label">Años de experiencia</label><input type="number" min="0" max="40" name="ExperienceYears" class="form-control" value="<?= (int)$c['ExperienceYears'] ?>"></div>
                  <div class="col-md-6"><label class="form-label">Carrera</label><input name="Carrera" class="form-control" value="<?= esc($c['Carrera']) ?>"></div>
                  <div class="col-md-6">
                    <label class="form-label">Escolaridad</label>
                    <select name="EducationLevel" class="form-select">
                      <?php foreach(['PREPARATORIA','LICENCIATURA','MAESTRÍA','DOCTORADO'] as $opt): ?>
                        <option <?= ($c['EducationLevel']===$opt?'selected':'') ?>><?= $opt ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-12"><label class="form-label">Áreas de experiencia (coma)</label><input name="Área_de_experiencia" class="form-control" value="<?= esc($c['Área de experiencia']) ?>"></div>
                  <div class="col-md-12"><label class="form-label">Competencias técnicas (coma)</label><input name="Competencias_técnicas" class="form-control" value="<?= esc($c['Competencias técnicas']) ?>"></div>
                  <div class="col-md-6">
                    <label class="form-label">Inglés del postulante</label>
                    <select name="Inglés_postulante" class="form-select">
                      <?php foreach(['NO','BASICO','INTERMEDIO','AVANZADO','NATIVO'] as $opt): ?>
                        <option <?= ($c['Inglés_postulante']===$opt?'selected':'') ?>><?= $opt ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-6"><div class="form-text">Inglés requerido por la solicitud: <b><?= esc($S['ingles_combo'] ?? 'NO') ?></b></div></div>
                </div>
              </div>
              <div class="modal-footer"><button class="btn btn-primary">Guardar cambios</button></div>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal Puntajes de entrevista -->
      <div class="modal fade" id="scores<?= (int)$c['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Puntajes de entrevista · <?= esc($c['Nombre completo']) ?></h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <form method="post">
              <input type="hidden" name="act" value="guardar_puntajes"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
              <div class="modal-body">
                <div class="row g-3">
                  <div class="col-4"><label class="form-label">Puntaje de personalidad</label><input type="number" min="0" max="100" name="PersonalityScore" class="form-control" value="<?= (int)($c['PersonalityScore'] ?? 0) ?>"></div>
                  <div class="col-4"><label class="form-label">Puntaje técnico</label><input type="number" min="0" max="100" name="SkillScore" class="form-control" value="<?= (int)($c['SkillScore'] ?? 0) ?>"></div>
                  <div class="col-4"><label class="form-label">Puntaje de la entrevista</label><input type="number" min="0" max="100" name="InterviewScore" class="form-control" value="<?= (int)($c['InterviewScore'] ?? 0) ?>"></div>
                </div>
                <div class="form-text mt-2">....</div>
              </div>
              <div class="modal-footer"><button class="btn btn-success">Guardar puntajes de entrevista</button></div>
            </form>
          </div>
        </div>
      </div>

    <?php endforeach; endif; ?>
  </div>
  <?php endif; // $S ?>
</div>

<script>
// MAYÚSCULAS + remover acentos en inputs de texto
(function(){
  const rmAcc = s => s.normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  document.querySelectorAll('.to-upper input[type="text"], .to-upper input:not([type="email"]):not([type="number"]), .to-upper textarea').forEach(el=>{
    el.addEventListener('input', ()=>{
      const p1=el.selectionStart,p2=el.selectionEnd;
      el.value = rmAcc(el.value.toUpperCase());
      if(p1!=null&&p2!=null) el.setSelectionRange(p1,p2);
    });
  });
})();
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/footer.php'; ?>
