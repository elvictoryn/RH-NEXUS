<?php
require_once __DIR__.'/../app/middlewares/auth.php';
if (($_SESSION['rol'] ?? '') !== 'admin') { header('Location: /sistema_rh/public/login.php'); exit; }
require_once __DIR__.'/../app/views/admin/index.php';
