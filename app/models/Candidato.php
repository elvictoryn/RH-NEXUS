<?php
// Incluir sistema de rutas dinámicas
require_once(__DIR__ . '/../config/paths.php');

// Incluir conexión usando ruta dinámica
safe_require_once(config_path('conexion.php'));

class Candidato {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::getConexion();
    }

    /**
     * Crear un nuevo candidato
     */
    public function crear($data) {
        try {
            $sql = "INSERT INTO candidatos (
                nombre, curp, edad, genero, nivel_educacion, carrera, 
                area_experiencia, anos_experiencia, companias_previas, 
                distancia_sede, telefono, correo, direccion, sede_id, departamento_id
            ) VALUES (
                :nombre, :curp, :edad, :genero, :nivel_educacion, :carrera,
                :area_experiencia, :anos_experiencia, :companias_previas,
                :distancia_sede, :telefono, :correo, :direccion, :sede_id, :departamento_id
            )";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nombre' => strtoupper(trim($data['nombre'])),
                ':curp' => strtoupper(trim($data['curp'])),
                ':edad' => $data['edad'],
                ':genero' => $data['genero'],
                ':nivel_educacion' => strtoupper(trim($data['nivel_educacion'])),
                ':carrera' => !empty($data['carrera']) ? strtoupper(trim($data['carrera'])) : null,
                ':area_experiencia' => strtoupper(trim($data['area_experiencia'])),
                ':anos_experiencia' => $data['anos_experiencia'],
                ':companias_previas' => !empty($data['companias_previas']) ? strtoupper(trim($data['companias_previas'])) : null,
                ':distancia_sede' => !empty($data['distancia_sede']) ? $data['distancia_sede'] : null,
                ':telefono' => trim($data['telefono']),
                ':correo' => strtolower(trim($data['correo'])),
                ':direccion' => strtoupper(trim($data['direccion'])),
                ':sede_id' => $data['sede_id'],
                ':departamento_id' => $data['departamento_id']
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear candidato: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verificar si ya existe un candidato con la misma CURP
     */
    public function existeCurp($curp, $id = null) {
        try {
            $sql = "SELECT COUNT(*) FROM candidatos WHERE curp = :curp";
            $params = [':curp' => strtoupper(trim($curp))];
            
            if ($id) {
                $sql .= " AND id != :id";
                $params[':id'] = $id;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar CURP: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los candidatos con información de sede y departamento
     */
    public function obtenerTodos() {
        try {
            $sql = "SELECT c.*, s.nombre AS sede_nombre, d.nombre AS departamento_nombre
                    FROM candidatos c
                    INNER JOIN sedes s ON c.sede_id = s.id
                    INNER JOIN departamentos d ON c.departamento_id = d.id
                    WHERE c.estado != 'inactivo'
                    ORDER BY c.fecha_registro DESC";
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener candidatos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener candidatos por sede y departamento
     */
    public function obtenerPorSedeDepartamento($sede_id, $departamento_id = null) {
        try {
            $sql = "SELECT c.*, s.nombre AS sede_nombre, d.nombre AS departamento_nombre
                    FROM candidatos c
                    INNER JOIN sedes s ON c.sede_id = s.id
                    INNER JOIN departamentos d ON c.departamento_id = d.id
                    WHERE c.sede_id = :sede_id";
            
            $params = [':sede_id' => $sede_id];
            
            if ($departamento_id) {
                $sql .= " AND c.departamento_id = :departamento_id";
                $params[':departamento_id'] = $departamento_id;
            }
            
            $sql .= " AND c.estado != 'inactivo' ORDER BY c.fecha_registro DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener candidatos por sede/departamento: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener candidato por ID
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT c.*, s.nombre AS sede_nombre, d.nombre AS departamento_nombre
                    FROM candidatos c
                    INNER JOIN sedes s ON c.sede_id = s.id
                    INNER JOIN departamentos d ON c.departamento_id = d.id
                    WHERE c.id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener candidato por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar candidato
     */
    public function actualizar($id, $data) {
        try {
            $sql = "UPDATE candidatos SET
                        nombre = :nombre,
                        curp = :curp,
                        edad = :edad,
                        genero = :genero,
                        nivel_educacion = :nivel_educacion,
                        carrera = :carrera,
                        area_experiencia = :area_experiencia,
                        anos_experiencia = :anos_experiencia,
                        companias_previas = :companias_previas,
                        distancia_sede = :distancia_sede,
                        telefono = :telefono,
                        correo = :correo,
                        direccion = :direccion,
                        sede_id = :sede_id,
                        departamento_id = :departamento_id,
                        estado = :estado,
                        actualizado_en = NOW()
                    WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nombre' => strtoupper(trim($data['nombre'])),
                ':curp' => strtoupper(trim($data['curp'])),
                ':edad' => $data['edad'],
                ':genero' => $data['genero'],
                ':nivel_educacion' => strtoupper(trim($data['nivel_educacion'])),
                ':carrera' => !empty($data['carrera']) ? strtoupper(trim($data['carrera'])) : null,
                ':area_experiencia' => strtoupper(trim($data['area_experiencia'])),
                ':anos_experiencia' => $data['anos_experiencia'],
                ':companias_previas' => !empty($data['companias_previas']) ? strtoupper(trim($data['companias_previas'])) : null,
                ':distancia_sede' => !empty($data['distancia_sede']) ? $data['distancia_sede'] : null,
                ':telefono' => trim($data['telefono']),
                ':correo' => strtolower(trim($data['correo'])),
                ':direccion' => strtoupper(trim($data['direccion'])),
                ':sede_id' => $data['sede_id'],
                ':departamento_id' => $data['departamento_id'],
                ':estado' => $data['estado'],
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar candidato: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar estado del candidato
     */
    public function cambiarEstado($id, $estado) {
        try {
            $sql = "UPDATE candidatos SET estado = :estado, actualizado_en = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error al cambiar estado del candidato: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar candidato (cambio lógico)
     */
    public function eliminarLogico($id) {
        return $this->cambiarEstado($id, 'inactivo');
    }

    /**
     * Obtener estadísticas de candidatos
     */
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        estado,
                        COUNT(*) as total,
                        COUNT(CASE WHEN genero = 'Masculino' THEN 1 END) as masculino,
                        COUNT(CASE WHEN genero = 'Femenino' THEN 1 END) as femenino,
                        COUNT(CASE WHEN genero = 'Otro' THEN 1 END) as otro
                    FROM candidatos 
                    WHERE estado != 'inactivo'
                    GROUP BY estado";
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar candidatos por criterios
     */
    public function buscar($termino, $sede_id = null, $departamento_id = null) {
        try {
            $sql = "SELECT c.*, s.nombre AS sede_nombre, d.nombre AS departamento_nombre
                    FROM candidatos c
                    INNER JOIN sedes s ON c.sede_id = s.id
                    INNER JOIN departamentos d ON c.departamento_id = d.id
                    WHERE c.estado != 'inactivo' AND (
                        c.nombre LIKE :termino OR
                        c.curp LIKE :termino OR
                        c.area_experiencia LIKE :termino OR
                        c.carrera LIKE :termino
                    )";
            
            $params = [':termino' => "%$termino%"];
            
            if ($sede_id) {
                $sql .= " AND c.sede_id = :sede_id";
                $params[':sede_id'] = $sede_id;
            }
            
            if ($departamento_id) {
                $sql .= " AND c.departamento_id = :departamento_id";
                $params[':departamento_id'] = $departamento_id;
            }
            
            $sql .= " ORDER BY c.fecha_registro DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al buscar candidatos: " . $e->getMessage());
            return [];
        }
    }
}
?> 