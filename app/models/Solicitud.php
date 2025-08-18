<?php
// Incluir sistema de rutas dinámicas
require_once(__DIR__ . '/../config/paths.php');

// Incluir conexión usando ruta dinámica
safe_require_once(config_path('conexion.php'));

class Solicitud {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::getConexion();
    }

    /**
     * Crear una nueva solicitud
     */
    public function crear($data) {
        try {
            $sql = "INSERT INTO solicitudes (
                titulo, descripcion, departamento_id, sede_id, perfil_puesto,
                cantidad, prioridad, modalidad, salario_min, salario_max,
                fecha_limite_cobertura, requisitos_json, solicitante_id, estado
            ) VALUES (
                :titulo, :descripcion, :departamento_id, :sede_id, :perfil_puesto,
                :cantidad, :prioridad, :modalidad, :salario_min, :salario_max,
                :fecha_limite_cobertura, :requisitos_json, :solicitante_id, :estado
            )";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':titulo' => strtoupper(trim($data['titulo'])),
                ':descripcion' => trim($data['descripcion']),
                ':departamento_id' => $data['departamento_id'],
                ':sede_id' => $data['sede_id'],
                ':perfil_puesto' => strtoupper(trim($data['perfil_puesto'])),
                ':cantidad' => $data['cantidad'],
                ':prioridad' => $data['prioridad'],
                ':modalidad' => $data['modalidad'],
                ':salario_min' => !empty($data['salario_min']) ? $data['salario_min'] : null,
                ':salario_max' => !empty($data['salario_max']) ? $data['salario_max'] : null,
                ':fecha_limite_cobertura' => !empty($data['fecha_limite_cobertura']) ? $data['fecha_limite_cobertura'] : null,
                ':requisitos_json' => !empty($data['requisitos_json']) ? json_encode($data['requisitos_json']) : null,
                ':solicitante_id' => $data['solicitante_id'],
                ':estado' => $data['estado'] ?? 'borrador'
            ]);
        } catch (PDOException $e) {
            error_log("Error al crear solicitud: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todas las solicitudes con información completa
     */
    public function obtenerTodas() {
        try {
            $sql = "SELECT * FROM v_solicitudes_completa ORDER BY creado_en DESC";
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener solicitudes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener solicitudes por departamento
     */
    public function obtenerPorDepartamento($departamento_id) {
        try {
            $sql = "SELECT * FROM v_solicitudes_completa WHERE departamento_id = :departamento_id ORDER BY creado_en DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':departamento_id' => $departamento_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener solicitudes por departamento: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener solicitudes por sede
     */
    public function obtenerPorSede($sede_id) {
        try {
            $sql = "SELECT * FROM v_solicitudes_completa WHERE sede_id = :sede_id ORDER BY creado_en DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':sede_id' => $sede_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener solicitudes por sede: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener solicitudes por sede y departamento
     */
    public function obtenerPorSedeDepartamento($sede_id, $departamento_id = null) {
        try {
            $sql = "SELECT * FROM v_solicitudes_completa WHERE sede_id = :sede_id";
            $params = [':sede_id' => $sede_id];
            
            if ($departamento_id) {
                $sql .= " AND departamento_id = :departamento_id";
                $params[':departamento_id'] = $departamento_id;
            }
            
            $sql .= " ORDER BY creado_en DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener solicitudes por sede/departamento: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener solicitud por ID
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT * FROM v_solicitudes_completa WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener solicitud por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener solicitudes por estado
     */
    public function obtenerPorEstado($estado) {
        try {
            $sql = "SELECT * FROM v_solicitudes_completa WHERE estado = :estado ORDER BY creado_en DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':estado' => $estado]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener solicitudes por estado: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualizar solicitud
     */
    public function actualizar($id, $data) {
        try {
            $sql = "UPDATE solicitudes SET
                        titulo = :titulo,
                        descripcion = :descripcion,
                        departamento_id = :departamento_id,
                        sede_id = :sede_id,
                        perfil_puesto = :perfil_puesto,
                        cantidad = :cantidad,
                        prioridad = :prioridad,
                        modalidad = :modalidad,
                        salario_min = :salario_min,
                        salario_max = :salario_max,
                        fecha_limite_cobertura = :fecha_limite_cobertura,
                        requisitos_json = :requisitos_json,
                        estado = :estado,
                        motivo_rechazo = :motivo_rechazo,
                        motivo_cierre = :motivo_cierre,
                        actualizado_en = NOW()
                    WHERE id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':titulo' => strtoupper(trim($data['titulo'])),
                ':descripcion' => trim($data['descripcion']),
                ':departamento_id' => $data['departamento_id'],
                ':sede_id' => $data['sede_id'],
                ':perfil_puesto' => strtoupper(trim($data['perfil_puesto'])),
                ':cantidad' => $data['cantidad'],
                ':prioridad' => $data['prioridad'],
                ':modalidad' => $data['modalidad'],
                ':salario_min' => !empty($data['salario_min']) ? $data['salario_min'] : null,
                ':salario_max' => !empty($data['salario_max']) ? $data['salario_max'] : null,
                ':fecha_limite_cobertura' => !empty($data['fecha_limite_cobertura']) ? $data['fecha_limite_cobertura'] : null,
                ':requisitos_json' => !empty($data['requisitos_json']) ? json_encode($data['requisitos_json']) : null,
                ':estado' => $data['estado'],
                ':motivo_rechazo' => !empty($data['motivo_rechazo']) ? trim($data['motivo_rechazo']) : null,
                ':motivo_cierre' => !empty($data['motivo_cierre']) ? trim($data['motivo_cierre']) : null,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Error al actualizar solicitud: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar estado de la solicitud
     */
    public function cambiarEstado($id, $estado, $motivo = null) {
        try {
            $sql = "UPDATE solicitudes SET estado = :estado, actualizado_en = NOW()";
            $params = [':estado' => $estado, ':id' => $id];
            
            if ($estado === 'rechazada' && $motivo) {
                $sql .= ", motivo_rechazo = :motivo";
                $params[':motivo'] = $motivo;
            } elseif ($estado === 'cerrada' && $motivo) {
                $sql .= ", motivo_cierre = :motivo";
                $params[':motivo'] = $motivo;
            } elseif ($estado === 'solicita cambios' && $motivo) {
                $sql .= ", cambios_solicitados = :motivo";
                $params[':motivo'] = $motivo;
            }
            
            $sql .= " WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Error al cambiar estado de solicitud: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Asignar gerente a la solicitud
     */
    public function asignarGerente($id, $gerente_id) {
        try {
            $sql = "UPDATE solicitudes SET gerente_id = :gerente_id, actualizado_en = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':gerente_id' => $gerente_id, ':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error al asignar gerente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar solicitud (soft delete)
     */
    public function eliminarLogico($id) {
        try {
            $sql = "UPDATE solicitudes SET activo = 0, actualizado_en = NOW() WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error al eliminar solicitud: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de solicitudes
     */
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        estado,
                        COUNT(*) as total,
                        COUNT(CASE WHEN prioridad = 'alta' THEN 1 END) as alta_prioridad,
                        COUNT(CASE WHEN prioridad = 'media' THEN 1 END) as media_prioridad,
                        COUNT(CASE WHEN prioridad = 'baja' THEN 1 END) as baja_prioridad
                    FROM solicitudes 
                    WHERE activo = 1
                    GROUP BY estado";
            
            $stmt = $this->pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Buscar solicitudes
     */
    public function buscar($termino, $departamento_id = null, $sede_id = null) {
        try {
            $sql = "SELECT * FROM v_solicitudes_completa 
                    WHERE (titulo LIKE :termino OR perfil_puesto LIKE :termino OR codigo LIKE :termino)";
            $params = [':termino' => "%$termino%"];
            
            if ($departamento_id) {
                $sql .= " AND departamento_id = :departamento_id";
                $params[':departamento_id'] = $departamento_id;
            }
            
            if ($sede_id) {
                $sql .= " AND sede_id = :sede_id";
                $params[':sede_id'] = $sede_id;
            }
            
            $sql .= " ORDER BY creado_en DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al buscar solicitudes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener solicitudes próximas a vencer
     */
    public function obtenerProximasAVencer($dias = 30) {
        try {
            $sql = "SELECT * FROM v_solicitudes_completa 
                    WHERE fecha_limite_cobertura BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                    AND estado NOT IN ('cerrada', 'rechazada')
                    ORDER BY fecha_limite_cobertura ASC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':dias' => $dias]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener solicitudes próximas a vencer: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verificar si existe una solicitud con el mismo código
     */
    public function existeCodigo($codigo, $id = null) {
        try {
            $sql = "SELECT COUNT(*) FROM solicitudes WHERE codigo = :codigo";
            $params = [':codigo' => $codigo];
            
            if ($id) {
                $sql .= " AND id != :id";
                $params[':id'] = $id;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar código: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener el ID de la última solicitud creada
     */
    public function obtenerUltimoId() {
        try {
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error al obtener último ID: " . $e->getMessage());
            return false;
        }
    }
}
?>
