<?php
// config/ia.php
if (!defined('BASE_PATH')) define('BASE_PATH','/sistema_rh');

return [
  'IA_BASE_URL' => getenv('IA_BASE_URL') ?: 'https://web-production-1fa4.up.railway.app/',
  'IA_API_KEY'  => getenv('IA_API_KEY')  ?: 'TOKEN_IA_123456', // Mismo que en Railway
  // timeouts
  'CONNECT_TIMEOUT' => 5,
  'TIMEOUT'         => 25,
];