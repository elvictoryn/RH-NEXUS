# 🗄️ Diagramas de Base de Datos - RH Nexus

Esta carpeta contiene los diagramas de base de datos del sistema RH Nexus, que muestran la estructura relacional, las entidades y el flujo de datos del sistema.

## 📋 Lista de Diagramas

### **🏗️ Estructura de Base de Datos**
- **01_relational_model.puml** - Modelo relacional completo
  - Todas las tablas con sus campos
  - Claves primarias, foráneas y únicas
  - Relaciones entre tablas
  - Índices y restricciones

- **02_entity_relationships.puml** - Relaciones entre entidades
  - Entidades principales del sistema
  - Relaciones uno a muchos y muchos a muchos
  - Cardinalidades de las relaciones
  - Dependencias entre entidades

- **03_database_schema.puml** - Esquema de base de datos
  - Organización por paquetes funcionales
  - Gestión de usuarios
  - Estructura organizacional
  - Solicitudes de personal
  - Reclutamiento y candidatos
  - Sistema de notificaciones

- **04_data_flow.puml** - Flujo de datos
  - Flujo de información entre entidades
  - Dependencias de datos
  - Relaciones de integridad referencial
  - Flujo de datos del sistema

## 🎯 Características de los Diagramas

### **Diseño Completo**
- **Estructura completa** de la base de datos
- **Relaciones detalladas** entre tablas
- **Restricciones de integridad** referencial
- **Índices y claves** optimizadas

### **Organización por Paquetes**
- **Gestión de Usuarios** - Autenticación y autorización
- **Estructura Organizacional** - Sedes y departamentos
- **Solicitudes de Personal** - Proceso de solicitudes
- **Reclutamiento y Candidatos** - Evaluación y selección
- **Sistema de Notificaciones** - Comunicación del sistema

### **Entidades Principales**
- **Usuario** - Usuarios del sistema con roles
- **Sede** - Ubicaciones físicas de la empresa
- **Departamento** - Áreas funcionales por sede
- **Solicitud** - Solicitudes de personal
- **Candidato** - Postulantes a vacantes
- **Comentario** - Comentarios en solicitudes
- **Seguimiento** - Historial de cambios de estado
- **Notificacion** - Mensajes del sistema

## 🔧 Cómo Usar

### **Visualización**
```bash
# Usar con PlantUML
plantuml *.puml

# O usar herramientas online como:
# - plantuml.com
# - plantuml-editor.kkeisuke.com
```

### **Integración en Documentación**
Los diagramas están diseñados para ser incluidos en:
- Documentación técnica
- Manuales de administrador
- Presentaciones técnicas
- Capacitación de desarrolladores

## 📝 Notas Técnicas

### **Estructura de Base de Datos**
- **Motor**: MySQL/MariaDB
- **Charset**: utf8mb4
- **Collation**: utf8mb4_general_ci
- **Engine**: InnoDB

### **Tablas Principales**
1. **usuarios** - Gestión de usuarios y autenticación
2. **sedes** - Ubicaciones físicas de la empresa
3. **departamentos** - Áreas funcionales por sede
4. **solicitudes** - Solicitudes de personal
5. **postulantes_por_vacante** - Candidatos a vacantes
6. **solicitudes_comentarios** - Comentarios en solicitudes
7. **solicitudes_seguimiento** - Historial de cambios
8. **notificaciones** - Sistema de notificaciones

### **Relaciones Clave**
- **Usuario → Sede** (gerente_id)
- **Usuario → Departamento** (responsable_id)
- **Sede → Departamento** (sede_id)
- **Solicitud → Usuario** (autor_id, autorizada_por, rechazada_por)
- **Solicitud → Sede** (sede_id)
- **Solicitud → Departamento** (departamento_id)
- **Candidato → Solicitud** (solicitud_id)

### **Restricciones de Integridad**
- **Claves primarias** en todas las tablas
- **Claves foráneas** con restricciones CASCADE
- **Claves únicas** para usuarios y números de empleado
- **Índices** para optimización de consultas
- **Restricciones de dominio** en campos enum

### **Campos Virtuales**
- **uniq_gerente_sede** - Gerente único por sede
- **uniq_jefe_sede** - Jefe único por sede
- **uniq_jefe_dep** - Jefe único por departamento

### **Estados y Enums**
- **usuarios.rol**: admin, rh, gerente, jefe_area
- **usuarios.estado**: activo, inactivo
- **solicitudes.estado_actual**: ENVIADA, EN_REV_GER, APROBADA, BUSCANDO, EN_ENTREVISTA, EN_DECISION, CERRADA, RECHAZADA
- **postulantes_por_vacante.Decisión_Final**: CONTRATADO, NO_CONTRATADO, PENDIENTE

## 🚀 Próximos Pasos

Para expandir estos diagramas, se pueden agregar:
- Diagramas de índices y optimización
- Diagramas de particionado
- Diagramas de backup y recuperación
- Diagramas de replicación
- Diagramas de auditoría y logs

---

*Estos diagramas proporcionan una visión completa de la estructura de base de datos del sistema RH Nexus, facilitando la comprensión tanto para desarrolladores como para administradores de base de datos.*

