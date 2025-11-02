<?php
require_once __DIR__.'/../app/middlewares/auth.php';
if (($_SESSION['rol'] ?? '') !== 'jefe_area') { header('Location: /sistema_rh/public/login.php'); exit; }
require_once __DIR__.'/../app/views/jefe_area/index.php';
