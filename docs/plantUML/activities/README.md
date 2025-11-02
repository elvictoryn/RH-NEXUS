# Diagramas de Actividades - Sistema RH-NEXUS

## Descripción

Los diagramas de actividades muestran el flujo de control de un proceso, describiendo las actividades y decisiones que ocurren en el sistema. Son especialmente útiles para modelar procesos de negocio, algoritmos y flujos de trabajo.

## Diagramas Disponibles

### **🔄 Proceso de Reclutamiento**
- **01_proceso_reclutamiento.puml** - Flujo completo del proceso de reclutamiento
  - Creación de solicitudes por jefes de área
  - Aprobación gerencial
  - Reclutamiento y evaluación de candidatos
  - Proceso de entrevistas
  - Selección final y contratación

### **🔐 Autenticación de Usuario**
- **02_autenticacion_usuario.puml** - Proceso de login y autenticación
  - Validación de credenciales
  - Verificación de estado de cuenta
  - Protección contra fuerza bruta
  - Gestión de sesiones
  - Redirección por rol

### **👥 Gestión de Candidatos**
- **03_gestion_candidatos.puml** - Proceso de gestión de candidatos
  - Creación de candidatos
  - Evaluación automática con IA
  - Edición de información
  - Generación de rankings
  - Eliminación de candidatos

### **🔔 Sistema de Notificaciones**
- **04_sistema_notificaciones.puml** - Proceso del sistema de notificaciones
  - Carga de notificaciones
  - Filtrado y marcado como leídas
  - Generación automática de notificaciones
  - Actualizaciones en tiempo real

### **🤖 Evaluación IA de Candidatos**
- **05_evaluacion_ia_candidatos.puml** - Proceso de evaluación con IA
  - Preparación de datos
  - Normalización de información
  - Comunicación con servicio de IA
  - Procesamiento de resultados
  - Cálculo de compatibilidad

### **👤 Gestión de Usuarios**
- **06_gestion_usuarios.puml** - Proceso de gestión de usuarios
  - Creación de usuarios
  - Edición de información
  - Cambio de roles
  - Activación/desactivación
  - Eliminación de usuarios

### **📝 Creación de Solicitudes**
- **07_proceso_creacion_solicitud.puml** - Proceso de creación de solicitudes
  - Validación de permisos
  - Completado de formulario
  - Generación de folio automático
  - Asignación de gerente
  - Lógica por rol del creador

### **✅ Aprobación de Solicitudes**
- **08_proceso_aprobacion_solicitud.puml** - Proceso de aprobación gerencial
  - Revisión de solicitudes
  - Evaluación de justificación
  - Decisión de aprobación/rechazo
  - Notificaciones automáticas
  - Manejo de modificaciones

### **🎯 Evaluación Manual**
- **09_proceso_evaluacion_manual.puml** - Proceso de evaluación manual de candidatos
  - Realización de entrevistas
  - Evaluación de competencias
  - Asignación de puntajes
  - Combinación con puntajes IA
  - Determinación de viabilidad

### **🏢 Gestión Organizacional**
- **10_proceso_gestion_organizacional.puml** - Proceso de gestión de sedes y departamentos
  - Creación de sedes
  - Creación de departamentos
  - Edición de información
  - Eliminación con verificación de dependencias
  - Validaciones de unicidad

### **🔔 Sistema de Notificaciones**
- **11_proceso_sistema_notificaciones.puml** - Proceso completo del sistema de notificaciones
  - Carga de notificaciones
  - Interacción del usuario
  - Generación automática de notificaciones
  - Filtrado y marcado como leídas
  - Monitoreo en tiempo real

### **🏊 Procesos Avanzados con Swimlanes**
- **12_proceso_completo_swimlanes.puml** - Proceso completo con swimlanes por rol
  - Separación por actores (Jefe de Area, Gerente, RH, Sistema)
  - Procesos paralelos (fork/join)
  - Flujo completo de reclutamiento
  - Interacciones entre roles

### **🔐 Autenticación Avanzada**
- **13_proceso_autenticacion_avanzado.puml** - Proceso de autenticación con características avanzadas
  - Bucles de reintento (repeat/while)
  - Manejo avanzado de errores
  - Switch cases para roles
  - Throttling y bloqueos temporales

## Elementos Clave

### **Actividades**
- **Rectángulos redondeados**: Representan actividades o acciones
- **Flechas**: Muestran el flujo de control entre actividades
- **Decisiones**: Diamantes que representan puntos de decisión

### **Flujo de Control**
- **Secuencial**: Actividades que se ejecutan en orden
- **Condicional**: Ramas basadas en condiciones
- **Paralelo**: Actividades que pueden ejecutarse simultáneamente
- **Iterativo**: Bucles y repeticiones

### **Puntos de Decisión**
- **Validaciones**: Verificación de datos y condiciones
- **Autorizaciones**: Verificación de permisos
- **Estados**: Verificación de estados del sistema
- **Errores**: Manejo de condiciones de error

## Patrones de Flujo

### **1. Proceso Lineal**
```
Inicio → Actividad 1 → Actividad 2 → Actividad 3 → Fin
```

### **2. Proceso con Decisiones**
```
Inicio → Actividad 1 → ¿Condición? → Actividad 2A / Actividad 2B → Fin
```

### **3. Proceso con Bucles**
```
Inicio → Actividad 1 → ¿Condición? → Actividad 2 → Regresar → Fin
```

### **4. Proceso con Manejo de Errores**
```
Inicio → Actividad 1 → ¿Éxito? → Actividad 2 → Fin
                    ↓
                  Manejo de Error → Fin
```

### **5. Proceso con Swimlanes (Carriles)**
```
|Actor 1| |Actor 2| |Actor 3|
Actividad 1 → Actividad 2 → Actividad 3
```

### **6. Proceso con Actividades Paralelas**
```
fork
  Actividad A
fork again
  Actividad B
end fork
```

### **7. Proceso con Bucles Avanzados**
```
repeat
  Actividad
repeat while (Condición) is (Verdadero)
```

## Uso por Rol

### **👨‍💼 Administradores**
- Gestión de usuarios
- Configuración del sistema
- Monitoreo de procesos

### **👩‍💼 RH**
- Gestión de candidatos
- Evaluación con IA
- Proceso de reclutamiento

### **👨‍💼 Gerentes**
- Aprobación de solicitudes
- Supervisión de procesos
- Toma de decisiones

### **👨‍💻 Jefes de Área**
- Creación de solicitudes
- Seguimiento de procesos
- Participación en selección

## Beneficios

### **📋 Documentación**
- Procesos claramente definidos
- Flujos de trabajo documentados
- Procedimientos estandarizados

### **🔍 Análisis**
- Identificación de puntos de mejora
- Optimización de procesos
- Reducción de errores

### **👥 Comunicación**
- Comprensión común de procesos
- Entrenamiento de usuarios
- Coordinación entre roles

### **⚙️ Implementación**
- Guía para desarrollo
- Pruebas de procesos
- Validación de funcionalidades

## Características Avanzadas de PlantUML

### **Swimlanes (Carriles)**
- Separación visual por actores o roles
- Sintaxis: `|#Color|Actor|`
- Útil para mostrar responsabilidades por rol

### **Actividades Paralelas**
- `fork` y `fork again` para actividades simultáneas
- `end fork` para unir las ramas
- Ideal para procesos que pueden ejecutarse en paralelo

### **Bucles Avanzados**
- `repeat ... repeat while (condición) is (verdadero)`
- `while (condición) is (verdadero) ... endwhile`
- `for (variable) in (rango) ... endfor`

### **Switch Cases**
- `switch (variable)`
- `case (valor) ... endcase`
- `case (otro valor) ... endcase`
- `endswitch`

### **Notas y Anotaciones**
- `note right: texto` - Nota a la derecha
- `note left: texto` - Nota a la izquierda
- `note top: texto` - Nota arriba
- `note bottom: texto` - Nota abajo

### **Colores y Estilos**
- `|#Color|` para colorear swimlanes
- `!theme plain` para tema limpio
- Colores predefinidos: LightBlue, LightGreen, LightYellow, etc.

## Notas Técnicas

- Los diagramas están optimizados para PlantUML
- Utilizan el tema "plain" para mejor legibilidad
- Incluyen notas explicativas para claridad
- Siguen las mejores prácticas de UML
- Son compatibles con herramientas de documentación
- Incluyen características avanzadas como swimlanes y procesos paralelos

## Generación de Imágenes

Para generar imágenes PNG/SVG desde los archivos .puml:

```bash
# Instalar PlantUML
npm install -g plantuml

# Generar imágenes
plantuml RH-NEXUS/docs/plantuml/activities/*.puml

# Generar imagen específica
plantuml RH-NEXUS/docs/plantuml/activities/01_proceso_reclutamiento.puml
```

## Mantenimiento

- Actualizar diagramas cuando cambien los procesos
- Revisar flujos después de cambios en el sistema
- Validar que los diagramas reflejen la implementación real
- Documentar cambios en el README

