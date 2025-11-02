<?php
// ============================================================
// Crear Solicitud - Nexus RH (ENVIAR + confirmación/éxito en la misma vista)
// ============================================================
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
if (session_status() === PHP_SESSION_NONE) session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
$db = Conexion::getConexion();

// ===== Seguridad básica: sesión y roles permitidos =====
$uid = (int)($_SESSION['id'] ?? 0);
$rol = strtolower($_SESSION['rol'] ?? '');
if (!$uid || !in_array($rol, ['admin','rh','gerente','jefe_area'], true)) {
  header('Location: '.BASE_PATH.'/public/login.php'); exit;
}

$USER = [
  'id'              => $uid,
  'nombre'          => $_SESSION['nombre_completo'] ?? ($_SESSION['usuario'] ?? 'USUARIO'),
  'sede_id'         => $_SESSION['sede_id'] ?? null,
  'departamento_id' => $_SESSION['departamento_id'] ?? null,
];

// ===== AJAX: departamentos por sede =====
if (isset($_GET['ajax']) && $_GET['ajax']==='deps' && isset($_GET['sede_id'])) {
  header('Content-Type: application/json; charset=utf-8');
  $s = (int)$_GET['sede_id'];
  try {
    $st = $db->prepare("SELECT id, nombre
                        FROM departamentos
                        WHERE sede_id=:s AND (LOWER(estado)='activo' OR estado IS NULL)
                        ORDER BY nombre");
    $st->execute([':s'=>$s]);
    $items = $st->fetchAll(PDO::FETCH_ASSOC);
  } catch (\Throwable $e) {
    $st = $db->prepare("SELECT id, nombre FROM departamentos WHERE sede_id=:s ORDER BY nombre");
    $st->execute([':s'=>$s]);
    $items = $st->fetchAll(PDO::FETCH_ASSOC);
  }
  echo json_encode(['ok'=>true,'items'=>$items], JSON_UNESCAPED_UNICODE);
  exit;
}

// ===== Catálogos =====
$sedes = [];
try { $sedes = $db->query("SELECT id, nombre FROM sedes ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC); } catch (\Throwable $e) {}

$areas_exp = [
 'ADMINISTRATIVA','FINANZAS','RECURSOS HUMANOS','COMPRAS','ALMACEN','LOGISTICA','CADENA DE SUMINISTRO',
 'VENTAS','ATENCION A CLIENTES','MARKETING','COMUNICACION','RELACIONES PUBLICAS',
 'PRODUCCION','OPERACIONES','CALIDAD','MEJORA CONTINUA','MANTENIMIENTO','SEGURIDAD E HIGIENE',
 'SALUD','EDUCACION','LEGAL','INVESTIGACION Y DESARROLLO',
 'TI DESARROLLO','TI SOPORTE','CIBERSEGURIDAD','DATA/ANALITICA',
 'ARQUITECTURA','INGENIERIA CIVIL','INGENIERIA INDUSTRIAL','INGENIERIA MECATRONICA','INGENIERIA ELECTRICA'
];

$competencias_sugeridas = [
 'LIDERAZGO','TRABAJO EN EQUIPO','COMUNICACION EFECTIVA','ORIENTACION A RESULTADOS','RESOLUCION DE PROBLEMAS',
 'ANALISIS','NEGOCIACION','PLANEACION','ORGANIZACION','ADAPTABILIDAD','SERVICIO AL CLIENTE',
 'TOMA DE DECISIONES','MANEJO DE CONFLICTOS','PENSAMIENTO CRITICO','INNOVACION','GESTION DEL TIEMPO'
];

$carreras_cat = [
  'INGENIERIA INDUSTRIAL','INGENIERIA MECANICA','INGENIERIA MECATRONICA','INGENIERIA ELECTRICA',
  'INGENIERIA ELECTRONICA','INGENIERIA SISTEMAS COMPUTACIONALES','INGENIERIA INFORMATICA','INGENIERIA CIVIL',
  'INGENIERIA QUIMICA','INGENIERIA EN LOGISTICA','INGENIERIA AMBIENTAL','INGENIERIA PETROLERA',
  'ADMINISTRACION DE EMPRESAS','CONTADURIA','FINANZAS','NEGOCIOS INTERNACIONALES','COMERCIO INTERNACIONAL','ECONOMIA',
  'MERCADOTECNIA','RECURSOS HUMANOS','RELACIONES INTERNACIONALES',
  'DERECHO','PSICOLOGIA','PEDAGOGIA','COMUNICACION','TRABAJO SOCIAL','CRIMINOLOGIA',
  'MEDICINA','ENFERMERIA','NUTRICION','ODONTOLOGIA','FISIOTERAPIA','QUIMICO FARMACOBIOLOGO',
  'ARQUITECTURA','DISENO GRAFICO','DISENO INDUSTRIAL',
  'MATEMATICAS','FISICA','QUIMICA','BIOLOGIA','BIOTECNOLOGIA',
  'TURISMO','GASTRONOMIA','LOGISTICA','SEGURIDAD E HIGIENE',
  'CIENCIA DE DATOS','INTELIGENCIA ARTIFICIAL','CIENCIAS DE LA COMPUTACION'
];

$titulo_pagina = "Crear Solicitud";
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/header.php';
?>
<style>
:root{ --nav-h:64px; --brand:#0D6EFD; --brand-600:#0b5ed7; --ink:#334155; --ok:#22c55e; --err:#ef4444 }
.page-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;
  padding:.85rem 1rem;border-radius:16px;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);
  backdrop-filter:blur(8px);box-shadow:0 6px 16px rgba(0,0,0,.12)}
.hero{display:flex;align-items:center;gap:.8rem}
.hero .hero-icon{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;background:linear-gradient(135deg,var(--brand),#6ea8fe);color:#fff}
.hero .title{margin:0;line-height:1.1;font-weight:900;letter-spacing:.2px;font-size:clamp(1.6rem,2.2vw + .6rem,2.2rem);color:var(--ink)}
.subtitle{color:#1f2937; opacity:.95; font-weight:600}
.form-card{border-radius:16px;border:1px solid #e5e7eb;background:#fff;box-shadow:0 8px 24px rgba(0,0,0,.08); position:relative}
.section-title{font-weight:800;color:var(--brand);margin:.75rem 0 .5rem;display:flex;align-items:center;gap:.4rem}
.section-title .dot{width:.55rem;height:.55rem;border-radius:999px;background:var(--brand)}
.chips{display:flex;flex-wrap:wrap;gap:.4rem}
.chip{display:inline-flex;align-items:center;gap:.35rem;padding:.2rem .5rem;border-radius:999px;border:1px solid #e5e7eb;background:#f8fafc;font-size:.85rem}
.chip button{background:transparent;border:0;color:#64748b}
.btn-bandeja{border-color:#111827;color:#111827}
.btn-bandeja:hover{background:#111827;color:#fff}
.btn-brand{background:var(--brand);border-color:var(--brand)}
.btn-brand:hover{background:var(--brand-600);border-color:var(--brand-600)}
.btn-outline-brand{border-color:var(--brand);color:var(--brand)}
.btn-outline-brand:hover{background:var(--brand);color:#fff}
.req::after{content:" *"; color:#dc2626; font-weight:700}
.small-help{font-size:.86rem;color:#64748b}

/* ===== Alertas en-forma (centradas) ===== */
.alert-layer{
  position:absolute; inset:0; display:none; align-items:center; justify-content:center;
  background:rgba(8,12,20,.42); backdrop-filter:blur(3px); z-index:5;
}
.alert-box{
  width:min(540px, 92vw); border-radius:16px; padding:18px 16px; text-align:center;
  background:#0c1428; color:#eaf2ff; border:1px solid rgba(255,255,255,.16);
  box-shadow:0 24px 64px rgba(0,0,0,.45);
  word-break:break-word;
}
.alert-title{margin:.1rem 0 .25rem; font-weight:900; font-size:1.3rem}
.alert-msg{margin:.2rem 0 .6rem; color:#cfe0ff}
.alert-actions{display:flex; gap:8px; justify-content:center}
.btn-ghost{background:transparent; border:1px solid #475569; color:#e2e8f0; padding:.55rem .9rem; border-radius:10px; font-weight:700}
.btn-yes{background:var(--brand); border:1px solid var(--brand-600); color:#fff; padding:.55rem .9rem; border-radius:10px; font-weight:800}

/* Éxito animado */
.succ-mark{
  width:82px; height:82px; border-radius:50%; border:4px solid rgba(34,197,94,.35);
  display:grid; place-items:center; margin:6px auto 10px; position:relative; animation:pop .5s ease-out both;
}
.succ-mark::after{
  content:""; width:34px; height:17px; border-left:6px solid var(--ok); border-bottom:6px solid var(--ok);
  transform:rotate(-45deg); position:absolute; animation:draw .45s .2s ease-out both;
}
@keyframes pop{ 0%{transform:scale(.6); opacity:0} 100%{transform:scale(1); opacity:1} }
@keyframes draw{ 0%{opacity:0; transform:rotate(-45deg) scale(.8)} 100%{opacity:1; transform:rotate(-45deg) scale(1)} }

/* Toast simple top-center */
.toast{
  position:absolute; top:12px; left:50%; transform:translateX(-50%);
  display:none; padding:.55rem .9rem; border-radius:10px; font-weight:700; color:#0b1b12; background:#bbf7d0; z-index:6;
}
.toast.err{ background:#fecaca; color:#3f1a1a }

/* Botón con spinner */
.btn-brand[disabled]{ opacity:.7; position:relative }
.btn-brand[disabled]::after{
  content:""; position:absolute; right:-28px; top:50%; transform:translateY(-50%);
  width:18px;height:18px;border-radius:50%; border:2px solid rgba(255,255,255,.55); border-top-color:transparent;
  animation:spin .7s linear infinite;
}
@keyframes spin{to{transform:translateY(-50%) rotate(360deg)}}
</style>

<div class="container py-4" style="max-width:1100px">
  <div class="page-head">
    <div class="hero">
      <div class="hero-icon">📝</div>
      <div>
        <h1 class="title">Nueva Solicitud</h1>
        <div class="subtitle">Captura de vacantes y requerimientos</div>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a href="<?= BASE_PATH; ?>/app/views/admin/solicitudes/menu.php" class="btn btn-bandeja">📬 Bandeja</a>
    </div>
  </div>

  <div class="form-card p-4 mt-3" id="formCard">
    <!-- TOAST -->
    <div id="toast" class="toast"></div>

    <!-- CAPA DE ALERTAS -->
    <div class="alert-layer" id="alertLayer" aria-hidden="true">
      <div class="alert-box" id="alertBox"></div>
    </div>

    <!-- Autor/origen -->
    <div class="row g-3 mb-1">
      <div class="col-md-5">
        <label class="form-label">Autor</label>
        <input class="form-control" value="<?= htmlspecialchars($USER['nombre']) ?>" readonly>
      </div>
      <div class="col-md-3">
        <label class="form-label">Rol</label>
        <input class="form-control" value="<?= strtoupper($rol) ?>" readonly>
      </div>
      <div class="col-md-4">
        <label class="form-label">Origen (Sede/Departamento)</label>
        <input class="form-control" value="<?= 'SEDE: '.(int)($USER['sede_id'] ?? 0).' · DEP: '.(int)($USER['departamento_id'] ?? 0) ?>" readonly>
      </div>
    </div>

    <form id="formSolicitud" class="mt-2" method="POST" action="<?= BASE_PATH; ?>/app/views/admin/solicitudes/guardar_solicitud.php" novalidate>
      <input type="hidden" name="competencias_json" id="competencias_json">
      <input type="hidden" name="carrera_estudiada" id="carrera_estudiada">
      <input type="hidden" name="area_experiencia" id="area_experiencia">

      <div class="section-title"><span class="dot"></span>Datos de la vacante</div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">TITULO DE LA SOLICITUD</label>
          <input name="titulo" class="form-control to-upper" placeholder="EJ. INGENIERO DE CALIDAD">
        </div>
        <div class="col-md-4">
          <label class="form-label req">PUESTO</label>
          <input name="puesto" class="form-control to-upper" required>
        </div>
        <div class="col-md-2">
          <label class="form-label req">VACANTES</label>
          <input type="number" min="1" name="vacantes" class="form-control" value="1" required>
        </div>

        <div class="col-md-6">
          <label class="form-label req">SEDE</label>
          <select name="sede_id" id="sede_id" class="form-select" <?= ($rol==='gerente' && $USER['sede_id'])?'':'required' ?> <?= ($rol==='gerente' && $USER['sede_id'])?'disabled':'' ?>>
            <option value="">— SELECCIONA —</option>
            <?php foreach($sedes as $s): ?>
              <option value="<?= (int)$s['id'] ?>" <?= ($USER['sede_id'] && (int)$USER['sede_id']===(int)$s['id'])?'selected':'' ?>>
                <?= htmlspecialchars($s['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if ($rol==='gerente' && $USER['sede_id']): ?>
            <input type="hidden" name="sede_id" value="<?= (int)$USER['sede_id'] ?>">
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label req">DEPARTAMENTO</label>
          <select name="departamento_id" id="departamento_id" class="form-select" <?= ($rol==='jefe_area' && $USER['departamento_id'])?'disabled':'required' ?>>
            <option value="">— SELECCIONA —</option>
          </select>
          <?php if ($rol==='jefe_area' && $USER['departamento_id']): ?>
            <input type="hidden" name="departamento_id" value="<?= (int)$USER['departamento_id'] ?>">
          <?php endif; ?>
        </div>

        <div class="col-md-4">
          <label class="form-label">FECHA DE INGRESO DESEADA</label>
          <input type="date" name="fecha_ingreso_deseada" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">TIPO DE CONTRATO</label>
          <select name="tipo_contrato" class="form-select to-upper">
            <option value="">— SELECCIONA —</option>
            <option>INDETERMINADO</option><option>DETERMINADO</option>
            <option>POR PROYECTO</option><option>HONORARIOS</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">MODALIDAD</label>
          <select name="modalidad" class="form-select to-upper">
            <option value="">— SELECCIONA —</option>
            <option>PRESENCIAL</option><option>HIBRIDO</option><option>REMOTO</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">HORARIO</label>
          <input name="horario" class="form-control to-upper" placeholder="L-V 9:00-18:00">
        </div>
        <div class="col-md-4">
          <label class="form-label">SALARIO MIN</label>
          <input type="number" step="0.01" name="salario_min" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">SALARIO MAX</label>
          <input type="number" step="0.01" name="salario_max" class="form-control">
        </div>
      </div>

      <div class="section-title"><span class="dot"></span>Perfil requerido</div>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label req">ESCOLARIDAD MINIMA</label>
          <select name="escolaridad_min" class="form-select" required>
            <option value="">— SELECCIONA —</option>
            <option value="1">BACHILLERATO</option>
            <option value="2">LICENCIATURA</option>
            <option value="3">MAESTRIA</option>
            <option value="4">DOCTORADO</option>
          </select>
        </div>

        <div class="col-md-8">
          <label class="form-label">CARRERA / AFIN (PUEDES AGREGAR VARIAS)</label>
          <div class="d-flex gap-2">
            <input id="carrera_input" list="dl-carreras" class="form-control to-upper" placeholder="EJ. INGENIERIA INDUSTRIAL" autocomplete="off">
            <button type="button" id="btn_add_carrera" class="btn btn-outline-brand">AGREGAR</button>
          </div>
          <datalist id="dl-carreras">
            <?php foreach($carreras_cat as $c): ?><option><?= $c ?></option><?php endforeach; ?>
          </datalist>
          <div id="carrera_chips" class="chips mt-2"></div>
        </div>

        <div class="col-md-6">
          <label class="form-label">EXPERIENCIA (ANIOS)</label>
          <input type="number" min="0" name="experiencia_anios" class="form-control" value="0">
        </div>

        <div class="col-md-6">
          <label class="form-label">AREAS DE EXPERIENCIA (VARIAS OPCIONES)</label>
          <div class="d-flex gap-2">
            <input id="area_input" list="dl-areas" class="form-control to-upper" placeholder="EJ. CALIDAD" autocomplete="off">
            <button type="button" id="btn_add_area" class="btn btn-outline-brand">AGREGAR</button>
          </div>
          <datalist id="dl-areas">
            <?php foreach($areas_exp as $ax): ?><option><?= $ax ?></option><?php endforeach; ?>
          </datalist>
          <div id="area_chips" class="chips mt-2"></div>
        </div>

        <div class="col-md-6">
          <label class="form-label">INGLES (NIVEL)</label>
          <select name="ingles_combo" id="ingles_combo" class="form-select">
            <option value="0">NO REQUERIDO</option>
            <option value="BASICO">BASICO (~30%)</option>
            <option value="INTERMEDIO">INTERMEDIO (~60%)</option>
            <option value="AVANZADO">AVANZADO (~85%)</option>
            <option value="NATIVO">NATIVO (100%)</option>
          </select>
        </div>

        <div class="col-12">
          <label class="form-label">COMPETENCIAS</label>
          <div class="d-flex gap-2">
            <input id="comp_input" list="dl-competencias" class="form-control to-upper" placeholder="ESCRIBE Y PRESIONA ENTER" autocomplete="off">
            <button type="button" id="btn_add_comp" class="btn btn-outline-brand">AGREGAR</button>
          </div>
          <datalist id="dl-competencias">
            <?php foreach($competencias_sugeridas as $c): ?><option><?= $c ?></option><?php endforeach; ?>
          </datalist>
          <div id="comp_chips" class="chips mt-2"></div>
        </div>
      </div>

      <div class="section-title"><span class="dot"></span>Motivo y justificacion</div>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">MOTIVO</label>
          <select name="motivo" class="form-select to-upper">
            <option value="">— SELECCIONA —</option>
            <option>CRECIMIENTO</option><option>REEMPLAZO</option><option>PROYECTO</option><option>SEASONAL</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">REEMPLAZO DE</label>
          <input name="reemplazo_de" class="form-control to-upper" placeholder="NOMBRE A REEMPLAZAR">
        </div>
        <div class="col-md-4">
          <label class="form-label">PRIORIDAD</label>
          <select name="prioridad" class="form-select to-upper">
            <option>NORMAL</option><option>URGENTE</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label req">JUSTIFICACION</label>
          <textarea name="justificacion" rows="3" class="form-control to-upper" required></textarea>
        </div>
        <div class="col-12">
          <label class="form-label">RESPONSABILIDADES (BREVE LISTADO)</label>
          <textarea name="responsabilidades" rows="3" class="form-control to-upper" placeholder="- TAREA 1&#10;- TAREA 2"></textarea>
        </div>
      </div>

      <div class="d-flex flex-wrap gap-2 mt-4 justify-content-center">
        <a href="<?= BASE_PATH; ?>/app/views/admin/solicitudes/menu.php" class="btn btn-outline-secondary">← Bandeja</a>
        <button id="btnEnviar" type="button" class="btn btn-brand">📨 Enviar</button>
      </div>
    </form>
  </div>
</div>

<script>
// ===== Mayúsculas SIN acentos =====
function toUpperNoAccent(s){ if(!s) return ''; return s.normalize('NFD').replace(/[\u0300-\u036f]/g,'').toUpperCase(); }
document.querySelectorAll('.to-upper').forEach(i=>{ i.addEventListener('input', ()=> i.value = toUpperNoAccent(i.value)); });

// ===== Dependientes Sede -> Depto =====
const sedeSel = document.getElementById('sede_id');
const depSel  = document.getElementById('departamento_id');
async function cargarDeps(){
  const s = sedeSel?.value || '';
  depSel.innerHTML = '<option value="">— SELECCIONA —</option>';
  if(!s) return;
  const base = window.location.pathname;
  const url  = `${base}?ajax=deps&sede_id=${encodeURIComponent(s)}`;
  try{
    const r = await fetch(url, {credentials:'same-origin'});
    const data = await r.json();
    (data.items||[]).forEach(d=>{
      const o = document.createElement('option');
      o.value = d.id; o.textContent = toUpperNoAccent(String(d.nombre||''));
      <?php if ($rol==='jefe_area' && $USER['departamento_id']): ?>
        if (String(d.id) === "<?= (int)$USER['departamento_id'] ?>") o.selected = true;
      <?php endif; ?>
      depSel.appendChild(o);
    });
  }catch(e){ showToast('err','No se pudieron cargar los departamentos.'); }
}
sedeSel?.addEventListener('change', cargarDeps);
<?php if ($USER['sede_id']): ?>document.addEventListener('DOMContentLoaded', cargarDeps);<?php endif; ?>

// ===== Chips =====
function textNodeSafe(txt){ return document.createTextNode(txt); }
function Chips({input, addBtn, chipsBox, onChange}){
  const items = [];
  function render(){
    chipsBox.innerHTML='';
    items.forEach((txt,idx)=>{
      const span=document.createElement('span'); span.className='chip';
      const t=document.createElement('span'); t.appendChild(textNodeSafe(txt));
      const btn=document.createElement('button'); btn.type='button'; btn.setAttribute('aria-label','Quitar'); btn.innerHTML='&times;';
      btn.addEventListener('click', ()=>{ items.splice(idx,1); render(); onChange(items); });
      span.appendChild(t); span.appendChild(btn); chipsBox.appendChild(span);
    });
  }
  function addFromInput(){
    const v = toUpperNoAccent((input.value||'').trim());
    if(!v) return;
    if(!items.includes(v)){ items.push(v); render(); onChange(items); }
    input.value='';
  }
  addBtn.addEventListener('click', addFromInput);
  input.addEventListener('keydown', e=>{ if(e.key==='Enter'){ e.preventDefault(); addFromInput(); }});
  return { add:(v)=>{ if(v && !items.includes(v)){ items.push(toUpperNoAccent(v)); render(); onChange(items); } } };
}
const comp = Chips({ input: document.getElementById('comp_input'), addBtn: document.getElementById('btn_add_comp'),
  chipsBox: document.getElementById('comp_chips'), onChange: (arr)=>{ document.getElementById('competencias_json').value = JSON.stringify(arr); }});
const carrera = Chips({ input: document.getElementById('carrera_input'), addBtn: document.getElementById('btn_add_carrera'),
  chipsBox: document.getElementById('carrera_chips'), onChange: (arr)=>{ document.getElementById('carrera_estudiada').value = arr.join(' | '); }});
const area = Chips({ input: document.getElementById('area_input'), addBtn: document.getElementById('btn_add_area'),
  chipsBox: document.getElementById('area_chips'), onChange: (arr)=>{ document.getElementById('area_experiencia').value = arr.join(' | '); }});

// ===== Alertas (en la misma tarjeta) =====
const layer  = document.getElementById('alertLayer');
const box    = document.getElementById('alertBox');
const toast  = document.getElementById('toast');

function showToast(type, msg){
  toast.textContent = msg;
  toast.className = 'toast' + (type==='err' ? ' err' : '');
  toast.style.display = 'inline-block';
  setTimeout(()=>{ toast.style.display='none'; }, 2500);
}
function showConfirm({title='Confirmar', msg='¿Seguro?', yesText='Sí, enviar', noText='Cancelar', onYes=null}){
  box.innerHTML = `
    <div class="alert-title">${title}</div>
    <div class="alert-msg">${msg}</div>
    <div class="alert-actions">
      <button type="button" class="btn-ghost" id="btnNo">${noText}</button>
      <button type="button" class="btn-yes" id="btnYes">${yesText}</button>
    </div>`;
  layer.style.display='flex';
  document.getElementById('btnNo').onclick = ()=>{ layer.style.display='none'; };
  document.getElementById('btnYes').onclick= ()=>{ layer.style.display='none'; if(typeof onYes==='function') onYes(); };
}
function showSuccess({title='¡Enviado con éxito!', msg='Redireccionando al detalle…', onDone=null}){
  box.innerHTML = `
    <div class="succ-mark"></div>
    <div class="alert-title">${title}</div>
    <div class="alert-msg">${msg}</div>`;
  layer.style.display='flex';
  setTimeout(()=>{ layer.style.display='none'; if(typeof onDone==='function') onDone(); }, 1400);
}
function showErrorModal(rawText){
  box.innerHTML = `
    <div class="alert-title">Error del servidor</div>
    <div class="alert-msg" style="text-align:left; max-height:40vh; overflow:auto; white-space:pre-wrap;">${rawText}</div>
    <div class="alert-actions">
      <button type="button" class="btn-yes" id="btnCloseErr">Cerrar</button>
    </div>`;
  layer.style.display='flex';
  document.getElementById('btnCloseErr').onclick = ()=>{ layer.style.display='none'; };
}

// ===== Envío AJAX con confirmación + robusto ante HTML/warnings =====
const form   = document.getElementById('formSolicitud');
const btnEnv = document.getElementById('btnEnviar');

function validarBasico(){
  const puesto = (form.querySelector('[name="puesto"]').value||'').trim();
  const sede   = parseInt(form.querySelector('[name="sede_id"]')?.value||'0',10);
  const dep    = parseInt(form.querySelector('[name="departamento_id"]')?.value||'0',10);
  const just   = (form.querySelector('[name="justificacion"]').value||'').trim();
  const vac    = parseInt(form.querySelector('[name="vacantes"]').value||'0',10);
  const salMin = parseFloat(form.querySelector('[name="salario_min"]').value||'0');
  const salMax = parseFloat(form.querySelector('[name="salario_max"]').value||'0');

  if (puesto==='') { showToast('err','El campo PUESTO es obligatorio.'); return false; }
  if (!(sede>0))   { showToast('err','Debes seleccionar una SEDE.'); return false; }
  if (!(dep>0))    { showToast('err','Debes seleccionar un DEPARTAMENTO.'); return false; }
  if (just==='')   { showToast('err','La JUSTIFICACIÓN es obligatoria.'); return false; }
  if (vac<1)       { showToast('err','VACANTES debe ser al menos 1.'); return false; }
  if (!isNaN(salMin) && !isNaN(salMax) && salMin>0 && salMax>0 && salMin>salMax){
    showToast('err','SALARIO MIN no puede ser mayor que SALARIO MAX.'); return false;
  }
  return true;
}

btnEnv.addEventListener('click', ()=>{
  if (!validarBasico()) return;
  showConfirm({
    title: 'Confirmar envío',
    msg: '¿Estás seguro de que la información es correcta?',
    yesText: 'Sí, enviar ahora',
    noText: 'Seguir editando',
    onYes: async ()=>{
      try{
        btnEnv.disabled = true;
        const fd = new FormData(form);
        fd.append('ajax','1'); // <- para que PHP responda JSON

        const r   = await fetch(form.action, { method:'POST', body: fd, headers:{ 'X-Requested-With':'fetch' } });
        const txt = await r.text();

        let data;
        try {
          data = JSON.parse(txt);
        } catch(parseErr){
          // Si PHP escupió HTML/Warning junto con el JSON, muéstralo
          btnEnv.disabled = false;
          showErrorModal(txt.substring(0, 4000));
          console.error('Respuesta no-JSON del servidor:', txt);
          return;
        }

        if (!data || !data.ok) {
          btnEnv.disabled = false;
          showToast('err', (data && data.msg) ? data.msg : 'No se pudo guardar la solicitud.');
          return;
        }

        // Éxito
        showSuccess({
          title:'¡Solicitud enviada con éxito!',
          msg:'Redireccionando al detalle…',
          onDone: ()=>{ window.location.href = data.detalle_url; }
        });

      }catch(e){
        btnEnv.disabled = false;
        showToast('err','Error de red. Intenta de nuevo.');
        console.error(e);
      }
    }
  });
});
</script>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/footer.php'; ?>
