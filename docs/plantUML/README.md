# 📊 Diagramas de Casos de Uso - Sistema RH Nexus

Este directorio contiene los diagramas de casos de uso en formato PlantUML para el Sistema RH Nexus, organizados por módulos funcionales.

## 📋 **Lista de Diagramas**

### **00_sistema_completo.puml**
- **Descripción**: Vista general del sistema completo con todos los módulos
- **Contenido**: Diagrama de alto nivel mostrando la interacción entre todos los módulos
- **Uso**: Para entender la arquitectura general del sistema

### **01_gestion_organizacional.puml**
- **Descripción**: Módulo de Gestión Organizacional
- **Contenido**: Casos de uso para gestión de sedes, departamentos y estructura organizacional
- **Actores**: Administrador, Gerente, Jefe de Área

### **02_gestion_usuarios.puml**
- **Descripción**: Módulo de Gestión de Usuarios
- **Contenido**: Casos de uso para registro, autenticación, roles y permisos
- **Actores**: Administrador, Usuario, Sistema

### **03_solicitudes_personal.puml**
- **Descripción**: Módulo de Solicitudes de Personal
- **Contenido**: Casos de uso para creación, gestión y definición de requisitos
- **Actores**: Jefe de Área, Gerente, RH, Administrador, Sistema

### **04_aprobacion_solicitudes.puml**
- **Descripción**: Módulo de Aprobación de Solicitudes
- **Contenido**: Casos de uso para flujo de aprobación, gestión de estados y asignación
- **Actores**: Gerente, RH, Administrador, Sistema

### **05_reclutamiento_inteligente.puml**
- **Descripción**: Módulo de Reclutamiento Inteligente
- **Contenido**: Casos de uso para evaluación automática, análisis de competencias y machine learning
- **Actores**: RH, Administrador, Sistema IA, Candidato

### **06_evaluacion_candidatos.puml**
- **Descripción**: Módulo de Evaluación de Candidatos
- **Contenido**: Casos de uso para entrevistas, pruebas técnicas y evaluación de personalidad
- **Actores**: RH, Entrevistador, Administrador, Sistema

### **07_seleccion_contratacion.puml**
- **Descripción**: Módulo de Selección y Contratación
- **Contenido**: Casos de uso para proceso de selección, contratación y seguimiento
- **Actores**: RH, Gerente, Administrador, Sistema, Candidato

### **08_reportes_analytics.puml**
- **Descripción**: Módulo de Reportes y Analytics
- **Contenido**: Casos de uso para generación de reportes, analytics y dashboard ejecutivo
- **Actores**: Administrador, RH, Gerente, Jefe de Área, Sistema

### **09_notificaciones.puml**
- **Descripción**: Módulo de Notificaciones
- **Contenido**: Casos de uso para notificaciones automáticas, configuración y gestión
- **Actores**: Usuario, Sistema, Administrador, RH, Gerente, Jefe de Área

### **10_configuracion_sistema.puml**
- **Descripción**: Módulo de Configuración del Sistema
- **Contenido**: Casos de uso para configuración general, seguridad, integraciones y mantenimiento
- **Actores**: Administrador, Sistema, RH, Gerente

## 🚀 **Cómo Usar los Diagramas**

### **Requisitos**
- PlantUML instalado localmente o
- Editor que soporte PlantUML (VS Code, IntelliJ, etc.) o
- Servicio online de PlantUML

### **Visualización Local**
```bash
# Instalar PlantUML
npm install -g plantuml

# Generar imágenes
plantuml *.puml

# Generar imagen específica
plantuml 01_gestion_organizacional.puml
```

### **Visualización Online**
1. Copiar el contenido del archivo .puml
2. Pegar en [PlantUML Online Server](http://www.plantuml.com/plantuml/uml/)
3. Generar la imagen

### **En VS Code**
1. Instalar extensión "PlantUML"
2. Abrir archivo .puml
3. Usar Ctrl+Shift+P → "PlantUML: Preview Current Diagram"

## 📚 **Estructura de los Diagramas**

Cada diagrama incluye:
- **Actores**: Roles que interactúan con el sistema
- **Casos de Uso**: Funcionalidades específicas del módulo
- **Relaciones**: Conexiones entre actores y casos de uso
- **Notas**: Información adicional y criterios de validación
- **Paquetes**: Agrupación lógica de casos de uso relacionados

## 🎯 **Propósito de los Diagramas**

Estos diagramas sirven para:
- **Documentación**: Entender las funcionalidades del sistema
- **Desarrollo**: Guiar la implementación de características
- **Testing**: Identificar casos de prueba
- **Comunicación**: Explicar el sistema a stakeholders
- **Mantenimiento**: Entender el impacto de cambios

## 🔄 **Actualización de Diagramas**

Cuando se modifique el sistema:
1. Actualizar el diagrama correspondiente
2. Verificar que las relaciones sean correctas
3. Actualizar las notas con nueva información
4. Regenerar las imágenes si es necesario

## 📝 **Notas Importantes**

- Los diagramas están en español para facilitar la comprensión
- Cada módulo es independiente pero se relaciona con otros
- Los actores pueden tener diferentes niveles de acceso según su rol
- Las relaciones entre casos de uso muestran dependencias y flujos
- Las notas proporcionan contexto adicional y criterios de validación

## 🆘 **Soporte**

Para dudas sobre los diagramas o el sistema:
- Revisar la documentación del código
- Consultar los comentarios en los archivos .puml
- Verificar las relaciones entre módulos en el diagrama general
