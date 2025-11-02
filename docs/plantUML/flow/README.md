# 🔄 Diagramas de Flujo - RH Nexus

Esta carpeta contiene los diagramas de flujo del sistema RH Nexus, que muestran los procesos y decisiones principales del sistema de manera clara y concisa.

## 📋 Lista de Diagramas

### **🔐 Autenticación y Sesiones**
- **01_login_flow.puml** - Flujo de login de usuario
  - Validación de credenciales
  - Verificación de estado y bloqueos
  - Redirección por rol
  - Manejo de intentos fallidos

### **👥 Gestión de Usuarios**
- **02_crear_usuario_flow.puml** - Flujo de creación de usuario
  - Validación de duplicados
  - Reglas de negocio por rol
  - Procesamiento de fotografía
  - Validaciones de seguridad

### **📝 Solicitudes de Personal**
- **03_crear_solicitud_flow.puml** - Flujo de creación de solicitud
  - Generación automática de folio
  - Lógica por rol del creador
  - Asignación de gerente
  - Estados automáticos

- **04_aprobar_solicitud_flow.puml** - Flujo de aprobación de solicitudes
  - Validación de permisos
  - Decisiones de aprobación/rechazo
  - Notificaciones automáticas
  - Manejo de modificaciones

### **🤖 Reclutamiento Inteligente**
- **05_registrar_candidato_flow.puml** - Flujo de registro de candidatos
  - Validación de datos
  - Cálculo automático de compatibilidad
  - Evaluación por criterios
  - Sistema de semáforos

- **06_evaluar_candidato_flow.puml** - Flujo de evaluación de candidatos
  - Registro de puntajes
  - Cálculo de puntaje final
  - Determinación de viabilidad
  - Actualización de resultados

- **07_contratar_candidato_flow.puml** - Flujo de contratación
  - Selección del candidato final
  - Confirmación de contratación
  - Cierre automático de solicitud
  - Notificaciones a stakeholders

### **🏢 Gestión Organizacional**
- **08_crear_sede_flow.puml** - Flujo de creación de sedes
  - Validación de nombre único
  - Validación de formatos
  - Normalización de datos
  - Creación en base de datos

- **09_crear_departamento_flow.puml** - Flujo de creación de departamentos
  - Validación de nombre único por sede
  - Asignación a sede
  - Validación de dependencias
  - Creación en base de datos

### **🤖 Inteligencia Artificial**
- **10_ia_evaluation_flow.puml** - Flujo de evaluación con IA
  - Carga del modelo Random Forest
  - Procesamiento de datos
  - Aplicación de algoritmo
  - Generación de reportes

### **🔄 Sistema General**
- **11_system_flow.puml** - Flujo general del sistema
  - Autenticación y autorización
  - Redirección por rol
  - Funcionalidades por rol
  - Cierre de sesión

## 🎯 Características de los Diagramas

### **Diseño Conciso**
- Cada diagrama se enfoca en un proceso específico
- Máximo 20-25 pasos por diagrama
- Eliminación de detalles técnicos innecesarios
- Fácil comprensión para usuarios no técnicos

### **Flujos Principales**
- **Autenticación** - Login y gestión de sesiones
- **Gestión de Usuarios** - Creación y validación
- **Solicitudes** - Creación y aprobación
- **Reclutamiento** - Registro y evaluación
- **Contratación** - Proceso de selección
- **Organización** - Gestión de sedes y departamentos
- **IA** - Evaluación inteligente

### **Decisiones Críticas**
- Validación de permisos por rol
- Verificación de duplicados
- Aplicación de reglas de negocio
- Manejo de errores y excepciones
- Notificaciones automáticas

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
- Manuales de usuario
- Documentación técnica
- Presentaciones
- Capacitación de personal

## 📝 Notas Técnicas

### **Patrones de Flujo Identificados**
- **Validación** - Verificación de datos y permisos
- **Procesamiento** - Transformación y cálculo
- **Persistencia** - Almacenamiento en base de datos
- **Notificación** - Comunicación con usuarios
- **Redirección** - Navegación por rol

### **Flujos Críticos**
1. **Autenticación** - Punto de entrada del sistema
2. **Creación de Solicitudes** - Flujo principal de negocio
3. **Evaluación de Candidatos** - Proceso de selección
4. **Contratación** - Cierre del proceso
5. **Gestión Organizacional** - Estructura del sistema

### **Validaciones Importantes**
- Duplicados de usuarios y números de empleado
- Permisos por rol y sede
- Estados de solicitudes
- Compatibilidad de candidatos
- Formatos de datos

## 🚀 Próximos Pasos

Para expandir estos diagramas, se pueden agregar:
- Flujos de notificaciones
- Flujos de reportes y analytics
- Flujos de configuración del sistema
- Flujos de manejo de errores
- Flujos de backup y recuperación

---

*Estos diagramas proporcionan una visión clara de los procesos principales del sistema RH Nexus, facilitando la comprensión tanto para usuarios finales como para desarrolladores y administradores del sistema.*
