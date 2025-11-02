# Diagramas de Estados - Sistema RH-NEXUS

## Descripción

Los diagramas de estados (también conocidos como diagramas de máquina de estados) muestran los diferentes estados por los que puede pasar un objeto o sistema, así como las transiciones entre estos estados en respuesta a eventos específicos. Son fundamentales para entender el comportamiento dinámico del sistema RH-NEXUS.

## Diagramas Disponibles

### **📋 Estados de Solicitudes de Personal**
- **01_estado_solicitudes.puml** - Ciclo de vida completo de las solicitudes
  - Estados: BORRADOR, PENDIENTE, APROBADA, BUSCANDO, EN_ENTREVISTA, EN_DECISION, CERRADA, RECHAZADA
  - Transiciones: Flujo completo desde creación hasta cierre
  - Responsables: Jefe de Area, Gerente, RH según el estado

### **👥 Estados de Usuarios del Sistema**
- **02_estado_usuarios.puml** - Ciclo de vida de usuarios y roles
  - Estados: CREADO, ACTIVO, INACTIVO, BLOQUEADO, SUSPENDIDO, ELIMINADO
  - Roles: ADMIN, RH, GERENTE, JEFE_AREA
  - Transiciones: Activación, desactivación, cambio de roles

### **🎯 Estados de Candidatos**
- **03_estado_candidatos.puml** - Proceso de evaluación y selección
  - Estados: REGISTRADO, EVALUANDO, ENTREVISTADO, SELECCIONADO, CONTRATADO, DESCARTADO, EN_ESPERA
  - Evaluaciones: IA, Manual, Ranking
  - Viabilidad: ALTA, MEDIA, BAJA

### **🔐 Estados de Sesiones de Usuario**
- **04_estado_sesiones.puml** - Gestión de autenticación y sesiones
  - Estados: NO_AUTENTICADO, AUTENTICANDO, AUTENTICADO, ACTIVO, INACTIVO, EXPIRADO, BLOQUEADO
  - Seguridad: Bloqueos temporales y permanentes
  - Roles: Diferentes tipos de sesión según el rol

### **🤖 Estados del Sistema de IA**
- **05_estado_sistema_ia.puml** - Operación del sistema de inteligencia artificial
  - Estados: INACTIVO, CONECTANDO, ACTIVO, EVALUANDO, PROCESANDO_IA, RANKING, COMPLETADO, ERROR
  - Procesos: Normalización, validación, envío, recepción
  - Mantenimiento: Modo mantenimiento y reconexión

## Elementos Clave

### **Estados (States)**
- Representan condiciones o situaciones del sistema
- Pueden tener sub-estados para mayor detalle
- Incluyen descripciones de funcionalidad
- Especifican responsables y duración

### **Transiciones (Transitions)**
- Flechas que conectan estados
- Indican cambios de estado
- Pueden incluir eventos o condiciones
- Muestran la dirección del flujo

### **Eventos (Events)**
- Acciones que desencadenan transiciones
- Pueden ser internos o externos
- Incluyen condiciones de activación
- Especifican responsables

### **Notas (Notes)**
- Documentación de estados
- Responsables y acciones
- Duración y características
- Reglas de negocio

## Flujo de Estados

### **Patrón General:**
1. **Estado Inicial**: Punto de partida del proceso
2. **Estados Intermedios**: Procesamiento y validación
3. **Estados de Decisión**: Puntos de bifurcación
4. **Estados Finales**: Terminación del proceso
5. **Estados de Error**: Manejo de excepciones

### **Patrones Específicos:**

#### **Solicitudes:**
- BORRADOR → PENDIENTE → APROBADA → BUSCANDO → EN_ENTREVISTA → EN_DECISION → CERRADA
- Posibles desvíos: RECHAZADA, regreso a estados anteriores

#### **Usuarios:**
- CREADO → ACTIVO → (INACTIVO/BLOQUEADO/SUSPENDIDO) → ELIMINADO
- Cambios de rol dentro del estado ACTIVO

#### **Candidatos:**
- REGISTRADO → EVALUANDO → ENTREVISTADO → (SELECCIONADO/CONTRATADO/DESCARTADO)
- Evaluaciones paralelas: IA y Manual

#### **Sesiones:**
- NO_AUTENTICADO → AUTENTICANDO → AUTENTICADO → ACTIVO → (EXPIRADO/INACTIVO)
- Estados de seguridad: BLOQUEADO, FALLO_LOGIN

#### **Sistema IA:**
- INACTIVO → CONECTANDO → ACTIVO → EVALUANDO → PROCESANDO_IA → RANKING → COMPLETADO
- Estados de error y recuperación

## Uso por Rol

### **Para Desarrolladores:**
- Entender el flujo de estados del sistema
- Implementar validaciones de estado
- Manejar transiciones y eventos
- Debugging de flujos de estado

### **Para Analistas:**
- Comprender procesos de negocio
- Identificar puntos de decisión
- Analizar flujos de trabajo
- Documentar reglas de negocio

### **Para Administradores:**
- Entender el ciclo de vida de entidades
- Gestionar estados del sistema
- Monitorear transiciones
- Resolver problemas de estado

## Características Técnicas

### **PlantUML Syntax:**
- Usa `state` para definir estados
- Usa `-->` para mostrar transiciones
- Usa `[*]` para estado inicial/final
- Usa `note` para documentación

### **Elementos de Documentación:**
- Notas explicativas en cada estado
- Descripción de responsabilidades
- Duración y características
- Reglas de transición

### **Organización:**
- Diagramas agrupados por entidad
- Estados jerárquicos cuando es necesario
- Flujo claro de transiciones
- Documentación completa

## Ventajas de los Diagramas de Estados

1. **Claridad de Procesos**: Muestran claramente el flujo de estados
2. **Fácil Comprensión**: Son intuitivos para diferentes roles
3. **Documentación Viva**: Reflejan el comportamiento real del sistema
4. **Herramienta de Diseño**: Útiles para diseñar nuevos flujos
5. **Debugging**: Ayudan a identificar problemas de flujo

## Mantenimiento

- Actualizar cuando se modifiquen los flujos de estado
- Revisar transiciones al agregar nuevos estados
- Mantener la consistencia en la nomenclatura
- Documentar cambios en las reglas de negocio
- Validar que los diagramas reflejen la implementación real

## Relación con Otros Diagramas

- **Diagramas de Secuencia**: Muestran las interacciones que causan transiciones
- **Diagramas de Colaboración**: Detallan la colaboración entre objetos en cada estado
- **Diagramas de Flujo**: Complementan mostrando el proceso paso a paso
- **Diagramas de Clases**: Definen la estructura de las entidades que cambian de estado
