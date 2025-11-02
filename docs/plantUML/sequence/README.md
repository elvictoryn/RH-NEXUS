# 📊 Diagramas de Secuencia - RH Nexus

Esta carpeta contiene los diagramas de secuencia del sistema RH Nexus, que muestran las interacciones entre actores y componentes durante los flujos principales del sistema.

## 📋 Lista de Diagramas

### **🔐 Autenticación y Sesiones**
- **01_login_sequence.puml** - Proceso de login de usuario
  - Validación de credenciales
  - Verificación de estado y bloqueos
  - Creación de sesión y redirección por rol

### **👥 Gestión de Usuarios**
- **02_crear_usuario_sequence.puml** - Creación de nuevo usuario
  - Validación de duplicados (usuario, número empleado)
  - Procesamiento de fotografía
  - Aplicación de reglas de negocio por rol

### **📝 Solicitudes de Personal**
- **03_crear_solicitud_sequence.puml** - Creación de solicitud de personal
  - Generación automática de folio
  - Asignación de gerente por sede
  - Lógica de estado según rol del creador

- **04_aprobar_solicitud_sequence.puml** - Aprobación de solicitudes
  - Validación de permisos por rol
  - Cambio de estado de solicitud
  - Notificaciones automáticas

### **🤖 Reclutamiento Inteligente**
- **05_registrar_candidato_sequence.puml** - Registro de candidatos
  - Almacenamiento de datos del candidato
  - Cálculo automático de compatibilidad
  - Evaluación por criterios (escolaridad, experiencia, etc.)

- **06_evaluar_candidato_sequence.puml** - Evaluación de candidatos
  - Registro de puntajes (personalidad, técnico, entrevista)
  - Cálculo de puntaje final y viabilidad
  - Actualización de base de datos

- **07_contratar_candidato_sequence.puml** - Proceso de contratación
  - Selección del candidato final
  - Cierre automático de solicitud
  - Notificaciones a stakeholders

### **🏢 Gestión Organizacional**
- **08_crear_sede_sequence.puml** - Creación de sedes
  - Validación de nombre único
  - Normalización de datos
  - Almacenamiento en base de datos

- **09_crear_departamento_sequence.puml** - Creación de departamentos
  - Validación de nombre único por sede
  - Asignación a sede específica
  - Gestión de responsables

### **🤖 Inteligencia Artificial**
- **10_ia_evaluation_sequence.puml** - Evaluación con IA
  - Carga del modelo de machine learning
  - Procesamiento de datos de candidatos
  - Aplicación de algoritmo Random Forest
  - Generación de reportes de compatibilidad

## 🎯 Características de los Diagramas

### **Diseño Conciso**
- Cada diagrama se enfoca en un flujo específico
- Máximo 15-20 interacciones por diagrama
- Eliminación de detalles técnicos innecesarios

### **Actores Principales**
- **Usuario/Admin** - Administrador del sistema
- **RH** - Recursos Humanos
- **Gerente** - Gerente de sede
- **Jefe Area** - Jefe de departamento

### **Componentes del Sistema**
- **Controllers** - Lógica de negocio
- **Models** - Acceso a datos
- **Database** - Almacenamiento
- **Forms** - Interfaz de usuario
- **Notification System** - Sistema de notificaciones

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
Los diagramas están diseñados para ser incluidos en documentación técnica, presentaciones o manuales de usuario.

## 📝 Notas Técnicas

### **Patrones Identificados**
- **MVC Pattern** - Separación de responsabilidades
- **Middleware Pattern** - Validación de sesiones y permisos
- **Factory Pattern** - Conexión a base de datos
- **Observer Pattern** - Sistema de notificaciones

### **Flujos Críticos**
1. **Autenticación** - Punto de entrada del sistema
2. **Creación de Solicitudes** - Flujo principal de negocio
3. **Evaluación de Candidatos** - Proceso de selección
4. **Contratación** - Cierre del proceso

### **Validaciones Importantes**
- Duplicados de usuarios y números de empleado
- Permisos por rol y sede
- Estados de solicitudes
- Compatibilidad de candidatos

## 🚀 Próximos Pasos

Para expandir estos diagramas, se pueden agregar:
- Diagramas de flujo de notificaciones
- Secuencias de reportes y analytics
- Flujos de configuración del sistema
- Diagramas de manejo de errores

---

*Estos diagramas proporcionan una visión clara y concisa de los flujos principales del sistema RH Nexus, facilitando la comprensión tanto para desarrolladores como para usuarios finales.*
