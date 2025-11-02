# 🏗️ Diagramas de Clases - RH Nexus

Esta carpeta contiene los diagramas de clases del sistema RH Nexus, que muestran la estructura y relaciones entre las clases principales del sistema.

## 📋 Lista de Diagramas

### **🔧 Clases Principales**
- **01_core_classes.puml** - Clases principales del sistema
  - `Conexion` - Singleton para conexión a base de datos
  - `AuthController` - Controlador de autenticación
  - `UsuariosController` - Controlador de usuarios
  - `Solicitud` - Modelo de solicitudes

### **📊 Modelos de Datos**
- **02_model_classes.puml** - Clases de modelo de datos
  - `Usuario` - Modelo de usuarios con validaciones
  - `Sede` - Modelo de sedes con validaciones
  - `Departamento` - Modelo de departamentos

### **🎮 Controladores**
- **03_controller_classes.puml** - Clases controladoras
  - `UsuariosController` - Controlador principal de usuarios
  - `AuthController` - Controlador de autenticación
  - `UsuarioCrea` - Controlador especializado para creación
  - `UsuarioEdita` - Controlador especializado para edición

### **🛡️ Middleware**
- **04_middleware_classes.puml** - Clases de middleware
  - `AuthMiddleware` - Middleware de autenticación
  - `SolicitudesAuthMiddleware` - Middleware de solicitudes

### **🔧 Utilidades**
- **05_utility_classes.puml** - Clases de utilidades
  - `CompatibilityEngine` - Motor de compatibilidad
  - `ScoringSystem` - Sistema de puntuación
  - `NotificationSystem` - Sistema de notificaciones
  - `FileUploadHandler` - Manejo de archivos

### **🤖 API de Python**
- **06_python_api_classes.puml** - Clases de la API de Python
  - `CandidateEvaluator` - Evaluador de candidatos
  - `ModelTrainer` - Entrenador de modelos
  - `DataProcessor` - Procesador de datos

### **🗄️ Base de Datos**
- **07_database_classes.puml** - Esquema de base de datos
  - `DatabaseSchema` - Esquema principal
  - `UsuariosTable` - Tabla de usuarios
  - `SedesTable` - Tabla de sedes
  - `DepartamentosTable` - Tabla de departamentos
  - `SolicitudesTable` - Tabla de solicitudes

### **👁️ Vistas**
- **08_view_classes.puml** - Clases de vista
  - `BaseView` - Vista base
  - `AdminView` - Vistas de administrador
  - `RHView` - Vistas de recursos humanos
  - `GerenteView` - Vistas de gerente
  - `JefeAreaView` - Vistas de jefe de área
  - `SharedView` - Vistas compartidas

### **⚙️ Configuración**
- **09_configuration_classes.puml** - Clases de configuración
  - `EnvironmentConfig` - Configuración de entorno
  - `DatabaseConfig` - Configuración de base de datos
  - `AppConfig` - Configuración de aplicación
  - `SecurityConfig` - Configuración de seguridad

### **🔗 Relaciones**
- **10_relationships.puml** - Relaciones entre clases
  - Dependencias entre controladores y modelos
  - Relaciones de base de datos
  - Flujo de datos entre componentes

## 🎯 Características de los Diagramas

### **Diseño Modular**
- Cada diagrama se enfoca en un aspecto específico del sistema
- Separación clara entre responsabilidades
- Fácil mantenimiento y comprensión

### **Patrones de Diseño Identificados**
- **Singleton Pattern** - `Conexion` para base de datos
- **MVC Pattern** - Separación de Modelos, Vistas y Controladores
- **Factory Pattern** - Creación de conexiones a base de datos
- **Observer Pattern** - Sistema de notificaciones
- **Strategy Pattern** - Diferentes tipos de evaluación

### **Arquitectura del Sistema**
- **Capa de Presentación** - Vistas y controladores
- **Capa de Lógica de Negocio** - Modelos y servicios
- **Capa de Datos** - Base de datos y persistencia
- **Capa de Utilidades** - Servicios auxiliares

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
Los diagramas están diseñados para ser incluidos en documentación técnica, presentaciones o manuales de desarrollo.

## 📝 Notas Técnicas

### **Principios de Diseño**
- **Single Responsibility** - Cada clase tiene una responsabilidad específica
- **Open/Closed** - Extensible sin modificar código existente
- **Dependency Inversion** - Dependencias hacia abstracciones
- **Interface Segregation** - Interfaces específicas y cohesivas

### **Flujos de Datos**
1. **Autenticación** - `AuthController` → `Usuario` → `Conexion`
2. **Gestión de Usuarios** - `UsuariosController` → `Usuario` → `Conexion`
3. **Solicitudes** - `Solicitud` → `Usuario` → `Sede` → `Departamento`
4. **Evaluación** - `CompatibilityEngine` → `ScoringSystem` → `NotificationSystem`

### **Validaciones Importantes**
- Duplicados de usuarios y números de empleado
- Nombres únicos de sedes y departamentos
- Permisos por rol y sede
- Estados de solicitudes

## 🚀 Próximos Pasos

Para expandir estos diagramas, se pueden agregar:
- Diagramas de interfaces y contratos
- Diagramas de herencia y polimorfismo
- Diagramas de composición y agregación
- Diagramas de patrones de diseño específicos

---

*Estos diagramas proporcionan una visión clara de la arquitectura del sistema RH Nexus, facilitando el desarrollo, mantenimiento y comprensión del código para desarrolladores y arquitectos de software.*
