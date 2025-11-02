<?php
/* ============================================================
   Mi perfil — TODO en un solo archivo (vista + acciones POST)
   - Guarda foto en /public/img/usuarios/
   - Actualiza columnas: nombre_completo, correo, telefono, fotografia
   - Cambia contraseña en columna: contrasena
   - Refresca sesión: nombre_completo y foto
   ============================================================ */
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');
if (session_status() === PHP_SESSION_NONE) session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';
$db = Conexion::getConexion();

/* ---------- Helpers ---------- */
function esc($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function digits($s){ return preg_replace('/\D+/', '', (string)$s); }
function initials($name){
  $n = trim((string)$name); if ($n==='') return 'U';
  $p = preg_split('/\s+/u',$n);
  $ini = mb_substr($p[0],0,1,'UTF-8'); if (count($p)>1) $ini .= mb_substr(end($p),0,1,'UTF-8');
  return mb_strtoupper($ini,'UTF-8');
}
function photoUrl(?string $raw): string {
  $raw = trim((string)$raw);
  if ($raw==='') return '';
  // si ya es URL absoluta o ruta con subcarpetas
  if (preg_match('#^https?://#i',$raw)) return $raw.(str_contains($raw,'?')?'&':'?').'v='.time();
  if (str_contains($raw,'/')) return BASE_PATH.'/'.ltrim($raw,'/').(str_contains($raw,'?')?'&':'?').'v='.time();
  // filename simple en /public/img/usuarios/
  return BASE_PATH.'/public/img/usuarios/'.$raw.'?v='.time();
}
/* Toast redirect */
function goBackMsg($ok, $msg){
  $ok = $ok ? '1' : '0';
  header('Location: ' . BASE_PATH . '/app/views/perfil/index.php?ok='.$ok.'&msg='.urlencode($msg));
  exit;
}

/* ---------- Sesión mínima ---------- */
$UID = (int)($_SESSION['id'] ?? 0);
if (!$UID) { header('Location: '.BASE_PATH.'/public/login.php'); exit; }

/* ---------- CSRF ---------- */
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$CSRF = $_SESSION['csrf'];

/* ---------- Acciones POST (en este mismo archivo) ---------- */
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (empty($_POST['csrf']) || !hash_equals($CSRF, (string)$_POST['csrf'])) {
    goBackMsg(false, 'CSRF inválido.');
  }
  $action = (string)($_POST['action'] ?? '');

  /* ===== Guardar perfil (datos + avatar base64 opcional) ===== */
  if ($action === 'save_profile') {
    $nombre   = trim((string)($_POST['nombre_completo'] ?? ''));
    $correo   = trim((string)($_POST['correo'] ?? ''));
    $telefono = digits($_POST['telefono'] ?? '');
    $avatarB64= (string)($_POST['avatar_data'] ?? '');

    if ($nombre==='') goBackMsg(false, 'El nombre es obligatorio.');
    if ($correo!=='' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) goBackMsg(false, 'Correo inválido.');
    if ($telefono!=='' && strlen($telefono)!==10) goBackMsg(false, 'El teléfono debe tener 10 dígitos.');

    // Cargar registro actual para saber foto previa
    try{
      $st=$db->prepare("SELECT fotografia FROM usuarios WHERE id=? LIMIT 1");
      $st->execute([$UID]);
      $actual = $st->fetch(PDO::FETCH_ASSOC) ?: [];
    }catch(Throwable $e){ goBackMsg(false, 'No se pudo cargar tu registro.'); }

    $root   = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH;
    $dirFS  = $root . '/public/img/usuarios/';
    if (!is_dir($dirFS)) @mkdir($dirFS, 0775, true);

    $fotoNueva = null;
    if ($avatarB64!=='') {
      if (!preg_match('#^data:image/(png|jpeg);base64,#i', $avatarB64, $m)) {
        goBackMsg(false, 'Formato de imagen no soportado (usa JPG o PNG).');
      }
      $ext = strtolower($m[1])==='png' ? 'png' : 'jpg';
      $raw = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $avatarB64), true);
      if ($raw===false) goBackMsg(false, 'Imagen inválida.');
      if (strlen($raw) > 2*1024*1024) goBackMsg(false, 'La imagen supera 2 MB.');

      $fname = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
      if (file_put_contents($dirFS.$fname, $raw) === false) {
        goBackMsg(false, 'No se pudo guardar la imagen.');
      }
      // borrar foto previa si existía
      if (!empty($actual['fotografia'])) {
        $old = $dirFS . basename($actual['fotografia']);
        if (is_file($old)) @unlink($old);
      }
      $fotoNueva = $fname;
    }

    // Actualizar BD
    try{
      $sets = "nombre_completo=:n, correo=:c, telefono=:t";
      $params = [':n'=>$nombre, ':c'=>($correo?:null), ':t'=>($telefono?:null), ':id'=>$UID];
      if ($fotoNueva) { $sets .= ", fotografia=:f"; $params[':f']=$fotoNueva; }
      $st=$db->prepare("UPDATE usuarios SET $sets WHERE id=:id LIMIT 1");
      $ok=$st->execute($params);
      if (!$ok) goBackMsg(false, 'No se pudo guardar los cambios.');
    }catch(Throwable $e){ goBackMsg(false, 'Error al guardar.'); }

    // Refrescar sesión (navbar)
    $_SESSION['nombre_completo'] = $nombre;
    if ($fotoNueva !== null) $_SESSION['foto'] = $fotoNueva;

    goBackMsg(true, 'Datos guardados correctamente.');
  }

  /* ===== Cambiar contraseña ===== */
  if ($action === 'change_password') {
    $old = (string)($_POST['old'] ?? '');
    $nw  = (string)($_POST['new'] ?? '');
    $nw2 = (string)($_POST['new2'] ?? '');

    if ($nw==='' && $nw2==='') goBackMsg(false, 'No hay cambios.');
    if ($nw==='' || $nw2==='') goBackMsg(false, 'Completa y confirma la nueva contraseña.');
    if ($nw !== $nw2) goBackMsg(false, 'Las contraseñas no coinciden.');
    if (strlen($nw) < 6) goBackMsg(false, 'La nueva contraseña debe tener al menos 6 caracteres.');

    try{
      $st=$db->prepare("SELECT contrasena FROM usuarios WHERE id=? LIMIT 1");
      $st->execute([$UID]); $hash=(string)$st->fetchColumn();

      if ($hash!=='' && !password_verify($old, $hash)) {
        goBackMsg(false, 'La contraseña actual no es correcta.');
      }

      $newHash = password_hash($nw, PASSWORD_BCRYPT);
      $st=$db->prepare("UPDATE usuarios SET contrasena=? WHERE id=? LIMIT 1");
      $ok=$st->execute([$newHash, $UID]);
      if (!$ok) goBackMsg(false, 'No se pudo actualizar la contraseña.');
    }catch(Throwable $e){ goBackMsg(false, 'Error al actualizar la contraseña.'); }

    goBackMsg(true, 'Contraseña actualizada.');
  }

  // Acción desconocida
  goBackMsg(false, 'Acción inválida.');
}

/* ---------- GET (render de la vista) ---------- */
/* Datos del usuario */
try {
  $st = $db->prepare("
    SELECT u.id, u.usuario, u.nombre_completo, u.correo, u.telefono, u.rol, u.estado,
           u.sede_id, u.departamento_id, u.fotografia,
           s.nombre AS sede_nombre, d.nombre AS dep_nombre
    FROM usuarios u
    LEFT JOIN sedes s ON s.id=u.sede_id
    LEFT JOIN departamentos d ON d.id=u.departamento_id
    WHERE u.id=? LIMIT 1
  ");
  $st->execute([$UID]);
  $u = $st->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (\Throwable $e) { $u=[]; }
if (!$u) { echo 'Usuario no encontrado.'; exit; }

/* Foto a mostrar (BD > sesión) */
$foto_db  = trim((string)($u['fotografia'] ?? ''));
$foto_ses = trim((string)($_SESSION['foto'] ?? ''));
$foto_raw = $foto_db ?: $foto_ses;
$foto_url = $foto_raw ? photoUrl($foto_raw) : '';
$ini      = initials($u['nombre_completo'] ?: $u['usuario']);

/* Catálogos solo para mostrar */
$sedes=$departamentos=[];
try{
  $sedes = $db->query("SELECT id,nombre FROM sedes ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC) ?: [];
  $departamentos = $db->query("SELECT id,nombre FROM departamentos ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC) ?: [];
}catch(\Throwable $e){}

/* ---------- Vista ---------- */
$titulo_pagina = "Mi perfil";
require $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/header.php';
?>
<style>
:root{
  --ink:#eaf2ff; --muted:#c8d4f0; --brand:#0D6EFD; --ok:#22c55e;
  --glass-bg: linear-gradient(180deg, rgba(255,255,255,.12), rgba(255,255,255,.08));
  --glass-brd: rgba(255,255,255,.26);
}
body{ background:#0a0f1d; }
.pf-wrap{ width:min(1100px,96vw); margin: calc(var(--nav-h,64px) + 18px) auto 24px; color:#eaf2ff; }
.pf-head{ display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 16px;
  border:1px solid var(--glass-brd); background:var(--glass-bg); border-radius:20px; backdrop-filter:blur(12px);
  box-shadow:0 28px 80px rgba(0,0,0,.36), inset 0 1px 0 rgba(255,255,255,.18); }
.pf-title{ margin:0; font-size:clamp(1.6rem,2.2vw,2rem); font-weight:1000 }
.pf-grid{ display:grid; grid-template-columns: 320px 1fr; gap:14px; margin-top:14px }
@media (max-width: 960px){ .pf-grid{ grid-template-columns: 1fr } }
.card-g{ border:1px solid var(--glass-brd); background:linear-gradient(180deg,#0c1428,#0b1120);
  border-radius:18px; padding:14px; box-shadow:0 14px 44px rgba(0,0,0,.32); }
.ava-box{ display:grid; place-items:center; gap:10px }
.ava{ width:120px;height:120px;border-radius:50%; overflow:hidden; display:grid; place-items:center;
  background:conic-gradient(from 120deg,#0ea5e9,#6366f1,#22c55e,#0ea5e9); color:#fff; font-weight:1000; font-size:2.2rem; }
.ava img{ width:100%; height:100%; object-fit:cover; border-radius:50% }
.drop{ margin-top:8px; border:1px dashed rgba(255,255,255,.26); border-radius:14px; padding:10px; text-align:center; cursor:pointer; color:#cfe0ff }
.drop input{ display:none }
.form-label{ font-weight:800; color:#cfe0ff }
.form-control,.form-select{ background:#0e1c36; color:#dbe8ff; border:1px solid rgba(255,255,255,.18) }
.form-control:disabled,.form-select:disabled{ background:#0e1c36 !important; color:#a9c2ff !important; opacity:1 }
.help{ color:#9fb3ff; font-size:.85rem }
.toastx{ position: fixed; top: calc(var(--nav-h,64px) + 12px); right: 14px; z-index: 1080;
  padding: 12px 14px; border-radius: 12px; color:#0b1326; background:#d1fae5; box-shadow:0 10px 30px rgba(0,0,0,.35);
  font-weight:800; opacity:0; transform:translateY(-6px); transition: opacity .28s ease, transform .28s ease; }
.toastx.err{ background:#fee2e2; } .toastx.on{ opacity:1; transform:translateY(0) }
.badge-soft{ display:inline-block; padding:.14rem .55rem; border-radius:999px; border:1px solid rgba(255,255,255,.2); background:#0e1c36; color:#cfe0ff }
</style>

<div class="pf-wrap">
  <div class="pf-head">
    <div>
      <h1 class="pf-title">Mi perfil</h1>
      <div class="pf-sub">Administra tu información personal y tu contraseña.</div>
    </div>
    <div><span class="badge-soft"><?= esc(strtoupper($u['rol'])) ?></span></div>
  </div>

  <div class="pf-grid">
    <!-- Avatar -->
    <div class="card-g">
      <div class="ava-box">
        <div class="ava" id="pfAvatar">
          <?php if ($foto_url): ?>
            <img src="<?= esc($foto_url) ?>" alt="avatar" onerror="this.style.display='none'; this.parentElement.textContent='<?= esc(initials($u['nombre_completo']?:$u['usuario'])) ?>';">
          <?php else: ?>
            <?= esc(initials($u['nombre_completo']?:$u['usuario'])) ?>
          <?php endif; ?>
        </div>
        <div class="ava-name"><?= esc($u['nombre_completo'] ?: $u['usuario']) ?></div>
        <div class="ava-user">@<?= esc($u['usuario']) ?></div>

        <label class="drop" id="dropArea">
          <div><i class="bi bi-image"></i> Arrastra una imagen aquí o haz clic para seleccionar</div>
          <div class="help">JPG/PNG, máx. 2 MB.</div>
          <input type="file" id="fileInput" accept="image/png,image/jpeg">
        </label>
      </div>
    </div>

    <!-- Datos -->
    <div class="card-g">
      <form method="post" id="formPerfil" onsubmit="return validateProfile()" class="mb-4">
        <input type="hidden" name="csrf" value="<?= esc($CSRF) ?>">
        <input type="hidden" name="action" value="save_profile">
        <input type="hidden" name="avatar_data" id="avatarData">

        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Nombre completo</label>
            <input type="text" name="nombre_completo" class="form-control" value="<?= esc($u['nombre_completo']) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Usuario</label>
            <input type="text" class="form-control" value="<?= esc($u['usuario']) ?>" disabled readonly>
          </div>

          <div class="col-md-6">
            <label class="form-label">Correo</label>
            <input type="email" name="correo" id="correo" class="form-control" value="<?= esc($u['correo']) ?>" placeholder="alguien@dominio.com">
          </div>
          <div class="col-md-6">
            <label class="form-label">Teléfono (10 dígitos)</label>
            <input type="text" name="telefono" id="telefono" class="form-control" value="<?= esc($u['telefono']) ?>" inputmode="numeric" maxlength="14">
          </div>

          <div class="col-md-6">
            <label class="form-label">Sede</label>
            <input class="form-control" value="<?= esc($u['sede_nombre'] ?: '— Sin sede') ?>" disabled>
            <div class="help">Este campo no es editable.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Departamento</label>
            <input class="form-control" value="<?= esc($u['dep_nombre'] ?: '— Sin departamento') ?>" disabled>
            <div class="help">Este campo no es editable.</div>
          </div>
        </div>

        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary"><i class="bi bi-save"></i> Guardar cambios</button>
          <a class="btn btn-outline-secondary" href="<?= BASE_PATH ?>/public/dashboard.php">Cancelar</a>
        </div>
      </form>

      <hr class="text-secondary">

      <form method="post" class="mt-3" autocomplete="off" id="formPwd" onsubmit="return validatePass()">
        <input type="hidden" name="csrf" value="<?= esc($CSRF) ?>">
        <input type="hidden" name="action" value="change_password">
        <h5 class="mb-3">Cambiar contraseña</h5>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Contraseña actual</label>
            <input type="password" name="old" class="form-control" minlength="6">
          </div>
          <div class="col-md-4">
            <label class="form-label">Nueva contraseña</label>
            <input type="password" name="new" id="p1" class="form-control" minlength="6">
          </div>
          <div class="col-md-4">
            <label class="form-label">Confirmar nueva</label>
            <input type="password" name="new2" id="p2" class="form-control" minlength="6">
          </div>
        </div>
        <div class="mt-3">
          <button class="btn btn-warning"><i class="bi bi-key"></i> Actualizar contraseña</button>
        </div>
        <div class="help mt-2">Si no quieres cambiar la contraseña deja los campos vacíos.</div>
      </form>
    </div>
  </div>
</div>

<div id="toastx" class="toastx" role="status" aria-live="polite"></div>

<script>
/* Uploader con preview (base64) */
(function(){
  const fileInput = document.getElementById('fileInput');
  const dropArea  = document.getElementById('dropArea');
  const avatarBox = document.getElementById('pfAvatar');
  const avatarData= document.getElementById('avatarData');

  function preview(file){
    if(!file) return;
    if(!/image\/(png|jpeg)/i.test(file.type)){ showToast('Formato no válido. Usa JPG o PNG.', false); return; }
    if(file.size > 2*1024*1024){ showToast('La imagen supera 2 MB.', false); return; }
    const reader = new FileReader();
    reader.onload = e => {
      const src = e.target.result;
      avatarBox.innerHTML = '<img src="'+src+'" alt="preview">';
      avatarData.value = src; // se envía en save_profile
    };
    reader.readAsDataURL(file);
  }

  dropArea.addEventListener('click', ()=> fileInput.click());
  fileInput.addEventListener('change', e=> preview(e.target.files[0]));
  ['dragenter','dragover','dragleave','drop'].forEach(ev=>{
    dropArea.addEventListener(ev, e=>{ e.preventDefault(); e.stopPropagation(); });
  });
  dropArea.addEventListener('drop', e=>{
    const f = e.dataTransfer.files[0]; if(f) preview(f);
  });
})();

/* Formatos y validaciones de email/teléfono */
(function(){
  const tel = document.getElementById('telefono');
  const mail= document.getElementById('correo');
  if (tel){
    tel.addEventListener('input', ()=>{
      const d = (tel.value || '').replace(/\D+/g,'');
      if (d.length <= 3) tel.value = d;
      else if (d.length <= 6) tel.value = d.slice(0,3)+' '+d.slice(3);
      else tel.value = d.slice(0,3)+' '+d.slice(3,6)+' '+d.slice(6,10);
    });
  }
  if (mail){
    mail.addEventListener('blur', ()=>{
      const v = mail.value.trim();
      if (v && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) {
        showToast('Ingresa un correo válido (incluye @).', false);
        mail.focus();
      }
    });
  }
})();
function validateProfile(){
  const mail = document.getElementById('correo').value.trim();
  const telRaw = document.getElementById('telefono').value.trim();
  const digits = telRaw.replace(/\D+/g,'');
  if (mail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mail)){ showToast('Ingresa un correo válido.', false); return false; }
  if (digits && digits.length !== 10){ showToast('El teléfono debe tener exactamente 10 dígitos.', false); return false; }
  document.getElementById('telefono').value = digits; // se envía "limpio"
  return true;
}
function validatePass(){
  const a = document.getElementById('p1').value.trim();
  const b = document.getElementById('p2').value.trim();
  if (!a && !b) return true;
  if (!a || !b){ showToast('Debes capturar y confirmar tu nueva contraseña.', false); return false; }
  if (a !== b){ showToast('Las contraseñas no coinciden.', false); return false; }
  return true;
}

/* Toast (?ok=&msg=) */
(function(){
  const params = new URLSearchParams(location.search);
  if(!params.has('ok') || !params.has('msg')) return;
  showToast(params.get('msg') || '', params.get('ok')==='1');
})();
function showToast(msg, ok=true){
  const t = document.getElementById('toastx');
  t.textContent = msg || (ok ? 'Operación exitosa' : 'Ocurrió un error');
  t.classList.toggle('err', !ok);
  t.classList.add('on');
  setTimeout(()=>t.classList.remove('on'), 3200);
}
</script>

<?php require $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/footer.php'; ?>
