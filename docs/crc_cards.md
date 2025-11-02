# Tarjetas CRC (Class-Responsibility-Collaboration)
## Sistema de Gestión de Recursos Humanos - RH Nexus

---

## 1. CONTROLADORES

### AuthController
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Gestionar autenticación de usuarios (login, logout)
  - Validar credenciales de acceso
  - Manejar sesiones de usuario
  - Redirigir según rol del usuario
  - Gestionar cookies de "recordar sesión"
  - Controlar tiempo de inactividad
- **Colaboración:**
  - Usuario (modelo)
  - Conexion (configuración)
  - Environment (configuración)
  - Sesiones PHP

### UsuarioController
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Gestionar operaciones CRUD de usuarios
  - Validar datos de entrada
  - Manejar subida de fotografías
  - Controlar acceso por roles
  - Generar respuestas JSON para AJAX
- **Colaboración:**
  - Usuario (modelo)
  - Sede (modelo)
  - Departamento (modelo)
  - Conexion (configuración)

### SedeController
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Gestionar operaciones CRUD de sedes
  - Validar datos de entrada
  - Controlar acceso por roles
  - Generar respuestas JSON para AJAX
- **Colaboración:**
  - Sede (modelo)
  - Conexion (configuración)

### DepartamentoController
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Gestionar operaciones CRUD de departamentos
  - Validar datos de entrada
  - Controlar acceso por roles
  - Generar respuestas JSON para AJAX
- **Colaboración:**
  - Departamento (modelo)
  - Sede (modelo)
  - Conexion (configuración)

### SolicitudController
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Gestionar operaciones CRUD de solicitudes de personal
  - Validar datos de entrada
  - Controlar acceso por roles
  - Generar respuestas JSON para AJAX
  - Manejar flujo de aprobación de solicitudes
- **Colaboración:**
  - Solicitud (modelo)
  - Usuario (modelo)
  - Sede (modelo)
  - Departamento (modelo)
  - Conexion (configuración)

---

## 2. MODELOS

### Usuario
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Gestionar datos de usuarios en la base de datos
  - Crear nuevos usuarios
  - Validar existencia de usuarios y números de empleado
  - Obtener información de usuarios
  - Eliminar usuarios lógicamente
  - Validar existencia de jefes en departamentos
  - Validar existencia de gerentes en sedes
- **Colaboración:**
  - Conexion (configuración)
  - Base de datos MySQL

### Sede
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Gestionar datos de sedes en la base de datos
  - Crear nuevas sedes
  - Validar datos de entrada
  - Obtener información de sedes
  - Validar existencia de sedes
- **Colaboración:**
  - Conexion (configuración)
  - Base de datos MySQL

### Departamento
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Gestionar datos de departamentos en la base de datos
  - Crear nuevos departamentos
  - Validar datos de entrada
  - Obtener información de departamentos
  - Validar existencia de departamentos en sedes
  - Eliminar departamentos lógicamente
  - Obtener departamentos por sede
- **Colaboración:**
  - Sede (modelo)
  - Conexion (configuración)
  - Base de datos MySQL

### Solicitud
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Gestionar datos de solicitudes de personal en la base de datos
  - Crear nuevas solicitudes
  - Generar folios únicos
  - Obtener información de solicitudes
  - Manejar estados de solicitudes
  - Validar datos de entrada
  - Obtener gerentes de sedes
- **Colaboración:**
  - Usuario (modelo)
  - Sede (modelo)
  - Departamento (modelo)
  - Conexion (configuración)
  - Base de datos MySQL

---

## 3. CONFIGURACIÓN

### Conexion
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Gestionar conexiones a la base de datos
  - Configurar parámetros de conexión según entorno
  - Manejar errores de conexión
  - Optimizar conexiones para servidores compartidos
- **Colaboración:**
  - Environment (configuración)
  - PDO (PHP)
  - Base de datos MySQL

### Environment
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Detectar entorno de ejecución (desarrollo/producción)
  - Detectar si se está ejecutando en InfinityFree
  - Detectar si se está usando HTTPS
  - Generar URLs base con protocolo correcto
  - Definir constantes de configuración
- **Colaboración:**
  - Variables de servidor PHP
  - Configuración de entorno

---

## 4. MIDDLEWARES

### auth.php
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Verificar autenticación de usuarios
  - Controlar tiempo de inactividad
  - Redirigir usuarios no autenticados
  - Limpiar sesiones expiradas
  - Actualizar timestamp de actividad
- **Colaboración:**
  - Sesiones PHP
  - Environment (configuración)
  - AuthController

### solicitudes_auth.php
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Verificar acceso al módulo de solicitudes
  - Validar roles permitidos
  - Redirigir usuarios no autorizados
  - Controlar acceso por roles específicos
- **Colaboración:**
  - Sesiones PHP
  - Environment (configuración)
  - AuthController

---

## 5. VISTAS/INTERFACES

### admin.php
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Mostrar dashboard de administrador
  - Gestionar usuarios del sistema
  - Gestionar sedes y departamentos
  - Controlar acceso completo al sistema
  - Mostrar estadísticas generales
- **Colaboración:**
  - UsuarioController
  - SedeController
  - DepartamentoController
  - SolicitudController
  - JavaScript/AJAX

### rh.php
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Mostrar dashboard de recursos humanos
  - Gestionar solicitudes de personal
  - Gestionar usuarios de RH
  - Mostrar estadísticas de RH
- **Colaboración:**
  - SolicitudController
  - UsuarioController
  - JavaScript/AJAX

### gerente.php
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Mostrar dashboard de gerente
  - Gestionar solicitudes de su sede
  - Aprobar/rechazar solicitudes
  - Mostrar estadísticas de su sede
- **Colaboración:**
  - SolicitudController
  - UsuarioController
  - JavaScript/AJAX

### jefe_area.php
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Mostrar dashboard de jefe de área
  - Gestionar solicitudes de su departamento
  - Aprobar/rechazar solicitudes
  - Mostrar estadísticas de su departamento
- **Colaboración:**
  - SolicitudController
  - UsuarioController
  - JavaScript/AJAX

### login.php
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Mostrar formulario de login
  - Validar credenciales de acceso
  - Manejar errores de autenticación
  - Redirigir según rol del usuario
- **Colaboración:**
  - AuthController
  - JavaScript/AJAX
  - SweetAlert2

---

## 6. UTILIDADES

### SweetAlert2
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Mostrar notificaciones al usuario
  - Confirmar acciones importantes
  - Mostrar mensajes de éxito/error
  - Mejorar experiencia de usuario
- **Colaboración:**
  - JavaScript
  - Todas las vistas del sistema

### JavaScript/AJAX
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Manejar peticiones asíncronas
  - Actualizar interfaz sin recargar página
  - Validar formularios en tiempo real
  - Gestionar respuestas del servidor
- **Colaboración:**
  - Todos los controladores
  - Todas las vistas
  - SweetAlert2

---

## 7. CONFIGURACIÓN DE APLICACIÓN

### app.php
- **Superclase:** Ninguna
- **Subclase:** Ninguna
- **Responsabilidad:**
  - Definir configuración de la aplicación
  - Establecer claves de seguridad
  - Configurar políticas de sesión
  - Definir límites de seguridad
- **Colaboración:**
  - AuthController
  - Environment (configuración)

---

## NOTAS IMPORTANTES

1. **Patrón MVC:** El sistema sigue el patrón Modelo-Vista-Controlador, donde los controladores manejan la lógica de negocio, los modelos gestionan los datos y las vistas presentan la información.

2. **Separación de responsabilidades:** Cada clase tiene una responsabilidad específica y bien definida, lo que facilita el mantenimiento y la escalabilidad.

3. **Colaboración entre capas:** Las clases colaboran entre sí siguiendo el flujo de datos del sistema, desde las vistas hasta los modelos a través de los controladores.

4. **Configuración centralizada:** La configuración del sistema está centralizada en las clases de configuración, facilitando el mantenimiento.

5. **Seguridad:** El sistema implementa múltiples capas de seguridad a través de middlewares, validaciones y control de acceso por roles.
