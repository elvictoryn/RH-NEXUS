-- =====================================================
-- RH-NEXUS - TRIGGERS Y PROCEDIMIENTOS ALMACENADOS
-- =====================================================
-- Archivo: 02_triggers_procedimientos.sql
-- Descripción: Triggers y procedimientos para automatización
-- Fecha: 2025-01-XX
-- =====================================================

-- USE `sistema_rh_remodelado`;

-- =====================================================
-- TRIGGERS PARA SOLICITUDES
-- =====================================================

DELIMITER $$

-- Trigger para generar código automático de solicitudes
CREATE TRIGGER `generar_codigo_solicitud`
BEFORE INSERT ON `solicitudes`
FOR EACH ROW
BEGIN
    DECLARE anio_actual INT;
    DECLARE siguiente_numero INT;
    
    -- Si no se proporciona código, generarlo automáticamente
    IF NEW.codigo IS NULL OR NEW.codigo = '' THEN
        SET anio_actual = YEAR(CURRENT_DATE);
        
        -- Obtener el siguiente número para este año
        SELECT COALESCE(MAX(CAST(SUBSTRING(codigo, 9) AS UNSIGNED)), 0) + 1
        INTO siguiente_numero
        FROM `solicitudes` 
        WHERE codigo LIKE CONCAT('SOL-', anio_actual, '-%');
        
        -- Generar el código con formato SOL-YYYY-XXX
        SET NEW.codigo = CONCAT('SOL-', anio_actual, '-', LPAD(siguiente_numero, 3, '0'));
    END IF;
END$$

-- Trigger para registrar automáticamente al solicitante como participante
CREATE TRIGGER `registrar_solicitante_participante`
AFTER INSERT ON `solicitudes`
FOR EACH ROW
BEGIN
    INSERT INTO `solicitudes_participantes` (
        `solicitud_id`, 
        `usuario_id`, 
        `rol_participante`, 
        `fecha_notificacion`
    ) VALUES (
        NEW.id, 
        NEW.solicitante_id, 
        'solicitante', 
        NOW()
    );
END$$

-- Trigger para registrar cambios de estado en el historial
CREATE TRIGGER `registrar_cambio_estado_solicitud`
AFTER UPDATE ON `solicitudes`
FOR EACH ROW
BEGIN
    -- Solo registrar si cambió el estado
    IF OLD.estado != NEW.estado THEN
        INSERT INTO `solicitudes_historial` (
            `solicitud_id`,
            `usuario_id`,
            `estado_anterior`,
            `estado_nuevo`,
            `tipo_cambio`,
            `fecha_cambio`
        ) VALUES (
            NEW.id,
            COALESCE(NEW.gerente_id, NEW.solicitante_id), -- Usar gerente si existe, sino solicitante
            OLD.estado,
            NEW.estado,
            'estado',
            NOW()
        );
    END IF;
END$$

-- Trigger para notificar automáticamente a RH y gerentes cuando se envía a gerencia
CREATE TRIGGER `notificar_gerencia_solicitud`
AFTER UPDATE ON `solicitudes`
FOR EACH ROW
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE usuario_id INT;
    DECLARE cur CURSOR FOR
        SELECT ud.usuario_id
        FROM usuarios_departamentos ud
        WHERE ud.departamento_id = NEW.departamento_id
          AND ud.sede_id = NEW.sede_id
          AND ud.rol_en_departamento IN ('rh', 'gerente')
          AND ud.activo = 1
          AND ud.usuario_id != NEW.solicitante_id; -- No notificar al solicitante
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Solo ejecutar cuando el estado cambie a 'enviada a gerencia'
    IF NEW.estado = 'enviada a gerencia' AND OLD.estado != 'enviada a gerencia' THEN
        
        OPEN cur;
        
        read_loop: LOOP
            FETCH cur INTO usuario_id;
            IF done THEN
                LEAVE read_loop;
            END IF;
            
            -- Insertar notificación solo si no existe
            INSERT IGNORE INTO `solicitudes_participantes` (
                `solicitud_id`,
                `usuario_id`,
                `rol_participante`,
                `fecha_notificacion`
            ) VALUES (
                NEW.id,
                usuario_id,
                'notificado',
                NOW()
            );
            
        END LOOP;
        
        CLOSE cur;
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- PROCEDIMIENTOS ALMACENADOS
-- =====================================================

DELIMITER $$

-- Procedimiento para obtener solicitudes por rol y contexto
CREATE PROCEDURE `ObtenerSolicitudesPorRol`(
    IN p_usuario_id INT,
    IN p_rol_usuario VARCHAR(20)
)
BEGIN
    DECLARE p_sede_id INT;
    DECLARE p_departamento_id INT;
    
    -- Obtener sede y departamento principal del usuario
    SELECT sede_principal_id, departamento_principal_id 
    INTO p_sede_id, p_departamento_id
    FROM usuarios WHERE id = p_usuario_id;
    
    -- Consulta base
    SET @sql = CONCAT('
        SELECT DISTINCT
            s.*,
            d.nombre AS departamento_nombre,
            sed.nombre AS sede_nombre,
            sol.nombre_completo AS solicitante_nombre,
            g.nombre_completo AS gerente_nombre,
            CASE 
                WHEN s.estado = "borrador" THEN "Borrador"
                WHEN s.estado = "enviada a gerencia" THEN "Enviada a Gerencia"
                WHEN s.estado = "aceptada gerencia" THEN "Aceptada por Gerencia"
                WHEN s.estado = "pospuesta" THEN "Pospuesta"
                WHEN s.estado = "rechazada" THEN "Rechazada"
                WHEN s.estado = "en proceso rh" THEN "En Proceso RH"
                WHEN s.estado = "solicita cambios" THEN "Solicita Cambios"
                WHEN s.estado = "cerrada" THEN "Cerrada"
                ELSE s.estado
            END AS estado_nombre
        FROM solicitudes s
        INNER JOIN departamentos d ON s.departamento_id = d.id
        INNER JOIN sedes sed ON s.sede_id = sed.id
        INNER JOIN usuarios sol ON s.solicitante_id = sol.id
        LEFT JOIN usuarios g ON s.gerente_id = g.id
        WHERE s.activo = 1
    ');
    
    -- Filtrar según el rol
    CASE p_rol_usuario
        WHEN 'admin' THEN
            -- Admin ve todas las solicitudes
            SET @sql = CONCAT(@sql, ' ORDER BY s.creado_en DESC');
            
        WHEN 'jefe_area' THEN
            -- Jefe de área ve solo las de su departamento
            SET @sql = CONCAT(@sql, ' AND s.departamento_id = ', p_departamento_id, ' ORDER BY s.creado_en DESC');
            
        WHEN 'gerente' THEN
            -- Gerente ve las de su sede
            SET @sql = CONCAT(@sql, ' AND s.sede_id = ', p_sede_id, ' ORDER BY s.creado_en DESC');
            
        WHEN 'rh' THEN
            -- RH ve todas las activas
            SET @sql = CONCAT(@sql, ' ORDER BY s.creado_en DESC');
            
        ELSE
            -- Rol no reconocido
            SET @sql = CONCAT(@sql, ' AND 1=0');
    END CASE;
    
    -- Ejecutar consulta dinámica
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

-- Procedimiento para cambiar estado de solicitud con auditoría
CREATE PROCEDURE `CambiarEstadoSolicitud`(
    IN p_solicitud_id INT,
    IN p_usuario_id INT,
    IN p_nuevo_estado VARCHAR(50),
    IN p_comentario TEXT
)
BEGIN
    DECLARE p_estado_anterior VARCHAR(50);
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Obtener estado anterior
    SELECT estado INTO p_estado_anterior
    FROM solicitudes WHERE id = p_solicitud_id;
    
    -- Actualizar estado
    UPDATE solicitudes 
    SET estado = p_nuevo_estado,
        actualizado_en = NOW()
    WHERE id = p_solicitud_id;
    
    -- Registrar en historial
    INSERT INTO solicitudes_historial (
        solicitud_id,
        usuario_id,
        estado_anterior,
        estado_nuevo,
        comentario,
        tipo_cambio
    ) VALUES (
        p_solicitud_id,
        p_usuario_id,
        p_estado_anterior,
        p_nuevo_estado,
        p_comentario,
        'estado'
    );
    
    COMMIT;
END$$

-- Procedimiento para obtener notificaciones no leídas de un usuario
CREATE PROCEDURE `ObtenerNotificacionesNoLeidas`(
    IN p_usuario_id INT
)
BEGIN
    SELECT 
        sp.*,
        s.codigo,
        s.titulo,
        s.estado,
        d.nombre AS departamento_nombre,
        sed.nombre AS sede_nombre,
        sol.nombre_completo AS solicitante_nombre
    FROM solicitudes_participantes sp
    INNER JOIN solicitudes s ON sp.solicitud_id = s.id
    INNER JOIN departamentos d ON s.departamento_id = d.id
    INNER JOIN sedes sed ON s.sede_id = sed.id
    INNER JOIN usuarios sol ON s.solicitante_id = sol.id
    WHERE sp.usuario_id = p_usuario_id
      AND sp.leido = 0
      AND sp.activo = 1
      AND s.activo = 1
    ORDER BY sp.fecha_notificacion DESC;
END$$

-- Procedimiento para marcar notificación como leída
CREATE PROCEDURE `MarcarNotificacionLeida`(
    IN p_solicitud_id INT,
    IN p_usuario_id INT
)
BEGIN
    UPDATE solicitudes_participantes 
    SET leido = 1,
        fecha_lectura = NOW()
    WHERE solicitud_id = p_solicitud_id 
      AND usuario_id = p_usuario_id;
END$$

-- Procedimiento para obtener estadísticas de solicitudes
CREATE PROCEDURE `ObtenerEstadisticasSolicitudes`(
    IN p_sede_id INT,
    IN p_departamento_id INT
)
BEGIN
    SELECT 
        estado,
        COUNT(*) as total,
        COUNT(CASE WHEN prioridad = 'alta' THEN 1 END) as alta_prioridad,
        COUNT(CASE WHEN prioridad = 'media' THEN 1 END) as media_prioridad,
        COUNT(CASE WHEN prioridad = 'baja' THEN 1 END) as baja_prioridad,
        COUNT(CASE WHEN modalidad = 'presencial' THEN 1 END) as presencial,
        COUNT(CASE WHEN modalidad = 'remoto' THEN 1 END) as remoto,
        COUNT(CASE WHEN modalidad = 'hibrido' THEN 1 END) as hibrido
    FROM solicitudes 
    WHERE activo = 1
      AND (p_sede_id IS NULL OR sede_id = p_sede_id)
      AND (p_departamento_id IS NULL OR departamento_id = p_departamento_id)
    GROUP BY estado
    ORDER BY total DESC;
END$$

DELIMITER ;

-- =====================================================
-- VISTAS ÚTILES
-- =====================================================

-- Vista completa de solicitudes con información relacionada
CREATE OR REPLACE VIEW `v_solicitudes_completa` AS
SELECT 
    s.id,
    s.codigo,
    s.titulo,
    s.descripcion,
    s.departamento_id,
    s.sede_id,
    s.perfil_puesto,
    s.cantidad,
    s.prioridad,
    s.modalidad,
    s.salario_min,
    s.salario_max,
    s.fecha_limite_cobertura,
    s.requisitos_json,
    s.solicitante_id,
    s.gerente_id,
    s.estado,
    s.motivo_rechazo,
    s.motivo_cierre,
    s.cambios_solicitados,
    s.activo,
    s.creado_en,
    s.actualizado_en,
    d.nombre AS departamento_nombre,
    sed.nombre AS sede_nombre,
    sol.nombre_completo AS solicitante_nombre,
    g.nombre_completo AS gerente_nombre
FROM solicitudes s
INNER JOIN departamentos d ON s.departamento_id = d.id
INNER JOIN sedes sed ON s.sede_id = sed.id
INNER JOIN usuarios sol ON s.solicitante_id = sol.id
LEFT JOIN usuarios g ON s.gerente_id = g.id
WHERE s.activo = 1;

-- Vista para usuarios con información de departamentos
CREATE OR REPLACE VIEW `v_usuarios_departamentos_completa` AS
SELECT 
    u.*,
    ud.departamento_id,
    ud.sede_id,
    ud.rol_en_departamento,
    d.nombre AS departamento_nombre,
    s.nombre AS sede_nombre
FROM usuarios u
LEFT JOIN usuarios_departamentos ud ON u.id = ud.usuario_id AND ud.activo = 1
LEFT JOIN departamentos d ON ud.departamento_id = d.id
LEFT JOIN sedes s ON ud.sede_id = s.id
WHERE u.estado = 'activo'
ORDER BY u.nombre_completo;

-- Vista para notificaciones pendientes
CREATE OR REPLACE VIEW `v_notificaciones_pendientes` AS
SELECT 
    sp.*,
    s.codigo,
    s.titulo,
    s.estado,
    d.nombre AS departamento_nombre,
    sed.nombre AS sede_nombre,
    sol.nombre_completo AS solicitante_nombre,
    u.nombre_completo AS usuario_nombre
FROM solicitudes_participantes sp
INNER JOIN solicitudes s ON sp.solicitud_id = s.id
INNER JOIN departamentos d ON s.departamento_id = d.id
INNER JOIN sedes sed ON s.sede_id = sed.id
INNER JOIN usuarios sol ON s.solicitante_id = sol.id
INNER JOIN usuarios u ON sp.usuario_id = u.id
WHERE sp.leido = 0
  AND sp.activo = 1
  AND s.activo = 1
ORDER BY sp.fecha_notificacion DESC;

-- =====================================================
-- COMENTARIOS FINALES
-- =====================================================
/*
TRIGGERS IMPLEMENTADOS:
1. generar_codigo_solicitud: Genera códigos automáticos SOL-YYYY-XXX
2. registrar_solicitante_participante: Registra automáticamente al solicitante
3. registrar_cambio_estado_solicitud: Auditoría automática de cambios de estado
4. notificar_gerencia_solicitud: Notifica automáticamente a RH y gerentes

PROCEDIMIENTOS IMPLEMENTADOS:
1. ObtenerSolicitudesPorRol: Filtra solicitudes según rol y contexto
2. CambiarEstadoSolicitud: Cambia estado con auditoría automática
3. ObtenerNotificacionesNoLeidas: Obtiene notificaciones pendientes
4. MarcarNotificacionLeida: Marca notificación como leída
5. ObtenerEstadisticasSolicitudes: Estadísticas con filtros opcionales

VISTAS IMPLEMENTADAS:
1. v_solicitudes_completa: Solicitudes con información completa
2. v_usuarios_departamentos_completa: Usuarios con contexto de departamentos
3. v_notificaciones_pendientes: Notificaciones no leídas con contexto
*/ 