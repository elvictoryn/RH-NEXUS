<?php
// Incluir sistema de rutas dinámicas
require_once(__DIR__ . '/../config/paths.php');

// Incluir conexión usando ruta dinámica
safe_require_once(config_path('conexion.php'));

class Sede {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::getConexion();
    }

    public function crear($data) {
        try {
            $sql = "INSERT INTO sedes (nombre, domicilio, numero, interior, colonia, municipio, estado, cp, telefono)
                    VALUES (:nombre, :domicilio, :numero, :interior, :colonia, :municipio, :estado, :cp, :telefono)";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nombre'    => $data['nombre'],
                ':domicilio' => $data['domicilio'],
                ':numero'    => $data['numero'],
                ':interior'  => $data['interior'],
                ':colonia'   => $data['colonia'],
                ':municipio' => $data['municipio'],
                ':estado'    => $data['estado'],
                ':cp'        => $data['cp'],
                ':telefono'  => $data['telefono']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function existeNombre($nombre) {
        $sql = "SELECT COUNT(*) FROM sedes WHERE nombre = :nombre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nombre' => strtoupper($nombre)]);
        return $stmt->fetchColumn() > 0;
    }

    public function existeNombreExcepto($nombre, $id) {
        $sql = "SELECT COUNT(*) FROM sedes WHERE nombre = :nombre AND id != :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => strtoupper($nombre),
            ':id' => $id
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function desactivar($id) {
    $sql = "UPDATE sedes SET activo = 0 WHERE id = :id";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([':id' => $id]);
}

public function obtenerTodas() {
    $sql = "SELECT * FROM sedes WHERE activo = 1 ORDER BY nombre ASC";
    $stmt = $this->pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM sedes WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($id, $data) {
        try {
            $sql = "UPDATE sedes SET
                        nombre = :nombre,
                        domicilio = :domicilio,
                        numero = :numero,
                        interior = :interior,
                        colonia = :colonia,
                        municipio = :municipio,
                        estado = :estado,
                        cp = :cp,
                        telefono = :telefono
                    WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nombre'    => $data['nombre'],
                ':domicilio' => $data['domicilio'],
                ':numero'    => $data['numero'],
                ':interior'  => $data['interior'],
                ':colonia'   => $data['colonia'],
                ':municipio' => $data['municipio'],
                ':estado'    => $data['estado'],
                ':cp'        => $data['cp'],
                ':telefono'  => $data['telefono'],
                ':id'        => $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
