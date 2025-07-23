# 📊 Diagramas PlantUML - Nexus RH

Este directorio contiene todos los diagramas del sistema Nexus RH organizados por tipo y funcionalidad para mayor claridad y mantenimiento.

## 📁 Estructura de Directorios

```
diagramas/
├── casos-uso/          # Diagramas de casos de uso
│   ├── 01-diagrama-general.puml
│   ├── 02-modulo-autenticacion.puml
│   ├── 03-01-usuarios-gestion-basica.puml
│   ├── 03-02-usuarios-busqueda.puml
│   ├── 03-03-usuarios-seguridad.puml
│   ├── 04-modulo-departamentos.puml
│   ├── 05-modulo-sedes.puml
│   ├── 06-01-solicitudes-captura.puml
│   ├── 06-02-solicitudes-procesamiento.puml
│   ├── 06-03-solicitudes-aprobacion.puml
│   ├── 07-01-evaluaciones-configuracion.puml
│   ├── 07-02-evaluaciones-ejecucion.puml
│   ├── 07-03-evaluaciones-resultados.puml
│   ├── 08-modulo-resultados.puml
│   ├── 09-modulo-ia.puml
│   ├── 10-modulo-configuracion.puml
│   └── README.md
└── secuencia/          # Diagramas de secuencia
    ├── 01-login.puml
    ├── 02-crear-usuario.puml
    ├── 03-crear-departamento.puml
    ├── 04-crear-sede.puml
    ├── 05-buscar-usuarios.puml
    ├── 06-procesar-solicitud.puml
    ├── 07-evaluar-candidato.puml
    └── README.md
```

## 🎯 Tipos de Diagramas

### **📋 Diagramas de Casos de Uso**
Ubicación: `casos-uso/`

**Propósito**: Mostrar las funcionalidades del sistema desde la perspectiva del usuario.

**Características**:
- ✅ **Actores**: Usuarios del sistema
- ✅ **Casos de uso**: Funcionalidades específicas
- ✅ **Relaciones**: Inclusión y extensión
- ✅ **Organización**: Divididos en submódulos para mayor claridad

**Estructura**:
- **Diagrama General**: Vista completa del sistema
- **Módulos Funcionales**: Autenticación, usuarios, departamentos, sedes
- **Submódulos**: División por funcionalidad (gestión básica, búsqueda, seguridad)

### **⏱️ Diagramas de Secuencia**
Ubicación: `secuencia/`

**Propósito**: Mostrar la interacción detallada entre componentes del sistema a lo largo del tiempo.

**Características**:
- ✅ **Actores**: Usuarios del sistema
- ✅ **Participantes**: Componentes técnicos (Controllers, Models, Database)
- ✅ **Activaciones**: Control de flujo temporal
- ✅ **Mensajes**: Comunicación entre componentes
- ✅ **Alternativas**: Flujos condicionales

**Estructura**:
- **Flujos Principales**: Login, crear usuario, crear departamento, crear sede, buscar usuarios
- **Flujos de Negocio**: Procesar solicitudes, evaluar candidatos (en desarrollo)

## 🛠️ Cómo Usar los Diagramas

### **Generación de Imágenes**
```bash
# Generar todos los diagramas de casos de uso
cd casos-uso && plantuml *.puml

# Generar todos los diagramas de secuencia
cd secuencia && plantuml *.puml

# Generar diagrama específico
plantuml 01-diagrama-general.puml

# Generar en diferentes formatos
plantuml -tsvg diagrama.puml    # SVG
plantuml -tpdf diagrama.puml    # PDF
plantuml -thtml diagrama.puml   # HTML
```

### **Comandos Útiles**
```bash
# Generar solo submódulos de usuarios
cd casos-uso && plantuml 03-*.puml

# Generar solo flujos principales
cd secuencia && plantuml 0[1-5]-*.puml

# Generar diagramas de un módulo específico
cd casos-uso && plantuml 06-*.puml  # Solo solicitudes
```

## 🎨 Personalización

### **Tema y Colores**
Todos los diagramas usan el tema `plain` con colores personalizados:

**Casos de Uso**:
- **Actores**: Azul oscuro (#1e3a8a)
- **Casos de uso**: Blanco con borde gris
- **Sistema**: Azul claro (#E3F2FD)

**Secuencia**:
- **Actores**: Azul oscuro (#1e3a8a)
- **Participantes**: Blanco con borde gris
- **Activaciones**: Líneas de vida con colores automáticos

## 📋 Estado de Desarrollo

### **✅ Completados**
- **Casos de Uso**: Todos los módulos principales y submódulos
- **Secuencia**: Flujos principales del sistema (01-05)

### **🔄 En Desarrollo**
- **Casos de Uso**: Módulos de resultados, IA y configuración
- **Secuencia**: Flujos de procesos de negocio (06-07)

### **📝 Notas Importantes**
- Los diagramas de casos de uso usan sintaxis `rectangle` en lugar de `system`
- Los actores tienen nombres descriptivos visibles
- Se incluyen notas explicativas para casos de uso complejos
- Las relaciones están claramente definidas con `<<include>>` y `<<extend>>`

## 🔄 Mantenimiento

### **Agregar Nuevo Diagrama**
1. **Casos de Uso**: Crear en `casos-uso/` con nomenclatura `XX-YY-modulo-funcionalidad.puml`
2. **Secuencia**: Crear en `secuencia/` con nomenclatura `XX-nombre-flujo.puml`
3. Usar la estructura estándar correspondiente
4. Incluir notas explicativas para elementos complejos
5. Actualizar el README correspondiente

### **Modificar Diagrama Existente**
1. Mantener la nomenclatura existente
2. Preservar las relaciones y activaciones
3. Actualizar notas explicativas si es necesario
4. Verificar que el diagrama se renderice correctamente

## 📞 Soporte

Para dudas sobre los diagramas o sugerencias de mejora:
- **Casos de Uso**: Consultar `casos-uso/README.md`
- **Secuencia**: Consultar `secuencia/README.md`
- **General**: Consultar la documentación del proyecto Nexus RH 