<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/conexion.php';

if (!defined('BASE_PATH')) {
  // Ajusta si se cuelga de otra carpeta
  define('BASE_PATH', '/sistema_rh');
}

class AuthController {
  private PDO $db;
  private array $cfg;

  public function __construct() {
    $this->db  = Conexion::getConexion();
    // Config opcional; si no existe app.php, usar defaults seguros
    $this->cfg = is_file(__DIR__.'/../../config/app.php')
      ? (require __DIR__.'/../../config/app.php')
      : [
          'APP_KEY'           => bin2hex(random_bytes(16)),
          'REMEMBER_DAYS'     => 7,
          'LOGIN_MAX_ATTEMPTS'=> 5,
          'AUTO_UNLOCK_MIN'   => 15,
        ];
    if (session_status() === PHP_SESSION_NONE) session_start();
  }

  /* ---------- Helpers internos ---------- */

  private function notificarAdmins(string $mensaje, ?string $link=null): void {
    // Silencioso para activar  la tabla 'notificaciones'
    try {
      $q  = $this->db->query("SELECT id FROM usuarios WHERE rol='admin' AND estado='activo'");
      $admins = $q->fetchAll(PDO::FETCH_ASSOC);
      if (!$admins) return;

      $st = $this->db->prepare(
        "INSERT INTO notificaciones (usuario_id, mensaje, link, leida, expira_en)
         VALUES (?,?,?,?,?)"
      );
      $expira = (new DateTime('+15 days'))->format('Y-m-d H:i:s'); // regla campana 15 días
      foreach ($admins as $a) {
        $st->execute([$a['id'], $mensaje, $link, 0, $expira]);
      }
    } catch (\Throwable $e) {
      /* no romper login si no hay módulo de notificaciones aún */
    }
  }

  private function setSession(array $u, bool $remember): void {
    // Llaves de sesión compatibles con las vistas y módulos
    $_SESSION['id']              = (int)$u['id'];                        // las vistas usan 'id'
    $_SESSION['uid']             = (int)$u['id'];                        // compat si en algún lugar usas 'uid'
    $_SESSION['usuario']         = $u['usuario'] ?? ($u['numero_empleado'] ?? '');
    $_SESSION['rol']             = strtolower($u['rol'] ?? '');          // admin|rh|gerente|jefe_area
    $_SESSION['sede_id']         = isset($u['sede_id']) ? (int)$u['sede_id'] : null;
    $_SESSION['departamento_id'] = isset($u['departamento_id']) ? (int)$u['departamento_id'] : null; // vistas leen 'departamento_id'
    $_SESSION['depto_id']        = $_SESSION['departamento_id'];         // compat antiguo
    $_SESSION['nombre_completo'] = $u['nombre_completo'] ?? '';
    $_SESSION['nombre']          = $_SESSION['nombre_completo'];         // compat con código existente
    $_SESSION['last_act']        = time(); // inactividad

    if ($remember) {
      $key   = (string)($this->cfg['APP_KEY'] ?? '');
      $token = $u['id'].'|'.hash_hmac('sha256', (string)$u['id'], $key);
      $days  = (int)($this->cfg['REMEMBER_DAYS'] ?? 7);
      setcookie('remember_me', $token, time()+($days*86400), BASE_PATH ?: '/', '', false, true);
    }
  }

  private function clearSession(): void {
    setcookie('remember_me', '', time()-3600, BASE_PATH ?: '/');
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
      session_unset();
      session_destroy();
    }
  }

  private function goHomeByRole(string $rol): void {
    switch ($rol) {
      case 'admin':     header('Location: ' . BASE_PATH . '/public/admin.php'); break;
      case 'rh':        header('Location: ' . BASE_PATH . '/public/rh.php'); break;
      case 'gerente':   header('Location: ' . BASE_PATH . '/public/gerente.php'); break;
      case 'jefe_area': header('Location: ' . BASE_PATH . '/public/jefe_area.php'); break;
      default:          header('Location: ' . BASE_PATH . '/public/login.php'); break;
    }
    exit;
  }

  /* ---------- Vistas ---------- */

  public function showLogin(?string $error=null): void {
    // Pasa $error a la vista si se usas (echo en alert)
    $login_error = $error; // alias común
    include __DIR__ . '/../views/auth/login.php';
  }

  /* ---------- Acciones ---------- */

  public function login(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();

    // Si no es POST, muestra login
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $this->showLogin(); return;
    }

    $usuario  = trim($_POST['usuario'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $remember = !empty($_POST['remember']);
    $generic  = 'Usuario o contraseña incorrectos.';

    if ($usuario === '' || $password === '') {
      $this->showLogin('Ingresa tu usuario y contraseña.'); return;
    }

    // 1) Traer usuario por usuario O número de empleado
    $st = $this->db->prepare("
      SELECT id, usuario, contrasena, rol, nombre_completo, numero_empleado,
             correo, telefono, estado, fotografia, sede_id, departamento_id,
             failed_attempts, locked_until, last_login, last_failed_at
      FROM usuarios
      WHERE (usuario = :u OR numero_empleado = :u)
      LIMIT 1
    ");
    $st->execute([':u' => $usuario]);
    $u = $st->fetch(PDO::FETCH_ASSOC);

    if (!$u) { $this->showLogin($generic); return; }

    // 2) Estado debe ser 'activo'
    if (mb_strtolower((string)$u['estado']) !== 'activo') {
      $this->showLogin('Usuario inactivo. Contacta al administrador.'); return;
    }

    // 3) ¿Bloqueo temporal vigente?
    if (!empty($u['locked_until'])) {
      $ts = strtotime((string)$u['locked_until']);
      if ($ts && $ts > time()) {
        $mins = ceil(($ts - time()) / 60);
        $this->showLogin("Tu cuenta está bloqueada. Intenta en ~{$mins} min o contacta al Administrador."); return;
      } else {
        // limpiar bloqueo expirado
        $this->db->prepare("UPDATE usuarios SET failed_attempts=0, locked_until=NULL WHERE id=?")
                 ->execute([$u['id']]);
        $u['failed_attempts'] = 0;
        $u['locked_until']    = null;
      }
    }

    // 4) Campo de contraseña en tu BD: 'contrasena'
    if (empty($u['contrasena'])) {
      $this->showLogin('Configuración de contraseña no encontrada.'); return;
    }

    // 5) Verificar contraseña
    if (!password_verify($password, (string)$u['contrasena'])) {
      $fails = (int)($u['failed_attempts'] ?? 0) + 1;

      // Política de seguridad (configurable)
      $MAX_ATTEMPTS = (int)($this->cfg['LOGIN_MAX_ATTEMPTS'] ?? 5);  // intentos antes de bloquear
      $LOCK_MINUTES = (int)($this->cfg['AUTO_UNLOCK_MIN']  ?? 15);   // minutos de bloqueo

      if ($fails >= $MAX_ATTEMPTS) {
        $lock = (new DateTime("+{$LOCK_MINUTES} minutes"))->format('Y-m-d H:i:s');
        $this->db->prepare("UPDATE usuarios SET failed_attempts=?, locked_until=?, last_failed_at=NOW() WHERE id=?")
                 ->execute([$fails, $lock, $u['id']]);
        $this->notificarAdmins("Usuario {$u['usuario']} fue bloqueado por intentos fallidos.", BASE_PATH . '/public/login.php');
        $this->showLogin("Demasiados intentos. Tu cuenta fue bloqueada por {$LOCK_MINUTES} minutos.");
        return;
      } else {
        $this->db->prepare("UPDATE usuarios SET failed_attempts=?, last_failed_at=NOW() WHERE id=?")
                 ->execute([$fails, $u['id']]);
        // Throttling suave opcional (si hubo fallo reciente)
        if (!empty($u['last_failed_at'])) {
          $diff = time() - strtotime((string)$u['last_failed_at']);
          if ($diff < 120) { usleep(250000); } // 0.25s
        }
        $this->showLogin($generic); return;
      }
    }

    // 6) Éxito → resetear contador/bloqueo y marcar last_login
    $this->db->prepare("UPDATE usuarios SET failed_attempts=0, locked_until=NULL, last_login=NOW() WHERE id=?")
             ->execute([$u['id']]);

    // 7) Crear sesión con llaves COMPATIBLES con las vistas
    session_regenerate_id(true);
    $this->setSession($u, $remember);

    // 8) Redirigir por rol a las rutas existentes
    $this->goHomeByRole(strtolower($u['rol'] ?? ''));
  }

  public function logout(): void {
    $this->clearSession();
    header('Location: ' . BASE_PATH . '/public/login.php'); exit;
  }
}
