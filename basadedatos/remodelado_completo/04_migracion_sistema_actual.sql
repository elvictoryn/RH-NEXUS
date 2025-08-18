-- =====================================================
-- RH-NEXUS - MIGRACIÓN DESDE SISTEMA ACTUAL
-- =====================================================
-- Archivo: 04_migracion_sistema_actual.sql
-- Descripción: Script para migrar datos del sistema actual al remodelado
-- Fecha: 2025-01-XX
-- =====================================================

-- IMPORTANTE: Este archivo debe ejecutarse DESPUÉS de crear la nueva estructura
-- y solo si deseas migrar datos del sistema actual

-- USE `sistema_rh_remodelado`;

-- =====================================================
-- FUNCIÓN PARA MIGRAR DATOS EXISTENTES
-- =====================================================

DELIMITER $$

-- Procedimiento para migrar sedes existentes
CREATE PROCEDURE `MigrarSedesExistentes`()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE sede_id_old INT;
    DECLARE sede_nombre VARCHAR(100);
    DECLARE sede_domicilio VARCHAR(150);
    DECLARE sede_numero VARCHAR(10);
    DECLARE sede_interior VARCHAR(10);
    DECLARE sede_colonia VARCHAR(100);
    DECLARE sede_municipio VARCHAR(100);
    DECLARE sede_estado VARCHAR(100);
    DECLARE sede_cp VARCHAR(10);
    DECLARE sede_telefono VARCHAR(20);
    
    -- Cursor para leer sedes del sistema actual
    DECLARE cur CURSOR FOR
        SELECT id, nombre, domicilio, numero, interior, colonia, municipio, estado, cp, telefono
        FROM sistema_rh.sedes 
        WHERE activo = 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO sede_id_old, sede_nombre, sede_domicilio, sede_numero, sede_interior, 
                     sede_colonia, sede_municipio, sede_estado, sede_cp, sede_telefono;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Insertar en nueva tabla de sedes
        INSERT INTO sedes (nombre, domicilio, numero, interior, colonia, municipio, estado, cp, telefono)
        VALUES (sede_nombre, sede_domicilio, sede_numero, sede_interior, sede_colonia, 
                sede_municipio, sede_estado, sede_cp, sede_telefono);
        
    END LOOP;
    
    CLOSE cur;
    
    SELECT 'Migración de sedes completada' as resultado;
END$$

-- Procedimiento para migrar departamentos existentes
CREATE PROCEDURE `MigrarDepartamentosExistentes`()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE dept_id_old INT;
    DECLARE dept_nombre VARCHAR(100);
    DECLARE dept_descripcion TEXT;
    DECLARE dept_sede_id_old INT;
    DECLARE dept_sede_id_new INT;
    
    -- Cursor para leer departamentos del sistema actual
    DECLARE cur CURSOR FOR
        SELECT d.id, d.nombre, d.descripcion, d.sede_id
        FROM sistema_rh.departamentos d
        INNER JOIN sistema_rh.sedes s ON d.sede_id = s.id
        WHERE d.estado = 1 AND s.activo = 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO dept_id_old, dept_nombre, dept_descripcion, dept_sede_id_old;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Obtener el nuevo ID de sede
        SELECT id INTO dept_sede_id_new
        FROM sedes 
        WHERE nombre = (SELECT nombre FROM sistema_rh.sedes WHERE id = dept_sede_id_old);
        
        -- Insertar en nueva tabla de departamentos
        INSERT INTO departamentos (nombre, descripcion, sede_id)
        VALUES (dept_nombre, dept_descripcion, dept_sede_id_new);
        
    END LOOP;
    
    CLOSE cur;
    
    SELECT 'Migración de departamentos completada' as resultado;
END$$

-- Procedimiento para migrar usuarios existentes
CREATE PROCEDURE `MigrarUsuariosExistentes`()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE user_id_old INT;
    DECLARE user_usuario VARCHAR(50);
    DECLARE user_contrasena VARCHAR(255);
    DECLARE user_rol VARCHAR(20);
    DECLARE user_nombre_completo VARCHAR(100);
    DECLARE user_numero_empleado VARCHAR(20);
    DECLARE user_correo VARCHAR(100);
    DECLARE user_fotografia VARCHAR(255);
    DECLARE user_sede_old VARCHAR(100);
    DECLARE user_departamento_old VARCHAR(100);
    DECLARE user_sede_id_new INT;
    DECLARE user_departamento_id_new INT;
    
    -- Cursor para leer usuarios del sistema actual
    DECLARE cur CURSOR FOR
        SELECT id, usuario, contrasena, rol, nombre_completo, numero_empleado, correo, fotografia, sede, departamento
        FROM sistema_rh.usuarios 
        WHERE estado = 'activo';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO user_id_old, user_usuario, user_contrasena, user_rol, user_nombre_completo, 
                     user_numero_empleado, user_correo, user_fotografia, user_sede_old, user_departamento_old;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Obtener nuevos IDs de sede y departamento
        SELECT id INTO user_sede_id_new
        FROM sedes 
        WHERE nombre = user_sede_old;
        
        SELECT id INTO user_departamento_id_new
        FROM departamentos 
        WHERE nombre = user_departamento_old;
        
        -- Insertar en nueva tabla de usuarios
        INSERT INTO usuarios (usuario, contrasena, rol, nombre_completo, numero_empleado, correo, fotografia, sede_principal_id, departamento_principal_id)
        VALUES (user_usuario, user_contrasena, user_rol, user_nombre_completo, user_numero_empleado, 
                user_correo, user_fotografia, user_sede_id_new, user_departamento_id_new);
        
    END LOOP;
    
    CLOSE cur;
    
    SELECT 'Migración de usuarios completada' as resultado;
END$$

-- Procedimiento para migrar candidatos existentes
CREATE PROCEDURE `MigrarCandidatosExistentes`()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE cand_id_old INT;
    DECLARE cand_nombre VARCHAR(255);
    DECLARE cand_curp VARCHAR(18);
    DECLARE cand_edad INT;
    DECLARE cand_genero VARCHAR(20);
    DECLARE cand_nivel_educacion VARCHAR(50);
    DECLARE cand_carrera VARCHAR(255);
    DECLARE cand_area_experiencia VARCHAR(255);
    DECLARE cand_anos_experiencia INT;
    DECLARE cand_companias_previas TEXT;
    DECLARE cand_distancia_sede DECIMAL(8,2);
    DECLARE cand_telefono VARCHAR(15);
    DECLARE cand_correo VARCHAR(255);
    DECLARE cand_direccion TEXT;
    DECLARE cand_sede_id_old INT;
    DECLARE cand_departamento_id_old INT;
    DECLARE cand_sede_id_new INT;
    DECLARE cand_departamento_id_new INT;
    
    -- Cursor para leer candidatos del sistema actual
    DECLARE cur CURSOR FOR
        SELECT c.id, c.nombre, c.curp, c.edad, c.genero, c.nivel_educacion, c.carrera, 
               c.area_experiencia, c.anos_experiencia, c.companias_previas, c.distancia_sede,
               c.telefono, c.correo, c.direccion, c.sede_id, c.departamento_id
        FROM sistema_rh.candidatos c
        WHERE c.estado != 'inactivo';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO cand_id_old, cand_nombre, cand_curp, cand_edad, cand_genero, cand_nivel_educacion,
                     cand_carrera, cand_area_experiencia, cand_anos_experiencia, cand_companias_previas,
                     cand_distancia_sede, cand_telefono, cand_correo, cand_direccion, cand_sede_id_old, cand_departamento_id_old;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Obtener nuevos IDs de sede y departamento
        SELECT id INTO cand_sede_id_new
        FROM sedes 
        WHERE id = cand_sede_id_old;
        
        SELECT id INTO cand_departamento_id_new
        FROM departamentos 
        WHERE id = cand_departamento_id_old;
        
        -- Insertar en nueva tabla de candidatos
        INSERT INTO candidatos (nombre, curp, edad, genero, nivel_educacion, carrera, area_experiencia,
                               anos_experiencia, companias_previas, distancia_sede, telefono, correo, direccion,
                               sede_id, departamento_id, estado)
        VALUES (cand_nombre, cand_curp, cand_edad, cand_genero, cand_nivel_educacion, cand_carrera,
                cand_area_experiencia, cand_anos_experiencia, cand_companias_previas, cand_distancia_sede,
                cand_telefono, cand_correo, cand_direccion, cand_sede_id_new, cand_departamento_id_new, 'activo');
        
    END LOOP;
    
    CLOSE cur;
    
    SELECT 'Migración de candidatos completada' as resultado;
END$$

-- Procedimiento para crear relaciones usuario-departamento basadas en datos existentes
CREATE PROCEDURE `CrearRelacionesUsuarioDepartamento`()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE user_id_new INT;
    DECLARE user_rol VARCHAR(20);
    DECLARE dept_id_new INT;
    DECLARE sede_id_new INT;
    DECLARE rol_en_dept VARCHAR(20);
    
    -- Cursor para leer usuarios con sus departamentos principales
    DECLARE cur CURSOR FOR
        SELECT u.id, u.rol, u.departamento_principal_id, u.sede_principal_id
        FROM usuarios u
        WHERE u.departamento_principal_id IS NOT NULL AND u.sede_principal_id IS NOT NULL;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO user_id_new, user_rol, dept_id_new, sede_id_new;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Determinar rol en departamento basado en rol principal
        CASE user_rol
            WHEN 'jefe_area' THEN SET rol_en_dept = 'jefe_area';
            WHEN 'gerente' THEN SET rol_en_dept = 'gerente';
            WHEN 'rh' THEN SET rol_en_dept = 'rh';
            ELSE SET rol_en_dept = 'empleado';
        END CASE;
        
        -- Insertar relación usuario-departamento
        INSERT INTO usuarios_departamentos (usuario_id, departamento_id, sede_id, rol_en_departamento)
        VALUES (user_id_new, dept_id_new, sede_id_new, rol_en_dept);
        
    END LOOP;
    
    CLOSE cur;
    
    SELECT 'Relaciones usuario-departamento creadas' as resultado;
END$$

-- Procedimiento principal de migración
CREATE PROCEDURE `MigrarSistemaCompleto`()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Ejecutar migraciones en orden
    CALL MigrarSedesExistentes();
    CALL MigrarDepartamentosExistentes();
    CALL MigrarUsuariosExistentes();
    CALL MigrarCandidatosExistentes();
    CALL CrearRelacionesUsuarioDepartamento();
    
    COMMIT;
    
    SELECT 'Migración completa exitosa' as resultado;
END$$

DELIMITER ;

-- =====================================================
-- INSTRUCCIONES DE MIGRACIÓN
-- =====================================================

/*
INSTRUCCIONES PARA MIGRAR DESDE EL SISTEMA ACTUAL:

1. PRIMERO: Crear la nueva base de datos con los archivos 01, 02 y 03
   - Esto creará la estructura nueva con datos de ejemplo

2. SEGUNDO: Si deseas migrar datos existentes, ejecutar:
   CALL MigrarSistemaCompleto();

3. TERCERO: Verificar la migración:
   - Revisar que los datos se hayan migrado correctamente
   - Verificar que las relaciones estén intactas
   - Confirmar que los usuarios puedan acceder

NOTAS IMPORTANTES:
- La migración asume que existe una base de datos llamada 'sistema_rh'
- Los datos existentes se copiarán a la nueva estructura
- Las relaciones se recrearán automáticamente
- Los usuarios mantendrán sus credenciales originales

ADVERTENCIAS:
- Hacer backup antes de ejecutar la migración
- Verificar que no haya conflictos de datos
- Probar en ambiente de desarrollo primero
*/

-- =====================================================
-- LIMPIAR PROCEDIMIENTOS DE MIGRACIÓN (OPCIONAL)
-- =====================================================

/*
-- Después de la migración, puedes eliminar estos procedimientos:
DROP PROCEDURE IF EXISTS MigrarSedesExistentes;
DROP PROCEDURE IF EXISTS MigrarDepartamentosExistentes;
DROP PROCEDURE IF EXISTS MigrarUsuariosExistentes;
DROP PROCEDURE IF EXISTS MigrarCandidatosExistentes;
DROP PROCEDURE IF EXISTS CrearRelacionesUsuarioDepartamento;
DROP PROCEDURE IF EXISTS MigrarSistemaCompleto;
*/ 