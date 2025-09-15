<?php
if (!isset($_SESSION)) session_start();
require_once dirname(__DIR__, 4) . '/config/conexion.php';

class UsuarioCrea {
    private PDO $db;
    public function __construct(){ $this->db = Conexion::getConexion(); }

    /* ===== Validaciones ===== */
    public function existeUsuario(string $u): bool {
        $st = $this->db->prepare("SELECT 1 FROM usuarios WHERE UPPER(usuario)=UPPER(:u) LIMIT 1");
        $st->execute([':u'=>$u]); return (bool)$st->fetchColumn();
    }
    public function existeNumEmpleado(string $ne): bool {
        $st = $this->db->prepare("SELECT 1 FROM usuarios WHERE numero_empleado=:ne LIMIT 1");
        $st->execute([':ne'=>$ne]); return (bool)$st->fetchColumn();
    }
    public function existeGerenteEnSede(?int $sedeId): bool {
        if (empty($sedeId)) return false;
        $st = $this->db->prepare("SELECT 1 FROM usuarios
                                   WHERE rol='gerente' AND sede_id=:s AND LOWER(estado)='activo' LIMIT 1");
        $st->execute([':s'=>$sedeId]); return (bool)$st->fetchColumn();
    }
    public function existeJefeEnDeptoSede(?int $sedeId, ?int $depId): bool {
        if (empty($sedeId) || empty($depId)) return false;
        $st = $this->db->prepare("SELECT 1 FROM usuarios
                                   WHERE rol='jefe_area' AND sede_id=:s AND departamento_id=:d
                                     AND LOWER(estado)='activo' LIMIT 1");
        $st->execute([':s'=>$sedeId, ':d'=>$depId]); return (bool)$st->fetchColumn();
    }

    /* ===== Catálogos ===== */
    public function sedesActivas(): array {
        return $this->db->query("SELECT id, nombre FROM sedes WHERE activo=1 ORDER BY nombre")->fetchAll();
    }
    public function departamentosActivos(?int $sedeId=null): array {
        if ($sedeId) {
            $st = $this->db->prepare("SELECT id, nombre FROM departamentos
                                       WHERE LOWER(estado)='activo' AND sede_id=:s ORDER BY nombre");
            $st->execute([':s'=>$sedeId]); return $st->fetchAll();
        }
        return $this->db->query("SELECT id, nombre FROM departamentos
                                  WHERE LOWER(estado)='activo' ORDER BY nombre")->fetchAll();
    }

    /* ===== Crear (transacción) ===== */
    public function crear(array $d): int {
        $this->db->beginTransaction();
        try {
            $st = $this->db->prepare("INSERT INTO usuarios
                    (usuario, contrasena, rol, nombre_completo, numero_empleado, correo, telefono,
                     sede_id, departamento_id, estado, fotografia, fecha_registro)
                VALUES (:usuario,:pass,:rol,:nombre,:ne,:correo,:tel,:sede,:dep,:estado,:foto,NOW())");
            $st->execute([
                ':usuario'=>$d['usuario'], ':pass'=>$d['contrasena_hash'], ':rol'=>$d['rol'],
                ':nombre'=>$d['nombre_completo'], ':ne'=>$d['numero_empleado'], ':correo'=>$d['correo'] ?: null,
                ':tel'=>$d['telefono'] ?: null, ':sede'=>$d['sede_id'] ?: null, ':dep'=>$d['departamento_id'] ?: null,
                ':estado'=>$d['estado'], ':foto'=>$d['fotografia'] ?: null
            ]);
            $id = (int)$this->db->lastInsertId();

            // Enlaces (si ACTIVO)
            if (strtolower($d['estado'])==='activo') {
                if ($d['rol']==='jefe_area' && !empty($d['departamento_id'])) {
                    $up = $this->db->prepare("UPDATE departamentos SET responsable_id=:uid WHERE id=:dep");
                    $up->execute([':uid'=>$id, ':dep'=>$d['departamento_id']]);
                }
                if ($d['rol']==='gerente' && !empty($d['sede_id'])) {
                    $up2 = $this->db->prepare("UPDATE sedes SET gerente_id=:uid WHERE id=:sede");
                    $up2->execute([':uid'=>$id, ':sede'=>$d['sede_id']]);
                }
            }

            $this->db->commit();
            return $id;
        } catch(Throwable $e){
            $this->db->rollBack(); throw $e;
        }
    }
}

$M = new UsuarioCrea();

/* ===== AJAX ===== */
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json; charset=utf-8');
    switch ($_GET['ajax']) {
        case 'deps_por_sede':
            $s = !empty($_GET['sede_id']) ? (int)$_GET['sede_id'] : null;
            echo json_encode(['ok'=>true,'items'=>$M->departamentosActivos($s)]); break;
        case 'val_usuario':
            $u = strtoupper(trim($_GET['u'] ?? ''));
            echo json_encode(['ok'=>!$M->existeUsuario($u)]); break;
        case 'val_ne':
            $ne = trim($_GET['ne'] ?? '');
            echo json_encode(['ok'=>!$M->existeNumEmpleado($ne)]); break;
        case 'val_gerente':
            $s = !empty($_GET['sede_id']) ? (int)$_GET['sede_id'] : null;
            echo json_encode(['ok'=>!$M->existeGerenteEnSede($s)]); break;
        case 'val_jefe':
            $s = !empty($_GET['sede_id']) ? (int)$_GET['sede_id'] : null;
            $d = !empty($_GET['departamento_id']) ? (int)$_GET['departamento_id'] : null;
            echo json_encode(['ok'=>!$M->existeJefeEnDeptoSede($s,$d)]); break;
        default: echo json_encode(['ok'=>false]);
    }
    exit;
}

/* ===== POST ===== */
$msg=""; $type="";
$root = dirname(__DIR__, 4);
$dirFs = $root.'/public/img/usuarios/';
$dirWeb = '../../../../public/img/usuarios/';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $usuario = strtoupper(trim($_POST['usuario'] ?? ''));
    $pass    = trim($_POST['contrasena'] ?? '');
    $pass2   = trim($_POST['confirmar'] ?? '');
    $rol     = $_POST['rol'] ?? '';
    $nombre  = strtoupper(trim($_POST['nombre_completo'] ?? ''));
    $ne      = trim($_POST['numero_empleado'] ?? '');
    $correo  = trim($_POST['correo'] ?? '');
    $tel     = trim($_POST['telefono'] ?? '');
    $sede    = !empty($_POST['sede_id']) ? (int)$_POST['sede_id'] : null;
    $dep     = !empty($_POST['departamento_id']) ? (int)$_POST['departamento_id'] : null;
    $estado  = $_POST['estado'] ?? 'activo';

    if ($usuario==='' || $pass==='' || $pass2==='' || $rol==='' || $nombre==='' || $ne==='') {
        $msg="Completa los campos obligatorios."; $type="warning";
    } elseif ($pass!==$pass2 || strlen($pass)<6) {
        $msg="La contraseña no coincide o es muy corta (mínimo 6)."; $type="error";
    } elseif (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $msg="Correo inválido."; $type="error";
    } elseif ($M->existeUsuario($usuario)) {
        $msg="El usuario ya existe."; $type="error";
    } elseif ($M->existeNumEmpleado($ne)) {
        $msg="El número de empleado ya existe."; $type="error";
    } elseif ($rol==='gerente' && empty($sede)) {
        $msg="Selecciona una sede para el gerente."; $type="error";
    } elseif ($rol==='gerente' && $M->existeGerenteEnSede($sede)) {
        $msg="Ya hay un gerente activo en esa sede."; $type="error";
    } elseif ($rol==='jefe_area' && (empty($sede) || empty($dep))) {
        $msg="Selecciona sede y departamento para el jefe de área."; $type="error";
    } elseif ($rol==='jefe_area' && $M->existeJefeEnDeptoSede($sede,$dep)) {
        $msg="Ya hay un jefe de área activo en esa sede/departamento."; $type="error";
    } else {
        // Foto (opcional)
        $foto = null;
        if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error']!==UPLOAD_ERR_NO_FILE) {
            $err  = $_FILES['fotografia']['error'];
            $size = $_FILES['fotografia']['size'] ?? 0;
            $tmp  = $_FILES['fotografia']['tmp_name'] ?? '';
            if ($err!==UPLOAD_ERR_OK) { $msg="Error subiendo la foto."; $type="error"; }
            elseif ($size>5*1024*1024) { $msg="Foto máxima 5MB."; $type="error"; }
            else {
                $fi = new finfo(FILEINFO_MIME_TYPE); $mime = $fi->file($tmp);
                $extMap = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
                if (!isset($extMap[$mime])) { $msg="Formato de foto no válido."; $type="error"; }
                else {
                    if (!is_dir($dirFs)) @mkdir($dirFs, 0775, true);
                    $nombreFoto = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extMap[$mime];
                    if (move_uploaded_file($tmp, $dirFs.$nombreFoto)) $foto = $nombreFoto;
                    else { $msg="No se pudo guardar la foto."; $type="error"; }
                }
            }
        }

        if ($msg==='') {
            try {
                $id = $M->crear([
                    'usuario'=>$usuario,
                    'contrasena_hash'=>password_hash($pass, PASSWORD_BCRYPT),
                    'rol'=>$rol,
                    'nombre_completo'=>$nombre,
                    'numero_empleado'=>$ne,
                    'correo'=>$correo,
                    'telefono'=>$tel,
                    'sede_id'=>$sede,
                    'departamento_id'=>$dep,
                    'estado'=>$estado,
                    'fotografia'=>$foto
                ]);
                $msg="Usuario creado correctamente (ID $id)."; $type="success";
            } catch (Throwable $e) {
                $msg="Error al crear: ".$e->getMessage(); $type="error";
            }
        }
    }
}

/* ===== HEADER + VISTA ===== */
$titulo_pagina = "Registrar Usuario";
include_once('../../shared/header.php');

/* Catálogos para selects */
$sedes = $M->sedesActivas();
$deps  = isset($sede) ? $M->departamentosActivos($sede) : $M->departamentosActivos();
?>
<style>
/* ====== Estilo del formulario que te gustó ====== */
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
    <div class="section-title"><span class="dot"></span>Datos generales</div>
    <form method="POST" enctype="multipart/form-data" id="formCrear" autocomplete="off" novalidate>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Usuario *</label>
          <input name="usuario" id="usuario" class="form-control text-upper" required>
          <div class="invalid-feedback" id="fb-user"></div>
        </div>
        <div class="col-md-4">
          <label class="form-label">Contraseña *</label>
          <input type="password" name="contrasena" id="contrasena" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Confirmar *</label>
          <input type="password" name="confirmar" id="confirmar" class="form-control" required>
        </div>

        <div class="col-md-8">
          <label class="form-label">Nombre completo *</label>
          <input name="nombre_completo" class="form-control text-upper" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Número de empleado *</label>
          <input name="numero_empleado" id="ne" class="form-control" required>
          <div class="invalid-feedback" id="fb-ne"></div>
        </div>

        <div class="col-md-5">
          <label class="form-label">Correo</label>
          <input name="correo" id="correo" type="email" class="form-control">
          <div class="invalid-feedback" id="fb-correo"></div>
        </div>
        <div class="col-md-3">
          <label class="form-label">Teléfono</label>
          <input name="telefono" class="form-control">
        </div>
      </div>

      <div class="section-title mt-3"><span class="dot"></span>Ubicación y rol</div>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Rol *</label>
          <select name="rol" id="rol" class="form-select" required>
            <option value="">— Selecciona —</option>
            <option value="admin">Administrador</option>
            <option value="rh">Recursos Humanos</option>
            <option value="jefe_area">Jefe de área</option>
            <option value="gerente">Gerente</option>
          </select>
          <div class="invalid-feedback" id="fb-rol"></div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Sede</label>
          <select name="sede_id" id="sede" class="form-select">
            <option value="">— Sin sede —</option>
            <?php foreach($sedes as $s): ?>
              <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="invalid-feedback" id="fb-sede"></div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Departamento</label>
          <select name="departamento_id" id="dep" class="form-select">
            <option value="">— Sin departamento —</option>
            <?php foreach($deps as $d): ?>
              <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="invalid-feedback" id="fb-dep"></div>
        </div>
      </div>

      <div class="section-title mt-3"><span class="dot"></span>Estado y fotografía</div>
      <div class="row g-3 align-items-center">
        <div class="col-md-4">
          <label class="form-label">Estado *</label>
          <select name="estado" class="form-select" required>
            <option value="activo" selected>Activo</option>
            <option value="inactivo">Inactivo</option>
          </select>
        </div>
        <div class="col-md-8">
          <label class="form-label d-flex justify-content-between">
            <span>Fotografía (opcional)</span>
            <small class="text-muted">JPG/PNG/WEBP · máx. 5MB</small>
          </label>
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <img id="fotoPreview" class="foto-preview"
                 src="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22120%22 height=%22120%22><rect width=%22100%25%22 height=%22100%25%22 fill=%22%23eee%22/></svg>">
            <input type="file" name="fotografia" id="foto" class="form-control" accept="image/*">
            <div class="invalid-feedback" id="fb-foto"></div>
          </div>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <a href="menu.php" class="btn btn-outline-secondary">← Cancelar</a>
        <a href="lista_usuario.php" class="btn btn-outline-info">📋 Lista de usuarios</a>
        <button class="btn btn-primary" id="btnGuardar">Crear</button>
      </div>
    </form>
  </div>
</div>

<?php if(!empty($msg)): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
  icon:'<?= $type ?>',
  title:'<?= addslashes($msg) ?>',
  timer: 1800,
  showConfirmButton:false
}).then(()=>{ if('<?= $type ?>'==='success'){ location.href='lista_usuario.php'; }});
</script>
<?php endif; ?>

<script>
const btn = document.getElementById('btnGuardar');
const usuario = document.getElementById('usuario');
const fbUser  = document.getElementById('fb-user');
const ne      = document.getElementById('ne');
const fbNe    = document.getElementById('fb-ne');
const correo  = document.getElementById('correo');
const fbCorreo= document.getElementById('fb-correo');
const rol = document.getElementById('rol');
const sede= document.getElementById('sede');
const dep = document.getElementById('dep');
const fbRol = document.getElementById('fb-rol');
const fbSede= document.getElementById('fb-sede');
const fbDep = document.getElementById('fb-dep');
const foto = document.getElementById('foto');
const fotoPreview = document.getElementById('fotoPreview');

document.querySelectorAll('.text-upper').forEach(el=>el.addEventListener('input',()=>el.value=el.value.toUpperCase()));

function setErr(el, fb, msg){ fb.textContent = msg||''; el?.classList?.toggle('is-invalid', !!msg); btn.disabled = !!document.querySelector('.is-invalid'); }
async function jget(params){
  const url = new URL(location.href);
  Object.entries(params).forEach(([k,v])=>url.searchParams.set(k,v));
  const r = await fetch(url, {headers:{'X-Requested-With':'fetch'}});
  return r.json();
}

usuario.addEventListener('blur', async ()=>{
  const v = usuario.value.trim().toUpperCase();
  if(!v){ fbUser.textContent=''; return; }
  const r = await jget({ajax:'val_usuario', u:v});
  setErr(usuario, fbUser, r.ok ? '' : 'Usuario ya existe');
});
ne.addEventListener('blur', async ()=>{
  const v = ne.value.trim();
  if(!v){ fbNe.textContent=''; return; }
  const r = await jget({ajax:'val_ne', ne:v});
  setErr(ne, fbNe, r.ok ? '' : 'Número de empleado duplicado');
});
correo.addEventListener('input', ()=>{
  const v = correo.value.trim();
  if(!v) return setErr(correo, fbCorreo, '');
  const ok=/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
  setErr(correo, fbCorreo, ok ? '' : 'Correo inválido');
});

async function validarRol(){
  fbRol.textContent = fbSede.textContent = fbDep.textContent = '';
  rol.classList.remove('is-invalid'); sede.classList.remove('is-invalid'); dep.classList.remove('is-invalid');

  if (rol.value==='gerente'){
    if(!sede.value){ setErr(sede, fbSede, 'Selecciona sede'); return; }
    const r = await jget({ajax:'val_gerente', sede_id:sede.value});
    if(!r.ok){ setErr(sede, fbSede, 'Ya hay un gerente activo en esa sede'); return; }
  }
  if (rol.value==='jefe_area'){
    if(!sede.value){ setErr(sede, fbSede, 'Selecciona sede'); return; }
    if(!dep.value){ setErr(dep, fbDep, 'Selecciona departamento'); return; }
    const r = await jget({ajax:'val_jefe', sede_id:sede.value, departamento_id:dep.value});
    if(!r.ok){ setErr(dep, fbDep, 'Ya hay un jefe de área activo ahí'); return; }
  }
  btn.disabled = !!document.querySelector('.is-invalid');
}
rol.addEventListener('change', validarRol);
sede.addEventListener('change', async ()=>{
  const s = sede.value;
  const r = await jget({ajax:'deps_por_sede', sede_id:s});
  dep.innerHTML = '<option value="">— Sin departamento —</option>';
  (r.items||[]).forEach(d=>{
    const o=document.createElement('option'); o.value=d.id; o.textContent=d.nombre; dep.appendChild(o);
  });
  validarRol();
});
dep.addEventListener('change', validarRol);

foto.addEventListener('change', ()=>{
  const f = foto.files && foto.files[0];
  if(!f) return;
  if(!['image/jpeg','image/png','image/webp'].includes(f.type)) return alert('Formato no válido');
  if(f.size>5*1024*1024) return alert('Foto máxima 5MB');
  const rd=new FileReader(); rd.onload=e=>fotoPreview.src=e.target.result; rd.readAsDataURL(f);
});
</script>
