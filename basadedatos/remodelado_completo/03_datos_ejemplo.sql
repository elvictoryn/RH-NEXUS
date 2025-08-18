-- =====================================================
-- RH-NEXUS - DATOS DE EJEMPLO PARA EL REMODELADO
-- =====================================================
-- Archivo: 03_datos_ejemplo.sql
-- Descripción: Datos de ejemplo para probar el sistema
-- Fecha: 2025-01-XX
-- =====================================================

-- USE `sistema_rh_remodelado`;

-- =====================================================
-- INSERTAR SEDES DE EJEMPLO
-- =====================================================
INSERT INTO `sedes` (`nombre`, `domicilio`, `numero`, `interior`, `colonia`, `municipio`, `estado`, `cp`, `telefono`) VALUES
('Sede Central Guadalajara', 'Av. Juárez', '652-690', '3', 'Zona Centro', 'Guadalajara', 'Jalisco', '44100', '3481255983'),
('Sede Aguascalientes', 'José María Morelos', '213', '3', 'Centro', 'Aguascalientes', 'Aguascalientes', '20000', '3334445665'),
('Sede Monterrey', 'Leona Vicario', '1123', '122', 'Colinas', 'Monterrey', 'Nuevo León', '64000', '3345654330'),
('Sede León', 'Leones', '123', '2', 'Las Américas', 'León', 'Guanajuato', '47182', '2587413697'),
('Sede Colima', 'Camino Real', '15978', '', 'Palmas', 'Colima', 'Colima', '14789', '3698521477');

-- =====================================================
-- INSERTAR DEPARTAMENTOS DE EJEMPLO
-- =====================================================
INSERT INTO `departamentos` (`nombre`, `descripcion`, `sede_id`) VALUES
('Sistemas', 'Departamento de Tecnologías de la Información y Desarrollo de Software', 1),
('Recursos Humanos', 'Gestión del capital humano y desarrollo organizacional', 1),
('Ventas', 'Comercialización y atención al cliente', 1),
('Contabilidad', 'Control financiero y contable de la empresa', 1),
('Marketing', 'Estrategias de promoción y posicionamiento de marca', 1),
('Calidad', 'Control de calidad y mejora continua', 1),
('Producción', 'Fabricación y control de procesos productivos', 1),
('Sistemas Aguascalientes', 'Soporte técnico y desarrollo regional', 2),
('Ventas Aguascalientes', 'Comercialización regional', 2),
('Contabilidad Monterrey', 'Control financiero regional', 3),
('Marketing León', 'Estrategias de promoción regional', 4),
('Producción Colima', 'Fabricación regional', 5);

-- =====================================================
-- INSERTAR USUARIOS DE EJEMPLO
-- =====================================================
INSERT INTO `usuarios` (`usuario`, `contrasena`, `rol`, `nombre_completo`, `numero_empleado`, `correo`, `sede_principal_id`, `departamento_principal_id`) VALUES
-- Administradores
('admin', '$2y$10$Td35RNEnT4e9thjrbh41Qu2Fk/gorMnkEpv8Hb0W25h/gwyTdHiea', 'admin', 'ADMINISTRADOR DEL SISTEMA', 'ADMIN001', 'admin@nexusrh.com', 1, 1),
('victor', '$2y$10$BGLTkqMHoUjnuVri.We79OkVOZDc2ydZalPrDIGxc4LZlXkIFcGwa', 'admin', 'VICTOR DANIEL GONZALEZ GARCIA', 'ADMIN002', 'victor@nexusrh.com', 1, 1),

-- Gerentes
('bryan', '$2y$10$V2144zJUUNGzO6IUCFmBCueLt1FzmgInOeSTxzXNyN/gXUYw4/7Zu', 'gerente', 'BRYAN RAMIREZ', 'GER001', 'bryan@nexusrh.com', 1, 1),
('maria_gerente', '$2y$10$6cRUKmzGeC0tjASnHrZbYed1eY2pafhI0DrP5cmxrMEM68j7Uh8Tq', 'gerente', 'MARIA DEL CARMEN GARCIA', 'GER002', 'maria@nexusrh.com', 2, 8),

-- Recursos Humanos
('luis', '$2y$10$ZtNqTmrRIEHpgQQUuXlNxuVEhQ1j.Z35xBXkSkdVkzN0044IkgRvC', 'rh', 'LUIS JESUS ESCAREÑO GARCIA', 'RH001', 'luis@nexusrh.com', 1, 2),
('armando', '$2y$10$0xP7lUBy1J8bKfJWQJB5yedTSaABQQ8bv9RbHODCPKleacaWxoyPS', 'rh', 'ARMANDO PULIDO GOMEZ', 'RH002', 'armando@nexusrh.com', 1, 1),
('rosa_rh', '$2y$10$XKwZ6DJs4Cfo1JPXJ4nS7.bWtD8d8mQ9ykHBlAE1HgFfcKjl.sAOO', 'rh', 'ROSA GARCIA', 'RH003', 'rosa@nexusrh.com', 1, 2),

-- Jefes de Área
('vero', '$2y$10$6Nsg6hfK3ACramV9oqoam.H51c/0Ki4gl2tuPWQu.eBZ90i00uHz.', 'jefe_area', 'VERONICA GONZALEZ GARCIA', 'JEF001', 'veronica@nexusrh.com', 1, 2),
('jose', '$2y$10$/k0zfiw1sslXSgomJylqEeY8rPfojwuHvMGQT4HrR/XN8djaUqxkm', 'jefe_area', 'JUAN JOSE CABRERA MARTINEZ', 'JEF002', 'jose@nexusrh.com', 3, 10),
('carlos', '$2y$10$vHgsymYW8gWwKfz87XWlPeIAIxVMfur103mTIf9Pl4xpBelMlX3y', 'jefe_area', 'CARLOS TORRES GARCIA', 'JEF003', 'carlos@nexusrh.com', 1, 3),
('monse', '$2y$10$GfJnsM1rgJqnC/SblNbGGurHT7OCb/Lwahz4TcfAyevcNUPz9EnVu', 'jefe_area', 'MONSERRAT ESCAREÑO GARCIA', 'JEF004', 'monse@nexusrh.com', 1, 7);

-- =====================================================
-- INSERTAR RELACIONES USUARIO-DEPARTAMENTO
-- =====================================================
INSERT INTO `usuarios_departamentos` (`usuario_id`, `departamento_id`, `sede_id`, `rol_en_departamento`) VALUES
-- Usuario 1 (admin) - Acceso a todo
(1, 1, 1, 'empleado'),
(1, 2, 1, 'empleado'),
(1, 3, 1, 'empleado'),
(1, 4, 1, 'empleado'),
(1, 5, 1, 'empleado'),
(1, 6, 1, 'empleado'),
(1, 7, 1, 'empleado'),

-- Usuario 2 (victor) - Admin secundario
(2, 1, 1, 'empleado'),
(2, 2, 1, 'empleado'),

-- Usuario 3 (bryan) - Gerente de Sistemas
(3, 1, 1, 'gerente'),
(3, 2, 1, 'empleado'),
(3, 3, 1, 'empleado'),

-- Usuario 4 (maria_gerente) - Gerente de Aguascalientes
(4, 8, 2, 'gerente'),
(4, 9, 2, 'empleado'),

-- Usuario 5 (luis) - RH en Recursos Humanos
(5, 2, 1, 'rh'),
(5, 1, 1, 'empleado'),

-- Usuario 6 (armando) - RH en Sistemas
(6, 1, 1, 'rh'),
(6, 2, 1, 'empleado'),

-- Usuario 7 (rosa_rh) - RH en Calidad
(7, 6, 1, 'rh'),
(7, 2, 1, 'empleado'),

-- Usuario 8 (vero) - Jefe de RH
(8, 2, 1, 'jefe_area'),
(8, 1, 1, 'empleado'),

-- Usuario 9 (jose) - Jefe de Marketing León
(9, 11, 4, 'jefe_area'),

-- Usuario 10 (carlos) - Jefe de Ventas
(10, 3, 1, 'jefe_area'),

-- Usuario 11 (monse) - Jefe de Producción
(11, 7, 1, 'jefe_area');

-- =====================================================
-- INSERTAR SOLICITUDES DE EJEMPLO
-- =====================================================
-- NOTA: Todas las solicitudes son para el departamento ID 8 (Sistemas Aguascalientes) y sede ID 2 (Sede Aguascalientes)
-- para demostrar el flujo completo de estados

INSERT INTO `solicitudes` (
    `codigo`, `titulo`, `descripcion`, `departamento_id`, `sede_id`, 
    `perfil_puesto`, `cantidad`, `prioridad`, `modalidad`, 
    `salario_min`, `salario_max`, `fecha_limite_cobertura`, 
    `requisitos_json`, `solicitante_id`, `estado`, `cambios_solicitados`, `motivo_rechazo`
) VALUES
-- Solicitud 1: Desarrollador Frontend (Borrador)
('SOL-2025-001', 'Solicitud de Desarrollador Frontend', 
'Se requiere un desarrollador frontend con experiencia en React y JavaScript para el equipo de desarrollo de Sistemas Aguascalientes.', 
8, 2, 'Desarrollador Frontend', 2, 'alta', 'hibrido', 
25000.00, 35000.00, '2025-12-31', 
'{"carrera": "Ingeniería en Sistemas", "area_exp": "Desarrollo Web", "nivel_educacion": "LICENCIATURA", "experiencia_minima": 2, "habilidades": ["React", "JavaScript", "HTML", "CSS"]}', 
8, 'borrador', NULL, NULL),

-- Solicitud 2: Desarrollador Backend (Enviada a Gerencia)
('SOL-2025-002', 'Solicitud de Desarrollador Backend', 
'Se busca un desarrollador backend con experiencia en PHP y MySQL para el equipo de desarrollo de Sistemas Aguascalientes.', 
8, 2, 'Desarrollador Backend', 1, 'alta', 'remoto', 
30000.00, 40000.00, '2025-12-20', 
'{"carrera": "Ingeniería en Sistemas", "area_exp": "Desarrollo Web", "nivel_educacion": "LICENCIATURA", "experiencia_minima": 3, "habilidades": ["PHP", "MySQL", "Laravel", "API REST"]}', 
8, 'enviada a gerencia', NULL, NULL),

-- Solicitud 3: Desarrollador Full Stack (Aceptada por Gerencia)
('SOL-2025-003', 'Solicitud de Desarrollador Full Stack', 
'Se requiere un desarrollador full stack con experiencia en tecnologías web modernas para el equipo de desarrollo de Sistemas Aguascalientes.', 
8, 2, 'Desarrollador Full Stack', 1, 'alta', 'presencial', 
35000.00, 45000.00, '2025-10-31', 
'{"carrera": "Ingeniería en Sistemas", "area_exp": "Desarrollo Web", "nivel_educacion": "LICENCIATURA", "experiencia_minima": 4, "habilidades": ["React", "Node.js", "PHP", "MySQL", "Docker"]}', 
8, 'aceptada gerencia', NULL, NULL),

-- Solicitud 4: Técnico de Soporte (En Proceso RH)
('SOL-2025-004', 'Solicitud de Técnico de Soporte', 
'Se busca un técnico de soporte técnico para atención a usuarios y mantenimiento de equipos en Sistemas Aguascalientes.', 
8, 2, 'Técnico de Soporte', 1, 'media', 'presencial', 
18000.00, 25000.00, '2025-12-15', 
'{"carrera": "Ingeniería en Sistemas", "area_exp": "Soporte Técnico", "nivel_educacion": "TECNICO", "experiencia_minima": 1, "habilidades": ["Windows", "Linux", "Redes", "Hardware", "Atención al cliente"]}', 
8, 'en proceso rh', NULL, NULL),

-- Solicitud 5: Analista de Sistemas (Solicita Cambios)
('SOL-2025-005', 'Solicitud de Analista de Sistemas', 
'Se requiere un analista de sistemas para análisis de requerimientos y diseño de soluciones en Sistemas Aguascalientes.', 
8, 2, 'Analista de Sistemas', 1, 'media', 'hibrido', 
28000.00, 38000.00, '2025-11-15', 
'{"carrera": "Ingeniería en Sistemas", "area_exp": "Análisis de Sistemas", "nivel_educacion": "LICENCIATURA", "experiencia_minima": 2, "habilidades": ["Análisis", "UML", "SQL", "Documentación", "Comunicación"]}', 
8, 'solicita cambios', 'Se requiere especificar mejor las responsabilidades del puesto y agregar requisitos de certificaciones en metodologías ágiles. También es necesario ajustar el rango salarial según el mercado local.', NULL),

-- Solicitud 6: Desarrollador Mobile (Pospuesta)
('SOL-2025-006', 'Solicitud de Desarrollador Mobile', 
'Se requiere un desarrollador mobile con experiencia en React Native para el equipo de desarrollo de Sistemas Aguascalientes.', 
8, 2, 'Desarrollador Mobile', 1, 'baja', 'remoto', 
32000.00, 42000.00, '2025-12-20', 
'{"carrera": "Ingeniería en Sistemas", "area_exp": "Desarrollo Mobile", "nivel_educacion": "LICENCIATURA", "experiencia_minima": 2, "habilidades": ["React Native", "JavaScript", "iOS", "Android", "Git"]}', 
8, 'pospuesta', NULL, NULL),

-- Solicitud 7: DevOps Engineer (Cerrada)
('SOL-2025-007', 'Solicitud de DevOps Engineer', 
'Se buscaba un DevOps Engineer para automatización de despliegues en Sistemas Aguascalientes. La posición ya fue cubierta.', 
8, 2, 'DevOps Engineer', 1, 'alta', 'hibrido', 
40000.00, 50000.00, '2025-09-30', 
'{"carrera": "Ingeniería en Sistemas", "area_exp": "DevOps", "nivel_educacion": "LICENCIATURA", "experiencia_minima": 3, "habilidades": ["Docker", "Kubernetes", "CI/CD", "AWS", "Linux"]}', 
8, 'cerrada', NULL, NULL),

-- Solicitud 8: QA Tester (Rechazada)
('SOL-2025-008', 'Solicitud de QA Tester', 
'Solicitud de QA Tester para el departamento de Sistemas Aguascalientes. Fue rechazada por restricciones presupuestarias.', 
8, 2, 'QA Tester', 1, 'media', 'presencial', 
22000.00, 30000.00, '2025-11-30', 
'{"carrera": "Ingeniería en Sistemas", "area_exp": "Testing", "nivel_educacion": "LICENCIATURA", "experiencia_minima": 1, "habilidades": ["Testing", "Selenium", "JIRA", "SQL", "Análisis"]}', 
8, 'rechazada', NULL, 'Restricciones presupuestarias para el Q4 2025. Se pospone la contratación hasta el siguiente trimestre fiscal.');

-- =====================================================
-- INSERTAR PARTICIPANTES EN SOLICITUDES
-- =====================================================
-- NOTA: Todas las solicitudes son para el departamento 8 (Sistemas Aguascalientes) y sede 2
-- Los participantes incluyen RH y gerentes relevantes para el contexto

INSERT INTO `solicitudes_participantes` (`solicitud_id`, `usuario_id`, `rol_participante`) VALUES
-- Solicitud 1 (Desarrollador Frontend - Borrador) - Solo solicitante (veronica)
-- Los triggers ya insertan automáticamente al solicitante

-- Solicitud 2 (Desarrollador Backend - Enviada a Gerencia) - Notificar a RH y gerentes
(2, 5, 'notificado'), -- luis (RH)
(2, 6, 'notificado'), -- armando (RH)
(2, 7, 'notificado'), -- rosa_rh (RH)
(2, 4, 'notificado'), -- maria_gerente (gerente de Aguascalientes)

-- Solicitud 3 (Desarrollador Full Stack - Aceptada por Gerencia) - Notificar a RH
(3, 5, 'notificado'), -- luis (RH)
(3, 6, 'notificado'), -- armando (RH)
(3, 7, 'notificado'), -- rosa_rh (RH)

-- Solicitud 4 (Técnico de Soporte - En Proceso RH) - Notificar a RH
(4, 5, 'notificado'), -- luis (RH)
(4, 6, 'notificado'), -- armando (RH)
(4, 7, 'notificado'), -- rosa_rh (RH)

-- Solicitud 5 (Analista de Sistemas - Solicita Cambios) - Notificar a RH y gerentes
(5, 5, 'notificado'), -- luis (RH)
(5, 6, 'notificado'), -- armando (RH)
(5, 7, 'notificado'), -- rosa_rh (RH)
(5, 4, 'notificado'), -- maria_gerente (gerente de Aguascalientes)

-- Solicitud 6 (Desarrollador Mobile - Pospuesta) - Notificar a RH y gerentes
(6, 5, 'notificado'), -- luis (RH)
(6, 6, 'notificado'), -- armando (RH)
(6, 7, 'notificado'), -- rosa_rh (RH)
(6, 4, 'notificado'), -- maria_gerente (gerente de Aguascalientes)

-- Solicitud 7 (DevOps Engineer - Cerrada) - Notificar a RH
(7, 5, 'notificado'), -- luis (RH)
(7, 6, 'notificado'), -- armando (RH)
(7, 7, 'notificado'), -- rosa_rh (RH)

-- Solicitud 8 (QA Tester - Rechazada) - Notificar a RH y gerentes
(8, 5, 'notificado'), -- luis (RH)
(8, 6, 'notificado'), -- armando (RH)
(8, 7, 'notificado'), -- rosa_rh (RH)
(8, 4, 'notificado'); -- maria_gerente (gerente de Aguascalientes)

-- =====================================================
-- INSERTAR HISTORIAL DE CAMBIOS
-- =====================================================
-- NOTA: Todas las solicitudes son para el departamento 8 (Sistemas Aguascalientes)
-- Los triggers ya registran automáticamente los cambios de estado
-- Aquí agregamos algunos comentarios adicionales para demostración

INSERT INTO `solicitudes_historial` (`solicitud_id`, `usuario_id`, `estado_anterior`, `estado_nuevo`, `comentario`, `tipo_cambio`) VALUES
-- Solicitud 2: Cambio de borrador a enviada a gerencia
(2, 8, 'borrador', 'enviada a gerencia', 'Solicitud enviada para revisión gerencial por veronica', 'estado'),

-- Solicitud 3: Cambio de enviada a aceptada por gerencia
(3, 4, 'enviada a gerencia', 'aceptada gerencia', 'Solicitud aprobada por maria_gerente. Se procede con el reclutamiento.', 'estado'),

-- Solicitud 4: Cambio de aceptada a en proceso RH
(4, 5, 'aceptada gerencia', 'en proceso rh', 'Iniciando proceso de reclutamiento y selección por luis', 'estado'),

-- Solicitud 5: Cambio de enviada a solicita cambios
(5, 4, 'enviada a gerencia', 'solicita cambios', 'Se solicitan cambios en los requisitos del puesto', 'estado'),

-- Solicitud 6: Cambio de enviada a pospuesta
(6, 4, 'enviada a gerencia', 'pospuesta', 'Solicitud pospuesta por restricciones temporales', 'estado'),

-- Solicitud 7: Cambio de en proceso a cerrada
(7, 5, 'en proceso rh', 'cerrada', 'Posición cubierta exitosamente. Candidato contratado por luis', 'estado'),

-- Solicitud 8: Cambio de enviada a rechazada
(8, 4, 'enviada a gerencia', 'rechazada', 'Solicitud rechazada por restricciones presupuestarias por maria_gerente', 'estado');

-- =====================================================
-- INSERTAR CANDIDATOS DE EJEMPLO
-- =====================================================
INSERT INTO `candidatos` (
    `nombre`, `curp`, `edad`, `genero`, `nivel_educacion`, `carrera`, 
    `area_experiencia`, `anos_experiencia`, `companias_previas`, 
    `distancia_sede`, `telefono`, `correo`, `direccion`, 
    `sede_id`, `departamento_id`, `estado`
) VALUES
-- Candidato 1: Desarrollador Frontend
('JUAN CARLOS LOPEZ MARTINEZ', 'LOMJ850315HJCPRN01', 38, 'Masculino', 'LICENCIATURA', 'INGENIERIA EN SISTEMAS COMPUTACIONALES', 
'Desarrollo Web Frontend', 8, 'Google México, Microsoft, StartUp Tech', 
12.5, '3312345678', 'juan.lopez@email.com', 'CALLE INDEPENDENCIA 123, COLONIA CENTRO, GUADALAJARA, JALISCO', 
1, 1, 'activo'),

-- Candidato 2: Analista de RH
('MARIA FERNANDA RODRIGUEZ GARCIA', 'ROGM920428MJCDRN02', 31, 'Femenino', 'LICENCIATURA', 'ADMINISTRACION DE EMPRESAS', 
'Recursos Humanos', 5, 'Grupo Bimbo, Walmart México, Consultora RH', 
8.2, '3323456789', 'maria.rodriguez@email.com', 'AVENIDA VALLARTA 456, COLONIA AMERICANA, GUADALAJARA, JALISCO', 
1, 2, 'activo'),

-- Candidato 3: Gerente de Ventas
('CARLOS ALBERTO HERNANDEZ LOPEZ', 'HELC780512HJCPRN03', 45, 'Masculino', 'LICENCIATURA', 'MERCADOTECNIA', 
'Ventas y Comercialización', 12, 'IBM México, Oracle, Salesforce', 
15.8, '3334567890', 'carlos.hernandez@email.com', 'CALLE LIBERTAD 789, COLONIA PROGRESO, GUADALAJARA, JALISCO', 
1, 3, 'contratado'),

-- Candidato 4: Contador
('ANA PATRICIA MORALES SANCHEZ', 'MOSA880630MJCPRN04', 35, 'Femenino', 'LICENCIATURA', 'CONTADURIA PUBLICA', 
'Contabilidad Corporativa', 7, 'Deloitte, KPMG, Empresa Industrial', 
6.7, '3345678901', 'ana.morales@email.com', 'CALLE REFORMA 321, COLONIA MODERNA, GUADALAJARA, JALISCO', 
1, 4, 'activo'),

-- Candidato 5: Especialista en Marketing
('ROBERTO EDUARDO VARGAS DIAZ', 'VADR900215HJCPRN05', 33, 'Masculino', 'LICENCIATURA', 'MERCADOTECNIA', 
'Marketing Digital', 6, 'Coca-Cola, PepsiCo, Agencia Digital', 
11.3, '3356789012', 'roberto.vargas@email.com', 'AVENIDA CHAPULTEPEC 654, COLONIA LAFEYETTE, GUADALAJARA, JALISCO', 
1, 5, 'evaluando'),

-- Candidato 6: Desarrollador Backend
('LAURA ISABEL CASTRO RUIZ', 'CARI870923MJCPRN06', 36, 'Femenino', 'LICENCIATURA', 'INGENIERIA EN SISTEMAS COMPUTACIONALES', 
'Desarrollo Web Backend', 9, 'Amazon, Netflix, Empresa Fintech', 
9.8, '3367890123', 'laura.castro@email.com', 'CALLE HIDALGO 987, COLONIA SANTA TERESA, GUADALAJARA, JALISCO', 
1, 1, 'activo'),

-- Candidato 7: Técnico de Calidad
('MIGUEL ANGEL FLORES ORTIZ', 'FLOM851107HJCPRN07', 38, 'Masculino', 'TECNICO', 'INGENIERIA INDUSTRIAL', 
'Control de Calidad', 10, 'General Motors, Ford, Empresa Automotriz', 
13.2, '3378901234', 'miguel.flores@email.com', 'CALLE MORELOS 147, COLONIA INDUSTRIAL, GUADALAJARA, JALISCO', 
1, 6, 'contratado'),

-- Candidato 8: Supervisor de Producción
('SANDRA PATRICIA REYES MARTINEZ', 'REMS820518MJCPRN08', 41, 'Femenino', 'LICENCIATURA', 'INGENIERIA INDUSTRIAL', 
'Producción y Manufactura', 11, 'Cemex, Grupo México, Empresa Minera', 
16.5, '3389012345', 'sandra.reyes@email.com', 'CALLE ZAPOPAN 258, COLONIA ZAPOPAN, GUADALAJARA, JALISCO', 
1, 7, 'rechazado');

-- =====================================================
-- ACTUALIZAR RESPONSABLES DE DEPARTAMENTOS
-- =====================================================
UPDATE `departamentos` SET `responsable_id` = 8 WHERE `id` = 2; -- RH: veronica
UPDATE `departamentos` SET `responsable_id` = 10 WHERE `id` = 3; -- Ventas: carlos
UPDATE `departamentos` SET `responsable_id` = 11 WHERE `id` = 7; -- Producción: monse

-- =====================================================
-- COMENTARIOS FINALES
-- =====================================================
/*
DATOS DE EJEMPLO INSERTADOS:

SEDES: 5 sedes en diferentes ciudades
DEPARTAMENTOS: 12 departamentos distribuidos en las sedes
USUARIOS: 11 usuarios con diferentes roles
RELACIONES: Usuarios asignados a departamentos con roles específicos
SOLICITUDES: 8 solicitudes en diferentes estados del flujo (TODAS para departamento 8 - Sistemas Aguascalientes)
PARTICIPANTES: Sistema de notificaciones configurado para RH y gerentes relevantes
HISTORIAL: Auditoría de cambios de estado con usuarios específicos
CANDIDATOS: 8 candidatos para diferentes posiciones

ESTADOS DE SOLICITUDES DEMOSTRADOS (todas para departamento 8, sede 2):
- borrador: 1 solicitud (Desarrollador Frontend)
- enviada a gerencia: 1 solicitud (Desarrollador Backend)
- aceptada gerencia: 1 solicitud (Desarrollador Full Stack)
- en proceso rh: 1 solicitud (Técnico de Soporte)
- solicita cambios: 1 solicitud (Analista de Sistemas)
- pospuesta: 1 solicitud (Desarrollador Mobile)
- cerrada: 1 solicitud (DevOps Engineer)
- rechazada: 1 solicitud (QA Tester)

ESTADOS DE CANDIDATOS DEMOSTRADOS:
- activo: 4 candidatos
- evaluando: 1 candidato
- contratado: 2 candidatos
- rechazado: 1 candidato

El sistema está listo para probar todas las funcionalidades del flujo de solicitudes
con un departamento específico (Sistemas Aguascalientes) que tiene solicitudes en todos los estados.
*/ 