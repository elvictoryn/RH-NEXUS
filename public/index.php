<?php
session_start();
require_once __DIR__.'/../app/controllers/AuthController.php';
require_once __DIR__.'/../app/middlewares/auth.php'; // lo usaremos solo en rutas protegidas

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');         // /TU_CARPETA/public
$route = '/'.ltrim(substr($uri, strlen($base)), '/');           // normaliza

$auth = new AuthController();

switch ($route) {
  // Auth
  case '/login':
    if ($_SERVER['REQUEST_METHOD']==='POST') $auth->login(); else $auth->showLogin(); 
    break;
  case '/logout':
    $auth->logout(); 
    break;

  // Dashboards por rol (rutas protegidas)
  case '/admin':
    require __DIR__.'/../app/middlewares/auth.php';
    if (($_SESSION['rol'] ?? '') !== 'admin') { header("Location: {$base}/login"); exit; }
    require __DIR__.'/../app/views/admin/index.php';
    break;

  case '/rh':
    require __DIR__.'/../app/middlewares/auth.php';
    if (($_SESSION['rol'] ?? '') !== 'rh') { header("Location: {$base}/login"); exit; }
    require __DIR__.'/../app/views/rh/index.php';
    break;

  case '/gerente':
    require __DIR__.'/../app/middlewares/auth.php';
    if (($_SESSION['rol'] ?? '') !== 'gerente') { header("Location: {$base}/login"); exit; }
    require __DIR__.'/../app/views/gerente/index.php';
    break;

  case '/jefe-area':
    require __DIR__.'/../app/middlewares/auth.php';
    if (($_SESSION['rol'] ?? '') !== 'jefe_area') { header("Location: {$base}/login"); exit; }
    require __DIR__.'/../app/views/jefe_area/index.php';
    break;

  // raíz → login o redirección por rol si ya está logueado
  case '/':
  default:
    if (!empty($_SESSION['uid'])) {
      $r = $_SESSION['rol'] ?? '';
      header("Location: {$base}/".($r==='admin'?'admin':($r==='rh'?'rh':($r==='gerente'?'gerente':($r==='jefe_area'?'jefe-area':'login')))));
      exit;
    }
    header("Location: {$base}/login"); 
    exit;
}
