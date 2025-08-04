<?php
// Incluir sistema de rutas dinámicas si no está ya incluido
if (!defined('ROOT_PATH')) {
    require_once(__DIR__ . '/../config/paths.php');
    safe_require_once(config_path('conexion.php'));
}

class Usuario {
    private $conn;

    public function __construct() {
        // Incluir configuración de rutas para obtener las constantes de DB
        if (!defined('DB_HOST')) {
            require_once __DIR__ . '/../config/paths.php';
        }
        
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Conexión fallida: " . $this->conn->connect_error);
        }
    }

    public function crear($usuario, $contrasena, $rol, $nombre_completo, $departamento, $sede, $numero_empleado, $correo, $estado, $foto_nombre) {
        $sql = "INSERT INTO usuarios (
            usuario, contrasena, rol, nombre_completo, departamento, sede,
            numero_empleado, correo, estado, fotografia, fecha_registro
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("ssssssssss", $usuario, $contrasena, $rol, $nombre_completo,
            $departamento, $sede, $numero_empleado, $correo, $estado, $foto_nombre);

        return $stmt->execute();
    }
    //funcion para revisar en el momento si el usuario, numero de empleado ya existe 
    public function existeUsuario($usuario, $numero_empleado) {
        $sql = "SELECT id FROM usuarios WHERE usuario = ? OR numero_empleado = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $usuario, $numero_empleado);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }
    //funcion para listar los usuarios creados 
    public function listar() {
    $sql = "SELECT * FROM usuarios ORDER BY fecha_registro DESC";
    $result = $this->conn->query($sql);
    $datos = [];
    while ($fila = $result->fetch_assoc()) {
        $datos[] = $fila;
    }
    return $datos;
    }

    //funcion prara llamar los datos desde la base de datos al listado de usuarios 
    public function obtenerTodos() {
    $sql = "SELECT id, usuario, rol, nombre_completo, departamento, sede, numero_empleado, correo, estado FROM usuarios";
    $resultado = $this->conn->query($sql);
    $usuarios = [];

    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $usuarios[] = $fila;
        }
    }

    return $usuarios;
}

//metodo para ver los detalles de los usuarios 
public function obtenerPorId($id) {
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado->fetch_assoc();
}
public function actualizar($id, $datos) {
    $campos = [];
    foreach ($datos as $key => $value) {
        $campos[] = "$key = ?";
    }

    $sql = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id = ?";
    $stmt = $this->pdo->prepare($sql);
    $valores = array_values($datos);
    $valores[] = $id;
    $stmt->execute($valores);
}
public function actualizarConPassword($id, $usuario, $contrasena, $rol, $nombre_completo, $departamento, $sede, $numero_empleado, $correo, $fotografia) {
    $sql = "UPDATE usuarios SET 
              usuario = ?, 
              contrasena = ?, 
              rol = ?, 
              nombre_completo = ?, 
              departamento = ?, 
              sede = ?, 
              numero_empleado = ?, 
              correo = ?, 
              fotografia = ? 
            WHERE id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("sssssssssi", $usuario, $contrasena, $rol, $nombre_completo, $departamento, $sede, $numero_empleado, $correo, $fotografia, $id);
    return $stmt->execute();
}

public function actualizarSinPassword($id, $usuario, $rol, $nombre_completo, $departamento, $sede, $numero_empleado, $correo, $fotografia) {
    $sql = "UPDATE usuarios SET 
              usuario = ?, 
              rol = ?, 
              nombre_completo = ?, 
              departamento = ?, 
              sede = ?, 
              numero_empleado = ?, 
              correo = ?, 
              fotografia = ? 
            WHERE id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ssssssssi", $usuario, $rol, $nombre_completo, $departamento, $sede, $numero_empleado, $correo, $fotografia, $id);
    return $stmt->execute();
}
///==================================================================================================================================
public function obtenerPorRol($rol) {
    $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE rol = ?");
    $stmt->bind_param("s", $rol);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado->fetch_all(MYSQLI_ASSOC);
}


}


