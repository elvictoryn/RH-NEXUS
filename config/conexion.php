<?php
class Conexion {
    private static $pdo = null;

    public static function getConexion() {
        if (self::$pdo === null) {
            $host = 'localhost';
            $db = 'sistema_rh';
            $user = 'root';
            $pass = '';

            try {
                self::$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass); // ← Aquí se usa self::$pdo
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}
