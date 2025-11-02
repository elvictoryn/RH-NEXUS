<?php
// ======= Header compartido Nexus RH (inyecta NAVBAR en todo el sitio) =======
if (!defined('BASE_PATH')) {
  define('BASE_PATH', '/sistema_rh'); // <-- ajusta si tu carpeta cambia
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Conexión a BD
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/config/conexion.php';

// Assets globales (logo y fondo)
if (!defined('LOGO_URL')) define('LOGO_URL', BASE_PATH . '/public/img/logo.png');
if (!defined('BG_URL'))   define('BG_URL',   BASE_PATH . '/public/img/bg.jpg');

// Título por defecto
$tituloPagina = $tituloPagina ?? 'Nexus RH';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo htmlspecialchars($tituloPagina); ?></title>

  <!-- Bootstrap 5 + Icons (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <!-- Estilos globales -->
  <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/public/css/estilos.css" />

  <!-- Inyector del fondo global (toma BG_URL) -->
  <style>
    :root{ --bg-url: url('<?php echo BG_URL; ?>'); }
    body::before{
      background-image:
        linear-gradient(135deg, rgba(30,58,138,.85) 0%, rgba(9,188,138,.82) 55%, rgba(246,189,96,.80) 100%),
        var(--bg-url) !important;
    }
  </style>
</head>
<body>

<?php
// ========== INYECTA NAVBAR EN TODAS LAS PÁGINAS ==========
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/navbar.php';
// =========================================================
// ========== INYECTA PILA DE ALERTAS GLOBAL ==========
require_once $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . '/app/views/shared/flash.php';


?>
