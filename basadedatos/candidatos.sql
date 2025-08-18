-- =====================================================
-- ESTRUCTURA COMPLETA DE LA TABLA CANDIDATOS
-- Sistema RH-NEXUS - Corrección de estructura
-- =====================================================

-- Eliminar tabla si existe (para recrearla correctamente)
DROP TABLE IF EXISTS `candidatos`;

-- Crear tabla de candidatos con estructura completa
CREATE TABLE `candidatos` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nombre` varchar(100) NOT NULL,
    `curp` varchar(18) NOT NULL,
    `edad` int(3) NOT NULL,
    `genero` enum('Masculino','Femenino','Otro') NOT NULL,
    `nivel_educacion` varchar(50) NOT NULL,
    `carrera` varchar(100) DEFAULT NULL,
    `area_experiencia` varchar(100) NOT NULL,
    `anos_experiencia` int(2) NOT NULL,
    `companias_previas` text DEFAULT NULL,
    `distancia_sede` decimal(5,2) DEFAULT NULL,
    `telefono` varchar(20) NOT NULL,
    `correo` varchar(100) NOT NULL,
    `direccion` text NOT NULL,
    `sede_id` int(11) NOT NULL,
    `departamento_id` int(11) NOT NULL,
    `estado` enum('activo','inactivo','contratado','rechazado') DEFAULT 'activo',
    `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
    `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    
    -- Clave primaria
    PRIMARY KEY (`id`),
    
    -- Claves foráneas
    FOREIGN KEY (`sede_id`) REFERENCES `sedes`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`departamento_id`) REFERENCES `departamentos`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    
    -- Índices para optimización
    INDEX `idx_sede_departamento` (`sede_id`, `departamento_id`),
    INDEX `idx_estado` (`estado`),
    INDEX `idx_fecha_registro` (`fecha_registro`),
    INDEX `idx_curp` (`curp`),
    INDEX `idx_carrera` (`carrera`),
    INDEX `idx_area_experiencia` (`area_experiencia`),
    
    -- Restricciones de unicidad
    UNIQUE KEY `uk_curp` (`curp`),
    UNIQUE KEY `uk_correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- DATOS DE EJEMPLO PARA PRUEBAS
-- =====================================================

-- Insertar candidatos de ejemplo
INSERT INTO `candidatos` (
    `nombre`, `curp`, `edad`, `genero`, `nivel_educacion`, `carrera`, 
    `area_experiencia`, `anos_experiencia`, `companias_previas`, 
    `distancia_sede`, `telefono`, `correo`, `direccion`, 
    `sede_id`, `departamento_id`, `estado`
) VALUES
-- Candidato 1: Desarrollador Full Stack
('JUAN CARLOS PÉREZ LÓPEZ', 'PERL800101HDFXXX01', 28, 'Masculino', 'Licenciatura', 
 'INGENIERÍA EN SISTEMAS COMPUTACIONALES', 'Desarrollo Web', 5, 
 'TechCorp, DigitalSolutions, WebDev Inc.', 15.50, '5551234567', 
 'juan.perez@email.com', 'CALLE REFORMA 123, COLONIA CENTRO, CIUDAD DE MÉXICO, CDMX, 06000', 
 2, 3, 'activo'),

-- Candidato 2: Analista de RH
('MARÍA GONZÁLEZ RODRÍGUEZ', 'GORM850215MDFXXX02', 32, 'Femenino', 'Licenciatura', 
 'ADMINISTRACIÓN DE EMPRESAS', 'Recursos Humanos', 7, 
 'HR Solutions, TalentCorp, PeopleFirst', 8.75, '5559876543', 
 'maria.gonzalez@email.com', 'AVENIDA INSURGENTES 456, COLONIA DEL VALLE, CIUDAD DE MÉXICO, CDMX, 03100', 
 2, 4, 'activo'),

-- Candidato 3: Diseñador UX/UI
('CARLOS MARTÍNEZ HERNÁNDEZ', 'MAHC900320HDFXXX03', 26, 'Masculino', 'Licenciatura', 
 'DISEÑO GRÁFICO', 'Diseño de Interfaces', 3, 
 'DesignStudio, CreativeAgency, UXLab', 12.25, '5554567890', 
 'carlos.martinez@email.com', 'CALLE NAPOLES 789, COLONIA JUÁREZ, CIUDAD DE MÉXICO, CDMX, 06600', 
 2, 3, 'activo'),

-- Candidato 4: Contador Senior
('ANA LÓPEZ GARCÍA', 'LOGA870512MDFXXX04', 35, 'Femenino', 'Licenciatura', 
 'CONTADURÍA PÚBLICA', 'Contabilidad Corporativa', 10, 
 'ContaCorp, FinanceGroup, AuditPro', 6.80, '5557890123', 
 'ana.lopez@email.com', 'CALLE TUXPAN 321, COLONIA ROMA NORTE, CIUDAD DE MÉXICO, CDMX, 06700', 
 4, 5, 'activo'),

-- Candidato 5: Especialista en Marketing
('ROBERTO SÁNCHEZ DÍAZ', 'SADR920825HDFXXX05', 29, 'Masculino', 'Licenciatura', 
 'MERCADOTECNIA', 'Marketing Digital', 4, 
 'MarketingPro, BrandCorp, DigitalAgency', 18.90, '5553210987', 
 'roberto.sanchez@email.com', 'CALLE SONORA 654, COLONIA CONDESA, CIUDAD DE MÉXICO, CDMX, 06140', 
 2, 6, 'activo'),

-- Candidato 6: Ingeniero de Calidad
('LAURA TORRES VARGAS', 'TOVL880630MDFXXX06', 31, 'Femenino', 'Licenciatura', 
 'INGENIERÍA INDUSTRIAL', 'Control de Calidad', 6, 
 'QualityCorp, IndustrialPro, StandardsInc', 22.15, '5556543210', 
 'laura.torres@email.com', 'CALLE COAHUILA 987, COLONIA ESCANDÓN, CIUDAD DE MÉXICO, CDMX, 11800', 
 2, 7, 'activo'),

-- Candidato 7: Vendedor Senior
('MIGUEL ÁNGEL FLORES RUIZ', 'FLRM850715HDFXXX07', 33, 'Masculino', 'Licenciatura', 
 'ADMINISTRACIÓN DE EMPRESAS', 'Ventas', 8, 
 'SalesCorp, BusinessPro, TradeGroup', 11.45, '5550987654', 
 'miguel.flores@email.com', 'CALLE TAMAULIPAS 147, COLONIA CONDESA, CIUDAD DE MÉXICO, CDMX, 06140', 
 3, 1, 'activo'),

-- Candidato 8: Operador de Producción
('PATRICIA JIMÉNEZ MORALES', 'JIMP890920MDFXXX08', 27, 'Femenino', 'Técnico Superior', 
 'TÉCNICO EN PRODUCCIÓN INDUSTRIAL', 'Operaciones', 2, 
 'ProductionCorp, IndustrialOps, FactoryPro', 25.80, '5555432109', 
 'patricia.jimenez@email.com', 'CALLE VERACRUZ 258, COLONIA NÁPOLES, CIUDAD DE MÉXICO, CDMX, 03810', 
 2, 8, 'activo'),

-- Candidato 9: Desarrollador Frontend
('DIEGO HERRERA CASTRO', 'HECD910315HDFXXX09', 25, 'Masculino', 'Licenciatura', 
 'INGENIERÍA EN SISTEMAS COMPUTACIONALES', 'Desarrollo Frontend', 2, 
 'FrontendCorp, WebDev, UIStudio', 16.70, '5558765432', 
 'diego.herrera@email.com', 'CALLE CHIHUAHUA 369, COLONIA ROMA SUR, CIUDAD DE MÉXICO, CDMX, 06760', 
 2, 3, 'activo'),

-- Candidato 10: Asistente de RH
('SOFÍA VARGAS MENDOZA', 'VAMS880425MDFXXX10', 30, 'Femenino', 'Licenciatura', 
 'PSICOLOGÍA ORGANIZACIONAL', 'Recursos Humanos', 5, 
 'HR Assistant, PeopleCorp, TalentPro', 9.30, '5552345678', 
 'sofia.vargas@email.com', 'CALLE TABASCO 741, COLONIA DEL VALLE, CIUDAD DE MÉXICO, CDMX, 03100', 
 2, 4, 'activo');

-- =====================================================
-- VERIFICACIÓN DE LA ESTRUCTURA
-- =====================================================

-- Verificar que la tabla se creó correctamente
DESCRIBE `candidatos`;

-- Verificar que los datos se insertaron
SELECT COUNT(*) as total_candidatos FROM `candidatos`;

-- Verificar candidatos por departamento
SELECT 
    d.nombre as departamento,
    COUNT(c.id) as total_candidatos
FROM candidatos c
INNER JOIN departamentos d ON c.departamento_id = d.id
WHERE c.estado = 'activo'
GROUP BY d.nombre
ORDER BY total_candidatos DESC;

-- Verificar candidatos por sede
SELECT 
    s.nombre as sede,
    COUNT(c.id) as total_candidatos
FROM candidatos c
INNER JOIN sedes s ON c.sede_id = s.id
WHERE c.estado = 'activo'
GROUP BY s.nombre
ORDER BY total_candidatos DESC;

-- =====================================================
-- NOTAS IMPORTANTES
-- =====================================================

/*
ESTRUCTURA CORREGIDA:

1. CLAVE PRIMARIA:
   - id: AUTO_INCREMENT para identificación única

2. CLAVES FORÁNEAS:
   - sede_id: Referencia a tabla sedes
   - departamento_id: Referencia a tabla departamentos

3. RESTRICCIONES:
   - ON DELETE RESTRICT: Evita eliminar sedes/departamentos con candidatos
   - ON UPDATE CASCADE: Actualiza referencias si cambia el ID

4. ÍNDICES OPTIMIZADOS:
   - sede_id + departamento_id: Para consultas por contexto
   - estado: Para filtrar por estado
   - fecha_registro: Para ordenamiento temporal
   - curp: Para búsquedas por CURP
   - carrera: Para filtros por carrera
   - area_experiencia: Para filtros por área

5. RESTRICCIONES DE UNICIDAD:
   - CURP: Evita duplicados de identificación
   - Correo: Evita duplicados de contacto

6. DATOS DE EJEMPLO:
   - 10 candidatos con perfiles variados
   - Distribuidos en diferentes sedes y departamentos
   - Información realista para pruebas

ESTA ESTRUCTURA PERMITE:
- Relación correcta con solicitudes
- Consultas eficientes por contexto
- Validación de integridad referencial
- Escalabilidad para futuras funcionalidades
*/ 
