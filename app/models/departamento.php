<?php
require_once(__DIR__ . '/../../config/conexion.php');

class Departamento {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::getConexion();
    }

    public function crear($data) {
        $sql = "INSERT INTO departamentos (nombre, descripcion, sede_id, estado, creado_en)
                VALUES (:nombre, :descripcion, :sede_id, 1, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre'      => strtoupper(trim($data['nombre'])),
            ':descripcion' => strtoupper(trim($data['descripcion'])),
            ':sede_id'     => $data['sede_id']
        ]);
    }

    public function existeNombreEnSede($nombre, $sede_id) {
        $sql = "SELECT COUNT(*) FROM departamentos WHERE nombre = :nombre AND sede_id = :sede_id AND estado = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nombre'   => strtoupper(trim($nombre)),
            ':sede_id'  => $sede_id
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function obtenerTodosConSede() {
        $sql = "SELECT d.*, s.nombre AS nombre_sede
                FROM departamentos d
                INNER JOIN sedes s ON d.sede_id = s.id
                WHERE d.estado = 1
                ORDER BY d.nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM departamentos WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($id, $data) {
    $sql = "UPDATE departamentos SET
                nombre = :nombre,
                descripcion = :descripcion,
                sede_id = :sede_id,
                responsable_id = :responsable_id,
                actualizado_en = NOW()
            WHERE id = :id";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([
        ':nombre'         => strtoupper(trim($data['nombre'])),
        ':descripcion'    => strtoupper(trim($data['descripcion'])),
        ':sede_id'        => $data['sede_id'],
        ':responsable_id' => $data['responsable_id'],
        ':id'             => $id
    ]);
}

    public function eliminarLogico($id) {
        $sql = "UPDATE departamentos SET estado = 0 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function listarSedesActivas() {
        $sql = "SELECT id, nombre FROM sedes WHERE activo = 1 ORDER BY nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTodosConSedeYResponsable() {
        try {
            $sql = "SELECT d.id, d.nombre, d.descripcion, d.sede_id, d.responsable_id, s.nombre AS sede_nombre,
                           CONCAT(u.nombre_completo, ' (', u.numero_empleado, ')') AS responsable
                    FROM departamentos d
                    INNER JOIN sedes s ON d.sede_id = s.id
                    LEFT JOIN usuarios u ON d.responsable_id = u.id
                    WHERE d.estado = 1
                    ORDER BY d.nombre ASC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener departamentos: " . $e->getMessage());
            return [];
        }
    }

public function obtenerUsuariosActivos() {
        try {
            $stmt = $this->pdo->prepare("SELECT id, nombre_completo, numero_empleado FROM usuarios WHERE estado = 1 ORDER BY nombre_completo ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener usuarios activos: " . $e->getMessage());
            return [];
        }
    }
    public function existeNombreEnSedeEditando($nombre, $sede_id, $id) {
    $sql = "SELECT COUNT(*) FROM departamentos 
            WHERE UPPER(nombre) = UPPER(:nombre) 
              AND sede_id = :sede_id 
              AND id != :id 
              AND estado = 1";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => strtoupper(trim($nombre)),
        ':sede_id' => $sede_id,
        ':id' => $id
    ]);
    return $stmt->fetchColumn() > 0;



    
    ///models para llamar a la tabla sedes y departamentos dentro del modulo de usuarios ///////////////
}
     public function obtenerSedes() {
        $stmt = $this->pdo->prepare("SELECT id, nombre FROM sedes WHERE activo = 1 ORDER BY nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTodosActivos() {
        $stmt = $this->pdo->query("SELECT * FROM departamentos WHERE estado = 1 ORDER BY nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorSede($sede_id) {
        $stmt = $this->pdo->prepare("SELECT id, nombre, responsable_id FROM departamentos WHERE sede_id = ? AND estado = 1 ORDER BY nombre ASC");
        $stmt->execute([$sede_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
