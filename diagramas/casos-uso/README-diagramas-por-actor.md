# 📊 Diagramas de Casos de Uso por Actor - Nexus RH

Este documento describe los diagramas de casos de uso organizados por actor, mostrando las diferencias de uso y funciones especiales de cada rol en el sistema.

## 🎭 **Actores del Sistema**

### **👥 Actores Humanos:**
1. **Administrador** - Gestión completa del sistema
2. **Recursos Humanos (RH)** - Procesos de RRHH
3. **Gerente** - Supervisión y reportes
4. **Jefe de Área** - Evaluación y gestión específica

### **⚙️ Actores del Sistema:**
5. **Sistema** - Componentes técnicos

## 📋 **Diagramas por Actor**

### **🔧 Módulo de Búsqueda de Usuarios**

#### **03-02-01-usuarios-busqueda-administrador.puml**
**Actor**: Administrador
**Funciones Especiales**:
- ✅ Acceso completo a búsqueda y filtros
- ✅ Exportar lista completa a Excel/PDF
- ✅ Ver detalles completos de usuarios
- ✅ Acceso directo a gestión (crear/editar/eliminar)
- ✅ Filtros por todos los roles
- ✅ Ordenamiento completo

#### **03-02-02-usuarios-busqueda-rh.puml**
**Actor**: Recursos Humanos (RH)
**Funciones Especiales**:
- ✅ Lista usuarios para procesos de RRHH
- ✅ Asignar usuarios a procesos de evaluación
- ✅ Consultar historial de evaluaciones
- ✅ Ver información relevante para RRHH
- ✅ Filtros por departamento para asignaciones

#### **03-02-03-usuarios-busqueda-gerente.puml**
**Actor**: Gerente
**Funciones Especiales**:
- ✅ Lista usuarios de su área de responsabilidad
- ✅ Ver información gerencial (rendimiento, antigüedad)
- ✅ Generar reportes de personal del área
- ✅ Consultar estadísticas de personal
- ✅ Filtros por departamentos bajo su supervisión

#### **03-02-04-usuarios-busqueda-jefe-area.puml**
**Actor**: Jefe de Área
**Funciones Especiales**:
- ✅ Lista usuarios de su departamento específico
- ✅ Acceder a evaluación de personal
- ✅ Asignar tareas a personal del departamento
- ✅ Consultar rendimiento de su equipo
- ✅ Ver información del departamento

### **🏢 Módulo de Gestión de Departamentos**

#### **04-01-departamentos-administrador.puml**
**Actor**: Administrador
**Funciones Especiales**:
- ✅ Gestión completa (crear/editar/eliminar)
- ✅ Reportar estructura completa de la empresa
- ✅ Exportar estructura a Excel/PDF
- ✅ Configurar jerarquía y dependencias
- ✅ Validar nombres únicos por sede

#### **04-02-departamentos-rh.puml**
**Actor**: Recursos Humanos (RH)
**Funciones Especiales**:
- ✅ Consultar departamentos para procesos de RRHH
- ✅ Asignar personal a departamentos
- ✅ Gestionar vacantes y posiciones abiertas
- ✅ Validar disponibilidad de puestos
- ✅ Reportar personal por departamento

#### **04-03-departamentos-gerente.puml**
**Actor**: Gerente
**Funciones Especiales**:
- ✅ Consultar departamentos de su área de responsabilidad
- ✅ Ver información gerencial (presupuesto, rendimiento)
- ✅ Analizar estructura organizacional del área
- ✅ Evaluar eficiencia de departamentos
- ✅ Planificar recursos humanos del área

#### **04-04-departamentos-jefe-area.puml**
**Actor**: Jefe de Área
**Funciones Especiales**:
- ✅ Consultar su departamento específico
- ✅ Gestionar personal del departamento
- ✅ Evaluar rendimiento de su equipo
- ✅ Asignar tareas a personal del departamento
- ✅ Planificar actividades del departamento

### **🏢 Módulo de Gestión de Sedes**

#### **05-01-sedes-administrador.puml**
**Actor**: Administrador
**Funciones Especiales**:
- ✅ Gestión completa (crear/editar/eliminar)
- ✅ Reportar ubicación completa de la empresa
- ✅ Exportar estructura a Excel/PDF
- ✅ Configurar jerarquía y dependencias
- ✅ Validar datos únicos y formatos

#### **05-02-sedes-rh.puml**
**Actor**: Recursos Humanos (RH)
**Funciones Especiales**:
- ✅ Consultar sedes para procesos de RRHH
- ✅ Asignar personal a sedes específicas
- ✅ Gestionar vacantes por sede
- ✅ Validar disponibilidad de puestos por sede
- ✅ Reportar personal por sede

#### **05-03-sedes-gerente.puml**
**Actor**: Gerente
**Funciones Especiales**:
- ✅ Consultar sedes de su área de responsabilidad
- ✅ Ver información gerencial (presupuesto, capacidad)
- ✅ Analizar distribución de personal por sede
- ✅ Evaluar eficiencia de sedes
- ✅ Planificar recursos por sede

#### **05-04-sedes-jefe-area.puml**
**Actor**: Jefe de Área
**Funciones Especiales**:
- ✅ Consultar su sede específica
- ✅ Gestionar personal de la sede
- ✅ Evaluar rendimiento de su equipo en la sede
- ✅ Asignar tareas a personal de la sede
- ✅ Planificar actividades de la sede

## 📊 **Comparativa de Funciones por Actor**

### **🔍 Nivel de Acceso:**

| Función | Administrador | RH | Gerente | Jefe de Área |
|---------|---------------|----|---------|---------------|
| **Gestión Completa** | ✅ | ❌ | ❌ | ❌ |
| **Consulta General** | ✅ | ✅ | ✅ | ✅ |
| **Reportes** | ✅ Completo | ✅ RRHH | ✅ Área | ✅ Departamento |
| **Asignaciones** | ✅ | ✅ | ❌ | ✅ |
| **Evaluaciones** | ✅ | ✅ | ❌ | ✅ |

### **📋 Funciones Especiales por Rol:**

#### **Administrador:**
- Gestión completa del sistema
- Exportación de datos
- Configuración de jerarquías
- Validaciones técnicas

#### **Recursos Humanos (RH):**
- Procesos de RRHH
- Asignaciones de personal
- Gestión de vacantes
- Historial de evaluaciones

#### **Gerente:**
- Supervisión de área
- Reportes gerenciales
- Análisis de eficiencia
- Planificación de recursos

#### **Jefe de Área:**
- Gestión de departamento específico
- Evaluación de equipo
- Asignación de tareas
- Planificación de actividades

## 🛠️ **Cómo Usar los Diagramas**

### **Generación de Diagramas por Actor:**
```bash
# Diagramas de Administrador
plantuml 03-02-01-*.puml 04-01-*.puml 05-01-*.puml

# Diagramas de RH
plantuml 03-02-02-*.puml 04-02-*.puml 05-02-*.puml

# Diagramas de Gerente
plantuml 03-02-03-*.puml 04-03-*.puml 05-03-*.puml

# Diagramas de Jefe de Área
plantuml 03-02-04-*.puml 04-04-*.puml 05-04-*.puml
```

### **Nomenclatura de Archivos:**
- `XX-YY-ZZ-modulo-actor.puml`
- `XX`: Número de módulo principal
- `YY`: Número de submódulo
- `ZZ`: Número de actor específico
- `modulo`: Nombre del módulo
- `actor`: Nombre del actor

## 📝 **Notas Importantes**

### **Diferencias Clave:**
1. **Administrador**: Acceso completo y funciones de gestión
2. **RH**: Enfoque en procesos de recursos humanos
3. **Gerente**: Enfoque en supervisión y reportes
4. **Jefe de Área**: Enfoque en gestión específica de área

### **Seguridad y Permisos:**
- Cada actor tiene acceso solo a las funciones relevantes para su rol
- Los diagramas reflejan las restricciones de acceso del sistema
- Las funciones especiales están claramente diferenciadas

### **Mantenimiento:**
- Al agregar nuevas funciones, considerar el impacto en cada actor
- Mantener consistencia en la nomenclatura
- Actualizar este README cuando se agreguen nuevos diagramas 