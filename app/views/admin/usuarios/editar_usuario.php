<?php
if (!isset($_SESSION)) session_start();
require_once dirname(__DIR__, 4) . '/config/conexion.php';

class UsuarioEdita {
    private PDO $db;
    public function __construct(){ $this->db = Conexion::getConexion(); }

    public function obtenerPorId(int $id): ?array {
        $st = $this->db->prepare("SELECT id, usuario, nombre_completo, numero_empleado, correo, rol,
                                         departamento_id, sede_id, estado, telefono, fotografia
                                  FROM usuarios WHERE id=:id LIMIT 1");
        $st->execute([':id'=>$id]); $r=$st->fetch(); return $r?:null;
    }
    public function existeUsuarioEditando(string $u, int $id): bool {
        $st=$this->db->prepare("SELECT 1 FROM usuarios WHERE UPPER(usuario)=UPPER(:u) AND id<>:id LIMIT 1");
        $st->execute([':u'=>$u, ':id'=>$id]); return (bool)$st->fetchColumn();
    }
    public function existeNumeroEmpleadoEditando(string $ne, int $id): bool {
        $st=$this->db->prepare("SELECT 1 FROM usuarios WHERE numero_empleado=:ne AND id<>:id LIMIT 1");
        $st->execute([':ne'=>$ne, ':id'=>$id]); return (bool)$st->fetchColumn();
    }
    public function existeGerenteEnSedeEditando(?int $sedeId, int $id): bool {
        if (empty($sedeId)) return false;
        $st=$this->db->prepare("SELECT 1 FROM usuarios
                                 WHERE rol='gerente' AND sede_id=:s
                                   AND LOWER(estado)='activo' AND id<>:id LIMIT 1");
        $st->execute([':s'=>$sedeId, ':id'=>$id]); return (bool)$st->fetchColumn();
    }
    public function existeJefeEnDeptoSedeEditando(?int $sedeId, ?int $depId, int $id): bool {
        if (empty($sedeId) || empty($depId)) return false;
        $st=$this->db->prepare("SELECT 1 FROM usuarios
                                 WHERE rol='jefe_area' AND sede_id=:s AND departamento_id=:d
                                   AND LOWER(estado)='activo' AND id<>:id LIMIT 1");
        $st->execute([':s'=>$sedeId, ':d'=>$depId, ':id'=>$id]); return (bool)$st->fetchColumn();
    }

    public function actualizar(array $d): bool {
        $campos=[
            "usuario=:usuario","nombre_completo=:nombre_completo","numero_empleado=:numero_empleado",
            "correo=:correo","rol=:rol","departamento_id=:departamento_id","sede_id=:sede_id",
            "estado=:estado","telefono=:telefono"
        ];
        $params=[
            ':usuario'=>$d['usuario'],':nombre_completo'=>$d['nombre_completo'],':numero_empleado'=>$d['numero_empleado'],
            ':correo'=>$d['correo'] ?: null,':rol'=>$d['rol'],':departamento_id'=>$d['departamento_id'] ?: null,
            ':sede_id'=>$d['sede_id'] ?: null,':estado'=>$d['estado'],':telefono'=>$d['telefono'] ?: null,':id'=>$d['id']
        ];
        if (!empty($d['contrasena_hash'])) { $campos[]="contrasena=:contrasena"; $params[':contrasena']=$d['contrasena_hash']; }
        if (!empty($d['fotografia'])) { $campos[]="fotografia=:fotografia"; $params[':fotografia']=$d['fotografia']; }
        $st=$this->db->prepare("UPDATE usuarios SET ".implode(', ',$campos)." WHERE id=:id LIMIT 1");
        return $st->execute($params);
    }

    /* Responsables */
    public function asignarResponsableDepto(int $depId, int $userId): bool {
        $st=$this->db->prepare("UPDATE departamentos SET responsable_id=:uid WHERE id=:dep LIMIT 1");
        return $st->execute([':uid'=>$userId, ':dep'=>$depId]);
    }
    public function limpiarResponsableSiCoincide(?int $depIdAnterior, int $userId): void {
        if (empty($depIdAnterior)) return;
        $st=$this->db->prepare("UPDATE departamentos SET responsable_id=NULL WHERE id=:dep AND responsable_id=:uid LIMIT 1");
        $st->execute([':dep'=>$depIdAnterior, ':uid'=>$userId]);
    }
    public function setGerenteSede(int $sedeId, int $userId): bool {
        $st=$this->db->prepare("UPDATE sedes SET gerente_id=:uid WHERE id=:sede LIMIT 1");
        return $st->execute([':uid'=>$userId, ':sede'=>$sedeId]);
    }
    public function limpiarGerenteSiCoincide(?int $sedeIdAnterior, int $userId): void {
        if (empty($sedeIdAnterior)) return;
        $st=$this->db->prepare("UPDATE sedes SET gerente_id=NULL WHERE id=:sede AND gerente_id=:uid LIMIT 1");
        $st->execute([':sede'=>$sedeIdAnterior, ':uid'=>$userId]);
    }

    /* Catálogos */
    public function sedesActivas(): array {
        return $this->db->query("SELECT id, nombre FROM sedes WHERE activo=1 ORDER BY nombre")->fetchAll();
    }
    public function departamentosActivos(?int $sedeId=null): array {
        if ($sedeId) {
            $st=$this->db->prepare("SELECT id, nombre FROM departamentos
                                     WHERE LOWER(estado)='activo' AND sede_id=:s ORDER BY nombre");
            $st->execute([':s'=>$sedeId]); return $st->fetchAll();
        }
        return $this->db->query("SELECT id, nombre FROM departamentos
                                  WHERE LOWER(estado)='activo' ORDER BY nombre")->fetchAll();
    }
}

$M = new UsuarioEdita();

/* ===== AJAX ===== */
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    $id=(int)($_GET['id'] ?? 0);
    switch ($_GET['ajax']) {
        case 'validar_usuario':
            $u=strtoupper(trim($_GET['usuario'] ?? ''));
            echo json_encode(['ok'=>!$M->existeUsuarioEditando($u,$id)]); break;
        case 'validar_ne':
            $ne=trim($_GET['ne'] ?? '');
            echo json_encode(['ok'=>!$M->existeNumeroEmpleadoEditando($ne,$id)]); break;
        case 'validar_gerente':
            $s=!empty($_GET['sede_id']) ? (int)$_GET['sede_id'] : null;
            echo json_encode(['ok'=>!$M->existeGerenteEnSedeEditando($s,$id)]); break;
        case 'validar_jefe':
            $s=!empty($_GET['sede_id']) ? (int)$_GET['sede_id'] : null;
            $d=!empty($_GET['departamento_id']) ? (int)$_GET['departamento_id'] : null;
            echo json_encode(['ok'=>!$M->existeJefeEnDeptoSedeEditando($s,$d,$id)]); break;
        case 'departamentos_por_sede':
            $s=!empty($_GET['sede_id']) ? (int)$_GET['sede_id'] : null;
            echo json_encode(['ok'=>true,'items'=>$M->departamentosActivos($s)]); break;
        default: echo json_encode(['ok'=>false]);
    }
    exit;
}

/* ===== POST ===== */
$mensaje=""; $tipo="";
$root = dirname(__DIR__, 4);
$dirFs = $root.'/public/img/usuarios/';
$dirWeb = '../../../../public/img/usuarios/';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id               = (int)($_POST['id'] ?? 0);
    $usuario          = strtoupper(trim($_POST['usuario'] ?? ''));
    $nombre_completo  = strtoupper(trim($_POST['nombre_completo'] ?? ''));
    $numero_empleado  = trim($_POST['numero_empleado'] ?? '');
    $correo           = trim($_POST['correo'] ?? '');
    $rol              = $_POST['rol'] ?? '';
    $sede_id          = !empty($_POST['sede_id']) ? (int)$_POST['sede_id'] : null;
    $departamento_id  = !empty($_POST['departamento_id']) ? (int)$_POST['departamento_id'] : null;
    $estado           = $_POST['estado'] ?? 'activo';
    $telefono         = trim($_POST['telefono'] ?? '');
    $nueva_pass       = trim($_POST['nueva_contrasena'] ?? '');
    $confirm_pass     = trim($_POST['confirmar_contrasena'] ?? '');

    if ($id<=0) { $mensaje="ID inválido."; $tipo="error"; }
    else { $actual = $M->obtenerPorId($id); if (!$actual) { $mensaje="Usuario no encontrado."; $tipo="error"; } }

    if (!$mensaje && ($usuario==='' || $nombre_completo==='' || $numero_empleado==='' || $rol==='')) {
        $mensaje="Completa los campos obligatorios."; $tipo="warning";
    }
    if (!$mensaje && !empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje="Correo inválido (usuario@dominio)."; $tipo="error";
    }
    if (!$mensaje && $M->existeUsuarioEditando($usuario,$id)) {
        $mensaje="El nombre de usuario ya está en uso."; $tipo="error";
    }
    if (!$mensaje && $M->existeNumeroEmpleadoEditando($numero_empleado,$id)) {
        $mensaje="El número de empleado ya está registrado."; $tipo="error";
    }

    if (!$mensaje && $rol==='gerente' && empty($sede_id)) {
        $mensaje="Para GERENTE debes seleccionar una sede."; $tipo="error";
    }
    if (!$mensaje && $rol==='gerente' && $M->existeGerenteEnSedeEditando($sede_id,$id)) {
        $mensaje="Ya existe un GERENTE activo en la sede seleccionada."; $tipo="error";
    }
    if (!$mensaje && $rol==='jefe_area' && (empty($sede_id) || empty($departamento_id))) {
        $mensaje="Para JEFE DE ÁREA elige sede y departamento."; $tipo="error";
    }
    if (!$mensaje && $rol==='jefe_area' && $M->existeJefeEnDeptoSedeEditando($sede_id,$departamento_id,$id)) {
        $mensaje="Ya existe un JEFE DE ÁREA activo en esa sede/departamento."; $tipo="error";
    }

    // Foto opcional
    $fotoFinal = null;
    if (!$mensaje && isset($_FILES['fotografia']) && $_FILES['fotografia']['error']!==UPLOAD_ERR_NO_FILE) {
        $err  = $_FILES['fotografia']['error']; $size=$_FILES['fotografia']['size'] ?? 0; $tmp=$_FILES['fotografia']['tmp_name'] ?? '';
        if ($err!==UPLOAD_ERR_OK) { $mensaje="Error al subir la fotografía (código $err)."; $tipo="error"; }
        elseif ($size>5*1024*1024) { $mensaje="La fotografía debe pesar máximo 5MB."; $tipo="error"; }
        else {
            $fi=new finfo(FILEINFO_MIME_TYPE); $mime=$fi->file($tmp);
            $extMap=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
            if (!isset($extMap[$mime])) { $mensaje="Formato no permitido (JPG, PNG o WEBP)."; $tipo="error"; }
            else {
                if (!is_dir($dirFs)) @mkdir($dirFs, 0775, true);
                $fotoFinal = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extMap[$mime];
                if (!move_uploaded_file($tmp, $dirFs.$fotoFinal)) { $mensaje="No se pudo guardar la fotografía."; $tipo="error"; }
                else {
                    if (!empty($actual['fotografia'])) {
                        $old=$dirFs.$actual['fotografia']; if (is_file($old)) @unlink($old);
                    }
                }
            }
        }
    }

    // Contraseña opcional
    $hash = null;
    if (!$mensaje && ($nueva_pass!=='' || $confirm_pass!=='')) {
        if ($nueva_pass==='' || $confirm_pass==='') {
            $mensaje="Completa ambos campos de contraseña."; $tipo="error";
        } elseif ($nueva_pass!==$confirm_pass) {
            $mensaje="Las contraseñas no coinciden."; $tipo="error";
        } elseif (strlen($nueva_pass)<6) {
            $mensaje="La contraseña debe tener al menos 6 caracteres."; $tipo="error";
        } else {
            $hash = password_hash($nueva_pass, PASSWORD_BCRYPT);
        }
    }

    if (!$mensaje) {
        $ok = $M->actualizar([
            'id'=>$id,'usuario'=>$usuario,'nombre_completo'=>$nombre_completo,
            'numero_empleado'=>$numero_empleado,'correo'=>$correo,'rol'=>$rol,
            'departamento_id'=>$departamento_id,'sede_id'=>$sede_id,'estado'=>$estado,
            'telefono'=>$telefono,'contrasena_hash'=>$hash,'fotografia'=>$fotoFinal
        ]);

        if ($ok) {
            // Jefe de área
            if ($rol==='jefe_area' && !empty($departamento_id) && strtolower($estado)==='activo') {
                if (!empty($actual['departamento_id']) && (int)$actual['departamento_id'] !== (int)$departamento_id) {
                    $M->limpiarResponsableSiCoincide((int)$actual['departamento_id'], (int)$id);
                }
                $M->asignarResponsableDepto((int)$departamento_id, (int)$id);
            } else {
                if (!empty($actual['departamento_id']) && $actual['rol']==='jefe_area') {
                    $M->limpiarResponsableSiCoincide((int)$actual['departamento_id'], (int)$id);
                }
            }
            // Gerente de sede
            if ($rol==='gerente' && !empty($sede_id) && strtolower($estado)==='activo') {
                if (!empty($actual['sede_id']) && (int)$actual['sede_id'] !== (int)$sede_id) {
                    $M->limpiarGerenteSiCoincide((int)$actual['sede_id'], (int)$id);
                }
                $M->setGerenteSede((int)$sede_id, (int)$id);
            } else {
                if (!empty($actual['sede_id']) && $actual['rol']==='gerente') {
                    $M->limpiarGerenteSiCoincide((int)$actual['sede_id'], (int)$id);
                }
            }

            $mensaje="Usuario actualizado correctamente."; $tipo="success";
        } else {
            $mensaje="Error al actualizar."; $tipo="error";
        }
    }

    $usuarioData = $M->obtenerPorId($id);
} else {
    $id = (int)($_GET['id'] ?? 0);
    if ($id<=0) die("ID no proporcionado o inválido.");
    $usuarioData = $M->obtenerPorId($id);
    if (!$usuarioData) die("Usuario no encontrado.");
}

/* ===== HEADER + VISTA ===== */
$titulo_pagina = "Editar Usuario";
include_once('../../shared/header.php');

$sedes = $M->sedesActivas();
$deps  = $M->departamentosActivos($usuarioData['sede_id'] ?? null);

function selected($a,$b){ return (string)$a===(string)$b ? 'selected' : ''; }
?>
<style>
.card{border-radius:1rem}
.section-title{font-weight:700;margin:8px 0 4px;display:flex;align-items:center;gap:.5rem}
.section-title .dot{width:.5rem;height:.5rem;border-radius:50%;background:#06b6d4}
.btn-primary{background:#5b21b6;border-color:#5b21b6}
.btn-primary:hover{background:#7c3aed;border-color:#7c3aed}
.text-upper{ text-transform: uppercase; }
.foto-preview{width:120px;height:120px;object-fit:cover;border-radius:.75rem;border:1px solid #e5e7eb}
.invalid-feedback{display:block}
</style>

<div class="container py-4" style="max-width:1040px">
  <div class="card shadow-sm p-4">
    <form method="POST" action="" id="formEditar" enctype="multipart/form-data" autocomplete="off" novalidate>
      <input type="hidden" name="id" id="id" value="<?= htmlspecialchars($usuarioData['id']) ?>">

      <div class="section-title"><span class="dot"></span>Datos generales</div>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Usuario *</label>
          <input type="text" name="usuario" id="usuario" class="form-control text-upper"
                 value="<?= htmlspecialchars($usuarioData['usuario']) ?>" required>
          <div id="fb-usuario" class="invalid-feedback"></div>
        </div>
        <div class="col-md-8">
          <label class="form-label">Nombre completo *</label>
          <input type="text" name="nombre_completo" id="nombre_completo" class="form-control text-upper"
                 value="<?= htmlspecialchars($usuarioData['nombre_completo']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Número de empleado *</label>
          <input type="text" name="numero_empleado" id="numero_empleado" class="form-control"
                 value="<?= htmlspecialchars($usuarioData['numero_empleado']) ?>" required>
          <div id="fb-ne" class="invalid-feedback"></div>
        </div>
        <div class="col-md-5">
          <label class="form-label">Correo</label>
          <input type="email" name="correo" id="correo" class="form-control"
                 value="<?= htmlspecialchars($usuarioData['correo'] ?? '') ?>">
          <div id="fb-correo" class="invalid-feedback"></div>
        </div>
        <div class="col-md-3">
          <label class="form-label">Teléfono</label>
          <input type="text" name="telefono" id="telefono" class="form-control"
                 value="<?= htmlspecialchars($usuarioData['telefono'] ?? '') ?>">
        </div>
      </div>

      <div class="section-title mt-3"><span class="dot"></span>Ubicación y rol</div>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Rol *</label>
          <select name="rol" id="rol" class="form-select" required>
            <option value="">— Selecciona —</option>
            <option value="admin"     <?= selected($usuarioData['rol'], 'admin') ?>>Administrador</option>
            <option value="rh"        <?= selected($usuarioData['rol'], 'rh') ?>>Recursos Humanos</option>
            <option value="jefe_area" <?= selected($usuarioData['rol'], 'jefe_area') ?>>Jefe de área</option>
            <option value="gerente"   <?= selected($usuarioData['rol'], 'gerente') ?>>Gerente</option>
          </select>
          <div id="fb-rol" class="invalid-feedback"></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Sede</label>
          <select name="sede_id" id="sede_id" class="form-select">
            <option value="">— Sin sede —</option>
            <?php foreach($sedes as $s): ?>
              <option value="<?= (int)$s['id'] ?>" <?= selected($usuarioData['sede_id'], $s['id']) ?>>
                <?= htmlspecialchars($s['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div id="fb-sede" class="invalid-feedback"></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Departamento</label>
          <select name="departamento_id" id="departamento_id" class="form-select">
            <option value="">— Sin departamento —</option>
            <?php foreach($deps as $d): ?>
              <option value="<?= (int)$d['id'] ?>" <?= selected($usuarioData['departamento_id'], $d['id']) ?>>
                <?= htmlspecialchars($d['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div id="fb-dep" class="invalid-feedback"></div>
        </div>
      </div>

      <div class="section-title mt-3"><span class="dot"></span>Estado y fotografía</div>
      <div class="row g-3 align-items-center">
        <div class="col-md-4">
          <label class="form-label">Estado *</label>
          <select name="estado" id="estado" class="form-select" required>
            <option value="activo"   <?= selected($usuarioData['estado'], 'activo') ?>>Activo</option>
            <option value="inactivo" <?= selected($usuarioData['estado'], 'inactivo') ?>>Inactivo</option>
          </select>
        </div>
        <div class="col-md-8">
          <label class="form-label d-flex justify-content-between">
            <span>Fotografía (opcional)</span>
            <small class="text-muted">JPG/PNG/WEBP · máx. 5MB</small>
          </label>
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <img id="fotoPreview" class="foto-preview"
                 src="<?= !empty($usuarioData['fotografia']) ? $dirWeb . htmlspecialchars($usuarioData['fotografia'])
                    : 'data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22120%22 height=%22120%22><rect width=%22100%25%22 height=%22100%25%22 fill=%22%23eee%22/></svg>' ?>">
            <div>
              <input type="file" name="fotografia" id="fotografia" class="form-control" accept="image/*">
              <?php if (!empty($usuarioData['fotografia'])): ?>
                <small class="text-muted">Actual: <?= htmlspecialchars($usuarioData['fotografia']) ?></small>
              <?php endif; ?>
              <div id="fb-foto" class="invalid-feedback"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="section-title mt-3"><span class="dot"></span>Seguridad</div>
      <div class="p-3 border rounded">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="togglePass">
          <label class="form-check-label" for="togglePass"><strong>Cambiar contraseña</strong></label>
        </div>
        <div id="passBox" class="row g-3 mt-1" style="display:none">
          <div class="col-md-6">
            <label class="form-label">Nueva contraseña</label>
            <input type="password" name="nueva_contrasena" id="nueva_contrasena" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Confirmar contraseña</label>
            <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" class="form-control">
          </div>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <a href="menu.php" class="btn btn-outline-secondary">← Cancelar</a>
        <a href="lista_usuario.php" class="btn btn-outline-info">📋 Lista de usuarios</a>
        <button type="submit" id="btnGuardar" class="btn btn-primary">Actualizar</button>
      </div>
    </form>
  </div>
</div>

<?php if (!empty($mensaje)): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
  icon:'<?= $tipo ?>',
  title:'<?= addslashes($mensaje) ?>',
  timer:1800,
  showConfirmButton:false
}).then(()=>{ if('<?= $tipo ?>'==='success'){ location.href='lista_usuario.php'; }});
</script>
<?php endif; ?>

<script>
document.querySelectorAll('.text-upper').forEach(el=>el.addEventListener('input',()=> el.value=el.value.toUpperCase()));

const id=document.getElementById('id').value, btn=document.getElementById('btnGuardar');
const usuario=document.getElementById('usuario'), fbUser=document.getElementById('fb-usuario');
const ne=document.getElementById('numero_empleado'), fbNe=document.getElementById('fb-ne');
const correo=document.getElementById('correo'), fbCorreo=document.getElementById('fb-correo');
const rol=document.getElementById('rol'), sedeSel=document.getElementById('sede_id'), depSel=document.getElementById('departamento_id');
const fbRol=document.getElementById('fb-rol'), fbSede=document.getElementById('fb-sede'), fbDep=document.getElementById('fb-dep');
const tPass=document.getElementById('togglePass'), passBox=document.getElementById('passBox');
const fotoInput=document.getElementById('fotografia'), fotoPrev=document.getElementById('fotoPreview'), fbFoto=document.getElementById('fb-foto');

tPass.addEventListener('change', ()=> passBox.style.display = tPass.checked ? 'flex' : 'none');

function setErr(el, fb, msg){ fb.textContent=msg||''; el?.classList?.toggle('is-invalid', !!msg); btn.disabled=!!document.querySelector('.is-invalid'); }
async function jget(params){ const url=new URL(location.href); Object.entries(params).forEach(([k,v])=>url.searchParams.set(k,v)); const r=await fetch(url.toString(), {headers:{'X-Requested-With':'fetch'}}); return r.json(); }

async function vUsuario(){ const v=usuario.value.trim().toUpperCase(); if(!v){fbUser.textContent='';return;} const r=await jget({ajax:'validar_usuario', id, usuario:v}); setErr(usuario, fbUser, r.ok?'':'Usuario ya existe'); }
async function vNe(){ const v=ne.value.trim(); if(!v){fbNe.textContent='';return;} const r=await jget({ajax:'validar_ne', id, ne:v}); setErr(ne, fbNe, r.ok?'':'Número de empleado duplicado'); }
function vCorreo(){ const v=correo.value.trim(); if(!v) return setErr(correo, fbCorreo, ''); const ok=/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); setErr(correo, fbCorreo, ok?'':'Correo inválido'); }

async function vRolGerJefe(e){
  const rRol=rol.value, sede=sedeSel.value, dep=depSel.value;

  if (e && e.type==='change' && e.target===sedeSel){
    const res=await jget({ajax:'departamentos_por_sede', sede_id:sede});
    const cur=depSel.value; depSel.innerHTML='<option value="">— Sin departamento —</option>';
    (res.items||[]).forEach(d=>{ const o=document.createElement('option'); o.value=d.id; o.textContent=d.nombre; depSel.appendChild(o); });
    if ([...depSel.options].some(o=>o.value===cur)) depSel.value=cur;
  }

  fbRol.textContent=fbSede.textContent=fbDep.textContent='';
  rol.classList.remove('is-invalid'); sedeSel.classList.remove('is-invalid'); depSel.classList.remove('is-invalid');

  if (rRol==='gerente'){
    if(!sede){ return setErr(sedeSel, fbSede, 'Selecciona una sede'); }
    const r=await jget({ajax:'validar_gerente', id, sede_id:sede}); if(!r.ok) return setErr(sedeSel, fbSede, 'Ya hay un GERENTE en esa sede');
  }
  if (rRol==='jefe_area'){
    if(!sede){ return setErr(sedeSel, fbSede, 'Selecciona una sede'); }
    if(!dep){ return setErr(depSel, fbDep, 'Selecciona un departamento'); }
    const r=await jget({ajax:'validar_jefe', id, sede_id:sede, departamento_id:dep}); if(!r.ok) return setErr(depSel, fbDep, 'Ya hay un JEFE DE ÁREA ahí');
  }
  btn.disabled=!!document.querySelector('.is-invalid');
}

usuario.addEventListener('blur', vUsuario);
ne.addEventListener('blur', vNe);
correo.addEventListener('input', vCorreo);
rol.addEventListener('change', vRolGerJefe);
sedeSel.addEventListener('change', vRolGerJefe);
depSel.addEventListener('change', vRolGerJefe);

fotoInput.addEventListener('change', ()=>{ const f=fotoInput.files && fotoInput.files[0]; fbFoto.textContent=''; if(!f) return;
  if(!['image/jpeg','image/png','image/webp'].includes(f.type)) return setErr(fotoInput, fbFoto, 'Formato no permitido');
  if(f.size>5*1024*1024) return setErr(fotoInput, fbFoto, 'Máximo 5MB');
  const rd=new FileReader(); rd.onload=e=>fotoPrev.src=e.target.result; rd.readAsDataURL(f);
});

// Disparo inicial
vRolGerJefe();
</script>
