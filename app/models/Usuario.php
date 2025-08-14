
<?php
require_once(__DIR__ . '/../../config/conexion.php');
// models/usuario.php
class Usuario {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::getConexion();
    }

    public function crear($datos) {
        $sql = "INSERT INTO usuarios 
            (nombre_completo, usuario, contrasena, rol, numero_empleado, correo, sede_id, departamento_id, telefono, fotografia, estado)
            VALUES 
            (:nombre_completo, :usuario, :contrasena, :rol, :numero_empleado, :correo, :sede_id, :departamento_id, :telefono, :fotografia, 'activo')";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre_completo'     => $datos['nombre_completo'],
            ':usuario'             => $datos['usuario'],
            ':contrasena'          => $datos['contrasena'],
            ':rol'                 => $datos['rol'],
            ':numero_empleado'     => $datos['numero_empleado'],
            ':correo'              => $datos['correo'],
            ':sede_id'             => $datos['sede_id'],
            ':departamento_id'     => $datos['departamento_id'],
            ':telefono'            => $datos['telefono'],
            ':fotografia'          => $datos['fotografia']
        ]);
    }

    public function existeUsuario($usuario) {
        $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        return $stmt->fetch() ? true : false;
    }

    public function existeNumeroEmpleado($numero) {
        $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE numero_empleado = ?");
        $stmt->execute([$numero]);
        return $stmt->fetch() ? true : false;
    }

    public function existeJefeEnDepartamento($sede_id, $departamento_id) {
        $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE rol = 'jefe_area' AND sede_id = ? AND departamento_id = ? AND estado = 'activo'");
        $stmt->execute([$sede_id, $departamento_id]);
        return $stmt->fetch() ? true : false;
    }

    public function existeGerenteEnSede($sede_id) {
        $stmt = $this->pdo->prepare("SELECT id FROM usuarios WHERE rol = 'gerente' AND sede_id = ? AND estado = 'activo'");
        $stmt->execute([$sede_id]);
        return $stmt->fetch() ? true : false;
    }
    public function obtenerTodosActivos() {
    $sql = "SELECT 
                u.id,
                u.nombre_completo,
                u.usuario,
                u.numero_empleado,
                u.rol,
                u.correo,
                u.telefono,
                u.fotografia,
                u.estado,
                d.nombre AS nombre_departamento,
                s.nombre AS nombre_sede
            FROM usuarios u
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            LEFT JOIN sedes s ON u.sede_id = s.id
            WHERE u.estado = 'activo'
            ORDER BY u.nombre_completo ASC";

    $stmt = $this->pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function eliminarLogico($id) {
    $stmt = $this->pdo->prepare("UPDATE usuarios SET estado = 'inactivo' WHERE id = ?");
    return $stmt->execute([$id]);
}



public function obtenerPorId($id) {
    $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


}
