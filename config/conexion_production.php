<?php
class Conexion {
  public static function getConexion(): PDO {
    // Configuración para InfinityFree
    $host = 'sqlXXX.epizy.com';  // Reemplazar con tu host de InfinityFree
    $db = 'epiz_XXXXXX_sistema_rh';  // Reemplazar con tu nombre de BD
    $user = 'epiz_XXXXXX';  // Reemplazar con tu usuario
    $pass = 'tu_password_aqui';  // Reemplazar con tu contraseña
    
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    
    try {
      return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30,  // Timeout para conexiones lentas
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
      ]);
    } catch (PDOException $e) {
      error_log("Error de conexión a BD: " . $e->getMessage());
      throw new Exception("Error de conexión a la base de datos");
    }
  }
}
