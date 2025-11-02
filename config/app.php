<?php
return [
  // Clave para firmar la cookie de “recordar sesión”
  'APP_KEY'            => 'd7A4jKp9X3qL0wZrM8tFyB2hV6nGc5RuN1oTsYeQ4kHxPiUvSmDlWfCjEaRbOg',

  // Políticas de sesión (lo que ya acordamos)
  'REMEMBER_DAYS'      => 30,   // recordar sesión
  'INACTIVITY_MINUTES' => 30,   // cierre por inactividad
  'LOGIN_MAX_ATTEMPTS' => 10,   // intentos fallidos
  'AUTO_UNLOCK_MIN'    => 30,   // desbloqueo automático
];
