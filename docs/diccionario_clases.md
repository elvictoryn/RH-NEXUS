# Diccionario de Clases - Sistema RH Nexus

## **CONTROLADORES**

### AuthController
**Ubicación:** `app/controllers/AuthController.php`  
**Propósito:** Gestionar autenticación y sesiones de usuario  
**Métodos principales:** `login()`, `logout()`, `showLogin()`

### UsuariosController
**Ubicación:** `app/controllers/usuariosController.php`  
**Propósito:** Gestionar operaciones CRUD de usuarios  
**Métodos principales:** `create()`, `index()`, `store()`, `ajax()`

### SedeController
**Ubicación:** `app/controllers/sedeController.php`  
**Propósito:** Gestionar operaciones CRUD de sedes  
**Métodos principales:** `create()`, `index()`, `store()`, `ajax()`

### DepartamentoController
**Ubicación:** `app/controllers/departamentoController.php`  
**Propósito:** Gestionar operaciones CRUD de departamentos  
**Métodos principales:** `create()`, `index()`, `store()`, `ajax()`

### SolicitudController
**Ubicación:** `app/controllers/solicitudController.php`  
**Propósito:** Gestionar operaciones CRUD de solicitudes de personal  
**Métodos principales:** `create()`, `index()`, `store()`, `ajax()`

---

## **MODELOS**

### Usuario
**Ubicación:** `app/models/Usuario.php`  
**Propósito:** Gestionar datos de usuarios en la base de datos  
**Métodos principales:** `crear()`, `existeUsuario()`, `obtenerTodosActivos()`, `eliminarLogico()`

### Sede
**Ubicación:** `app/models/Sede.php`  
**Propósito:** Gestionar datos de sedes en la base de datos  
**Métodos principales:** `crear()`, `existeNombre()`, `obtenerTodas()`, `actualizar()`

### Departamento
**Ubicación:** `app/models/departamento.php`  
**Propósito:** Gestionar datos de departamentos en la base de datos  
**Métodos principales:** `crear()`, `existeNombreEnSede()`, `obtenerTodosConSede()`, `eliminarLogico()`

### Solicitud
**Ubicación:** `app/models/Solicitud.php`  
**Propósito:** Gestionar datos de solicitudes de personal en la base de datos  
**Métodos principales:** `crear()`, `generarFolio()`, `getGerenteDeSede()`

---

## **CONFIGURACIÓN**

### Conexion
**Ubicación:** `config/conexion.php`  
**Propósito:** Gestionar conexiones a la base de datos  
**Métodos principales:** `getConexion()`

### Environment
**Ubicación:** `config/environment.php`  
**Propósito:** Detectar entorno y configurar constantes  
**Funciones principales:** `isInfinityFree()`, `isHTTPS()`, `getBaseURL()`

### App
**Ubicación:** `config/app.php`  
**Propósito:** Configuración general de la aplicación  
**Contenido:** Claves de seguridad, políticas de sesión

---

## **MIDDLEWARES**

### auth.php
**Ubicación:** `app/middlewares/auth.php`  
**Propósito:** Verificar autenticación y controlar inactividad  
**Funcionalidad:** Redirigir usuarios no autenticados, limpiar sesiones expiradas

### solicitudes_auth.php
**Ubicación:** `app/middlewares/solicitudes_auth.php`  
**Propósito:** Controlar acceso al módulo de solicitudes  
**Funcionalidad:** Validar roles permitidos, redirigir usuarios no autorizados

---

## **VISTAS/INTERFACES**

### admin.php
**Ubicación:** `public/admin.php`  
**Propósito:** Dashboard principal del administrador  
**Funcionalidad:** Acceso completo al sistema, gestión de usuarios, sedes y departamentos

### rh.php
**Ubicación:** `public/rh.php`  
**Propósito:** Dashboard de recursos humanos  
**Funcionalidad:** Gestión de solicitudes de personal, usuarios de RH

### gerente.php
**Ubicación:** `public/gerente.php`  
**Propósito:** Dashboard del gerente  
**Funcionalidad:** Gestión de solicitudes de su sede, aprobaciones

### jefe_area.php
**Ubicación:** `public/jefe_area.php`  
**Propósito:** Dashboard del jefe de área  
**Funcionalidad:** Gestión de solicitudes de su departamento, aprobaciones

### login.php
**Ubicación:** `public/login.php`  
**Propósito:** Formulario de autenticación  
**Funcionalidad:** Login de usuarios, validación de credenciales

---

## **CLASES DE VISTAS ESPECÍFICAS**

### UsuarioCrea
**Ubicación:** `app/views/admin/usuarios/crear_usuario.php`  
**Propósito:** Lógica para crear usuarios  
**Métodos principales:** `existeUsuario()`, `crear()`, `sedesActivas()`

### UsuarioEdita
**Ubicación:** `app/views/admin/usuarios/editar_usuario.php`  
**Propósito:** Lógica para editar usuarios  
**Métodos principales:** `obtenerPorId()`, `actualizar()`, `asignarResponsableDepto()`

---

## **UTILIDADES**

### SweetAlert2
**Ubicación:** `public/js/sweetalert2.min.js`  
**Propósito:** Notificaciones y alertas al usuario  
**Funcionalidad:** Mensajes de éxito/error, confirmaciones

### JavaScript/AJAX
**Ubicación:** `public/js/`  
**Propósito:** Peticiones asíncronas y validaciones  
**Funcionalidad:** Comunicación con controladores, actualización de interfaz

---

## **PYTHON API**

### EvaluadorCandidatos
**Ubicación:** `python_api/evaluar_candidatos_nuevos.py`  
**Propósito:** Evaluar candidatos usando machine learning  
**Funcionalidad:** Cargar modelo, evaluar candidatos, generar reportes

### ModeloEntrenamiento
**Ubicación:** `python_api/modelo_entrenamiento.ipynb`  
**Propósito:** Entrenar modelo de machine learning  
**Funcionalidad:** Procesar datos, entrenar modelo, validar resultados

---

## **ARCHIVOS DE CONFIGURACIÓN**

### .htaccess
**Ubicación:** `RH-NEXUS/.htaccess`  
**Propósito:** Configuración de Apache para redirecciones  
**Funcionalidad:** Redirigir a sistema_rh/public/, seguridad

### .htaccess_https
**Ubicación:** `RH-NEXUS/.htaccess_https`  
**Propósito:** Forzar HTTPS en InfinityFree  
**Funcionalidad:** Redirección HTTP a HTTPS, headers de seguridad

### public/.htaccess
**Ubicación:** `public/.htaccess`  
**Propósito:** Rewrite rules para URLs limpias  
**Funcionalidad:** Redirigir todas las rutas a index.php

---

## **ARCHIVOS PRINCIPALES**

### index.php
**Ubicación:** `public/index.php`  
**Propósito:** Punto de entrada principal del sistema  
**Funcionalidad:** Routing, redirección según entorno

### logout.php
**Ubicación:** `public/logout.php`  
**Propósito:** Cerrar sesión de usuario  
**Funcionalidad:** Limpiar sesión, redirigir a login

---

## **NOTAS IMPORTANTES**

1. **Patrón MVC:** El sistema sigue el patrón Modelo-Vista-Controlador
2. **Separación de responsabilidades:** Cada clase tiene una función específica
3. **Configuración dinámica:** El sistema detecta automáticamente el entorno
4. **Seguridad:** Múltiples capas de autenticación y autorización
5. **Escalabilidad:** Estructura modular que facilita el mantenimiento

---

## **LEGENDAS**

- **Controladores:** Manejan la lógica de negocio y comunicación entre modelos y vistas
- **Modelos:** Gestionan los datos y operaciones de base de datos
- **Vistas:** Presentan la información al usuario
- **Middlewares:** Interceptan peticiones para validaciones
- **Configuración:** Define parámetros del sistema
- **Utilidades:** Herramientas auxiliares para funcionalidad específica
