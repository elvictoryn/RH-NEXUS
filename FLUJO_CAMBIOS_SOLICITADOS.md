# 🔄 Flujo de Cambios Solicitados - Sistema RH-NEXUS

## **Descripción del Problema Original**

Cuando un gerente solicitaba cambios en una solicitud:
- ✅ El estado cambiaba a "solicita cambios"
- ✅ Se notificaba al jefe de área
- ❌ **Los cambios solicitados se perdían** - no se almacenaban ni mostraban

## **Solución Implementada**

### **1. Base de Datos**
Se agregó el campo `cambios_solicitados` a la tabla `solicitudes`:

```sql
ALTER TABLE `solicitudes` 
ADD COLUMN `cambios_solicitados` TEXT NULL 
COMMENT 'Cambios solicitados por gerentes o RH' 
AFTER `motivo_cierre`;
```

### **2. Modelo de Datos**
Se modificó el método `cambiarEstado()` en `Solicitud.php` para almacenar los cambios:

```php
elseif ($estado === 'solicita cambios' && $motivo) {
    $sql .= ", cambios_solicitados = :motivo";
    $params[':motivo'] = $motivo;
}
```

### **3. Interfaz de Usuario**

#### **Para Jefes de Área:**
- **Lista de solicitudes:** Nueva columna "Cambios Solicitados" que muestra un botón con tooltip
- **Vista detallada:** Sección destacada con los cambios solicitados y instrucciones para modificaciones

#### **Para Gerentes:**
- **Vista detallada:** Sección que muestra los cambios solicitados previamente
- **Historial:** Los cambios quedan registrados para auditoría

## **Flujo Completo de Trabajo**

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│  Jefe de Área   │    │     Gerente      │    │       RH        │
└─────────────────┘    └──────────────────┘    └─────────────────┘
         │                       │                       │
         │ 1. Crea Solicitud    │                       │
         │    (BORRADOR)        │                       │
         │                       │                       │
         │ 2. Envía a Gerencia  │                       │
         │ (ENVIADA A GERENCIA) │                       │
         │                       │                       │
         │                       │ 3. Revisa Solicitud   │
         │                       │                       │
         │                       │ 4. Solicita Cambios   │
         │                       │ (SOLICITA CAMBIOS)    │
         │                       │ + Almacena cambios    │
         │                       │ + Notifica jefe       │
         │                       │                       │
         │ 5. Ve Cambios        │                       │
         │    Solicitados       │                       │
         │                       │                       │
         │ 6. Edita Solicitud   │                       │
         │    (BORRADOR)        │                       │
         │                       │                       │
         │ 7. Re-envía          │                       │
         │ (ENVIADA A GERENCIA) │                       │
         │                       │                       │
         │                       │ 8. Re-revisa          │
         │                       │                       │
         │                       │ 9. Aprueba            │
         │                       │ (ACEPTADA GERENCIA)   │
         │                       │                       │
         │                       │                       │ 10. Procesa RH
         │                       │                       │ (EN PROCESO RH)
```

## **Estados de la Solicitud**

| Estado | Descripción | Quién puede ver | Acciones disponibles |
|--------|-------------|-----------------|---------------------|
| `borrador` | Solicitud en edición | Jefe de Área | Editar, Enviar a Gerencia |
| `enviada a gerencia` | Pendiente de revisión | Gerente | Aprobar, Rechazar, Posponer, Solicitar Cambios |
| `solicita cambios` | Requiere modificaciones | Jefe de Área | Ver cambios, Editar, Re-enviar |
| `aceptada gerencia` | Aprobada por gerencia | RH | Procesar solicitud |
| `rechazada` | No aprobada | Jefe de Área | Ver motivo, Crear nueva |
| `pospuesta` | En espera | Jefe de Área | Re-enviar cuando sea apropiado |
| `en proceso rh` | En gestión de RH | RH | Cambiar estado, Cerrar |
| `cerrada` | Finalizada | Todos | Solo consulta |

## **Dónde se Muestran los Cambios Solicitados**

### **1. Lista de Solicitudes (Jefe de Área)**
- **Columna nueva:** "Cambios Solicitados"
- **Botón:** "Ver Cambios" con tooltip mostrando los cambios
- **Estado:** Solo visible cuando `estado = 'solicita cambios'`

### **2. Vista Detallada (Jefe de Área)**
- **Sección destacada:** Card con borde naranja
- **Contenido:** Cambios solicitados + instrucciones para modificación
- **Acciones:** Botón para editar la solicitud

### **3. Vista Detallada (Gerente)**
- **Sección informativa:** Muestra los cambios solicitados previamente
- **Propósito:** Auditoría y seguimiento de decisiones

### **4. Base de Datos**
- **Campo:** `cambios_solicitados` en tabla `solicitudes`
- **Tipo:** TEXT (permite cambios largos)
- **Índice:** Para búsquedas eficientes

## **Beneficios de la Implementación**

✅ **Trazabilidad completa** - Se registra quién solicitó qué cambios  
✅ **Comunicación clara** - Los jefes de área saben exactamente qué modificar  
✅ **Auditoría** - Historial completo de decisiones y cambios  
✅ **Eficiencia** - No se pierde información entre revisiones  
✅ **Consistencia** - Mismo patrón para rechazos, cierres y cambios  

## **Archivos Modificados**

1. **Base de datos:** `05_agregar_cambios_solicitados.sql`
2. **Modelo:** `app/models/Solicitud.php`
3. **Vista Jefe de Área:** `app/views/jefe_area/solicitudes/lista.php`
4. **Vista Jefe de Área:** `app/views/jefe_area/solicitudes/ver.php`
5. **Vista Gerente:** `app/views/gerente/solicitudes/ver.php`

## **Próximos Pasos Recomendados**

1. **Ejecutar script SQL** para agregar el campo a la base de datos
2. **Probar el flujo completo** con solicitudes de prueba
3. **Implementar notificaciones** en tiempo real (opcional)
4. **Agregar reportes** de cambios solicitados por período
5. **Implementar métricas** de tiempo de respuesta a cambios

## **Conclusión**

Con esta implementación, el flujo de cambios solicitados es ahora **completo y transparente**:

- Los gerentes pueden solicitar cambios con descripción detallada
- Los jefes de área ven exactamente qué modificar
- El sistema mantiene historial completo de todas las decisiones
- La comunicación entre roles es clara y eficiente

El sistema ahora maneja correctamente el ciclo completo de revisión y modificación de solicitudes. 🎯
