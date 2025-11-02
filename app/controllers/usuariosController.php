<?php
// app/Controllers/UsuariosController.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__. '/../../config/conexion.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Models/Departamento.php';

class UsuariosController {
  private PDO $db;
  private Usuario $usuarioModel;
  private Departamento $departamentoModel;

  public function __construct(){
    $this->db = Conexion::getConexion(); // este es  el  método actual
    // Si los modelos o el models  NO reciben "PDO" en el constructor, cambia a: new Usuario(); new Departamento();
    $this->usuarioModel = new Usuario($this->db);
    $this->departamentoModel = new Departamento($this->db);
  }

  /* ====== Vistas ====== */
  public function create(){
    require _DIR_ . '/../../views/admin/usuarios/crear_usuario.php';
  }

  public function index(){
    require _DIR_ . '/../../views/admin/usuarios/lista_usuario.php';
  }

  /* ====== AJAX (validaciones y catálogos) ====== */
  public function ajax(){
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['action'] ?? '';

    try {
      switch ($action) {
        case 'verificar_usuario': {
          $usuario = $_GET['usuario'] ?? '';
          echo json_encode(['existe' => $this->usuarioModel->existeUsuario($usuario)]);
          break;
        }
        case 'verificar_num_empleado': {
          $num = $_GET['numero'] ?? '';
          echo json_encode(['existe' => $this->usuarioModel->existeNumeroEmpleado($num)]);
          break;
        }
        case 'verificar_jefe': {
          $sede = (int)($_GET['sede_id'] ?? 0);
          $dep  = (int)($_GET['departamento_id'] ?? 0);
          $existe = $this->usuarioModel->existeJefeEnDepartamento($sede, $dep); // usa siempre este nombre
          echo json_encode(['existe' => $existe]);
          break;
        }
        case 'verificar_gerente': {
          $sede = (int)($_GET['sede_id'] ?? 0);
          echo json_encode(['existe' => $this->usuarioModel->existeGerenteEnSede($sede)]);
          break;
        }
        case 'departamentos_por_sede': {
          $id = (int)($_GET['id'] ?? 0);
          $departamentos = $this->departamentoModel->obtenerPorSede($id);
          echo json_encode($departamentos);
          break;
        }
        default:
          http_response_code(400);
          echo json_encode(['ok'=>false,'error'=>'Acción no válida']);
      }
    } catch (Throwable $e) {
      http_response_code(500);
      echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
    }
    exit;
  }

  /* ====== Guardar ====== */
  public function store(){
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      header('Location: ' . BASE_URL . '/admin/usuarios/crear'); exit;
    }

    // --- Sanitización/normalización
    $data = $_POST;
    $data['nombre_completo']  = mb_strtoupper(trim($data['nombre_completo'] ?? ''), 'UTF-8');
    $data['usuario']          = mb_strtoupper(trim($data['usuario'] ?? ''), 'UTF-8');
    $data['numero_empleado']  = mb_strtoupper(trim($data['numero_empleado'] ?? ''), 'UTF-8');
    $data['rol']              = $data['rol'] ?? '';
    $data['sede_id']          = !empty($data['sede_id']) ? (int)$data['sede_id'] : null;
    $data['departamento_id']  = !empty($data['departamento_id']) ? (int)$data['departamento_id'] : null;
    $data['estado']           = $data['estado'] ?? 'activo';
    $pass                     = (string)($data['contrasena'] ?? '');
    $confirm                  = (string)($data['confirmar'] ?? '');

    // --- Validaciones mínimas
    if ($data['usuario']==='' || $pass==='' || $confirm==='' || $data['rol']==='' || $data['nombre_completo']==='' || $data['numero_empleado']==='') {
      $_SESSION['error_guardado'] = 'Completa los campos obligatorios.';
      header('Location: ' . BASE_URL . '/admin/usuarios/crear'); exit;
    }
    if (strlen($pass) < 6 || $pass !== $confirm) {
      $_SESSION['error_guardado'] = 'La contraseña no coincide o es demasiado corta (mínimo 6).';
      header('Location: ' . BASE_URL . '/admin/usuarios/crear'); exit;
    }

    // --- Reglas de negocio
    if ($this->usuarioModel->existeUsuario($data['usuario'])) {
      $_SESSION['error_guardado'] = 'El nombre de usuario ya existe.';
      header('Location: ' . BASE_URL . '/admin/usuarios/crear'); exit;
    }
    if ($this->usuarioModel->existeNumeroEmpleado($data['numero_empleado'])) {
      $_SESSION['error_guardado'] = 'El número de empleado ya está registrado.';
      header('Location: ' . BASE_URL . '/admin/usuarios/crear'); exit;
    }
    if ($data['rol'] === 'jefe_area') {
      if (empty($data['sede_id']) || empty($data['departamento_id'])) {
        $_SESSION['error_guardado'] = 'Selecciona sede y departamento para jefe de área.';
        header('Location: ' . BASE_URL . '/admin/usuarios/crear'); exit;
      }
      if ($this->usuarioModel->existeJefeEnDepartamento($data['sede_id'], $data['departamento_id'])) {
        $_SESSION['error_guardado'] = 'Ya existe un jefe de área activo en ese departamento/sede.';
        header('Location: ' . BASE_URL . '/admin/usuarios/crear'); exit;
      }
    }
    if ($data['rol'] === 'gerente') {
      if (empty($data['sede_id'])) {
        $_SESSION['error_guardado'] = 'Selecciona la sede para el gerente.';
        header('Location: ' . BASE_URL . '/admin/usuarios/crear'); exit;
      }
      if ($this->usuarioModel->existeGerenteEnSede($data['sede_id'])) {
        $_SESSION['error_guardado'] = 'Ya existe un gerente asignado a esa sede.';
        header('Location: ' . BASE_URL . '/admin/usuarios/crear'); exit;
      }
    }

    // --- Foto (opcional) con validación de MIME
    $data['fotografia'] = null;
    if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] !== UPLOAD_ERR_NO_FILE) {
      if ($_FILES['fotografia']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error_guardado'] = 'Error al subir la fotografía.';
        header('Location: ' . BASE_URL . '/admin/usuarios/crear'); exit;
      }
      $fi   = new finfo(FILEINFO_MIME_TYPE);
      $mime = $fi->file($_FILES['fotografia']['tmp_name']);
      $extMap = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
      if (!isset($extMap[$mime])) {
        $_SESSION['error_guardado'] = 'Formato de foto no válido (solo jpg, png, webp).';
        header('Location: ' . BASE_URL . '/admin/usuarios/crear'); exit;
      }
      if (($_FILES['fotografia']['size'] ?? 0) > 5*1024*1024) {
        $_SESSION['error_guardado'] = 'La foto supera el tamaño máximo (5MB).';
        header('Location: ' . BASE_URL . '/admin/usuarios/crear'); exit;
      }

      $nombreFoto = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $extMap[$mime];

      // Guardar en public/assets/img/usuarios/
      $dirFs  = dirname(_DIR_, 3) . '/public/assets/img/usuarios/';
      if (!is_dir($dirFs)) @mkdir($dirFs, 0775, true);
      if (!move_uploaded_file($_FILES['fotografia']['tmp_name'], $dirFs.$nombreFoto)) {
        $_SESSION['error_guardado'] = 'No se pudo guardar la fotografía en el servidor.';
        header('Location: ' . BASE_URL . '/admin/usuarios/crear'); exit;
      }
      $data['fotografia'] = $nombreFoto;
    }

    // --- Hash de contraseña
    $data['contrasena'] = password_hash($pass, PASSWORD_DEFAULT);

    // --- Guardar
    $ok = $this->usuarioModel->crear($data);
    if ($ok) {
      $_SESSION['usuario_guardado'] = '✅ Usuario creado exitosamente.';
      header('Location: ' . BASE_URL . '/admin/usuarios'); // lista
    } else {
      $_SESSION['error_guardado'] = '❌ Error al guardar el usuario.';
      header('Location: ' . BASE_URL . '/admin/usuarios/crear');
    }
    exit;
  }
}