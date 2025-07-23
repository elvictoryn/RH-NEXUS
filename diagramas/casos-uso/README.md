# 📊 Diagramas PlantUML - Nexus RH

Este directorio contiene todos los diagramas de casos de uso del sistema Nexus RH, organizados por módulos y submódulos para mayor claridad y legibilidad.

## 📁 Estructura de Archivos

### **Diagramas Generales**
- `01-diagrama-general.puml` - Vista general del sistema completo
- `02-modulo-autenticacion.puml` - Módulo de autenticación y seguridad

### **Módulo de Usuarios (Dividido en 3 submódulos)**
- `03-01-usuarios-gestion-basica.puml` - Crear, editar, eliminar, ver usuarios
- `03-02-usuarios-busqueda.puml` - Búsqueda, listado y filtros de usuarios
- `03-03-usuarios-seguridad.puml` - Roles, permisos y seguridad

### **Módulos de Gestión Organizacional**
- `04-modulo-departamentos.puml` - Gestión de departamentos
- `05-modulo-sedes.puml` - Gestión de sedes y ubicaciones

### **Módulo de Solicitudes (Dividido en 3 submódulos)**
- `06-01-solicitudes-captura.puml` - Captura de solicitudes y documentos
- `06-02-solicitudes-procesamiento.puml` - Procesamiento y seguimiento
- `06-03-solicitudes-aprobacion.puml` - Aprobación y notificaciones

### **Módulo de Evaluaciones (Dividido en 3 submódulos)**
- `07-01-evaluaciones-configuracion.puml` - Configuración de evaluaciones
- `07-02-evaluaciones-ejecucion.puml` - Ejecución y calificación
- `07-03-evaluaciones-resultados.puml` - Resultados y reportes

### **Módulos en Desarrollo**
- `08-modulo-resultados.puml` - Reportes y estadísticas generales
- `09-modulo-ia.puml` - Módulo de inteligencia artificial
- `10-modulo-configuracion.puml` - Configuración del sistema

## 🎯 Criterios de Organización

### **División por Funcionalidad**
Los módulos complejos se han dividido en submódulos más pequeños y manejables:

1. **Gestión Básica**: Operaciones CRUD fundamentales
2. **Búsqueda y Listado**: Funcionalidades de consulta y filtrado
3. **Seguridad**: Roles, permisos y validaciones
4. **Captura**: Entrada de datos y documentos
5. **Procesamiento**: Lógica de negocio y seguimiento
6. **Aprobación**: Decisiones y notificaciones
7. **Configuración**: Preparación y parámetros
8. **Ejecución**: Proceso principal de evaluación
9. **Resultados**: Análisis y reportes

### **Beneficios de la División**
- ✅ **Mayor claridad**: Cada diagrama se enfoca en una funcionalidad específica
- ✅ **Mejor mantenimiento**: Cambios en una funcionalidad no afectan otras
- ✅ **Legibilidad mejorada**: Diagramas más pequeños son más fáciles de entender
- ✅ **Reutilización**: Submódulos pueden ser referenciados en múltiples contextos

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
# Generar todos los diagramas
plantuml *.puml

# Generar solo submódulos de usuarios
plantuml 03-*.puml

# Generar solo submódulos de solicitudes
plantuml 06-*.puml
```

### **Personalización**
Los diagramas usan el tema `plain` con colores personalizados:
- **Actores**: Azul oscuro (#1e3a8a)
- **Casos de uso**: Blanco con borde gris
- **Sistema**: Azul claro (#E3F2FD)

## 📋 Estado de Desarrollo

### **✅ Completados**
- Diagrama general del sistema
- Módulo de autenticación
- Submódulos de usuarios (3)
- Submódulos de solicitudes (3)
- Submódulos de evaluaciones (3)
- Módulos de departamentos y sedes

### **🔄 En Desarrollo**
- Módulo de resultados y reportes
- Módulo de inteligencia artificial
- Módulo de configuración del sistema

### **📝 Notas Importantes**
- Los diagramas usan sintaxis `rectangle` en lugar de `system`
- Los actores tienen nombres descriptivos visibles
- Se incluyen notas explicativas para casos de uso complejos
- Las relaciones están claramente definidas con `<<include>>` y `<<extend>>`

## 🔄 Mantenimiento

### **Agregar Nuevo Diagrama**
1. Crear archivo con nomenclatura: `XX-YY-modulo-funcionalidad.puml`
2. Usar la estructura estándar con actores, casos de uso y relaciones
3. Incluir notas explicativas para casos de uso complejos
4. Actualizar este README

### **Modificar Diagrama Existente**
1. Mantener la nomenclatura de casos de uso (UC1, UC2, etc.)
2. Preservar las relaciones existentes
3. Actualizar notas explicativas si es necesario
4. Verificar que el diagrama se renderice correctamente

## 📞 Soporte

Para dudas sobre los diagramas o sugerencias de mejora, consultar la documentación del proyecto Nexus RH. 