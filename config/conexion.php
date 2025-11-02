<?php
require_once __DIR__ . '/environment.php';

class Conexion {
  public static function getConexion(): PDO {
    if (ENVIRONMENT === 'production') {
      // Configuración para InfinityFree
      $host = 'sql306.infinityfree.com';
      $db = 'if0_40170523_sistema_rh';
      $user = 'if0_40170523';
      $pass = 'a171LhcQWeVh';
    } else {
      // Configuración local
      $host = '127.0.0.1';
      $db = 'sistema_rh';
      $user = 'root';
      $pass = '';
    }
    
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    
    try {
      return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_PERSISTENT => false,  // No usar conexiones persistentes en InfinityFree
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true  // Optimizar para servidores compartidos
      ]);
    } catch (PDOException $e) {
      error_log("Error de conexión a BD: " . $e->getMessage());
      throw new Exception("Error de conexión a la base de datos");
    }
  }
}
