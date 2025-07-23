# 📊 Diagramas de Secuencia - Nexus RH

Este directorio contiene los diagramas de secuencia del sistema Nexus RH, que muestran la interacción detallada entre actores y componentes del sistema a lo largo del tiempo.

## 📁 Estructura de Archivos

### **Flujos Principales del Sistema**
- `01-login.puml` - Proceso de autenticación y login
- `02-crear-usuario.puml` - Creación de usuarios con validaciones
- `03-crear-departamento.puml` - Creación de departamentos
- `04-crear-sede.puml` - Creación de sedes y ubicaciones
- `05-buscar-usuarios.puml` - Búsqueda y filtrado de usuarios

### **Flujos de Procesos de Negocio (En Desarrollo)**
- `06-procesar-solicitud.puml` - Procesamiento de solicitudes de candidatos
- `07-evaluar-candidato.puml` - Evaluación de candidatos con IA

## 🎯 Características de los Diagramas

### **Elementos Utilizados**
- **Actores**: Usuarios del sistema (Administrador, RH, Jefe de Área, etc.)
- **Participantes**: Componentes del sistema (Controllers, Models, Views, etc.)
- **Activaciones**: Períodos de actividad de cada componente
- **Mensajes**: Comunicación entre componentes
- **Alternativas**: Flujos condicionales (alt/else)
- **Notas**: Explicaciones adicionales

### **Convenciones de Nomenclatura**
- **Actores**: Nombres descriptivos (Administrador, RH, etc.)
- **Participantes**: Nombres técnicos (Controller, Model, Database, etc.)
- **Mensajes**: Acciones específicas con parámetros
- **Activaciones**: Control de flujo con `activate`/`deactivate`

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
# Generar todos los diagramas de secuencia
plantuml *.puml

# Generar solo flujos principales
plantuml 0[1-5]-*.puml

# Generar solo flujos en desarrollo
plantuml 0[6-7]-*.puml
```

### **Personalización**
Los diagramas usan el tema `plain` con colores personalizados:
- **Actores**: Azul oscuro (#1e3a8a)
- **Participantes**: Blanco con borde gris
- **Activaciones**: Líneas de vida con colores automáticos

## 📋 Detalles de los Flujos

### **01 - Login**
- Validación de credenciales
- Verificación de contraseña con hash
- Creación de sesión
- Redirección según rol

### **02 - Crear Usuario**
- Validación AJAX en tiempo real
- Verificación de duplicados
- Encriptación de contraseña
- Subida de fotografía
- Gestión de errores

### **03 - Crear Departamento**
- Validación de datos
- Verificación de nombres únicos por sede
- Gestión de mensajes de sesión
- Redirección con feedback

### **04 - Crear Sede**
- Validación de campos obligatorios
- Verificación de formato de código postal
- Validación de teléfono
- Gestión de errores de validación

### **05 - Buscar Usuarios**
- Carga inicial de usuarios
- Búsqueda en tiempo real
- Filtros por rol
- Actualización dinámica de resultados

### **06 - Procesar Solicitud (En Desarrollo)**
- Carga de solicitudes pendientes
- Validación de documentos
- Asignación de evaluador
- Notificaciones automáticas

### **07 - Evaluar Candidato (En Desarrollo)**
- Carga de evaluaciones asignadas
- Proceso de calificación
- Análisis con IA
- Notificación a RH

## 🔄 Mantenimiento

### **Agregar Nuevo Diagrama**
1. Crear archivo con nomenclatura: `XX-nombre-flujo.puml`
2. Usar la estructura estándar con actores, participantes y activaciones
3. Incluir notas explicativas para flujos complejos
4. Actualizar este README

### **Modificar Diagrama Existente**
1. Mantener la nomenclatura de participantes
2. Preservar las activaciones existentes
3. Actualizar notas explicativas si es necesario
4. Verificar que el diagrama se renderice correctamente

## 📞 Soporte

Para dudas sobre los diagramas de secuencia o sugerencias de mejora, consultar la documentación del proyecto Nexus RH. 