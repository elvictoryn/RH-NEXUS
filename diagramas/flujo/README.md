# 🔄 Diagramas de Flujo - Nexus RH

Este directorio contiene los diagramas de flujo del sistema Nexus RH, que muestran la lógica de procesos y decisiones de manera visual y secuencial.

## 📁 Estructura de Archivos

### **Flujos Principales del Sistema**
- `01-login.puml` - Proceso de autenticación y login
- `02-crear-usuario.puml` - Creación de usuarios con validaciones
- `03-crear-departamento.puml` - Creación de departamentos
- `04-crear-sede.puml` - Creación de sedes y ubicaciones
- `05-buscar-usuarios.puml` - Búsqueda y filtrado de usuarios

### **Flujos de Gestión**
- `06-gestion-departamentos.puml` - Gestión completa de departamentos
- `07-gestion-sedes.puml` - Gestión completa de sedes

## 🎯 Características de los Diagramas

### **Elementos Utilizados**
- **Actividades**: Procesos y acciones del sistema
- **Decisiones**: Puntos de bifurcación con condiciones
- **Inicio/Fin**: Puntos de entrada y salida del flujo
- **Notas**: Explicaciones adicionales y contexto
- **Colores**: Diferenciación visual por tipo de elemento

### **Convenciones de Nomenclatura**
- **Actividades**: Acciones descriptivas en presente
- **Decisiones**: Preguntas claras con opciones Sí/No
- **Flujos**: Secuencia lógica de procesos
- **Notas**: Información contextual importante

## 🛠️ Cómo Usar los Diagramas

### **Visualización**
```bash
# Generar imagen PNG
plantuml diagrama.puml

# Generar imagen SVG
plantuml -tsvg diagrama.puml

# Generar PDF
plantuml -tpdf diagrama.puml
```

### **Comandos Útiles**
```bash
# Generar todos los diagramas de flujo
plantuml *.puml

# Generar solo flujos principales
plantuml 0[1-5]-*.puml

# Generar solo flujos de gestión
plantuml 0[6-7]-*.puml
```

### **Personalización**
Los diagramas usan el tema `plain` con colores personalizados:
- **Actividades**: Blanco con borde gris
- **Decisiones**: Amarillo claro con borde naranja
- **Inicio**: Verde claro con borde verde
- **Fin**: Rojo claro con borde rojo

## 📋 Detalles de los Flujos

### **01 - Login**
- **Validación**: Verificación de credenciales
- **Autenticación**: Verificación de usuario y contraseña
- **Autorización**: Redirección según rol
- **Manejo de errores**: Mensajes específicos por tipo de error

### **02 - Crear Usuario**
- **Validación en tiempo real**: Verificación de duplicados
- **Procesamiento**: Encriptación y subida de archivos
- **Validación**: Campos obligatorios y formatos
- **Manejo de errores**: Notificaciones específicas

### **03 - Crear Departamento**
- **Validación AJAX**: Verificación de nombre único por sede
- **Validación**: Campos obligatorios
- **Procesamiento**: Asignación a sede específica
- **Manejo de errores**: Mensajes de sesión

### **04 - Crear Sede**
- **Validación**: Verificación de nombre único
- **Procesamiento**: Datos de ubicación y contacto
- **Validación**: Campos obligatorios
- **Manejo de errores**: Mensajes de confirmación

### **05 - Buscar Usuarios**
- **Búsqueda en tiempo real**: Filtrado dinámico
- **Interfaz**: Tabla de resultados
- **Acciones**: Ver, editar, eliminar usuarios
- **Navegación**: Redirección a acciones específicas

### **06 - Gestión de Departamentos**
- **Control de acceso**: Funcionalidades según rol
- **Operaciones CRUD**: Crear, leer, actualizar, eliminar
- **Validaciones**: Dependencias y restricciones
- **Navegación**: Flujos de trabajo específicos

### **07 - Gestión de Sedes**
- **Control de acceso**: Funcionalidades según rol
- **Operaciones CRUD**: Crear, leer, actualizar, eliminar
- **Validaciones**: Dependencias y restricciones
- **Navegación**: Flujos de trabajo específicos

## 🔄 Mantenimiento

### **Agregar Nuevo Diagrama**
1. Crear archivo con nomenclatura: `XX-nombre-flujo.puml`
2. Usar la estructura estándar con actividades, decisiones y flujos
3. Incluir notas explicativas para procesos complejos
4. Actualizar este README

### **Modificar Diagrama Existente**
1. Mantener la nomenclatura de actividades
2. Preservar la lógica de decisiones
3. Actualizar notas explicativas si es necesario
4. Verificar que el diagrama se renderice correctamente

## 📝 **Notas de Ingeniería de Software**

### **Enfoque de los Diagramas:**
- **Nivel de abstracción**: Procesos de negocio
- **Lógica de decisiones**: Clara y comprensible
- **Flujos de trabajo**: Secuenciales y lógicos
- **Mantenibilidad**: Fácil de entender y modificar

### **Patrones de Diseño Representados:**
- **Validación**: Múltiples niveles de verificación
- **Manejo de errores**: Estratégico y consistente
- **Control de acceso**: Basado en roles
- **Navegación**: Flujos de usuario intuitivos

### **Beneficios para Ingeniería de Software:**
- **Comunicación**: Facilita la comunicación entre equipos
- **Documentación**: Proporciona guía clara para desarrollo
- **Mantenimiento**: Simplifica el mantenimiento del sistema
- **Escalabilidad**: Permite identificar puntos de mejora

## 📞 Soporte

Para dudas sobre los diagramas de flujo o sugerencias de mejora, consultar la documentación del proyecto Nexus RH. 