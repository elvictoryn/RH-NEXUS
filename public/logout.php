<?php
session_start();
session_unset();    // Limpia las variables de sesión
session_destroy();  // Destruye la sesión actual

header("Location: login.php"); // Redirige al login
exit;