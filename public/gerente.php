<?php
require_once __DIR__.'/../app/middlewares/auth.php';
if (($_SESSION['rol'] ?? '') !== 'gerente') { header('Location: /sistema_rh/public/login.php'); exit; }
require_once __DIR__.'/../app/views/gerente/index.php';
