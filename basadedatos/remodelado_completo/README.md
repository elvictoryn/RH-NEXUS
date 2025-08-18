# Sistema RH Remodelado - Base de Datos Completa

## 📋 Descripción
Base de datos completamente remodelada para el sistema de Recursos Humanos (RH-NEXUS) con un flujo completo de solicitudes de personal, incluyendo el nuevo sistema de "Solicitar Cambios" entre gerentes y jefes de área.

## 🗂️ Estructura de Archivos

### **Archivos Principales (Importar en este orden):**

1. **`01_estructura_base.sql`** - Estructura completa de la base de datos
   - Tablas: `sedes`, `departamentos`, `usuarios`, `usuarios_departamentos`, `solicitudes`, `solicitudes_participantes`, `solicitudes_historial`, `candidatos`
   - **NUEVO:** Campo `cambios_solicitados` en tabla `solicitudes`
   - **NUEVO:** Campo `motivo_rechazo` en tabla `solicitudes`
   - Índices y restricciones de integridad referencial

2. **`02_triggers_procedimientos.sql`** - Automatización y lógica de negocio
   - Triggers para generación automática de códigos
   - Triggers para notificaciones automáticas
   - Procedimientos almacenados para operaciones complejas
   - **ACTUALIZADO:** Vista `v_solicitudes_completa` con campo `cambios_solicitados`
   - Vistas para consultas optimizadas

3. **`03_datos_ejemplo.sql`** - Datos de prueba completos
   - Usuarios con roles específicos (admin, rh, gerente, jefe_area)
   - Solicitudes en todos los estados posibles
   - **NUEVO:** Solicitud con `cambios_solicitados` (estado: "solicita cambios")
   - **NUEVO:** Solicitud con `motivo_rechazo` (estado: "rechazada")
   - Candidatos de ejemplo
   - Historial de cambios y participantes

4. **`04_migracion_sistema_actual.sql`** - Migración desde sistema anterior
   - Procedimientos para migrar datos existentes
   - Conversión de roles y relaciones
   - **OPCIONAL:** Solo si tienes datos existentes

### **Archivos de Actualización (Para bases existentes):**

5. **`05_ALTER_TABLE_simple.sql`** - Agregar campos a tabla existente
   - Agrega `cambios_solicitados` a tabla `solicitudes`
   - Crea índice para optimizar consultas
   - **USO:** Si ya tienes la base creada y solo quieres agregar los nuevos campos

6. **`06_UPDATE_vista_solicitudes.sql`** - Actualizar vista existente
   - Recrea la vista `v_solicitudes_completa` con todos los campos
   - **USO:** Si ya tienes la base creada y quieres actualizar solo la vista

## 🚀 Instalación desde Cero

### **Opción 1: Instalación Completa (Recomendada)**
```sql
-- 1. Crear base de datos
CREATE DATABASE sistema_rh_remodelado CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Seleccionar base de datos
USE sistema_rh_remodelado;

-- 3. Importar archivos en orden:
--    - 01_estructura_base.sql
--    - 02_triggers_procedimientos.sql  
--    - 03_datos_ejemplo.sql
```

### **Opción 2: Solo Actualización de Campos**
```sql
-- Si ya tienes la base creada:
USE sistema_rh_remodelado;

-- Ejecutar solo:
-- 05_ALTER_TABLE_simple.sql
-- 06_UPDATE_vista_solicitudes.sql
```

## 🔑 Credenciales de Acceso

### **Usuarios de Prueba:**

| Usuario | Contraseña | Rol | Sede | Departamento |
|---------|------------|-----|------|---------------|
| `admin` | `admin123` | Administrador | Todas | Todos |
| `veronica` | `veronica123` | Jefe de Área | Aguascalientes | Sistemas |
| `maria_gerente` | `maria123` | Gerente | Aguascalientes | Todas |
| `luis` | `luis123` | RH | Guadalajara | Recursos Humanos |
| `armando` | `armando123` | RH | Guadalajara | Sistemas |

## 🆕 Nuevas Funcionalidades

### **Sistema de "Solicitar Cambios":**

#### **Flujo Completo:**
1. **Jefe de Área** crea solicitud → Estado: `borrador`
2. **Jefe de Área** envía a gerencia → Estado: `enviada a gerencia`
3. **Gerente** revisa y solicita cambios → Estado: `solicita cambios`
4. **Jefe de Área** ve cambios solicitados y edita
5. **Jefe de Área** reenvía a gerencia → Estado: `enviada a gerencia`
6. **Gerente** aprueba → Estado: `aceptada gerencia`
7. **RH** procesa → Estado: `en proceso rh`

#### **Campos Nuevos:**
- **`cambios_solicitados`**: Texto con cambios solicitados por gerencia
- **`motivo_rechazo`**: Texto con motivo del rechazo
- **`motivo_cierre`**: Texto con motivo del cierre

#### **Estados de Solicitud:**
- `borrador` → `enviada a gerencia` → `aceptada gerencia` → `en proceso rh` → `cerrada`
- `borrador` → `enviada a gerencia` → `solicita cambios` → `enviada a gerencia` → `aceptada gerencia`
- `borrador` → `enviada a gerencia` → `rechazada`
- `borrador` → `enviada a gerencia` → `pospuesta`

## 🧪 Pruebas y Verificación

### **Controlador de Prueba:**
```bash
# Verificar que el campo cambios_solicitados esté disponible:
http://localhost/RH-NEXUS/app/controllers/testSolicitudesCambios.php
```

### **Verificaciones Manuales:**
1. **Jefe de Área** debe poder ver cambios solicitados en lista
2. **Modal** debe mostrar texto completo de cambios
3. **Vista de edición** debe mostrar alerta con cambios
4. **Vista de detalles** debe mostrar card con cambios

## 📊 Estructura de la Base de Datos

### **Tabla Principal: `solicitudes`**
```sql
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
    `actualizado_en` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### **Vista Optimizada: `v_solicitudes_completa`**
```sql
CREATE OR REPLACE VIEW `v_solicitudes_completa` AS
SELECT 
    s.id, s.codigo, s.titulo, s.descripcion, s.departamento_id, s.sede_id,
    s.perfil_puesto, s.cantidad, s.prioridad, s.modalidad, s.salario_min,
    s.salario_max, s.fecha_limite_cobertura, s.requisitos_json, s.solicitante_id,
    s.gerente_id, s.estado, s.motivo_rechazo, s.motivo_cierre, 
    s.cambios_solicitados, s.activo, s.creado_en, s.actualizado_en,
    d.nombre AS departamento_nombre, sed.nombre AS sede_nombre,
    sol.nombre_completo AS solicitante_nombre, g.nombre_completo AS gerente_nombre
FROM solicitudes s
INNER JOIN departamentos d ON s.departamento_id = d.id
INNER JOIN sedes sed ON s.sede_id = sed.id
INNER JOIN usuarios sol ON s.solicitante_id = sol.id
LEFT JOIN usuarios g ON s.gerente_id = g.id
WHERE s.activo = 1;
```

## 🔧 Solución de Problemas

### **Error: "Field 'cambios_solicitados' doesn't exist"**
**Solución:** Ejecutar `05_ALTER_TABLE_simple.sql` o reimportar `01_estructura_base.sql`

### **Error: "View 'v_solicitudes_completa' doesn't exist"**
**Solución:** Ejecutar `06_UPDATE_vista_solicitudes.sql` o reimportar `02_triggers_procedimientos.sql`

### **Error: "Duplicate entry for key 'uk_solicitud_usuario'"**
**Solución:** El trigger ya está corregido en `02_triggers_procedimientos.sql` para usar `INSERT IGNORE`

## 📝 Notas de Desarrollo

- **Todos los campos nuevos** están incluidos en la estructura base
- **La vista está actualizada** para incluir todos los campos
- **Los datos de ejemplo** incluyen casos de uso para todos los estados
- **Los triggers están optimizados** para evitar errores de duplicación
- **La migración es opcional** y solo necesaria para datos existentes

## 🎯 Próximos Pasos

1. **Importar archivos** en el orden especificado
2. **Verificar funcionalidad** con el controlador de prueba
3. **Probar flujo completo** con usuarios de ejemplo
4. **Personalizar** según necesidades específicas
5. **Implementar frontend** para las nuevas funcionalidades 