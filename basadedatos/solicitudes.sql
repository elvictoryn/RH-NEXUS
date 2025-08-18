-- =====================================================
-- TABLA DE SOLICITUDES
-- =====================================================

CREATE TABLE IF NOT EXISTS `solicitudes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo` VARCHAR(20) UNIQUE NOT NULL COMMENT 'Código único de la solicitud',
    `titulo` VARCHAR(255) NOT NULL COMMENT 'Título de la solicitud',
    `descripcion` TEXT COMMENT 'Descripción detallada de la solicitud',
    `departamento_id` INT NOT NULL COMMENT 'ID del departamento solicitante',
    `sede_id` INT NOT NULL COMMENT 'ID de la sede',
    `perfil_puesto` VARCHAR(255) NOT NULL COMMENT 'Nombre del puesto solicitado',
    `cantidad` INT NOT NULL DEFAULT 1 COMMENT 'Cantidad de vacantes',
    `prioridad` ENUM('alta', 'media', 'baja') NOT NULL DEFAULT 'media' COMMENT 'Prioridad de la solicitud',
    `modalidad` ENUM('presencial', 'remoto', 'hibrido') NOT NULL DEFAULT 'presencial' COMMENT 'Modalidad de trabajo',
    `salario_min` DECIMAL(10,2) NULL COMMENT 'Salario mínimo ofrecido',
    `salario_max` DECIMAL(10,2) NULL COMMENT 'Salario máximo ofrecido',
    `fecha_limite_cobertura` DATE NULL COMMENT 'Fecha límite para cubrir la posición',
    `requisitos_json` JSON NULL COMMENT 'Requisitos en formato JSON',
    `solicitante_id` INT NOT NULL COMMENT 'ID del usuario que crea la solicitud',
    `gerente_id` INT NULL COMMENT 'ID del gerente que aprueba',
    `estado` ENUM('borrador', 'enviada a gerencia', 'aceptada gerencia', 'pospuesta', 'rechazada', 'en proceso rh', 'solicita cambios', 'cerrada') NOT NULL DEFAULT 'borrador' COMMENT 'Estado actual de la solicitud',
    `motivo_rechazo` TEXT NULL COMMENT 'Motivo del rechazo si aplica',
    `motivo_cierre` TEXT NULL COMMENT 'Motivo del cierre si aplica',
    `activo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Indica si el registro está activo',
    `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
    `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última actualización',
    
    INDEX `idx_solicitudes_codigo` (`codigo`),
    INDEX `idx_solicitudes_departamento` (`departamento_id`),
    INDEX `idx_solicitudes_sede` (`sede_id`),
    INDEX `idx_solicitudes_estado` (`estado`),
    INDEX `idx_solicitudes_solicitante` (`solicitante_id`),
    INDEX `idx_solicitudes_gerente` (`gerente_id`),
    INDEX `idx_solicitudes_activo` (`activo`),
    INDEX `idx_solicitudes_fecha_limite` (`fecha_limite_cobertura`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla para gestionar solicitudes de personal';

-- =====================================================
-- AGREGAR CLAVES FORÁNEAS DESPUÉS DE CREAR LA TABLA
-- =====================================================

-- Agregar claves foráneas
ALTER TABLE `solicitudes` 
ADD CONSTRAINT `fk_solicitudes_departamento` 
FOREIGN KEY (`departamento_id`) REFERENCES `departamentos`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `solicitudes` 
ADD CONSTRAINT `fk_solicitudes_sede` 
FOREIGN KEY (`sede_id`) REFERENCES `sedes`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `solicitudes` 
ADD CONSTRAINT `fk_solicitudes_solicitante` 
FOREIGN KEY (`solicitante_id`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `solicitudes` 
ADD CONSTRAINT `fk_solicitudes_gerente` 
FOREIGN KEY (`gerente_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- =====================================================
-- TRIGGER PARA GENERAR CÓDIGO AUTOMÁTICO
-- =====================================================

DELIMITER $$

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

DELIMITER ;

-- =====================================================
-- VISTA PARA SOLICITUDES CON INFORMACIÓN COMPLETA
-- =====================================================

CREATE OR REPLACE VIEW `v_solicitudes_completa` AS
SELECT 
    s.*,
    d.nombre AS departamento_nombre,
    sed.nombre AS sede_nombre,
    sol.nombre_completo AS solicitante_nombre,
    g.nombre_completo AS gerente_nombre,
    CASE 
        WHEN s.estado = 'borrador' THEN 'Borrador'
        WHEN s.estado = 'enviada a gerencia' THEN 'Enviada a Gerencia'
        WHEN s.estado = 'aceptada gerencia' THEN 'Aceptada por Gerencia'
        WHEN s.estado = 'pospuesta' THEN 'Pospuesta'
        WHEN s.estado = 'rechazada' THEN 'Rechazada'
        WHEN s.estado = 'en proceso rh' THEN 'En Proceso RH'
        WHEN s.estado = 'solicita cambios' THEN 'Solicita Cambios'
        WHEN s.estado = 'cerrada' THEN 'Cerrada'
        ELSE s.estado
    END AS estado_nombre,
    CASE 
        WHEN s.prioridad = 'alta' THEN 'Alta'
        WHEN s.prioridad = 'media' THEN 'Media'
        WHEN s.prioridad = 'baja' THEN 'Baja'
        ELSE s.prioridad
    END AS prioridad_nombre,
    CASE 
        WHEN s.modalidad = 'presencial' THEN 'Presencial'
        WHEN s.modalidad = 'remoto' THEN 'Remoto'
        WHEN s.modalidad = 'hibrido' THEN 'Híbrido'
        ELSE s.modalidad
    END AS modalidad_nombre
FROM `solicitudes` s
LEFT JOIN `departamentos` d ON s.departamento_id = d.id
LEFT JOIN `sedes` sed ON s.sede_id = sed.id
LEFT JOIN `usuarios` sol ON s.solicitante_id = sol.id
LEFT JOIN `usuarios` g ON s.gerente_id = g.id
WHERE s.activo = 1
ORDER BY s.creado_en DESC;

-- =====================================================
-- DATOS DE PRUEBA PARA SOLICITUDES (INSERTAR DESPUÉS DE CREAR LA TABLA)
-- =====================================================

-- Insertar solicitud 1: Desarrollador Frontend para SISTEMAS (ID: 3) en GUADALAJARA (ID: 2)
INSERT INTO `solicitudes` (
    `codigo`, `titulo`, `descripcion`, `departamento_id`, `sede_id`, 
    `perfil_puesto`, `cantidad`, `prioridad`, `modalidad`, 
    `salario_min`, `salario_max`, `fecha_limite_cobertura`, 
    `requisitos_json`, `solicitante_id`, `estado`
) VALUES (
    'SOL-2024-001',
    'Solicitud de Desarrollador Frontend',
    'Se requiere un desarrollador frontend con experiencia en React y JavaScript para el equipo de desarrollo.',
    3, 2, 'Desarrollador Frontend', 2, 'alta', 'hibrido',
    25000.00, 35000.00, '2024-12-31',
    '{"carrera": "Ingeniería en Sistemas", "area_exp": "Desarrollo Web", "nivel_educacion": "LICENCIATURA", "experiencia_minima": 2, "habilidades": ["React", "JavaScript", "HTML", "CSS"]}',
    1, 'borrador'
);

-- Insertar solicitud 2: Analista de RH para RECURSOS HUMANOS (ID: 4) en GUADALAJARA (ID: 2)
INSERT INTO `solicitudes` (
    `codigo`, `titulo`, `descripcion`, `departamento_id`, `sede_id`, 
    `perfil_puesto`, `cantidad`, `prioridad`, `modalidad`, 
    `salario_min`, `salario_max`, `fecha_limite_cobertura`, 
    `requisitos_json`, `solicitante_id`, `estado`
) VALUES (
    'SOL-2024-002',
    'Solicitud de Analista de Recursos Humanos',
    'Se busca un analista de RH para apoyar en procesos de reclutamiento y selección.',
    4, 2, 'Analista de Recursos Humanos', 1, 'media', 'presencial',
    18000.00, 25000.00, '2024-11-30',
    '{"carrera": "Administración de Empresas", "area_exp": "Recursos Humanos", "nivel_educacion": "LICENCIATURA", "experiencia_minima": 1, "habilidades": ["Reclutamiento", "Selección", "Excel", "Comunicación"]}',
    2, 'enviada a gerencia'
);

-- Insertar solicitud 3: Gerente de Ventas para VENTAS (ID: 1) en AGUASCALIENTES (ID: 3)
INSERT INTO `solicitudes` (
    `codigo`, `titulo`, `descripcion`, `departamento_id`, `sede_id`, 
    `perfil_puesto`, `cantidad`, `prioridad`, `modalidad`, 
    `salario_min`, `salario_max`, `fecha_limite_cobertura`, 
    `requisitos_json`, `solicitante_id`, `estado`
) VALUES (
    'SOL-2024-003',
    'Solicitud de Gerente de Ventas',
    'Se requiere un gerente de ventas con experiencia en el sector tecnológico.',
    1, 3, 'Gerente de Ventas', 1, 'alta', 'presencial',
    40000.00, 55000.00, '2024-10-31',
    '{"carrera": "Mercadotecnia", "area_exp": "Ventas", "nivel_educacion": "LICENCIATURA", "experiencia_minima": 5, "habilidades": ["Liderazgo", "Ventas", "Estrategia", "Gestión de equipos"]}',
    3, 'aceptada gerencia'
);

-- Insertar solicitud 4: Contador para CONTABILIDAD (ID: 5) en MONTERREY (ID: 4)
INSERT INTO `solicitudes` (
    `codigo`, `titulo`, `descripcion`, `departamento_id`, `sede_id`, 
    `perfil_puesto`, `cantidad`, `prioridad`, `modalidad`, 
    `salario_min`, `salario_max`, `fecha_limite_cobertura`, 
    `requisitos_json`, `solicitante_id`, `estado`
) VALUES (
    'SOL-2024-004',
    'Solicitud de Contador Senior',
    'Se busca un contador con experiencia en contabilidad corporativa y auditoría.',
    5, 4, 'Contador Senior', 1, 'media', 'presencial',
    22000.00, 30000.00, '2024-12-15',
    '{"carrera": "Contaduría Pública", "area_exp": "Contabilidad", "nivel_educacion": "LICENCIATURA", "experiencia_minima": 3, "habilidades": ["Contabilidad", "Auditoría", "Excel", "SAP"]}',
    4, 'borrador'
);

-- Insertar solicitud 5: Especialista en Marketing para MARKETING (ID: 6) en GUADALAJARA (ID: 2)
INSERT INTO `solicitudes` (
    `codigo`, `titulo`, `descripcion`, `departamento_id`, `sede_id`, 
    `perfil_puesto`, `cantidad`, `prioridad`, `modalidad`, 
    `salario_min`, `salario_max`, `fecha_limite_cobertura`, 
    `requisitos_json`, `solicitante_id`, `estado`
) VALUES (
    'SOL-2024-005',
    'Solicitud de Especialista en Marketing Digital',
    'Se requiere un especialista en marketing digital para estrategias de promoción y publicidad.',
    6, 2, 'Especialista en Marketing Digital', 1, 'alta', 'hibrido',
    28000.00, 38000.00, '2024-11-15',
    '{"carrera": "Mercadotecnia", "area_exp": "Marketing Digital", "nivel_educacion": "LICENCIATURA", "experiencia_minima": 2, "habilidades": ["Marketing Digital", "Redes Sociales", "Google Ads", "Analytics"]}',
    5, 'enviada a gerencia'
); 