-- =====================================================
-- RH-NEXUS - REMODELADO COMPLETO DE BASE DE DATOS
-- =====================================================
-- Archivo: 01_estructura_base.sql
-- Descripción: Estructura base de la base de datos remodelada
-- Fecha: 2025-01-XX
-- =====================================================

-- Crear base de datos si no existe
-- CREATE DATABASE IF NOT EXISTS `sistema_rh_remodelado` 
-- CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- USE `sistema_rh_remodelado`;

-- =====================================================
-- TABLA: SEDES (Entidad base)
-- =====================================================
CREATE TABLE `sedes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL UNIQUE,
    `domicilio` VARCHAR(150) NOT NULL,
    `numero` VARCHAR(10) NOT NULL,
    `interior` VARCHAR(10) NULL,
    `colonia` VARCHAR(100) NOT NULL,
    `municipio` VARCHAR(100) NOT NULL,
    `estado` VARCHAR(100) NOT NULL,
    `cp` VARCHAR(10) NOT NULL,
    `telefono` VARCHAR(20) NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_sedes_nombre` (`nombre`),
    INDEX `idx_sedes_activo` (`activo`),
    INDEX `idx_sedes_municipio` (`municipio`),
    INDEX `idx_sedes_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Sedes físicas de la empresa';

-- =====================================================
-- TABLA: DEPARTAMENTOS (Entidad base)
-- =====================================================
CREATE TABLE `departamentos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` TEXT NOT NULL,
    `sede_id` INT NOT NULL,
    `responsable_id` INT NULL,
    `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
    `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_departamentos_sede` (`sede_id`),
    INDEX `idx_departamentos_responsable` (`responsable_id`),
    INDEX `idx_departamentos_estado` (`estado`),
    INDEX `idx_departamentos_nombre` (`nombre`),
    
    CONSTRAINT `fk_departamentos_sede` 
        FOREIGN KEY (`sede_id`) REFERENCES `sedes`(`id`) 
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Departamentos organizacionales por sede';

-- =====================================================
-- TABLA: USUARIOS (Entidad base)
-- =====================================================
CREATE TABLE `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario` VARCHAR(50) NOT NULL UNIQUE,
    `contrasena` VARCHAR(255) NOT NULL,
    `rol` ENUM('admin', 'rh', 'gerente', 'jefe_area') NOT NULL,
    `nombre_completo` VARCHAR(100) NOT NULL,
    `numero_empleado` VARCHAR(20) NOT NULL UNIQUE,
    `correo` VARCHAR(100) NOT NULL UNIQUE,
    `estado` ENUM('activo', 'inactivo') DEFAULT 'activo',
    `fotografia` VARCHAR(255) NULL,
    `sede_principal_id` INT NULL,
    `departamento_principal_id` INT NULL,
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_usuarios_rol` (`rol`),
    INDEX `idx_usuarios_estado` (`estado`),
    INDEX `idx_usuarios_sede_principal` (`sede_principal_id`),
    INDEX `idx_usuarios_departamento_principal` (`departamento_principal_id`),
    
    CONSTRAINT `fk_usuarios_sede_principal` 
        FOREIGN KEY (`sede_principal_id`) REFERENCES `sedes`(`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_usuarios_departamento_principal` 
        FOREIGN KEY (`departamento_principal_id`) REFERENCES `departamentos`(`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Usuarios del sistema con roles y permisos';

-- =====================================================
-- TABLA: USUARIOS_DEPARTAMENTOS (Tabla intermedia)
-- =====================================================
CREATE TABLE `usuarios_departamentos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `departamento_id` INT NOT NULL,
    `sede_id` INT NOT NULL,
    `rol_en_departamento` ENUM('jefe_area', 'gerente', 'rh', 'empleado') NOT NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_usuarios_dept_usuario` (`usuario_id`),
    INDEX `idx_usuarios_dept_departamento` (`departamento_id`),
    INDEX `idx_usuarios_dept_sede` (`sede_id`),
    INDEX `idx_usuarios_dept_rol` (`rol_en_departamento`),
    INDEX `idx_usuarios_dept_activo` (`activo`),
    
    CONSTRAINT `fk_usuarios_dept_usuario` 
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_usuarios_dept_departamento` 
        FOREIGN KEY (`departamento_id`) REFERENCES `departamentos`(`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_usuarios_dept_sede` 
        FOREIGN KEY (`sede_id`) REFERENCES `sedes`(`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    
    UNIQUE KEY `uk_usuario_dept_sede` (`usuario_id`, `departamento_id`, `sede_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Relación muchos a muchos entre usuarios y departamentos con roles específicos';

-- =====================================================
-- TABLA: SOLICITUDES (Entidad principal)
-- =====================================================
CREATE TABLE `solicitudes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo` VARCHAR(20) NOT NULL UNIQUE,
    `titulo` VARCHAR(255) NOT NULL,
    `descripcion` TEXT NOT NULL,
    `departamento_id` INT NOT NULL,
    `sede_id` INT NOT NULL,
    `perfil_puesto` VARCHAR(255) NOT NULL,
    `cantidad` INT NOT NULL DEFAULT 1,
    `prioridad` ENUM('alta', 'media', 'baja') NOT NULL DEFAULT 'media',
    `modalidad` ENUM('presencial', 'remoto', 'hibrido') NOT NULL DEFAULT 'presencial',
    `salario_min` DECIMAL(10,2) NULL,
    `salario_max` DECIMAL(10,2) NULL,
    `fecha_limite_cobertura` DATE NULL,
    `requisitos_json` JSON NULL,
    `solicitante_id` INT NOT NULL,
    `gerente_id` INT NULL,
    `estado` ENUM('borrador', 'enviada a gerencia', 'aceptada gerencia', 'pospuesta', 'rechazada', 'en proceso rh', 'solicita cambios', 'cerrada') NOT NULL DEFAULT 'borrador',
    `motivo_rechazo` TEXT NULL,
    `motivo_cierre` TEXT NULL,
    `cambios_solicitados` TEXT NULL COMMENT 'Cambios solicitados por gerentes o RH',
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    `creado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_solicitudes_codigo` (`codigo`),
    INDEX `idx_solicitudes_departamento` (`departamento_id`),
    INDEX `idx_solicitudes_sede` (`sede_id`),
    INDEX `idx_solicitudes_estado` (`estado`),
    INDEX `idx_solicitudes_solicitante` (`solicitante_id`),
    INDEX `idx_solicitudes_gerente` (`gerente_id`),
    INDEX `idx_solicitudes_activo` (`activo`),
    INDEX `idx_solicitudes_fecha_limite` (`fecha_limite_cobertura`),
    INDEX `idx_solicitudes_prioridad` (`prioridad`),
    INDEX `idx_solicitudes_cambios` (`cambios_solicitados`(100)),
    
    CONSTRAINT `fk_solicitudes_departamento` 
        FOREIGN KEY (`departamento_id`) REFERENCES `departamentos`(`id`) 
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_solicitudes_sede` 
        FOREIGN KEY (`sede_id`) REFERENCES `sedes`(`id`) 
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_solicitudes_solicitante` 
        FOREIGN KEY (`solicitante_id`) REFERENCES `usuarios`(`id`) 
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_solicitudes_gerente` 
        FOREIGN KEY (`gerente_id`) REFERENCES `usuarios`(`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Solicitudes de personal con flujo de aprobación y cambios solicitados';

-- =====================================================
-- TABLA: SOLICITUDES_PARTICIPANTES (Notificaciones)
-- =====================================================
CREATE TABLE `solicitudes_participantes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `solicitud_id` INT NOT NULL,
    `usuario_id` INT NOT NULL,
    `rol_participante` ENUM('notificado', 'aprobador', 'revisor', 'solicitante') NOT NULL,
    `fecha_notificacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `leido` TINYINT(1) NOT NULL DEFAULT 0,
    `fecha_lectura` TIMESTAMP NULL,
    `activo` TINYINT(1) NOT NULL DEFAULT 1,
    
    INDEX `idx_solicitudes_part_solicitud` (`solicitud_id`),
    INDEX `idx_solicitudes_part_usuario` (`usuario_id`),
    INDEX `idx_solicitudes_part_rol` (`rol_participante`),
    INDEX `idx_solicitudes_part_leido` (`leido`),
    INDEX `idx_solicitudes_part_activo` (`activo`),
    
    CONSTRAINT `fk_solicitudes_part_solicitud` 
        FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes`(`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_solicitudes_part_usuario` 
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    
    UNIQUE KEY `uk_solicitud_usuario` (`solicitud_id`, `usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Participantes y notificaciones de solicitudes';

-- =====================================================
-- TABLA: SOLICITUDES_HISTORIAL (Auditoría)
-- =====================================================
CREATE TABLE `solicitudes_historial` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `solicitud_id` INT NOT NULL,
    `usuario_id` INT NOT NULL,
    `estado_anterior` VARCHAR(50) NULL,
    `estado_nuevo` VARCHAR(50) NOT NULL,
    `comentario` TEXT NULL,
    `fecha_cambio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `tipo_cambio` ENUM('estado', 'comentario', 'asignacion', 'otro') NOT NULL DEFAULT 'estado',
    
    INDEX `idx_solicitudes_hist_solicitud` (`solicitud_id`),
    INDEX `idx_solicitudes_hist_usuario` (`usuario_id`),
    INDEX `idx_solicitudes_hist_fecha` (`fecha_cambio`),
    INDEX `idx_solicitudes_hist_tipo` (`tipo_cambio`),
    
    CONSTRAINT `fk_solicitudes_hist_solicitud` 
        FOREIGN KEY (`solicitud_id`) REFERENCES `solicitudes`(`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_solicitudes_hist_usuario` 
        FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Historial completo de cambios en solicitudes';

-- =====================================================
-- TABLA: CANDIDATOS (Entidad existente mejorada)
-- =====================================================
CREATE TABLE `candidatos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(255) NOT NULL,
    `curp` VARCHAR(18) NOT NULL UNIQUE,
    `edad` INT NOT NULL,
    `genero` ENUM('Masculino', 'Femenino', 'Otro') NOT NULL,
    `nivel_educacion` ENUM('PRIMARIA', 'SECUNDARIA', 'PREPARATORIA', 'TECNICO', 'LICENCIATURA', 'INGENIERIA', 'MAESTRIA', 'DOCTORADO') NOT NULL,
    `carrera` VARCHAR(255) NULL,
    `area_experiencia` VARCHAR(255) NOT NULL,
    `anos_experiencia` INT NOT NULL DEFAULT 0,
    `companias_previas` TEXT NULL,
    `distancia_sede` DECIMAL(8,2) NULL,
    `telefono` VARCHAR(15) NOT NULL,
    `correo` VARCHAR(255) NOT NULL,
    `direccion` TEXT NOT NULL,
    `sede_id` INT NOT NULL,
    `departamento_id` INT NOT NULL,
    `estado` ENUM('activo', 'evaluando', 'contratado', 'rechazado', 'inactivo') NOT NULL DEFAULT 'activo',
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_candidatos_curp` (`curp`),
    INDEX `idx_candidatos_estado` (`estado`),
    INDEX `idx_candidatos_sede` (`sede_id`),
    INDEX `idx_candidatos_departamento` (`departamento_id`),
    INDEX `idx_candidatos_fecha_registro` (`fecha_registro`),
    
    CONSTRAINT `fk_candidatos_sede` 
        FOREIGN KEY (`sede_id`) REFERENCES `sedes`(`id`) 
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_candidatos_departamento` 
        FOREIGN KEY (`departamento_id`) REFERENCES `departamentos`(`id`) 
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Candidatos registrados en el sistema';

-- =====================================================
-- AGREGAR CLAVES FORÁNEAS RESTANTES
-- =====================================================

-- Agregar clave foránea para responsable de departamento
ALTER TABLE `departamentos` 
ADD CONSTRAINT `fk_departamentos_responsable` 
FOREIGN KEY (`responsable_id`) REFERENCES `usuarios`(`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- =====================================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- =====================================================

-- Índices compuestos para consultas frecuentes
CREATE INDEX `idx_usuarios_dept_rol_activo` ON `usuarios_departamentos` (`rol_en_departamento`, `activo`);
CREATE INDEX `idx_solicitudes_estado_activo` ON `solicitudes` (`estado`, `activo`);
CREATE INDEX `idx_solicitudes_dept_sede_estado` ON `solicitudes` (`departamento_id`, `sede_id`, `estado`);
CREATE INDEX `idx_candidatos_sede_dept_estado` ON `candidatos` (`sede_id`, `departamento_id`, `estado`);

-- =====================================================
-- COMENTARIOS FINALES
-- =====================================================
/*
ESTRUCTURA COMPLETA DEL REMODELADO:

1. SEDES: Entidad base para ubicaciones físicas
2. DEPARTAMENTOS: Estructura organizacional por sede
3. USUARIOS: Usuarios del sistema con roles principales
4. USUARIOS_DEPARTAMENTOS: Relación muchos a muchos con roles específicos
5. SOLICITUDES: Solicitudes de personal con flujo completo
6. SOLICITUDES_PARTICIPANTES: Sistema de notificaciones
7. SOLICITUDES_HISTORIAL: Auditoría completa de cambios
8. CANDIDATOS: Gestión de candidatos mejorada

BENEFICIOS DEL REMODELADO:
- Relaciones sólidas y auditables
- Control de acceso granular por departamento
- Sistema de notificaciones eficiente
- Historial completo de cambios
- Escalabilidad para futuras funcionalidades
*/ 