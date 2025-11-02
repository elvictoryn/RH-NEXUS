<?php
// Prueba directa de conexión a base de datos sin usar la clase Conexion
echo "<h2>Prueba Directa de Conexión a Base de Datos</h2>";

$host = 'sql306.infinityfree.com';
$db = 'if0_40170523_sistema_rh';
$user = 'if0_40170523';
$pass = 'a171LhcQWeVh';
$port = 3306;

echo "Intentando conectar con:<br>";
echo "Host: $host<br>";
echo "Database: $db<br>";
echo "User: $user<br>";
echo "Port: $port<br><br>";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    echo "DSN: $dsn<br><br>";
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 30
    ]);
    
    echo "✅ <strong>CONEXIÓN EXITOSA</strong><br>";
    
    // Probar consulta
    $stmt = $pdo->query("SELECT 1 as test, NOW() as fecha");
    $result = $stmt->fetch();
    echo "✅ Consulta de prueba: " . $result['test'] . "<br>";
    echo "✅ Fecha del servidor: " . $result['fecha'] . "<br>";
    
} catch (PDOException $e) {
    echo "❌ <strong>ERROR DE CONEXIÓN:</strong><br>";
    echo "Código: " . $e->getCode() . "<br>";
    echo "Mensaje: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
}

echo "<br><strong>Información del servidor:</strong><br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "PDO MySQL disponible: " . (extension_loaded('pdo_mysql') ? 'SÍ' : 'NO') . "<br>";
echo "MySQLi disponible: " . (extension_loaded('mysqli') ? 'SÍ' : 'NO') . "<br>";
?>
