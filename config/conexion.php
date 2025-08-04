<?php
// Incluir configuración de rutas para obtener las constantes de DB
require_once __DIR__ . '/../app/config/paths.php';

class Conexion {
    private static $pdo = null;

    public static function getConexion() {
        if (self::$pdo === null) {
            $host = DB_HOST;
            $db = DB_NAME;
            $user = DB_USER;
            $pass = DB_PASS;

            try {
                self::$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
