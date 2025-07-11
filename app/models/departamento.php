<?php
require_once(__DIR__ . '/../../config/conexion.php');

class Departamento {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::getConexion();
    }

    public function crear($data) {
        try {
            $sql = "INSERT INTO departamentos (nombre, descripcion, sede_id) 
                    VALUES (:nombre, :descripcion, :sede_id)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nombre'      => strtoupper($data['nombre']),
                ':descripcion' => strtoupper($data['descripcion']),
                ':sede_id'     => $data['sede_id']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerTodosConSede() {
        $sql = "SELECT d.*, s.nombre AS sede_nombre 
                FROM departamentos d
                JOIN sedes s ON d.sede_id = s.id
                ORDER BY d.nombre ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existeNombreEnSede($nombre, $sede_id) {
        $sql = "SELECT COUNT(*) FROM departamentos 
                WHERE nombre = :nombre AND sede_id = :sede_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => strtoupper($nombre),
            ':sede_id' => $sede_id
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM departamentos WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($data) {
        try {
            $sql = "UPDATE departamentos 
                    SET nombre = :nombre, descripcion = :descripcion, sede_id = :sede_id 
                    WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nombre'      => strtoupper($data['nombre']),
                ':descripcion' => strtoupper($data['descripcion']),
                ':sede_id'     => $data['sede_id'],
                ':id'          => $data['id']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminar($id) {
        $sql = "DELETE FROM departamentos WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
